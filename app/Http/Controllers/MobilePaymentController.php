<?php

namespace App\Http\Controllers;

use App\Models\PaymentAttempt;
use App\Models\PaymentCustomer;
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
        $customer = PaymentCustomer::where('alumno_id', $request->user()->getKey())
            ->where('gateway', config('payments.gateway'))->first();

        if (!$customer) {
            return response()->json(['data' => []]);
        }

        $cards = collect($this->gateway()->listPaymentMethods($customer->provider_customer_id))
            ->map(function ($method) {
                $card = $method->card;

                return [
                    'id' => $method->id,
                    'brand' => $card->brand,
                    'last4' => $card->last4,
                    'exp_month' => $card->exp_month,
                    'exp_year' => $card->exp_year,
                ];
            })->values();

        return response()->json(['data' => $cards]);
    }

    public function eliminarTarjeta(Request $request, string $paymentMethodId): JsonResponse
    {
        $customer = PaymentCustomer::where('alumno_id', $request->user()->getKey())
            ->where('gateway', config('payments.gateway'))->firstOrFail();
        $owned = collect($this->gateway()->listPaymentMethods($customer->provider_customer_id))
            ->contains(fn ($method) => $method->id === $paymentMethodId);

        if (!$owned) {
            return response()->json(['message' => 'Tarjeta no encontrada.'], 404);
        }

        $this->gateway()->detachPaymentMethod($paymentMethodId);

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
