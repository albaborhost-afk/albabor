<?php

namespace App\Filament\Vendor\Pages;

use App\Models\VendorProfile;
use Filament\Actions\Action;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Auth;

class ManageBoutique extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-building-storefront';

    protected static ?string $navigationLabel = 'Ma boutique';

    protected static ?string $navigationGroup = 'Ma boutique';

    protected static ?string $title = 'Ma boutique';

    protected static ?int $navigationSort = 1;

    protected static string $view = 'filament.vendor.pages.manage-boutique';

    public ?VendorProfile $profile = null;

    public ?array $data = [];

    public function mount(): void
    {
        $user = Auth::user();

        if (! $user || ! $user->hasVendorProfile()) {
            Notification::make()
                ->title('Aucune boutique')
                ->body('Vous devez d\'abord créer votre boutique pour accéder à cet espace.')
                ->warning()
                ->send();

            $this->redirect(route('vendor.onboarding.create'));

            return;
        }

        $this->profile = $user->vendorProfile;

        $this->form->fill($this->profile->only([
            'shop_name',
            'slug',
            'description',
            'logo_path',
            'banner_path',
            'contact_phone',
            'contact_email',
            'wilaya',
            'address',
            'is_active',
        ]));
    }

    public function form(Form $form): Form
    {
        $disk = config('filesystems.listing_disk', 'public');

        return $form
            ->schema([
                Section::make('Identité de la boutique')
                    ->description('Le nom et la description visibles par vos clients.')
                    ->icon('heroicon-o-identification')
                    ->schema([
                        TextInput::make('shop_name')
                            ->label('Nom de la boutique')
                            ->required()
                            ->maxLength(255)
                            ->columnSpanFull(),

                        Placeholder::make('slug_display')
                            ->label('Adresse de la boutique (slug)')
                            ->content(fn () => $this->profile?->slug ?? '—')
                            ->helperText('Le slug est généré automatiquement à partir du nom de la boutique. Il ne peut pas être modifié manuellement.')
                            ->columnSpanFull(),

                        RichEditor::make('description')
                            ->label('Description')
                            ->placeholder('Présentez votre boutique, vos spécialités, vos garanties...')
                            ->toolbarButtons([
                                'bold',
                                'italic',
                                'bulletList',
                                'orderedList',
                                'link',
                            ])
                            ->columnSpanFull(),
                    ]),

                Section::make('Visuels')
                    ->description('Le logo et la bannière affichés sur votre page boutique.')
                    ->icon('heroicon-o-photo')
                    ->schema([
                        FileUpload::make('logo_path')
                            ->label('Logo')
                            ->image()
                            ->avatar()
                            ->imageEditor()
                            ->disk($disk)
                            ->directory('vendor-logos')
                            ->maxSize(4096)
                            ->helperText('Format carré recommandé. Taille maximale : 4 Mo.'),

                        FileUpload::make('banner_path')
                            ->label('Bannière')
                            ->image()
                            ->imageEditor()
                            ->disk($disk)
                            ->directory('vendor-banners')
                            ->maxSize(8192)
                            ->helperText('Image large recommandée (ex : 1600 × 400). Taille maximale : 8 Mo.'),
                    ])
                    ->columns(2),

                Section::make('Coordonnées')
                    ->description('Les informations de contact de votre boutique.')
                    ->icon('heroicon-o-phone')
                    ->schema([
                        TextInput::make('contact_phone')
                            ->label('Téléphone de contact')
                            ->tel()
                            ->maxLength(50),

                        TextInput::make('contact_email')
                            ->label('Email de contact')
                            ->email()
                            ->maxLength(255),

                        TextInput::make('wilaya')
                            ->label('Wilaya')
                            ->placeholder('Ex : Alger, Oran, Annaba...')
                            ->maxLength(100),

                        TextInput::make('address')
                            ->label('Adresse')
                            ->placeholder('Adresse complète de la boutique')
                            ->maxLength(255),
                    ])
                    ->columns(2),

                Section::make('Visibilité')
                    ->description('Contrôlez la publication de votre boutique.')
                    ->icon('heroicon-o-eye')
                    ->schema([
                        Toggle::make('is_active')
                            ->label('Boutique visible publiquement')
                            ->helperText('Lorsque cette option est désactivée, votre boutique n\'apparaît plus dans l\'annuaire public.')
                            ->onColor('success')
                            ->offColor('warning'),
                    ]),
            ])
            ->statePath('data');
    }

    public function save(): void
    {
        $data = $this->form->getState();

        // Le slug est géré automatiquement par le modèle : on ne le modifie pas ici.
        unset($data['slug'], $data['slug_display']);

        $this->profile->update($data);

        $this->form->fill($this->profile->fresh()->only([
            'shop_name',
            'slug',
            'description',
            'logo_path',
            'banner_path',
            'contact_phone',
            'contact_email',
            'wilaya',
            'address',
            'is_active',
        ]));

        Notification::make()
            ->title('Boutique mise à jour')
            ->body('Les informations de votre boutique ont été enregistrées avec succès.')
            ->success()
            ->send();
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('voir_boutique')
                ->label('Voir ma boutique')
                ->icon('heroicon-o-arrow-top-right-on-square')
                ->color('gray')
                ->url(fn () => $this->profile ? route('boutiques.show', $this->profile) : '#')
                ->openUrlInNewTab()
                ->visible(fn () => filled($this->profile)),

            Action::make('enregistrer')
                ->label('Enregistrer les modifications')
                ->icon('heroicon-o-check')
                ->action('save'),
        ];
    }
}
