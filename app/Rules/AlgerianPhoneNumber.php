<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

/**
 * Valide et normalise un numéro de téléphone algérien.
 *
 * Formats acceptés :
 *  - 0XXXXXXXXX      (10 chiffres, ex: 0676085441)
 *  - +213XXXXXXXXX   (format international, ex: +213676085441)
 *  - 00213XXXXXXXXX  (variante internationale)
 *
 * Opérateurs mobiles algériens : 05x, 06x, 07x
 */
class AlgerianPhoneNumber implements ValidationRule
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

        if (! preg_match('/^(0[5-7]\d{8}|\+213[5-7]\d{8})$/', $cleaned)) {
            $fail(
                'Le numéro de téléphone doit être un numéro algérien valide ' .
                '(ex: 0676085441 ou +213676085441). ' .
                'Vérifiez que le numéro contient exactement 10 chiffres.'
            );
        }
    }

    /**
     * Supprime les caractères de formatage (espaces, tirets, points, parenthèses).
     * Convertit également 00213 → +213.
     */
    public static function strip(string $phone): string
    {
        $cleaned = preg_replace('/[\s\-\.\(\)]+/', '', trim($phone));

        // 00213XXXXXXXXX → +213XXXXXXXXX
        if (str_starts_with($cleaned, '00213')) {
            $cleaned = '+213' . substr($cleaned, 5);
        }

        return $cleaned;
    }

    /**
     * Normalise un numéro vers le format local : 0XXXXXXXXX
     *
     * +213676085441  → 0676085441
     * 00213676085441 → 0676085441
     * 0676085441     → 0676085441
     */
    public static function normalize(?string $phone): ?string
    {
        if ($phone === null || trim($phone) === '') {
            return null;
        }

        $cleaned = self::strip($phone);

        // +213XXXXXXXXX → 0XXXXXXXXX
        if (str_starts_with($cleaned, '+213')) {
            return '0' . substr($cleaned, 4);
        }

        return $cleaned;
    }

    /**
     * Règle nullable (numéro optionnel mais validé s'il est fourni).
     */
    public static function nullable(): self
    {
        return new self(required: false);
    }
}
