<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Stripe\StripeClient;

class PaymentController
{
    public function checkout(int $userId, int $amount)
    {
        if ($amount < 1 || $amount > 10000) {
            http_response_code(422);

            return 'Montant invalide.';
        }

        $user = User::findOrFail($userId);

        $stripe = new StripeClient((string) getenv('STRIPE_SECRET'));
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
            'success_url' => '/deposit/success?session_id={CHECKOUT_SESSION_ID}',
            'cancel_url' => '/deposit/cancel',
        ]);

        header('Location: ' . $session->url);
        return null;
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
