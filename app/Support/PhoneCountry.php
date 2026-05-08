<?php

namespace App\Support;

/**
 * Lookup + parser for international phone country codes.
 *
 * Codes are stored in app code (not a DB table) because they don't
 * change often and the list is small. Source-of-truth duplicated with
 * the Alpine.js list in resources/views/auth/register.blade.php.
 */
class PhoneCountry
{
    /**
     * Country code (with leading +) → ['name' => …, 'iso' => …, 'flag' => …].
     *
     * Where multiple countries share a code (NANPA +1, Russia/Kazakhstan +7),
     * the first / largest country wins for reverse lookup.
     */
    public const COUNTRIES = [
        '+213' => ['name' => 'Algerie',          'iso' => 'DZ', 'flag' => '🇩🇿'],
        '+212' => ['name' => 'Maroc',            'iso' => 'MA', 'flag' => '🇲🇦'],
        '+216' => ['name' => 'Tunisie',          'iso' => 'TN', 'flag' => '🇹🇳'],
        '+20'  => ['name' => 'Egypte',           'iso' => 'EG', 'flag' => '🇪🇬'],
        '+222' => ['name' => 'Mauritanie',       'iso' => 'MR', 'flag' => '🇲🇷'],
        '+221' => ['name' => 'Senegal',          'iso' => 'SN', 'flag' => '🇸🇳'],
        '+223' => ['name' => 'Mali',             'iso' => 'ML', 'flag' => '🇲🇱'],
        '+227' => ['name' => 'Niger',            'iso' => 'NE', 'flag' => '🇳🇪'],
        '+235' => ['name' => 'Tchad',            'iso' => 'TD', 'flag' => '🇹🇩'],
        '+249' => ['name' => 'Soudan',           'iso' => 'SD', 'flag' => '🇸🇩'],
        '+251' => ['name' => 'Ethiopie',         'iso' => 'ET', 'flag' => '🇪🇹'],
        '+252' => ['name' => 'Somalie',          'iso' => 'SO', 'flag' => '🇸🇴'],
        '+254' => ['name' => 'Kenya',            'iso' => 'KE', 'flag' => '🇰🇪'],
        '+255' => ['name' => 'Tanzania',         'iso' => 'TZ', 'flag' => '🇹🇿'],
        '+234' => ['name' => 'Nigeria',          'iso' => 'NG', 'flag' => '🇳🇬'],
        '+233' => ['name' => 'Ghana',            'iso' => 'GH', 'flag' => '🇬🇭'],
        '+237' => ['name' => 'Cameroun',         'iso' => 'CM', 'flag' => '🇨🇲'],
        '+225' => ['name' => "Cote d'Ivoire",    'iso' => 'CI', 'flag' => '🇨🇮'],
        '+226' => ['name' => 'Burkina Faso',     'iso' => 'BF', 'flag' => '🇧🇫'],
        '+224' => ['name' => 'Guinee',           'iso' => 'GN', 'flag' => '🇬🇳'],
        '+229' => ['name' => 'Benin',            'iso' => 'BJ', 'flag' => '🇧🇯'],
        '+228' => ['name' => 'Togo',             'iso' => 'TG', 'flag' => '🇹🇬'],
        '+250' => ['name' => 'Rwanda',           'iso' => 'RW', 'flag' => '🇷🇼'],
        '+256' => ['name' => 'Uganda',           'iso' => 'UG', 'flag' => '🇺🇬'],
        '+242' => ['name' => 'Congo',            'iso' => 'CG', 'flag' => '🇨🇬'],
        '+244' => ['name' => 'Angola',           'iso' => 'AO', 'flag' => '🇦🇴'],
        '+258' => ['name' => 'Mozambique',       'iso' => 'MZ', 'flag' => '🇲🇿'],
        '+261' => ['name' => 'Madagascar',       'iso' => 'MG', 'flag' => '🇲🇬'],
        '+27'  => ['name' => 'Afrique du Sud',   'iso' => 'ZA', 'flag' => '🇿🇦'],
        '+263' => ['name' => 'Zimbabwe',         'iso' => 'ZW', 'flag' => '🇿🇼'],
        '+260' => ['name' => 'Zambie',           'iso' => 'ZM', 'flag' => '🇿🇲'],
        '+267' => ['name' => 'Botswana',         'iso' => 'BW', 'flag' => '🇧🇼'],
        '+230' => ['name' => 'Maurice',          'iso' => 'MU', 'flag' => '🇲🇺'],
        '+241' => ['name' => 'Gabon',            'iso' => 'GA', 'flag' => '🇬🇦'],
        '+33'  => ['name' => 'France',           'iso' => 'FR', 'flag' => '🇫🇷'],
        '+49'  => ['name' => 'Allemagne',        'iso' => 'DE', 'flag' => '🇩🇪'],
        '+34'  => ['name' => 'Espagne',          'iso' => 'ES', 'flag' => '🇪🇸'],
        '+39'  => ['name' => 'Italie',           'iso' => 'IT', 'flag' => '🇮🇹'],
        '+351' => ['name' => 'Portugal',         'iso' => 'PT', 'flag' => '🇵🇹'],
        '+44'  => ['name' => 'Royaume-Uni',      'iso' => 'GB', 'flag' => '🇬🇧'],
        '+32'  => ['name' => 'Belgique',         'iso' => 'BE', 'flag' => '🇧🇪'],
        '+31'  => ['name' => 'Pays-Bas',         'iso' => 'NL', 'flag' => '🇳🇱'],
        '+41'  => ['name' => 'Suisse',           'iso' => 'CH', 'flag' => '🇨🇭'],
        '+43'  => ['name' => 'Autriche',         'iso' => 'AT', 'flag' => '🇦🇹'],
        '+46'  => ['name' => 'Suede',            'iso' => 'SE', 'flag' => '🇸🇪'],
        '+47'  => ['name' => 'Norvege',          'iso' => 'NO', 'flag' => '🇳🇴'],
        '+45'  => ['name' => 'Danemark',         'iso' => 'DK', 'flag' => '🇩🇰'],
        '+358' => ['name' => 'Finlande',         'iso' => 'FI', 'flag' => '🇫🇮'],
        '+353' => ['name' => 'Irlande',          'iso' => 'IE', 'flag' => '🇮🇪'],
        '+30'  => ['name' => 'Grece',            'iso' => 'GR', 'flag' => '🇬🇷'],
        '+90'  => ['name' => 'Turquie',          'iso' => 'TR', 'flag' => '🇹🇷'],
        '+48'  => ['name' => 'Pologne',          'iso' => 'PL', 'flag' => '🇵🇱'],
        '+420' => ['name' => 'Rep. tcheque',     'iso' => 'CZ', 'flag' => '🇨🇿'],
        '+36'  => ['name' => 'Hongrie',          'iso' => 'HU', 'flag' => '🇭🇺'],
        '+40'  => ['name' => 'Roumanie',         'iso' => 'RO', 'flag' => '🇷🇴'],
        '+359' => ['name' => 'Bulgarie',         'iso' => 'BG', 'flag' => '🇧🇬'],
        '+385' => ['name' => 'Croatie',          'iso' => 'HR', 'flag' => '🇭🇷'],
        '+381' => ['name' => 'Serbie',           'iso' => 'RS', 'flag' => '🇷🇸'],
        '+380' => ['name' => 'Ukraine',          'iso' => 'UA', 'flag' => '🇺🇦'],
        '+7'   => ['name' => 'Russie',           'iso' => 'RU', 'flag' => '🇷🇺'],
        '+421' => ['name' => 'Slovaquie',        'iso' => 'SK', 'flag' => '🇸🇰'],
        '+386' => ['name' => 'Slovenie',         'iso' => 'SI', 'flag' => '🇸🇮'],
        '+352' => ['name' => 'Luxembourg',       'iso' => 'LU', 'flag' => '🇱🇺'],
        '+356' => ['name' => 'Malte',            'iso' => 'MT', 'flag' => '🇲🇹'],
        '+372' => ['name' => 'Estonie',          'iso' => 'EE', 'flag' => '🇪🇪'],
        '+371' => ['name' => 'Lettonie',         'iso' => 'LV', 'flag' => '🇱🇻'],
        '+370' => ['name' => 'Lituanie',         'iso' => 'LT', 'flag' => '🇱🇹'],
        '+389' => ['name' => 'Macedoine',        'iso' => 'MK', 'flag' => '🇲🇰'],
        '+382' => ['name' => 'Montenegro',       'iso' => 'ME', 'flag' => '🇲🇪'],
        '+383' => ['name' => 'Kosovo',           'iso' => 'XK', 'flag' => '🇽🇰'],
        '+376' => ['name' => 'Andorre',          'iso' => 'AD', 'flag' => '🇦🇩'],
        '+377' => ['name' => 'Monaco',           'iso' => 'MC', 'flag' => '🇲🇨'],
        '+354' => ['name' => 'Islande',          'iso' => 'IS', 'flag' => '🇮🇸'],
        '+375' => ['name' => 'Belarus',          'iso' => 'BY', 'flag' => '🇧🇾'],
        '+373' => ['name' => 'Moldova',          'iso' => 'MD', 'flag' => '🇲🇩'],
        '+995' => ['name' => 'Georgie',          'iso' => 'GE', 'flag' => '🇬🇪'],
        '+374' => ['name' => 'Armenie',          'iso' => 'AM', 'flag' => '🇦🇲'],
        '+994' => ['name' => 'Azerbaidjan',      'iso' => 'AZ', 'flag' => '🇦🇿'],
        '+998' => ['name' => 'Ouzbekistan',      'iso' => 'UZ', 'flag' => '🇺🇿'],
        '+993' => ['name' => 'Turkmenistan',     'iso' => 'TM', 'flag' => '🇹🇲'],
        '+996' => ['name' => 'Kirghizistan',     'iso' => 'KG', 'flag' => '🇰🇬'],
        '+992' => ['name' => 'Tadjikistan',      'iso' => 'TJ', 'flag' => '🇹🇯'],
        '+93'  => ['name' => 'Afghanistan',      'iso' => 'AF', 'flag' => '🇦🇫'],
        '+92'  => ['name' => 'Pakistan',         'iso' => 'PK', 'flag' => '🇵🇰'],
        '+91'  => ['name' => 'Inde',             'iso' => 'IN', 'flag' => '🇮🇳'],
        '+880' => ['name' => 'Bangladesh',       'iso' => 'BD', 'flag' => '🇧🇩'],
        '+94'  => ['name' => 'Sri Lanka',        'iso' => 'LK', 'flag' => '🇱🇰'],
        '+977' => ['name' => 'Nepal',            'iso' => 'NP', 'flag' => '🇳🇵'],
        '+95'  => ['name' => 'Myanmar',          'iso' => 'MM', 'flag' => '🇲🇲'],
        '+66'  => ['name' => 'Thailande',        'iso' => 'TH', 'flag' => '🇹🇭'],
        '+84'  => ['name' => 'Vietnam',          'iso' => 'VN', 'flag' => '🇻🇳'],
        '+855' => ['name' => 'Cambodge',         'iso' => 'KH', 'flag' => '🇰🇭'],
        '+856' => ['name' => 'Laos',             'iso' => 'LA', 'flag' => '🇱🇦'],
        '+60'  => ['name' => 'Malaisie',         'iso' => 'MY', 'flag' => '🇲🇾'],
        '+65'  => ['name' => 'Singapour',        'iso' => 'SG', 'flag' => '🇸🇬'],
        '+62'  => ['name' => 'Indonesie',        'iso' => 'ID', 'flag' => '🇮🇩'],
        '+63'  => ['name' => 'Philippines',      'iso' => 'PH', 'flag' => '🇵🇭'],
        '+86'  => ['name' => 'Chine',            'iso' => 'CN', 'flag' => '🇨🇳'],
        '+81'  => ['name' => 'Japon',            'iso' => 'JP', 'flag' => '🇯🇵'],
        '+82'  => ['name' => 'Coree du Sud',     'iso' => 'KR', 'flag' => '🇰🇷'],
        '+976' => ['name' => 'Mongolie',         'iso' => 'MN', 'flag' => '🇲🇳'],
        '+673' => ['name' => 'Brunei',           'iso' => 'BN', 'flag' => '🇧🇳'],
        '+966' => ['name' => 'Arabie Saoudite',  'iso' => 'SA', 'flag' => '🇸🇦'],
        '+971' => ['name' => 'Emirats arabes',   'iso' => 'AE', 'flag' => '🇦🇪'],
        '+974' => ['name' => 'Qatar',            'iso' => 'QA', 'flag' => '🇶🇦'],
        '+965' => ['name' => 'Koweit',           'iso' => 'KW', 'flag' => '🇰🇼'],
        '+973' => ['name' => 'Bahrein',          'iso' => 'BH', 'flag' => '🇧🇭'],
        '+968' => ['name' => 'Oman',             'iso' => 'OM', 'flag' => '🇴🇲'],
        '+962' => ['name' => 'Jordanie',         'iso' => 'JO', 'flag' => '🇯🇴'],
        '+961' => ['name' => 'Liban',            'iso' => 'LB', 'flag' => '🇱🇧'],
        '+964' => ['name' => 'Irak',             'iso' => 'IQ', 'flag' => '🇮🇶'],
        '+98'  => ['name' => 'Iran',             'iso' => 'IR', 'flag' => '🇮🇷'],
        '+967' => ['name' => 'Yemen',            'iso' => 'YE', 'flag' => '🇾🇪'],
        '+970' => ['name' => 'Palestine',        'iso' => 'PS', 'flag' => '🇵🇸'],
        '+1'   => ['name' => 'USA / Canada',     'iso' => 'US', 'flag' => '🇺🇸'],
        '+52'  => ['name' => 'Mexique',          'iso' => 'MX', 'flag' => '🇲🇽'],
        '+55'  => ['name' => 'Bresil',           'iso' => 'BR', 'flag' => '🇧🇷'],
        '+54'  => ['name' => 'Argentine',        'iso' => 'AR', 'flag' => '🇦🇷'],
        '+57'  => ['name' => 'Colombie',         'iso' => 'CO', 'flag' => '🇨🇴'],
        '+56'  => ['name' => 'Chili',            'iso' => 'CL', 'flag' => '🇨🇱'],
        '+51'  => ['name' => 'Peru',             'iso' => 'PE', 'flag' => '🇵🇪'],
        '+58'  => ['name' => 'Venezuela',        'iso' => 'VE', 'flag' => '🇻🇪'],
        '+593' => ['name' => 'Equateur',         'iso' => 'EC', 'flag' => '🇪🇨'],
        '+591' => ['name' => 'Bolivie',          'iso' => 'BO', 'flag' => '🇧🇴'],
        '+598' => ['name' => 'Uruguay',          'iso' => 'UY', 'flag' => '🇺🇾'],
        '+595' => ['name' => 'Paraguay',         'iso' => 'PY', 'flag' => '🇵🇾'],
        '+53'  => ['name' => 'Cuba',             'iso' => 'CU', 'flag' => '🇨🇺'],
        '+509' => ['name' => 'Haiti',            'iso' => 'HT', 'flag' => '🇭🇹'],
        '+61'  => ['name' => 'Australie',        'iso' => 'AU', 'flag' => '🇦🇺'],
        '+64'  => ['name' => 'Nouvelle-Zelande', 'iso' => 'NZ', 'flag' => '🇳🇿'],
    ];

