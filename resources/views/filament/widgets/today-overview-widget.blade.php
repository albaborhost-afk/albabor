<x-filament-widgets::widget>
    <x-filament::section heading="Aujourd'hui">
        @php
            $stats = $this->getTodayStats();
        @endphp

        <div class="space-y-4">
            {{-- Today's Revenue --}}
            <div class="rounded-lg bg-success-50 p-4 dark:bg-success-500/10">
                <p class="text-xs text-gray-500 dark:text-gray-400">Revenus</p>
                <p class="text-2xl font-bold text-success-600 dark:text-success-400">
                    {{ number_format($stats['todayRevenue']) }} DA
                </p>
            </div>

            {{-- Stats --}}
            <div class="grid grid-cols-2 gap-3">
                <div class="text-center">
                    <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ $stats['newUsers'] }}</p>
                    <p class="text-xs text-gray-500">Utilisateurs</p>
                </div>
                <div class="text-center">
                    <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ $stats['newListings'] }}</p>
                    <p class="text-xs text-gray-500">Annonces</p>
                </div>
                <div class="text-center">
                    <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ $stats['newPayments'] }}</p>
                    <p class="text-xs text-gray-500">Paiements</p>
                </div>
                <div class="text-center">
                    <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ $stats['approvedPayments'] }}</p>
                    <p class="text-xs text-gray-500">Approuves</p>
                </div>
            </div>
        </div>
    </x-filament::section>
</x-filament-widgets::widget>
