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

    public function retrievePaymentMethod(string $paymentMethodId): array;

    /**
     * @deprecated No usar al guardar una tarjeta: Stripe verifica la tarjeta
     * al adjuntarla y puede rechazarla por saldo. El alta debe guardarla como
     * pendiente y dejar que Stripe la adjunte tras un pago exitoso.
     */
    public function attachPaymentMethod(string $customerId, string $paymentMethodId): array;

    public function detachPaymentMethod(string $paymentMethodId): void;

    public function verifyWebhook(string $payload, string $signature): array;
}
