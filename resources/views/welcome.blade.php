<x-app-layout
    title="La Marketplace Nautique N°1 en Algérie"
    description="Achetez et vendez des bateaux, jet-skis, moteurs et pièces détachées en Algérie. Annonces vérifiées, paiement sécurisé, livraison partout en Algérie."
>
    <!-- Hero Section with Video Background -->
    <div class="relative overflow-hidden" style="border-radius: 0 0 32px 32px;">
        <!-- Video Background -->
        <video autoplay muted loop playsinline class="absolute inset-0 w-full h-full object-cover" style="z-index: 0;">
            <source src="/videos/albabor_hero_video.mp4" type="video/mp4">
        </video>

        <!-- Overlay with animated gradient -->
        <div class="absolute inset-0 hero-overlay-animated" style="z-index: 1;"></div>


        <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex items-center justify-center" style="z-index: 2; min-height: 75vh; padding-top: 2rem; padding-bottom: 2rem;">
            <div class="text-center w-full max-w-3xl mx-auto">
                <!-- Logo -->
                <div class="flex justify-center mb-8 animate-fade-in-up">
                    <img src="/images/logo-full.png" alt="AlBabor" style="height: 44px; filter: brightness(0) invert(1) drop-shadow(0 4px 16px rgba(0,0,0,0.4));">
                </div>

                <!-- Tagline -->
                <h1 class="text-2xl sm:text-3xl lg:text-5xl font-extrabold text-white leading-tight mb-5 animate-fade-in-up stagger-1" style="text-shadow: 0 2px 24px rgba(0,0,0,0.3); letter-spacing: -0.02em;">
                    {{ __('La marketplace nautique') }}<br>
                    <span style="background: linear-gradient(135deg, #5DADE2, #76D7C4); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text;">{{ __('n°1 en Algerie') }}</span>
                </h1>

                <p class="text-sm sm:text-base lg:text-lg max-w-lg mx-auto leading-relaxed mb-10 animate-fade-in-up stagger-2" style="color: rgba(255,255,255,0.7);">
                    {{ __('Achetez et vendez des bateaux, jet-skis, moteurs et pieces detachees en toute confiance.') }}
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
                            <input type="text" name="q" placeholder="{{ __('Rechercher un bateau, moteur, jet-ski...') }}"
                                   class="flex-1 py-4 px-2 text-sm border-0 outline-none focus:ring-0 bg-transparent" style="color: #1B2A4A;">
                            <button type="submit" class="hero-search-btn m-1.5 px-4 sm:px-6 py-3 rounded-xl text-white text-sm font-semibold">
                                <svg class="w-4 h-4 sm:hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                                <span class="hidden sm:inline">{{ __('Rechercher') }}</span>
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Quick category links -->
                <div class="flex flex-wrap justify-center gap-2.5 mb-8 animate-fade-in-up stagger-3">
                    @foreach([
                        'boat' => ['label' => __('Bateaux'), 'img' => '/images/nav-boat.png'],
                        'jetski' => ['label' => __('Jet-Skis'), 'img' => '/images/nav-jetski.png'],
                        'engine' => ['label' => __('Moteurs'), 'img' => '/images/nav-moteur.png'],
                        'parts' => ['label' => __('Pieces'), 'img' => '/images/nav-pieces.png'],
                    ] as $catKey => $cat)
                        <a href="{{ route('listings.index', ['category' => $catKey]) }}" class="hero-category-pill inline-flex items-center gap-1.5 px-4 py-2 rounded-full text-xs font-medium text-white">
                            <img src="{{ $cat['img'] }}" alt="" class="w-4 h-4 object-contain" style="filter: brightness(0) invert(1) opacity(0.85);">
                            {{ $cat['label'] }}
                        </a>
                    @endforeach
                </div>

                <!-- CTA Buttons -->
                <div class="flex flex-col sm:flex-row justify-center gap-3 animate-fade-in-up stagger-4">
                    <a href="{{ route('listings.index') }}" class="btn-ghost group px-7 py-3.5 text-sm font-semibold rounded-2xl">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                        {{ __('Explorer les annonces') }}
                    </a>
                    @guest
                        <a href="{{ route('register') }}" class="btn-white px-7 py-3.5 text-sm font-semibold rounded-2xl">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"></path></svg>
                            {{ __('Creer un compte') }}
                        </a>
                    @else
                        <a href="{{ route('listings.create') }}" class="btn-white px-7 py-3.5 text-sm font-semibold rounded-2xl">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                            {{ __('Publier une annonce') }}
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

    <!-- Banner Slider / Publicités -->
    @if(isset($banners) && $banners->isNotEmpty())
    <div class="py-6 sm:py-8" style="background: #F0F4F8;">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div
                x-data="bannerSlider({{ $banners->count() }})"
                x-init="init()"
                @mouseenter="pause()"
                @mouseleave="resume()"
                class="relative overflow-hidden rounded-2xl shadow-lg h-64 md:h-80 lg:h-96 bg-gray-900"
            >
                {{-- Slides --}}
                @foreach($banners as $index => $banner)
                    <div
                        x-show="active === {{ $index }}"
                        x-transition:enter="transition ease-out duration-700"
                        x-transition:enter-start="opacity-0"
                        x-transition:enter-end="opacity-100"
                        x-transition:leave="transition ease-in duration-500"
                        x-transition:leave-start="opacity-100"
                        x-transition:leave-end="opacity-0"
                        class="absolute inset-0"
                    >
                        <img
                            src="{{ $banner->image_url }}"
                            alt="{{ $banner->title }}"
                            class="w-full h-full object-cover"
                            loading="{{ $index === 0 ? 'eager' : 'lazy' }}"
                        >

                        {{-- Gradient overlay for text readability --}}
                        <div class="absolute inset-0" style="background: linear-gradient(to top, rgba(0,0,0,0.75) 0%, rgba(0,0,0,0.25) 50%, rgba(0,0,0,0) 100%);"></div>

                        {{-- Publicité badge --}}
                        @if($banner->company_name)
                            <div class="absolute top-3 left-3 sm:top-4 sm:left-4 z-10">
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[10px] sm:text-xs font-semibold backdrop-blur-md" style="background: rgba(255,255,255,0.18); color: white; border: 1px solid rgba(255,255,255,0.25);">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z"/></svg>
                                    {{ __('Publicite') }} &bull; {{ $banner->company_name }}
                                </span>
                            </div>
                        @endif

                        {{-- Content overlay --}}
                        <div class="absolute inset-x-0 bottom-0 p-5 sm:p-8 lg:p-10">
                            <div class="max-w-2xl">
                                @if($banner->title)
                                    <h3 class="text-xl sm:text-2xl lg:text-3xl font-black text-white leading-tight mb-2" style="text-shadow: 0 2px 16px rgba(0,0,0,0.4);">
                                        {{ $banner->title }}
                                    </h3>
                                @endif
                                @if($banner->subtitle)
                                    <p class="text-xs sm:text-sm lg:text-base mb-3 sm:mb-4 leading-relaxed" style="color: rgba(255,255,255,0.88); text-shadow: 0 1px 8px rgba(0,0,0,0.3);">
                                        {{ $banner->subtitle }}
                                    </p>
                                @endif
                                @if($banner->link_url)
                                    <a
                                        href="{{ route('banners.click', $banner) }}"
                                        target="_blank"
                                        rel="noopener"
                                        class="inline-flex items-center gap-2 px-4 sm:px-5 py-2 sm:py-2.5 text-xs sm:text-sm font-semibold text-white rounded-xl transition-all hover:scale-105"
                                        style="background: linear-gradient(135deg, #2471A3, #17A2B8); box-shadow: 0 4px 16px rgba(36,113,163,0.4);"
                                    >
                                        {{ __("Voir l'offre") }}
                                        <svg class="w-3.5 h-3.5 sm:w-4 sm:h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/></svg>
                                    </a>
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach

                {{-- Touch swipe zones (mobile) --}}
                @if($banners->count() > 1)
                    <button
                        type="button"
                        @click="prev()"
                        aria-label="{{ __('Precedent') }}"
                        class="md:hidden absolute left-0 top-0 bottom-0 w-1/3 z-10 opacity-0"
                    ></button>
                    <button
                        type="button"
                        @click="next()"
                        aria-label="{{ __('Suivant') }}"
                        class="md:hidden absolute right-0 top-0 bottom-0 w-1/3 z-10 opacity-0"
                    ></button>

                    {{-- Desktop arrows --}}
                    <button
                        type="button"
                        @click="prev()"
                        aria-label="{{ __('Precedent') }}"
                        class="hidden md:flex absolute left-3 top-1/2 -translate-y-1/2 w-10 h-10 items-center justify-center rounded-full backdrop-blur-md transition-all hover:scale-110 z-20"
                        style="background: rgba(255,255,255,0.2); border: 1px solid rgba(255,255,255,0.3);"
                    >
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
                    </button>
                    <button
                        type="button"
                        @click="next()"
                        aria-label="{{ __('Suivant') }}"
                        class="hidden md:flex absolute right-3 top-1/2 -translate-y-1/2 w-10 h-10 items-center justify-center rounded-full backdrop-blur-md transition-all hover:scale-110 z-20"
                        style="background: rgba(255,255,255,0.2); border: 1px solid rgba(255,255,255,0.3);"
                    >
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
                    </button>

                    {{-- Dots --}}
                    <div class="absolute bottom-3 left-1/2 -translate-x-1/2 flex items-center gap-1.5 z-20">
                        <template x-for="i in total" :key="i">
                            <button
                                type="button"
                                @click="goTo(i-1)"
                                :aria-label="'Aller a la diapositive ' + i"
                                class="rounded-full transition-all duration-300"
                                :class="active === (i-1) ? 'w-6 h-2' : 'w-2 h-2'"
                                :style="active === (i-1) ? 'background: white' : 'background: rgba(255,255,255,0.5)'"
                            ></button>
                        </template>
                    </div>
                @endif
            </div>
        </div>

        <script>
            function bannerSlider(total) {
                return {
                    active: 0,
                    total: total,
                    timer: null,
                    paused: false,
                    init() {
                        if (this.total <= 1) return;
                        this.start();
                    },
                    start() {
                        clearInterval(this.timer);
                        this.timer = setInterval(() => {
                            if (!this.paused) this.next();
                        }, 5000);
                    },
                    next() {
                        this.active = (this.active + 1) % this.total;
                    },
                    prev() {
                        this.active = (this.active - 1 + this.total) % this.total;
                    },
                    goTo(i) {
                        this.active = i;
                    },
                    pause() { this.paused = true; },
                    resume() { this.paused = false; },
                };
            }
        </script>
    </div>
    @endif

    <!-- Categories -->
    <div class="py-8 sm:py-10" style="background: #F0F4F8;">
        <div class="max-w-7xl mx-auto">
            <div class="flex items-center justify-between mb-5 px-4 sm:px-6 lg:px-8">
                <h2 class="text-xl sm:text-2xl font-black tracking-tight" style="color: #1B2A4A;">{{ __('Nos categories') }}</h2>
                <a href="{{ route('listings.index') }}" class="text-xs font-semibold flex items-center gap-1" style="color: #17A2B8;">
                    {{ __('Voir tout') }} <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                </a>
            </div>

            <div x-data="categoryScroll()" x-init="init()" class="relative">
                <div x-ref="scroller" class="flex lg:grid lg:grid-cols-4 gap-3 sm:gap-5 overflow-x-auto lg:overflow-visible px-4 sm:px-6 lg:px-8 pb-2 lg:pb-0 snap-x snap-mandatory" style="scrollbar-width:none; -ms-overflow-style:none; -webkit-overflow-scrolling: touch;">
                    @foreach([
                        'boat'   => ['label' => __('Bateaux'),  'desc' => __('Voiliers, yachts, semi-rigides'), 'img' => '/images/yacht.png',   'bg' => 'linear-gradient(145deg,#E8F4FD,#D6EAF8)', 'accent' => '#2471A3', 'shadow' => '0 8px 30px rgba(36,113,163,0.18)'],
                        'jetski' => ['label' => __('Jet-Skis'), 'desc' => __('Scooters des mers'),               'img' => '/images/jetski.png',  'bg' => 'linear-gradient(145deg,#E8F8F9,#D1F2EB)', 'accent' => '#17A2B8', 'shadow' => '0 8px 30px rgba(23,162,184,0.18)'],
                        'engine' => ['label' => __('Moteurs'),  'desc' => __('Hors-bord, in-bord'),              'img' => '/images/moteurs.png', 'bg' => 'linear-gradient(145deg,#FEF9E7,#FDEBD0)', 'accent' => '#E67E22', 'shadow' => '0 8px 30px rgba(230,126,34,0.18)'],
                        'parts'  => ['label' => __('Pieces'),   'desc' => __('Accessoires & equipements'),       'img' => '/images/pieces.png',  'bg' => 'linear-gradient(145deg,#F5EEF8,#EBD5F9)', 'accent' => '#8E44AD', 'shadow' => '0 8px 30px rgba(142,68,173,0.18)'],
                    ] as $catKey => $cat)
                        <a href="{{ route('listings.index', ['category' => $catKey]) }}"
                           class="group flex-shrink-0 lg:flex-shrink flex flex-col items-center text-center transition-all duration-300 active:scale-95 snap-start"
                           style="min-width: 150px;"
                           onmouseenter="this.style.transform='translateY(-4px)'"
                           onmouseleave="this.style.transform='translateY(0)'">

                            <!-- Image -->
                            <div class="w-full flex items-center justify-center px-4">
                                <img src="{{ $cat['img'] }}" alt="{{ $cat['label'] }}"
                                     class="object-contain transition-transform duration-300 group-hover:scale-105"
                                     style="width: 170px; height: 170px;">
                            </div>

                            <!-- Text -->
                            <div class="pt-1 px-2">
                                <p class="text-sm font-bold" style="color: #1B2A4A;">{{ $cat['label'] }}</p>
                                <p class="text-[11px] mt-0.5 leading-snug" style="color: #9BA8B7;">{{ $cat['desc'] }}</p>
                            </div>
                        </a>
                    @endforeach
                </div>

                {{-- Scroll indicator dots (mobile only) --}}
                <div class="flex lg:hidden justify-center gap-1.5 mt-3">
                    <template x-for="i in 4" :key="i">
                        <div class="rounded-full transition-all duration-300"
                             :class="active === (i-1) ? 'w-6 h-2' : 'w-2 h-2'"
                             :style="active === (i-1) ? 'background:#2471A3' : 'background:#D5DDE5'">
                        </div>
                    </template>
                </div>

                {{-- Swipe hint arrow (shows briefly then fades) --}}
                <div x-show="showHint" x-transition:leave="transition ease-in duration-500" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
                     class="lg:hidden absolute right-2 top-1/2 -translate-y-1/2 pointer-events-none">
                    <div class="animate-bounce-x flex items-center gap-1 bg-white/90 backdrop-blur rounded-full px-3 py-1.5 shadow-lg">
                        <span class="text-xs font-medium" style="color:#2471A3;">Glisser</span>
                        <svg class="w-4 h-4" style="color:#2471A3;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                    </div>
                </div>
            </div>

            <style>
                @keyframes bounce-x { 0%,100% { transform: translateX(0); } 50% { transform: translateX(6px); } }
                .animate-bounce-x { animation: bounce-x 1s ease-in-out infinite; }
            </style>

            <script>
                function categoryScroll() {
                    return {
                        active: 0,
                        showHint: true,
                        autoScrollTimer: null,
                        init() {
                            const el = this.$refs.scroller;
                            if (!el) return;

                            // Update dots on manual scroll
                            el.addEventListener('scroll', () => {
                                const itemW = el.scrollWidth / 4;
                                this.active = Math.round(el.scrollLeft / itemW);
                            });

                            // Auto-scroll on mobile
                            if (window.innerWidth < 1024) {
                                this.startAutoScroll(el);

                                // Pause on touch
                                el.addEventListener('touchstart', () => { clearInterval(this.autoScrollTimer); }, { passive: true });
                                el.addEventListener('touchend', () => { this.startAutoScroll(el); }, { passive: true });
                            }

                            // Hide swipe hint after 3s
                            setTimeout(() => { this.showHint = false; }, 3000);
                        },
                        startAutoScroll(el) {
                            clearInterval(this.autoScrollTimer);
                            this.autoScrollTimer = setInterval(() => {
                                const itemW = el.scrollWidth / 4;
                                const next = (this.active + 1) % 4;
                                el.scrollTo({ left: next * itemW, behavior: 'smooth' });
                                this.active = next;
                            }, 3000);
                        }
                    };
                }
            </script>
        </div>
    </div>

    <!-- Annonces Sponsorisées -->
    @if(isset($featuredListings) && $featuredListings->count() > 0)
    <div class="py-8 sm:py-10" style="background: linear-gradient(180deg, #fffdf5 0%, #fff8e1 100%); border-top: 1px solid #fde68a;">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

            <!-- Header badge + title -->
            <div class="flex items-center justify-between mb-6">
                <div class="flex items-center gap-3">
                    <!-- Animated star badge -->
                    <div class="relative flex-shrink-0">
                        <div class="w-11 h-11 rounded-2xl flex items-center justify-center" style="background: linear-gradient(135deg,#F59E0B,#F97316); box-shadow: 0 4px 16px rgba(245,158,11,0.4);">
                            <svg class="w-5 h-5 text-white" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
                        </div>
                        <!-- Pulse ring -->
                        <div class="absolute inset-0 rounded-2xl animate-ping opacity-25" style="background: #F59E0B;"></div>
                    </div>
                    <div>
                        <div class="flex items-center gap-2 mb-0.5">
                            <h2 class="text-lg sm:text-xl font-black tracking-tight" style="color: #1B2A4A;">{{ __('Annonces sponsorisees') }}</h2>
                            <span class="text-[10px] font-bold px-2 py-0.5 rounded-full uppercase tracking-wide" style="background: #FEF3C7; color: #B45309;">Premium</span>
                        </div>
                        <p class="text-xs" style="color: #9BA8B7;">{{ __('Selectionnees et mises en avant par notre equipe') }}</p>
                    </div>
                </div>
                <a href="{{ route('listings.index') }}" class="hidden sm:flex text-xs font-semibold items-center gap-1" style="color: #D97706;">
                    {{ __('Voir tout') }}
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                </a>
            </div>

            <!-- Listing cards with sponsored badge overlay -->
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4">
                @foreach($featuredListings as $listing)
                    <div class="relative">
                        <!-- Sponsored badge -->
                        <div class="absolute top-2 left-2 z-20 flex items-center gap-1 px-2 py-1 rounded-lg text-[10px] font-bold shadow-md"
                             style="background: linear-gradient(135deg,#F59E0B,#F97316); color: white; letter-spacing: 0.03em;">
                            <svg class="w-2.5 h-2.5" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
                            {{ __('Sponsorise') }}
                        </div>
                        <x-listing-card :listing="$listing" />
                    </div>
                @endforeach
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
                        <h2 class="text-lg sm:text-xl font-bold" style="color: #1B2A4A;">{{ __('Dernieres annonces') }}</h2>
                        <p class="text-xs" style="color: #9BA8B7;">{{ __('Fraichement publiees') }}</p>
                    </div>
                </div>
                <a href="{{ route('listings.index') }}" class="btn-see-all hidden sm:inline-flex">
                    {{ __('Voir tout') }}
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                </a>
            </div>

            <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-3 sm:gap-4 reveal-stagger">
                @foreach($latestListings as $listing)
                    <x-listing-card :listing="$listing" />
                @endforeach
            </div>

            <div class="mt-8 text-center">
                <a href="{{ route('listings.index') }}" class="btn-view-all rounded-2xl">
                    {{ __('Voir toutes les annonces') }}
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
                    {{ __('Simple et rapide') }}
                </span>
                <h2 class="text-2xl sm:text-3xl font-black tracking-tight" style="color: #1B2A4A;">{{ __('Comment ca marche ?') }}</h2>
                <p class="mt-3 text-sm max-w-md mx-auto" style="color: #6B7B8D;">{{ __('Vendez ou achetez en 3 etapes simples') }}</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-8 sm:gap-6 relative reveal-stagger">
                <!-- Animated connecting line (desktop only) -->
                <div class="hidden md:block absolute connecting-line-animated" style="top: 48px; left: 20%; right: 20%;"></div>

                @foreach([
                    ['num' => '1', 'title' => __('Creez votre annonce'), 'desc' => __('Ajoutez des photos, une description et fixez votre prix en quelques minutes.'), 'gradient' => 'linear-gradient(135deg, #1B4F72, #2471A3)', 'icon' => 'M12 4v16m8-8H4', 'stepClass' => 'step-card-1'],
                    ['num' => '2', 'title' => __('Recevez des offres'), 'desc' => __('Les acheteurs vous contactent directement ou via notre systeme de mediation securise.'), 'gradient' => 'linear-gradient(135deg, #2471A3, #17A2B8)', 'icon' => 'M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z', 'stepClass' => 'step-card-2'],
                    ['num' => '3', 'title' => __('Concluez la vente'), 'desc' => __('Finalisez la transaction en toute securite avec un acheteur verifie.'), 'gradient' => 'linear-gradient(135deg, #17A2B8, #27AE60)', 'icon' => 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z', 'stepClass' => 'step-card-3'],
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
                    {{ __('Avantages') }}
                </span>
                <h2 class="text-2xl sm:text-3xl font-black tracking-tight" style="color: #1B2A4A;">{{ __('Pourquoi choisir AlBabor ?') }}</h2>
                <p class="mt-3 text-sm max-w-md mx-auto" style="color: #6B7B8D;">{{ __('La plateforme de confiance pour le nautisme en Algerie') }}</p>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-6 reveal-stagger">
                @foreach([
                    ['icon' => 'M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z', 'color' => '#17A2B8', 'gradient' => 'linear-gradient(135deg, #17A2B8, #1ABC9C)', 'title' => __('Securise'), 'desc' => __('Vendeurs verifies et systeme de mediation pour des transactions en toute confiance.')],
                    ['icon' => 'M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064M21 12a9 9 0 11-18 0 9 9 0 0118 0z', 'color' => '#27AE60', 'gradient' => 'linear-gradient(135deg, #27AE60, #2ECC71)', 'title' => __('Méditerranée'), 'desc' => __('Présent dans tous les pays de la Méditerranée — Algérie, Tunisie, Maroc, France, Italie et plus.')],
                    ['icon' => 'M13 10V3L4 14h7v7l9-11h-7z', 'color' => '#1B4F72', 'gradient' => 'linear-gradient(135deg, #1B4F72, #2471A3)', 'title' => __('Rapide'), 'desc' => __('Publiez votre annonce en quelques minutes avec notre formulaire simplifie.')],
                    ['icon' => 'M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z', 'color' => '#F39C12', 'gradient' => 'linear-gradient(135deg, #F39C12, #E67E22)', 'title' => __('Double devise'), 'desc' => __("Publiez en DZD ou EUR avec conversion automatique pour toucher plus d'acheteurs.")],
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
                <h2 class="text-xl sm:text-2xl lg:text-4xl font-black text-white mb-4 tracking-tight" style="text-shadow: 0 2px 16px rgba(0,0,0,0.2);">{{ __('Pret a naviguer ?') }}</h2>
                <p class="text-sm sm:text-base mb-10 max-w-md mx-auto leading-relaxed" style="color: rgba(255,255,255,0.6);">{{ __('Rejoignez des milliers de passionnes de nautisme sur AlBabor') }}</p>

                <div class="flex flex-col sm:flex-row justify-center gap-3">
                    @guest
                        <a href="{{ route('register') }}" class="btn-white-solid btn-cta-glow rounded-2xl">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"></path></svg>
                            {{ __('Creer un compte gratuit') }}
                        </a>
                        <a href="{{ route('listings.index') }}" class="btn-ghost btn-cta-glow px-8 py-4 text-sm font-semibold rounded-2xl">
                            {{ __('Explorer les annonces') }}
                        </a>
                    @else
                        <a href="{{ route('listings.create') }}" class="btn-white-solid btn-cta-glow rounded-2xl">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                            {{ __('Publier une annonce') }}
                        </a>
                    @endguest
                </div>
            </div>
        </div>
    </div>

    {{-- Payment section moved to footer in app-layout --}}

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
