<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <script>document.documentElement.classList.add('js');</script>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ isset($title) ? $title . ' — AlBabor' : 'AlBabor — Marketplace Nautique N°1 en Algérie' }}</title>
    <meta name="description" content="{{ $description ?? 'Achetez et vendez des bateaux, jet-skis, moteurs et pièces détachées en Algérie. La marketplace nautique de confiance avec paiement sécurisé.' }}">
    <meta name="keywords" content="bateau algérie, jet-ski, moteur hors-bord, vente bateau, achat bateau, nautisme algérie, albabor">
    <meta name="robots" content="index, follow">

    {{-- Open Graph --}}
    <meta property="og:type" content="website">
    <meta property="og:site_name" content="AlBabor">
    <meta property="og:title" content="{{ isset($title) ? $title . ' — AlBabor' : 'AlBabor — Marketplace Nautique N°1 en Algérie' }}">
    <meta property="og:description" content="{{ $description ?? 'Achetez et vendez des bateaux, jet-skis, moteurs et pièces détachées en Algérie.' }}">
    <meta property="og:image" content="{{ $ogImage ?? asset('images/og-image.png') }}">
    <meta property="og:url" content="{{ url()->current() }}">

    {{-- Twitter Card --}}
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="{{ isset($title) ? $title . ' — AlBabor' : 'AlBabor' }}">
    <meta name="twitter:description" content="{{ $description ?? 'La marketplace nautique N°1 en Algérie.' }}">
    <meta name="twitter:image" content="{{ $ogImage ?? asset('images/og-image.png') }}">

    <link rel="icon" type="image/png" href="/favicon.png?v=5">
    <link rel="apple-touch-icon" href="/favicon.png">
    <link rel="canonical" href="{{ url()->current() }}">

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700,800&display=swap" rel="stylesheet" />

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        [x-cloak] { display: none !important; }
        body { font-family: 'Inter', sans-serif; }
        /* Smooth scroll */
        html { scroll-behavior: smooth; }
    </style>
