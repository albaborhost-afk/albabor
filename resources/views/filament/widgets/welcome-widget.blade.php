<x-filament-widgets::widget>
    <div class="relative overflow-hidden rounded-xl bg-gradient-to-r from-blue-600 to-cyan-500 p-6 shadow-lg">
        {{-- Content --}}
        <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
            <div class="space-y-1">
                <h2 class="text-2xl font-bold text-white">
                    {{ $this->getGreeting() }}, {{ $this->getUserName() }} !
                </h2>
                <p class="text-sm text-white/80">
                    {{ $this->getTodayDate() }}
                </p>
            </div>

            <div class="flex flex-wrap gap-2">
                <a href="{{ route('filament.admin.resources.listings.index') }}"
                   class="inline-flex items-center gap-2 rounded-lg bg-white/20 px-4 py-2 text-sm font-medium text-white transition hover:bg-white/30">
                    <x-heroicon-o-document-text class="h-4 w-4" />
                    Annonces
                </a>
                <a href="{{ route('filament.admin.resources.payments.index') }}"
                   class="inline-flex items-center gap-2 rounded-lg bg-white px-4 py-2 text-sm font-medium text-blue-600 transition hover:bg-blue-50">
                    <x-heroicon-o-credit-card class="h-4 w-4" />
                    Paiements
                </a>
            </div>
        </div>
    </div>
</x-filament-widgets::widget>
