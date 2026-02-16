<x-filament-panels::page>
    <form wire:submit="save">
        <x-filament::section>
            <x-slot name="heading">
                Taux de change EUR / DZD
            </x-slot>

            <x-slot name="description">
                Definir le taux de conversion entre Euro et Dinar Algerien
            </x-slot>

            <div class="space-y-4">
                <div>
                    <x-filament::input.wrapper>
                        <x-filament::input
                            type="number"
                            wire:model="exchange_rate_eur_dzd"
                            step="0.01"
                            min="0.01"
                            placeholder="285.00"
                        />
                    </x-filament::input.wrapper>
                    <p class="text-xs text-gray-500 mt-2">
                        Taux actuel: {{ number_format((float)$exchange_rate_eur_dzd, 2, ',', ' ') }} DZD pour 1 EUR
                    </p>
                </div>
            </div>
        </x-filament::section>

        <div class="mt-6">
            <x-filament::button type="submit">
                Enregistrer
            </x-filament::button>
        </div>
    </form>
</x-filament-panels::page>
