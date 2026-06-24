<x-app-layout
    :title="__('Autorisation de vente et publication')"
    :description="__('Formulaire d\'autorisation de vente et de publication a imprimer et signer par le proprietaire du bateau.')"
>
    <div class="min-h-screen" style="background: linear-gradient(180deg, #F0F4F8 0%, #E8EEF4 50%, #F4F8FC 100%);">

        {{-- Hero (cache a l'impression) --}}
        <section class="no-print relative overflow-hidden" style="background: linear-gradient(135deg, #102B45 0%, #1B4F72 50%, #17A2B8 100%);">
            <div class="absolute inset-0 opacity-30" style="background: radial-gradient(ellipse at top right, rgba(255,255,255,0.15), transparent 60%);"></div>
            <div class="max-w-4xl mx-auto px-5 sm:px-8 lg:px-12 pt-16 pb-14 relative">
                <div class="flex items-center gap-2 text-sm mb-5 font-medium" style="color: rgba(255,255,255,0.7);">
                    <a href="{{ route('home') }}" class="hover:text-white transition-colors">{{ __('Accueil') }}</a>
                    <span>›</span>
                    <span class="text-white">{{ __('Autorisation de vente') }}</span>
                </div>
                <h1 class="text-3xl sm:text-4xl lg:text-5xl font-bold text-white leading-tight mb-4">
                    {{ __('Autorisation de vente et publication') }}
                </h1>
                <p class="text-base sm:text-lg max-w-2xl mb-6" style="color: rgba(255,255,255,0.82);">
                    {{ __('Formulaire a imprimer, remplir et signer par le proprietaire du bateau. Vous pouvez aussi l\'enregistrer en PDF via la fonction d\'impression.') }}
                </p>
                <button type="button" onclick="window.print()" class="inline-flex items-center gap-2 px-5 py-3 rounded-xl font-semibold text-sm transition-all duration-200 hover:-translate-y-0.5" style="background: white; color: #1B4F72; box-shadow: 0 6px 20px rgba(0,0,0,0.18);">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                    {{ __('Imprimer / Enregistrer en PDF') }}
                </button>
            </div>
        </section>

        {{-- Document imprimable --}}
        <section class="max-w-4xl mx-auto px-5 sm:px-8 lg:px-12 py-10 lg:py-14">
            <article id="autorisation-doc" class="bg-white rounded-3xl p-6 sm:p-10 lg:p-12"
                     style="box-shadow: 0 10px 40px rgba(27,79,114,0.08); border: 1px solid rgba(27,79,114,0.05); color: #25323F;">

                {{-- En-tete du document --}}
                <div class="text-center pb-6 mb-7" style="border-bottom: 2px solid #1B2A4A;">
                    <img src="/images/logo-full.png" alt="AlBabor" style="height: 38px; margin: 0 auto 14px;">
                    <h2 class="text-xl sm:text-2xl font-bold" style="color: #1B2A4A; letter-spacing: 0.02em;">{{ __('AUTORISATION DE VENTE ET PUBLICATION') }}</h2>
                    <p class="text-sm mt-1" style="color: #6B7B8D;">Albabor.com</p>
                    <div class="mt-4 text-sm flex items-center justify-center gap-2" style="color: #3A4B5E;">
                        <span class="font-medium">{{ __('Date') }} :</span>
                        <span class="auth-fill" style="flex: 0 0 180px; min-width: 180px;"></span>
                    </div>
                </div>

                {{-- 1. Proprietaire --}}
                <section class="mb-7">
                    <h3 class="auth-h">1. {{ __('Informations du proprietaire') }}</h3>
                    <div class="auth-row"><span class="auth-label">{{ __('Nom du proprietaire') }} :</span><span class="auth-fill"></span></div>
                    <div class="auth-row"><span class="auth-label">{{ __('Role (proprietaire / representant / agent)') }} :</span><span class="auth-fill"></span></div>
                    <div class="auth-row"><span class="auth-label">{{ __('Numero d\'identification (CIN / Passeport / ID)') }} :</span><span class="auth-fill"></span></div>
                    <div class="auth-row"><span class="auth-label">{{ __('Telephone') }} :</span><span class="auth-fill"></span></div>
                </section>

                {{-- 2. Bateau --}}
                <section class="mb-7">
                    <h3 class="auth-h">2. {{ __('Informations du bateau') }}</h3>
                    <div class="auth-row"><span class="auth-label">{{ __('Nom du bateau') }} :</span><span class="auth-fill"></span></div>
                    <div class="auth-row"><span class="auth-label">{{ __('Marque / Modele') }} :</span><span class="auth-fill"></span></div>
                    <div class="auth-row"><span class="auth-label">{{ __('Numero d\'immatriculation ou HIN') }} :</span><span class="auth-fill"></span></div>
                </section>

                {{-- 3. Autorisation --}}
                <section class="mb-7">
                    <h3 class="auth-h">3. {{ __('Autorisation') }}</h3>
                    <p class="auth-text">{{ __('Je soussigne(e), proprietaire du bateau mentionne ci-dessus, autorise Albabor.com a :') }}</p>
                    <ul class="auth-list">
                        <li>{{ __('photographier et filmer le bateau') }}</li>
                        <li>{{ __('publier les annonces sur le site et les reseaux sociaux') }}</li>
                        <li>{{ __('promouvoir et diffuser l\'annonce pour la vente') }}</li>
                    </ul>
                </section>

                {{-- 4. Declaration --}}
                <section class="mb-7">
                    <h3 class="auth-h">4. {{ __('Declaration') }}</h3>
                    <p class="auth-text mb-2">{{ __('Je confirme etre le proprietaire legal ou dument autorise a vendre ce bateau.') }}</p>
                    <p class="auth-text">{{ __('Je confirme que les informations fournies sont exactes a ma connaissance.') }}</p>
                </section>

                {{-- 5. Role d'Albabor.com --}}
                <section class="mb-7">
                    <h3 class="auth-h">5. {{ __('Role d\'Albabor.com') }}</h3>
                    <p class="auth-text mb-2">{{ __('Albabor.com est uniquement une plateforme de publicite et de mise en relation.') }}</p>
                    <p class="auth-text">{{ __('Albabor.com ne vend pas les bateaux et ne participe pas aux negociations ou a la vente.') }}</p>
                </section>

                {{-- 6. Signature --}}
                <section>
                    <h3 class="auth-h">6. {{ __('Signature') }}</h3>
                    <div class="auth-row"><span class="auth-label">{{ __('Nom du proprietaire') }} :</span><span class="auth-fill"></span></div>
                    <div class="mt-4">
                        <span class="auth-label text-sm" style="color: #3A4B5E;">{{ __('Signature') }} :</span>
                        <div class="auth-sign"></div>
                    </div>
                    <div class="auth-row mt-5"><span class="auth-label">{{ __('Date') }} :</span><span class="auth-fill" style="flex: 0 0 200px; min-width: 200px;"></span></div>
                </section>
            </article>
        </section>
    </div>

    <style>
        #autorisation-doc .auth-h { font-size: 1rem; font-weight: 700; color: #1B2A4A; margin-bottom: 14px; padding-bottom: 6px; border-bottom: 1px solid #E0E6ED; }
        #autorisation-doc .auth-row { display: flex; flex-wrap: wrap; align-items: flex-end; gap: 6px 10px; margin-bottom: 16px; font-size: 0.9rem; color: #3A4B5E; }
        #autorisation-doc .auth-label { font-weight: 500; }
        #autorisation-doc .auth-fill { flex: 1 1 200px; min-width: 160px; border-bottom: 1px solid #1B2A4A; height: 1.5em; }
        #autorisation-doc .auth-text { font-size: 0.9rem; line-height: 1.65; color: #3A4B5E; }
        #autorisation-doc .auth-list { list-style: disc; margin-left: 1.25rem; margin-top: 8px; font-size: 0.9rem; line-height: 1.6; color: #3A4B5E; }
        #autorisation-doc .auth-list li { margin-bottom: 6px; }
        #autorisation-doc .auth-sign { border-bottom: 1px solid #1B2A4A; height: 3em; margin-top: 8px; }
        @media print {
            nav, footer, .no-print { display: none !important; }
            #autorisation-doc { box-shadow: none !important; border: none !important; border-radius: 0 !important; padding: 0 !important; }
            #autorisation-doc section { page-break-inside: avoid; break-inside: avoid; }
            @page { size: A4; margin: 16mm; }
        }
    </style>
</x-app-layout>
