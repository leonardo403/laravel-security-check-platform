<?php

namespace App\Services\Payment;

use App\Models\SubscriptionPlan;
use App\Models\User;
use Stripe\StripeClient;

class StripePaymentService
{
    private ?StripeClient $client;

    public function __construct()
    {
        $this->client = config('services.stripe.secret_key')
            ? new StripeClient(config('services.stripe.secret_key'))
            : null;
    }

    public function isConfigured(): bool
    {
        return $this->client !== null;
    }

    public function createPaymentIntent(SubscriptionPlan $plan, User $user): array
    {
        $intent = $this->client()->paymentIntents->create([
            'amount' => $this->amountInCents($plan),
            'currency' => 'usd',
            'automatic_payment_methods' => ['enabled' => true],
            'metadata' => [
                'plan_id' => (string) $plan->id,
                'user_id' => (string) $user->id,
            ],
        ]);

        return [
            'id' => $intent->id,
            'client_secret' => $intent->client_secret,
        ];
    }

    public function isPaymentIntentSucceeded(string $paymentIntentId): bool
    {
        $intent = $this->client()->paymentIntents->retrieve($paymentIntentId);

        return $intent->status === 'succeeded';
    }

    public function amountInCents(SubscriptionPlan $plan): int
    {
        return (int) round((float) $plan->price * 100);
    }

    public function publishableKey(): ?string
    {
        return config('services.stripe.publishable_key');
    }

    private function client(): StripeClient
    {
        if (! $this->client) {
            throw new \RuntimeException('Stripe não configurado. Defina STRIPE_SECRET_KEY no arquivo .env.');
        }

        return $this->client;
    }
}
