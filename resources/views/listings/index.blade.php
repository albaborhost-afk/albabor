@php
    $pageCatNames = ['boat' => 'Bateaux', 'jetski' => 'Jet-Skis', 'engine' => 'Moteurs', 'parts' => 'Pièces détachées'];
    $pageTitle = request('category') ? ($pageCatNames[request('category')] ?? 'Annonces') : 'Toutes les Annonces';
@endphp
<x-app-layout
    :title="$pageTitle"
    description="Parcourez toutes les annonces de bateaux, jet-skis, moteurs et pièces détachées en Algérie. Filtrez par wilaya, état et prix."
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
                <span style="color: #1B2A4A;" class="font-medium">{{ __('Annonces') }}</span>
            </nav>
        </div>
    </div>

    <!-- Page Header -->
    <div class="relative overflow-hidden" style="background: linear-gradient(135deg, #1B4F72 0%, #2471A3 50%, #17A2B8 100%);">
        <!-- Decorative -->
        <div class="absolute inset-0 overflow-hidden pointer-events-none">
            <div class="absolute -top-10 -right-10 w-60 h-60 rounded-full" style="background: rgba(255,255,255,0.05); filter: blur(40px);"></div>
            <div class="absolute -bottom-10 -left-10 w-40 h-40 rounded-full" style="background: rgba(255,255,255,0.03); filter: blur(30px);"></div>
        </div>
        <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-10 pb-8">
            <div class="flex flex-col lg:flex-row lg:items-end lg:justify-between">
                <div>
                    <h1 class="text-2xl lg:text-3xl font-extrabold tracking-tight text-white">
                        @if(request('category'))
                            @php
                                $catNames = ['boat' => __('Bateaux'), 'jetski' => __('Jet-Skis'), 'engine' => __('Moteurs'), 'parts' => __('Pieces detachees')];
                            @endphp
                            {{ $catNames[request('category')] ?? __('Annonces') }}
                        @else
                            {{ __('Toutes les Annonces') }}
                        @endif
                    </h1>
                    <p class="mt-2 text-sm" style="color: rgba(255,255,255,0.7);">
                        {{ __('Explorez notre collection de bateaux, jet-skis, moteurs et accessoires nautiques') }}
                    </p>
                </div>

                @auth
                    <a href="{{ route('listings.create') }}"
                       class="mt-5 lg:mt-0 inline-flex items-center px-5 py-2.5 bg-white rounded-xl font-bold transition-all duration-300 transform hover:-translate-y-0.5"
                       style="color: #1B4F72; box-shadow: 0 8px 25px rgba(0,0,0,0.15);">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                        {{ __('Publier une annonce') }}
                    </a>
                @endauth
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div style="background: #F0F4F8;" class="pb-16">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <x-listing-filters />
            <div>
                <!-- Listings Content -->
                <main class="flex-1 min-w-0">
                    <!-- Results Header -->
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
                        <!-- Results Count -->
                        <div class="flex items-center gap-3">
                            <div class="inline-flex items-center px-4 py-2 rounded-full bg-white" style="box-shadow: 0 4px 12px rgba(0,0,0,0.04);">
                                <span class="text-2xl font-bold gradient-text">{{ $listings->total() }}</span>
                                <span class="ml-2 font-medium" style="color: #6B7B8D;">
                                    {{ $listings->total() == 1 ? __('annonce trouvee') : __('annonces trouvees') }}
                                </span>
                            </div>
                            @if(request()->anyFilled(['q', 'category', 'wilaya', 'etat', 'type_offre', 'currency', 'price_min', 'price_max', 'fabricant', 'year_min', 'year_max', 'length_min', 'length_max', 'power_min', 'power_max', 'engine_brand', 'cabins_min', 'berths_min']))
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium" style="background: rgba(23, 162, 184, 0.1); color: #17A2B8;">
                                    {{ __('Filtres actifs') }}
                                </span>
                            @endif
                        </div>

                        <!-- Sort Dropdown -->
                        <div class="flex items-center gap-2">
                            <span class="text-sm font-medium" style="color: #9BA8B7;">{{ __('Trier par:') }}</span>
                            <div class="relative">
                                <select name="sort"
                                        form="filterForm"
                                        class="appearance-none glass-input pl-4 pr-10 py-2.5 rounded-xl focus:outline-none transition-all duration-200 cursor-pointer text-sm font-medium"
                                        onchange="document.getElementById('filterForm').submit()">
                                    <option value="recent" {{ request('sort') == 'recent' || !request('sort') ? 'selected' : '' }}>{{ __('Plus recentes') }}</option>
                                    <option value="price_asc" {{ request('sort') == 'price_asc' ? 'selected' : '' }}>{{ __('Prix croissant') }}</option>
                                    <option value="price_desc" {{ request('sort') == 'price_desc' ? 'selected' : '' }}>{{ __('Prix decroissant') }}</option>
                                    <option value="views" {{ request('sort') == 'views' ? 'selected' : '' }}>{{ __('Plus populaires') }}</option>
                                </select>
                                <svg class="absolute right-3 top-1/2 transform -translate-y-1/2 w-4 h-4 pointer-events-none" style="color: #9BA8B7;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                </svg>
                            </div>
                        </div>
                    </div>

                    <!-- Listings Grid -->
                    @if($listings->count() > 0)
                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-5">
                            @foreach($listings as $listing)
                                <div class="animate-fade-in-up opacity-0" style="animation-delay: {{ $loop->index * 0.05 }}s">
                                    <x-listing-card :listing="$listing" />
                                </div>
                            @endforeach
                        </div>

                        <!-- Pagination -->
                        @if($listings->hasPages())
                            <div class="mt-12 flex justify-center">
                                <div class="inline-flex items-center gap-1 p-2 rounded-2xl bg-white" style="box-shadow: 0 10px 25px rgba(0,0,0,0.06), 0 3px 8px rgba(0,0,0,0.03);">
                                    {{-- Previous Page Link --}}
                                    @if($listings->onFirstPage())
                                        <span class="p-3 rounded-xl opacity-40 cursor-not-allowed" style="color: #9BA8B7;">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                                            </svg>
                                        </span>
                                    @else
                                        <a href="{{ $listings->previousPageUrl() }}" class="p-3 rounded-xl pagination-btn" style="color: #6B7B8D;">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                                            </svg>
                                        </a>
                                    @endif

                                    {{-- Page Numbers --}}
                                    @php
                                        $start = max(1, $listings->currentPage() - 2);
                                        $end = min($listings->lastPage(), $listings->currentPage() + 2);
                                    @endphp

                                    @if($start > 1)
                                        <a href="{{ $listings->url(1) }}" class="min-w-[44px] h-11 flex items-center justify-center rounded-xl font-medium pagination-btn" style="color: #6B7B8D;">1</a>
                                        @if($start > 2)
                                            <span class="px-2" style="color: #9BA8B7;">...</span>
                                        @endif
                                    @endif

                                    @for($i = $start; $i <= $end; $i++)
                                        @if($i == $listings->currentPage())
                                            <span class="min-w-[44px] h-11 flex items-center justify-center rounded-xl font-bold text-white gradient-primary" style="box-shadow: 0 4px 12px rgba(27, 79, 114, 0.25);">
                                                {{ $i }}
                                            </span>
                                        @else
                                            <a href="{{ $listings->url($i) }}" class="min-w-[44px] h-11 flex items-center justify-center rounded-xl font-medium pagination-btn" style="color: #6B7B8D;">
                                                {{ $i }}
                                            </a>
                                        @endif
                                    @endfor

                                    @if($end < $listings->lastPage())
                                        @if($end < $listings->lastPage() - 1)
                                            <span class="px-2" style="color: #9BA8B7;">...</span>
                                        @endif
                                        <a href="{{ $listings->url($listings->lastPage()) }}" class="min-w-[44px] h-11 flex items-center justify-center rounded-xl font-medium pagination-btn" style="color: #6B7B8D;">
                                            {{ $listings->lastPage() }}
                                        </a>
                                    @endif

                                    {{-- Next Page Link --}}
                                    @if($listings->hasMorePages())
                                        <a href="{{ $listings->nextPageUrl() }}" class="p-3 rounded-xl pagination-btn" style="color: #6B7B8D;">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                            </svg>
                                        </a>
                                    @else
                                        <span class="p-3 rounded-xl opacity-40 cursor-not-allowed" style="color: #9BA8B7;">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                            </svg>
                                        </span>
                                    @endif
                                </div>
                            </div>

                            <!-- Page Info -->
                            <p class="text-center mt-4 text-sm" style="color: #9BA8B7;">
                                {{ __('Affichage de') }} {{ $listings->firstItem() }} {{ __('a') }} {{ $listings->lastItem() }} {{ __('sur') }} {{ $listings->total() }} {{ __('resultats') }}
                            </p>
                        @endif

                    @else
                        <!-- Empty State -->
                        <div class="bg-white rounded-2xl p-12 text-center" style="box-shadow: 0 10px 25px rgba(0,0,0,0.06), 0 3px 8px rgba(0,0,0,0.03);">
                            <!-- Illustration -->
                            <div class="relative w-40 h-40 mx-auto mb-8 empty-state-float">
                                <div class="absolute inset-0 rounded-full empty-state-pulse" style="background: rgba(23, 162, 184, 0.08);"></div>
                                <div class="absolute inset-4 rounded-full" style="background: rgba(23, 162, 184, 0.12);"></div>
                                <div class="absolute inset-0 flex items-center justify-center">
                                    <svg class="w-20 h-20" style="color: #17A2B8;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                    </svg>
                                </div>
                            </div>

                            <h3 class="text-2xl font-bold mb-3" style="color: #1B2A4A;">
                                {{ __('Aucune annonce trouvee') }}
                            </h3>
                            <p class="text-lg mb-8 max-w-md mx-auto" style="color: #6B7B8D;">
                                {{ __("Nous n'avons pas trouve d'annonces correspondant a vos criteres de recherche. Essayez de modifier vos filtres.") }}
                            </p>

                            <div class="flex flex-col sm:flex-row gap-4 justify-center">
                                <a href="{{ route('listings.index') }}"
                                   class="inline-flex items-center justify-center px-6 py-3 rounded-xl font-bold text-white transition-all duration-300 transform hover:-translate-y-1 gradient-primary"
                                   style="box-shadow: 0 8px 25px rgba(27, 79, 114, 0.25);">
                                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                                    </svg>
                                    {{ __('Reinitialiser les filtres') }}
                                </a>
                                @auth
                                    <a href="{{ route('listings.create') }}"
                                       class="inline-flex items-center justify-center px-6 py-3 rounded-xl font-bold transition-all duration-300"
                                       style="border: 1px solid rgba(23, 162, 184, 0.4); color: #17A2B8;">
                                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                                        </svg>
                                        {{ __('Publier une annonce') }}
                                    </a>
                                @endauth
                            </div>
                        </div>
                    @endif
                </main>
            </div>
        </div>
    </div>


</x-app-layout>
