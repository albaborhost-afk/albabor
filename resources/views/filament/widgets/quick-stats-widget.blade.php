<x-filament-widgets::widget>
    <div class="grid grid-cols-2 gap-4 md:grid-cols-3 lg:grid-cols-6">
        @foreach($this->getStats() as $stat)
            <x-filament::section class="!p-4">
                <div class="flex items-center gap-3">
                    <div class="rounded-lg bg-{{ $stat['color'] }}-500/10 p-2 text-{{ $stat['color'] }}-600 dark:text-{{ $stat['color'] }}-400">
                        @svg($stat['icon'], 'h-5 w-5')
                    </div>
                    <div>
                        <p class="text-2xl font-bold text-gray-950 dark:text-white">
                            {{ $stat['value'] }}
                        </p>
                        <p class="text-xs text-gray-500 dark:text-gray-400">
                            {{ $stat['label'] }}
                        </p>
                    </div>
                </div>
            </x-filament::section>
        @endforeach
    </div>
</x-filament-widgets::widget>
