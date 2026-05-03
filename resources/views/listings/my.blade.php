<x-app-layout>
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
                <span style="color: #1B2A4A;" class="font-medium">{{ __('Mes Annonces') }}</span>
            </nav>
        </div>
    </div>

    <!-- Page Header -->
    <div class="relative overflow-hidden" style="background: linear-gradient(135deg, #1B4F72 0%, #2471A3 50%, #17A2B8 100%);">
        <div class="absolute top-0 right-0 w-72 h-72 rounded-full blur-3xl animate-float" style="background: rgba(255,255,255,0.08);"></div>
        <div class="absolute bottom-0 left-0 w-48 h-48 rounded-full blur-3xl animate-float-reverse" style="background: rgba(255,255,255,0.05);"></div>
        <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-8 pb-8">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <h1 class="text-3xl font-extrabold tracking-tight text-white">{{ __('Mes Annonces') }}</h1>
                    <p class="mt-1 text-white/70">{{ __('Gerez vos annonces publiees') }}</p>
                </div>
                <a href="{{ route('listings.create') }}"
                   class="inline-flex items-center px-5 py-2.5 bg-white rounded-xl font-bold transition-all duration-300 transform hover:-translate-y-0.5"
                   style="color: #1B4F72; box-shadow: 0 8px 25px rgba(0,0,0,0.15);">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    {{ __('Nouvelle annonce') }}
                </a>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div style="background: #F0F4F8;" class="pb-16">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

            <!-- Flash Messages -->
            @if(session('listing_created'))
                {{-- ★ Confirmation card après création / paiement ★ --}}
                @php
                    $waMsg = urlencode("Bonjour AlBabor 👋\nJ'ai une question concernant mon annonce. Pouvez-vous m'aider ? 🙏");
                @endphp
                <div class="mb-6 rounded-2xl overflow-hidden" style="box-shadow: 0 12px 35px rgba(27,79,114,0.15); border: 1px solid rgba(27,79,114,0.1);">

                    {{-- Top gradient band --}}
                    <div class="px-6 py-5 flex items-center gap-4" style="background: linear-gradient(135deg, #1B4F72 0%, #17A2B8 100%);">
                        <div class="w-12 h-12 rounded-2xl flex items-center justify-center flex-shrink-0" style="background: rgba(255,255,255,0.2);">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                            </svg>
                        </div>
                        <div class="flex-1">
                            <p class="text-white font-bold text-base">{{ session('success') }}</p>
                            <p class="text-white/70 text-xs mt-0.5">{{ __('Notre equipe AlBabor prendra contact avec vous tres prochainement.') }}</p>
                        </div>
                        {{-- Animated pulse dot --}}
                        <div class="flex-shrink-0 flex items-center gap-1.5">
                            <span class="relative flex h-3 w-3">
                                <span class="animate-ping absolute inline-flex h-full w-full rounded-full opacity-75" style="background: #25D366;"></span>
                                <span class="relative inline-flex rounded-full h-3 w-3" style="background: #25D366;"></span>
                            </span>
                            <span class="text-white/80 text-xs font-medium">{{ __('En ligne') }}</span>
                        </div>
                    </div>

                    {{-- Bottom contact section --}}
                    <div class="px-6 py-5 flex flex-col sm:flex-row items-center gap-3" style="background: white;">
                        <div class="flex-1">
                            <p class="text-sm font-semibold" style="color: #1B2A4A;">{{ __('Vous avez une question ? Contactez-nous directement :') }}</p>
                            <p class="text-xs mt-0.5" style="color: #9BA8B7;">{{ __('Reponse sous 24h • Equipe disponible 7j/7') }}</p>
                        </div>
                        <div class="flex gap-3 flex-shrink-0">
                            {{-- WhatsApp --}}
                            <a href="https://wa.me/213791807475?text={{ $waMsg }}" target="_blank" rel="noopener"
                               class="flex items-center gap-2 px-5 py-2.5 rounded-xl font-bold text-white text-sm transition-all hover:-translate-y-0.5"
                               style="background: linear-gradient(135deg, #25D366, #128C7E); box-shadow: 0 4px 15px rgba(37,211,102,0.35);">
                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347z"/></svg>
                                WhatsApp
                            </a>
                            {{-- Appel --}}
                            <a href="tel:+213791807475"
                               class="flex items-center gap-2 px-5 py-2.5 rounded-xl font-bold text-sm transition-all hover:-translate-y-0.5"
                               style="background: white; border: 2px solid #1B4F72; color: #1B4F72; box-shadow: 0 4px 15px rgba(27,79,114,0.12);">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                                0791807475
                            </a>
                        </div>
                    </div>
                </div>
            @elseif(session('renewed'))
                {{-- ★ Renouvellement réussi ★ --}}
                <div class="mb-6 rounded-2xl overflow-hidden" style="background: linear-gradient(135deg, #0E7490, #17A2B8); box-shadow: 0 8px 24px rgba(23,162,184,0.25);">
                    <div class="px-5 py-4 flex items-start gap-4">
                        <div class="w-10 h-10 rounded-xl flex items-center justify-center flex-shrink-0" style="background: rgba(255,255,255,0.2);">
                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M5 10l7-7m0 0l7 7m-7-7v18"/>
                            </svg>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="font-bold text-white text-sm leading-snug">Annonce remontée en tête de liste !</p>
                            <p class="text-white/80 text-xs mt-0.5 truncate">« {{ session('renewed') }} »</p>
                            <p class="text-white/60 text-[11px] mt-1.5 flex items-center gap-1">
                                <svg class="w-3 h-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                Prochain renouvellement disponible dans 7 jours
                            </p>
                        </div>
                    </div>
                </div>
            @elseif(session('success'))
                <div class="mb-6 p-4 rounded-xl flex items-center gap-3" style="background: rgba(39, 174, 96, 0.08); border: 1px solid rgba(39, 174, 96, 0.2);">
                    <svg class="w-5 h-5 flex-shrink-0" style="color: #27AE60;" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                    </svg>
                    <p class="font-medium" style="color: #27AE60;">{{ session('success') }}</p>
                </div>
            @endif

            @if(session('error'))
                <div class="mb-6 p-4 rounded-xl flex items-center gap-3" style="background: rgba(231, 76, 60, 0.08); border: 1px solid rgba(231, 76, 60, 0.2);">
                    <svg class="w-5 h-5 flex-shrink-0" style="color: #E74C3C;" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                    </svg>
                    <p class="font-medium" style="color: #E74C3C;">{{ session('error') }}</p>
                </div>
            @endif

            @if($listings->count() > 0)
                <!-- Desktop Table View -->
                <div class="hidden md:block bg-white rounded-2xl overflow-hidden" style="box-shadow: 0 10px 25px rgba(0,0,0,0.06), 0 3px 8px rgba(0,0,0,0.03);">
                    <table class="min-w-full">
                        <thead>
                            <tr style="background: #F0F4F8; border-bottom: 1px solid #E0E6ED;">
                                <th class="px-6 py-4 text-left text-xs font-bold uppercase tracking-wider" style="color: #6B7B8D;">{{ __('Annonce') }}</th>
                                <th class="px-6 py-4 text-left text-xs font-bold uppercase tracking-wider" style="color: #6B7B8D;">{{ __('Prix') }}</th>
                                <th class="px-6 py-4 text-left text-xs font-bold uppercase tracking-wider" style="color: #6B7B8D;">{{ __('Statut') }}</th>
                                <th class="px-6 py-4 text-left text-xs font-bold uppercase tracking-wider" style="color: #6B7B8D;">{{ __('Stats') }}</th>
                                <th class="px-6 py-4 text-right text-xs font-bold uppercase tracking-wider" style="color: #6B7B8D;">{{ __('Actions') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($listings as $listing)
                                <tr style="border-bottom: 1px solid #F0F4F8;" class="hover:bg-[#F8FAFC] transition-colors">
                                    <!-- Listing Info -->
                                    <td class="px-6 py-4">
                                        <div class="flex items-center">
                                            <div class="w-16 h-16 flex-shrink-0 rounded-xl overflow-hidden" style="background: #E8EEF4;">
                                                @if($listing->media->first())
                                                    <img src="{{ $listing->media->first()->thumbnail_url ?? $listing->media->first()->url }}"
                                                         alt="" class="w-full h-full object-cover"
                                                         onerror="this.onerror=null;this.style.display='none';this.nextElementSibling.style.display='flex'">
                                                    <div class="w-full h-full items-center justify-center" style="color: #9BA8B7; display: none;">
                                                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                                        </svg>
                                                    </div>
                                                @else
                                                    <div class="w-full h-full flex items-center justify-center" style="color: #9BA8B7;">
                                                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                                        </svg>
                                                    </div>
                                                @endif
                                            </div>
                                            <div class="ml-4">
                                                <div class="text-sm font-semibold" style="color: #1B2A4A;">{{ Str::limit($listing->title, 40) }}</div>
                                                <div class="text-sm mt-0.5" style="color: #9BA8B7;">{{ $listing->category_label }} · {{ $listing->wilaya }}</div>
                                                @if($listing->isFeatured())
                                                    <span class="inline-flex items-center mt-1 px-2 py-0.5 rounded-full text-xs font-semibold" style="background: linear-gradient(135deg, #FFA500, #FF7200); color: white;">
                                                        {{ __('En vedette') }} · {{ $listing->featured_until->format('d/m/Y') }}
                                                    </span>
                                                @endif
                                            </div>
                                        </div>
                                    </td>

                                    <!-- Price -->
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-bold" style="color: #1B4F72;">{{ $listing->formatted_price }}</div>
                                        @if($listing->centimes_display)
                                            <div class="inline-flex items-center gap-1 mt-1 px-1.5 py-0.5 rounded-md text-[10px] font-bold" style="background: linear-gradient(135deg,#FFF8E7,#FFE9B8); color: #92591C; border: 1px solid rgba(241,196,15,0.4);">
                                                <span style="font-size:9px;">🇩🇿</span>
                                                <span>{{ $listing->centimes_display }}</span>
                                            </div>
                                        @endif
                                        @if($listing->formatted_converted_price)
                                            <div class="text-xs mt-0.5" style="color: #9BA8B7;">{{ $listing->formatted_converted_price }}</div>
                                        @endif
                                    </td>

                                    <!-- Status -->
                                    <td class="px-6 py-4">
                                        @php
                                            $statusStyles = [
                                                'active'           => ['bg' => 'rgba(39, 174, 96, 0.1)',   'color' => '#27AE60'],
                                                'pending_review'   => ['bg' => 'rgba(23, 162, 184, 0.1)',  'color' => '#17A2B8'],
                                                'awaiting_payment' => ['bg' => 'rgba(255, 107, 107, 0.1)', 'color' => '#FF6B6B'],
                                                'sold'             => ['bg' => 'rgba(107, 123, 141, 0.1)', 'color' => '#6B7B8D'],
                                                'paused'           => ['bg' => 'rgba(155, 168, 183, 0.1)', 'color' => '#9BA8B7'],
                                                'rejected'         => ['bg' => 'rgba(231, 76, 60, 0.1)',   'color' => '#E74C3C'],
                                                'draft'            => ['bg' => 'rgba(107, 123, 141, 0.1)', 'color' => '#6B7B8D'],
                                            ];
                                            $s = $statusStyles[$listing->status] ?? $statusStyles['draft'];
                                        @endphp
                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-semibold"
                                              style="background: {{ $s['bg'] }}; color: {{ $s['color'] }};">
                                            @if($listing->status === 'pending_review')
                                                <svg class="w-3 h-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                                </svg>
                                            @elseif($listing->status === 'awaiting_payment')
                                                <svg class="w-3 h-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                                                </svg>
                                            @elseif($listing->status === 'active')
                                                <svg class="w-3 h-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                                </svg>
                                            @elseif($listing->status === 'rejected')
                                                <svg class="w-3 h-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                                </svg>
                                            @endif
                                            {{ $listing->status_label }}
                                        </span>
                                        @if($listing->status === 'pending_review')
                                            <p class="text-xs mt-1.5 flex items-center gap-1" style="color: #9BA8B7;">
                                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                                {{ __('Reponse admin sous 24h') }}
                                            </p>
                                        @elseif($listing->status === 'awaiting_payment')
                                            <p class="text-xs mt-1.5" style="color: #9BA8B7;">{{ __('Paiement requis pour publier') }}</p>
                                        @elseif($listing->status === 'rejected')
                                            <p class="text-xs mt-1.5 font-semibold" style="color: #E74C3C;">{{ __('Annonce refusee') }}</p>
                                            @if($listing->rejection_reason)
                                                <div class="mt-2 p-2.5 rounded-lg text-xs max-w-xs" style="background: rgba(231,76,60,0.06); border: 1px solid rgba(231,76,60,0.15); color: #1B2A4A;">
                                                    <p class="font-semibold mb-1" style="color: #E74C3C;">{{ __('Raison :') }}</p>
                                                    <p style="color: #6B7B8D;">{{ $listing->rejection_reason }}</p>
                                                </div>
                                                <a href="{{ route('listings.edit', $listing) }}" class="inline-flex items-center gap-1 mt-2 text-xs font-semibold transition-opacity hover:opacity-80" style="color: #17A2B8;">
                                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                                    {{ __('Corriger et republier') }}
                                                </a>
                                            @endif
                                        @endif
                                    </td>

                                    <!-- Stats -->
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center space-x-4 text-sm" style="color: #6B7B8D;">
                                            <span class="flex items-center">
                                                <svg class="w-4 h-4 mr-1" style="color: #17A2B8;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                                </svg>
                                                {{ $listing->views_count }}
                                            </span>
                                            <span class="flex items-center">
                                                <svg class="w-4 h-4 mr-1" style="color: #FF6B6B;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
                                                </svg>
                                                {{ $listing->favorites_count }}
                                            </span>
                                        </div>
                                    </td>

                                    <!-- Actions -->
                                    <td class="px-6 py-4 whitespace-nowrap text-right">
                                        <div class="flex items-center justify-end gap-2">
                                            @if($listing->status === 'awaiting_payment')
                                                <a href="{{ route('listings.payment', $listing) }}" class="inline-flex items-center px-3 py-1.5 rounded-lg text-xs font-semibold transition-all duration-200 hover:scale-105" style="background: rgba(255, 107, 107, 0.1); color: #FF6B6B;">
                                                    <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>
                                                    {{ __('Payer') }}
                                                </a>
                                            @endif

                                            @if($listing->status === 'active')
                                                <a href="{{ route('listings.show', $listing) }}" class="inline-flex items-center px-3 py-1.5 rounded-lg text-xs font-semibold transition-all duration-200 hover:scale-105" style="background: rgba(23, 162, 184, 0.1); color: #17A2B8;">
                                                    <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                                    {{ __('Voir') }}
                                                </a>
                                                <a href="{{ route('listings.edit', $listing) }}" class="inline-flex items-center px-3 py-1.5 rounded-lg text-xs font-semibold transition-all duration-200 hover:scale-105" style="background: rgba(107, 123, 141, 0.1); color: #6B7B8D;">
                                                    <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                                    {{ __('Modifier') }}
                                                </a>

                                                @if(!$listing->isFeatured())
                                                    <a href="{{ route('listings.feature', $listing) }}" class="inline-flex items-center px-3 py-1.5 rounded-lg text-xs font-semibold transition-all duration-200 hover:scale-105" style="background: rgba(243, 156, 18, 0.1); color: #F39C12;">
                                                        <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/></svg>
                                                        {{ __('Vedette') }}
                                                    </a>
                                                @endif

                                                @if($listing->canRenew())
                                                    <form action="{{ route('listings.renew', $listing) }}" method="POST" class="inline">
                                                        @csrf
                                                        <button type="submit" class="inline-flex items-center px-3 py-1.5 rounded-lg text-xs font-semibold transition-all duration-200 hover:scale-105" style="background: rgba(23,162,184,0.1); color: #17A2B8;">
                                                            <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 10l7-7m0 0l7 7m-7-7v18"/></svg>
                                                            {{ __('Renouveler') }}
                                                        </button>
                                                    </form>
                                                @else
                                                    <span class="inline-flex items-center px-3 py-1.5 rounded-lg text-xs font-semibold cursor-not-allowed opacity-60" style="background: rgba(155,168,183,0.08); color: #9BA8B7;" title="{{ __('Renouvellement dans') }} {{ $listing->daysUntilRenewal() }}j">
                                                        <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                                        {{ $listing->daysUntilRenewal() }}j
                                                    </span>
                                                @endif

                                                <form action="{{ route('listings.pause', $listing) }}" method="POST" class="inline">
                                                    @csrf
                                                    <button type="submit" class="inline-flex items-center px-3 py-1.5 rounded-lg text-xs font-semibold transition-all duration-200 hover:scale-105" style="background: rgba(155, 168, 183, 0.1); color: #9BA8B7;">
                                                        <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 9v6m4-6v6m7-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                                        {{ __('Pause') }}
                                                    </button>
                                                </form>

                                                <form action="{{ route('listings.sold', $listing) }}" method="POST" class="inline">
                                                    @csrf
                                                    <button type="submit" class="inline-flex items-center px-3 py-1.5 rounded-lg text-xs font-semibold transition-all duration-200 hover:scale-105" style="background: rgba(39, 174, 96, 0.1); color: #27AE60;">
                                                        <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                                        {{ __('Vendu') }}
                                                    </button>
                                                </form>
                                            @endif

                                            @if($listing->status === 'paused')
                                                <form action="{{ route('listings.reactivate', $listing) }}" method="POST" class="inline">
                                                    @csrf
                                                    <button type="submit" class="inline-flex items-center px-3 py-1.5 rounded-lg text-xs font-semibold transition-all duration-200 hover:scale-105" style="background: rgba(39, 174, 96, 0.1); color: #27AE60;">
                                                        <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                                        {{ __('Reactiver') }}
                                                    </button>
                                                </form>
                                            @endif

                                            @if(in_array($listing->status, ['draft', 'rejected', 'paused', 'pending_review', 'awaiting_payment']))
                                                <a href="{{ route('listings.edit', $listing) }}" class="inline-flex items-center px-3 py-1.5 rounded-lg text-xs font-semibold transition-all duration-200 hover:scale-105" style="background: rgba(107, 123, 141, 0.1); color: #6B7B8D;">
                                                    <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                                    {{ __('Modifier') }}
                                                </a>
                                            @endif

                                            <form action="{{ route('listings.destroy', $listing) }}" method="POST" class="inline" onsubmit="return confirm('{{ addslashes(__('Etes-vous sur de vouloir supprimer cette annonce ?')) }}')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="inline-flex items-center px-3 py-1.5 rounded-lg text-xs font-semibold transition-all duration-200 hover:scale-105" style="background: rgba(231, 76, 60, 0.1); color: #E74C3C;">
                                                    <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                                    {{ __('Supprimer') }}
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Mobile Card View -->
                <div class="md:hidden space-y-4">
                    @foreach($listings as $listing)
                        <div class="bg-white rounded-2xl overflow-hidden animate-fade-in-up opacity-0" style="box-shadow: 0 10px 25px rgba(0,0,0,0.06), 0 3px 8px rgba(0,0,0,0.03); animation-delay: {{ $loop->index * 0.08 }}s;">
                            <div class="flex items-start p-4 gap-4">
                                <!-- Image -->
                                <div class="w-20 h-20 flex-shrink-0 rounded-xl overflow-hidden" style="background: #E8EEF4;">
                                    @if($listing->media->first())
                                        <img src="{{ $listing->media->first()->thumbnail_url ?? $listing->media->first()->url }}"
                                             alt="" class="w-full h-full object-cover"
                                             onerror="this.onerror=null;this.style.display='none';this.nextElementSibling.style.display='flex'">
                                        <div class="w-full h-full items-center justify-center" style="color: #9BA8B7; display: none;">
                                            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                            </svg>
                                        </div>
                                    @else
                                        <div class="w-full h-full flex items-center justify-center" style="color: #9BA8B7;">
                                            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                            </svg>
                                        </div>
                                    @endif
                                </div>

                                <!-- Info -->
                                <div class="flex-1 min-w-0">
                                    <h3 class="text-sm font-semibold truncate" style="color: #1B2A4A;">{{ $listing->title }}</h3>
                                    <p class="text-xs mt-0.5" style="color: #9BA8B7;">{{ $listing->category_label }} · {{ $listing->wilaya }}</p>
                                    <div class="flex items-center gap-2 mt-2">
                                        <span class="text-sm font-bold" style="color: #1B4F72;">{{ $listing->formatted_price }}</span>
                                        @php
                                            $s = $statusStyles[$listing->status] ?? $statusStyles['draft'];
                                        @endphp
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-semibold" style="background: {{ $s['bg'] }}; color: {{ $s['color'] }};">
                                            @if($listing->status === 'pending_review')
                                                <svg class="w-2.5 h-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                            @endif
                                            {{ $listing->status_label }}
                                        </span>
                                    </div>
                                    @if($listing->status === 'pending_review')
                                        <p class="text-[10px] mt-1" style="color: #9BA8B7;">{{ __('Reponse admin sous 24h') }}</p>
                                    @elseif($listing->status === 'awaiting_payment')
                                        <p class="text-[10px] mt-1" style="color: #FF6B6B;">{{ __('Paiement requis') }}</p>
                                    @elseif($listing->status === 'rejected')
                                        <p class="text-[10px] mt-1 font-semibold" style="color: #E74C3C;">{{ __('Annonce refusee') }}</p>
                                        @if($listing->rejection_reason)
                                            <div class="mt-2 p-2.5 rounded-lg text-xs" style="background: rgba(231,76,60,0.06); border: 1px solid rgba(231,76,60,0.15); color: #1B2A4A;">
                                                <p class="font-semibold mb-1" style="color: #E74C3C;">{{ __('Raison :') }}</p>
                                                <p style="color: #6B7B8D;">{{ $listing->rejection_reason }}</p>
                                            </div>
                                            <a href="{{ route('listings.edit', $listing) }}" class="inline-flex items-center gap-1 mt-2 text-xs font-semibold" style="color: #17A2B8;">
                                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                                {{ __('Corriger et republier') }}
                                            </a>
                                        @endif
                                    @endif
                                    <!-- Stats -->
                                    <div class="flex items-center gap-3 mt-1.5 text-xs" style="color: #9BA8B7;">
                                        <span class="flex items-center">
                                            <svg class="w-3.5 h-3.5 mr-0.5" style="color: #17A2B8;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                            {{ $listing->views_count }}
                                        </span>
                                        <span class="flex items-center">
                                            <svg class="w-3.5 h-3.5 mr-0.5" style="color: #FF6B6B;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/></svg>
                                            {{ $listing->favorites_count }}
                                        </span>
                                    </div>
                                </div>
                            </div>

                            <!-- Mobile Actions -->
                            <div class="px-4 pb-4 pt-3" style="border-top: 1px solid #F0F4F8;">

                                {{-- Primary actions row --}}
                                <div class="flex gap-2 mb-2">

                                    {{-- Modifier — toujours visible sauf vendu/expiré --}}
                                    @if(!in_array($listing->status, ['sold', 'expired']))
                                    <a href="{{ route('listings.edit', $listing) }}"
                                       class="flex-1 flex items-center justify-center gap-1.5 py-2.5 rounded-xl text-xs font-bold text-white transition-all active:scale-95"
                                       style="background: linear-gradient(135deg, #1B4F72, #17A2B8); box-shadow: 0 4px 12px rgba(27,79,114,0.3);">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                        </svg>
                                        {{ __('Modifier') }}
                                    </a>
                                    @endif

                                    {{-- Payer --}}
                                    @if($listing->status === 'awaiting_payment')
                                    <a href="{{ route('listings.payment', $listing) }}"
                                       class="flex-1 flex items-center justify-center gap-1.5 py-2.5 rounded-xl text-xs font-bold text-white transition-all active:scale-95"
                                       style="background: linear-gradient(135deg, #E74C3C, #FF6B6B); box-shadow: 0 4px 12px rgba(231,76,60,0.3);">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                                        </svg>
                                        {{ __('Payer') }}
                                    </a>
                                    @endif

                                    {{-- Voir l'annonce --}}
                                    @if($listing->status === 'active')
                                    <a href="{{ route('listings.show', $listing) }}"
                                       class="flex items-center justify-center gap-1.5 px-4 py-2.5 rounded-xl text-xs font-bold transition-all active:scale-95"
                                       style="background: rgba(23,162,184,0.1); color: #17A2B8; border: 1.5px solid rgba(23,162,184,0.25);">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                        </svg>
                                        {{ __('Voir') }}
                                    </a>
                                    @endif

                                </div>

                                {{-- Secondary actions row --}}
                                <div class="flex gap-2">

                                    {{-- Vedette --}}
                                    @if($listing->status === 'active' && !$listing->isFeatured())
                                    <a href="{{ route('listings.feature', $listing) }}"
                                       class="flex items-center gap-1.5 px-3 py-2 rounded-xl text-xs font-semibold transition-all active:scale-95"
                                       style="background: rgba(243,156,18,0.1); color: #D68910; border: 1.5px solid rgba(243,156,18,0.2);">
                                        <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                                        {{ __('Vedette') }}
                                    </a>
                                    @endif

                                    {{-- Renouveler --}}
                                    @if($listing->status === 'active')
                                        @if($listing->canRenew())
                                            <form action="{{ route('listings.renew', $listing) }}" method="POST" class="inline">@csrf
                                                <button type="submit"
                                                        class="flex items-center gap-1.5 px-3 py-2 rounded-xl text-xs font-semibold transition-all active:scale-95"
                                                        style="background: rgba(23,162,184,0.1); color: #17A2B8; border: 1.5px solid rgba(23,162,184,0.25);">
                                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 10l7-7m0 0l7 7m-7-7v18"/></svg>
                                                    {{ __('Renouveler') }}
                                                </button>
                                            </form>
                                        @else
                                            <span class="flex items-center gap-1.5 px-3 py-2 rounded-xl text-xs font-semibold opacity-50 cursor-not-allowed"
                                                  style="background: rgba(155,168,183,0.08); color: #9BA8B7; border: 1.5px solid rgba(155,168,183,0.15);">
                                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                                {{ $listing->daysUntilRenewal() }}j
                                            </span>
                                        @endif
                                    @endif

                                    {{-- Vendu --}}
                                    @if($listing->status === 'active')
                                    <form action="{{ route('listings.sold', $listing) }}" method="POST" class="inline">@csrf
                                        <button type="submit"
                                                class="flex items-center gap-1.5 px-3 py-2 rounded-xl text-xs font-semibold transition-all active:scale-95"
                                                style="background: rgba(39,174,96,0.1); color: #1E8449; border: 1.5px solid rgba(39,174,96,0.2);">
                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                                            {{ __('Vendu') }}
                                        </button>
                                    </form>
                                    @endif

                                    {{-- Reactiver --}}
                                    @if($listing->status === 'paused')
                                    <form action="{{ route('listings.reactivate', $listing) }}" method="POST" class="inline">@csrf
                                        <button type="submit"
                                                class="flex items-center gap-1.5 px-3 py-2 rounded-xl text-xs font-semibold transition-all active:scale-95"
                                                style="background: rgba(39,174,96,0.1); color: #1E8449; border: 1.5px solid rgba(39,174,96,0.2);">
                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                            {{ __('Reactiver') }}
                                        </button>
                                    </form>
                                    @endif

                                    {{-- Supprimer --}}
                                    <form action="{{ route('listings.destroy', $listing) }}" method="POST" class="inline ml-auto"
                                          onsubmit="return confirm('{{ addslashes(__('Supprimer cette annonce definitivement ?')) }}')">
                                        @csrf @method('DELETE')
                                        <button type="submit"
                                                class="flex items-center gap-1.5 px-3 py-2 rounded-xl text-xs font-semibold transition-all active:scale-95"
                                                style="background: rgba(231,76,60,0.08); color: #E74C3C; border: 1.5px solid rgba(231,76,60,0.18);">
                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                            {{ __('Supprimer') }}
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                <!-- Pagination -->
                @if($listings->hasPages())
                    <div class="mt-8 flex justify-center">
                        <div class="inline-flex items-center gap-1 p-2 rounded-2xl bg-white" style="box-shadow: 0 10px 25px rgba(0,0,0,0.06);">
                            {{ $listings->links() }}
                        </div>
                    </div>
                @endif

            @else
                <!-- Empty State -->
                <div class="bg-white rounded-2xl p-12 text-center" style="box-shadow: 0 10px 25px rgba(0,0,0,0.06), 0 3px 8px rgba(0,0,0,0.03);">
                    <div class="relative w-40 h-40 mx-auto mb-8 animate-float-gentle">
                        <div class="absolute inset-0 rounded-full" style="background: rgba(23, 162, 184, 0.08);"></div>
                        <div class="absolute inset-4 rounded-full" style="background: rgba(23, 162, 184, 0.12);"></div>
                        <div class="absolute inset-0 flex items-center justify-center">
                            <svg class="w-20 h-20" style="color: #17A2B8;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 13h6m-3-3v6m5 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                        </div>
                    </div>

                    <h3 class="text-2xl font-bold mb-3" style="color: #1B2A4A;">{{ __('Aucune annonce pour le moment') }}</h3>
                    <p class="text-lg mb-8 max-w-md mx-auto" style="color: #6B7B8D;">
                        {{ __("Commencez par publier votre premiere annonce et atteignez des milliers d'acheteurs.") }}
                    </p>
                    <a href="{{ route('listings.create') }}"
                       class="inline-flex items-center px-6 py-3 gradient-primary rounded-xl font-bold text-white transition-all duration-300 transform hover:-translate-y-1"
                       style="box-shadow: 0 8px 25px rgba(27, 79, 114, 0.25);">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                        {{ __('Publier une annonce') }}
                    </a>
                </div>
            @endif
        </div>
    </div>

    <script>
        (() => {
            const draftKey = 'albabor_listing_draft';
            const sessionKey = 'albabor_listing_draft_session';
            const filesBaseKey = 'albabor_listing_draft_files';

            try {
                const sessionId = sessionStorage.getItem(sessionKey);
                sessionStorage.removeItem(draftKey);
                sessionStorage.removeItem(sessionKey);

                if (!sessionId || typeof indexedDB === 'undefined') {
                    return;
                }

                const request = indexedDB.open('albabor-photo-uploader', 1);
                request.onupgradeneeded = () => {
                    const db = request.result;
                    if (!db.objectStoreNames.contains('drafts')) {
                        db.createObjectStore('drafts');
                    }
                };
                request.onsuccess = () => {
                    const db = request.result;
                    const tx = db.transaction('drafts', 'readwrite');
                    tx.objectStore('drafts').delete(`${filesBaseKey}:${sessionId}`);
                    tx.oncomplete = () => db.close();
                    tx.onerror = () => db.close();
                    tx.onabort = () => db.close();
                };
            } catch (e) {
                // Ignore cleanup failures
            }
        })();
    </script>
</x-app-layout>
