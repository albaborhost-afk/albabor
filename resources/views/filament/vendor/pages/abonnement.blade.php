@php
    use Carbon\Carbon;

    $user = auth()->user();
    $activeSubscription = $user?->activeSubscription;
    $plans = \App\Models\Plan::active()->orderBy('price_dzd')->get();
    $payments = $user
        ? $user->payments()->where('type', 'vendor_subscription')->latest()->get()
        : collect();

    $statusColors = [
        'pending' => 'warning',
        'approved' => 'success',
        'rejected' => 'danger',
    ];
@endphp

<x-filament-panels::page>
    <div class="space-y-6">

        {{-- Statut de l'abonnement --}}
        <x-filament::section icon="heroicon-o-shield-check">
            <x-slot name="heading">Statut de mon abonnement</x-slot>
            <x-slot name="description">L'état actuel de votre abonnement vendeur.</x-slot>

            @if ($activeSubscription)
                @php
                    $endsAt = $activeSubscription->ends_at;
                    $daysLeft = $endsAt ? max(0, Carbon::now()->diffInDays($endsAt, false)) : null;
                @endphp
                <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                    <div class="space-y-1">
                        <div class="flex items-center gap-2">
                            <span class="text-lg font-semibold text-gray-950 dark:text-white">
                                {{ $activeSubscription->plan?->name ?? 'Plan' }}
                            </span>
                            <x-filament::badge color="success">Actif</x-filament::badge>
                        </div>
                        <p class="text-sm text-gray-500 dark:text-gray-400">
                            Date de fin :
                            <span class="font-medium text-gray-700 dark:text-gray-300">
                                {{ $endsAt ? $endsAt->format('d/m/Y') : '—' }}
                            </span>
                        </p>
                        @if (! is_null($daysLeft))
                            <p class="text-sm text-gray-500 dark:text-gray-400">
                                Jours restants :
                                <span class="font-medium text-gray-700 dark:text-gray-300">
                                    {{ (int) $daysLeft }} jour{{ (int) $daysLeft > 1 ? 's' : '' }}
                                </span>
                            </p>
                        @endif
                    </div>
                </div>
            @else
                <div class="flex items-center gap-3">
                    <x-filament::badge color="gray" size="lg">Aucun abonnement actif</x-filament::badge>
                    <p class="text-sm text-gray-500 dark:text-gray-400">
                        Choisissez un plan ci-dessous pour activer votre abonnement vendeur.
                    </p>
                </div>
            @endif
        </x-filament::section>

        {{-- Plans disponibles --}}
        <x-filament::section icon="heroicon-o-rectangle-stack">
            <x-slot name="heading">Plans disponibles</x-slot>
            <x-slot name="description">Les formules d'abonnement proposées par AlBabor.</x-slot>

            @if ($plans->isEmpty())
                <p class="text-sm text-gray-500 dark:text-gray-400">Aucun plan disponible pour le moment.</p>
            @else
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach ($plans as $plan)
                        <div class="rounded-xl border border-gray-200 bg-white p-5 dark:border-white/10 dark:bg-white/5">
                            <div class="space-y-3">
                                <h3 class="text-base font-semibold text-gray-950 dark:text-white">
                                    {{ $plan->name }}
                                </h3>
                                @if ($plan->description)
                                    <p class="text-sm text-gray-500 dark:text-gray-400">
                                        {{ $plan->description }}
                                    </p>
                                @endif
                                <div class="flex items-baseline gap-1">
                                    <span class="text-2xl font-bold text-primary-600 dark:text-primary-400">
                                        {{ $plan->formatted_price }}
                                    </span>
                                </div>
                                <x-filament::badge color="gray">
                                    Durée : {{ $plan->duration_days }} jours
                                </x-filament::badge>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </x-filament::section>

        {{-- Formulaire de souscription --}}
        <x-filament::section icon="heroicon-o-arrow-up-circle">
            <x-slot name="heading">S'abonner ou renouveler</x-slot>
            <x-slot name="description">
                Soumettez votre justificatif de paiement pour activer ou prolonger votre abonnement.
            </x-slot>

            <form wire:submit="submitProof" class="space-y-6">
                {{ $this->form }}

                <div class="flex justify-end">
                    <x-filament::button type="submit" icon="heroicon-o-paper-airplane">
                        Envoyer le justificatif
                    </x-filament::button>
                </div>
            </form>
        </x-filament::section>

        {{-- Historique des paiements --}}
        <x-filament::section icon="heroicon-o-clock">
            <x-slot name="heading">Historique des paiements</x-slot>
            <x-slot name="description">Vos paiements d'abonnement vendeur.</x-slot>

            @if ($payments->isEmpty())
                <p class="text-sm text-gray-500 dark:text-gray-400">
                    Aucun paiement d'abonnement enregistré pour le moment.
                </p>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="border-b border-gray-200 text-left dark:border-white/10">
                                <th class="py-2 pr-4 font-medium text-gray-500 dark:text-gray-400">Date</th>
                                <th class="py-2 pr-4 font-medium text-gray-500 dark:text-gray-400">Méthode</th>
                                <th class="py-2 pr-4 font-medium text-gray-500 dark:text-gray-400">Montant</th>
                                <th class="py-2 font-medium text-gray-500 dark:text-gray-400">Statut</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($payments as $payment)
                                <tr class="border-b border-gray-100 last:border-0 dark:border-white/5">
                                    <td class="py-3 pr-4 text-gray-700 dark:text-gray-300">
                                        {{ $payment->created_at?->format('d/m/Y H:i') ?? '—' }}
                                    </td>
                                    <td class="py-3 pr-4 text-gray-700 dark:text-gray-300">
                                        {{ $payment->method_label }}
                                    </td>
                                    <td class="py-3 pr-4 font-medium text-gray-950 dark:text-white">
                                        {{ number_format($payment->amount_dzd, 0, ',', ' ') }} DA
                                    </td>
                                    <td class="py-3">
                                        <x-filament::badge :color="$statusColors[$payment->status] ?? 'gray'">
                                            {{ $payment->status_label }}
                                        </x-filament::badge>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </x-filament::section>

    </div>
</x-filament-panels::page>
