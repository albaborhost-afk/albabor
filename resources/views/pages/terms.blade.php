<x-app-layout
    :title="__('Conditions d\'utilisation')"
    :description="__('Conditions generales d\'utilisation de la marketplace AlBabor.')"
>
    <div class="min-h-screen" style="background: linear-gradient(180deg, #F0F4F8 0%, #E8EEF4 50%, #F4F8FC 100%);">

        <section class="relative overflow-hidden" style="background: linear-gradient(135deg, #102B45 0%, #1B4F72 50%, #17A2B8 100%);">
            <div class="absolute inset-0 opacity-30" style="background: radial-gradient(ellipse at top right, rgba(255,255,255,0.15), transparent 60%);"></div>
            <div class="max-w-4xl mx-auto px-5 sm:px-8 lg:px-12 pt-16 pb-14 relative">
                <div class="flex items-center gap-2 text-sm mb-5 font-medium" style="color: rgba(255,255,255,0.7);">
                    <a href="{{ route('home') }}" class="hover:text-white transition-colors">{{ __('Accueil') }}</a>
                    <span>›</span>
                    <span class="text-white">{{ __('Conditions d\'utilisation') }}</span>
                </div>
                <h1 class="text-3xl sm:text-4xl lg:text-5xl font-bold text-white leading-tight mb-4">
                    {{ __('Conditions d\'utilisation') }}
                </h1>
                <p class="text-base sm:text-lg max-w-2xl" style="color: rgba(255,255,255,0.82);">
                    {{ __('Les regles qui regissent votre utilisation d\'AlBabor. Merci de les lire attentivement.') }}
                </p>
                <div class="mt-6 inline-flex items-center gap-2 px-3 py-1.5 rounded-full text-xs font-semibold"
                     style="background: rgba(255,255,255,0.12); color: white; backdrop-filter: blur(10px);">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    {{ __('Derniere mise a jour : 21 avril 2026') }}
                </div>
            </div>
        </section>

        <section class="max-w-4xl mx-auto px-5 sm:px-8 lg:px-12 py-10 lg:py-14">
            <article class="bg-white rounded-3xl p-6 sm:p-10 lg:p-12 space-y-8"
                     style="box-shadow: 0 10px 40px rgba(27,79,114,0.08); border: 1px solid rgba(27,79,114,0.05);">

                <p class="text-base leading-relaxed" style="color: #3A4B5E;">
                    {{ __('En utilisant AlBabor, vous acceptez les presentes conditions d\'utilisation. Si vous n\'acceptez pas ces conditions, n\'utilisez pas le Service.') }}
                </p>

                <section>
                    <h2 class="text-2xl font-bold mb-3" style="color: #1B2A4A;">1. {{ __('Description du Service') }}</h2>
                    <p class="text-base leading-relaxed" style="color: #3A4B5E;">
                        {{ __('AlBabor est une marketplace qui met en relation acheteurs et vendeurs de bateaux, jet-skis, moteurs et pieces detachees en Algerie. AlBabor n\'est pas partie aux transactions conclues entre utilisateurs.') }}
                    </p>
                </section>

                <section>
                    <h2 class="text-2xl font-bold mb-3" style="color: #1B2A4A;">2. {{ __('Inscription et compte') }}</h2>
                    <ul class="list-disc ml-5 space-y-2 text-base leading-relaxed" style="color: #3A4B5E;">
                        <li>{{ __('Vous devez avoir au moins 18 ans pour creer un compte.') }}</li>
                        <li>{{ __('Vous devez fournir des informations exactes et a jour.') }}</li>
                        <li>{{ __('Vous etes seul responsable de la confidentialite de votre mot de passe.') }}</li>
                        <li>{{ __('Un seul compte par personne est autorise.') }}</li>
                    </ul>
                </section>

                <section>
                    <h2 class="text-2xl font-bold mb-3" style="color: #1B2A4A;">3. {{ __('Publication d\'annonces') }}</h2>
                    <p class="text-base leading-relaxed mb-3" style="color: #3A4B5E;">
                        {{ __('En publiant une annonce, vous declarez :') }}
                    </p>
                    <ul class="list-disc ml-5 space-y-2 text-base leading-relaxed" style="color: #3A4B5E;">
                        <li>{{ __('Etre le proprietaire legitime du bien ou avoir l\'autorisation de le vendre.') }}</li>
                        <li>{{ __('Fournir des informations exactes et non trompeuses.') }}</li>
                        <li>{{ __('Utiliser uniquement des photos que vous avez prises ou pour lesquelles vous avez les droits.') }}</li>
                        <li>{{ __('Respecter la legislation algerienne en vigueur.') }}</li>
                    </ul>
                    <p class="text-base leading-relaxed mt-3" style="color: #3A4B5E;">
                        {{ __('AlBabor se reserve le droit de moderer, modifier ou supprimer toute annonce non conforme.') }}
                    </p>
                </section>

                <section>
                    <h2 class="text-2xl font-bold mb-3" style="color: #1B2A4A;">4. {{ __('Tarifs') }}</h2>
                    <ul class="list-disc ml-5 space-y-2 text-base leading-relaxed" style="color: #3A4B5E;">
                        <li>{{ __('Publication d\'annonce (bateaux/jet-skis) : 5 000 DA pour 365 jours.') }}</li>
                        <li>{{ __('Mise en avant d\'une annonce : 12 000 DA pour 30 jours.') }}</li>
                        <li>{{ __('Abonnement vendeur (moteurs/pieces) : selon le plan choisi.') }}</li>
                        <li>{{ __('Mediation AlBabor : 500 DA par dossier.') }}</li>
                        <li>{{ __('La premiere annonce de chaque utilisateur est gratuite.') }}</li>
                    </ul>
                </section>

                <section>
                    <h2 class="text-2xl font-bold mb-3" style="color: #1B2A4A;">5. {{ __('Contenus interdits') }}</h2>
                    <p class="text-base leading-relaxed mb-3" style="color: #3A4B5E;">
                        {{ __('Les contenus suivants sont strictement interdits :') }}
                    </p>
                    <ul class="list-disc ml-5 space-y-2 text-base leading-relaxed" style="color: #3A4B5E;">
                        <li>{{ __('Produits illegaux, contrefaits ou voles') }}</li>
                        <li>{{ __('Contenus discriminatoires, diffamatoires ou haineux') }}</li>
                        <li>{{ __('Tentatives d\'arnaque, de fraude ou d\'usurpation d\'identite') }}</li>
                        <li>{{ __('Publicites pour d\'autres sites ou services') }}</li>
                        <li>{{ __('Tout contenu violant la loi algerienne') }}</li>
                    </ul>
                </section>

                <section>
                    <h2 class="text-2xl font-bold mb-3" style="color: #1B2A4A;">6. {{ __('Responsabilite') }}</h2>
                    <p class="text-base leading-relaxed" style="color: #3A4B5E;">
                        {{ __('AlBabor agit en qualite d\'hebergeur et non de vendeur. Les transactions sont conclues directement entre utilisateurs. Nous ne garantissons pas la qualite, la legalite ou l\'exactitude des annonces. Il appartient a chaque utilisateur de verifier le bien avant achat.') }}
                    </p>
                </section>

                <section>
                    <h2 class="text-2xl font-bold mb-3" style="color: #1B2A4A;">7. {{ __('Suspension et resiliation') }}</h2>
                    <p class="text-base leading-relaxed" style="color: #3A4B5E;">
                        {{ __('Nous nous reservons le droit de suspendre ou supprimer tout compte qui enfreint les presentes conditions. Vous pouvez supprimer votre compte a tout moment en nous contactant.') }}
                    </p>
                </section>

                <section>
                    <h2 class="text-2xl font-bold mb-3" style="color: #1B2A4A;">8. {{ __('Droit applicable') }}</h2>
                    <p class="text-base leading-relaxed" style="color: #3A4B5E;">
                        {{ __('Les presentes conditions sont regies par le droit algerien. Tout litige sera de la competence exclusive des tribunaux d\'Alger.') }}
                    </p>
                </section>

                <section>
                    <h2 class="text-2xl font-bold mb-3" style="color: #1B2A4A;">9. {{ __('Contact') }}</h2>
                    <div class="rounded-xl p-4" style="background: #F0F4F8;">
                        <p class="text-base" style="color: #3A4B5E;">
                            {{ __('Pour toute question concernant ces conditions :') }}
                            <a href="mailto:contact@albabor.com" class="font-semibold" style="color: #17A2B8;">contact@albabor.com</a>
                        </p>
                    </div>
                </section>
            </article>
        </section>
    </div>
</x-app-layout>
