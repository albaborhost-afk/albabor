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

        // Local Algerian mobile: implicit +213, with or without the leading 0
        // ("0770308424" or "770308424" — clients pairing a +213 selector with
        // a bare national number submit the second shape).
        if (preg_match('/^0?[5-7]\d{8}$/', $cleaned)) {
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
     *
     * Keeps only digits and a leading '+'. This also removes the invisible
     * bidi/direction marks (U+200E, U+200F, …) and non-breaking spaces that
     * phones insert when a number is pasted from contacts or WhatsApp on an
     * Arabic-locale device — those used to survive and fail validation even
     * though the number looked correct on screen.
     */
    public static function strip(string $phone): string
    {
        // Eastern Arabic (٠-٩) and Persian (۰-۹) digits → ASCII
        $phone = strtr($phone, [
            '٠' => '0', '١' => '1', '٢' => '2', '٣' => '3', '٤' => '4',
            '٥' => '5', '٦' => '6', '٧' => '7', '٨' => '8', '٩' => '9',
            '۰' => '0', '۱' => '1', '۲' => '2', '۳' => '3', '۴' => '4',
            '۵' => '5', '۶' => '6', '۷' => '7', '۸' => '8', '۹' => '9',
        ]);

        // Drop every byte that is not a digit or '+', then keep '+' only in
        // first position.
        $cleaned = preg_replace('/[^0-9+]/', '', $phone);
        $hasPlus = str_starts_with($cleaned, '+');
        $cleaned = ($hasPlus ? '+' : '').str_replace('+', '', $cleaned);

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

        // Local Algerian mobile (with or without leading 0) → implicit +213
        if (preg_match('/^0?([5-7]\d{8})$/', $cleaned, $matches)) {
            return ['+213', '0'.$matches[1]];
        }

        $code = PhoneCountry::detectCode($cleaned);
        if ($code === null) {
            return [null, $cleaned];
        }

        $national = substr($cleaned, strlen($code));

        // Algerian national numbers are stored with a leading 0 by historic
        // convention; keep the existing storage shape on every Algerian save
        // (ltrim guards against "+2130…" double-zero submissions).
        if ($code === '+213') {
            $national = '0'.ltrim($national, '0');
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

        // Bare Algerian mobile (with or without leading 0) → 0XXXXXXXXX
        if (preg_match('/^0?([5-7]\d{8})$/', $cleaned, $matches)) {
            return '0'.$matches[1];
        }

        // +213XXXXXXXXX → 0XXXXXXXXX (legacy Algerian storage shape);
        // ltrim guards against "+2130…" double-zero submissions.
        if (str_starts_with($cleaned, '+213')) {
            return '0'.ltrim(substr($cleaned, 4), '0');
        }

        return $cleaned;
    }
}
