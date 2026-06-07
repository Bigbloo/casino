<?php

namespace App\Http\Controllers;

use App\Models\User;
use Stripe\Exception\SignatureVerificationException;
use Stripe\Webhook;

class StripeWebhookController
{
    public function handle()
    {
        $payload = @file_get_contents('php://input') ?: '';
        $signature = $_SERVER['HTTP_STRIPE_SIGNATURE'] ?? '';
        $secret = (string) getenv('STRIPE_WEBHOOK_SECRET');

        try {
            $event = Webhook::constructEvent($payload, $signature, $secret);
        } catch (SignatureVerificationException $exception) {
            http_response_code(400);
            return 'Invalid signature';
        } catch (\Throwable $exception) {
            http_response_code(400);
            return 'Invalid payload';
        }

        if ($event->type === 'checkout.session.completed') {
            $session = $event->data->object;
            $userId = (int) ($session->metadata->user_id ?? 0);
            $amountCents = (int) ($session->metadata->amount_cents ?? 0);

            if ($userId > 0 && $amountCents > 0 && $session->payment_status === 'paid') {
                $user = User::findOrFail($userId);
                $user->creditBalance($amountCents);
            }
        }

        http_response_code(200);
        return 'Webhook handled';
    }
}
