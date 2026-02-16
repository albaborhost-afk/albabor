<x-app-layout>
    <!-- Breadcrumb Bar -->
    <div style="background: #FFFFFF; border-bottom: 1px solid #E0E6ED;">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4">
            <nav class="flex items-center space-x-2 text-sm">
                <a href="{{ route('home') }}" style="color: #9BA8B7;" class="hover:opacity-80 transition-opacity flex items-center">
                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                    </svg>
                    Accueil
                </a>
                <svg class="w-4 h-4" style="color: #E0E6ED;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                </svg>
                <span style="color: #1B2A4A;" class="font-medium">Annonces</span>
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
                                $catNames = ['boat' => 'Bateaux', 'jetski' => 'Jet-Skis', 'engine' => 'Moteurs', 'parts' => 'Pieces detachees'];
                            @endphp
                            {{ $catNames[request('category')] ?? 'Annonces' }}
                        @else
                            Toutes les Annonces
                        @endif
                    </h1>
                    <p class="mt-2 text-sm" style="color: rgba(255,255,255,0.7);">
                        Explorez notre collection de bateaux, jet-skis, moteurs et accessoires nautiques
                    </p>
                </div>

                @auth
                    <a href="{{ route('listings.create') }}"
                       class="mt-5 lg:mt-0 inline-flex items-center px-5 py-2.5 bg-white rounded-xl font-bold transition-all duration-300 transform hover:-translate-y-0.5"
                       style="color: #1B4F72; box-shadow: 0 8px 25px rgba(0,0,0,0.15);">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                        Publier une annonce
                    </a>
                @endauth
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div style="background: #F0F4F8;" class="pb-16">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex flex-col lg:flex-row gap-8">

                <!-- Filters Sidebar (desktop only) -->
                <aside class="hidden lg:block lg:w-80 flex-shrink-0">
                    <div class="lg:sticky lg:top-24">
                        <div class="bg-white rounded-2xl p-6" style="box-shadow: 0 10px 25px rgba(0,0,0,0.06), 0 3px 8px rgba(0,0,0,0.03);">
                            <form action="{{ route('listings.index') }}" method="GET" id="filterForm">
                                <!-- Filters Header -->
                                <div class="flex items-center justify-between mb-6">
                                    <h3 class="text-lg font-bold" style="color: #1B2A4A;">
                                        <svg class="w-5 h-5 inline-block mr-2" style="color: #17A2B8;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" />
                                        </svg>
                                        Filtres
                                    </h3>
                                    @if(request()->anyFilled(['q', 'category', 'wilaya', 'etat', 'type_offre', 'currency', 'price_min', 'price_max']))
                                        <a href="{{ route('listings.index') }}" class="text-sm font-medium transition-colors" style="color: #17A2B8;">
                                            Effacer tout
                                        </a>
                                    @endif
                                </div>

                                <!-- Search Input -->
                                <div class="mb-5">
                                    <label class="block text-sm font-semibold mb-2" style="color: #1B2A4A;">Rechercher</label>
                                    <div class="relative">
                                        <input type="text"
                                               name="q"
                                               value="{{ request('q') }}"
                                               placeholder="Mot-cle, marque, modele..."
                                               class="glass-input search-input-focus w-full pl-10 pr-4 py-3 rounded-xl focus:outline-none transition-all duration-200">
                                        <svg class="absolute left-3 top-1/2 transform -translate-y-1/2 w-5 h-5" style="color: #9BA8B7;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                        </svg>
                                    </div>
                                </div>

                                <!-- Category Filter -->
                                <div class="mb-5">
                                    <label class="block text-sm font-semibold mb-2" style="color: #1B2A4A;">Categorie</label>
                                    <div class="space-y-2">
                                        @php
                                            $categories = [
                                                '' => ['name' => 'Toutes les categories', 'icon' => 'M4 6h16M4 10h16M4 14h16M4 18h16'],
                                                'boat' => ['name' => 'Bateaux', 'icon' => 'M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z'],
                                                'jetski' => ['name' => 'Jet-Skis', 'icon' => 'M13 10V3L4 14h7v7l9-11h-7z'],
                                                'engine' => ['name' => 'Moteurs', 'icon' => 'M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z'],
                                                'parts' => ['name' => 'Pieces detachees', 'icon' => 'M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z']
                                            ];
                                        @endphp
                                        @foreach($categories as $value => $cat)
                                            <label class="flex items-center p-3 rounded-xl cursor-pointer transition-all duration-200 category-filter-label"
                                                   style="{{ request('category') == $value ? 'background: rgba(23, 162, 184, 0.08); border: 1px solid rgba(23, 162, 184, 0.3);' : 'border: 1px solid transparent;' }}">
                                                <input type="radio"
                                                       name="category"
                                                       value="{{ $value }}"
                                                       {{ request('category') == $value ? 'checked' : '' }}
                                                       class="sr-only"
                                                       onchange="document.getElementById('filterForm').submit()">
                                                <svg class="w-5 h-5 mr-3"
                                                     style="color: {{ request('category') == $value ? '#17A2B8' : '#9BA8B7' }};"
                                                     fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $cat['icon'] }}" />
                                                </svg>
                                                <span class="font-medium" style="color: {{ request('category') == $value ? '#17A2B8' : '#6B7B8D' }};">
                                                    {{ $cat['name'] }}
                                                </span>
                                            </label>
                                        @endforeach
                                    </div>
                                </div>

                                <!-- Divider -->
                                <div style="border-top: 1px solid #E0E6ED;" class="my-5"></div>

                                <!-- Wilaya Dropdown -->
                                <div class="mb-5">
                                    <label class="block text-sm font-semibold mb-2" style="color: #1B2A4A;">Wilaya</label>
                                    <div class="relative">
                                        <select name="wilaya"
                                                class="glass-input w-full py-3 pl-4 pr-10 rounded-xl focus:outline-none transition-all duration-200 appearance-none cursor-pointer"
                                                onchange="document.getElementById('filterForm').submit()">
                                            <option value="">Toutes les wilayas</option>
                                            @foreach($wilayas as $code => $name)
                                                <option value="{{ $code }}" {{ request('wilaya') == $code ? 'selected' : '' }}>
                                                    {{ $code }} - {{ $name }}
                                                </option>
                                            @endforeach
                                        </select>
                                        <svg class="absolute right-3 top-1/2 transform -translate-y-1/2 w-5 h-5 pointer-events-none" style="color: #9BA8B7;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                        </svg>
                                    </div>
                                </div>

                                <!-- Currency Filter -->
                                <div class="mb-5">
                                    <label class="block text-sm font-semibold mb-2" style="color: #1B2A4A;">Devise</label>
                                    <div class="flex gap-2">
                                        @php $currencyFilter = request('currency'); @endphp
                                        <label class="flex-1 text-center p-3 rounded-xl cursor-pointer transition-all duration-200"
                                               style="{{ !$currencyFilter ? 'background: rgba(23, 162, 184, 0.08); border: 1px solid rgba(23, 162, 184, 0.3);' : 'border: 1px solid #E0E6ED;' }}">
                                            <input type="radio" name="currency" value="" {{ !$currencyFilter ? 'checked' : '' }} class="sr-only" onchange="document.getElementById('filterForm').submit()">
                                            <span class="text-sm font-medium" style="color: {{ !$currencyFilter ? '#17A2B8' : '#6B7B8D' }};">Toutes</span>
                                        </label>
                                        <label class="flex-1 text-center p-3 rounded-xl cursor-pointer transition-all duration-200"
                                               style="{{ $currencyFilter == 'DZD' ? 'background: rgba(23, 162, 184, 0.08); border: 1px solid rgba(23, 162, 184, 0.3);' : 'border: 1px solid #E0E6ED;' }}">
                                            <input type="radio" name="currency" value="DZD" {{ $currencyFilter == 'DZD' ? 'checked' : '' }} class="sr-only" onchange="document.getElementById('filterForm').submit()">
                                            <span class="text-sm font-medium" style="color: {{ $currencyFilter == 'DZD' ? '#17A2B8' : '#6B7B8D' }};">DZD</span>
                                        </label>
                                        <label class="flex-1 text-center p-3 rounded-xl cursor-pointer transition-all duration-200"
                                               style="{{ $currencyFilter == 'EUR' ? 'background: rgba(23, 162, 184, 0.08); border: 1px solid rgba(23, 162, 184, 0.3);' : 'border: 1px solid #E0E6ED;' }}">
                                            <input type="radio" name="currency" value="EUR" {{ $currencyFilter == 'EUR' ? 'checked' : '' }} class="sr-only" onchange="document.getElementById('filterForm').submit()">
                                            <span class="text-sm font-medium" style="color: {{ $currencyFilter == 'EUR' ? '#17A2B8' : '#6B7B8D' }};">EUR</span>
                                        </label>
                                    </div>
                                </div>

                                <!-- Divider -->
                                <div style="border-top: 1px solid #E0E6ED;" class="my-5"></div>

                                <!-- Price Range -->
                                <div class="mb-5">
                                    <label class="block text-sm font-semibold mb-2" style="color: #1B2A4A;">Fourchette de prix</label>
                                    <div class="grid grid-cols-2 gap-3">
                                        <input type="number"
                                               name="price_min"
                                               value="{{ request('price_min') }}"
                                               placeholder="Min"
                                               class="glass-input w-full py-3 px-4 rounded-xl focus:outline-none transition-all duration-200 text-sm">
                                        <input type="number"
                                               name="price_max"
                                               value="{{ request('price_max') }}"
                                               placeholder="Max"
                                               class="glass-input w-full py-3 px-4 rounded-xl focus:outline-none transition-all duration-200 text-sm">
                                    </div>
                                </div>

                                <!-- Etat (5-level condition) -->
                                <div class="mb-5">
                                    <label class="block text-sm font-semibold mb-2" style="color: #1B2A4A;">Etat</label>
                                    <div class="space-y-2">
                                        @php
                                            $etatOptions = [
                                                '' => 'Tous',
                                                'jamais_utilise' => 'Jamais utilise',
                                                'comme_neuf' => 'Comme neuf',
                                                'bon_etat' => 'Bon etat',
                                                'etat_moyen' => 'Etat moyen',
                                                'a_reviser' => 'A reviser',
                                            ];
                                        @endphp
                                        <div class="flex flex-wrap gap-2">
                                            @foreach($etatOptions as $value => $label)
                                                <label class="px-3 py-2 rounded-xl cursor-pointer transition-all duration-200 text-sm font-medium"
                                                       style="{{ request('etat') == $value || (!request('etat') && $value === '') ? 'background: rgba(23, 162, 184, 0.08); border: 1px solid rgba(23, 162, 184, 0.3); color: #17A2B8;' : 'border: 1px solid #E0E6ED; color: #6B7B8D;' }}">
                                                    <input type="radio" name="etat" value="{{ $value }}"
                                                           {{ request('etat') == $value || (!request('etat') && $value === '') ? 'checked' : '' }}
                                                           class="sr-only"
                                                           onchange="document.getElementById('filterForm').submit()">
                                                    {{ $label }}
                                                </label>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>

                                <!-- Type d'offre -->
                                <div class="mb-6">
                                    <label class="block text-sm font-semibold mb-2" style="color: #1B2A4A;">Type d'offre</label>
                                    <div class="flex flex-wrap gap-2">
                                        @php
                                            $typeOffreOptions = [
                                                '' => 'Tous',
                                                'negociable' => 'Negociable',
                                                'fix' => 'Prix fixe',
                                                'offert' => 'Offert',
                                            ];
                                        @endphp
                                        @foreach($typeOffreOptions as $value => $label)
                                            <label class="px-3 py-2 rounded-xl cursor-pointer transition-all duration-200 text-sm font-medium"
                                                   style="{{ request('type_offre') == $value || (!request('type_offre') && $value === '') ? 'background: rgba(23, 162, 184, 0.08); border: 1px solid rgba(23, 162, 184, 0.3); color: #17A2B8;' : 'border: 1px solid #E0E6ED; color: #6B7B8D;' }}">
                                                <input type="radio" name="type_offre" value="{{ $value }}"
                                                       {{ request('type_offre') == $value || (!request('type_offre') && $value === '') ? 'checked' : '' }}
                                                       class="sr-only"
                                                       onchange="document.getElementById('filterForm').submit()">
                                                {{ $label }}
                                            </label>
                                        @endforeach
                                    </div>
                                </div>

                                <!-- Apply Button -->
                                <button type="submit"
                                        class="w-full py-3.5 rounded-xl font-bold text-white transition-all duration-300 transform hover:-translate-y-0.5 gradient-primary"
                                        style="box-shadow: 0 8px 25px rgba(27, 79, 114, 0.25);">
                                    <svg class="w-5 h-5 inline-block mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                    </svg>
                                    Appliquer les filtres
                                </button>
                            </form>
                        </div>
                    </div>
                </aside>

                <!-- Listings Content -->
                <main class="flex-1 min-w-0">
                    <!-- Results Header -->
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
                        <!-- Results Count -->
                        <div class="flex items-center gap-3">
                            <div class="inline-flex items-center px-4 py-2 rounded-full bg-white" style="box-shadow: 0 4px 12px rgba(0,0,0,0.04);">
                                <span class="text-2xl font-bold gradient-text">{{ $listings->total() }}</span>
                                <span class="ml-2 font-medium" style="color: #6B7B8D;">
                                    {{ $listings->total() == 1 ? 'annonce trouvee' : 'annonces trouvees' }}
                                </span>
                            </div>
                            @if(request()->anyFilled(['q', 'category', 'wilaya', 'etat', 'type_offre', 'currency', 'price_min', 'price_max']))
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium" style="background: rgba(23, 162, 184, 0.1); color: #17A2B8;">
                                    Filtres actifs
                                </span>
                            @endif
                        </div>

                        <!-- Sort Dropdown -->
                        <div class="flex items-center gap-2">
                            <span class="text-sm font-medium" style="color: #9BA8B7;">Trier par:</span>
                            <div class="relative">
                                <select name="sort"
                                        form="filterForm"
                                        class="appearance-none glass-input pl-4 pr-10 py-2.5 rounded-xl focus:outline-none transition-all duration-200 cursor-pointer text-sm font-medium"
                                        onchange="document.getElementById('filterForm').submit()">
                                    <option value="recent" {{ request('sort') == 'recent' || !request('sort') ? 'selected' : '' }}>Plus recentes</option>
                                    <option value="price_asc" {{ request('sort') == 'price_asc' ? 'selected' : '' }}>Prix croissant</option>
                                    <option value="price_desc" {{ request('sort') == 'price_desc' ? 'selected' : '' }}>Prix decroissant</option>
                                    <option value="views" {{ request('sort') == 'views' ? 'selected' : '' }}>Plus populaires</option>
                                </select>
                                <svg class="absolute right-3 top-1/2 transform -translate-y-1/2 w-4 h-4 pointer-events-none" style="color: #9BA8B7;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                </svg>
                            </div>
                        </div>
                    </div>

                    <!-- Listings Grid -->
                    @if($listings->count() > 0)
                        <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-6">
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
                                Affichage de {{ $listings->firstItem() }} a {{ $listings->lastItem() }} sur {{ $listings->total() }} resultats
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
                                Aucune annonce trouvee
                            </h3>
                            <p class="text-lg mb-8 max-w-md mx-auto" style="color: #6B7B8D;">
                                Nous n'avons pas trouve d'annonces correspondant a vos criteres de recherche. Essayez de modifier vos filtres.
                            </p>

                            <div class="flex flex-col sm:flex-row gap-4 justify-center">
                                <a href="{{ route('listings.index') }}"
                                   class="inline-flex items-center justify-center px-6 py-3 rounded-xl font-bold text-white transition-all duration-300 transform hover:-translate-y-1 gradient-primary"
                                   style="box-shadow: 0 8px 25px rgba(27, 79, 114, 0.25);">
                                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                                    </svg>
                                    Reinitialiser les filtres
                                </a>
                                @auth
                                    <a href="{{ route('listings.create') }}"
                                       class="inline-flex items-center justify-center px-6 py-3 rounded-xl font-bold transition-all duration-300"
                                       style="border: 1px solid rgba(23, 162, 184, 0.4); color: #17A2B8;">
                                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                                        </svg>
                                        Publier une annonce
                                    </a>
                                @endauth
                            </div>
                        </div>
                    @endif
                </main>
            </div>
        </div>
    </div>

    <!-- Mobile Filter Toggle (Fixed Bottom) -->
    <div class="lg:hidden fixed bottom-6 left-1/2 transform -translate-x-1/2 z-40" x-data>
        <button type="button"
                @click="$dispatch('open-mobile-filters')"
                class="inline-flex items-center px-6 py-3.5 rounded-full font-bold text-white gradient-primary transition-all duration-300"
                style="box-shadow: 0 8px 30px rgba(27, 79, 114, 0.4), 0 0 0 4px rgba(27, 79, 114, 0.1);">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" />
            </svg>
            Filtrer
            @if(request()->anyFilled(['q', 'category', 'wilaya', 'etat', 'type_offre', 'currency', 'price_min', 'price_max']))
                <span class="ml-2 w-5 h-5 rounded-full flex items-center justify-center text-[10px] font-bold" style="background: rgba(255,255,255,0.3);">!</span>
            @endif
        </button>
    </div>

    <!-- Mobile Filter Drawer -->
    <div x-data="{ open: false }"
         @open-mobile-filters.window="open = true"
         class="lg:hidden">
        <!-- Backdrop -->
        <div x-show="open" x-cloak
             x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
             @click="open = false"
             class="fixed inset-0 z-50" style="background: rgba(27,42,74,0.6); backdrop-filter: blur(4px);"></div>

        <!-- Drawer -->
        <div x-show="open" x-cloak
             x-transition:enter="transition ease-out duration-300" x-transition:enter-start="translate-y-full" x-transition:enter-end="translate-y-0"
             x-transition:leave="transition ease-in duration-200" x-transition:leave-start="translate-y-0" x-transition:leave-end="translate-y-full"
             class="fixed inset-x-0 bottom-0 z-50 bg-white rounded-t-3xl overflow-y-auto" style="max-height: 85vh; box-shadow: 0 -10px 40px rgba(0,0,0,0.15);">
            <!-- Handle -->
            <div class="sticky top-0 bg-white pt-3 pb-2 px-6 z-10 rounded-t-3xl" style="border-bottom: 1px solid #E0E6ED;">
                <div class="w-10 h-1 bg-gray-300 rounded-full mx-auto mb-3"></div>
                <div class="flex items-center justify-between">
                    <h3 class="text-lg font-bold" style="color: #1B2A4A;">Filtres</h3>
                    <button @click="open = false" class="w-8 h-8 rounded-full flex items-center justify-center" style="background: #F0F4F8; color: #6B7B8D;">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
            </div>
            <div class="p-6">
                <form action="{{ route('listings.index') }}" method="GET">
                    <!-- Search -->
                    <div class="mb-5">
                        <label class="block text-sm font-semibold mb-2" style="color: #1B2A4A;">Rechercher</label>
                        <input type="text" name="q" value="{{ request('q') }}" placeholder="Mot-cle, marque..."
                               class="glass-input w-full pl-4 pr-4 py-3 rounded-xl text-sm">
                    </div>
                    <!-- Category chips -->
                    <div class="mb-5">
                        <label class="block text-sm font-semibold mb-2" style="color: #1B2A4A;">Categorie</label>
                        <div class="flex flex-wrap gap-2">
                            @foreach(['' => 'Toutes', 'boat' => 'Bateaux', 'jetski' => 'Jet-Skis', 'engine' => 'Moteurs', 'parts' => 'Pieces'] as $val => $name)
                                <label class="px-4 py-2.5 rounded-xl cursor-pointer text-sm font-medium transition-all"
                                       style="{{ request('category') == $val || (!request('category') && $val === '') ? 'background: rgba(23, 162, 184, 0.1); border: 1px solid rgba(23, 162, 184, 0.3); color: #17A2B8;' : 'border: 1px solid #E0E6ED; color: #6B7B8D;' }}">
                                    <input type="radio" name="category" value="{{ $val }}" {{ request('category') == $val || (!request('category') && $val === '') ? 'checked' : '' }} class="sr-only">
                                    {{ $name }}
                                </label>
                            @endforeach
                        </div>
                    </div>
                    <!-- Wilaya -->
                    <div class="mb-5">
                        <label class="block text-sm font-semibold mb-2" style="color: #1B2A4A;">Wilaya</label>
                        <select name="wilaya" class="glass-input w-full py-3 px-4 rounded-xl text-sm appearance-none">
                            <option value="">Toutes les wilayas</option>
                            @foreach($wilayas as $code => $wname)
                                <option value="{{ $code }}" {{ request('wilaya') == $code ? 'selected' : '' }}>{{ $code }} - {{ $wname }}</option>
                            @endforeach
                        </select>
                    </div>
                    <!-- Etat chips -->
                    <div class="mb-5">
                        <label class="block text-sm font-semibold mb-2" style="color: #1B2A4A;">Etat</label>
                        <div class="flex flex-wrap gap-2">
                            @foreach(['' => 'Tous', 'jamais_utilise' => 'Jamais utilise', 'comme_neuf' => 'Comme neuf', 'bon_etat' => 'Bon etat', 'etat_moyen' => 'Etat moyen', 'a_reviser' => 'A reviser'] as $val => $label)
                                <label class="px-3 py-2 rounded-xl cursor-pointer text-xs font-medium transition-all"
                                       style="{{ request('etat') == $val || (!request('etat') && $val === '') ? 'background: rgba(23, 162, 184, 0.1); border: 1px solid rgba(23, 162, 184, 0.3); color: #17A2B8;' : 'border: 1px solid #E0E6ED; color: #6B7B8D;' }}">
                                    <input type="radio" name="etat" value="{{ $val }}" {{ request('etat') == $val || (!request('etat') && $val === '') ? 'checked' : '' }} class="sr-only">
                                    {{ $label }}
                                </label>
                            @endforeach
                        </div>
                    </div>
                    <!-- Submit -->
                    <button type="submit" class="w-full py-3.5 rounded-xl font-bold text-white gradient-primary" style="box-shadow: 0 6px 20px rgba(27, 79, 114, 0.25);">
                        Appliquer les filtres
                    </button>
                    @if(request()->anyFilled(['q', 'category', 'wilaya', 'etat', 'type_offre', 'currency', 'price_min', 'price_max']))
                        <a href="{{ route('listings.index') }}" class="block w-full text-center mt-3 py-3 rounded-xl font-medium text-sm" style="color: #17A2B8;">
                            Effacer tous les filtres
                        </a>
                    @endif
                </form>
            </div>
        </div>
    </div>
    <!-- Page-specific animations -->
    <style>
        /* Search input focus: subtle scale + enhanced glow */
        .search-input-focus {
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }
        .search-input-focus:focus {
            transform: scale(1.02);
            box-shadow: 0 0 0 3px rgba(23, 162, 184, 0.15), 0 4px 16px rgba(23, 162, 184, 0.1);
        }

        /* Category filter labels: hover background */
        .category-filter-label:hover {
            background: rgba(23, 162, 184, 0.04);
            border-color: rgba(23, 162, 184, 0.15) !important;
        }

        /* Empty state: gentle floating */
        @keyframes emptyStateFloat {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-10px); }
        }
        .empty-state-float {
            animation: emptyStateFloat 3s ease-in-out infinite;
        }

        /* Empty state outer ring: subtle pulse */
        @keyframes emptyStatePulse {
            0%, 100% { transform: scale(1); opacity: 1; }
            50% { transform: scale(1.05); opacity: 0.7; }
        }
        .empty-state-pulse {
            animation: emptyStatePulse 3s ease-in-out infinite;
        }
    </style>
</x-app-layout>
