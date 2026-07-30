@php
    // Le nom passe par le modèle : un vendeur qui publie sous « Invité »
    // arrive déjà anonymisé ici, photo comprise.
    $sellerName = $user->name ?: __('Vendeur');
    $isAnonymous = $user->identityMasked();
    $sellerInitial = $isAnonymous ? null : Str::upper(Str::substr($sellerName, 0, 1));
@endphp

<x-app-layout
    :title="$sellerName"
    :description="$sellerName . ' — ' . trans_choice('{0}aucune annonce|{1}:count annonce|[2,*]:count annonces', $stats['active_listings'], ['count' => $stats['active_listings']]) . ' sur AlBabor.'"
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
                <a href="{{ route('listings.index') }}" style="color: #9BA8B7;" class="hover:opacity-80 transition-opacity">{{ __('Annonces') }}</a>
                <svg class="w-4 h-4" style="color: #E0E6ED;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                </svg>
                <span style="color: #1B2A4A;" class="font-medium truncate">{{ $sellerName }}</span>
            </nav>
        </div>
    </div>

    <!-- Bandeau -->
    <div class="relative w-full overflow-hidden" style="height: 180px; background: linear-gradient(135deg, #1B4F72 0%, #2471A3 55%, #17A2B8 100%);">
        <div class="absolute top-0 right-0 w-72 h-72 rounded-full blur-3xl" style="background: rgba(255,255,255,0.08);"></div>
        <div class="absolute inset-0" style="background: linear-gradient(to top, rgba(0,0,0,0.35) 0%, transparent 70%);"></div>
    </div>

    <!-- En-tête vendeur -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="relative bg-white rounded-2xl shadow-sm -mt-14 p-5 sm:p-6 flex flex-col sm:flex-row sm:items-center gap-5">
            {{-- Avatar --}}
            <div class="flex-shrink-0 relative">
                @if($user->profile_picture_url)
                    <img src="{{ $user->profile_picture_url }}"
                         alt="{{ $sellerName }}"
                         class="w-24 h-24 rounded-2xl object-cover shadow-md ring-4 ring-white"
                         onerror="this.onerror=null;this.style.display='none';this.nextElementSibling.style.display='flex'">
                    <div class="w-24 h-24 rounded-2xl shadow-md ring-4 ring-white items-center justify-center" style="display:none; background: linear-gradient(135deg, #17A2B8, #2471A3);">
                        <span class="text-3xl font-extrabold text-white">{{ $sellerInitial }}</span>
                    </div>
                @else
                    <div class="w-24 h-24 rounded-2xl shadow-md ring-4 ring-white flex items-center justify-center" style="background: linear-gradient(135deg, #17A2B8, #2471A3);">
                        @if($isAnonymous)
                            {{-- Silhouette générique : une initiale trahirait le nom --}}
                            <svg class="w-11 h-11 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.8">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                            </svg>
                        @else
                            <span class="text-3xl font-extrabold text-white">{{ $sellerInitial }}</span>
                        @endif
                    </div>
                @endif

                @if($user->verified_badge)
                    <div class="absolute -bottom-1.5 -right-1.5 w-7 h-7 rounded-full flex items-center justify-center" style="background: #27AE60; border: 3px solid white;">
                        <svg class="w-3.5 h-3.5 text-white" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                    </div>
                @endif
            </div>

            {{-- Nom + badges --}}
            <div class="flex-1 min-w-0">
                <h1 class="text-xl sm:text-2xl font-extrabold truncate" style="color: #1B2A4A; letter-spacing: -0.01em;">
                    {{ $sellerName }}
                </h1>

                <div class="flex flex-wrap items-center gap-2 mt-2">
                    @if($user->verified_badge)
                        <span class="inline-flex items-center gap-1 text-xs font-semibold px-2.5 py-1 rounded-full" style="background: rgba(39,174,96,0.12); color: #1E8449;">
                            <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M6.267 3.455a3.066 3.066 0 001.745-.723 3.066 3.066 0 013.976 0 3.066 3.066 0 001.745.723 3.066 3.066 0 012.812 2.812c.051.643.304 1.254.723 1.745a3.066 3.066 0 010 3.976 3.066 3.066 0 00-.723 1.745 3.066 3.066 0 01-2.812 2.812 3.066 3.066 0 00-1.745.723 3.066 3.066 0 01-3.976 0 3.066 3.066 0 00-1.745-.723 3.066 3.066 0 01-2.812-2.812 3.066 3.066 0 00-.723-1.745 3.066 3.066 0 010-3.976 3.066 3.066 0 00.723-1.745 3.066 3.066 0 012.812-2.812zm7.44 5.252a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                            {{ __('Vendeur verifie') }}
                        </span>
                    @endif

                    <span class="inline-flex items-center gap-1 text-xs font-semibold px-2.5 py-1 rounded-full" style="background: rgba(27,79,114,0.10); color: #1B4F72;">
                        {{ $user->isVendor() ? __('messages.vendor') : __('Particulier') }}
                    </span>

                    @if($isAnonymous)
                        <span class="inline-flex items-center gap-1 text-xs font-semibold px-2.5 py-1 rounded-full" style="background: rgba(155,168,183,0.16); color: #6B7B8D;">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" />
                            </svg>
                            {{ __('Identite masquee') }}
                        </span>
                    @endif

                    <span class="inline-flex items-center gap-1 text-xs font-medium px-2.5 py-1 rounded-full" style="background: #F0F4F8; color: #6B7B8D;">
                        {{ __('Membre depuis') }} {{ $user->created_at?->translatedFormat('F Y') ?? '—' }}
                    </span>
                </div>
            </div>

            {{-- Compteurs --}}
            <div class="flex gap-3 sm:gap-4 flex-shrink-0">
                <div class="rounded-xl px-4 py-3 text-center min-w-[86px]" style="background: #F0F4F8;">
                    <p class="text-xl font-extrabold" style="color: #1B2A4A;">{{ $stats['active_listings'] }}</p>
                    <p class="text-[11px] font-medium" style="color: #6B7B8D;">{{ __('Annonces') }}</p>
                </div>
                <div class="rounded-xl px-4 py-3 text-center min-w-[86px]" style="background: #F0F4F8;">
                    <p class="text-xl font-extrabold" style="color: #1B2A4A;">{{ number_format($stats['total_views'], 0, ',', ' ') }}</p>
                    <p class="text-[11px] font-medium" style="color: #6B7B8D;">{{ __('Vues') }}</p>
                </div>
            </div>
        </div>

        @if($boutique)
            <a href="{{ route('boutiques.show', $boutique) }}"
               class="mt-4 flex items-center justify-between gap-3 bg-white rounded-2xl shadow-sm px-5 py-4 transition-all hover:-translate-y-0.5">
                <span class="flex items-center gap-3 min-w-0">
                    <span class="w-10 h-10 rounded-xl flex items-center justify-center flex-shrink-0" style="background: rgba(243,156,18,0.14);">
                        <svg class="w-5 h-5" style="color: #B97708;" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/>
                        </svg>
                    </span>
                    <span class="min-w-0">
                        <span class="block text-sm font-bold truncate" style="color: #1B2A4A;">{{ $boutique->shop_name }}</span>
                        <span class="block text-xs" style="color: #6B7B8D;">{{ __('Voir la boutique de pieces et moteurs') }}</span>
                    </span>
                </span>
                <svg class="w-5 h-5 flex-shrink-0" style="color: #9BA8B7;" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
                </svg>
            </a>
        @endif
    </div>

    <!-- Annonces -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
        <div class="flex items-center justify-between mb-6">
            <h2 class="text-lg sm:text-xl font-extrabold" style="color: #1B2A4A;">{{ __('Toutes ses annonces') }}</h2>
            @if($listings->total())
                <span class="text-sm" style="color: #6B7B8D;">
                    {{ trans_choice('{1}:count annonce|[2,*]:count annonces', $listings->total(), ['count' => $listings->total()]) }}
                </span>
            @endif
        </div>

        @if($listings->count())
            <div class="grid grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4 sm:gap-5">
                @foreach($listings as $listing)
                    <x-listing-card :listing="$listing" />
                @endforeach
            </div>

            <div class="mt-10">
                {{ $listings->links() }}
            </div>
        @else
            <div class="bg-white rounded-2xl shadow-sm py-20 px-6 text-center">
                <div class="w-16 h-16 mx-auto rounded-full flex items-center justify-center mb-4" style="background: #F0F4F8;">
                    <svg class="w-8 h-8" style="color: #9BA8B7;" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                    </svg>
                </div>
                <h3 class="text-base font-bold mb-1" style="color: #1B2A4A;">{{ __('Aucune annonce active pour le moment.') }}</h3>
                <p class="text-sm" style="color: #6B7B8D;">{{ __('Revenez bientot pour decouvrir ses prochaines annonces.') }}</p>
            </div>
        @endif
    </div>
</x-app-layout>
