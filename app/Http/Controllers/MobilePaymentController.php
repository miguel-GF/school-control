<?php

namespace App\Http\Controllers;

use App\Models\PaymentAttempt;
use App\Models\PaymentCustomer;
use App\Models\PaymentMethod;
use App\Models\SchoolPayment;
use App\Payments\PaymentGatewayStrategy;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Stripe\Exception\SignatureVerificationException;
use UnexpectedValueException;

class MobilePaymentController extends Controller
{
    public function configuracion(): JsonResponse
    {
        return response()->json([
            'gateway' => config('payments.gateway'),
            'publishable_key' => config('services.stripe.publishable'),
            'currency' => config('payments.currency'),
            'amount_cents' => config('payments.demo_amount_cents'),
        ]);
    }

    /**
     * @deprecated La app Flutter ya no usa SetupIntent para guardar tarjetas:
     * Stripe puede rechazar el alta por saldo al confirmarlo. Se conserva por
     * compatibilidad con clientes viejos.
     */
    public function crearSetupIntent(Request $request): JsonResponse
    {
        $customer = $this->customerFor($request);
        $intent = $this->gateway()->createSetupIntent($customer->provider_customer_id);

        return response()->json([
            'customer_id' => $customer->provider_customer_id,
            'ephemeral_key_secret' => $this->gateway()->createEphemeralKey($customer->provider_customer_id),
            'setup_intent_client_secret' => $intent['client_secret'],
        ]);
    }

    public function listarTarjetas(Request $request): JsonResponse
    {
        $alumnoId = $request->user()->getKey();
        $gatewayName = config('payments.gateway');
        $customer = PaymentCustomer::where('alumno_id', $alumnoId)
            ->where('gateway', $gatewayName)->first();

        if ($customer) {
            $this->syncAttachedCards($customer);
        }

        $cards = PaymentMethod::where('alumno_id', $alumnoId)
            ->where('gateway', $gatewayName)
            ->orderBy('created_at')
            ->get()
            ->map(fn (PaymentMethod $method) => $this->cardResponse($method))
            ->values();

        return response()->json(['data' => $cards]);
    }