    /**
     * Detect the country code prefix in a phone string.
     *
     * Strategy: longest prefix match. We try 4 digits, then 3, then 2, then 1
     * because country codes are variable length and a 1-digit code (+1, +7)
     * could be confused with the start of a 3-digit one.
     *
     * @return string|null e.g. '+213', or null if no known prefix matched
     */
    public static function detectCode(string $phone): ?string
    {
        $phone = preg_replace('/\s+/', '', $phone);
        if (! str_starts_with($phone, '+')) {
            return null;
        }

        for ($len = 4; $len >= 1; $len--) {
            $candidate = substr($phone, 0, $len + 1); // +1 for the leading '+'
            if (strlen($candidate) === $len + 1 && isset(self::COUNTRIES[$candidate])) {
                return $candidate;
            }
        }
        return null;
    }

    /**
     * Parse an international phone string into structured pieces.
     *
     * Input examples:
     *   "+213676085441"  → ['code' => '+213', 'iso' => 'DZ', 'flag' => '🇩🇿', 'national' => '676085441']
     *   "+33 6 76 08 54 41" → ['code' => '+33', ..., 'national' => '676085441']
     *   "0676085441"     → null (no country prefix — caller decides default)
     *
     * @return array{code: string, iso: string, flag: string, name: string, national: string}|null
     */
    public static function parse(string $phone): ?array
    {
        $stripped = preg_replace('/[\s\-().]/', '', $phone);
        $code = self::detectCode($stripped);
        if ($code === null) {
            return null;
        }

        $national = substr($stripped, strlen($code));
        $national = ltrim($national, '0'); // strip a leading 0 if present (national prefix)

        $info = self::COUNTRIES[$code];
        return [
            'code'     => $code,
            'iso'      => $info['iso'],
            'flag'     => $info['flag'],
            'name'     => $info['name'],
            'national' => $national,
        ];
    }

    /**
     * Look up flag/iso/name for a stored country code prefix.
     * Returns null if the code isn't in our list.
     *
     * @return array{name: string, iso: string, flag: string}|null
     */
    public static function info(?string $code): ?array
    {
        if ($code === null || $code === '') {
            return null;
        }
        return self::COUNTRIES[$code] ?? null;
    }

    /**
     * "🇩🇿 +213" — short label for tables and badges.
     */
    public static function label(?string $code): string
    {
        $info = self::info($code);
        if (! $info) {
            return $code ?? '';
        }
        return $info['flag'].' '.$code;
    }

    /**
     * Filament SelectFilter / form options keyed by code, label "🇩🇿 +213 — Algerie".
     *
     * @return array<string,string>
     */
    public static function options(): array
    {
        $options = [];
        foreach (self::COUNTRIES as $code => $info) {
            $options[$code] = $info['flag'].'  '.$code.'  — '.$info['name'];
        }
        return $options;
    }
}