</head>
<body class="antialiased" style="background-color: #F0F4F8; color: #6B7B8D;">
    <!-- Navigation -->
    <nav class="glass-nav sticky top-0 z-50" x-data="{ mobileOpen: false, scrolled: false }" @scroll.window="scrolled = window.scrollY > 20" :class="{ 'scrolled': scrolled }">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16 items-center">
                <!-- Logo -->
                <div class="flex items-center">
                    <a href="{{ route('home') }}" class="flex items-center logo-hover gap-2">
                        <img src="/images/logo-full.png" alt="AlBabor" style="height: 30px;">
                    </a>
                </div>

                <!-- Desktop Navigation -->
                <div class="hidden md:flex items-center space-x-1">
                    <a href="{{ route('listings.index', ['category' => 'boat']) }}" class="nav-link-animated nav-item-boat px-3 py-2 rounded-xl font-medium text-sm inline-flex items-center gap-1.5">
                        <img src="/images/nav-boat.png" alt="" class="w-5 h-5 object-contain" style="filter: invert(30%) sepia(20%) saturate(800%) hue-rotate(170deg) brightness(90%);">{{ __('Bateaux') }}
                    </a>
                    <a href="{{ route('listings.index', ['category' => 'jetski']) }}" class="nav-link-animated nav-item-boat px-3 py-2 rounded-xl font-medium text-sm inline-flex items-center gap-1.5">
                        <img src="/images/nav-jetski.png" alt="" class="w-5 h-5 object-contain" style="filter: invert(30%) sepia(20%) saturate(800%) hue-rotate(170deg) brightness(90%);">{{ __('Jet-Skis') }}
                    </a>
                    <a href="{{ route('listings.index', ['category' => 'engine']) }}" class="nav-link-animated nav-item-boat px-3 py-2 rounded-xl font-medium text-sm inline-flex items-center gap-1.5">
                        <img src="/images/nav-moteur.png" alt="" class="w-5 h-5 object-contain" style="filter: invert(30%) sepia(20%) saturate(800%) hue-rotate(170deg) brightness(90%);">{{ __('Moteurs') }}
                    </a>
                    <a href="{{ route('listings.index', ['category' => 'parts']) }}" class="nav-link-animated nav-item-boat px-3 py-2 rounded-xl font-medium text-sm inline-flex items-center gap-1.5">
                        <img src="/images/nav-pieces.png" alt="" class="w-5 h-5 object-contain" style="filter: invert(30%) sepia(20%) saturate(800%) hue-rotate(170deg) brightness(90%);">{{ __('Pieces') }}
                    </a>
                    <div style="width: 1px; height: 20px; background: linear-gradient(180deg, transparent, #E0E6ED, transparent); margin: 0 6px;"></div>
                    @auth
                        @php $unreadMsgCount = auth()->user()->totalUnreadMessagesCount(); @endphp
                        <a href="{{ route('conversations.index') }}" class="nav-link-animated nav-item-default px-3.5 py-2 rounded-xl font-medium text-sm inline-flex items-center gap-1.5 relative">
                            <span class="relative">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
                                @if($unreadMsgCount > 0)
                                    <span class="absolute -top-1.5 -right-2 flex items-center justify-center">
                                        <span class="absolute inline-flex h-full w-full rounded-full opacity-60 animate-ping" style="background: #FF4757;"></span>
                                        <span class="relative inline-flex items-center justify-center rounded-full text-[9px] font-black text-white shadow-lg" style="background: linear-gradient(135deg, #FF4757, #FF6B81); min-width: 16px; height: 16px; padding: 0 4px; box-shadow: 0 2px 8px rgba(255,71,87,0.5);">{{ $unreadMsgCount }}</span>
                                    </span>
                                @endif
                            </span>
                            {{ __('messages.my_messages') }}
                        </a>
                    @endauth
                </div>

                <!-- Desktop Auth -->
                <div class="hidden md:flex items-center space-x-3">
                    <!-- Language Switcher -->
                    <div class="relative" x-data="{ langOpen: false }" @click.away="langOpen = false">
                        <button @click="langOpen = !langOpen" class="flex items-center gap-1.5 px-3 py-2 rounded-xl text-sm font-medium transition-colors hover:bg-gray-100" style="color: #6B7B8D;">
                            <span>{{ app()->getLocale() === 'fr' ? '🇫🇷' : (app()->getLocale() === 'en' ? '🇬🇧' : '🇪🇸') }}</span>
                            <span class="uppercase text-xs font-bold">{{ app()->getLocale() }}</span>
                            <svg class="w-3 h-3 transition-transform duration-200" :class="{ 'rotate-180': langOpen }" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                        </button>
                        <div x-show="langOpen" x-cloak
                             x-transition:enter="transition ease-out duration-150"
                             x-transition:enter-start="opacity-0 scale-95"
                             x-transition:enter-end="opacity-100 scale-100"
                             x-transition:leave="transition ease-in duration-100"
                             x-transition:leave-start="opacity-100 scale-100"
                             x-transition:leave-end="opacity-0 scale-95"
                             class="absolute right-0 mt-2 w-28 rounded-xl py-1.5 z-50 origin-top-right"
                             style="background: rgba(255,255,255,0.98); border: 1px solid rgba(224,230,237,0.8); box-shadow: 0 8px 24px rgba(27,79,114,0.1);">
                            @foreach(['fr' => '🇫🇷 Français', 'en' => '🇬🇧 English', 'es' => '🇪🇸 Español'] as $locale => $label)
                                <a href="{{ route('lang.switch', ['locale' => $locale]) }}"
                                   class="flex items-center gap-2 px-3 py-2 text-xs font-medium transition-colors hover:bg-gray-50 {{ app()->getLocale() === $locale ? 'font-bold' : '' }}"
                                   style="color: {{ app()->getLocale() === $locale ? '#1B4F72' : '#6B7B8D' }};">
                                    {{ $label }}
                                </a>
                            @endforeach
                        </div>
                    </div>
                    @guest
                        <a href="{{ route('login') }}" class="nav-item-login px-4 py-2 rounded-xl font-medium text-sm">{{ __('Connexion') }}</a>
                        <a href="{{ route('register') }}" class="btn-primary px-5 py-2.5 rounded-xl text-sm font-semibold">
                            {{ __("S'inscrire") }}
                        </a>
                    @else
                        <div class="relative" id="userDropdown" x-data="{ open: false }" @click.away="open = false">
                            <button @click="open = !open" class="nav-user-btn flex items-center space-x-2.5 px-3 py-2 rounded-xl">
                                @if(auth()->user()->profile_picture_url)
                                    <img src="{{ auth()->user()->profile_picture_url }}"
                                         alt="{{ auth()->user()->name }}"
                                         class="w-8 h-8 rounded-full object-cover"
                                         style="box-shadow: 0 2px 8px rgba(27,79,114,0.25); border: 2px solid rgba(23,162,184,0.2);">
                                @else
                                    <div class="w-8 h-8 rounded-full flex items-center justify-center" style="background: linear-gradient(135deg, #1B4F72, #17A2B8); box-shadow: 0 2px 8px rgba(27,79,114,0.25);">
                                        <span class="text-white font-semibold text-sm">{{ substr(auth()->user()->name, 0, 1) }}</span>
                                    </div>
                                @endif
                                <span class="font-medium text-sm" style="color: #1B2A4A;">{{ auth()->user()->name }}</span>
                                <svg class="w-4 h-4 transition-transform duration-300" :class="{ 'rotate-180': open }" style="color: #9BA8B7;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                </svg>
                            </button>

                            <div x-show="open" x-cloak
                                 x-transition:enter="transition ease-out duration-200"
                                 x-transition:enter-start="opacity-0 scale-95 -translate-y-1"
                                 x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                                 x-transition:leave="transition ease-in duration-150"
                                 x-transition:leave-start="opacity-100 scale-100 translate-y-0"
                                 x-transition:leave-end="opacity-0 scale-95 -translate-y-1"
                                 class="absolute right-0 mt-2.5 w-56 rounded-2xl py-2 z-50 origin-top-right"
                                 style="background: rgba(255,255,255,0.98); backdrop-filter: blur(20px); -webkit-backdrop-filter: blur(20px); border: 1px solid rgba(224,230,237,0.8); box-shadow: 0 20px 60px rgba(27,79,114,0.12), 0 4px 16px rgba(0,0,0,0.06);">
                                <div class="px-4 py-3 mb-1" style="border-bottom: 1px solid rgba(224,230,237,0.6);">
                                    <p class="text-xs font-medium" style="color: #9BA8B7;">{{ __('Connecte en tant que') }}</p>
                                    <p class="text-sm font-semibold truncate" style="color: #1B2A4A;">{{ auth()->user()->name }}</p>
                                </div>
                                <a href="{{ route('profile.show') }}" class="dropdown-item flex items-center gap-3 px-4 py-2.5 text-sm">
                                    <svg class="w-4 h-4" style="color: #9BA8B7;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                                    {{ __('Mon profil') }}
                                </a>
                                <a href="{{ route('listings.my') }}" class="dropdown-item flex items-center gap-3 px-4 py-2.5 text-sm">
                                    <svg class="w-4 h-4" style="color: #9BA8B7;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                                    {{ __('Mes annonces') }}
                                </a>
                                <a href="{{ route('conversations.index') }}" class="dropdown-item flex items-center gap-3 px-4 py-2.5 text-sm">
                                    <span class="relative">
                                        <svg class="w-4 h-4" style="color: {{ $unreadMsgCount > 0 ? '#FF4757' : '#9BA8B7' }};" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
                                        @if($unreadMsgCount > 0)
                                            <span class="absolute -top-1 -right-1 w-2 h-2 rounded-full" style="background: #FF4757; box-shadow: 0 0 6px rgba(255,71,87,0.6);"></span>
                                        @endif
                                    </span>
                                    {{ __('messages.my_messages') }}
                                    @if($unreadMsgCount > 0)
                                        <span class="inline-flex items-center justify-center rounded-full text-[9px] font-black text-white ml-auto" style="background: linear-gradient(135deg, #FF4757, #FF6B81); min-width: 20px; height: 20px; padding: 0 5px; box-shadow: 0 2px 8px rgba(255,71,87,0.4);">{{ $unreadMsgCount }}</span>
                                    @endif
                                </a>
                                <a href="{{ route('favorites.index') }}" class="dropdown-item flex items-center gap-3 px-4 py-2.5 text-sm">
                                    <svg class="w-4 h-4" style="color: #9BA8B7;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/></svg>
                                    {{ __('Mes favoris') }}
                                </a>
                                <div class="my-1.5 mx-3" style="height: 1px; background: linear-gradient(90deg, transparent, rgba(224,230,237,0.8), transparent);"></div>
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit" class="dropdown-item-danger flex items-center gap-3 w-full text-left px-4 py-2.5 text-sm">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                                        {{ __('Deconnexion') }}
                                    </button>
                                </form>
                            </div>
                        </div>

                        <a href="{{ route('listings.create') }}" class="btn-create-glow inline-flex items-center gap-2 px-5 py-2.5 rounded-xl text-sm font-semibold text-white">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                            {{ __('Creer') }}
                        </a>
                    @endguest
                </div>

                <!-- Mobile menu button -->
                <div class="md:hidden flex items-center gap-2">
                    @auth
                        <a href="{{ route('listings.create') }}" class="btn-create-glow inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl text-xs font-semibold text-white">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                            {{ __('Creer') }}
                        </a>
                    @endauth
                    <button @click="mobileOpen = !mobileOpen" class="nav-mobile-btn w-10 h-10 flex items-center justify-center rounded-xl relative">
                        <svg class="w-5 h-5 transition-transform duration-300" :class="{ 'rotate-90': mobileOpen }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path x-show="!mobileOpen" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                            <path x-show="mobileOpen" x-cloak stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                        @auth
                            @if($unreadMsgCount > 0)
                                <span class="absolute top-1 right-1 flex items-center justify-center">
                                    <span class="absolute inline-flex w-3 h-3 rounded-full opacity-50 animate-ping" style="background: #FF4757;"></span>
                                    <span class="relative inline-flex w-2.5 h-2.5 rounded-full" style="background: linear-gradient(135deg, #FF4757, #FF6B81); box-shadow: 0 0 6px rgba(255,71,87,0.5);"></span>
                                </span>
                            @endif
                        @endauth
                    </button>
                </div>
            </div>
        </div>

        <!-- Mobile menu -->
        <div x-show="mobileOpen" x-cloak
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 -translate-y-2"
             x-transition:enter-end="opacity-100 translate-y-0"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100 translate-y-0"
             x-transition:leave-end="opacity-0 -translate-y-2"
             class="md:hidden mobile-menu-glass origin-top">
            <div class="px-4 py-5 space-y-1">

                <!-- Category links with image icons -->
                <div class="grid grid-cols-4 gap-2 mb-4 pb-4" style="border-bottom: 1px solid rgba(224,230,237,0.6);">
                    @foreach([
                        'boat'   => ['label' => __('Bateaux'),  'img' => '/images/nav-boat.png'],
                        'jetski' => ['label' => __('Jet-Skis'), 'img' => '/images/nav-jetski.png'],
                        'engine' => ['label' => __('Moteurs'),  'img' => '/images/nav-moteur.png'],
                        'parts'  => ['label' => __('Pieces'),   'img' => '/images/nav-pieces.png'],
                    ] as $catKey => $cat)
                        <a href="{{ route('listings.index', ['category' => $catKey]) }}"
                           class="mobile-category-chip flex flex-col items-center gap-1.5 py-3 rounded-2xl text-center transition-all active:scale-95"
                           style="background: rgba(27,79,114,0.06);">
                            <img src="{{ $cat['img'] }}" alt="" class="w-7 h-7 object-contain" style="filter: invert(30%) sepia(20%) saturate(800%) hue-rotate(170deg) brightness(90%);">
                            <span class="text-[11px] font-bold" style="color: #1B4F72;">{{ $cat['label'] }}</span>
                        </a>
                    @endforeach
                </div>

                <!-- Mobile Language Switcher -->
                <div class="flex gap-2 mb-3 pb-3" style="border-bottom: 1px solid rgba(224,230,237,0.6);">
                    @foreach(['fr' => '🇫🇷 FR', 'en' => '🇬🇧 EN', 'es' => '🇪🇸 ES'] as $locale => $label)
                        <a href="{{ route('lang.switch', ['locale' => $locale]) }}"
                           class="flex-1 text-center py-2.5 rounded-xl text-xs font-bold transition-colors"
                           style="{{ app()->getLocale() === $locale ? 'background: rgba(27,79,114,0.1); color: #1B4F72;' : 'background: rgba(0,0,0,0.03); color: #9BA8B7;' }}">
                            {{ $label }}
                        </a>
                    @endforeach
                </div>

                @auth
                    {{-- User info header --}}
                    <div class="flex items-center gap-3 px-3 py-3 mb-1 rounded-2xl" style="background: rgba(27,79,114,0.04);">
                        @if(auth()->user()->profile_picture_url)
                            <img src="{{ auth()->user()->profile_picture_url }}" alt="{{ auth()->user()->name }}"
                                 class="w-10 h-10 rounded-full object-cover" style="border: 2px solid rgba(23,162,184,0.25); box-shadow: 0 2px 8px rgba(27,79,114,0.15);">
                        @else
                            <div class="w-10 h-10 rounded-full flex items-center justify-center" style="background: linear-gradient(135deg, #1B4F72, #17A2B8); box-shadow: 0 2px 8px rgba(27,79,114,0.25);">
                                <span class="text-white font-bold text-sm">{{ substr(auth()->user()->name, 0, 1) }}</span>
                            </div>
                        @endif
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-bold truncate" style="color: #1B2A4A;">{{ auth()->user()->name }}</p>
                            <p class="text-[11px] truncate" style="color: #9BA8B7;">{{ auth()->user()->email }}</p>
                        </div>
                    </div>

                    <a href="{{ route('favorites.index') }}" class="mobile-nav-item flex items-center gap-3 px-4 py-3 font-medium text-sm">
                        <div class="w-8 h-8 rounded-xl flex items-center justify-center" style="background: rgba(255,107,107,0.08);">
                            <svg class="w-4 h-4" style="color: #FF6B6B;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/></svg>
                        </div>
                        <span style="color: #1B2A4A;">{{ __('Favoris') }}</span>
                    </a>
                    <a href="{{ route('listings.my') }}" class="mobile-nav-item flex items-center gap-3 px-4 py-3 font-medium text-sm">
                        <div class="w-8 h-8 rounded-xl flex items-center justify-center" style="background: rgba(23,162,184,0.08);">
                            <svg class="w-4 h-4" style="color: #17A2B8;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                        </div>
                        <span style="color: #1B2A4A;">{{ __('Mes annonces') }}</span>
                    </a>
                    <a href="{{ route('conversations.index') }}" class="mobile-nav-item flex items-center gap-3 px-4 py-3 font-medium text-sm">
                        <div class="w-8 h-8 rounded-xl flex items-center justify-center relative" style="background: rgba(23,162,184,0.08);">
                            <svg class="w-4 h-4" style="color: {{ $unreadMsgCount > 0 ? '#FF4757' : '#17A2B8' }};" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
                            @if($unreadMsgCount > 0)
                                <span class="absolute -top-0.5 -right-0.5 w-2.5 h-2.5 rounded-full animate-pulse" style="background: #FF4757; box-shadow: 0 0 6px rgba(255,71,87,0.6); border: 1.5px solid white;"></span>
                            @endif
                        </div>
                        <span style="color: #1B2A4A;">{{ __('messages.my_messages') }}</span>
                        @if($unreadMsgCount > 0)
                            <span class="inline-flex items-center justify-center rounded-full text-[9px] font-black text-white ml-auto" style="background: linear-gradient(135deg, #FF4757, #FF6B81); min-width: 20px; height: 20px; padding: 0 5px; box-shadow: 0 2px 8px rgba(255,71,87,0.4);">{{ $unreadMsgCount }}</span>
                        @endif
                    </a>
                    <a href="{{ route('profile.show') }}" class="mobile-nav-item flex items-center gap-3 px-4 py-3 font-medium text-sm">
                        <div class="w-8 h-8 rounded-xl flex items-center justify-center" style="background: rgba(27,79,114,0.08);">
                            <svg class="w-4 h-4" style="color: #1B4F72;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                        </div>
                        <span style="color: #1B2A4A;">{{ __('Mon profil') }}</span>
                    </a>
                    <div class="pt-3 mt-2" style="border-top: 1px solid rgba(224,230,237,0.6);">
                        <a href="{{ route('listings.create') }}" class="flex items-center justify-center gap-2 px-4 py-3.5 rounded-2xl text-white font-semibold text-sm text-center btn-create-glow">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                            {{ __('Creer une annonce') }}
                        </a>
                    </div>
                    <div class="pt-2 mt-1">
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="flex items-center gap-3 w-full px-4 py-3 rounded-xl text-left font-medium text-sm transition-colors duration-200" style="color: #FF6B6B;">
                                <div class="w-8 h-8 rounded-xl flex items-center justify-center" style="background: rgba(255,107,107,0.08);">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                                </div>
                                {{ __('Deconnexion') }}
                            </button>
                        </form>
                    </div>
                @else
                    <a href="{{ route('login') }}" class="mobile-nav-item flex items-center gap-3 px-4 py-3 font-medium text-sm">
                        <div class="w-8 h-8 rounded-xl flex items-center justify-center" style="background: rgba(23,162,184,0.08);">
                            <svg class="w-4 h-4" style="color: #17A2B8;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"/></svg>
                        </div>
                        <span style="color: #1B2A4A;">{{ __('Connexion') }}</span>
                    </a>
                    <div class="pt-3">
                        <a href="{{ route('register') }}" class="flex items-center justify-center gap-2 px-4 py-3.5 rounded-2xl text-white font-semibold text-sm text-center btn-primary">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/></svg>
                            {{ __("S'inscrire") }}
                        </a>
                    </div>
                @endguest
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <main class="min-h-screen">
        {{ $slot }}
    </main>

    <!-- Footer -->
    <footer class="relative overflow-hidden" style="background: linear-gradient(180deg, #1B2A4A 0%, #0F1A2E 50%, #0A1220 100%);">
        <!-- Gradient accent top border -->
        <div style="height: 3px; background: linear-gradient(90deg, #1B4F72, #17A2B8, #5DADE2, #17A2B8, #1B4F72);"></div>

        <!-- Wave separator -->
        <div style="margin-top: -1px;">
            <svg viewBox="0 0 1440 60" fill="none" xmlns="http://www.w3.org/2000/svg" style="display: block; width: 100%;">
                <path d="M0 60V20C240 45 480 0 720 20C960 40 1200 10 1440 30V60H0Z" fill="#1B2A4A"/>
            </svg>
        </div>

        <!-- Subtle background decoration -->
        <div style="position: absolute; top: 50%; right: -5%; width: 300px; height: 300px; background: radial-gradient(circle, rgba(23,162,184,0.04) 0%, transparent 70%); border-radius: 50%; pointer-events: none;"></div>
        <div style="position: absolute; bottom: 10%; left: -3%; width: 200px; height: 200px; background: radial-gradient(circle, rgba(27,79,114,0.05) 0%, transparent 70%); border-radius: 50%; pointer-events: none;"></div>

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-10 pb-8 relative">
            <!-- Top section: Logo + Links -->
            <div class="flex flex-col lg:flex-row lg:items-start lg:justify-between gap-8 lg:gap-12 mb-8 lg:mb-14">
                <div class="max-w-sm">
                    <div class="mb-6">
                        <img src="/images/logo-full.png" alt="AlBabor" style="height: 34px; filter: brightness(0) invert(1) drop-shadow(0 2px 8px rgba(0,0,0,0.3));">
                    </div>
                    <p class="leading-relaxed text-sm mb-7" style="color: rgba(255,255,255,0.45); line-height: 1.7;">
                        La marketplace nautique n°1 en Algerie. Achetez et vendez bateaux, jet-skis, moteurs et pieces en toute confiance.
                    </p>
                    <!-- Social Links -->
                    <div class="flex items-center gap-3">
                        <a href="#" class="social-icon-hover w-10 h-10 rounded-xl flex items-center justify-center" style="background: rgba(255,255,255,0.06); color: rgba(255,255,255,0.4); border: 1px solid rgba(255,255,255,0.06);">
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/></svg>
                        </a>
                        <a href="#" class="social-icon-hover w-10 h-10 rounded-xl flex items-center justify-center" style="background: rgba(255,255,255,0.06); color: rgba(255,255,255,0.4); border: 1px solid rgba(255,255,255,0.06);">
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zM12 0C8.741 0 8.333.014 7.053.072 2.695.272.273 2.69.073 7.052.014 8.333 0 8.741 0 12c0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98C8.333 23.986 8.741 24 12 24c3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98C15.668.014 15.259 0 12 0zm0 5.838a6.162 6.162 0 100 12.324 6.162 6.162 0 000-12.324zM12 16a4 4 0 110-8 4 4 0 010 8zm6.406-11.845a1.44 1.44 0 100 2.881 1.44 1.44 0 000-2.881z"/></svg>
                        </a>
                        <a href="#" class="social-icon-hover w-10 h-10 rounded-xl flex items-center justify-center" style="background: rgba(255,255,255,0.06); color: rgba(255,255,255,0.4); border: 1px solid rgba(255,255,255,0.06);">
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M23.498 6.186a3.016 3.016 0 00-2.122-2.136C19.505 3.545 12 3.545 12 3.545s-7.505 0-9.377.505A3.017 3.017 0 00.502 6.186C0 8.07 0 12 0 12s0 3.93.502 5.814a3.016 3.016 0 002.122 2.136c1.871.505 9.376.505 9.376.505s7.505 0 9.377-.505a3.015 3.015 0 002.122-2.136C24 15.93 24 12 24 12s0-3.93-.502-5.814zM9.545 15.568V8.432L15.818 12l-6.273 3.568z"/></svg>
                        </a>
                        <a href="#" class="social-icon-hover w-10 h-10 rounded-xl flex items-center justify-center" style="background: rgba(255,255,255,0.06); color: rgba(255,255,255,0.4); border: 1px solid rgba(255,255,255,0.06);">
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981zm11.387-5.464c-.074-.124-.272-.198-.57-.347-.297-.149-1.758-.868-2.031-.967-.272-.099-.47-.149-.669.149-.198.297-.768.967-.941 1.165-.173.198-.347.223-.644.074-.297-.149-1.255-.462-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.297-.347.446-.521.151-.172.2-.296.3-.495.099-.198.05-.372-.025-.521-.075-.148-.669-1.611-.916-2.206-.242-.579-.487-.501-.669-.51l-.57-.01c-.198 0-.52.074-.792.372s-1.04 1.016-1.04 2.479 1.065 2.876 1.213 3.074c.149.198 2.095 3.2 5.076 4.487.709.306 1.263.489 1.694.626.712.226 1.36.194 1.872.118.571-.085 1.758-.719 2.006-1.413.248-.695.248-1.29.173-1.414z"/></svg>
                        </a>
                    </div>
                </div>

                <!-- Links columns -->
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6 lg:gap-14">
                    <div>
                        <h3 class="footer-heading font-semibold mb-5 text-xs uppercase tracking-widest" style="color: rgba(255,255,255,0.75);">{{ __('Categories') }}</h3>
                        <ul class="space-y-3">
                            <li><a href="{{ route('listings.index', ['category' => 'boat']) }}" class="footer-link text-sm flex items-center gap-2.5">
                                <span class="w-1.5 h-1.5 rounded-full flex-shrink-0" style="background: #1B4F72; box-shadow: 0 0 6px rgba(27,79,114,0.5);"></span>{{ __('Bateaux') }}
                            </a></li>
                            <li><a href="{{ route('listings.index', ['category' => 'jetski']) }}" class="footer-link text-sm flex items-center gap-2.5">
                                <span class="w-1.5 h-1.5 rounded-full flex-shrink-0" style="background: #17A2B8; box-shadow: 0 0 6px rgba(23,162,184,0.5);"></span>{{ __('Jet-Skis') }}
                            </a></li>
                            <li><a href="{{ route('listings.index', ['category' => 'engine']) }}" class="footer-link text-sm flex items-center gap-2.5">
                                <span class="w-1.5 h-1.5 rounded-full flex-shrink-0" style="background: #F39C12; box-shadow: 0 0 6px rgba(243,156,18,0.5);"></span>{{ __('Moteurs') }}
                            </a></li>
                            <li><a href="{{ route('listings.index', ['category' => 'parts']) }}" class="footer-link text-sm flex items-center gap-2.5">
                                <span class="w-1.5 h-1.5 rounded-full flex-shrink-0" style="background: #9B59B6; box-shadow: 0 0 6px rgba(155,89,182,0.5);"></span>{{ __('Pieces') }}
                            </a></li>
                        </ul>
                    </div>

                    <div>
                        <h3 class="footer-heading font-semibold mb-5 text-xs uppercase tracking-widest" style="color: rgba(255,255,255,0.75);">{{ __('Liens utiles') }}</h3>
                        <ul class="space-y-3">
                            <li><a href="{{ route('listings.index') }}" class="footer-link text-sm">{{ __('Toutes les annonces') }}</a></li>
                            @guest
                                <li><a href="{{ route('register') }}" class="footer-link text-sm">{{ __('Creer un compte') }}</a></li>
                                <li><a href="{{ route('login') }}" class="footer-link text-sm">{{ __('Connexion') }}</a></li>
                            @endguest
                            @auth
                                <li><a href="{{ route('listings.create') }}" class="footer-link text-sm">{{ __('Publier une annonce') }}</a></li>
                                <li><a href="{{ route('listings.my') }}" class="footer-link text-sm">{{ __('Mes annonces') }}</a></li>
                            @endauth
                        </ul>
                    </div>

                    <div>
                        <h3 class="footer-heading font-semibold mb-5 text-xs uppercase tracking-widest" style="color: rgba(255,255,255,0.75);">{{ __('Contact') }}</h3>
                        <ul class="space-y-3.5">
                            <li class="flex items-center gap-3 text-sm group" style="color: rgba(255,255,255,0.4);">
                                <div class="w-8 h-8 rounded-lg flex items-center justify-center flex-shrink-0" style="background: rgba(23,162,184,0.1);">
                                    <svg class="w-3.5 h-3.5" style="color: #17A2B8;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
                                </div>
                                <span class="break-all">contact@albabor.dz</span>
                            </li>
                            <li class="flex items-center gap-3 text-sm" style="color: rgba(255,255,255,0.4);">
                                <div class="w-8 h-8 rounded-lg flex items-center justify-center flex-shrink-0" style="background: rgba(23,162,184,0.1);">
                                    <svg class="w-3.5 h-3.5" style="color: #17A2B8;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                                </div>
                                Alger, Algerie
                            </li>
                            <li class="flex items-center gap-3 text-sm" style="color: rgba(255,255,255,0.4);">
                                <div class="w-8 h-8 rounded-lg flex items-center justify-center flex-shrink-0" style="background: rgba(23,162,184,0.1);">
                                    <svg class="w-3.5 h-3.5" style="color: #17A2B8;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path></svg>
                                </div>
                                +213791807475
                            </li>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- Payment Methods -->
            <div class="pt-8 pb-2 mb-8" style="border-top: 1px solid rgba(255,255,255,0.06);">
                <p class="text-xs font-semibold uppercase tracking-widest text-center mb-5" style="color: rgba(23,162,184,0.7); letter-spacing: 0.15em;">{{ __('Moyens de paiement acceptes') }}</p>
                <div class="flex flex-wrap items-center justify-center gap-5">
                    {{-- BaridiMob --}}
                    <div class="flex items-center gap-3 px-5 py-3 rounded-xl" style="background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.08);">
                        <img src="/images/baridimob.png" alt="BaridiMob" class="w-8 h-8 object-contain">
                        <div>
                            <p class="text-xs font-bold" style="color: rgba(255,255,255,0.75);">BaridiMob</p>
                            <p class="text-[10px]" style="color: rgba(255,255,255,0.35);">{{ __('Paiement mobile') }}</p>
                        </div>
                    </div>
                    {{-- PayPal --}}
                    <div class="flex items-center gap-3 px-5 py-3 rounded-xl" style="background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.08);">
                        <img src="/images/paypal.png" alt="PayPal" class="w-8 h-8 object-contain">
                        <div>
                            <p class="text-xs font-bold" style="color: rgba(255,255,255,0.75);">PayPal</p>
                            <p class="text-[10px]" style="color: rgba(255,255,255,0.35);">{{ __('International') }}</p>
                        </div>
                    </div>
                    {{-- Carte Bancaire --}}
                    <div class="flex items-center gap-3 px-5 py-3 rounded-xl" style="background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.08);">
                        <img src="/images/mastercard-visa.webp" alt="Mastercard Visa" class="w-8 h-8 object-contain">
                        <div>
                            <p class="text-xs font-bold" style="color: rgba(255,255,255,0.75);">Carte Bancaire</p>
                            <p class="text-[10px]" style="color: rgba(255,255,255,0.35);">Mastercard · Visa</p>
                        </div>
                    </div>
                </div>
                <p class="text-center text-[10px] mt-4" style="color: rgba(255,255,255,0.2);">{{ __('Paiement manuel securise · Approbation admin sous 24h · Justificatif requis') }}</p>
            </div>

            <!-- Bottom bar -->
            <div class="pt-7 flex flex-col sm:flex-row items-center justify-between gap-4" style="border-top: 1px solid rgba(255,255,255,0.06);">
                <p class="text-xs font-medium" style="color: rgba(255,255,255,0.25);">&copy; {{ date('Y') }} AlBabor. Tous droits reserves.</p>
            </div>
        </div>
    </footer>

    <!-- Scroll to Top Button -->
    <button class="scroll-to-top" id="scrollToTop" onclick="window.scrollTo({top: 0, behavior: 'smooth'})">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
            <path stroke-linecap="round" stroke-linejoin="round" d="M5 15l7-7 7 7"/>
        </svg>
    </button>

    <script>
        // Legacy dropdown support (kept for backward compat, Alpine.js is primary now)
        function toggleUserDropdown() {
            const menu = document.getElementById('userDropdownMenu');
            const arrow = document.getElementById('dropdownArrow');

            if (menu && menu.style.display === 'none') {
                menu.style.display = 'block';
                if (arrow) arrow.style.transform = 'rotate(180deg)';
            } else if (menu) {
                menu.style.display = 'none';
                if (arrow) arrow.style.transform = 'rotate(0deg)';
            }
        }

        // Close dropdown when clicking outside (legacy)
        document.addEventListener('click', function(event) {
            const dropdown = document.getElementById('userDropdown');
            const menu = document.getElementById('userDropdownMenu');

            if (dropdown && menu && !dropdown.contains(event.target)) {
                menu.style.display = 'none';
                const arrow = document.getElementById('dropdownArrow');
                if (arrow) arrow.style.transform = 'rotate(0deg)';
            }
        });

        // Scroll to Top button visibility
        const scrollBtn = document.getElementById('scrollToTop');
        if (scrollBtn) {
            window.addEventListener('scroll', function() {
                if (window.scrollY > 400) {
                    scrollBtn.classList.add('visible');
                } else {
                    scrollBtn.classList.remove('visible');
                }
            });
        }
    </script>
</body>
</html>
