<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Laravel\Sanctum\PersonalAccessToken;

class ClearUserTokens extends Command
{
    protected $signature = 'user:clear-tokens
                            {--email= : Clear tokens for a specific user by email}
                            {--id=    : Clear tokens for a specific user by ID}
                            {--all    : Clear tokens for ALL users (force logout everyone)}';

    protected $description = 'Delete Sanctum tokens to force users to re-login and get fresh data';

    public function handle(): int
    {
        if ($this->option('email')) {
            return $this->clearForEmail($this->option('email'));
        }

        if ($this->option('id')) {
            return $this->clearForId((int) $this->option('id'));
        }

        if ($this->option('all')) {
            return $this->clearAll();
        }

        $this->error('Please specify an option: --email=, --id=, or --all');
        $this->line('Examples:');
        $this->line('  php artisan user:clear-tokens --email=user@example.com');
        $this->line('  php artisan user:clear-tokens --id=42');
        $this->line('  php artisan user:clear-tokens --all');

        return 1;
    }

    private function clearForEmail(string $email): int
    {
        $user = User::where('email', $email)->first();

        if (! $user) {
            $this->error("Aucun utilisateur trouvé avec l'email : {$email}");
            return 1;
        }

        $count = $user->tokens()->count();
        $user->tokens()->delete();

        $this->info("✓ {$count} token(s) supprimé(s) pour {$user->name} ({$email}).");
        $this->line("  L'utilisateur devra se reconnecter pour récupérer les données fraîches.");

        return 0;
    }

    private function clearForId(int $id): int
    {
        $user = User::find($id);

        if (! $user) {
            $this->error("Aucun utilisateur trouvé avec l'ID : {$id}");
            return 1;
        }

        $count = $user->tokens()->count();
        $user->tokens()->delete();

        $this->info("✓ {$count} token(s) supprimé(s) pour {$user->name} (ID={$id}).");
        $this->line("  L'utilisateur devra se reconnecter pour récupérer les données fraîches.");

        return 0;
    }

    private function clearAll(): int
    {
        if (! $this->confirm('Supprimer les tokens de TOUS les utilisateurs ? Ils seront tous déconnectés.')) {
            $this->line('Annulé.');
            return 0;
        }

        $count = PersonalAccessToken::count();
        PersonalAccessToken::truncate();

        $this->info("✓ {$count} token(s) supprimé(s). Tous les utilisateurs sont maintenant déconnectés.");
        $this->line("  Ils devront se reconnecter pour récupérer les données fraîches.");

        return 0;
    }
}
