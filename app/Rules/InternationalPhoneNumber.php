<?php

namespace App\Rules;

use App\Support\PhoneCountry;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

/**
 * Valide un numéro de téléphone international.
 *
 * Format attendu : +CC suivi de 6 à 14 chiffres après le code pays.
 *  - +213676085441    (Algérie)
 *  - +33676085441     (France)
 *  - +491761234567    (Allemagne)
 *
 * Tolère les formats locaux historiques (0XXXXXXXXX) qui sont
 * automatiquement traités comme des numéros algériens — préserve
 * la compatibilité avec les anciens formulaires.
 */
class InternationalPhoneNumber implements ValidationRule
{
    public function __construct(private bool $required = true) {}

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if ($value === null || trim($value) === '') {
            if ($this->required) {
                $fail('Le numéro de téléphone est obligatoire.');
            }
            return;
        }

        $cleaned = self::strip($value);

        // Local Algerian format: implicit +213
        if (preg_match('/^0[5-7]\d{8}$/', $cleaned)) {
            return;
        }

        // International: +CC followed by 6–14 digits
        if (! preg_match('/^\+\d{1,4}\d{6,14}$/', $cleaned)) {
            $fail(
                'Le numéro de téléphone doit être au format international '
                .'(ex: +213676085441, +33676085441).'
            );
            return;
        }

        if (PhoneCountry::detectCode($cleaned) === null) {
            $fail('Indicatif pays non reconnu. Vérifiez le numéro.');
        }
    }

    /**
     * Strip formatting characters and normalize 00CC → +CC.
     */
    public static function strip(string $phone): string
    {
        $cleaned = preg_replace('/[\s\-\.\(\)]+/', '', trim($phone));

        if (str_starts_with($cleaned, '00')) {
            $cleaned = '+'.substr($cleaned, 2);
        }

        return $cleaned;
    }

    /**
     * Split a phone into [country_code, national_number].
     *
     * Returns ['+213', '0676085441'] for Algerian inputs (with leading 0
     * preserved to match the historic stored format), or ['+33', '676085441']
     * for non-Algerian inputs. Returns [null, $original] if the format is
     * unrecognised — caller should validate first.
     *
     * @return array{0: string|null, 1: string}
     */
    public static function split(?string $phone): array
    {
        if ($phone === null || trim($phone) === '') {
            return [null, ''];
        }

        $cleaned = self::strip($phone);

        // Local Algerian format → implicit +213 country code
        if (preg_match('/^0[5-7]\d{8}$/', $cleaned)) {
            return ['+213', $cleaned];
        }

        $code = PhoneCountry::detectCode($cleaned);
        if ($code === null) {
            return [null, $cleaned];
        }

        $national = substr($cleaned, strlen($code));

        // Algerian national numbers are stored with a leading 0 by historic
        // convention; keep the existing storage shape on every Algerian save.
        if ($code === '+213' && ! str_starts_with($national, '0')) {
            $national = '0'.$national;
        }

        return [$code, $national];
    }

    public static function nullable(): self
    {
        return new self(required: false);
    }

    /**
     * Normalise a phone for storage.
     *
     * - Algerian numbers collapse to the historic local format 0XXXXXXXXX
     *   (matches what AlgerianPhoneNumber::normalize produced before).
     * - All other international numbers are kept in +CC… form so the
     *   country code is preserved in storage.
     * - Empty input → null.
     */
    public static function normalize(?string $phone): ?string
    {
        if ($phone === null || trim($phone) === '') {
            return null;
        }

        $cleaned = self::strip($phone);

        // +213XXXXXXXXX → 0XXXXXXXXX (legacy Algerian storage shape)
        if (str_starts_with($cleaned, '+213')) {
            return '0'.substr($cleaned, 4);
        }

        return $cleaned;
    }
}
