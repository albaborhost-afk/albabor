{{--
    Paiement en ligne (Stripe Checkout).

    Le bloc ne s'affiche que si Stripe est réellement configuré ET si le montant
    atteint le minimum accepté par Stripe. Sans clés, il disparaît simplement —
    pas de bouton mort qui renvoie une erreur après le clic.

    Les moyens réellement proposés sur la page Stripe (Visa, Mastercard, Amex,
    Apple Pay, Google Pay, Link…) dépendent du tableau de bord Stripe et de
    l'appareil du visiteur : le serveur n'impose plus « carte uniquement ».

    Alpine sert uniquement au confort (voile de redirection). S'il ne charge
    pas, le formulaire part quand même : rien d'essentiel n'en dépend.
--}}
@props([
    'action',
    'amountDzd' => 0,
    'amountEur' => 0,
    'label' => 'Montant à payer',
    'note' => null,
])

@php
    $stripeReady = filled(config('services.stripe.secret')) && filled(config('services.stripe.key'));

    // Stripe refuse toute charge sous 0,50 € : proposer le bouton reviendrait à
    // promettre un paiement qui échouerait à la création de la session.
    $stripePayable = $stripeReady && $amountEur >= 0.50;

    $eurDisplay = number_format($amountEur, 2, ',', ' ');
    $dzdDisplay = number_format($amountDzd, 0, ',', ' ');

    $networks = [
        ['src' => '/images/payments/visa.svg',       'alt' => 'Visa'],
        ['src' => '/images/payments/mastercard.svg', 'alt' => 'Mastercard'],
        ['src' => '/images/payments/amex.svg',       'alt' => 'American Express'],
        ['src' => '/images/payments/apple-pay.svg',  'alt' => 'Apple Pay'],
        ['src' => '/images/payments/google-pay.svg', 'alt' => 'Google Pay'],
        ['src' => '/images/payments/link.svg',       'alt' => 'Link'],
    ];
@endphp

