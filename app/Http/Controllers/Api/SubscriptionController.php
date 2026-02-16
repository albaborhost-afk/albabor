<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Plan;
use App\Models\Subscription;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SubscriptionController extends Controller
{
    public function plans(): JsonResponse
    {
        $plans = Plan::active()->orderBy('price_dzd')->get();

        return response()->json($plans);
    }

    public function index(Request $request): JsonResponse
    {
        $subscriptions = $request->user()
            ->subscriptions()
            ->with('plan')
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($subscriptions);
    }

    public function active(Request $request): JsonResponse
    {
        $user = $request->user();
        $subscription = $user->activeSubscription?->load('plan');

        if (! $subscription) {
            return response()->json([
                'message' => 'Aucun abonnement actif.',
                'subscription' => null,
            ]);
        }

        return response()->json([
            'message' => 'Abonnement actif trouvé.',
            'subscription' => $subscription,
        ]);
    }
}
