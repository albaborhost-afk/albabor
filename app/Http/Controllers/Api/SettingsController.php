<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\JsonResponse;

class SettingsController extends Controller
{
    public function exchangeRate(): JsonResponse
    {
        return response()->json([
            'exchange_rate_eur_dzd' => Setting::getExchangeRate(),
        ]);
    }
}
