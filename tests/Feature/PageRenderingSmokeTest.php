<?php

namespace Tests\Feature;

use App\Models\Listing;
use App\Models\ListingMedia;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * Chaque page importante doit s'afficher sans erreur.
 *
 * Une erreur 500 est partie en production parce qu'aucun test n'ouvrait la
 * page de publication : un type JSDoc « {{...}} » dans le composant photo
 * était compilé par Blade en `echo`, et toute la page tombait. Compiler les
 * vues (`view:cache`) ne suffit pas — la compilation réussit, c'est
 * l'exécution qui échoue.
 *
 * Ce test ne vérifie pas le contenu : il vérifie que ça s'affiche. C'est le
 * filet qui manquait.
 */
class PageRenderingSmokeTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
        Storage::fake(config('filesystems.listing_disk', 'public'));
    }

    private function seller(): User
    {
        return User::factory()->create(['phone' => '+213670000010']);
    }

    private function listingFor(User $seller): Listing
    {
        return Listing::create([
            'user_id'         => $seller->id,
            'title'           => 'Bateau de peche 6m',
            'description'     => 'En bon etat.',
            'category'        => 'boat',
            'type'            => 'bateau_peche',
            'price_dzd'       => 1_500_000,
            'currency'        => 'DZD',
            'type_offre'      => 'negociable',
            'etat'            => 'bon_etat',
            'wilaya'          => 'Alger',
            'status'          => 'active',
            'published_until' => now()->addYear(),
        ]);
    }

    // ── Pages publiques ──────────────────────────────────────────────────────

    public function test_the_public_pages_render(): void
    {
        $seller  = $this->seller();
        $listing = $this->listingFor($seller);

        $pages = [
            'accueil'          => route('home'),
            'annonces'         => route('listings.index'),
            'détail annonce'   => route('listings.show', $listing),
            'profil vendeur'   => route('sellers.show', $seller),
            'boutiques'        => route('boutiques.index'),
            'publicité'        => route('publicite.create'),
            'connexion'        => route('login'),
            'inscription'      => route('register'),
        ];

        foreach ($pages as $name => $url) {
            $this->get($url)->assertOk("La page « {$name} » ne s'affiche pas.");
        }
    }

    // ── Pages authentifiées ──────────────────────────────────────────────────

    public function test_the_listing_creation_page_renders(): void
    {
        // C'est exactement la page qui est tombée en production.
        $this->actingAs($this->seller())
            ->get(route('listings.create'))
            ->assertOk()
            ->assertSee('photoUploader', false);
    }

    public function test_the_listing_edit_page_renders_with_existing_photos(): void
    {
        $seller  = $this->seller();
        $listing = $this->listingFor($seller);

        // Le contrôleur supprime les médias absents du stockage : le fichier
        // doit exister pour que la branche « photos déjà enregistrées » soit
        // réellement parcourue.
        $disk = config('filesystems.listing_disk', 'public');
        Storage::disk($disk)->put('listings/' . $listing->id . '/photo.jpg', 'contenu');

        ListingMedia::create([
            'listing_id' => $listing->id,
            'path'       => 'listings/' . $listing->id . '/photo.jpg',
            'order'      => 1,
        ]);

        $this->actingAs($seller)
            ->get(route('listings.edit', $listing))
            ->assertOk()
            ->assertSee('photoUploader', false);
    }

    public function test_the_account_pages_render(): void
    {
        $seller  = $this->seller();
        $this->listingFor($seller);

        $pages = [
            'profil'        => route('profile.show'),
            'profil édition'=> route('profile.edit'),
            'mes annonces'  => route('listings.my'),
            'favoris'       => route('favorites.index'),
            'messages'      => route('conversations.index'),
            'paiements'     => route('payments.index'),
            'médiation'     => route('mediation.index'),
        ];

        foreach ($pages as $name => $url) {
            $this->actingAs($seller)->get($url)->assertOk("La page « {$name} » ne s'affiche pas.");
        }
    }

    public function test_the_admin_panel_renders(): void
    {
        $admin = User::factory()->create([
            'account_type' => 'admin',
            'phone'        => '+213670000011',
        ]);

        foreach (['/admin', '/admin/statistiques', '/admin/banner-requests'] as $url) {
            $this->actingAs($admin)->get($url)->assertOk("La page admin « {$url} » ne s'affiche pas.");
        }
    }

    /**
     * La zone de dépôt doit être un <label> relié au champ de fichiers.
     *
     * Elle ouvrait la galerie via `$refs.fileInput.click()`. Sur Android, un
     * clic déclenché depuis JavaScript n'ouvre pas toujours le sélecteur, et
     * le moindre appui un peu long sélectionnait le texte au lieu d'ouvrir
     * quoi que ce soit : l'utilisateur voyait « Cliquez » surligné en bleu et
     * ne pouvait plus rien envoyer. Un <label for="…"> est traité nativement.
     */
    public function test_the_photo_drop_zone_opens_the_picker_natively(): void
    {
        $html = $this->actingAs($this->seller())
            ->get(route('listings.create'))
            ->assertOk()
            ->getContent();

        $this->assertStringContainsString(
            'id="photo-uploader-images"',
            $html,
            'Le champ de fichiers doit porter un identifiant pour être ciblé par un label.'
        );

        $this->assertGreaterThanOrEqual(
            2,
            substr_count($html, 'for="photo-uploader-images"'),
            'La zone vide et la tuile « Ajouter » doivent toutes deux être des labels.'
        );

        // Un appui ne doit jamais se transformer en sélection de texte.
        $this->assertStringContainsString('-webkit-touch-callout:none', $html);
    }

    /**
     * Aucune commande photo ne doit dépendre du survol.
     *
     * Supprimer et Flouter étaient masqués au-delà de 640 px et ne
     * réapparaissaient qu'au survol : sur une tablette — écran large, pas de
     * survol — ils étaient définitivement inaccessibles, et sur ordinateur
     * personne ne devine qu'il faut survoler une photo pour la supprimer.
     *
     * Les libellés d'aide passent par la classe `photo-hint`, qui n'est
     * masquée que sous `@media (hover: hover)`.
     */
    public function test_no_photo_control_is_hidden_behind_hover(): void
    {
        $component = file_get_contents(
            resource_path('views/components/photo-uploader.blade.php')
        );

        foreach (['sm:opacity-0', 'opacity-0 group-hover', 'sm:group-hover:opacity-100'] as $pattern) {
            $this->assertStringNotContainsString(
                $pattern,
                $component,
                "« {$pattern} » rend une commande inaccessible sur écran tactile large."
            );
        }
    }

    /**
     * Blade compile toute accolade doublée en `echo`, y compris dans un
     * commentaire JavaScript. C'est ce qui a provoqué la panne.
     */
    public function test_no_view_contains_a_javascript_type_in_double_braces(): void
    {
        $offenders = [];

        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator(resource_path('views'))
        );

        foreach ($iterator as $file) {
            if (! $file->isFile() || ! str_ends_with($file->getFilename(), '.blade.php')) {
                continue;
            }

            foreach (file($file->getPathname()) as $number => $line) {
                // `{{` suivi d'une accolade ouvrante : une expression Blade ne
                // commence jamais ainsi, un type JSDoc si.
                if (preg_match('/\{\{\s*\{/', $line)) {
                    $offenders[] = str_replace(base_path() . '/', '', $file->getPathname()) . ':' . ($number + 1);
                }
            }
        }

        $this->assertSame([], $offenders, "Accolades doublées suspectes — Blade les exécutera :\n");
    }
}
