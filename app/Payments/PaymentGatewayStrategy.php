<?php

namespace App\Payments;

interface PaymentGatewayStrategy
{
    public function createCustomer(array $metadata): string;

    public function createEphemeralKey(string $customerId): string;

    public function createSetupIntent(string $customerId): array;

    public function createPaymentIntent(string $customerId, int $amount, string $currency, string $description, array $metadata, string $idempotencyKey): array;

    public function retrievePaymentIntent(string $paymentIntentId): array;

    public function listPaymentMethods(string $customerId): array;

    public function attachPaymentMethod(string $customerId, string $paymentMethodId): array;

    public function detachPaymentMethod(string $paymentMethodId): void;

    public function verifyWebhook(string $payload, string $signature): array;
}
