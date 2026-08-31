<?php

namespace App\Filament\Support;

use App\Models\User;
use App\Rules\EligibleListingOwner;
use App\Rules\InternationalPhoneNumber;
use App\Services\ListingOwnership;
use Filament\Forms;
use Filament\Forms\Components\Select;

/**
 * Le champ « Vendeur » de l'administration : à qui appartient l'annonce.
 *
 * Un seul composant pour la création d'annonce et pour le transfert, afin
 * que la règle soit la même partout : recherche par nom, e-mail ou téléphone,
 * jamais de compte administrateur, et création du compte sur place quand la
 * personne n'en a pas encore.
 */
final class ListingOwnerSelect
{
    public static function make(string $name = 'user_id'): Select
    {
        return Select::make($name)
            ->label('Vendeur (propriétaire de l\'annonce)')
            ->placeholder('Rechercher par nom, e-mail ou téléphone…')
            ->searchable()
            ->searchDebounce(300)
            ->searchPrompt('Tapez un nom, un e-mail ou un numéro de téléphone.')
            ->noSearchResultsMessage('Aucun compte trouvé — vous pouvez le créer ci-dessous.')
            ->getSearchResultsUsing(fn (string $search): array => self::search($search))
            ->getOptionLabelUsing(function ($value): ?string {
                $user = $value ? User::find($value) : null;

                return $user ? self::label($user) : null;
            })
            ->required()
            ->rule(new EligibleListingOwner)
            ->validationMessages([
                'required' => 'Choisissez le compte du vendeur, ou créez-le.',
            ])
            ->helperText('Le compte au nom duquel l\'annonce est publiée. Les comptes administrateurs sont exclus.')
            ->createOptionModalHeading('Créer le compte du vendeur')
            ->createOptionAction(fn (Forms\Components\Actions\Action $action) => $action
                ->label('Créer un compte')
                ->modalSubmitActionLabel('Créer le compte')
                ->modalDescription('La personne n\'a pas encore de compte : créez-le ici, il sera sélectionné automatiquement.'))
            ->createOptionForm(self::accountForm())
            ->createOptionUsing(fn (array $data): int => app(ListingOwnership::class)->createOwnerAccount($data)->id);
    }

    /**
     * Comptes proposés par la recherche : tout sauf les administrateurs.
     * Les comptes bloqués apparaissent (l'administrateur doit pouvoir les
     * retrouver) mais sont refusés à la validation, avec l'explication.
     *
     * @return array<int, string>
     */
    public static function search(string $term): array
    {
        $term = trim($term);

        if ($term === '') {
            return [];
        }

        $digits = preg_replace('/\D+/', '', $term) ?? '';

        return User::query()
            ->where('account_type', '!=', 'admin')
            ->where(function ($query) use ($term, $digits) {
                $query->where('name', 'like', "%{$term}%")
                    ->orWhere('email', 'like', "%{$term}%");

                if (strlen($digits) >= 4) {
                    $query->orWhere('phone', 'like', "%{$digits}%");

                    // « +213 6… » tapé au format international alors que les
                    // numéros algériens sont stockés en 06… : on cherche aussi
                    // la forme nationale.
                    if (str_starts_with($digits, '213')) {
                        $query->orWhere('phone', 'like', '%0'.substr($digits, 3).'%');
                    }
                }
            })
            ->orderBy('name')
            ->limit(25)
            ->get()
            ->mapWithKeys(fn (User $user) => [$user->id => self::label($user)])
            ->all();
    }

    /** « Nom — e-mail — téléphone », avec l'état du compte quand il compte. */
    public static function label(User $user): string
    {
        $parts = array_filter([
            $user->real_name,
            $user->email,
            $user->phone ? trim(($user->phone_country_code ?? '').' '.$user->phone) : null,
        ]);

        $label = implode(' — ', $parts);

        if ($user->isAdmin()) {
            $label .= ' (compte administrateur)';
        } elseif ($user->isBlocked()) {
            $label .= ' (bloqué)';
        }

        return $label;
    }

    /**
     * Formulaire de création du compte vendeur, sur place.
     *
     * @return array<int, Forms\Components\Component>
     */
    public static function accountForm(): array
    {
        return [
            Forms\Components\TextInput::make('name')
                ->label('Nom complet')
                ->required()
                ->maxLength(255),
            Forms\Components\TextInput::make('email')
                ->label('Adresse e-mail')
                ->email()
                ->required()
                ->maxLength(255)
                ->live(onBlur: true)
                ->afterStateUpdated(fn (Forms\Set $set, ?string $state) => $set('email', strtolower(trim((string) $state))))
                ->unique(table: User::class, column: 'email')
                ->validationMessages([
                    'unique' => 'Un compte existe déjà avec cette adresse : recherchez-le plutôt que d\'en créer un second.',
                ]),
            Forms\Components\TextInput::make('phone')
                ->label('Téléphone')
                ->tel()
                ->required()
                ->maxLength(30)
                ->rule(new InternationalPhoneNumber)
                ->helperText('Format local (0676085441) ou international (+33676085441).'),
            Forms\Components\Select::make('account_type')
                ->label('Type de compte')
                ->options([
                    'user'   => 'Particulier',
                    'vendor' => 'Vendeur professionnel',
                ])
                ->default('user')
                ->required()
                ->native(false)
                ->helperText('Vendeur professionnel : pour publier des moteurs et des pièces (abonnement requis).'),
            Forms\Components\TextInput::make('password')
                ->label('Mot de passe')
                ->password()
                ->revealable()
                ->minLength(8)
                ->maxLength(255)
                ->helperText('Facultatif. Laissez vide : un mot de passe aléatoire est enregistré et la personne en choisit un via « Mot de passe oublié ».'),
        ];
    }
}
