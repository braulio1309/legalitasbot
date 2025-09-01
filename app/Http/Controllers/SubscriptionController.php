<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Laravel\Cashier\Cashier;
use Stripe\Checkout\Session;
use Stripe\Stripe;

class SubscriptionController extends Controller
{
    public function checkout($plan)
    {
        $user = auth()->user();
        $planPrices = [
            'premium' => env('STRIPE_PREMIUM_PRICE_ID'),
            'professional' => env('STRIPE_PROFESSIONAL_PRICE_ID')
        ];

        if (!isset($planPrices[$plan])) {
            return redirect()->back()->with('error', 'Plan no válido');
        }

        Stripe::setApiKey(env('STRIPE_SECRET'));

        try {
            $session = Session::create([
                'payment_method_types' => ['card'],
                'line_items' => [[
                    'price' => $planPrices[$plan],
                    'quantity' => 1,
                ]],
                'mode' => 'subscription',
                'success_url' => route('subscription.success') . '?session_id={CHECKOUT_SESSION_ID}',
                'cancel_url' => route('subscription.cancel'),
                'customer_email' => $user->email,
                'client_reference_id' => $user->id,
                'metadata' => [
                    'plan' => $plan,
                    'user_id' => $user->id
                ]
            ]);

            return redirect($session->url);
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error al crear la sesión de pago');
        }
    }

    public function success(Request $request)
    {
        // Aquí procesas el éxito del pago
        return view('subscription.success');
    }

    public function cancel()
    {
        // Aquí manejas la cancelación
        return view('subscription.cancel');
    }

    public function handleWebhook(Request $request)
    {
        // Lógica para webhooks de Stripe
    }
}