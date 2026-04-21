<x-app-layout
    :title="__('Politique de confidentialite')"
    :description="__('Politique de confidentialite AlBabor — comment nous collectons, utilisons et protegeons vos donnees personnelles.')"
>
    <div class="min-h-screen" style="background: linear-gradient(180deg, #F0F4F8 0%, #E8EEF4 50%, #F4F8FC 100%);">

        {{-- ═══════════════════════════════ HERO ═══════════════════════════════ --}}
        <section class="relative overflow-hidden" style="background: linear-gradient(135deg, #102B45 0%, #1B4F72 50%, #17A2B8 100%);">
            <div class="absolute inset-0 opacity-30" style="background: radial-gradient(ellipse at top right, rgba(255,255,255,0.15), transparent 60%);"></div>
            <div class="max-w-4xl mx-auto px-5 sm:px-8 lg:px-12 pt-16 pb-14 relative">
                <div class="flex items-center gap-2 text-sm mb-5 font-medium" style="color: rgba(255,255,255,0.7);">
                    <a href="{{ route('home') }}" class="hover:text-white transition-colors">{{ __('Accueil') }}</a>
                    <span>›</span>
                    <span class="text-white">{{ __('Politique de confidentialite') }}</span>
                </div>
                <h1 class="text-3xl sm:text-4xl lg:text-5xl font-bold text-white leading-tight mb-4">
                    {{ __('Politique de confidentialite') }}
                </h1>
                <p class="text-base sm:text-lg max-w-2xl" style="color: rgba(255,255,255,0.82);">
                    {{ __('Votre vie privee compte. Cette page explique clairement quelles donnees nous collectons, comment nous les utilisons et vos droits.') }}
                </p>
                <div class="mt-6 inline-flex items-center gap-2 px-3 py-1.5 rounded-full text-xs font-semibold"
                     style="background: rgba(255,255,255,0.12); color: white; backdrop-filter: blur(10px);">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    {{ __('Derniere mise a jour : 21 avril 2026') }}
                </div>
            </div>
        </section>

        {{-- ═══════════════════════════════ CONTENT ═══════════════════════════════ --}}
        <section class="max-w-4xl mx-auto px-5 sm:px-8 lg:px-12 py-10 lg:py-14">

            <div class="grid grid-cols-1 lg:grid-cols-[260px_1fr] gap-8">

                {{-- ──────── Table of contents (sticky) ──────── --}}
                <aside class="hidden lg:block">
                    <nav class="sticky top-24 rounded-2xl bg-white p-5 text-sm"
                         style="box-shadow: 0 8px 28px rgba(27,79,114,0.08); border: 1px solid rgba(27,79,114,0.06);">
                        <p class="text-xs font-bold uppercase tracking-widest mb-4" style="color: #6B7B8D;">{{ __('Sommaire') }}</p>
                        <ol class="space-y-2.5" style="color: #1B2A4A;">
                            @foreach ([
                                ['1', 'qui-sommes-nous', __('Qui sommes-nous ?')],
                                ['2', 'donnees-collectees', __('Donnees que nous collectons')],
                                ['3', 'utilisation', __('Comment nous utilisons vos donnees')],
                                ['4', 'partage', __('Partage de vos donnees')],
                                ['5', 'stockage', __('Stockage et securite')],
                                ['6', 'conservation', __('Duree de conservation')],
                                ['7', 'droits', __('Vos droits')],
                                ['8', 'cookies', __('Cookies')],
                                ['9', 'enfants', __('Mineurs')],
                                ['10', 'modifications', __('Modifications')],
                                ['11', 'contact', __('Contact')],
                            ] as $item)
                                <li>
                                    <a href="#{{ $item[1] }}" class="flex items-start gap-2 hover:text-[#17A2B8] transition-colors leading-snug">
                                        <span class="font-semibold" style="color: #17A2B8;">{{ $item[0] }}.</span>
                                        <span>{{ $item[2] }}</span>
                                    </a>
                                </li>
                            @endforeach
                        </ol>
                    </nav>
                </aside>

                {{-- ──────── Main content ──────── --}}
                <article class="bg-white rounded-3xl p-6 sm:p-10 lg:p-12 space-y-10"
                         style="box-shadow: 0 10px 40px rgba(27,79,114,0.08); border: 1px solid rgba(27,79,114,0.05);">

                    <p class="text-base leading-relaxed" style="color: #3A4B5E;">
                        {{ __('La presente politique de confidentialite decrit la maniere dont AlBabor (ci-apres « nous », « notre », « AlBabor ») collecte, utilise, stocke et partage les informations personnelles lorsque vous utilisez notre site web') }}
                        <a href="{{ route('home') }}" class="font-semibold" style="color: #17A2B8;">albabor.com</a>
                        {{ __('et notre application mobile (ci-apres « le Service »).') }}
                    </p>

                    {{-- 1. WHO WE ARE --}}
                    <section id="qui-sommes-nous" class="scroll-mt-24">
                        <h2 class="text-2xl font-bold mb-4" style="color: #1B2A4A;">1. {{ __('Qui sommes-nous ?') }}</h2>
                        <div class="space-y-3 text-base leading-relaxed" style="color: #3A4B5E;">
                            <p>{{ __('AlBabor est une marketplace nautique basee en Algerie, specialisee dans l\'achat et la vente de bateaux, jet-skis, moteurs et pieces detachees. Nous facilitons la mise en relation entre acheteurs et vendeurs.') }}</p>
                            <p>{{ __('Responsable du traitement :') }}</p>
                            <ul class="list-disc ml-5 space-y-1">
                                <li>{{ __('Entite :') }} <strong>AlBabor</strong></li>
                                <li>{{ __('Email contact :') }} <a href="mailto:contact@albabor.dz" class="font-semibold" style="color: #17A2B8;">contact@albabor.dz</a></li>
                                <li>{{ __('Adresse :') }} {{ __('Alger, Algerie') }}</li>
                            </ul>
                        </div>
                    </section>

                    {{-- 2. DATA COLLECTED --}}
                    <section id="donnees-collectees" class="scroll-mt-24">
                        <h2 class="text-2xl font-bold mb-4" style="color: #1B2A4A;">2. {{ __('Donnees que nous collectons') }}</h2>
                        <p class="text-base leading-relaxed mb-5" style="color: #3A4B5E;">
                            {{ __('Selon votre utilisation du Service, nous collectons les categories de donnees suivantes :') }}
                        </p>

                        <div class="space-y-4">
                            {{-- Account data --}}
                            <div class="rounded-xl p-4 sm:p-5" style="background: #F0F4F8; border-left: 4px solid #17A2B8;">
                                <h3 class="font-bold mb-2 flex items-center gap-2" style="color: #1B2A4A;">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                                    {{ __('Donnees de compte') }}
                                </h3>
                                <p class="text-sm" style="color: #3A4B5E;">
                                    {{ __('Nom, prenom, adresse email, numero de telephone, mot de passe (stocke sous forme hachee), photo de profil (optionnel).') }}
                                </p>
                            </div>

                            {{-- Listing data --}}
                            <div class="rounded-xl p-4 sm:p-5" style="background: #F0F4F8; border-left: 4px solid #2471A3;">
                                <h3 class="font-bold mb-2 flex items-center gap-2" style="color: #1B2A4A;">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6z"/></svg>
                                    {{ __('Donnees des annonces') }}
                                </h3>
                                <p class="text-sm" style="color: #3A4B5E;">
                                    {{ __('Photos, description, prix, wilaya, caracteristiques techniques, coordonnees de contact (WhatsApp, telephone, email) que vous publiez dans vos annonces.') }}
                                </p>
                            </div>

                            {{-- Verification data --}}
                            <div class="rounded-xl p-4 sm:p-5" style="background: #FEF3E7; border-left: 4px solid #F39C12;">
                                <h3 class="font-bold mb-2 flex items-center gap-2" style="color: #1B2A4A;">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                                    {{ __('Donnees de verification (sensibles)') }}
                                </h3>
                                <p class="text-sm" style="color: #3A4B5E;">
                                    {{ __('Si vous choisissez de verifier votre identite pour obtenir le badge « Vendeur verifie », nous collectons une copie de votre piece d\'identite. Ces documents sont stockes de maniere chiffree et ne sont jamais partages avec d\'autres utilisateurs.') }}
                                </p>
                            </div>

                            {{-- Communication --}}
                            <div class="rounded-xl p-4 sm:p-5" style="background: #F0F4F8; border-left: 4px solid #17A2B8;">
                                <h3 class="font-bold mb-2 flex items-center gap-2" style="color: #1B2A4A;">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
                                    {{ __('Messages et mediation') }}
                                </h3>
                                <p class="text-sm" style="color: #3A4B5E;">
                                    {{ __('Contenu des messages echanges avec d\'autres utilisateurs et des tickets de mediation AlBabor.') }}
                                </p>
                            </div>

                            {{-- Payment data --}}
                            <div class="rounded-xl p-4 sm:p-5" style="background: #F0F4F8; border-left: 4px solid #27AE60;">
                                <h3 class="font-bold mb-2 flex items-center gap-2" style="color: #1B2A4A;">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>
                                    {{ __('Donnees de paiement') }}
                                </h3>
                                <p class="text-sm" style="color: #3A4B5E;">
                                    {{ __('Mode de paiement (BaridiMob, CCP, virement, PayPal, RedotPay, carte bancaire via Stripe), preuve de paiement (image) que vous telechargez. Nous ne stockons jamais les numeros de carte bancaire — ceux-ci sont traites directement par Stripe.') }}
                                </p>
                            </div>

                            {{-- Technical --}}
                            <div class="rounded-xl p-4 sm:p-5" style="background: #F0F4F8; border-left: 4px solid #6B7B8D;">
                                <h3 class="font-bold mb-2 flex items-center gap-2" style="color: #1B2A4A;">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                                    {{ __('Donnees techniques') }}
                                </h3>
                                <p class="text-sm" style="color: #3A4B5E;">
                                    {{ __('Adresse IP (hachee pour comptabiliser les vues uniques), type de navigateur, systeme d\'exploitation, pages visitees, date et heure de visite.') }}
                                </p>
                            </div>
                        </div>
                    </section>

                    {{-- 3. USE --}}
                    <section id="utilisation" class="scroll-mt-24">
                        <h2 class="text-2xl font-bold mb-4" style="color: #1B2A4A;">3. {{ __('Comment nous utilisons vos donnees') }}</h2>
                        <p class="text-base leading-relaxed mb-4" style="color: #3A4B5E;">
                            {{ __('Nous utilisons vos donnees personnelles uniquement aux fins suivantes :') }}
                        </p>
                        <ul class="space-y-2 ml-1" style="color: #3A4B5E;">
                            @foreach ([
                                __('Creer et gerer votre compte utilisateur'),
                                __('Afficher vos annonces publiquement sur la plateforme'),
                                __('Faciliter la mise en relation entre acheteurs et vendeurs'),
                                __('Verifier votre identite lorsque vous le demandez (badge « Vendeur verifie »)'),
                                __('Traiter les paiements liees a la publication d\'annonces, mises en avant, abonnements et mediations'),
                                __('Envoyer des emails de service (confirmation d\'inscription, reinitialisation de mot de passe, notifications importantes)'),
                                __('Prevenir la fraude, les abus et garantir la securite de la plateforme'),
                                __('Respecter nos obligations legales et fiscales en Algerie'),
                                __('Ameliorer le Service et corriger les bugs'),
                            ] as $use)
                                <li class="flex items-start gap-2.5">
                                    <svg class="w-4 h-4 mt-1 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20" style="color: #17A2B8;"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                                    <span>{{ $use }}</span>
                                </li>
                            @endforeach
                        </ul>
                        <p class="text-sm mt-4 italic" style="color: #6B7B8D;">
                            {{ __('Nous ne vendons jamais vos donnees personnelles a des tiers. Nous n\'utilisons pas vos donnees pour de la publicite ciblee.') }}
                        </p>
                    </section>

                    {{-- 4. SHARING --}}
                    <section id="partage" class="scroll-mt-24">
                        <h2 class="text-2xl font-bold mb-4" style="color: #1B2A4A;">4. {{ __('Partage de vos donnees') }}</h2>
                        <p class="text-base leading-relaxed mb-4" style="color: #3A4B5E;">
                            {{ __('Nous partageons vos donnees uniquement dans les cas suivants :') }}
                        </p>
                        <div class="space-y-3">
                            <div class="rounded-xl p-4" style="background: #F0F4F8;">
                                <h3 class="font-semibold mb-1" style="color: #1B2A4A;">{{ __('Autres utilisateurs') }}</h3>
                                <p class="text-sm" style="color: #3A4B5E;">
                                    {{ __('Votre nom, photo de profil, wilaya et coordonnees de contact (WhatsApp, telephone, email) sont visibles par les autres utilisateurs lorsqu\'ils consultent vos annonces, sauf si vous activez la mediation AlBabor qui masque vos coordonnees.') }}
                                </p>
                            </div>
                            <div class="rounded-xl p-4" style="background: #F0F4F8;">
                                <h3 class="font-semibold mb-1" style="color: #1B2A4A;">Stripe</h3>
                                <p class="text-sm" style="color: #3A4B5E;">
                                    {{ __('Pour le traitement des paiements par carte bancaire. Stripe traite les donnees conformement a sa propre politique de confidentialite.') }}
                                </p>
                            </div>
                            <div class="rounded-xl p-4" style="background: #F0F4F8;">
                                <h3 class="font-semibold mb-1" style="color: #1B2A4A;">Google (Sign-In)</h3>
                                <p class="text-sm" style="color: #3A4B5E;">
                                    {{ __('Si vous choisissez de vous connecter avec Google, nous recevons de Google votre email et nom. Nous ne partageons aucune donnee avec Google.') }}
                                </p>
                            </div>
                            <div class="rounded-xl p-4" style="background: #F0F4F8;">
                                <h3 class="font-semibold mb-1" style="color: #1B2A4A;">{{ __('Prestataires techniques') }}</h3>
                                <p class="text-sm" style="color: #3A4B5E;">
                                    {{ __('Hebergement (Laravel Cloud), stockage d\'images (Amazon Web Services), envoi d\'emails transactionnels. Ces prestataires agissent comme sous-traitants et ne peuvent utiliser vos donnees qu\'a notre demande.') }}
                                </p>
                            </div>
                            <div class="rounded-xl p-4" style="background: #F0F4F8;">
                                <h3 class="font-semibold mb-1" style="color: #1B2A4A;">{{ __('Obligations legales') }}</h3>
                                <p class="text-sm" style="color: #3A4B5E;">
                                    {{ __('Nous pouvons divulguer vos donnees si la loi nous y oblige, en reponse a une procedure judiciaire valide ou pour proteger nos droits.') }}
                                </p>
                            </div>
                        </div>
                    </section>

                    {{-- 5. STORAGE & SECURITY --}}
                    <section id="stockage" class="scroll-mt-24">
                        <h2 class="text-2xl font-bold mb-4" style="color: #1B2A4A;">5. {{ __('Stockage et securite') }}</h2>
                        <div class="space-y-3 text-base leading-relaxed" style="color: #3A4B5E;">
                            <p>
                                {{ __('Nous prenons la securite de vos donnees tres au serieux et appliquons les mesures suivantes :') }}
                            </p>
                            <ul class="list-disc ml-5 space-y-1">
                                <li>{{ __('Connexion HTTPS (TLS) obligatoire sur toutes les pages du Service') }}</li>
                                <li>{{ __('Mots de passe hashes avec bcrypt (jamais stockes en clair)') }}</li>
                                <li>{{ __('Tokens d\'authentification stockes de maniere securisee et exclus des sauvegardes cloud automatiques') }}</li>
                                <li>{{ __('Donnees hebergees sur des serveurs proteges avec acces restreint')}}</li>
                                <li>{{ __('Limitation de taux (rate limiting) pour prevenir les attaques par force brute') }}</li>
                                <li>{{ __('Moderation humaine pour la verification d\'identite et les paiements') }}</li>
                            </ul>
                            <p class="text-sm italic" style="color: #6B7B8D;">
                                {{ __('Aucun systeme n\'est parfaitement securise. Si vous suspectez une compromission de votre compte, contactez-nous immediatement.') }}
                            </p>
                        </div>
                    </section>

                    {{-- 6. RETENTION --}}
                    <section id="conservation" class="scroll-mt-24">
                        <h2 class="text-2xl font-bold mb-4" style="color: #1B2A4A;">6. {{ __('Duree de conservation') }}</h2>
                        <div class="overflow-x-auto">
                            <table class="w-full text-sm" style="border-collapse: collapse;">
                                <thead>
                                    <tr style="background: #F0F4F8;">
                                        <th class="text-left p-3 font-semibold" style="color: #1B2A4A;">{{ __('Type de donnees') }}</th>
                                        <th class="text-left p-3 font-semibold" style="color: #1B2A4A;">{{ __('Duree') }}</th>
                                    </tr>
                                </thead>
                                <tbody style="color: #3A4B5E;">
                                    <tr style="border-bottom: 1px solid #E0E6ED;">
                                        <td class="p-3">{{ __('Compte utilisateur actif') }}</td>
                                        <td class="p-3">{{ __('Jusqu\'a la suppression du compte') }}</td>
                                    </tr>
                                    <tr style="border-bottom: 1px solid #E0E6ED;">
                                        <td class="p-3">{{ __('Annonces publiees') }}</td>
                                        <td class="p-3">{{ __('Jusqu\'a 365 jours apres publication') }}</td>
                                    </tr>
                                    <tr style="border-bottom: 1px solid #E0E6ED;">
                                        <td class="p-3">{{ __('Documents de verification') }}</td>
                                        <td class="p-3">{{ __('3 ans apres validation') }}</td>
                                    </tr>
                                    <tr style="border-bottom: 1px solid #E0E6ED;">
                                        <td class="p-3">{{ __('Registres de paiement') }}</td>
                                        <td class="p-3">{{ __('10 ans (obligations comptables)') }}</td>
                                    </tr>
                                    <tr>
                                        <td class="p-3">{{ __('Messages et mediation') }}</td>
                                        <td class="p-3">{{ __('5 ans apres la derniere activite') }}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </section>

                    {{-- 7. RIGHTS --}}
                    <section id="droits" class="scroll-mt-24">
                        <h2 class="text-2xl font-bold mb-4" style="color: #1B2A4A;">7. {{ __('Vos droits') }}</h2>
                        <p class="text-base leading-relaxed mb-4" style="color: #3A4B5E;">
                            {{ __('Conformement a la loi algerienne n° 18-07 du 10 juin 2018 relative a la protection des personnes physiques dans le traitement des donnees a caractere personnel, vous disposez des droits suivants :') }}
                        </p>
                        <div class="grid sm:grid-cols-2 gap-3">
                            @foreach ([
                                [__('Droit d\'acces'), __('Obtenir une copie de toutes vos donnees personnelles.')],
                                [__('Droit de rectification'), __('Corriger toute information inexacte ou incomplete.')],
                                [__('Droit a l\'effacement'), __('Demander la suppression de vos donnees (avec certaines limitations legales).')],
                                [__('Droit d\'opposition'), __('Vous opposer au traitement de vos donnees dans certaines circonstances.')],
                                [__('Droit a la portabilite'), __('Recevoir vos donnees dans un format structure et couramment utilise.')],
                                [__('Droit de retrait'), __('Retirer votre consentement a tout moment.')],
                            ] as $right)
                                <div class="rounded-xl p-4" style="background: #F0F4F8;">
                                    <h3 class="font-semibold mb-1" style="color: #1B2A4A;">{{ $right[0] }}</h3>
                                    <p class="text-sm" style="color: #3A4B5E;">{{ $right[1] }}</p>
                                </div>
                            @endforeach
                        </div>
                        <p class="text-base mt-4" style="color: #3A4B5E;">
                            {{ __('Pour exercer l\'un de ces droits, ecrivez-nous a') }}
                            <a href="mailto:privacy@albabor.dz" class="font-semibold" style="color: #17A2B8;">privacy@albabor.dz</a>
                            {{ __('avec une copie de votre piece d\'identite. Nous repondrons dans un delai de 30 jours.') }}
                        </p>
                    </section>

                    {{-- 8. COOKIES --}}
                    <section id="cookies" class="scroll-mt-24">
                        <h2 class="text-2xl font-bold mb-4" style="color: #1B2A4A;">8. {{ __('Cookies') }}</h2>
                        <div class="space-y-3 text-base leading-relaxed" style="color: #3A4B5E;">
                            <p>
                                {{ __('Nous utilisons uniquement les cookies strictement necessaires au fonctionnement du Service :') }}
                            </p>
                            <ul class="list-disc ml-5 space-y-1">
                                <li><strong>{{ __('Cookie de session') }}</strong> {{ __('(Laravel) : maintient votre session de connexion.') }}</li>
                                <li><strong>{{ __('Cookie CSRF') }}</strong> {{ __(': protege contre les attaques de falsification de requete.') }}</li>
                                <li><strong>{{ __('Cookie de langue') }}</strong> {{ __(': memorise votre choix de langue.') }}</li>
                            </ul>
                            <p>
                                {{ __('Nous n\'utilisons aucun cookie de tracking, de publicite ou d\'analyse tiers. L\'application mobile n\'utilise pas de cookies.') }}
                            </p>
                        </div>
                    </section>

                    {{-- 9. CHILDREN --}}
                    <section id="enfants" class="scroll-mt-24">
                        <h2 class="text-2xl font-bold mb-4" style="color: #1B2A4A;">9. {{ __('Mineurs') }}</h2>
                        <p class="text-base leading-relaxed" style="color: #3A4B5E;">
                            {{ __('Le Service n\'est pas destine aux personnes de moins de 18 ans. Nous ne collectons pas sciemment de donnees personnelles de mineurs. Si vous pensez qu\'un mineur nous a transmis des donnees personnelles, contactez-nous pour que nous procedions a leur suppression.') }}
                        </p>
                    </section>

                    {{-- 10. CHANGES --}}
                    <section id="modifications" class="scroll-mt-24">
                        <h2 class="text-2xl font-bold mb-4" style="color: #1B2A4A;">10. {{ __('Modifications de cette politique') }}</h2>
                        <p class="text-base leading-relaxed" style="color: #3A4B5E;">
                            {{ __('Nous pouvons mettre a jour cette politique de temps en temps pour refleter des changements de nos pratiques ou pour des raisons legales. La date de derniere mise a jour est indiquee en haut de cette page. Pour les modifications importantes, nous vous enverrons une notification par email.') }}
                        </p>
                    </section>

                    {{-- 11. CONTACT --}}
                    <section id="contact" class="scroll-mt-24">
                        <h2 class="text-2xl font-bold mb-4" style="color: #1B2A4A;">11. {{ __('Contact') }}</h2>
                        <div class="rounded-2xl p-5 sm:p-6" style="background: linear-gradient(135deg, #1B4F72 0%, #17A2B8 100%); color: white;">
                            <p class="mb-3">{{ __('Pour toute question ou preoccupation concernant cette politique de confidentialite ou le traitement de vos donnees personnelles :') }}</p>
                            <ul class="space-y-2 text-sm">
                                <li class="flex items-center gap-2">
                                    <svg class="w-4 h-4 opacity-80" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                                    <a href="mailto:privacy@albabor.dz" class="hover:underline">privacy@albabor.dz</a>
                                </li>
                                <li class="flex items-center gap-2">
                                    <svg class="w-4 h-4 opacity-80" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                                    <a href="mailto:contact@albabor.dz" class="hover:underline">contact@albabor.dz</a>
                                </li>
                                <li class="flex items-center gap-2">
                                    <svg class="w-4 h-4 opacity-80" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                    {{ __('Alger, Algerie') }}
                                </li>
                            </ul>
                        </div>
                    </section>

                </article>
            </div>
        </section>
    </div>
</x-app-layout>