    public function adjuntarTarjeta(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'payment_method_id' => ['required', 'string', 'starts_with:pm_'],
        ]);
        $customer = $this->customerFor($request);

        try {
            $method = $this->gateway()->retrievePaymentMethod($validated['payment_method_id']);
        } catch (\Throwable $exception) {
            Log::warning('No se pudo recuperar el método de pago', [
                'alumno_id' => $request->user()->getKey(),
                'error' => $exception->getMessage(),
            ]);

            return response()->json([
                'message' => 'La tarjeta no es válida o ya no está disponible.',
            ], 422);
        }

        $card = $method['card'] ?? null;
        if (!is_array($card) || $card === []) {
            return response()->json(['message' => 'El método de pago no es una tarjeta.'], 422);
        }

        $owner = $method['customer'] ?? null;
        if ($owner && $owner !== $customer->provider_customer_id) {
            return response()->json(['message' => 'La tarjeta no pertenece a este alumno.'], 409);
        }

        if ($this->expired($card)) {
            return response()->json(['message' => 'La tarjeta está vencida.'], 422);
        }

        // No se adjunta la tarjeta al customer en el alta: Stripe verifica la
        // tarjeta al adjuntarla y puede rechazarla por saldo. Se guarda como
        // pendiente y Stripe la adjunta sola cuando el primer pago sea exitoso.
        $saved = $this->saveCard($customer, $method, $owner === $customer->provider_customer_id);

        return response()->json(['data' => $this->cardResponse($saved)], 201);
    }

    public function eliminarTarjeta(Request $request, string $paymentMethodId): JsonResponse
    {
        $alumnoId = $request->user()->getKey();
        $gatewayName = config('payments.gateway');
        $customer = PaymentCustomer::where('alumno_id', $alumnoId)
            ->where('gateway', $gatewayName)->first();
        $local = PaymentMethod::where('alumno_id', $alumnoId)
            ->where('gateway', $gatewayName)
            ->where('provider_payment_method_id', $paymentMethodId)
            ->first();

        $attached = false;
        if ($customer) {
            $attached = collect($this->gateway()->listPaymentMethods($customer->provider_customer_id))
                ->contains(fn ($method) => ($method['id'] ?? null) === $paymentMethodId);
        }

        if (!$local && !$attached) {
            return response()->json(['message' => 'Tarjeta no encontrada.'], 404);
        }

        if ($attached) {
            $this->gateway()->detachPaymentMethod($paymentMethodId);
        }
        $local?->delete();

        return response()->json(['message' => 'Tarjeta eliminada.']);
    }

    public function crearPago(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'request_id' => ['required', 'uuid'],
        ]);
        $amount = (int) config('payments.demo_amount_cents');
        $currency = config('payments.currency');

        if ($amount < 1000 || $currency !== 'mxn') {
            return response()->json(['message' => 'La configuración de pago de prueba no es válida.'], 503);
        }

        $payment = SchoolPayment::firstOrCreate(
            ['id' => $validated['request_id']],
            [
                'alumno_id' => $request->user()->getKey(),
                'amount_cents' => $amount,
                'currency' => $currency,
                'status' => 'pending',
                'gateway' => config('payments.gateway'),
                'description' => 'Pago de prueba escolar',
            ]
        );

        if ((string) $payment->alumno_id !== (string) $request->user()->getKey()) {
            return response()->json(['message' => 'No se pudo iniciar el pago.'], 409);
        }

        if ($payment->provider_payment_id) {
            return response()->json($this->paymentResponse($payment));
        }

        $customer = $this->customerFor($request);
        $intent = $this->gateway()->createPaymentIntent(
            $customer->provider_customer_id,
            $payment->amount_cents,
            $payment->currency,
            $payment->description,
            ['payment_id' => $payment->id, 'alumno_id' => (string) $payment->alumno_id],
            $payment->id
        );

        $payment->provider_payment_id = $intent['id'];
        $payment->save();
        $payment->attempts()->create([
            'gateway' => config('payments.gateway'),
            'provider_payment_id' => $intent['id'],
            'status' => $intent['status'],
            'metadata' => ['provider' => config('payments.gateway')],
        ]);

        return response()->json($this->paymentResponse(
            $payment,
            $intent['client_secret'],
            $customer->provider_customer_id,
            $this->gateway()->createEphemeralKey($customer->provider_customer_id)
        ));
    }

    public function confirmarPago(Request $request, string $paymentIntentId): JsonResponse
    {
        $payment = SchoolPayment::where('alumno_id', $request->user()->getKey())
            ->where('provider_payment_id', $paymentIntentId)->firstOrFail();
        $intent = $this->gateway()->retrievePaymentIntent($paymentIntentId);
        $this->syncStatus($payment, $intent['status'], null, $intent['payment_method'] ?? null);

        return response()->json($this->paymentResponse($payment));
    }

    public function listarPagos(Request $request): JsonResponse
    {
        $payments = SchoolPayment::where('alumno_id', $request->user()->getKey())
            ->orderByDesc('created_at')->get()->map(fn ($payment) => $this->paymentResponse($payment));

        return response()->json(['data' => $payments]);
    }

    public function webhook(Request $request): JsonResponse
    {
        try {
            $event = $this->gateway()->verifyWebhook($request->getContent(), (string) $request->header('Stripe-Signature'));
        } catch (SignatureVerificationException|UnexpectedValueException $exception) {
            return response()->json(['message' => 'Webhook inválido.'], 400);
        }

        $object = $event['data']['object'] ?? [];
        $intentId = $object['id'] ?? null;
        $status = match ($event['type'] ?? '') {
            'payment_intent.succeeded' => 'succeeded',
            'payment_intent.payment_failed' => 'failed',
            'payment_intent.canceled' => 'canceled',
            default => null,
        };

        if ($intentId && $status) {
            $payment = SchoolPayment::where('provider_payment_id', $intentId)->first();
            if ($payment) {
                $this->syncStatus(
                    $payment,
                    $status,
                    $object['last_payment_error']['code'] ?? null,
                    $object['payment_method'] ?? null
                );
            }
        }

        return response()->json(['received' => true]);
    }

    private function customerFor(Request $request): PaymentCustomer
    {
        $alumno = $request->user();
        $gatewayName = config('payments.gateway');
        $customer = PaymentCustomer::where('alumno_id', $alumno->getKey())
            ->where('gateway', $gatewayName)->first();

        if ($customer) {
            return $customer;
        }

        $providerId = $this->gateway()->createCustomer([
            'alumno_id' => (string) $alumno->getKey(),
            'matricula' => (string) $alumno->numestudiante,
        ]);

        return PaymentCustomer::create([
            'alumno_id' => $alumno->getKey(),
            'gateway' => $gatewayName,
            'provider_customer_id' => $providerId,
        ]);
    }

    private function gateway(): PaymentGatewayStrategy
    {
        return app(PaymentGatewayStrategy::class);
    }

    private function syncAttachedCards(PaymentCustomer $customer): void
    {
        try {
            foreach ($this->gateway()->listPaymentMethods($customer->provider_customer_id) as $method) {
                $this->saveCard($customer, $method, true);
            }
        } catch (\Throwable $exception) {
            Log::warning('No se pudieron sincronizar las tarjetas del customer', [
                'alumno_id' => $customer->alumno_id,
                'error' => $exception->getMessage(),
            ]);
        }
    }

    private function saveCard(PaymentCustomer $customer, array $method, bool $attached): PaymentMethod
    {
        $card = $method['card'] ?? [];

        return PaymentMethod::updateOrCreate(
            [
                'alumno_id' => $customer->alumno_id,
                'gateway' => $customer->gateway,
                'provider_payment_method_id' => $method['id'],
            ],
            [
                'brand' => $card['brand'] ?? 'unknown',
                'last4' => $card['last4'] ?? '',
                'exp_month' => $card['exp_month'] ?? null,
                'exp_year' => $card['exp_year'] ?? null,
                'attached' => $attached,
            ]
        );
    }

    private function cardResponse(PaymentMethod $method): array
    {
        return [
            'id' => $method->provider_payment_method_id,
            'brand' => $method->brand,
            'last4' => $method->last4,
            'exp_month' => $method->exp_month,
            'exp_year' => $method->exp_year,
        ];
    }

    private function expired(array $card): bool
    {
        $month = (int) ($card['exp_month'] ?? 0);
        $year = (int) ($card['exp_year'] ?? 0);
        if ($month < 1 || $month > 12 || $year < 1) {
            return true;
        }

        $now = now();

        return $year < $now->year || ($year === $now->year && $month < $now->month);
    }

    private function syncStatus(SchoolPayment $payment, string $providerStatus, ?string $failureCode = null, ?string $paymentMethodId = null): void
    {
        $status = match ($providerStatus) {
            'succeeded' => 'paid',
            'requires_payment_method', 'canceled' => 'failed',
            'processing' => 'processing',
            default => 'pending',
        };
        $payment->status = $status;
        if ($paymentMethodId) {
            $payment->payment_method = 'card';
            if ($status === 'paid') {
                PaymentMethod::where('alumno_id', $payment->alumno_id)
                    ->where('gateway', $payment->gateway)
                    ->where('provider_payment_method_id', $paymentMethodId)
                    ->update(['attached' => true]);
            }
        }
        if ($status === 'paid' && !$payment->paid_at) {
            $payment->paid_at = now();
        }
        $payment->save();
        $attempt = $payment->attempts()->where('provider_payment_id', $payment->provider_payment_id)->first();
        if ($attempt) {
            $metadata = $attempt->metadata ?? [];
            if ($paymentMethodId) {
                $metadata['provider_payment_method_id'] = $paymentMethodId;
            }
            $attempt->update([
                'status' => $providerStatus,
                'failure_code' => $failureCode,
                'metadata' => $metadata,
            ]);
        }
    }

    private function paymentResponse(SchoolPayment $payment, ?string $clientSecret = null, ?string $customerId = null, ?string $ephemeralKey = null): array
    {
        return array_filter([
            'id' => $payment->id,
            'amount_cents' => $payment->amount_cents,
            'currency' => $payment->currency,
            'status' => $payment->status,
            'gateway' => $payment->gateway,
            'payment_method' => $payment->payment_method,
            'description' => $payment->description,
            'folio' => $payment->provider_payment_id,
            'paid_at' => $payment->paid_at?->toIso8601String(),
            'created_at' => $payment->created_at?->toIso8601String(),
            'client_secret' => $clientSecret,
            'customer_id' => $customerId,
            'ephemeral_key_secret' => $ephemeralKey,
        ], fn ($value) => $value !== null);
    }
}
