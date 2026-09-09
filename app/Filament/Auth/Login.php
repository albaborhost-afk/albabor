<?php

namespace App\Filament\Auth;

use Filament\Forms\Components\Component;
use Filament\Pages\Auth\Login as BaseLogin;

class Login extends BaseLogin
{
    protected function getEmailFormComponent(): Component
    {
        $normalizeEmail = static fn ($state) => is_string($state) ? strtolower(trim($state)) : $state;

        return parent::getEmailFormComponent()
            ->autocomplete('username')
            ->extraInputAttributes([
                'autocapitalize' => 'none',
                'spellcheck' => 'false',
                'inputmode' => 'email',
                // Capture before Livewire reads the value for its form state.
                'x-on:input.capture' => '$event.target.value = $event.target.value.toLowerCase()',
                'x-on:change.capture' => '$event.target.value = $event.target.value.trim().toLowerCase()',
            ], merge: true)
            ->mutateStateForValidationUsing($normalizeEmail)
            ->dehydrateStateUsing($normalizeEmail);
    }
}
