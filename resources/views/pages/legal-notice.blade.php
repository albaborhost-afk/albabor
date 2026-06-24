<x-app-layout
    :title="__('Mentions legales et avertissement')"
    :description="__('Mentions legales, role de la plateforme, avertissement et limitation de responsabilite de la marketplace AlBabor.')"
>
    <div class="min-h-screen" style="background: linear-gradient(180deg, #F0F4F8 0%, #E8EEF4 50%, #F4F8FC 100%);">

        <section class="relative overflow-hidden" style="background: linear-gradient(135deg, #102B45 0%, #1B4F72 50%, #17A2B8 100%);">
            <div class="absolute inset-0 opacity-30" style="background: radial-gradient(ellipse at top right, rgba(255,255,255,0.15), transparent 60%);"></div>
            <div class="max-w-4xl mx-auto px-5 sm:px-8 lg:px-12 pt-16 pb-14 relative">
                <div class="flex items-center gap-2 text-sm mb-5 font-medium" style="color: rgba(255,255,255,0.7);">
                    <a href="{{ route('home') }}" class="hover:text-white transition-colors">{{ __('Accueil') }}</a>
                    <span>›</span>
                    <span class="text-white">{{ __('Mentions legales') }}</span>
                </div>
                <h1 class="text-3xl sm:text-4xl lg:text-5xl font-bold text-white leading-tight mb-4">
                    {{ __('Mentions legales et avertissement') }}
                </h1>
                <p class="text-base sm:text-lg max-w-2xl" style="color: rgba(255,255,255,0.82);">
                    {{ __('Informations importantes concernant le role d\'Albabor, l\'utilisation du site et les transactions entre utilisateurs.') }}
                </p>
                <div class="mt-6 inline-flex items-center gap-2 px-3 py-1.5 rounded-full text-xs font-semibold"
                     style="background: rgba(255,255,255,0.12); color: white; backdrop-filter: blur(10px);">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    {{ __('Derniere mise a jour : 24 juin 2026') }}
                </div>
            </div>
        </section>

        <section class="max-w-4xl mx-auto px-5 sm:px-8 lg:px-12 py-10 lg:py-14">
            <article class="bg-white rounded-3xl p-6 sm:p-10 lg:p-12 space-y-8"
                     style="box-shadow: 0 10px 40px rgba(27,79,114,0.08); border: 1px solid rgba(27,79,114,0.05);">

                <p class="text-base leading-relaxed" style="color: #3A4B5E;">
                    {{ __('Les informations, photos, videos et descriptions publiees sur Albabor.com sont fournies a titre informatif uniquement.') }}
                </p>

                <section>
                    <h2 class="text-2xl font-bold mb-3" style="color: #1B2A4A;">1. {{ __('Role d\'Albabor.com') }}</h2>
                    <p class="text-base leading-relaxed mb-3" style="color: #3A4B5E;">
                        {{ __('Albabor.com est une plateforme de publicite et de mise en relation entre vendeurs et acheteurs de bateaux.') }}
                    </p>
                    <p class="text-base leading-relaxed mb-3" style="color: #3A4B5E;">
                        {{ __('Albabor.com ne vend pas les bateaux et n\'intervient pas dans :') }}
                    </p>
                    <ul class="list-disc ml-5 space-y-2 text-base leading-relaxed" style="color: #3A4B5E;">
                        <li>{{ __('la negociation des prix,') }}</li>
                        <li>{{ __('les accords entre les parties,') }}</li>
                        <li>{{ __('ou la transaction finale.') }}</li>
                    </ul>
                    <p class="text-base leading-relaxed mt-3 mb-3" style="color: #3A4B5E;">
                        {{ __('Les echanges, negociations et conditions de vente sont traites directement entre le proprietaire du bateau et l\'acheteur.') }}
                    </p>
                    <p class="text-base leading-relaxed mb-3" style="color: #3A4B5E;">
                        {{ __('Le vendeur reste seul responsable :') }}
                    </p>
                    <ul class="list-disc ml-5 space-y-2 text-base leading-relaxed" style="color: #3A4B5E;">
                        <li>{{ __('du prix affiche,') }}</li>
                        <li>{{ __('des informations publiees,') }}</li>
                        <li>{{ __('et de la vente du bateau.') }}</li>
                    </ul>
                    <p class="text-base leading-relaxed mt-3" style="color: #3A4B5E;">
                        {{ __('Albabor.com ne pourra etre tenu responsable des accords, litiges ou transactions entre vendeur et acheteur.') }}
                    </p>
                </section>

                <section>
                    <h2 class="text-2xl font-bold mb-3" style="color: #1B2A4A;">2. {{ __('Exactitude des informations') }}</h2>
                    <p class="text-base leading-relaxed" style="color: #3A4B5E;">
                        {{ __('Nous faisons notre possible pour assurer l\'exactitude des informations, mais nous ne garantissons ni l\'etat du bateau, ni l\'exactitude complete des donnees fournies par le vendeur.') }}
                    </p>
                </section>

                <section>
                    <h2 class="text-2xl font-bold mb-3" style="color: #1B2A4A;">3. {{ __('Verifications avant achat') }}</h2>
                    <p class="text-base leading-relaxed" style="color: #3A4B5E;">
                        {{ __('Les acheteurs doivent effectuer leurs propres verifications, inspections et essais avant tout achat.') }}
                    </p>
                </section>

                <section>
                    <h2 class="text-2xl font-bold mb-3" style="color: #1B2A4A;">4. {{ __('Vente « en l\'etat » (AS IS)') }}</h2>
                    <p class="text-base leading-relaxed" style="color: #3A4B5E;">
                        {{ __('Sauf indication ecrite contraire, tous les bateaux sont vendus « en l\'etat » (« AS IS »), sans garantie.') }}
                    </p>
                </section>

                <section>
                    <h2 class="text-2xl font-bold mb-3" style="color: #1B2A4A;">5. {{ __('Limitation de responsabilite') }}</h2>
                    <p class="text-base leading-relaxed mb-3" style="color: #3A4B5E;">
                        {{ __('Albabor.com agit uniquement comme plateforme de publication / intermediaire et ne pourra etre tenu responsable :') }}
                    </p>
                    <ul class="list-disc ml-5 space-y-2 text-base leading-relaxed" style="color: #3A4B5E;">
                        <li>{{ __('des defauts caches,') }}</li>
                        <li>{{ __('des erreurs dans les annonces,') }}</li>
                        <li>{{ __('des litiges entre acheteur et vendeur,') }}</li>
                        <li>{{ __('des pertes financieres ou dommages lies a une transaction.') }}</li>
                    </ul>
                </section>

                <section>
                    <h2 class="text-2xl font-bold mb-3" style="color: #1B2A4A;">6. {{ __('Photos et videos') }}</h2>
                    <p class="text-base leading-relaxed" style="color: #3A4B5E;">
                        {{ __('Les photos et videos representent l\'etat du bateau a la date de publication et peuvent changer avec le temps.') }}
                    </p>
                </section>

                <section>
                    <h2 class="text-2xl font-bold mb-3" style="color: #1B2A4A;">7. {{ __('Acceptation') }}</h2>
                    <div class="rounded-xl p-4" style="background: #F0F4F8;">
                        <p class="text-base font-medium" style="color: #1B2A4A;">
                            {{ __('En utilisant ce site, vous acceptez ces conditions.') }}
                        </p>
                    </div>
                </section>
            </article>
        </section>
    </div>
</x-app-layout>
