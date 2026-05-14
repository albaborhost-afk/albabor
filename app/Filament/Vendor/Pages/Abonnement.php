<?php

namespace App\Filament\Vendor\Pages;

use App\Models\Payment;
use App\Models\Plan;
use App\Models\Subscription;
use Filament\Actions\Action;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Auth;

class Abonnement extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-credit-card';

    protected static ?string $navigationLabel = 'Mon abonnement';

    protected static ?string $navigationGroup = 'Abonnement';

    protected static ?string $title = 'Mon abonnement';

    protected static ?int $navigationSort = 1;

    protected static string $view = 'filament.vendor.pages.abonnement';

    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill();
    }

    public function form(Form $form): Form
    {
        $disk = config('filesystems.listing_disk', 'public');

        return $form
            ->schema([
                Section::make('S\'abonner ou renouveler')
                    ->description('Choisissez un plan, sélectionnez votre moyen de paiement et téléversez le justificatif. Votre abonnement sera activé après validation par l\'administration.')
                    ->icon('heroicon-o-arrow-up-circle')
                    ->schema([
                        Select::make('plan_id')
                            ->label('Plan')
                            ->options(Plan::active()->orderBy('price_dzd')->get()->mapWithKeys(fn (Plan $plan) => [
                                $plan->id => $plan->name . ' — ' . $plan->formatted_price . ' / ' . $plan->duration_days . ' jours',
                            ]))
                            ->required()
                            ->native(false)
                            ->placeholder('Sélectionnez un plan'),

                        Select::make('method')
                            ->label('Moyen de paiement')
                            ->options([
                                'baridimob' => 'BaridiMob',
                                'ccp' => 'CCP',
                                'bank_transfer' => 'BEA – Virement bancaire',
                                'paypal' => 'PayPal',
                                'redotpay' => 'RedotPay – SEPA',
                            ])
                            ->required()
                            ->native(false)
                            ->placeholder('Sélectionnez un moyen de paiement'),

                        FileUpload::make('proof')
                            ->label('Justificatif de paiement')
                            ->image()
                            ->required()
                            ->disk($disk)
                            ->directory('payment-proofs')
                            ->maxSize(5120)
                            ->helperText('Téléversez une capture ou une photo du reçu de paiement. Taille maximale : 5 Mo.')
                            ->columnSpanFull(),
                    ])
                    ->columns(2),
            ])
            ->statePath('data');
    }

    public function submitProof(): void
    {
        $data = $this->form->getState();

        $plan = Plan::active()->findOrFail($data['plan_id']);

        $subscription = Subscription::create([
            'user_id' => Auth::id(),
            'plan_id' => $plan->id,
            'status' => 'pending',
            'starts_at' => now(),
            'ends_at' => now()->addDays($plan->duration_days),
        ]);

        Payment::create([
            'user_id' => Auth::id(),
            'type' => 'vendor_subscription',
            'amount_dzd' => $plan->price_dzd,
            'method' => $data['method'],
            'proof_path' => $data['proof'],
            'status' => 'pending',
            'subscription_id' => $subscription->id,
        ]);

        $this->form->fill();

        Notification::make()
            ->title('Justificatif envoyé')
            ->body('Justificatif envoyé — en attente de validation par l\'administration.')
            ->success()
            ->send();
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('submitProof')
                ->label('Envoyer le justificatif')
                ->icon('heroicon-o-paper-airplane')
                ->action('submitProof'),
        ];
    }
}
