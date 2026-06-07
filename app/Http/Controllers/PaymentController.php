<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Stripe\StripeClient;
use Throwable;

class PaymentController
{
    public function checkout(int $userId, int $amount): RedirectResponse
    {
        if ($amount < 1 || $amount > 10000) {
            abort(422, 'Montant invalide.');
        }

        if ((int) auth()->id() !== $userId) {
            abort(403, 'Action non autorisée.');
        }

        $user = User::findOrFail($userId);

        $secret = config('services.stripe.secret') ?? getenv('STRIPE_SECRET');
        if (! is_string($secret) || $secret === '') {
            Log::error('Stripe secret is not configured.');
            abort(500, 'Configuration de paiement invalide.');
        }

        try {
            $stripe = new StripeClient($secret);
            $session = $stripe->checkout->sessions->create([
                'payment_method_types' => ['card'],
                'line_items' => [[
                    'price_data' => [
                        'currency' => 'eur',
                        'product_data' => [
                            'name' => 'Dépôt sur Gaming Platform',
                        ],
                        'unit_amount' => $amount * 100,
                    ],
                    'quantity' => 1,
                ]],
                'mode' => 'payment',
                'metadata' => [
                    'user_id' => (string) $user->id,
                    'amount_cents' => (string) ($amount * 100),
                ],
                'success_url' => url('/deposit/success?session_id={CHECKOUT_SESSION_ID}'),
                'cancel_url' => url('/deposit/cancel'),
            ]);
        } catch (Throwable $exception) {
            Log::error('Stripe checkout session creation failed.', ['exception' => $exception]);
            abort(502, 'Impossible de créer la session de paiement.');
        }

        return redirect()->away((string) $session->url);
    }

    public function success(Request $request)
    {
        return view('payment.success');
    }

    public function cancel()
    {
        return view('payment.cancel');
    }
}
