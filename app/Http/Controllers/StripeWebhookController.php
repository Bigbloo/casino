<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use Stripe\Exception\SignatureVerificationException;
use Stripe\Webhook;
use Throwable;

class StripeWebhookController
{
    public function handle(): Response
    {
        $payload = request()->getContent();
        $signature = (string) request()->header('Stripe-Signature', '');
        $secret = config('services.stripe.webhook_secret') ?? getenv('STRIPE_WEBHOOK_SECRET');

        if (! is_string($secret) || $secret === '') {
            Log::error('Stripe webhook secret is not configured.');
            return response('Webhook secret missing', 500);
        }

        try {
            $event = Webhook::constructEvent($payload, $signature, $secret);
        } catch (SignatureVerificationException $exception) {
            return response('Invalid signature', 400);
        } catch (Throwable $exception) {
            return response('Invalid payload', 400);
        }

        if ($event->type === 'checkout.session.completed') {
            $session = $event->data->object;
            $userId = (int) ($session->metadata->user_id ?? 0);
            $amountCents = (int) ($session->metadata->amount_cents ?? 0);
            $paymentStatus = (string) ($session->payment_status ?? '');
            $paymentIntentId = (string) ($session->payment_intent ?? '');

            if ($userId > 0 && $amountCents > 0 && $paymentStatus === 'paid') {
                $user = User::find($userId);

                if ($user instanceof User) {
                    if ($paymentIntentId !== '' && $user->transactions()->where('reference', $paymentIntentId)->exists()) {
                        return response('Webhook already processed', 200);
                    }

                    $reference = $paymentIntentId !== '' ? $paymentIntentId : 'checkout_session_' . ((string) ($session->id ?? uniqid('', true)));
                    $user->creditBalance($amountCents, $reference);
                }
            }
        }

        return response('Webhook handled', 200);
    }
}
