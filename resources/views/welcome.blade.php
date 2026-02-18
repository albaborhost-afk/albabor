<x-app-layout>
    <!-- Hero Section with Video Background -->
    <div class="relative overflow-hidden" style="border-radius: 0 0 32px 32px;">
        <!-- Video Background -->
        <video autoplay muted loop playsinline class="absolute inset-0 w-full h-full object-cover" style="z-index: 0;">
            <source src="/videos/albabor_hero_video.mp4" type="video/mp4">
        </video>

        <!-- Overlay with animated gradient -->
        <div class="absolute inset-0 hero-overlay-animated" style="z-index: 1;"></div>

        <!-- Floating decorative elements -->
        <div class="absolute inset-0 overflow-hidden pointer-events-none" style="z-index: 1;">
            <div class="absolute animate-float" style="top: 12%; left: 6%; width: 140px; height: 140px; background: rgba(23,162,184,0.1); border-radius: 50%; filter: blur(50px);"></div>
            <div class="absolute animate-float-reverse" style="bottom: 15%; right: 8%; width: 180px; height: 180px; background: rgba(255,255,255,0.05); border-radius: 50%; filter: blur(60px);"></div>
            <div class="absolute animate-float-slow" style="top: 50%; left: 50%; width: 200px; height: 200px; background: rgba(93,173,226,0.06); border-radius: 50%; filter: blur(60px);"></div>
            <div class="absolute" style="top: 20%; left: 15%; width: 4px; height: 4px; background: rgba(255,255,255,0.3); border-radius: 50%; animation: float 6s ease-in-out infinite;"></div>
            <div class="absolute" style="top: 60%; left: 80%; width: 3px; height: 3px; background: rgba(93,173,226,0.4); border-radius: 50%; animation: float-reverse 8s ease-in-out infinite;"></div>
            <div class="absolute" style="top: 40%; left: 30%; width: 5px; height: 5px; background: rgba(118,215,196,0.3); border-radius: 50%; animation: float-slow 10s ease-in-out infinite;"></div>
            <div class="absolute" style="top: 75%; left: 50%; width: 3px; height: 3px; background: rgba(255,255,255,0.2); border-radius: 50%; animation: float 7s ease-in-out infinite 1s;"></div>
            <div class="absolute" style="top: 30%; left: 70%; width: 4px; height: 4px; background: rgba(93,173,226,0.25); border-radius: 50%; animation: float-reverse 9s ease-in-out infinite 0.5s;"></div>
        </div>

        <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex items-center justify-center" style="z-index: 2; min-height: 75vh; padding-top: 2rem; padding-bottom: 2rem;">
            <div class="text-center w-full max-w-3xl mx-auto">
                <!-- Logo -->
                <div class="flex justify-center mb-8 animate-fade-in-up">
                    <img src="/images/logo-full.png" alt="AlBabor" style="height: 44px; filter: brightness(0) invert(1) drop-shadow(0 4px 16px rgba(0,0,0,0.4));">
                </div>

                <!-- Tagline -->
                <h1 class="text-2xl sm:text-3xl lg:text-5xl font-extrabold text-white leading-tight mb-5 animate-fade-in-up stagger-1" style="text-shadow: 0 2px 24px rgba(0,0,0,0.3); letter-spacing: -0.02em;">
                    La marketplace nautique<br>
                    <span style="background: linear-gradient(135deg, #5DADE2, #76D7C4); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text;">n°1 en Algerie</span>
                </h1>

                <p class="text-sm sm:text-base lg:text-lg max-w-lg mx-auto leading-relaxed mb-10 animate-fade-in-up stagger-2" style="color: rgba(255,255,255,0.7);">
                    Achetez et vendez des bateaux, jet-skis, moteurs et pieces detachees en toute confiance.
                </p>

                <!-- Search bar with glass-morphism -->
                <div class="max-w-xl mx-auto mb-8 animate-fade-in-up stagger-3">
                    <form action="{{ route('listings.index') }}" method="GET" class="relative">
                        <div class="flex items-center hero-search-glass rounded-2xl overflow-hidden">
                            <div class="pl-5 pr-2">
                                <svg class="w-5 h-5" style="color: #9BA8B7;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                                </svg>
                            </div>
                            <input type="text" name="q" placeholder="Rechercher un bateau, moteur, jet-ski..."
                                   class="flex-1 py-4 px-2 text-sm border-0 outline-none focus:ring-0 bg-transparent" style="color: #1B2A4A;">
                            <button type="submit" class="hero-search-btn m-1.5 px-4 sm:px-6 py-3 rounded-xl text-white text-sm font-semibold">
                                <svg class="w-4 h-4 sm:hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                                <span class="hidden sm:inline">Rechercher</span>
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Quick category links -->
                <div class="flex flex-wrap justify-center gap-2.5 mb-8 animate-fade-in-up stagger-3">
                    @foreach([
                        'boat' => ['label' => 'Bateaux', 'icon' => 'M3 17h18l-3-8H6L3 17zM12 3v6M9 6h6'],
                        'jetski' => ['label' => 'Jet-Skis', 'icon' => 'M13 10V3L4 14h7v7l9-11h-7z'],
                        'engine' => ['label' => 'Moteurs', 'icon' => 'M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.066 2.573c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.573 1.066c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.066-2.573c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z'],
                        'parts' => ['label' => 'Pieces', 'icon' => 'M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4'],
                    ] as $catKey => $cat)
                        <a href="{{ route('listings.index', ['category' => $catKey]) }}" class="hero-category-pill inline-flex items-center gap-1.5 px-4 py-2 rounded-full text-xs font-medium text-white">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $cat['icon'] }}"/></svg>
                            {{ $cat['label'] }}
                        </a>
                    @endforeach
                </div>

                <!-- CTA Buttons -->
                <div class="flex flex-col sm:flex-row justify-center gap-3 animate-fade-in-up stagger-4">
                    <a href="{{ route('listings.index') }}" class="btn-ghost group px-7 py-3.5 text-sm font-semibold rounded-2xl">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                        Explorer les annonces
                    </a>
                    @guest
                        <a href="{{ route('register') }}" class="btn-white px-7 py-3.5 text-sm font-semibold rounded-2xl">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"></path></svg>
                            Creer un compte
                        </a>
                    @else
                        <a href="{{ route('listings.create') }}" class="btn-white px-7 py-3.5 text-sm font-semibold rounded-2xl">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                            Publier une annonce
                        </a>
                    @endguest
                </div>

                <!-- Scroll indicator -->
                <div class="mt-10 animate-bounce" style="animation-duration: 2.5s;">
                    <svg class="w-5 h-5 mx-auto" style="color: rgba(255,255,255,0.3);" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3"></path>
                    </svg>
                </div>
            </div>
        </div>
    </div>

    <!-- Categories -->
    <div class="py-8 sm:py-10" style="background-color: #F0F4F8;">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-6 reveal">
                <span class="inline-flex items-center gap-1.5 px-4 py-1.5 text-[11px] font-semibold uppercase tracking-wider rounded-full mb-4" style="background: rgba(23, 162, 184, 0.08); color: #17A2B8;">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/></svg>
                    Explorez
                </span>
                <h2 class="text-2xl sm:text-3xl font-black tracking-tight" style="color: #1B2A4A;">Nos categories</h2>
                <p class="mt-3 text-sm max-w-md mx-auto" style="color: #6B7B8D;">Trouvez exactement ce que vous cherchez</p>
            </div>

            <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-6 reveal-stagger">
                @foreach([
                    'boat' => ['label' => 'Bateaux', 'desc' => 'Voiliers, yachts, semi-rigides', 'img' => '/images/yacht.png', 'color' => '#1B4F72', 'gradient' => 'linear-gradient(135deg, #1B4F72, #2471A3)', 'bg' => 'rgba(27,79,114,0.06)'],
                    'jetski' => ['label' => 'Jet-Skis', 'desc' => 'Scooters des mers', 'img' => '/images/jetski.png', 'color' => '#17A2B8', 'gradient' => 'linear-gradient(135deg, #17A2B8, #1ABC9C)', 'bg' => 'rgba(23,162,184,0.06)'],
                    'engine' => ['label' => 'Moteurs', 'desc' => 'Hors-bord, in-bord', 'img' => '/images/moteurs.png', 'color' => '#F39C12', 'gradient' => 'linear-gradient(135deg, #F39C12, #E67E22)', 'bg' => 'rgba(243,156,18,0.06)'],
                    'parts' => ['label' => 'Pieces', 'desc' => 'Accessoires et equipements', 'img' => '/images/pieces.png', 'color' => '#9B59B6', 'gradient' => 'linear-gradient(135deg, #9B59B6, #8E44AD)', 'bg' => 'rgba(155,89,182,0.06)'],
                ] as $catKey => $cat)
                    <a href="{{ route('listings.index', ['category' => $catKey]) }}"
                       class="category-card-wrapper group relative bg-white rounded-2xl text-center overflow-hidden">
                        <!-- Top colored accent bar -->
                        <div class="h-1 w-full rounded-t-2xl" style="background: {{ $cat['gradient'] }};"></div>

                        <!-- Hover gradient bg -->
                        <div class="absolute inset-0 opacity-0 group-hover:opacity-100 transition-opacity duration-300 rounded-2xl" style="background: {{ $cat['bg'] }};"></div>

                        <div class="relative p-5 sm:p-6">
                            <div class="w-24 h-24 sm:w-28 sm:h-28 mx-auto mb-4 rounded-2xl flex items-center justify-center transition-transform duration-300 group-hover:scale-110" style="background: {{ $cat['bg'] }};">
                                <img src="{{ $cat['img'] }}" alt="{{ $cat['label'] }}" class="w-20 h-20 sm:w-24 sm:h-24 object-contain drop-shadow-md">
                            </div>
                            <h3 class="text-sm sm:text-base font-bold mb-1 transition-colors duration-300" style="color: #1B2A4A;">{{ $cat['label'] }}</h3>
                            <p class="text-[11px] sm:text-xs" style="color: #9BA8B7;">{{ $cat['desc'] }}</p>

                            <!-- Arrow indicator -->
                            <div class="mt-3 opacity-0 group-hover:opacity-100 transition-all duration-300 transform translate-y-2 group-hover:translate-y-0">
                                <span class="inline-flex items-center gap-1 px-3 py-1 rounded-full text-[11px] font-semibold" style="color: white; background: {{ $cat['gradient'] }};">
                                    Voir tout
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                                </span>
                            </div>
                        </div>
                    </a>
                @endforeach
            </div>
        </div>
    </div>

    <!-- Featured Listings -->
    @if(isset($featuredListings) && $featuredListings->count() > 0)
    <div class="py-8 sm:py-10" style="background: white;">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between mb-6">
                <div class="flex items-center gap-3 reveal-left">
                    <div class="w-10 h-10 rounded-xl flex items-center justify-center" style="background: linear-gradient(135deg, rgba(241,196,15,0.15), rgba(255,140,0,0.12));">
                        <svg class="w-5 h-5" style="color: #F1C40F;" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
                    </div>
                    <div>
                        <h2 class="text-lg sm:text-xl font-bold" style="color: #1B2A4A;">Annonces en vedette</h2>
                        <p class="text-xs" style="color: #9BA8B7;">Les meilleures offres selectionnees</p>
                    </div>
                </div>
                <a href="{{ route('listings.index') }}" class="btn-see-all hidden sm:inline-flex">
                    Voir tout
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                </a>
            </div>

            <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-5 reveal-stagger">
                @foreach($featuredListings as $listing)
                    <x-listing-card :listing="$listing" />
                @endforeach
            </div>

            <div class="sm:hidden mt-6 text-center">
                <a href="{{ route('listings.index') }}" class="btn-see-all px-5 py-2.5">
                    Voir toutes les annonces
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                </a>
            </div>
        </div>
    </div>
    @endif

    <!-- Latest Listings -->
    @if(isset($latestListings) && $latestListings->count() > 0)
    <div class="py-8 sm:py-10" style="background-color: #F0F4F8;">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between mb-6">
                <div class="flex items-center gap-3 reveal-right">
                    <div class="w-10 h-10 rounded-xl flex items-center justify-center" style="background: rgba(27, 79, 114, 0.08);">
                        <svg class="w-5 h-5" style="color: #1B4F72;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </div>
                    <div>
                        <h2 class="text-lg sm:text-xl font-bold" style="color: #1B2A4A;">Dernieres annonces</h2>
                        <p class="text-xs" style="color: #9BA8B7;">Fraichement publiees</p>
                    </div>
                </div>
                <a href="{{ route('listings.index') }}" class="btn-see-all hidden sm:inline-flex">
                    Voir tout
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                </a>
            </div>

            <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-5 reveal-stagger">
                @foreach($latestListings as $listing)
                    <x-listing-card :listing="$listing" />
                @endforeach
            </div>

            <div class="mt-8 text-center">
                <a href="{{ route('listings.index') }}" class="btn-view-all rounded-2xl">
                    Voir toutes les annonces
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/></svg>
                </a>
            </div>
        </div>
    </div>
    @endif

    <!-- How it works -->
    <div class="py-8 sm:py-10" style="background: white;">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-6 reveal">
                <span class="inline-flex items-center gap-1.5 px-4 py-1.5 text-[11px] font-semibold uppercase tracking-wider rounded-full mb-4" style="background: rgba(27, 79, 114, 0.06); color: #1B4F72;">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                    Simple et rapide
                </span>
                <h2 class="text-2xl sm:text-3xl font-black tracking-tight" style="color: #1B2A4A;">Comment ca marche ?</h2>
                <p class="mt-3 text-sm max-w-md mx-auto" style="color: #6B7B8D;">Vendez ou achetez en 3 etapes simples</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-8 sm:gap-6 relative reveal-stagger">
                <!-- Animated connecting line (desktop only) -->
                <div class="hidden md:block absolute connecting-line-animated" style="top: 48px; left: 20%; right: 20%;"></div>

                @foreach([
                    ['num' => '1', 'title' => 'Creez votre annonce', 'desc' => 'Ajoutez des photos, une description et fixez votre prix en quelques minutes.', 'gradient' => 'linear-gradient(135deg, #1B4F72, #2471A3)', 'icon' => 'M12 4v16m8-8H4', 'stepClass' => 'step-card-1'],
                    ['num' => '2', 'title' => 'Recevez des offres', 'desc' => 'Les acheteurs vous contactent directement ou via notre systeme de mediation securise.', 'gradient' => 'linear-gradient(135deg, #2471A3, #17A2B8)', 'icon' => 'M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z', 'stepClass' => 'step-card-2'],
                    ['num' => '3', 'title' => 'Concluez la vente', 'desc' => 'Finalisez la transaction en toute securite avec un acheteur verifie.', 'gradient' => 'linear-gradient(135deg, #17A2B8, #27AE60)', 'icon' => 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z', 'stepClass' => 'step-card-3'],
                ] as $step)
                    <div class="card-hover-lift step-card {{ $step['stepClass'] }} relative bg-white rounded-2xl p-6 sm:p-8 text-center">
                        <!-- Step number badge -->
                        <div class="absolute -top-3 left-1/2 -translate-x-1/2 w-7 h-7 rounded-full flex items-center justify-center text-xs font-black text-white" style="background: {{ $step['gradient'] }}; box-shadow: 0 4px 10px rgba(27,79,114,0.3);">
                            {{ $step['num'] }}
                        </div>
                        <div class="feature-icon w-16 h-16 mx-auto mb-4 mt-2 rounded-2xl flex items-center justify-center" style="background: {{ $step['gradient'] }}; box-shadow: 0 8px 25px rgba(27,79,114,0.2);">
                            <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $step['icon'] }}"/></svg>
                        </div>
                        <h3 class="text-base font-bold mb-2" style="color: #1B2A4A;">{{ $step['title'] }}</h3>
                        <p class="text-sm leading-relaxed max-w-xs mx-auto" style="color: #6B7B8D;">{{ $step['desc'] }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    <!-- Features / Why AlBabor -->
    <div class="py-8 sm:py-10" style="background-color: #F0F4F8;">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-6 reveal">
                <span class="inline-flex items-center gap-1.5 px-4 py-1.5 text-[11px] font-semibold uppercase tracking-wider rounded-full mb-4" style="background: rgba(39, 174, 96, 0.08); color: #27AE60;">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    Avantages
                </span>
                <h2 class="text-2xl sm:text-3xl font-black tracking-tight" style="color: #1B2A4A;">Pourquoi choisir AlBabor ?</h2>
                <p class="mt-3 text-sm max-w-md mx-auto" style="color: #6B7B8D;">La plateforme de confiance pour le nautisme en Algerie</p>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-6 reveal-stagger">
                @foreach([
                    ['icon' => 'M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z', 'color' => '#17A2B8', 'gradient' => 'linear-gradient(135deg, #17A2B8, #1ABC9C)', 'title' => 'Securise', 'desc' => 'Vendeurs verifies et systeme de mediation pour des transactions en toute confiance.'],
                    ['icon' => 'M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z', 'color' => '#27AE60', 'gradient' => 'linear-gradient(135deg, #27AE60, #2ECC71)', 'title' => '69 Wilayas', 'desc' => 'Couverture nationale complete. Trouvez des annonces partout en Algerie.'],
                    ['icon' => 'M13 10V3L4 14h7v7l9-11h-7z', 'color' => '#1B4F72', 'gradient' => 'linear-gradient(135deg, #1B4F72, #2471A3)', 'title' => 'Rapide', 'desc' => 'Publiez votre annonce en quelques minutes avec notre formulaire simplifie.'],
                    ['icon' => 'M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z', 'color' => '#F39C12', 'gradient' => 'linear-gradient(135deg, #F39C12, #E67E22)', 'title' => 'Double devise', 'desc' => 'Publiez en DZD ou EUR avec conversion automatique pour toucher plus d\'acheteurs.'],
                ] as $feature)
                    <div class="card-hover-lift bg-white rounded-2xl overflow-hidden">
                        <div class="h-1 w-full" style="background: {{ $feature['gradient'] }};"></div>
                        <div class="p-6 sm:p-7">
                            <div class="feature-icon w-12 h-12 mb-4 rounded-xl flex items-center justify-center" style="background: {{ $feature['gradient'] }}; box-shadow: 0 4px 12px {{ $feature['color'] }}33;">
                                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $feature['icon'] }}"></path>
                                </svg>
                            </div>
                            <h3 class="text-sm font-bold mb-1.5" style="color: #1B2A4A;">{{ $feature['title'] }}</h3>
                            <p style="color: #6B7B8D;" class="text-xs leading-relaxed">{{ $feature['desc'] }}</p>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    <!-- CTA -->
    <div class="px-4 sm:px-6 lg:px-8 py-6">
        <div class="relative py-10 sm:py-12 overflow-hidden reveal-scale" style="background: linear-gradient(135deg, #0A192F 0%, #1B4F72 40%, #17A2B8 100%); border-radius: 28px;">
            <!-- Decorative elements -->
            <div class="absolute inset-0 overflow-hidden pointer-events-none">
                <div class="absolute animate-float-slow" style="top: -30px; right: 10%; width: 250px; height: 250px; background: rgba(23,162,184,0.12); border-radius: 50%; filter: blur(60px);"></div>
                <div class="absolute animate-float" style="bottom: -40px; left: 15%; width: 300px; height: 200px; background: rgba(93,173,226,0.08); border-radius: 50%; filter: blur(60px);"></div>
                <div class="absolute animate-float-reverse" style="top: 20%; left: 50%; width: 180px; height: 180px; background: rgba(255,255,255,0.04); border-radius: 50%; filter: blur(40px);"></div>
                <div class="absolute inset-0" style="background-image: radial-gradient(rgba(255,255,255,0.03) 1px, transparent 1px); background-size: 32px 32px;"></div>
            </div>

            <!-- Floating particles -->
            <div class="absolute inset-0 overflow-hidden pointer-events-none cta-particles">
                <div class="particle"></div>
                <div class="particle"></div>
                <div class="particle"></div>
                <div class="particle"></div>
                <div class="particle"></div>
                <div class="particle"></div>
                <div class="particle"></div>
                <div class="particle"></div>
            </div>

            <div class="relative max-w-2xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
                <!-- Anchor icon -->
                <div class="w-16 h-16 mx-auto mb-6 rounded-2xl flex items-center justify-center" style="background: rgba(255,255,255,0.1); border: 1px solid rgba(255,255,255,0.15); backdrop-filter: blur(8px);">
                    <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7zm0 9.5c-1.38 0-2.5-1.12-2.5-2.5s1.12-2.5 2.5-2.5 2.5 1.12 2.5 2.5-1.12 2.5-2.5 2.5z"/>
                    </svg>
                </div>
                <h2 class="text-xl sm:text-2xl lg:text-4xl font-black text-white mb-4 tracking-tight" style="text-shadow: 0 2px 16px rgba(0,0,0,0.2);">Pret a naviguer ?</h2>
                <p class="text-sm sm:text-base mb-10 max-w-md mx-auto leading-relaxed" style="color: rgba(255,255,255,0.6);">Rejoignez des milliers de passionnes de nautisme sur AlBabor</p>

                <div class="flex flex-col sm:flex-row justify-center gap-3">
                    @guest
                        <a href="{{ route('register') }}" class="btn-white-solid btn-cta-glow rounded-2xl">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"></path></svg>
                            Creer un compte gratuit
                        </a>
                        <a href="{{ route('listings.index') }}" class="btn-ghost btn-cta-glow px-8 py-4 text-sm font-semibold rounded-2xl">
                            Explorer les annonces
                        </a>
                    @else
                        <a href="{{ route('listings.create') }}" class="btn-white-solid btn-cta-glow rounded-2xl">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                            Publier une annonce
                        </a>
                    @endguest
                </div>
            </div>
        </div>
    </div>

