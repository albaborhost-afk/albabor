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
                <a href="{{ route('listings.my') }}" style="color: #9BA8B7;" class="hover:opacity-80">Mes Annonces</a>
                <svg class="w-4 h-4" style="color: #E0E6ED;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                </svg>
                <span style="color: #1B2A4A;" class="font-medium">Paiement</span>
            </nav>
        </div>
    </div>

    <!-- Page Header -->
    <div class="relative overflow-hidden" style="background: linear-gradient(135deg, #1B4F72 0%, #27AE60 100%);">
        <div class="absolute top-0 right-0 w-72 h-72 rounded-full blur-3xl" style="background: rgba(255,255,255,0.08);"></div>
        <div class="relative max-w-2xl mx-auto px-4 sm:px-6 lg:px-8 pt-8 pb-8">
            <div class="flex items-center gap-3">
                <div class="w-12 h-12 rounded-2xl flex items-center justify-center" style="background: rgba(255,255,255,0.2);">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                    </svg>
                </div>
                <div>
                    <h1 class="text-3xl font-extrabold tracking-tight text-white">Paiement</h1>
                    <p class="mt-0.5 text-white/70">Finalisez le paiement pour publier votre annonce</p>
                </div>
            </div>
        </div>
    </div>

    <div style="background: #F0F4F8;" class="py-8 pb-16">
        <div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8">

            @if(session('success'))
                <div class="mb-6 p-4 rounded-xl flex items-center gap-3" style="background: rgba(39,174,96,0.08); border: 1px solid rgba(39,174,96,0.25);">
                    <svg class="w-5 h-5 flex-shrink-0" style="color: #27AE60;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    <p class="text-sm font-semibold" style="color: #27AE60;">{{ session('success') }}</p>
                </div>
            @endif

            @if($errors->any())
                <div class="mb-6 p-4 rounded-xl" style="background: rgba(231, 76, 60, 0.08); border: 1px solid rgba(231, 76, 60, 0.2);">
                    <ul class="list-disc list-inside text-sm" style="color: #E74C3C;">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @php
                $requestedServices = $listing->specs['services'] ?? [];
                $serviceLabels = [
                    'photo'     => ['label' => 'Shooting photo professionnel', 'icon' => '📷', 'color' => '#F39C12'],
                    'reception' => ['label' => 'Réception des appels',         'icon' => '📞', 'color' => '#27AE60'],
                    'video'     => ['label' => 'Vidéo de présentation',        'icon' => '🎬', 'color' => '#8E44AD'],
                ];
                if (!empty($requestedServices)) {
                    $servicesText = implode(' + ', array_map(fn($s) => $serviceLabels[$s]['label'] ?? $s, $requestedServices));
                    $waText  = "Bonjour AlBabor 👋\n";
                    $waText .= "J'ai créé l'annonce *{$listing->title}* et je souhaite activer les services suivants :\n";
                    foreach ($requestedServices as $s) {
                        $waText .= "→ " . ($serviceLabels[$s]['label'] ?? $s) . "\n";
                    }
                    $waText .= "\nMerci de me contacter pour confirmer et planifier. 🙏";
                    $waLink  = "https://wa.me/213791807475?text=" . urlencode($waText);
                }

                $amountEur = $exchangeRate > 0 ? round($amount / $exchangeRate, 2) : 21.00;
            @endphp

            @if(!empty($requestedServices))
            <div class="rounded-2xl mb-6 overflow-hidden" style="box-shadow: 0 10px 30px rgba(243,156,18,0.2); border: 2px solid rgba(243,156,18,0.4);">
                <div class="px-6 py-4 flex items-center gap-3" style="background: linear-gradient(135deg, #1B4F72 0%, #F39C12 100%);">
                    <div class="w-10 h-10 rounded-xl flex items-center justify-center flex-shrink-0" style="background: rgba(255,255,255,0.2);">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"/></svg>
                    </div>
                    <div>
                        <p class="text-white font-bold text-sm">Services AlBabor demandés</p>
                        <p class="text-white/70 text-xs">Contactez notre équipe pour confirmer et planifier</p>
                    </div>
                    <span class="ml-auto text-xs font-bold text-white bg-white/20 px-3 py-1 rounded-full">{{ count($requestedServices) }} service(s)</span>
                </div>
                <div class="px-6 py-5" style="background: linear-gradient(160deg, #FFFDF5, #FEF9E7);">
                    <div class="flex flex-wrap gap-2 mb-5">
                        @foreach($requestedServices as $s)
                            @if(isset($serviceLabels[$s]))
                            <span class="inline-flex items-center gap-1.5 text-sm font-semibold px-4 py-2 rounded-full" style="background: white; border: 2px solid {{ $serviceLabels[$s]['color'] }}; color: {{ $serviceLabels[$s]['color'] }}; box-shadow: 0 2px 8px rgba(0,0,0,0.06);">
                                {{ $serviceLabels[$s]['icon'] }} {{ $serviceLabels[$s]['label'] }}
                            </span>
                            @endif
                        @endforeach
                    </div>
                    <a href="{{ $waLink }}" target="_blank" rel="noopener"
                       class="flex items-center justify-center gap-3 w-full py-4 rounded-2xl font-bold text-white text-sm transition-all duration-300 hover:-translate-y-1"
                       style="background: linear-gradient(135deg, #25D366, #128C7E); box-shadow: 0 8px 25px rgba(37,211,102,0.4);">
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347z"/></svg>
                        Contacter l'équipe AlBabor sur WhatsApp
                    </a>
                </div>
            </div>
            @endif

            <!-- Listing Summary -->
            <div class="bg-white rounded-2xl p-6 mb-6" style="box-shadow: 0 10px 25px rgba(0,0,0,0.06);">
                <h2 class="text-base font-bold mb-4 flex items-center gap-2" style="color: #1B2A4A;">
                    <svg class="w-5 h-5" style="color: #17A2B8;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                    </svg>
                    Résumé de l'annonce
                </h2>
                <div class="flex items-start gap-4">
                    <div class="w-20 h-20 rounded-xl overflow-hidden flex-shrink-0 flex items-center justify-center" style="background: #F0F4F8;">
                        @if($listing->media->first())
                            <img src="{{ $listing->media->first()->thumbnail_url ?? $listing->media->first()->url }}"
                                 alt="" class="w-full h-full object-cover"
                                 onerror="this.style.display='none'">
                        @else
                            <svg class="w-8 h-8" style="color: #C5D0DB;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                            </svg>
                        @endif
                    </div>
                    <div>
                        <h3 class="font-bold" style="color: #1B2A4A;">{{ $listing->title }}</h3>
                        <p class="text-sm" style="color: #6B7B8D;">{{ $listing->category_label }}{{ $listing->wilaya ? ' · ' . $listing->wilaya : '' }}</p>
                        <p class="text-lg font-black mt-1" style="color: #1B4F72;">{{ $listing->formatted_price }}</p>
                    </div>
                </div>
            </div>

            <!-- Publication Fee -->
            <div class="rounded-2xl p-5 mb-6 flex items-center justify-between" style="background: rgba(23,162,184,0.08); border: 1px solid rgba(23,162,184,0.2);">
                <div>
                    <p class="font-bold" style="color: #1B2A4A;">Frais de publication</p>
                    <p class="text-xs mt-0.5" style="color: #6B7B8D;">Valable 365 jours — annonce visible sur AlBabor</p>
                </div>
                <div class="text-right">
                    <p class="text-2xl font-black" style="color: #1B4F72;">{{ number_format($amount, 0, ',', ' ') }} DA</p>
                    @if($amountEur > 0)
                        <p class="text-xs" style="color: #9BA8B7;">≈ {{ number_format($amountEur, 2) }} €</p>
                    @endif
                </div>
            </div>

            {{-- ══════════════════════════════════════════════
                 ① STRIPE — Paiement immédiat par carte
            ══════════════════════════════════════════════ --}}
            <div class="rounded-3xl mb-5 overflow-hidden" style="box-shadow: 0 12px 35px rgba(99,91,255,0.18); border: 2px solid rgba(99,91,255,0.25);">

                {{-- Header --}}
                <div class="px-6 py-4 flex items-center gap-3" style="background: linear-gradient(135deg, #635BFF 0%, #4B44D9 100%);">
                    <div class="w-10 h-10 rounded-xl flex items-center justify-center flex-shrink-0" style="background: rgba(255,255,255,0.2);">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                        </svg>
                    </div>
                    <div class="flex-1">
                        <div class="flex items-center gap-2">
                            <p class="text-white font-bold text-sm">Payer par carte bancaire</p>
                            <span class="text-xs font-bold px-2 py-0.5 rounded-full" style="background: rgba(255,255,255,0.25); color: white;">RECOMMANDÉ</span>
                        </div>
                        <p class="text-white/70 text-xs mt-0.5">Visa · Mastercard · American Express</p>
                    </div>
                    {{-- Stripe logo --}}
                    <svg viewBox="0 0 60 25" class="h-6 w-auto" fill="white" xmlns="http://www.w3.org/2000/svg">
                        <path d="M59.64 14.28h-8.06c.19 1.93 1.6 2.55 3.2 2.55 1.64 0 2.96-.37 4.05-.95v3.32a8.33 8.33 0 0 1-4.56 1.1c-4.01 0-6.83-2.5-6.83-7.48 0-4.19 2.39-7.52 6.3-7.52 3.92 0 5.96 3.28 5.96 7.5 0 .4-.04 1.26-.06 1.48zm-5.92-5.62c-1.03 0-2.17.73-2.17 2.58h4.25c0-1.85-1.07-2.58-2.08-2.58zM40.95 20.3c-1.44 0-2.32-.6-2.9-1.04l-.02 4.63-4.12.87V5.57h3.76l.08 1.02a4.7 4.7 0 0 1 3.23-1.29c2.9 0 5.62 2.6 5.62 7.4 0 5.23-2.7 7.6-5.65 7.6zM40 8.95c-.95 0-1.54.34-1.97.81l.02 6.12c.4.44.98.78 1.95.78 1.52 0 2.54-1.65 2.54-3.87 0-2.15-1.04-3.84-2.54-3.84zM28.24 5.57h4.13v14.44h-4.13V5.57zm0-4.7L32.37 0v3.36l-4.13.88V.88zm-4.32 9.35v9.79H19.8V5.57h3.7l.12 1.22c1-1.77 2.98-1.63 3.54-1.43v3.79c-.31-.09-1.28-.61-2.24.28zm-8.55 4.72c0 2.43 2.6 1.68 3.12 1.46v3.36c-.55.3-1.54.54-2.89.54a4.15 4.15 0 0 1-4.27-4.24l.01-13.17 4.02-.86v3.54h3.14V9.1h-3.13v5.85zm-4.91.7c0 2.97-2.31 4.66-5.73 4.66a11.2 11.2 0 0 1-4.46-.93v-3.93c1.38.75 3.1 1.31 4.46 1.31.92 0 1.53-.24 1.53-1C6.26 13.77 0 14.51 0 9.95 0 7.04 2.28 5.3 5.62 5.3c1.5 0 3 .25 4.43.08v3.77c-1.3-.3-2.67-.4-4.43-.4-.78 0-1.31.27-1.31.9 0 1.98 6.41.99 6.41 6.07z"/>
                    </svg>
                </div>

                {{-- Body --}}
                <div class="px-6 py-5" style="background: linear-gradient(160deg, #F8F7FF, #F0EFFF);">
                    <div class="flex flex-wrap gap-3 mb-5">
                        <div class="flex items-center gap-2 text-xs font-semibold px-3 py-1.5 rounded-full" style="background: white; border: 1px solid #E8E6FF; color: #635BFF;">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                            Paiement immédiat
                        </div>
                        <div class="flex items-center gap-2 text-xs font-semibold px-3 py-1.5 rounded-full" style="background: white; border: 1px solid #E8E6FF; color: #635BFF;">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                            Sécurisé SSL
                        </div>
                        <div class="flex items-center gap-2 text-xs font-semibold px-3 py-1.5 rounded-full" style="background: white; border: 1px solid #E8E6FF; color: #635BFF;">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                            Aucun justificatif requis
                        </div>
                    </div>

                    <div class="flex items-center justify-between p-4 rounded-2xl mb-4" style="background: white; border: 1.5px solid rgba(99,91,255,0.15);">
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-wider" style="color: #9BA8B7;">Montant à payer</p>
                            <p class="text-2xl font-black mt-0.5" style="color: #1B2A4A;">{{ number_format($amountEur, 2) }} <span style="color: #635BFF;">€</span></p>
                            <p class="text-xs mt-0.5" style="color: #9BA8B7;">≈ {{ number_format($amount, 0, ',', ' ') }} DA au taux indicatif</p>
                        </div>
                        <div class="flex gap-1.5">
                            <img src="https://upload.wikimedia.org/wikipedia/commons/thumb/5/5e/Visa_Inc._logo.svg/200px-Visa_Inc._logo.svg.png" class="h-6 w-auto object-contain" alt="Visa" onerror="this.style.display='none'">
                            <img src="https://upload.wikimedia.org/wikipedia/commons/thumb/2/2a/Mastercard-logo.svg/200px-Mastercard-logo.svg.png" class="h-6 w-auto object-contain" alt="Mastercard" onerror="this.style.display='none'">
                        </div>
                    </div>

                    <form action="{{ route('payments.stripe.checkout', $listing) }}" method="POST">
                        @csrf
                        <button type="submit"
                                class="w-full py-4 rounded-2xl font-bold text-white text-base transition-all duration-300 hover:-translate-y-0.5 hover:shadow-xl flex items-center justify-center gap-3"
                                style="background: linear-gradient(135deg, #635BFF, #4B44D9); box-shadow: 0 8px 25px rgba(99,91,255,0.4);">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>
                            Payer {{ number_format($amountEur, 2) }} € avec Stripe
                            <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                        </button>
                    </form>

                    <p class="text-center text-xs mt-3" style="color: #9BA8B7;">
                        Vous serez redirigé vers la page de paiement sécurisée de Stripe
                    </p>
                </div>
            </div>

            {{-- ══════════════════════════════════════════════
                 Séparateur
            ══════════════════════════════════════════════ --}}
            <div class="flex items-center gap-4 mb-5">
                <div class="flex-1 h-px" style="background: #E0E6ED;"></div>
                <span class="text-xs font-semibold px-3 py-1.5 rounded-full" style="background: #E8EEF4; color: #9BA8B7;">ou payer manuellement</span>
                <div class="flex-1 h-px" style="background: #E0E6ED;"></div>
            </div>

            {{-- ══════════════════════════════════════════════
                 ② METHODES MANUELLES — BaridiMob / BEA / PayPal
            ══════════════════════════════════════════════ --}}
            <form action="{{ route('payments.listing', $listing) }}" method="POST" enctype="multipart/form-data"
                  x-data="{ method: '{{ old('method', '') }}' }" class="space-y-5">
                @csrf

                <!-- Payment Method -->
                <div class="bg-white rounded-2xl p-6" style="box-shadow: 0 10px 25px rgba(0,0,0,0.06);">
                    <h2 class="text-base font-bold mb-4 flex items-center gap-2" style="color: #1B2A4A;">
                        <svg class="w-5 h-5" style="color: #17A2B8;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                        </svg>
                        Méthode de paiement
                    </h2>

                    <div class="space-y-3">

                        {{-- BaridiMob --}}
                        <label class="flex items-start p-4 rounded-xl cursor-pointer transition-all"
                               :style="method === 'baridimob' ? 'border: 2px solid #17A2B8; background: rgba(23,162,184,0.04);' : 'border: 1px solid #E0E6ED;'">
                            <input type="radio" name="method" value="baridimob" x-model="method" required
                                   style="accent-color: #17A2B8; margin-top: 3px;">
                            <img src="/images/baridimob.png" alt="BaridiMob" class="ml-3 h-8 w-auto object-contain flex-shrink-0">
                            <span class="ml-3">
                                <span class="block font-semibold" style="color: #1B2A4A;">BaridiMob</span>
                                <span class="block text-sm" style="color: #6B7B8D;">Numéro : <span class="font-mono font-bold">00799999002543569223</span></span>
                                <span class="block text-xs mt-0.5" style="color: #9BA8B7;">Titulaire : DJAMAA BILEL</span>
                            </span>
                        </label>

                        {{-- BEA --}}
                        <label class="flex items-start p-4 rounded-xl cursor-pointer transition-all"
                               :style="method === 'bank_transfer' ? 'border: 2px solid #17A2B8; background: rgba(23,162,184,0.04);' : 'border: 1px solid #E0E6ED;'">
                            <input type="radio" name="method" value="bank_transfer" x-model="method"
                                   style="accent-color: #17A2B8; margin-top: 3px;">
                            <img src="/images/bea.png" alt="BEA" class="ml-3 h-8 w-auto object-contain flex-shrink-0">
                            <span class="ml-3">
                                <span class="block font-semibold" style="color: #1B2A4A;">BEA – Banque Extérieure d'Algérie</span>
                                <span class="block text-sm" style="color: #6B7B8D;">🇩🇿 RIB : <span class="font-mono font-bold">00200090090220206690</span></span>
                                <span class="block text-xs mt-0.5" style="color: #9BA8B7;">Titulaire : DJAMAA BILEL</span>
                            </span>
                        </label>

                        {{-- PayPal --}}
                        <label class="flex items-start p-4 rounded-xl cursor-pointer transition-all"
                               :style="method === 'paypal' ? 'border: 2px solid #17A2B8; background: rgba(23,162,184,0.04);' : 'border: 1px solid #E0E6ED;'">
                            <input type="radio" name="method" value="paypal" x-model="method"
                                   style="accent-color: #17A2B8; margin-top: 3px;">
                            <img src="/images/paypal.png" alt="PayPal" class="ml-3 h-8 w-auto object-contain flex-shrink-0">
                            <span class="ml-3">
                                <span class="block font-semibold" style="color: #1B2A4A;">PayPal</span>
                                <span class="block text-sm" style="color: #6B7B8D;">albabordz@gmail.com</span>
                                <span class="block text-xs mt-0.5" style="color: #9BA8B7;">Titulaire : DJAMAA BILEL</span>
                            </span>
                        </label>

                    </div>
                </div>

                <!-- Payment Proof (shown when a manual method is selected) -->
                <div x-show="method !== ''" x-transition class="bg-white rounded-2xl p-6" style="box-shadow: 0 10px 25px rgba(0,0,0,0.06);">
                    <h2 class="text-base font-bold mb-2 flex items-center gap-2" style="color: #1B2A4A;">
                        <svg class="w-5 h-5" style="color: #17A2B8;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                        Justificatif de paiement
                    </h2>
                    <p class="text-sm mb-4" style="color: #6B7B8D;">Téléversez une capture d'écran ou photo du virement effectué</p>

                    <div class="flex justify-center px-6 pt-5 pb-6 rounded-xl transition-colors"
                         style="border: 2px dashed #E0E6ED; background: #F8FAFC;">
                        <div class="space-y-2 text-center">
                            <svg class="mx-auto h-12 w-12" style="color: #9BA8B7;" stroke="currentColor" fill="none" viewBox="0 0 48 48">
                                <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                            </svg>
                            <div class="flex text-sm justify-center" style="color: #6B7B8D;">
                                <label for="proof" class="relative cursor-pointer rounded-md font-semibold" style="color: #17A2B8;">
                                    <span>Choisir un fichier</span>
                                    <input id="proof" name="proof" type="file" class="sr-only" accept="image/*"
                                           :required="method !== ''">
                                </label>
                                <p class="pl-1">ou glisser-déposer</p>
                            </div>
                            <p class="text-xs" style="color: #9BA8B7;">PNG, JPG jusqu'à 5 MB</p>
                        </div>
                    </div>
                    <div id="proofPreview" class="mt-4 hidden">
                        <img id="proofImage" src="" alt="" class="max-h-48 mx-auto rounded-xl">
                    </div>
                </div>

                <!-- Submit -->
                <div x-show="method !== ''" x-transition class="flex justify-between items-center">
                    <a href="{{ route('listings.my') }}" class="px-5 py-3 rounded-xl font-semibold transition-all text-sm" style="color: #6B7B8D; border: 1px solid #E0E6ED;">
                        Annuler
                    </a>
                    <button type="submit"
                            class="px-8 py-3 rounded-xl font-bold text-white text-sm transition-all duration-300 hover:-translate-y-0.5"
                            style="background: linear-gradient(135deg, #1B4F72, #17A2B8); box-shadow: 0 8px 25px rgba(27,79,114,0.25);">
                        Envoyer le justificatif →
                    </button>
                </div>

                <div x-show="method === ''" x-transition class="flex justify-start">
                    <a href="{{ route('listings.my') }}" class="px-5 py-3 rounded-xl font-semibold transition-all text-sm" style="color: #6B7B8D; border: 1px solid #E0E6ED;">
                        Annuler
                    </a>
                </div>

            </form>

            {{-- Info note --}}
            <div class="mt-6 flex items-start gap-3 px-4 py-3 rounded-xl" style="background: rgba(27,79,114,0.05); border: 1px solid rgba(27,79,114,0.1);">
                <svg class="w-4 h-4 flex-shrink-0 mt-0.5" style="color: #1B4F72;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <p class="text-xs" style="color: #6B7B8D;">
                    Les paiements manuels (BaridiMob, BEA, PayPal) sont vérifiés par notre équipe sous 24–48h.
                    Le paiement Stripe est confirmé instantanément.
                </p>
            </div>

        </div>
    </div>

    <script>
        document.getElementById('proof').addEventListener('change', function(e) {
            const preview = document.getElementById('proofPreview');
            const image   = document.getElementById('proofImage');
            if (e.target.files[0]) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    image.src = e.target.result;
                    preview.classList.remove('hidden');
                };
                reader.readAsDataURL(e.target.files[0]);
            }
        });

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
