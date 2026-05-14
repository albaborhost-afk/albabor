<x-app-layout>
    @php $title = 'Boutique créée — Albabor Pro'; @endphp

    <div class="min-h-screen flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8" style="background: linear-gradient(135deg, #F0F4F8 0%, #E8F2F7 100%);">
        <div class="max-w-md w-full bg-white rounded-2xl p-8 sm:p-10 text-center" style="box-shadow: 0 12px 32px rgba(0,0,0,0.08);">
            <div class="w-20 h-20 rounded-full flex items-center justify-center mx-auto mb-6" style="background: linear-gradient(135deg, #27AE60, #17A2B8);">
                <svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" /></svg>
            </div>
            <h1 class="text-2xl sm:text-3xl font-bold mb-3" style="color: #1B2A4A;">Votre boutique est en ligne !</h1>
            <p class="text-base mb-8" style="color: #6B7B8D;">
                Bienvenue dans Albabor Pro. Vous pouvez maintenant publier vos moteurs et pièces détachées depuis votre espace vendeur.
            </p>
            <a href="{{ url('/vendeur') }}" class="inline-flex items-center justify-center gap-2 w-full py-3 rounded-xl font-bold text-white transition-all"
               style="background: linear-gradient(135deg, #17A2B8, #2471A3); box-shadow: 0 4px 12px rgba(36,113,163,0.3);">
                Accéder à mon espace vendeur
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" /></svg>
            </a>
        </div>
    </div>
</x-app-layout>
