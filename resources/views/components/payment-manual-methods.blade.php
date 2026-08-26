{{--
    Méthodes de paiement manuelles (virement puis justificatif).

    À placer DANS un formulaire dont un ancêtre Alpine expose `method` :
    `<form x-data="{ method: '{{ old('method', '') }}' }">`. Le composant lit et
    écrit cette variable, ce qui permet au parent d'afficher le téléversement du
    justificatif seulement une fois un moyen choisi.

    Les coordonnées vivaient en double dans la page de publication et dans celle
    des abonnements ; elles n'existent plus qu'ici.
--}}
@props([
    'only' => ['baridimob', 'bank_transfer', 'paypal'],
    'required' => true,
    'title' => 'Méthode de paiement',
])

@php
    $holder = 'DJAMAA BILEL';

    $catalogue = [
        'baridimob' => [
            'logo'    => '/images/baridimob.png',
            'name'    => 'BaridiMob',
            'detail'  => 'Numéro : 00799999002543569223',
            'mono'    => true,
        ],
        'bank_transfer' => [
            'logo'    => '/images/bea.png',
            'name'    => "BEA – Banque Extérieure d'Algérie",
            'detail'  => 'RIB : 00200090090220206690',
            'mono'    => true,
        ],
        'paypal' => [
            'logo'    => '/images/payments/paypal-tile.svg',
            'name'    => 'PayPal',
            'detail'  => 'albabordz@gmail.com',
            'mono'    => false,
        ],
        'card' => [
            'logo'    => '/images/payments/card.svg',
            'name'    => 'Carte bancaire — Mastercard / Visa',
            'detail'  => 'Paiement international, puis justificatif',
            'mono'    => false,
        ],
    ];

    $methods = array_values(array_filter(
        array_map(fn ($key) => isset($catalogue[$key]) ? ['key' => $key] + $catalogue[$key] : null, $only)
    ));
@endphp

<div class="bg-white rounded-2xl p-5 sm:p-6" style="box-shadow: 0 10px 25px rgba(0,0,0,0.06);">
    <h2 class="text-base font-bold mb-1.5 flex items-center gap-2" style="color: #1B2A4A;">
        <svg class="w-5 h-5" style="color: #17A2B8;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
        </svg>
        {{ $title }}
    </h2>
    <p class="text-sm mb-4" style="color: #6B7B8D;">
        Effectuez le virement, puis téléversez le justificatif. Notre équipe le vérifie sous 24–48&nbsp;h.
    </p>

    <div class="space-y-3">
        @foreach($methods as $index => $m)
            <label class="flex items-center gap-3 p-3 sm:p-4 rounded-2xl cursor-pointer transition-all"
                   x-bind:style="method === '{{ $m['key'] }}'
                        ? 'border: 2px solid #17A2B8; background: rgba(23,162,184,0.05); box-shadow: 0 4px 14px rgba(23,162,184,0.14);'
                        : 'border: 1px solid #E0E6ED; background: #FFFFFF;'"
                   style="border: 1px solid #E0E6ED;">

                <input type="radio" name="method" value="{{ $m['key'] }}" x-model="method"
                       @if($required && $index === 0) required @endif
                       class="flex-shrink-0" style="accent-color: #17A2B8;">

                {{-- Tuile logo, comme sur Yachtei : le moyen se reconnaît d'un coup d'œil --}}
                <span class="flex h-14 w-20 sm:h-16 sm:w-24 flex-shrink-0 items-center justify-center overflow-hidden rounded-xl p-2"
                      style="background: #FFFFFF; border: 1px solid #E8EEF4; box-shadow: inset 0 0 0 1px rgba(0,0,0,0.02);">
                    <img src="{{ $m['logo'] }}" alt="{{ $m['name'] }}" class="max-h-full max-w-full object-contain">
                </span>

                <span class="min-w-0 flex-1">
                    <span class="block font-semibold text-sm sm:text-base" style="color: #1B2A4A;">{{ $m['name'] }}</span>
                    <span class="block text-xs sm:text-sm mt-0.5 {{ $m['mono'] ? 'font-mono break-all' : 'break-all' }}" style="color: #6B7B8D;">{{ $m['detail'] }}</span>
                    <span class="block text-xs mt-0.5" style="color: #9BA8B7;">Titulaire : {{ $holder }}</span>
                </span>

                <svg class="w-5 h-5 flex-shrink-0 hidden sm:block" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                     x-bind:style="method === '{{ $m['key'] }}' ? 'color: #17A2B8;' : 'color: #C5D0DB;'">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                </svg>
            </label>
        @endforeach
    </div>
</div>
