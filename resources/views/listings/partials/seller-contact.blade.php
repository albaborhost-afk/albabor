                    {{-- ======== SELLER CARD ======== --}}
                    @php
                        $seller = $listing->user;
                        // Le nom vient du modèle : un vendeur qui publie sous
                        // « Privé » arrive déjà anonymisé, photo comprise.
                        $sellerAnonymous = (bool) $seller?->identityMasked();
                        $sellerName = $seller?->name ?: __('Vendeur');
                        $viewerIsSeller = auth()->check() && auth()->id() === $listing->user_id;
                    @endphp
                    <div class="listing-card-frame listing-seller-card rounded-3xl overflow-hidden" x-data="{ showMessage: false }">
                        {{-- Seller Header — ouvre le profil public du vendeur. Un profil
                             privé n'a pas de page publique : pas de lien (ni initiale, ni photo). --}}
                        @php $sellerHasPublicPage = $seller && ! $sellerAnonymous; @endphp
                        <div class="p-5 sm:p-6 relative annonce-seller-header">
                            @if($sellerHasPublicPage)
                                <a href="{{ route('sellers.show', $seller) }}" class="flex items-center gap-3.5 group">
                            @else
                                <div class="flex items-center gap-3.5">
                            @endif
                                <div class="relative flex-shrink-0">
                                    @if($seller?->profile_picture_url)
                                        <img src="{{ $seller->profile_picture_url }}" alt="{{ $sellerName }}"
                                             class="w-14 h-14 rounded-2xl object-cover" style="box-shadow: 0 4px 12px rgba(27,79,114,0.3);"
                                             onerror="this.onerror=null;this.style.display='none';this.nextElementSibling.style.display='flex'">
                                        <div class="w-14 h-14 gradient-primary rounded-2xl items-center justify-center text-white font-bold text-xl" style="display:none; box-shadow: 0 4px 12px rgba(27,79,114,0.3);">
                                            {{ strtoupper(substr($sellerName, 0, 1)) }}
                                        </div>
                                    @else
                                        <div class="w-14 h-14 gradient-primary rounded-2xl flex items-center justify-center text-white font-bold text-xl" style="box-shadow: 0 4px 12px rgba(27,79,114,0.3);">
                                            @if($sellerAnonymous)
                                                {{-- Silhouette : une initiale trahirait le nom --}}
                                                <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.8">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                                </svg>
                                            @else
                                                {{ strtoupper(substr($sellerName, 0, 1)) }}
                                            @endif
                                        </div>
                                    @endif
                                    @if($seller?->verified_badge ?? false)
                                        <div class="absolute -bottom-1 -right-1 w-5 h-5 rounded-full flex items-center justify-center" style="background: #27AE60; border: 2.5px solid white; box-shadow: 0 2px 4px rgba(39,174,96,0.3);">
                                            <svg class="w-2.5 h-2.5 text-white" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                                        </div>
                                    @endif
                                </div>
                                <div class="min-w-0 flex-1">
                                    <h3 class="font-bold text-base truncate {{ $sellerHasPublicPage ? 'group-hover:underline' : '' }}" style="color: #1B2A4A;">{{ $sellerName }}</h3>
                                    @if($seller?->verified_badge ?? false)
                                        <span class="inline-flex items-center gap-1 text-xs font-semibold" style="color: #27AE60;">
                                            <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M6.267 3.455a3.066 3.066 0 001.745-.723 3.066 3.066 0 013.976 0 3.066 3.066 0 001.745.723 3.066 3.066 0 012.812 2.812c.051.643.304 1.254.723 1.745a3.066 3.066 0 010 3.976 3.066 3.066 0 00-.723 1.745 3.066 3.066 0 01-2.812 2.812 3.066 3.066 0 00-1.745.723 3.066 3.066 0 01-3.976 0 3.066 3.066 0 00-1.745-.723 3.066 3.066 0 01-2.812-2.812 3.066 3.066 0 00-.723-1.745 3.066 3.066 0 010-3.976 3.066 3.066 0 00.723-1.745 3.066 3.066 0 012.812-2.812zm7.44 5.252a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                                            {{ __('Vendeur verifie') }}
                                        </span>
                                    @endif
                                    @if($sellerHasPublicPage)
                                        <p class="text-xs mt-0.5 flex items-center gap-1" style="color: #17A2B8;">
                                            {{ __('Voir toutes ses annonces') }}
                                            <svg class="w-3 h-3 transition-transform group-hover:translate-x-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
                                        </p>
                                    @elseif($sellerAnonymous)
                                        <p class="text-xs mt-0.5 flex items-center gap-1" style="color: #6B7B8D;">
                                            <svg class="w-3 h-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" /></svg>
                                            {{ __('messages.hide_name_badge') }} · {{ __('messages.private_seller_card_hint') }}
                                        </p>
                                    @endif
                                    <p class="text-xs mt-0.5" style="color: #9BA8B7;">{{ __('Membre depuis') }} {{ $seller?->created_at?->format('m/Y') ?? 'N/A' }}</p>
                                </div>
                            @if($sellerHasPublicPage)
                                </a>
                            @else
                                </div>
                            @endif

                            @if($viewerIsSeller && $seller?->hidesName())
                                {{-- Rappel au vendeur : voici ce que voient les acheteurs. --}}
                                <div class="mt-3 flex items-start gap-2 rounded-xl px-3 py-2" style="background: rgba(155,168,183,0.12);">
                                    <svg class="w-4 h-4 mt-0.5 flex-shrink-0" style="color: #6B7B8D;" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" />
                                    </svg>
                                    <p class="text-[11px] leading-snug" style="color: #6B7B8D;">
                                        {{ __('messages.private_profile_owner_reminder') }}
                                        <a href="{{ route('profile.edit') }}" class="font-semibold hover:underline" style="color: #17A2B8;">{{ __('Modifier') }}</a>
                                    </p>
                                </div>
                            @endif
                        </div>

                        {{-- Admin Quick Actions --}}
                        @auth
                            @if(auth()->user()->isAdmin())
                                <div class="px-5 pt-3 flex gap-2">
                                    <a href="{{ route('listings.edit', $listing) }}" class="flex-1 flex items-center justify-center gap-2 px-3 py-2.5 rounded-xl text-xs font-semibold transition-all duration-200 hover:-translate-y-0.5" style="background: rgba(36,113,163,0.1); color: #2471A3;">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                        {{ __('Modifier') }}
                                    </a>
                                    <a href="/admin/listings/{{ $listing->id }}/edit" class="flex-1 flex items-center justify-center gap-2 px-3 py-2.5 rounded-xl text-xs font-semibold transition-all duration-200 hover:-translate-y-0.5" style="background: rgba(27,79,114,0.1); color: #1B4F72;">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.066 2.573c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.573 1.066c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.066-2.573c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                        Admin
                                    </a>
                                </div>
                            @endif
                        @endauth

                        {{-- Action Buttons --}}
                        <div class="p-5 space-y-3">
                            <div class="flex gap-2">
                                @if(!$viewerIsSeller && !$listing->mediation_enabled)
                                    @auth
                                        <button type="button" @click="showMessage = !showMessage; $nextTick(() => { if (showMessage) $el.closest('.listing-seller-card').querySelector('textarea')?.focus(); })" class="flex-1 gradient-primary text-white rounded-xl py-3 px-4 text-sm font-semibold">{{ __('Message') }}</button>
                                    @else
                                        <a href="{{ route('login') }}" class="flex-1 gradient-primary text-white text-center rounded-xl py-3 px-4 text-sm font-semibold">{{ __('Message') }}</a>
                                    @endauth
                                @endif
                                <x-listing-share :listing="$listing" />
                            </div>

                            {{-- WhatsApp / Call / Email — masqués pour le propriétaire, si médiation ou si profil privé --}}
                            @if(!$contactHidden && !$viewerIsOwner)
                                @if($listing->numero_whatsapp)
                                    <a href="https://wa.me/{{ $waNumber }}" target="_blank" class="w-full flex items-center justify-center gap-2.5 px-4 py-3.5 text-white rounded-xl font-semibold text-sm transition-all duration-200 hover:-translate-y-0.5 cta-whatsapp-glow" style="background: linear-gradient(135deg, #25D366, #128C7E); box-shadow: 0 4px 15px rgba(37, 211, 102, 0.3);">
                                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347z M12 0C5.373 0 0 5.373 0 12c0 2.126.553 4.122 1.519 5.859L.057 24l6.305-1.654A11.954 11.954 0 0012 24c6.627 0 12-5.373 12-12S18.627 0 12 0zm0 21.892a9.875 9.875 0 01-5.031-1.378l-.361-.214-3.741.981.999-3.648-.235-.374A9.861 9.861 0 012.108 12C2.108 6.967 6.967 2.108 12 2.108S21.892 6.967 21.892 12 17.033 21.892 12 21.892z"/></svg>
                                        WhatsApp
                                    </a>
                                @endif

                                @if($listing->numero_mobile)
                                    <a href="tel:{{ $listing->numero_mobile }}" class="w-full flex items-center justify-center gap-2.5 px-4 py-3.5 text-white rounded-xl font-semibold text-sm transition-all duration-200 hover:-translate-y-0.5" style="background: linear-gradient(135deg, #2471A3, #1B4F72); box-shadow: 0 4px 15px rgba(36, 113, 163, 0.3);">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                                        {{ __('Appeler') }} {{ $listing->numero_mobile }}
                                    </a>
                                @elseif($listing->user?->phone ?? null)
                                    <a href="tel:{{ $listing->user?->phone }}" class="w-full flex items-center justify-center gap-2.5 px-4 py-3.5 text-white rounded-xl font-semibold text-sm transition-all duration-200 hover:-translate-y-0.5" style="background: linear-gradient(135deg, #2471A3, #1B4F72); box-shadow: 0 4px 15px rgba(36, 113, 163, 0.3);">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                                        {{ __('Appeler le vendeur') }}
                                    </a>
                                @endif

                                @if($listing->contact_email)
                                    <a href="mailto:{{ $listing->contact_email }}" class="w-full flex items-center justify-center gap-2 px-4 py-3 rounded-xl font-semibold text-sm transition-all duration-200 hover:-translate-y-0.5" style="color: #6B7B8D; border: 1.5px solid #E0E6ED; background: white;">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                                        {{ __('Envoyer un email') }}
                                    </a>
                                @endif
                            @endif

                            {{-- Message / Mediation / Favoris — login required --}}
                            @auth
                                @if(auth()->id() !== $listing->user_id)
                                    @if($sellerPrivate)
                                        {{-- Profil privé : aucune coordonnée, la messagerie est le seul canal --}}
                                        <div class="mb-3 flex items-start gap-2 rounded-xl px-3 py-2.5" style="background: rgba(23,162,184,0.08);">
                                            <svg class="w-4 h-4 mt-0.5 flex-shrink-0" style="color: #117A8B;" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                                            </svg>
                                            <p class="text-xs leading-snug" style="color: #117A8B;">{{ __('messages.private_seller_notice') }}</p>
                                        </div>
                                    @endif

                                    @if(!$listing->mediation_enabled)
                                    {{-- Direct Message — pre-filled like Facebook Marketplace --}}
                                    <div class="mb-3" id="contact-message-{{ $contactPlacement }}" x-show="showMessage" x-cloak>
                                        <form action="{{ route('conversations.store', $listing) }}" method="POST">
                                            @csrf
                                            <textarea name="body" rows="3" required maxlength="2000"
                                                      class="w-full px-4 py-3 rounded-xl text-sm resize-none focus:outline-none focus:ring-2 transition-all duration-200 mb-2"
                                                      style="background: #F0F4F8; border: 1.5px solid #E0E6ED; color: #1B2A4A;">{{ old('body', 'Bonjour, votre annonce « ' . Str::limit($listing->title, 40) . ' » est-elle toujours disponible ?') }}</textarea>
                                            @error('body')
                                                <p class="text-xs mb-2" style="color: #E74C3C;">{{ $message }}</p>
                                            @enderror
                                            <button type="submit" class="w-full flex items-center justify-center gap-2 px-4 py-3.5 rounded-xl text-white font-semibold text-sm transition-all duration-200 hover:-translate-y-0.5" style="background: linear-gradient(135deg, #1B4F72, #17A2B8); box-shadow: 0 4px 15px rgba(27, 79, 114, 0.3);">
                                                <svg class="w-4.5 h-4.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/></svg>
                                                {{ __('Envoyer le message') }}
                                            </button>
                                        </form>
                                    </div>

                                    @endif

                                    @if($listing->mediation_enabled)
                                        {{-- Mediation Button --}}
                                        <a href="{{ route('mediation.create', $listing) }}" class="w-full flex items-center justify-center gap-2 px-4 py-3.5 text-white rounded-xl font-semibold text-sm transition-all duration-200 hover:-translate-y-0.5 animate-pulse-glow" style="background: linear-gradient(135deg, #1B4F72, #17A2B8); box-shadow: 0 4px 15px rgba(27, 79, 114, 0.3);">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                                            {{ __('Message à l’administration') }}
                                        </a>
                                        <div class="flex items-center justify-center gap-1.5 mt-1">
                                            <svg class="w-3 h-3" style="color: #27AE60;" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M2.166 4.999A11.954 11.954 0 0010 1.944 11.954 11.954 0 0017.834 5c.11.65.166 1.32.166 2.001 0 5.225-3.34 9.67-8 11.317C5.34 16.67 2 12.225 2 7c0-.682.057-1.35.166-2.001zm11.541 3.708a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                                            <p class="text-center text-[10px] font-medium" style="color: #9BA8B7;">{{ __('Transaction securisee par AlBabor') }}</p>
                                        </div>
                                    @endif
                                @endif

                                {{-- Favorite Button --}}
                                <form action="{{ route('favorites.toggle', $listing) }}" method="POST">
                                    @csrf
                                    @php $isFavorited = auth()->user()->hasFavorited($listing) ?? false; @endphp
                                    <button type="submit" class="w-full flex items-center justify-center gap-2 px-4 py-3 rounded-xl font-semibold text-sm transition-all duration-200 hover:-translate-y-0.5"
                                            style="{{ $isFavorited ? 'background: rgba(255,107,107,0.08); color: #FF6B6B; border: 1.5px solid rgba(255,107,107,0.25); box-shadow: 0 2px 8px rgba(255,107,107,0.1);' : 'background: white; color: #6B7B8D; border: 1.5px solid #E0E6ED;' }}">
                                        <svg class="w-5 h-5" fill="{{ $isFavorited ? 'currentColor' : 'none' }}" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/></svg>
                                        {{ $isFavorited ? __('Retirer des favoris') : __('Ajouter aux favoris') }}
                                    </button>
                                </form>
                            @else
                                {{-- Guest CTA: login to message/mediate --}}
                                <div class="text-center py-5">
                                    <div class="w-16 h-16 mx-auto rounded-2xl flex items-center justify-center mb-4" style="background: linear-gradient(135deg, rgba(27,79,114,0.06), rgba(23,162,184,0.08));">
                                        <svg class="w-8 h-8" style="color: #9BA8B7;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                                    </div>
                                    @if($sellerPrivate && !$listing->mediation_enabled)
                                        <p class="text-xs mb-3 rounded-xl px-3 py-2" style="background: rgba(23,162,184,0.08); color: #117A8B;">{{ __('messages.private_seller_notice') }}</p>
                                    @endif
                                    <p class="text-sm font-medium mb-4" style="color: #6B7B8D;">{{ $listing->mediation_enabled ? __('Connectez-vous pour contacter le vendeur') : __('Connectez-vous pour envoyer un message') }}</p>
                                    <a href="{{ route('login') }}" class="block w-full px-4 py-3.5 text-white rounded-xl font-semibold text-sm text-center transition-all duration-200 hover:-translate-y-0.5" style="background: linear-gradient(135deg, #1B4F72, #17A2B8); box-shadow: 0 4px 15px rgba(27, 79, 114, 0.3);">{{ __('Se connecter') }}</a>
                                    <p class="text-xs mt-3" style="color: #9BA8B7;">{{ __('Pas encore de compte?') }} <a href="{{ route('register') }}" class="font-semibold hover:underline" style="color: #17A2B8;">{{ __('Inscrivez-vous') }}</a></p>
                                </div>
                            @endauth
                        </div>

                    </div>
