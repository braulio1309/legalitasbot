<?php

use App\Http\Controllers\HomeController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\WebhookController;
use Illuminate\Http\Request;

Route::get('/', [HomeController::class, 'index'])->name('home');

// Rutas de suscripción




// Webhook para Stripe (opcional pero recomendado)
//Route::stripeWebhooks('stripe-webhook');
