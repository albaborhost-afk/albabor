<?php

namespace App\Rules;

use App\Models\User;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

/**
 * La valeur est l'identifiant d'un compte qui peut être propriétaire d'une
 * annonce — ni administrateur, ni bloqué (voir User::listingOwnershipRefusal).
 */
class EligibleListingOwner implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $user = User::find($value);

        if (! $user) {
            $fail('Ce compte n\'existe pas.');

            return;
        }

        if ($reason = $user->listingOwnershipRefusal()) {
            $fail($reason);
        }
    }
}