@if($stripePayable)
<section x-data="{ redirecting: false }" class="mb-5">

    <div class="mb-3 flex items-center gap-2">
        <svg class="w-4 h-4" style="color: #F39C12;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
        </svg>
        <h2 class="text-base font-bold" style="color: #1B2A4A;">Paiement en ligne</h2>
    </div>

    <div class="rounded-3xl overflow-hidden" style="box-shadow: 0 12px 35px rgba(99,91,255,0.18); border: 2px solid rgba(99,91,255,0.25);">

        {{-- En-tête --}}
        <div class="px-5 py-4 flex items-center gap-3" style="background: linear-gradient(135deg, #635BFF 0%, #4B44D9 100%);">
            <div class="w-10 h-10 rounded-xl flex items-center justify-center flex-shrink-0" style="background: rgba(255,255,255,0.2);">
                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                </svg>
            </div>
            <div class="flex-1 min-w-0">
                <div class="flex flex-wrap items-center gap-2">
                    <p class="text-white font-bold text-sm">Carte, Apple&nbsp;Pay, Google&nbsp;Pay</p>
                    <span class="inline-flex items-center gap-1 text-[10px] font-bold uppercase tracking-wide px-2 py-0.5 rounded-full" style="background: rgba(255,255,255,0.25); color: white;">
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                        Instantané
                    </span>
                </div>
                <p class="text-white/70 text-xs mt-0.5">Confirmation immédiate — aucun justificatif à envoyer</p>
            </div>
            <svg viewBox="0 0 60 25" class="h-5 w-auto flex-shrink-0 hidden sm:block" fill="white" xmlns="http://www.w3.org/2000/svg" role="img" aria-label="Stripe">
                <path d="M59.64 14.28h-8.06c.19 1.93 1.6 2.55 3.2 2.55 1.64 0 2.96-.37 4.05-.95v3.32a8.33 8.33 0 0 1-4.56 1.1c-4.01 0-6.83-2.5-6.83-7.48 0-4.19 2.39-7.52 6.3-7.52 3.92 0 5.96 3.28 5.96 7.5 0 .4-.04 1.26-.06 1.48zm-5.92-5.62c-1.03 0-2.17.73-2.17 2.58h4.25c0-1.85-1.07-2.58-2.08-2.58zM40.95 20.3c-1.44 0-2.32-.6-2.9-1.04l-.02 4.63-4.12.87V5.57h3.76l.08 1.02a4.7 4.7 0 0 1 3.23-1.29c2.9 0 5.62 2.6 5.62 7.4 0 5.23-2.7 7.6-5.65 7.6zM40 8.95c-.95 0-1.54.34-1.97.81l.02 6.12c.4.44.98.78 1.95.78 1.52 0 2.54-1.65 2.54-3.87 0-2.15-1.04-3.84-2.54-3.84zM28.24 5.57h4.13v14.44h-4.13V5.57zm0-4.7L32.37 0v3.36l-4.13.88V.88zm-4.32 9.35v9.79H19.8V5.57h3.7l.12 1.22c1-1.77 2.98-1.63 3.54-1.43v3.79c-.31-.09-1.28-.61-2.24.28zm-8.55 4.72c0 2.43 2.6 1.68 3.12 1.46v3.36c-.55.3-1.54.54-2.89.54a4.15 4.15 0 0 1-4.27-4.24l.01-13.17 4.02-.86v3.54h3.14V9.1h-3.13v5.85zm-4.91.7c0 2.97-2.31 4.66-5.73 4.66a11.2 11.2 0 0 1-4.46-.93v-3.93c1.38.75 3.1 1.31 4.46 1.31.92 0 1.53-.24 1.53-1C6.26 13.77 0 14.51 0 9.95 0 7.04 2.28 5.3 5.62 5.3c1.5 0 3 .25 4.43.08v3.77c-1.3-.3-2.67-.4-4.43-.4-.78 0-1.31.27-1.31.9 0 1.98 6.41.99 6.41 6.07z"/>
            </svg>
        </div>

        {{-- Corps --}}
        <div class="px-5 py-5" style="background: linear-gradient(160deg, #F8F7FF, #F0EFFF);">

            {{-- Moyens acceptés --}}
            <div class="mb-4">
                <p class="text-[11px] font-semibold uppercase tracking-wider mb-2" style="color: #9BA8B7;">Moyens acceptés</p>
                <div class="flex flex-wrap items-center gap-2">
                    @foreach($networks as $network)
                        <img src="{{ $network['src'] }}" alt="{{ $network['alt'] }}" width="48" height="32"
                             class="h-8 w-auto object-contain" style="filter: drop-shadow(0 1px 2px rgba(0,0,0,0.06));">
                    @endforeach
                </div>
                <p class="text-[11px] mt-2" style="color: #9BA8B7;">
                    Apple&nbsp;Pay et Google&nbsp;Pay apparaissent selon votre appareil et votre navigateur.
                </p>
            </div>

            {{-- Montant --}}
            <div class="flex items-center justify-between gap-4 p-4 rounded-2xl mb-4" style="background: white; border: 1.5px solid rgba(99,91,255,0.15);">
                <div class="min-w-0">
                    <p class="text-xs font-semibold uppercase tracking-wider" style="color: #9BA8B7;">{{ $label }}</p>
                    <p class="text-2xl font-black mt-0.5" style="color: #1B2A4A;">
                        {{ $eurDisplay }} <span style="color: #635BFF;">€</span>
                    </p>
                    <p class="text-xs mt-0.5" style="color: #9BA8B7;">≈ {{ $dzdDisplay }} DA au taux indicatif</p>
                </div>
                <div class="flex-shrink-0 flex items-center gap-1.5 text-xs font-semibold px-3 py-1.5 rounded-full" style="background: rgba(39,174,96,0.1); color: #27AE60;">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                    Sécurisé
                </div>
            </div>

            <form action="{{ $action }}" method="POST" x-on:submit="redirecting = true">
                @csrf
                <button type="submit"
                        x-bind:disabled="redirecting"
                        class="w-full py-4 rounded-2xl font-bold text-white text-base transition-all duration-300 hover:-translate-y-0.5 hover:shadow-xl flex items-center justify-center gap-3 disabled:opacity-60 disabled:cursor-not-allowed"
                        style="background: linear-gradient(135deg, #635BFF, #4B44D9); box-shadow: 0 8px 25px rgba(99,91,255,0.4);">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" x-show="!redirecting">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                    </svg>
                    <svg class="w-5 h-5 animate-spin" fill="none" viewBox="0 0 24 24" x-show="redirecting" style="display: none;">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                    </svg>
                    <span x-show="!redirecting">Payer {{ $eurDisplay }} € en ligne</span>
                    <span x-show="redirecting" style="display: none;">Redirection…</span>
                </button>
            </form>

            <p class="text-center text-xs mt-3" style="color: #9BA8B7;">
                Vous serez redirigé vers la page de paiement sécurisée de Stripe.
                Vos données de carte ne transitent jamais par AlBabor.
            </p>

            @if($note)
                <p class="text-center text-xs mt-1.5" style="color: #9BA8B7;">{{ $note }}</p>
            @endif
        </div>
    </div>

    {{-- Voile de redirection : rassure pendant la création de la session Stripe --}}
    <template x-teleport="body">
        <div x-show="redirecting" x-transition.opacity style="display: none;"
             class="fixed inset-0 z-[100] flex flex-col items-center justify-center gap-5 px-6 text-center"
             role="status" aria-live="polite">
            <div class="absolute inset-0" style="background: rgba(27,42,74,0.85); backdrop-filter: blur(4px);"></div>
            <div class="relative h-12 w-12 animate-spin rounded-full border-4" style="border-color: rgba(255,255,255,0.25); border-top-color: #F39C12;"></div>
            <div class="relative space-y-1.5">
                <p class="text-lg font-bold text-white">Redirection vers le paiement sécurisé…</p>
                <p class="text-sm text-white/70">Veuillez patienter, ne fermez pas cette fenêtre.</p>
            </div>
            <div class="relative flex items-center gap-2 text-xs font-semibold" style="color: #F39C12;">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                Connexion chiffrée
            </div>
        </div>
    </template>

    {{--
        Le séparateur appartient au bloc en ligne : affiché seul (Stripe non
        configuré, montant trop faible), il annonçait une alternative à rien.
    --}}
    <div class="flex items-center gap-4 mt-6">
        <div class="flex-1 h-px" style="background: #E0E6ED;"></div>
        <span class="text-xs font-semibold px-3 py-1.5 rounded-full" style="background: #E8EEF4; color: #9BA8B7;">ou payer par virement</span>
        <div class="flex-1 h-px" style="background: #E0E6ED;"></div>
    </div>
</section>
@endif
