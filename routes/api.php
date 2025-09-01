<?php

use App\Http\Controllers\WebhookController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use Illuminate\Support\Facades\Log;
use Stripe\Webhook;

    Route::post('webhook/subscription', [WebhookController::class, 'handle'])->name('webhook');

