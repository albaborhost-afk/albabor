<x-filament-widgets::widget>
    <div class="relative overflow-hidden rounded-xl bg-gradient-to-r from-cyan-600 to-teal-500 p-6 shadow-lg">
        <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
            <div class="space-y-1">
                <h2 class="text-2xl font-bold text-white">
                    {{ $this->getGreeting() }}, {{ $this->getShopName() }} !
                </h2>
                <p class="text-sm text-white/80">
                    {{ $this->getTodayDate() }}
                </p>
            </div>

            <div class="flex flex-wrap gap-2">
                <a href="{{ $this->getBoutiqueUrl() }}"
                   class="inline-flex items-center gap-2 rounded-lg bg-white px-4 py-2 text-sm font-medium text-teal-600 transition hover:bg-teal-50">
                    <x-heroicon-o-building-storefront class="h-4 w-4" />
                    Gérer ma boutique
                </a>
            </div>
        </div>
    </div>
</x-filament-widgets::widget>
