<x-app-layout>
    @php $title = 'Albabor Pro — Devenir vendeur de pièces'; @endphp

    <div class="min-h-screen" style="background: linear-gradient(135deg, #F0F4F8 0%, #E8F2F7 100%);">

        {{-- Hero --}}
        <section class="relative overflow-hidden">
            <div class="absolute inset-0" style="background: linear-gradient(135deg, #1B2A4A 0%, #2C5F7C 55%, #17A2B8 100%);"></div>
            <div class="absolute inset-0 opacity-10" style="background-image: radial-gradient(circle at 20% 30%, #fff 0, transparent 40%), radial-gradient(circle at 80% 70%, #fff 0, transparent 35%);"></div>

            <div class="relative max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-20 sm:py-28 text-center">
                <span class="inline-flex items-center gap-2 px-4 py-1.5 rounded-full text-xs font-bold tracking-wide text-white mb-6" style="background: rgba(255,255,255,0.15); backdrop-filter: blur(4px);">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" /></svg>
                    ALBABOR PRO
                </span>
                <h1 class="text-4xl sm:text-5xl lg:text-6xl font-black text-white mb-5 leading-tight">
                    Devenez vendeur de pièces<br class="hidden sm:block"> sur Albabor Pro
                </h1>
                <p class="text-lg sm:text-xl max-w-2xl mx-auto mb-10" style="color: #D6E4EC;">
                    Vendez vos moteurs et pièces détachées, ouvrez votre boutique en ligne et pilotez votre activité depuis un tableau de bord professionnel.
                </p>

                <div class="flex flex-col sm:flex-row items-center justify-center gap-4">
                    @if($hasShop)
                        <a href="{{ url('/vendeur') }}" class="inline-flex items-center gap-2 px-8 py-4 rounded-xl font-bold text-base transition-all" style="background: #F39C12; color: #1B2A4A; box-shadow: 0 8px 24px rgba(243,156,18,0.4);">
                            Accéder à mon espace vendeur
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" /></svg>
                        </a>
                    @else
                        @guest
                            <a href="{{ route('register') }}" class="inline-flex items-center gap-2 px-8 py-4 rounded-xl font-bold text-base transition-all" style="background: #F39C12; color: #1B2A4A; box-shadow: 0 8px 24px rgba(243,156,18,0.4);">
                                Créer un compte et commencer
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" /></svg>
                            </a>
                            <a href="{{ route('login') }}" class="inline-flex items-center gap-2 px-8 py-4 rounded-xl font-semibold text-base text-white transition-all" style="background: rgba(255,255,255,0.12); border: 1px solid rgba(255,255,255,0.3);">
                                J'ai déjà un compte
                            </a>
                        @else
                            <a href="{{ route('vendor.onboarding.create') }}" class="inline-flex items-center gap-2 px-8 py-4 rounded-xl font-bold text-base transition-all" style="background: #F39C12; color: #1B2A4A; box-shadow: 0 8px 24px rgba(243,156,18,0.4);">
                                Créer ma boutique
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" /></svg>
                            </a>
                        @endguest
                    @endif
                </div>
            </div>
        </section>

        {{-- Avantages --}}
        <section class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-16 sm:py-20">
            <div class="text-center mb-12">
                <h2 class="text-3xl sm:text-4xl font-bold mb-3" style="color: #1B2A4A;">Tout ce qu'il vous faut pour vendre</h2>
                <p class="text-base max-w-2xl mx-auto" style="color: #6B7B8D;">Albabor Pro réunit les outils des professionnels de la pièce nautique en une seule plateforme.</p>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                @php
                    $features = [
                        ['#2C5F7C', 'M13 10V3L4 14h7v7l9-11h-7z', 'Vendre moteurs & pièces', 'Publiez des annonces de moteurs et de pièces détachées sans limite, dans une catégorie dédiée.'],
                        ['#17A2B8', 'M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4', 'Boutique dédiée & vitrine publique', 'Une page boutique à votre nom, partageable, où les acheteurs retrouvent toutes vos annonces.'],
                        ['#9B59B6', 'M9 17v-2a4 4 0 014-4h4m0 0l-3-3m3 3l-3 3M5 21h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v14a2 2 0 002 2z', 'Tableau de bord professionnel', 'Gérez vos annonces, votre profil et vos contacts depuis un espace vendeur clair et complet.'],
                        ['#E67E22', 'M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z', 'Statistiques de vues & favoris', 'Suivez les vues et les mises en favori de chaque annonce pour ajuster vos prix et vos offres.'],
                        ['#27AE60', 'M4 6h16M4 10h16M4 14h16M4 18h16', 'Gestion centralisée des annonces', 'Activez, mettez en pause ou modifiez toutes vos annonces depuis un seul endroit.'],
                        ['#E74C3C', 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z', 'Profil vendeur de confiance', 'Logo, description et coordonnées : rassurez les acheteurs et démarquez-vous des particuliers.'],
                    ];
                @endphp

                @foreach($features as $f)
                    <div class="bg-white rounded-2xl p-6 transition-all hover:-translate-y-1" style="box-shadow: 0 8px 24px rgba(0,0,0,0.06);">
                        <div class="w-12 h-12 rounded-xl flex items-center justify-center mb-4" style="background: {{ $f[0] }};">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $f[1] }}" /></svg>
                        </div>
                        <h3 class="font-bold text-lg mb-2" style="color: #1B2A4A;">{{ $f[2] }}</h3>
                        <p class="text-sm leading-relaxed" style="color: #6B7B8D;">{{ $f[3] }}</p>
                    </div>
                @endforeach
            </div>
        </section>

        {{-- Étapes --}}
        <section class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 pb-16 sm:pb-20">
            <div class="bg-white rounded-3xl p-8 sm:p-12" style="box-shadow: 0 12px 32px rgba(0,0,0,0.07);">
                <h2 class="text-2xl sm:text-3xl font-bold mb-10 text-center" style="color: #1B2A4A;">Comment ça marche</h2>
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-8">
                    @php
                        $steps = [
                            ['1', 'Créez votre compte', 'Inscrivez-vous gratuitement sur Albabor en quelques secondes.'],
                            ['2', 'Ouvrez votre boutique', 'Renseignez le nom, le logo et les coordonnées de votre boutique.'],
                            ['3', 'Publiez & vendez', 'Mettez en ligne vos moteurs et pièces, suivez vos statistiques.'],
                        ];
                    @endphp
                    @foreach($steps as $s)
                        <div class="text-center">
                            <div class="w-14 h-14 rounded-full flex items-center justify-center mx-auto mb-4 text-xl font-black text-white" style="background: linear-gradient(135deg, #17A2B8, #2471A3);">{{ $s[0] }}</div>
                            <h3 class="font-bold text-base mb-2" style="color: #1B2A4A;">{{ $s[1] }}</h3>
                            <p class="text-sm" style="color: #6B7B8D;">{{ $s[2] }}</p>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>

        {{-- CTA final --}}
        <section class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 pb-20">
            <div class="rounded-3xl p-10 sm:p-14 text-center" style="background: linear-gradient(135deg, #1B2A4A 0%, #2C5F7C 100%);">
                <h2 class="text-2xl sm:text-3xl font-bold text-white mb-3">Prêt à développer votre activité ?</h2>
                <p class="text-base mb-8 max-w-xl mx-auto" style="color: #D6E4EC;">Rejoignez les vendeurs professionnels d'Albabor Pro et donnez une vraie vitrine à vos pièces.</p>

                @if($hasShop)
                    <a href="{{ url('/vendeur') }}" class="inline-flex items-center gap-2 px-8 py-4 rounded-xl font-bold text-base transition-all" style="background: #F39C12; color: #1B2A4A; box-shadow: 0 8px 24px rgba(243,156,18,0.4);">
                        Accéder à mon espace vendeur
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" /></svg>
                    </a>
                @else
                    @guest
                        <a href="{{ route('register') }}" class="inline-flex items-center gap-2 px-8 py-4 rounded-xl font-bold text-base transition-all" style="background: #F39C12; color: #1B2A4A; box-shadow: 0 8px 24px rgba(243,156,18,0.4);">
                            Créer un compte et commencer
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" /></svg>
                        </a>
                    @else
                        <a href="{{ route('vendor.onboarding.create') }}" class="inline-flex items-center gap-2 px-8 py-4 rounded-xl font-bold text-base transition-all" style="background: #F39C12; color: #1B2A4A; box-shadow: 0 8px 24px rgba(243,156,18,0.4);">
                            Créer ma boutique
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" /></svg>
                        </a>
                    @endguest
                @endif

                <a href="{{ route('pages.sale-authorization') }}" class="inline-flex items-center justify-center gap-2 text-sm font-medium mt-6 transition-colors hover:text-white" style="color: #D6E4EC;">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    Autorisation de vente et publication (à imprimer)
                </a>
            </div>
        </section>

    </div>
</x-app-layout>
