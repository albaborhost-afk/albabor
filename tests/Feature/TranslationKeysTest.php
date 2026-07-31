<?php

namespace Tests\Feature;

use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use Tests\TestCase;

/**
 * Garde-fou contre les clés de traduction affichées telles quelles.
 *
 * Le projet contient un dossier `resources/lang` ET, historiquement, un
 * dossier `lang` à la racine. Laravel ne charge que `resources/lang` (il le
 * préfère dès qu'il existe), si bien que des clés ajoutées au mauvais endroit
 * s'affichaient en clair sur le site : « messages.banner_request_title » au
 * lieu du titre. Le dossier mort a été supprimé ; ce test empêche la rechute.
 *
 * Il attrape aussi une simple faute de frappe dans une clé.
 */
class TranslationKeysTest extends TestCase
{
    public function test_every_message_key_used_in_a_view_exists(): void
    {
        $translations = require base_path('resources/lang/fr/messages.php');
        $missing = [];

        foreach ($this->bladeFiles() as $path) {
            $contents = file_get_contents($path);

            if (! preg_match_all('/__\(\s*[\'"]messages\.([a-zA-Z0-9_.]+)[\'"]/', $contents, $matches)) {
                continue;
            }

            foreach ($matches[1] as $key) {
                if (! array_key_exists($key, $translations)) {
                    $missing[] = $key . '  (' . str_replace(base_path() . '/', '', $path) . ')';
                }
            }
        }

        $this->assertSame(
            [],
            array_values(array_unique($missing)),
            "Ces clés sont utilisées dans une vue mais absentes de resources/lang/fr/messages.php.\n"
            . "Elles s'afficheraient en clair sur le site."
        );
    }

    public function test_the_dead_root_lang_directory_is_gone(): void
    {
        $this->assertDirectoryDoesNotExist(
            base_path('lang'),
            'Laravel charge resources/lang dès qu\'il existe : un dossier lang/ à la racine '
            . 'serait ignoré en silence et les traductions qu\'il contient invisibles.'
        );
    }

    public function test_translations_actually_resolve(): void
    {
        // Une assertion non tautologique : `__()` doit renvoyer autre chose que
        // la clé. Comparer `__(clé)` au contenu de la page passerait même quand
        // les deux affichent la clé brute.
        foreach (['banner_request_title', 'banner_cta_home_button', 'hide_name_label'] as $key) {
            $this->assertNotSame(
                "messages.$key",
                __("messages.$key"),
                "La clé messages.$key ne se traduit pas."
            );
        }
    }

    /** @return iterable<string> */
    private function bladeFiles(): iterable
    {
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator(resource_path('views'))
        );

        foreach ($iterator as $file) {
            if ($file->isFile() && str_ends_with($file->getFilename(), '.blade.php')) {
                yield $file->getPathname();
            }
        }
    }
}
