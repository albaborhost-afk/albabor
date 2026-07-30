<x-app-layout>
    <style>
        /* Avatar floating animation */
        @keyframes avatar-float {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-8px); }
        }
        .avatar-float {
            animation: avatar-float 6s ease-in-out infinite;
        }

        /* Staggered fade-in-up for stats cards */
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        .animate-fade-in-up {
            opacity: 0;
            animation: fadeInUp 0.6s ease-out forwards;
        }

        /* Sidebar nav hover slide */
        .sidebar-nav-item {
            transition: transform 0.2s ease, background 0.2s ease;
        }
        .sidebar-nav-item:hover {
            transform: translateX(6px);
            background: rgba(27, 79, 114, 0.05);
        }

        /* Counter animation via JS */
        .count-up {
            display: inline-block;
        }

        [x-cloak] {
            display: none !important;
        }
    </style>

    @php
        $profileChecklist = [
            ['label' => __('messages.profile_check_name'), 'completed' => filled($user->name)],
            ['label' => __('messages.profile_check_email'), 'completed' => filled($user->email)],
            ['label' => __('messages.profile_check_phone'), 'completed' => filled($user->phone)],
            ['label' => __('messages.profile_check_picture'), 'completed' => filled($user->profile_picture_url)],
        ];
        $completedProfileItems = collect($profileChecklist)->where('completed', true)->count();
        $profileCompletion = (int) round(($completedProfileItems / max(count($profileChecklist), 1)) * 100);
    @endphp

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
                <span style="color: #1B2A4A;" class="font-medium">Mon Profil</span>
            </nav>
        </div>
    </div>

    <!-- HERO HEADER -->
    <div class="relative overflow-hidden" style="background: linear-gradient(135deg, #1B4F72 0%, #2471A3 50%, #17A2B8 100%);">
        <!-- Decorative circles -->
        <div class="absolute top-10 left-10 w-72 h-72 rounded-full blur-3xl" style="background: rgba(255,255,255,0.08);"></div>
        <div class="absolute bottom-10 right-10 w-96 h-96 rounded-full blur-3xl" style="background: rgba(255,255,255,0.05);"></div>

        <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-12 pb-32">
            <div class="flex flex-col items-center text-center">
                <!-- Avatar -->
                <div class="mb-6 relative">
                    <div class="p-1 rounded-full avatar-float" style="background: linear-gradient(135deg, rgba(255,255,255,0.4), rgba(255,255,255,0.1));">
                        @if(auth()->user()->profile_picture_url)
                            <img src="{{ auth()->user()->profile_picture_url }}"
                                 alt="{{ auth()->user()->name }}"
                                 class="w-32 h-32 rounded-full object-cover">
                        @else
                            <div class="w-32 h-32 rounded-full flex items-center justify-center" style="background: linear-gradient(135deg, #1B4F72, #17A2B8);">
                                <span class="text-5xl font-black text-white">
                                    {{ strtoupper(substr(auth()->user()->name, 0, 2)) }}
                                </span>
                            </div>
                        @endif
                    </div>
                    <!-- Upload Button -->
                    <button onclick="document.getElementById('profilePictureInput').click()"
                            class="absolute bottom-0 right-0 w-10 h-10 rounded-full flex items-center justify-center transition-all duration-300"
                            style="background: linear-gradient(135deg, #27AE60, #2ECC71); box-shadow: 0 4px 12px rgba(39,174,96,0.4);">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                    </button>
                    <form id="profilePictureForm" action="{{ route('profile.update') }}" method="POST" enctype="multipart/form-data" class="hidden">
                        @csrf
                        @method('PUT')
                        <input type="hidden" name="name" value="{{ auth()->user()->name }}">
                        <input type="hidden" name="phone" value="{{ auth()->user()->phone }}">
                        <input type="file" id="profilePictureInput" name="profile_picture" accept="image/*" onchange="document.getElementById('profilePictureForm').submit()">
                    </form>
                </div>

                <!-- Name and verification badge -->
                <div class="flex items-center gap-3 mb-2">
                    <h1 class="text-4xl md:text-5xl font-black text-white tracking-tight">
                        {{ auth()->user()->name }}
                    </h1>
                    @if(auth()->user()->verified_badge)
                        <div class="flex items-center justify-center w-10 h-10 rounded-full" style="background: linear-gradient(135deg, #27AE60, #2ECC71); box-shadow: 0 4px 15px rgba(39, 174, 96, 0.4);" title="Compte verifie">
                            <svg class="w-6 h-6 text-white" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                            </svg>
                        </div>
                    @endif
                </div>

                <!-- Member since -->
                <div class="px-6 py-2 rounded-full mb-4" style="background: rgba(255,255,255,0.15); border: 1px solid rgba(255,255,255,0.2);">
                    <p class="text-white/80 text-sm flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                        Membre depuis {{ auth()->user()->created_at->translatedFormat('F Y') }}
                    </p>
                </div>

                <!-- Status badges -->
                <div class="flex gap-3">
                    @if($user->isVendor())
                        <span class="px-4 py-2 rounded-full text-sm font-semibold text-white flex items-center gap-2" style="background: rgba(255,255,255,0.15); border: 1px solid rgba(255,255,255,0.2);">
                            <svg class="w-4 h-4" style="color: #F39C12;" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                            </svg>
                            Vendeur Pro
                        </span>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- STATS CARDS - Floating overlap -->
    <div class="relative -mt-24 z-20 max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 md:gap-6">
            <!-- Total Annonces -->
            <div class="bg-white rounded-2xl p-6 text-center transition-all duration-300 hover:-translate-y-1 animate-fade-in-up" style="box-shadow: 0 10px 25px rgba(0,0,0,0.06), 0 3px 8px rgba(0,0,0,0.03); animation-delay: 0.1s;">
                <div class="w-14 h-14 mx-auto rounded-xl flex items-center justify-center mb-4" style="background: rgba(27, 79, 114, 0.1);">
                    <svg class="w-7 h-7" style="color: #1B4F72;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                    </svg>
                </div>
                <p class="text-4xl font-black count-up" style="color: #1B4F72;" data-count="{{ auth()->user()->listings()->count() }}">
                    {{ auth()->user()->listings()->count() }}
                </p>
                <p class="text-sm mt-1 font-medium" style="color: #6B7B8D;">Total Annonces</p>
            </div>

            <!-- Vues Totales -->
            <div class="bg-white rounded-2xl p-6 text-center transition-all duration-300 hover:-translate-y-1 animate-fade-in-up" style="box-shadow: 0 10px 25px rgba(0,0,0,0.06), 0 3px 8px rgba(0,0,0,0.03); animation-delay: 0.2s;">
                <div class="w-14 h-14 mx-auto rounded-xl flex items-center justify-center mb-4" style="background: rgba(23, 162, 184, 0.1);">
                    <svg class="w-7 h-7" style="color: #17A2B8;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                    </svg>
                </div>
                <p class="text-4xl font-black count-up" style="color: #17A2B8;" data-count="{{ auth()->user()->listings()->sum('views_count') ?? 0 }}">
                    {{ number_format(auth()->user()->listings()->sum('views_count') ?? 0) }}
                </p>
                <p class="text-sm mt-1 font-medium" style="color: #6B7B8D;">Vues Totales</p>
            </div>

            <!-- Favoris Recus -->
            <div class="bg-white rounded-2xl p-6 text-center transition-all duration-300 hover:-translate-y-1 animate-fade-in-up" style="box-shadow: 0 10px 25px rgba(0,0,0,0.06), 0 3px 8px rgba(0,0,0,0.03); animation-delay: 0.3s;">
                <div class="w-14 h-14 mx-auto rounded-xl flex items-center justify-center mb-4" style="background: rgba(255, 107, 107, 0.1);">
                    <svg class="w-7 h-7" style="color: #FF6B6B;" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
                    </svg>
                </div>
                <p class="text-4xl font-black count-up" style="color: #FF6B6B;" data-count="{{ auth()->user()->listings()->withCount('favorites')->get()->sum('favorites_count') ?? 0 }}">
                    {{ number_format(auth()->user()->listings()->withCount('favorites')->get()->sum('favorites_count') ?? 0) }}
                </p>
                <p class="text-sm mt-1 font-medium" style="color: #6B7B8D;">Favoris Recus</p>
            </div>

            <!-- Badge Statut -->
            <div class="bg-white rounded-2xl p-6 text-center transition-all duration-300 hover:-translate-y-1 animate-fade-in-up" style="box-shadow: 0 10px 25px rgba(0,0,0,0.06), 0 3px 8px rgba(0,0,0,0.03); animation-delay: 0.4s;">
                <div class="w-14 h-14 mx-auto rounded-xl flex items-center justify-center mb-4" style="background: {{ auth()->user()->verified_badge ? 'rgba(39, 174, 96, 0.1)' : 'rgba(243, 156, 18, 0.1)' }};">
                    @if(auth()->user()->verified_badge)
                        <svg class="w-7 h-7" style="color: #27AE60;" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M6.267 3.455a3.066 3.066 0 001.745-.723 3.066 3.066 0 013.976 0 3.066 3.066 0 001.745.723 3.066 3.066 0 012.812 2.812c.051.643.304 1.254.723 1.745a3.066 3.066 0 010 3.976 3.066 3.066 0 00-.723 1.745 3.066 3.066 0 01-2.812 2.812 3.066 3.066 0 00-1.745.723 3.066 3.066 0 01-3.976 0 3.066 3.066 0 00-1.745-.723 3.066 3.066 0 01-2.812-2.812 3.066 3.066 0 00-.723-1.745 3.066 3.066 0 010-3.976 3.066 3.066 0 00.723-1.745 3.066 3.066 0 012.812-2.812zm7.44 5.252a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                        </svg>
                    @else
                        <svg class="w-7 h-7" style="color: #F39C12;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                    @endif
                </div>
                <p class="text-lg font-black" style="color: {{ auth()->user()->verified_badge ? '#27AE60' : '#F39C12' }};">
                    {{ auth()->user()->verified_badge ? 'Verifie' : 'Non Verifie' }}
                </p>
                <p class="text-sm mt-1 font-medium" style="color: #6B7B8D;">Badge Statut</p>
            </div>
        </div>
    </div>

    <!-- MAIN CONTENT -->
    <div style="background: #F0F4F8;" class="pt-12 pb-20">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

            <!-- Flash Messages -->
            @if(session('success'))
                <div class="mb-8 p-4 rounded-xl flex items-center gap-3" style="background: rgba(39, 174, 96, 0.08); border: 1px solid rgba(39, 174, 96, 0.2);">
                    <svg class="w-5 h-5 flex-shrink-0" style="color: #27AE60;" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                    </svg>
                    <p class="font-semibold" style="color: #27AE60;">{{ session('success') }}</p>
                </div>
            @endif

            @if(session('error'))
                <div class="mb-8 p-4 rounded-xl flex items-center gap-3" style="background: rgba(231, 76, 60, 0.08); border: 1px solid rgba(231, 76, 60, 0.2);">
                    <svg class="w-5 h-5 flex-shrink-0" style="color: #E74C3C;" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                    </svg>
                    <p class="font-semibold" style="color: #E74C3C;">{{ session('error') }}</p>
                </div>
            @endif

            @if($errors->any())
                <div class="mb-8 p-4 rounded-xl" style="background: rgba(231, 76, 60, 0.08); border: 1px solid rgba(231, 76, 60, 0.2);">
                    <p class="font-semibold mb-2" style="color: #E74C3C;">{{ __('messages.error') }}</p>
                    <ul class="list-disc list-inside text-sm" style="color: #E74C3C;">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="grid grid-cols-1 lg:grid-cols-4 gap-8">

                <!-- SIDEBAR -->
                <div class="lg:col-span-1 space-y-6">
                    <!-- Quick Links Card -->
                    <div class="bg-white rounded-2xl overflow-hidden" style="box-shadow: 0 10px 25px rgba(0,0,0,0.06), 0 3px 8px rgba(0,0,0,0.03);">
                        <div class="p-5" style="background: linear-gradient(135deg, #1B4F72, #17A2B8);">
                            <h3 class="text-lg font-bold text-white flex items-center gap-2">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                                </svg>
                                Acces Rapide
                            </h3>
                        </div>
                        <nav class="p-4 space-y-2">
                            <a href="{{ route('listings.my') }}" class="sidebar-nav-item flex items-center gap-3 p-3 rounded-xl group">
                                <div class="w-10 h-10 rounded-lg flex items-center justify-center transition-colors" style="background: rgba(27, 79, 114, 0.08);">
                                    <svg class="w-5 h-5" style="color: #1B4F72;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                                    </svg>
                                </div>
                                <span class="font-semibold" style="color: #1B2A4A;">Mes Annonces</span>
                            </a>
                            <a href="{{ route('favorites.index') }}" class="sidebar-nav-item flex items-center gap-3 p-3 rounded-xl group">
                                <div class="w-10 h-10 rounded-lg flex items-center justify-center" style="background: rgba(255, 107, 107, 0.08);">
                                    <svg class="w-5 h-5" style="color: #FF6B6B;" fill="currentColor" viewBox="0 0 24 24">
                                        <path d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
                                    </svg>
                                </div>
                                <span class="font-semibold" style="color: #1B2A4A;">Mes Favoris</span>
                            </a>
                            <a href="{{ route('payments.index') }}" class="sidebar-nav-item flex items-center gap-3 p-3 rounded-xl group">
                                <div class="w-10 h-10 rounded-lg flex items-center justify-center" style="background: rgba(39, 174, 96, 0.08);">
                                    <svg class="w-5 h-5" style="color: #27AE60;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                                    </svg>
                                </div>
                                <span class="font-semibold" style="color: #1B2A4A;">Paiements</span>
                            </a>
                            <a href="{{ route('mediation.index') }}" class="sidebar-nav-item flex items-center gap-3 p-3 rounded-xl group">
                                <div class="w-10 h-10 rounded-lg flex items-center justify-center" style="background: rgba(23, 162, 184, 0.08);">
                                    <svg class="w-5 h-5" style="color: #17A2B8;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                                    </svg>
                                </div>
                                <span class="font-semibold" style="color: #1B2A4A;">Mediation</span>
                            </a>
                        </nav>
                    </div>

                    <!-- Create Listing CTA -->
                    <div class="rounded-2xl p-6 text-white" style="background: linear-gradient(135deg, #1B4F72, #17A2B8); box-shadow: 0 10px 25px rgba(27, 79, 114, 0.25);">
                        <div class="w-14 h-14 rounded-xl flex items-center justify-center mb-4" style="background: rgba(255,255,255,0.2);">
                            <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                            </svg>
                        </div>
                        <h3 class="text-xl font-bold mb-2">Creer une Annonce</h3>
                        <p class="text-white/70 text-sm mb-4">Vendez votre bateau ou equipement rapidement</p>
                        <a href="{{ route('listings.create') }}" class="inline-flex items-center px-5 py-3 bg-white rounded-xl font-bold transition-all duration-300 hover:-translate-y-0.5" style="color: #1B4F72; box-shadow: 0 4px 15px rgba(0,0,0,0.1);">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                            </svg>
                            Publier
                        </a>
                    </div>
                </div>

                <!-- MAIN CONTENT AREA -->
                <div class="lg:col-span-3 space-y-8">

                    <!-- TABS -->
                    <div x-data="{ activeTab: 'info', editMode: {{ $errors->any() ? 'true' : 'false' }} }" class="space-y-6">
                        <!-- Tab Navigation -->
                        <div class="bg-white rounded-2xl p-2 flex flex-wrap gap-2" style="box-shadow: 0 10px 25px rgba(0,0,0,0.06), 0 3px 8px rgba(0,0,0,0.03);">
                            <button @click="activeTab = 'info'"
                                    :class="activeTab === 'info' ? 'text-white' : ''"
                                    :style="activeTab === 'info' ? 'background: linear-gradient(135deg, #1B4F72, #17A2B8); box-shadow: 0 4px 15px rgba(27, 79, 114, 0.3);' : 'color: #6B7B8D;'"
                                    class="flex-1 min-w-[140px] px-6 py-3 rounded-xl font-bold transition-all duration-300 flex items-center justify-center gap-2 hover:bg-gray-50">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                </svg>
                                Mes Informations
                            </button>
                            <button @click="activeTab = 'listings'"
                                    :class="activeTab === 'listings' ? 'text-white' : ''"
                                    :style="activeTab === 'listings' ? 'background: linear-gradient(135deg, #1B4F72, #17A2B8); box-shadow: 0 4px 15px rgba(27, 79, 114, 0.3);' : 'color: #6B7B8D;'"
                                    class="flex-1 min-w-[140px] px-6 py-3 rounded-xl font-bold transition-all duration-300 flex items-center justify-center gap-2 hover:bg-gray-50">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                                </svg>
                                Mes Annonces
                            </button>
                        </div>

                        <!-- TAB: Mes Informations -->
                        <div x-show="activeTab === 'info'" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 transform translate-y-4" x-transition:enter-end="opacity-100 transform translate-y-0">
                            <div class="bg-white rounded-2xl overflow-hidden" style="box-shadow: 0 10px 25px rgba(0,0,0,0.06), 0 3px 8px rgba(0,0,0,0.03);">
                                <div class="p-6 flex flex-col gap-4 md:flex-row md:items-center md:justify-between" style="background: linear-gradient(135deg, #1B4F72, #17A2B8);">
                                    <div>
                                        <h2 class="text-xl font-bold text-white flex items-center gap-3">
                                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                            </svg>
                                            {{ __('messages.personal_info') }}
                                        </h2>
                                        <p class="text-sm text-white/75 mt-2">{{ __('messages.edit_inline_description') }}</p>
                                    </div>

                                    <div class="flex flex-col sm:flex-row gap-3">
                                        <button
                                            type="button"
                                            @click="editMode = !editMode"
                                            class="inline-flex items-center justify-center gap-2 px-4 py-2 rounded-xl text-sm font-semibold transition-all duration-200 hover:-translate-y-0.5"
                                            style="background: rgba(255,255,255,0.2); color: white; backdrop-filter: blur(4px);"
                                        >
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                            </svg>
                                            <span x-text="editMode ? '{{ __('messages.close_editor') }}' : '{{ __('messages.edit_details') }}'"></span>
                                        </button>

                                        <a
                                            href="{{ route('profile.edit') }}"
                                            class="inline-flex items-center justify-center gap-2 px-4 py-2 rounded-xl text-sm font-semibold transition-all duration-200 hover:-translate-y-0.5"
                                            style="background: rgba(10, 23, 38, 0.18); color: white; border: 1px solid rgba(255,255,255,0.18);"
                                        >
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12H9m12 0A9 9 0 1112 3a9 9 0 019 9z" />
                                            </svg>
                                            {{ __('messages.open_full_editor') }}
                                        </a>
                                    </div>
                                </div>

                                <div class="p-6">
                                    <div class="grid grid-cols-1 xl:grid-cols-[1.4fr,0.9fr] gap-6 mb-6">
                                        <div class="rounded-2xl p-5" style="background: linear-gradient(180deg, rgba(27,79,114,0.06), rgba(23,162,184,0.03)); border: 1px solid rgba(23,162,184,0.15);">
                                            <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                                                <div>
                                                    <p class="text-sm font-semibold" style="color: #1B4F72;">{{ __('messages.profile_completion') }}</p>
                                                    <h3 class="text-3xl font-black mt-1" style="color: #1B2A4A;">{{ $profileCompletion }}%</h3>
                                                    <p class="text-sm mt-2" style="color: #6B7B8D;">{{ __('messages.profile_completion_hint') }}</p>
                                                </div>
                                                <span class="inline-flex items-center gap-2 px-4 py-2 rounded-full text-sm font-semibold self-start md:self-center" style="background: {{ $profileCompletion === 100 ? 'rgba(39, 174, 96, 0.12)' : 'rgba(243, 156, 18, 0.12)' }}; color: {{ $profileCompletion === 100 ? '#27AE60' : '#F39C12' }};">
                                                    <span class="w-2.5 h-2.5 rounded-full" style="background: currentColor;"></span>
                                                    {{ $profileCompletion === 100 ? __('messages.completion_ready') : __('messages.completion_in_progress') }}
                                                </span>
                                            </div>

                                            <div class="mt-5 h-3 rounded-full overflow-hidden" style="background: rgba(27, 79, 114, 0.08);">
                                                <div class="h-full rounded-full transition-all duration-500" style="width: {{ $profileCompletion }}%; background: linear-gradient(90deg, #1B4F72, #17A2B8);"></div>
                                            </div>

                                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 mt-5">
                                                @foreach($profileChecklist as $item)
                                                    <div class="flex items-center gap-3 rounded-xl px-4 py-3" style="background: white; border: 1px solid rgba(224, 230, 237, 0.95);">
                                                        <div class="w-9 h-9 rounded-full flex items-center justify-center" style="background: {{ $item['completed'] ? 'rgba(39, 174, 96, 0.12)' : 'rgba(107, 123, 141, 0.12)' }}; color: {{ $item['completed'] ? '#27AE60' : '#6B7B8D' }};">
                                                            @if($item['completed'])
                                                                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                                                </svg>
                                                            @else
                                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                                </svg>
                                                            @endif
                                                        </div>
                                                        <div>
                                                            <p class="text-sm font-semibold" style="color: #1B2A4A;">{{ $item['label'] }}</p>
                                                            <p class="text-xs" style="color: #6B7B8D;">{{ $item['completed'] ? __('messages.completed') : __('messages.completion_in_progress') }}</p>
                                                        </div>
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>

                                        <div class="rounded-2xl p-5 text-white" style="background: linear-gradient(160deg, #102B45 0%, #1B4F72 55%, #17A2B8 120%); box-shadow: inset 0 1px 0 rgba(255,255,255,0.08);">
                                            <p class="text-sm font-semibold text-white/75">{{ __('messages.profile_snapshot') }}</p>
                                            <div class="mt-4 space-y-4">
                                                <div class="rounded-2xl px-4 py-3" style="background: rgba(255,255,255,0.08); border: 1px solid rgba(255,255,255,0.08);">
                                                    <p class="text-xs uppercase tracking-[0.2em] text-white/55">{{ __('messages.profile_status') }}</p>
                                                    <p class="text-xl font-bold mt-2">{{ $user->isVendor() ? __('messages.vendor') : 'Particulier' }}</p>
                                                    <p class="text-sm text-white/70 mt-2">{{ __('messages.manage_from_here') }}</p>
                                                </div>

                                                <div class="grid grid-cols-2 gap-3">
                                                    <div class="rounded-2xl px-4 py-3" style="background: rgba(255,255,255,0.08);">
                                                        <p class="text-xs text-white/55">{{ __('messages.buyer_trust') }}</p>
                                                        <p class="text-lg font-bold mt-2">{{ $user->verified_badge ? __('messages.verified') : __('messages.not_verified') }}</p>
                                                    </div>
                                                    <div class="rounded-2xl px-4 py-3" style="background: rgba(255,255,255,0.08);">
                                                        <p class="text-xs text-white/55">{{ __('messages.contact_ready') }}</p>
                                                        <p class="text-lg font-bold mt-2">{{ filled($user->phone) ? __('messages.completed') : __('messages.completion_in_progress') }}</p>
                                                    </div>
                                                </div>

                                                <div class="rounded-2xl px-4 py-4" style="background: rgba(255,255,255,0.08); border: 1px dashed rgba(255,255,255,0.18);">
                                                    @if(!filled($user->phone))
                                                        <p class="text-sm font-semibold">{{ __('messages.phone_missing') }}</p>
                                                    @elseif(!filled($user->profile_picture_url))
                                                        <p class="text-sm font-semibold">{{ __('messages.picture_missing') }}</p>
                                                    @elseif(!$user->verified_badge)
                                                        <p class="text-sm font-semibold">{{ __('messages.verification_hint') }}</p>
                                                    @else
                                                        <p class="text-sm font-semibold">{{ __('messages.completion_ready') }}</p>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <dl class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                        <div class="rounded-xl p-5" style="background: #F0F4F8;">
                                            <dt class="text-sm mb-2 flex items-center gap-2" style="color: #6B7B8D;">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                                </svg>
                                                Nom Complet
                                            </dt>
                                            <dd class="text-lg font-bold" style="color: #1B2A4A;">{{ $user->name }}</dd>
                                            @if($user->hidesName())
                                                <dd class="mt-2">
                                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold" style="background: rgba(23,162,184,0.10); color: #117A8B;">
                                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                                            <path stroke-linecap="round" stroke-linejoin="round" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" />
                                                        </svg>
                                                        {{ __('messages.hide_name_badge') }}
                                                    </span>
                                                    <p class="text-xs mt-1.5" style="color: #9BA8B7;">{{ __('Les acheteurs voient') }} « {{ \App\Models\User::ANONYMOUS_NAME }} »</p>
                                                </dd>
                                            @endif
                                        </div>

                                        <div class="rounded-xl p-5" style="background: #F0F4F8;">
                                            <dt class="text-sm mb-2 flex items-center gap-2" style="color: #6B7B8D;">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                                </svg>
                                                Adresse Email
                                            </dt>
                                            <dd class="text-lg font-bold" style="color: #1B2A4A;">{{ $user->email }}</dd>
                                        </div>

                                        <div class="rounded-xl p-5" style="background: #F0F4F8;">
                                            <dt class="text-sm mb-2 flex items-center gap-2" style="color: #6B7B8D;">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                                                </svg>
                                                Telephone
                                            </dt>
                                            <dd class="text-lg font-bold" style="color: #1B2A4A;">
                                                @if(filled($user->phone))
                                                    @php
                                                        $pcInfo = \App\Support\PhoneCountry::info($user->phone_country_code);
                                                    @endphp
                                                    @if($pcInfo)
                                                        <span class="mr-1">{{ $pcInfo['flag'] }}</span><span class="text-sm font-semibold mr-1" style="color: #6B7B8D;">{{ $user->phone_country_code }}</span>{{ $user->phone }}
                                                    @else
                                                        {{ $user->phone }}
                                                    @endif
                                                @else
                                                    Non renseigne
                                                @endif
                                            </dd>
                                        </div>

                                        <div class="rounded-xl p-5" style="background: #F0F4F8;">
                                            <dt class="text-sm mb-2 flex items-center gap-2" style="color: #6B7B8D;">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                                </svg>
                                                {{ __('messages.member_since') }}
                                            </dt>
                                            <dd class="text-lg font-bold" style="color: #1B2A4A;">{{ $user->created_at->translatedFormat('d F Y') }}</dd>
                                        </div>

                                        <div class="rounded-xl p-5" style="background: #F0F4F8;">
                                            <dt class="text-sm mb-2 flex items-center gap-2" style="color: #6B7B8D;">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                                                </svg>
                                                Statut Verification
                                            </dt>
                                            <dd class="flex items-center gap-2">
                                                @if($user->verified_badge)
                                                    <span class="inline-flex items-center gap-1 px-3 py-1 rounded-full text-sm font-semibold" style="background: rgba(39, 174, 96, 0.1); color: #27AE60;">
                                                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                                        </svg>
                                                        {{ __('messages.verified_account') }}
                                                    </span>
                                                @else
                                                    <span class="inline-flex items-center gap-1 px-3 py-1 rounded-full text-sm font-semibold" style="background: rgba(243, 156, 18, 0.1); color: #F39C12;">
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                        </svg>
                                                        {{ __('messages.not_verified') }}
                                                    </span>
                                                @endif
                                            </dd>
                                        </div>

                                        <div class="rounded-xl p-5" style="background: #F0F4F8;">
                                            <dt class="text-sm mb-2 flex items-center gap-2" style="color: #6B7B8D;">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5.121 17.804A13.937 13.937 0 0112 16c2.5 0 4.847.655 6.879 1.804M15 10a3 3 0 11-6 0 3 3 0 016 0zm6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                </svg>
                                                Type de Compte
                                            </dt>
                                            <dd class="flex items-center gap-2">
                                                @if($user->isVendor())
                                                    <span class="inline-flex items-center gap-1 px-3 py-1 rounded-full text-sm font-semibold" style="background: rgba(27, 79, 114, 0.1); color: #1B4F72;">
                                                        <svg class="w-4 h-4" style="color: #F39C12;" fill="currentColor" viewBox="0 0 20 20">
                                                            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                                                        </svg>
                                                        {{ __('messages.vendor') }}
                                                    </span>
                                                @else
                                                    <span class="inline-flex items-center gap-1 px-3 py-1 rounded-full text-sm font-semibold" style="background: rgba(107, 123, 141, 0.1); color: #6B7B8D;">
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                                        </svg>
                                                        Particulier
                                                    </span>
                                                @endif
                                            </dd>
                                        </div>
                                    </dl>

                                    <div x-show="editMode" x-cloak x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4" x-transition:enter-end="opacity-100 translate-y-0" class="mt-6 pt-6" style="border-top: 1px solid #E0E6ED;">
                                        <form action="{{ route('profile.update') }}" method="POST" class="rounded-2xl p-6" style="background: linear-gradient(180deg, #F8FBFD 0%, #FFFFFF 100%); border: 1px solid #E0E6ED;">
                                            @csrf
                                            @method('PUT')

                                            <div class="flex flex-col gap-4 md:flex-row md:items-start md:justify-between mb-6">
                                                <div>
                                                    <h3 class="text-lg font-bold" style="color: #1B2A4A;">{{ __('messages.edit_profile') }}</h3>
                                                    <p class="text-sm mt-1" style="color: #6B7B8D;">{{ __('messages.manage_from_here') }}</p>
                                                </div>

                                                <button
                                                    type="button"
                                                    @click="editMode = false"
                                                    class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-sm font-semibold transition-colors"
                                                    style="background: #FFFFFF; color: #6B7B8D; border: 1px solid #E0E6ED;"
                                                >
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                                    </svg>
                                                    {{ __('messages.close_editor') }}
                                                </button>
                                            </div>

                                            @include('profile.partials.personal-info-fields', [
                                                'user' => $user,
                                                'phoneId' => 'profile_phone_inline',
                                            ])

                                            <div class="mt-6 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                                                <p class="text-sm" style="color: #6B7B8D;">{{ __('messages.edit_inline_description') }}</p>

                                                <div class="flex flex-col sm:flex-row gap-3">
                                                    <a
                                                        href="{{ route('profile.edit') }}"
                                                        class="inline-flex items-center justify-center px-5 py-3 rounded-xl font-semibold transition-all duration-200 hover:-translate-y-0.5"
                                                        style="color: #1B4F72; border: 1px solid rgba(27, 79, 114, 0.15); background: white;"
                                                    >
                                                        {{ __('messages.open_full_editor') }}
                                                    </a>

                                                    <button
                                                        type="submit"
                                                        class="inline-flex items-center justify-center px-5 py-3 rounded-xl font-bold text-white transition-all duration-300 hover:-translate-y-0.5"
                                                        style="background: linear-gradient(135deg, #1B4F72, #17A2B8); box-shadow: 0 8px 25px rgba(27, 79, 114, 0.25);"
                                                    >
                                                        {{ __('messages.save_profile') }}
                                                    </button>
                                                </div>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- TAB: Mes Annonces -->
                        <div x-show="activeTab === 'listings'" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 transform translate-y-4" x-transition:enter-end="opacity-100 transform translate-y-0">
                            <div class="bg-white rounded-2xl overflow-hidden" style="box-shadow: 0 10px 25px rgba(0,0,0,0.06), 0 3px 8px rgba(0,0,0,0.03);">
                                <div class="p-6 flex items-center justify-between" style="background: linear-gradient(135deg, #1B4F72, #17A2B8);">
                                    <h2 class="text-xl font-bold text-white flex items-center gap-3">
                                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                                        </svg>
                                        Mes Annonces
                                    </h2>
                                    <a href="{{ route('listings.create') }}" class="inline-flex items-center px-4 py-2 rounded-lg font-semibold transition-all duration-200 text-white" style="background: rgba(255,255,255,0.2);">
                                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                                        </svg>
                                        Nouvelle Annonce
                                    </a>
                                </div>
                                <div class="p-6">
                                    @if(auth()->user()->listings()->count() > 0)
                                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                            @foreach(auth()->user()->listings()->latest()->take(6)->get() as $listing)
                                                <div class="bg-white rounded-xl overflow-hidden transition-all duration-300 hover:-translate-y-1" style="border: 1px solid #E0E6ED; box-shadow: 0 4px 12px rgba(0,0,0,0.04);">
                                                    <div class="relative h-40 overflow-hidden">
                                                        @if($listing->media->count() > 0)
                                                            <img src="{{ $listing->media->first()->thumbnail_url ?? $listing->media->first()->url }}" alt="{{ $listing->title }}" class="w-full h-full object-cover transition-transform duration-500 hover:scale-105"
                                                                 onerror="this.onerror=null;this.style.display='none';this.nextElementSibling.style.display='flex'">
                                                            <div class="w-full h-full items-center justify-center" style="background: #F0F4F8; display: none;">
                                                                <svg class="w-16 h-16" style="color: #E0E6ED;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                                                </svg>
                                                            </div>
                                                        @else
                                                            <div class="w-full h-full flex items-center justify-center" style="background: #F0F4F8;">
                                                                <svg class="w-16 h-16" style="color: #E0E6ED;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                                                </svg>
                                                            </div>
                                                        @endif
                                                        <div class="absolute top-3 right-3">
                                                            @php
                                                                $statusStyles = [
                                                                    'active' => 'background: rgba(39, 174, 96, 0.9); color: white;',
                                                                    'pending_review' => 'background: rgba(243, 156, 18, 0.9); color: white;',
                                                                    'sold' => 'background: rgba(23, 162, 184, 0.9); color: white;',
                                                                ];
                                                                $style = $statusStyles[$listing->status] ?? 'background: rgba(107, 123, 141, 0.9); color: white;';
                                                            @endphp
                                                            <span class="px-3 py-1 text-xs font-bold rounded-full" style="{{ $style }}">
                                                                {{ $listing->status === 'active' ? 'Active' : ucfirst(str_replace('_', ' ', $listing->status)) }}
                                                            </span>
                                                        </div>
                                                    </div>
                                                    <div class="p-4">
                                                        <h3 class="font-bold truncate mb-2" style="color: #1B2A4A;">{{ $listing->title }}</h3>
                                                        <p class="text-lg font-black" style="color: #1B4F72;">{{ $listing->formatted_price }}</p>
                                                        <div class="flex items-center justify-between mt-3 pt-3" style="border-top: 1px solid #E0E6ED;">
                                                            <div class="flex items-center gap-4 text-sm" style="color: #9BA8B7;">
                                                                <span class="flex items-center gap-1">
                                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                                                    </svg>
                                                                    {{ $listing->views_count ?? 0 }}
                                                                </span>
                                                                <span class="flex items-center gap-1">
                                                                    <svg class="w-4 h-4" style="color: #FF6B6B;" fill="currentColor" viewBox="0 0 24 24">
                                                                        <path d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
                                                                    </svg>
                                                                    {{ $listing->favorites_count ?? 0 }}
                                                                </span>
                                                            </div>
                                                            <a href="{{ route('listings.edit', $listing) }}" class="font-semibold text-sm transition-colors" style="color: #17A2B8;">
                                                                Modifier
                                                            </a>
                                                        </div>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                        @if(auth()->user()->listings()->count() > 6)
                                            <div class="mt-6 text-center">
                                                <a href="{{ route('listings.my') }}" class="inline-flex items-center px-6 py-3 gradient-primary text-white rounded-xl font-bold transition-all duration-300 hover:-translate-y-0.5" style="box-shadow: 0 8px 25px rgba(27, 79, 114, 0.25);">
                                                    Voir toutes mes annonces
                                                    <svg class="w-5 h-5 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3" />
                                                    </svg>
                                                </a>
                                            </div>
                                        @endif
                                    @else
                                        <div class="text-center py-12">
                                            <div class="w-24 h-24 mx-auto rounded-full flex items-center justify-center mb-6" style="background: rgba(23, 162, 184, 0.08);">
                                                <svg class="w-12 h-12" style="color: #17A2B8;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                                                </svg>
                                            </div>
                                            <h3 class="text-xl font-bold mb-2" style="color: #1B2A4A;">Aucune annonce</h3>
                                            <p class="mb-6" style="color: #6B7B8D;">Vous n'avez pas encore publie d'annonce</p>
                                            <a href="{{ route('listings.create') }}" class="inline-flex items-center px-6 py-3 gradient-primary text-white rounded-xl font-bold transition-all duration-300 hover:-translate-y-0.5" style="box-shadow: 0 8px 25px rgba(27, 79, 114, 0.25);">
                                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                                                </svg>
                                                Creer ma premiere annonce
                                            </a>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Counter animation for stat numbers
            const counters = document.querySelectorAll('.count-up[data-count]');
            const observer = new IntersectionObserver(function (entries) {
                entries.forEach(function (entry) {
                    if (entry.isIntersecting) {
                        const el = entry.target;
                        const target = parseInt(el.getAttribute('data-count')) || 0;
                        if (target === 0) return;
                        const duration = 1200;
                        const startTime = performance.now();
                        function update(currentTime) {
                            const elapsed = currentTime - startTime;
                            const progress = Math.min(elapsed / duration, 1);
                            // Ease-out curve
                            const eased = 1 - Math.pow(1 - progress, 3);
                            const current = Math.floor(eased * target);
                            el.textContent = current.toLocaleString('fr-FR');
                            if (progress < 1) {
                                requestAnimationFrame(update);
                            } else {
                                el.textContent = target.toLocaleString('fr-FR');
                            }
                        }
                        requestAnimationFrame(update);
                        observer.unobserve(el);
                    }
                });
            }, { threshold: 0.3 });

            counters.forEach(function (counter) {
                observer.observe(counter);
            });
        });
    </script>
</x-app-layout>
