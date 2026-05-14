<x-app-layout
    :title="$vendorProfile->shop_name"
    :description="'Boutique ' . $vendorProfile->shop_name . ' — moteurs et pieces detachees nautiques en Algerie.'"
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
                <a href="{{ route('boutiques.index') }}" style="color: #9BA8B7;" class="hover:opacity-80 transition-opacity">{{ __('Nos boutiques') }}</a>
                <svg class="w-4 h-4" style="color: #E0E6ED;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                </svg>
                <span style="color: #1B2A4A;" class="font-medium">{{ $vendorProfile->shop_name }}</span>
            </nav>
        </div>
    </div>

    <!-- Banner -->
    <div class="relative w-full overflow-hidden" style="height: 220px;">
        @if($vendorProfile->banner_url)
            <img src="{{ $vendorProfile->banner_url }}"
                 alt="{{ $vendorProfile->shop_name }}"
                 class="w-full h-full object-cover"
                 onerror="this.onerror=null;this.style.display='none';this.parentElement.style.background='linear-gradient(135deg, #17A2B8 0%, #F39C12 100%)'">
        @else
            <div class="w-full h-full" style="background: linear-gradient(135deg, #17A2B8 0%, #F39C12 100%);"></div>
        @endif
        <div class="absolute inset-0" style="background: linear-gradient(to top, rgba(0,0,0,0.45) 0%, rgba(0,0,0,0.05) 60%, transparent 100%);"></div>
    </div>

    <!-- Shop Header -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="relative bg-white rounded-2xl shadow-sm -mt-16 p-5 sm:p-6 flex flex-col sm:flex-row sm:items-center gap-5">
            {{-- Logo --}}
            <div class="flex-shrink-0">
                @if($vendorProfile->logo_url)
                    <img src="{{ $vendorProfile->logo_url }}"
                         alt="{{ $vendorProfile->shop_name }}"
                         class="w-24 h-24 rounded-2xl object-cover shadow-md ring-4 ring-white"
                         onerror="this.onerror=null;this.style.display='none';this.nextElementSibling.style.display='flex'">
                    <div class="w-24 h-24 rounded-2xl shadow-md ring-4 ring-white items-center justify-center" style="display:none; background: linear-gradient(135deg, #17A2B8, #2471A3);">
                        <span class="text-3xl font-extrabold text-white">{{ Str::upper(Str::substr($vendorProfile->shop_name, 0, 1)) }}</span>
                    </div>
                @else
                    <div class="w-24 h-24 rounded-2xl shadow-md ring-4 ring-white flex items-center justify-center" style="background: linear-gradient(135deg, #17A2B8, #2471A3);">
                        <span class="text-3xl font-extrabold text-white">{{ Str::upper(Str::substr($vendorProfile->shop_name, 0, 1)) }}</span>
                    </div>
                @endif
            </div>

            {{-- Name + badges --}}
            <div class="flex-1 min-w-0">
                <h1 class="text-xl sm:text-2xl font-extrabold" style="color: #1B2A4A; letter-spacing: -0.01em;">
                    {{ $vendorProfile->shop_name }}
                </h1>
                <div class="flex flex-wrap items-center gap-2 mt-2">
                    @if($vendorProfile->wilaya)
                        <span class="inline-flex items-center gap-1 text-xs font-semibold px-2.5 py-1 rounded-full" style="background: rgba(23,162,184,0.12); color: #117A8B;">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                            </svg>
                            {{ $vendorProfile->wilaya }}
                        </span>
                    @endif
                    <span class="inline-flex items-center gap-1 text-xs font-semibold px-2.5 py-1 rounded-full" style="background: rgba(243,156,18,0.14); color: #B97708;">
                        <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                        {{ __('Boutique professionnelle') }}
                    </span>
                </div>
            </div>
        </div>

        {{-- Description + Contact --}}
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-5 mt-5">
            {{-- Description --}}
            <div class="lg:col-span-2 bg-white rounded-2xl shadow-sm p-5 sm:p-6">
                <h2 class="text-sm font-bold uppercase tracking-wide mb-3" style="color: #6B7B8D;">{{ __('A propos') }}</h2>
                @if(filled($vendorProfile->description))
                    <p class="text-sm leading-relaxed whitespace-pre-line" style="color: #1B2A4A;">{{ $vendorProfile->description }}</p>
                @else
                    <p class="text-sm" style="color: #9BA8B7;">{{ __('Cette boutique n\'a pas encore ajoute de description.') }}</p>
                @endif
            </div>

            {{-- Contact --}}
            <div class="bg-white rounded-2xl shadow-sm p-5 sm:p-6">
                <h2 class="text-sm font-bold uppercase tracking-wide mb-4" style="color: #6B7B8D;">{{ __('Contact') }}</h2>

                <div class="space-y-3">
                    @if($vendorProfile->contact_phone)
                        <a href="tel:{{ $vendorProfile->contact_phone }}"
                           class="flex items-center justify-center gap-2 w-full py-2.5 rounded-xl text-sm font-semibold text-white transition-opacity hover:opacity-90"
                           style="background: #17A2B8;">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                            </svg>
                            {{ __('Appeler') }}
                        </a>
                    @endif

                    @if($vendorProfile->contact_email)
                        <a href="mailto:{{ $vendorProfile->contact_email }}"
                           class="flex items-center justify-center gap-2 w-full py-2.5 rounded-xl text-sm font-semibold transition-colors"
                           style="background: #F0F4F8; color: #1B2A4A;">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                            </svg>
                            {{ __('Envoyer un e-mail') }}
                        </a>
                    @endif

                    @if($vendorProfile->contact_phone)
                        <div class="flex items-start gap-2 text-sm pt-1" style="color: #6B7B8D;">
                            <svg class="w-4 h-4 mt-0.5 flex-shrink-0" style="color: #9BA8B7;" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                            </svg>
                            <span>{{ $vendorProfile->contact_phone }}</span>
                        </div>
                    @endif

                    @if($vendorProfile->contact_email)
                        <div class="flex items-start gap-2 text-sm" style="color: #6B7B8D;">
                            <svg class="w-4 h-4 mt-0.5 flex-shrink-0" style="color: #9BA8B7;" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                            </svg>
                            <span class="break-all">{{ $vendorProfile->contact_email }}</span>
                        </div>
                    @endif

                    @if($vendorProfile->address)
                        <div class="flex items-start gap-2 text-sm" style="color: #6B7B8D;">
                            <svg class="w-4 h-4 mt-0.5 flex-shrink-0" style="color: #9BA8B7;" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                            </svg>
                            <span>{{ $vendorProfile->address }}</span>
                        </div>
                    @endif

                    @if(! $vendorProfile->contact_phone && ! $vendorProfile->contact_email && ! $vendorProfile->address)
                        <p class="text-sm" style="color: #9BA8B7;">{{ __('Aucune information de contact disponible.') }}</p>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Listings -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
        <div class="flex items-center justify-between mb-6">
            <h2 class="text-lg sm:text-xl font-extrabold" style="color: #1B2A4A;">{{ __('Annonces de la boutique') }}</h2>
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
                <h3 class="text-base font-bold mb-1" style="color: #1B2A4A;">{{ __('Cette boutique n\'a pas encore d\'annonces.') }}</h3>
                <p class="text-sm" style="color: #6B7B8D;">{{ __('Revenez bientot pour decouvrir ses moteurs et pieces detachees.') }}</p>
            </div>
        @endif
    </div>
</x-app-layout>
