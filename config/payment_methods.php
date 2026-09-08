<?php

// Payment destinations already used by the website; shared with both mobile apps.
return [
    'holder' => 'DJAMAA BILEL',
    'methods' => [
        'baridimob' => ['logo' => '/images/baridimob.png', 'name' => 'BaridiMob', 'detail' => 'Numéro : 00799999002543569223', 'mono' => true],
        'bank_transfer' => ['logo' => '/images/bea.png', 'name' => 'BEA – Banque Extérieure d’Algérie', 'detail' => 'RIB : 00200090090220206690', 'mono' => true],
        'paypal' => ['logo' => '/images/payments/paypal-tile.svg', 'name' => 'PayPal', 'detail' => 'albabordz@gmail.com', 'mono' => false],
        'card' => ['logo' => '/images/payments/card.svg', 'name' => 'Carte bancaire — Mastercard / Visa', 'detail' => 'Paiement international, puis justificatif', 'mono' => false],
    ],
];
