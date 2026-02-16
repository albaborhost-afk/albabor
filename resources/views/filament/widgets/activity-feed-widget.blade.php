<x-filament-widgets::widget>
    <x-filament::section heading="Activite recente">
        <div class="space-y-3">
            @foreach($this->getRecentActivities() as $activity)
                <div class="flex items-center gap-3">
                    <div class="rounded-lg p-2
                        @if($activity['color'] === 'blue') bg-blue-500/10
                        @elseif($activity['color'] === 'purple') bg-purple-500/10
                        @elseif($activity['color'] === 'green') bg-green-500/10
                        @elseif($activity['color'] === 'amber') bg-amber-500/10
                        @elseif($activity['color'] === 'red') bg-red-500/10
                        @else bg-gray-500/10
                        @endif">
                        @svg($activity['icon'], 'h-4 w-4 ' . match($activity['color']) {
                            'blue' => 'text-blue-600 dark:text-blue-400',
                            'purple' => 'text-purple-600 dark:text-purple-400',
                            'green' => 'text-green-600 dark:text-green-400',
                            'amber' => 'text-amber-600 dark:text-amber-400',
                            'red' => 'text-red-600 dark:text-red-400',
                            default => 'text-gray-600 dark:text-gray-400',
                        })
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-medium text-gray-900 dark:text-white truncate">
                            {{ $activity['description'] }}
                        </p>
                        <p class="text-xs text-gray-500">
                            {{ $activity['time']->locale('fr')->diffForHumans() }}
                        </p>
                    </div>
                </div>
            @endforeach
        </div>
    </x-filament::section>
</x-filament-widgets::widget>
