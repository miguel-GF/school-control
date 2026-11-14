<?php

namespace App\Providers;

use App\Payments\PaymentGatewayStrategy;
use App\Payments\StripePaymentStrategy;
use Illuminate\Support\ServiceProvider;
use InvalidArgumentException;
use Stripe\StripeClient;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(StripeClient::class, function (): StripeClient {
            $secret = config('services.stripe.secret');

            if (empty($secret)) {
                throw new InvalidArgumentException('Falta configurar STRIPE_SECRET_KEY.');
            }

            return new StripeClient($secret);
        });

        $this->app->bind(PaymentGatewayStrategy::class, function ($app): PaymentGatewayStrategy {
            return match (config('payments.gateway')) {
                'stripe' => $app->make(StripePaymentStrategy::class),
                default => throw new InvalidArgumentException('La pasarela de pago configurada no está soportada.'),
            };
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
