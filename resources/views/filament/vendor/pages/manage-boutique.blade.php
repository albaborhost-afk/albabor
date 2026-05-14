<x-filament-panels::page>
    <form wire:submit="save">
        {{ $this->form }}

        <div class="mt-6 flex items-center gap-3">
            <x-filament::button type="submit" icon="heroicon-o-check">
                Enregistrer les modifications
            </x-filament::button>

            @if ($profile)
                <x-filament::button
                    tag="a"
                    color="gray"
                    icon="heroicon-o-arrow-top-right-on-square"
                    :href="route('boutiques.show', $profile)"
                    target="_blank"
                >
                    Voir ma boutique
                </x-filament::button>
            @endif
        </div>
    </form>
</x-filament-panels::page>
