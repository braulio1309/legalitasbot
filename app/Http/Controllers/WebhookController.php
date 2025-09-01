<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class WebhookController extends Controller
{
    public function handle(Request $request)
    {
        $payload = $request->all();

        $subscription = $payload['data']['object'] ?? null;

        if (!$subscription) {
            return;
        }

        // Buscar al usuario por stripe_id
        $user = \App\Models\User::where('stripe_id', $subscription['customer'])->first();

        if ($user) {
            $user->status = $subscription['status'] ?? 'unknown'; // active, past_due, etc.
            $user->subscription_renewed_at = now();
            $subscription = $user->subscriptions()->latest('created_at')->first(); // Cashier obtiene la suscripción

            if ($subscription) {
                $subscription->stripe_status = $subscription['status']; 
                $subscription->save();
            }

            if($subscription['status'] != 'active') {
                $user->plan = 'free';
                $user->queries_this_month = 0;
            }
            $user->save();

        } 

        // 5. Responder con 200 OK al servicio
        return response()->json(['status' => 'success'], 200);
    }
}
