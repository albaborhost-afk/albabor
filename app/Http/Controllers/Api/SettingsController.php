<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\JsonResponse;

class SettingsController extends Controller
{
    public function paymentMethods(): JsonResponse
    {
        $methods = collect(config('payment_methods.methods'))
            ->except('card')->map(fn ($method, $key) => ['key' => $key] + $method)->values();

        return response()->json(['holder' => config('payment_methods.holder'), 'methods' => $methods]);
    }

    public function exchangeRate(): JsonResponse
    {
        return response()->json([
            'exchange_rate_eur_dzd' => Setting::getExchangeRate(),
        ]);
    }
}
