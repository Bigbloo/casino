<?php

namespace VanguardLTE\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use VanguardLTE\User;

class PaymentController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth')->except(['webhookStripe']);
    }

    /**
     * Initiate a Stripe Checkout session for a deposit.
     *
     * @param  int    $userId
     * @param  float  $amount  Amount in EUR
     */
    public function checkout($userId, $amount)
    {
        $user = User::findOrFail($userId);

        // Ensure the authenticated user can only deposit for themselves
        if (auth()->id() !== (int) $userId) {
            abort(403, 'Unauthorized action.');
        }

        $amount = (float) $amount;
        if ($amount < 1 || $amount > 10000) {
            return back()->withErrors(['amount' => 'Le montant doit être compris entre 1 € et 10 000 €.']);
        }

        $secretKey = config('payments.drivers.stripe.secret_key') ?: env('STRIPE_SECRET_KEY') ?: env('STRIPE_SECRET');
        if (!$secretKey) {
            return back()->withErrors(['stripe' => 'Stripe n\'est pas configuré.']);
        }

        // Record the payment intent in DB before redirecting
        $intentId = DB::table('payment_intents')->insertGetId([
            'user_id'    => $user->id,
            'driver'     => 'stripe',
            'amount'     => $amount,
            'currency'   => strtolower(config('payments.default_currency', 'EUR')),
            'status'     => 'pending',
            'meta'       => json_encode([]),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $stripe = new \Stripe\StripeClient($secretKey);

        try {
            $session = $stripe->checkout->sessions->create([
                'payment_method_types' => ['card'],
                'customer_email'       => $user->email,
                'client_reference_id'  => (string) $intentId,
                'line_items'           => [[
                    'price_data' => [
                        'currency'     => strtolower(config('payments.default_currency', 'eur')),
                        'product_data' => [
                            'name' => 'Dépôt sur ' . config('app.name', 'Gaming Platform'),
                        ],
                        'unit_amount'  => (int) round($amount * 100),
                    ],
                    'quantity' => 1,
                ]],
                'mode'        => 'payment',
                'success_url' => route('deposit.success') . '?session_id={CHECKOUT_SESSION_ID}',
                'cancel_url'  => route('deposit.cancel'),
            ]);
        } catch (\Stripe\Exception\ApiErrorException $e) {
            Log::error('Stripe checkout error: ' . $e->getMessage());
            return back()->withErrors(['stripe' => 'Erreur Stripe : ' . $e->getMessage()]);
        }

        // Store the Stripe session URL in the intent record
        DB::table('payment_intents')->where('id', $intentId)->update([
            'external_id' => $session->id,
            'payment_url' => $session->url,
            'updated_at'  => now(),
        ]);

        return redirect($session->url);
    }

    /**
     * Handle successful payment return from Stripe.
     */
    public function success(Request $request)
    {
        $sessionId = $request->query('session_id');

        $intent = null;
        if ($sessionId) {
            $intent = DB::table('payment_intents')
                ->where('external_id', $sessionId)
                ->where('user_id', auth()->id())
                ->first();
        }

        return view('payment.success', compact('intent'));
    }

    /**
     * Handle cancelled payment return from Stripe.
     */
    public function cancel()
    {
        return view('payment.cancel');
    }

    /**
     * Handle Stripe webhook events (checkout.session.completed).
     * This endpoint must be excluded from CSRF verification.
     */
    public function webhookStripe(Request $request)
    {
        $webhookSecret = config('payments.drivers.stripe.webhook_secret') ?: env('STRIPE_WEBHOOK_SECRET');
        $signature     = $request->header('Stripe-Signature', '');
        $payload       = $request->getContent();

        if ($webhookSecret) {
            try {
                $event = \Stripe\Webhook::constructEvent($payload, $signature, $webhookSecret);
            } catch (\Stripe\Exception\SignatureVerificationException $e) {
                Log::warning('Stripe webhook signature mismatch: ' . $e->getMessage());
                return response()->json(['error' => 'Invalid signature'], 401);
            }
        } else {
            $event = json_decode($payload, true);
        }

        $type = is_array($event) ? ($event['type'] ?? '') : $event->type;

        if ($type !== 'checkout.session.completed') {
            return response()->json(['status' => 'ignored']);
        }

        $session   = is_array($event) ? ($event['data']['object'] ?? []) : $event->data->object;
        $intentId  = is_array($session) ? ($session['client_reference_id'] ?? null) : ($session->client_reference_id ?? null);
        $sessionId = is_array($session) ? ($session['id'] ?? null) : ($session->id ?? null);

        if (!$intentId) {
            return response()->json(['error' => 'Missing client_reference_id'], 422);
        }

        $intent = DB::table('payment_intents')->where('id', $intentId)->first();

        if (!$intent || $intent->status === 'completed') {
            return response()->json(['status' => 'already_processed']);
        }

        DB::transaction(function () use ($intent, $sessionId) {
            // Lock the user row and read balance before crediting
            $user = DB::table('users')->where('id', $intent->user_id)->lockForUpdate()->first();
            $balanceBefore = $user ? (float) $user->balance : 0.0;
            $balanceAfter  = $balanceBefore + (float) $intent->amount;

            // Credit the user's balance
            DB::table('users')
                ->where('id', $intent->user_id)
                ->increment('balance', $intent->amount);

            // Record the transaction (matches w_transactions schema)
            DB::table('transactions')->insert([
                'user_id'        => $intent->user_id,
                'direction'      => 'add',
                'amount'         => $intent->amount,
                'balance_before' => $balanceBefore,
                'balance_after'  => $balanceAfter,
                'source'         => 'stripe',
                'note'           => 'Stripe session ' . $sessionId,
                'created_at'     => now(),
                'updated_at'     => now(),
            ]);

            // Mark intent as completed
            DB::table('payment_intents')->where('id', $intent->id)->update([
                'status'     => 'completed',
                'updated_at' => now(),
            ]);
        });

        Log::info("Stripe deposit completed: user_id={$intent->user_id}, amount={$intent->amount}");

        return response()->json(['status' => 'ok']);
    }
}
