<?php

namespace App\Payments;

use Stripe\StripeClient;
use Stripe\Webhook;

class StripePaymentStrategy implements PaymentGatewayStrategy
{
    public function __construct(private readonly StripeClient $stripe)
    {
    }

    public function createCustomer(array $metadata): string
    {
        $customer = $this->stripe->customers->create(['metadata' => $metadata]);

        return $customer->id;
    }

    public function createEphemeralKey(string $customerId): string
    {
        $apiVersion = config('services.stripe.api_version');
        if (!$apiVersion) {
            throw new \InvalidArgumentException('Falta configurar STRIPE_API_VERSION.');
        }
        $key = $this->stripe->ephemeralKeys->create(
            ['customer' => $customerId],
            ['stripe_version' => $apiVersion]
        );

        return $key->secret;
    }

    public function createSetupIntent(string $customerId): array
    {
        $intent = $this->stripe->setupIntents->create([
            'customer' => $customerId,
            'usage' => 'off_session',
            'automatic_payment_methods' => [
                'enabled' => true,
                'allow_redirects' => 'never',
            ],
        ]);

        return $intent->toArray();
    }

    public function createPaymentIntent(string $customerId, int $amount, string $currency, string $description, array $metadata, string $idempotencyKey): array
    {
        $intent = $this->stripe->paymentIntents->create([
            'amount' => $amount,
            'currency' => $currency,
            'customer' => $customerId,
            'description' => $description,
            'metadata' => $metadata,
            'setup_future_usage' => 'off_session',
            'automatic_payment_methods' => ['enabled' => true],
        ], ['idempotency_key' => $idempotencyKey]);

        return $intent->toArray();
    }

    public function retrievePaymentIntent(string $paymentIntentId): array
    {
        return $this->stripe->paymentIntents->retrieve($paymentIntentId, [])->toArray();
    }

    public function listPaymentMethods(string $customerId): array
    {
        return $this->stripe->paymentMethods->all(['customer' => $customerId, 'type' => 'card'])->data;
    }

    public function detachPaymentMethod(string $paymentMethodId): void
    {
        $this->stripe->paymentMethods->detach($paymentMethodId, []);
    }

    public function verifyWebhook(string $payload, string $signature): array
    {
        $secret = config('services.stripe.webhook_secret');
        if (!$secret) {
            throw new \InvalidArgumentException('Falta configurar STRIPE_WEBHOOK_SECRET.');
        }

        return Webhook::constructEvent($payload, $signature, $secret)->toArray();
    }
}
