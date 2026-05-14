<x-app-layout
    title="Nos boutiques"
    description="Decouvrez les boutiques professionnelles de moteurs et pieces detachees nautiques en Algerie."
>
    <!-- Breadcrumb Bar -->
    <div style="background: #FFFFFF; border-bottom: 1px solid #E0E6ED;">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4">
            <nav class="flex items-center space-x-2 text-sm">
                <a href="{{ route('home') }}" style="color: #9BA8B7;" class="hover:opacity-80 transition-opacity flex items-center">
                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                    </svg>
                    {{ __('Accueil') }}
                </a>
                <svg class="w-4 h-4" style="color: #E0E6ED;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                </svg>
                <span style="color: #1B2A4A;" class="font-medium">{{ __('Nos boutiques') }}</span>
            </nav>
        </div>
    </div>

    <!-- Page Header -->
    <div class="relative overflow-hidden" style="background: linear-gradient(135deg, #1B4F72 0%, #2471A3 50%, #17A2B8 100%);">
        <div class="absolute inset-0 overflow-hidden pointer-events-none">
            <div class="absolute -top-16 -right-16 w-64 h-64 rounded-full" style="background: rgba(243,156,18,0.18);"></div>
            <div class="absolute -bottom-20 -left-10 w-72 h-72 rounded-full" style="background: rgba(255,255,255,0.08);"></div>
        </div>
        <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12 sm:py-16">
            <h1 class="text-2xl sm:text-4xl font-extrabold text-white mb-3" style="letter-spacing: -0.02em;">
                {{ __('Nos boutiques') }}
            </h1>
            <p class="text-sm sm:text-base max-w-xl" style="color: rgba(255,255,255,0.78);">
                {{ __('Les vendeurs professionnels de moteurs et pieces detachees nautiques en Algerie.') }}
            </p>
        </div>
    </div>

    <!-- Boutiques Grid -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
        @if($boutiques->count())
            <p class="text-sm mb-6" style="color: #6B7B8D;">
                {{ trans_choice('{1}:count boutique disponible|[2,*]:count boutiques disponibles', $boutiques->total(), ['count' => $boutiques->total()]) }}
            </p>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-5">
                @foreach($boutiques as $boutique)
                    <a href="{{ route('boutiques.show', $boutique) }}"
                       class="group bg-white rounded-2xl overflow-hidden shadow-sm hover:shadow-md transition-shadow duration-300 flex flex-col">
                        {{-- Logo --}}
                        <div class="relative flex items-center justify-center" style="aspect-ratio: 16/9; background: linear-gradient(135deg, #17A2B8 0%, #2471A3 100%);">
                            @if($boutique->logo_url)
                                <img src="{{ $boutique->logo_url }}"
                                     alt="{{ $boutique->shop_name }}"
                                     class="w-20 h-20 rounded-2xl object-cover bg-white shadow-md"
                                     loading="lazy"
                                     onerror="this.onerror=null;this.style.display='none';this.nextElementSibling.style.display='flex'">
                                <div class="w-20 h-20 rounded-2xl bg-white shadow-md items-center justify-center" style="display:none;">
                                    <span class="text-2xl font-extrabold" style="color: #2471A3;">{{ Str::upper(Str::substr($boutique->shop_name, 0, 1)) }}</span>
                                </div>
                            @else
                                <div class="w-20 h-20 rounded-2xl bg-white shadow-md flex items-center justify-center">
                                    <span class="text-2xl font-extrabold" style="color: #2471A3;">{{ Str::upper(Str::substr($boutique->shop_name, 0, 1)) }}</span>
                                </div>
                            @endif
                        </div>

                        {{-- Info --}}
                        <div class="px-4 pt-3 pb-4 flex flex-col flex-1">
                            <h3 class="text-sm font-bold line-clamp-1 group-hover:text-[#2471A3] transition-colors duration-200" style="color: #1B2A4A;">
                                {{ $boutique->shop_name }}
                            </h3>

                            @if($boutique->wilaya)
                                <div class="flex items-center gap-1 mt-1.5">
                                    <svg class="w-3.5 h-3.5 flex-shrink-0" style="color: #6B7B8D;" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    </svg>
                                    <span class="text-xs truncate" style="color: #6B7B8D;">{{ $boutique->wilaya }}</span>
                                </div>
                            @endif

                            <div class="mt-auto pt-3">
                                <span class="inline-flex items-center gap-1.5 text-xs font-semibold px-2.5 py-1 rounded-full" style="background: #F0F4F8; color: #1B2A4A;">
                                    <svg class="w-3.5 h-3.5" style="color: #17A2B8;" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                                    </svg>
                                    {{ trans_choice('{0}Aucune annonce|{1}:count annonce|[2,*]:count annonces', $boutique->listings_count, ['count' => $boutique->listings_count]) }}
                                </span>
                            </div>
                        </div>
                    </a>
                @endforeach
            </div>

            <div class="mt-10">
                {{ $boutiques->links() }}
            </div>
        @else
            <div class="bg-white rounded-2xl shadow-sm py-20 px-6 text-center">
                <div class="w-16 h-16 mx-auto rounded-full flex items-center justify-center mb-4" style="background: #F0F4F8;">
                    <svg class="w-8 h-8" style="color: #9BA8B7;" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/>
                    </svg>
                </div>
                <h3 class="text-base font-bold mb-1" style="color: #1B2A4A;">{{ __('Aucune boutique pour le moment') }}</h3>
                <p class="text-sm" style="color: #6B7B8D;">{{ __('Revenez bientot pour decouvrir nos vendeurs professionnels.') }}</p>
            </div>
        @endif
    </div>
</x-app-layout>
