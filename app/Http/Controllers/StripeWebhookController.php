<?php

namespace App\Http\Controllers;

use App\Models\Subscription;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Stripe\Webhook;

class StripeWebhookController extends Controller
{
    public function handle(Request $request): Response
    {
        $payload = $request->getContent();
        $signature = $request->header('Stripe-Signature');
        $webhookSecret = config('services.stripe.webhook_secret');

        if (! $webhookSecret) {
            return response('Webhook secret não configurado', 500);
        }

        try {
            $event = Webhook::constructEvent($payload, $signature, $webhookSecret);
        } catch (\Exception) {
            return response('Assinatura inválida', 400);
        }

        if ($event->type !== 'payment_intent.succeeded') {
            return response('Evento ignorado', 200);
        }

        $intent = $event->data->object;
        $subscription = Subscription::where('stripe_payment_intent_id', $intent->id)->first();

        if ($subscription && $subscription->status !== Subscription::STATUS_ACTIVE) {
            $subscription->update([
                'status' => Subscription::STATUS_ACTIVE,
                'stripe_status' => 'succeeded',
                'starts_at' => now(),
                'expires_at' => now()->addMonth(),
            ]);
        }

        return response('OK', 200);
    }
}
