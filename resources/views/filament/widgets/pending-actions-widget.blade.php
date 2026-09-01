<x-filament-widgets::widget>
    <x-filament::section heading="Actions en attente">
        <div class="space-y-2">
            {{-- Annonces a valider --}}
            <a href="{{ $this->getListingsUrl() }}" class="flex items-center justify-between rounded-lg p-3 transition hover:bg-gray-50 dark:hover:bg-white/5">
                <div class="flex items-center gap-3">
                    <div class="rounded-lg bg-warning-500/10 p-2">
                        <x-heroicon-o-document-text class="h-5 w-5 text-warning-600 dark:text-warning-400" />
                    </div>
                    <span class="font-medium text-gray-700 dark:text-gray-200">Annonces a valider</span>
                </div>
                @if($this->getPendingListingsCount() > 0)
                    <span class="rounded-full bg-warning-500 px-2.5 py-0.5 text-xs font-bold text-white">
                        {{ $this->getPendingListingsCount() }}
                    </span>
                @else
                    <x-heroicon-o-check-circle class="h-5 w-5 text-success-500" />
                @endif
            </a>

            {{-- Paiements a verifier --}}
            <a href="{{ $this->getPaymentsUrl() }}" class="flex items-center justify-between rounded-lg p-3 transition hover:bg-gray-50 dark:hover:bg-white/5">
                <div class="flex items-center gap-3">
                    <div class="rounded-lg bg-danger-500/10 p-2">
                        <x-heroicon-o-credit-card class="h-5 w-5 text-danger-600 dark:text-danger-400" />
                    </div>
                    <span class="font-medium text-gray-700 dark:text-gray-200">Paiements a verifier</span>
                </div>
                @if($this->getPendingPaymentsCount() > 0)
                    <span class="rounded-full bg-danger-500 px-2.5 py-0.5 text-xs font-bold text-white">
                        {{ $this->getPendingPaymentsCount() }}
                    </span>
                @else
                    <x-heroicon-o-check-circle class="h-5 w-5 text-success-500" />
                @endif
            </a>

            {{-- Verifications en attente --}}
            <a href="{{ $this->getVerificationsUrl() }}" class="flex items-center justify-between rounded-lg p-3 transition hover:bg-gray-50 dark:hover:bg-white/5">
                <div class="flex items-center gap-3">
                    <div class="rounded-lg bg-info-500/10 p-2">
                        <x-heroicon-o-shield-check class="h-5 w-5 text-info-600 dark:text-info-400" />
                    </div>
                    <span class="font-medium text-gray-700 dark:text-gray-200">Verifications</span>
                </div>
                @if($this->getPendingVerificationsCount() > 0)
                    <span class="rounded-full bg-info-500 px-2.5 py-0.5 text-xs font-bold text-white">
                        {{ $this->getPendingVerificationsCount() }}
                    </span>
                @else
                    <x-heroicon-o-check-circle class="h-5 w-5 text-success-500" />
                @endif
            </a>

            {{-- Mediations actives --}}
            <a href="{{ $this->getMediationsUrl() }}" class="flex items-center justify-between rounded-lg p-3 transition hover:bg-gray-50 dark:hover:bg-white/5">
                <div class="flex items-center gap-3">
                    <div class="rounded-lg bg-primary-500/10 p-2">
                        <x-heroicon-o-chat-bubble-left-right class="h-5 w-5 text-primary-600 dark:text-primary-400" />
                    </div>
                    <span class="font-medium text-gray-700 dark:text-gray-200">Mediations</span>
                </div>
                @if($this->getActiveMediationsCount() > 0)
                    <span class="rounded-full bg-primary-500 px-2.5 py-0.5 text-xs font-bold text-white">
                        {{ $this->getActiveMediationsCount() }}
                    </span>
                @else
                    <x-heroicon-o-check-circle class="h-5 w-5 text-success-500" />
                @endif
            </a>
        </div>
    </x-filament::section>
</x-filament-widgets::widget>