<script>
// Scroll Reveal with stagger support
document.addEventListener('DOMContentLoaded', function() {
    const reveals = document.querySelectorAll('.reveal, .reveal-left, .reveal-right, .reveal-scale, .reveal-stagger');
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('active');
                observer.unobserve(entry.target);
            }
        });
    }, { threshold: 0.08, rootMargin: '0px 0px -40px 0px' });
    reveals.forEach(el => observer.observe(el));

    // Counter animation for stats
    const counterObserver = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                const el = entry.target;
                const target = el.getAttribute('data-count');
                if (!target || el.dataset.counted) return;
                el.dataset.counted = 'true';

                const isText = isNaN(target);
                if (isText) { el.textContent = target; return; }

                const end = parseInt(target);
                const duration = 1500;
                const startTime = performance.now();

                function easeOutExpo(t) {
                    return t === 1 ? 1 : 1 - Math.pow(2, -10 * t);
                }

                function updateCounter(currentTime) {
                    const elapsed = currentTime - startTime;
                    const progress = Math.min(elapsed / duration, 1);
                    const easedProgress = easeOutExpo(progress);
                    const current = Math.round(easedProgress * end);

                    el.textContent = current.toLocaleString('fr-FR') + (end >= 100 ? '+' : '');

                    if (progress < 1) {
                        requestAnimationFrame(updateCounter);
                    }
                }
                requestAnimationFrame(updateCounter);
            }
        });
    }, { threshold: 0.5 });

    document.querySelectorAll('[data-count]').forEach(el => counterObserver.observe(el));
});
</script>
</x-app-layout>
