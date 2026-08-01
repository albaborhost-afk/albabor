<?php

namespace App\Http\Controllers;

use App\Models\Listing;
use App\Models\VendorProfile;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Cache;

/**
 * Plan du site pour les moteurs de recherche.
 *
 * /sitemap.xml renvoyait 404 : Google devait découvrir les annonces en
 * suivant les liens, ce qui laisse de côté celles qui ne sont plus en
 * première page. Pour une place de marché, c'est la principale source de
 * visiteurs qui manquait.
 *
 * Le contenu est mis en cache : régénérer le plan à chaque passage de robot
 * ferait un balayage complet de la table à chaque fois.
 */
class SitemapController extends Controller
{
    private const CACHE_MINUTES = 60;

    /** Limite de la norme sitemap ; largement au-dessus du volume actuel. */
    private const MAX_URLS = 50_000;

    public function index(): Response
    {
        $xml = Cache::remember('sitemap.xml', now()->addMinutes(self::CACHE_MINUTES), fn () => $this->build());

        return response($xml, 200, [
            'Content-Type'  => 'application/xml; charset=UTF-8',
            'Cache-Control' => 'public, max-age=3600',
        ]);
    }

    private function build(): string
    {
        $urls = [];

        // Pages fixes. Priorités relatives : l'accueil et la liste d'annonces
        // sont les portes d'entrée, les pages légales n'intéressent personne.
        $static = [
            ['route' => 'home',              'priority' => '1.0', 'freq' => 'daily'],
            ['route' => 'listings.index',    'priority' => '0.9', 'freq' => 'daily'],
            ['route' => 'boutiques.index',   'priority' => '0.6', 'freq' => 'weekly'],
            ['route' => 'publicite.create',  'priority' => '0.4', 'freq' => 'monthly'],
            ['route' => 'pages.terms',       'priority' => '0.2', 'freq' => 'yearly'],
            ['route' => 'pages.privacy',     'priority' => '0.2', 'freq' => 'yearly'],
            ['route' => 'pages.legal',       'priority' => '0.2', 'freq' => 'yearly'],
        ];

        foreach ($static as $page) {
            $urls[] = [
                'loc'      => route($page['route']),
                'priority' => $page['priority'],
                'freq'     => $page['freq'],
            ];
        }

        // Une page par catégorie : ce sont les requêtes que les gens tapent.
        foreach (['boat', 'jetski', 'engine', 'parts'] as $category) {
            $urls[] = [
                'loc'      => route('listings.index', ['category' => $category]),
                'priority' => '0.8',
                'freq'     => 'daily',
            ];
        }

        // Annonces actives, la plus récemment modifiée en premier.
        Listing::query()
            ->active()
            ->select(['id', 'updated_at'])
            ->orderByDesc('updated_at')
            ->limit(self::MAX_URLS - count($urls) - 100)
            ->chunk(500, function ($listings) use (&$urls) {
                foreach ($listings as $listing) {
                    $urls[] = [
                        'loc'      => route('listings.show', $listing->id),
                        'lastmod'  => $listing->updated_at?->toAtomString(),
                        'priority' => '0.7',
                        'freq'     => 'weekly',
                    ];
                }
            });

        // Boutiques professionnelles actives.
        VendorProfile::query()
            ->where('is_active', true)
            ->select(['id', 'updated_at'])
            ->chunk(200, function ($boutiques) use (&$urls) {
                foreach ($boutiques as $boutique) {
                    $urls[] = [
                        'loc'      => route('boutiques.show', $boutique->id),
                        'lastmod'  => $boutique->updated_at?->toAtomString(),
                        'priority' => '0.5',
                        'freq'     => 'weekly',
                    ];
                }
            });

        return $this->render($urls);
    }

    /** @param array<int, array<string, string|null>> $urls */
    private function render(array $urls): string
    {
        $lines = [
            '<?xml version="1.0" encoding="UTF-8"?>',
            '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">',
        ];

        foreach ($urls as $url) {
            $lines[] = '  <url>';
            $lines[] = '    <loc>' . htmlspecialchars($url['loc'], ENT_XML1) . '</loc>';

            if (! empty($url['lastmod'])) {
                $lines[] = '    <lastmod>' . $url['lastmod'] . '</lastmod>';
            }

            $lines[] = '    <changefreq>' . $url['freq'] . '</changefreq>';
            $lines[] = '    <priority>' . $url['priority'] . '</priority>';
            $lines[] = '  </url>';
        }

        $lines[] = '</urlset>';

        return implode("\n", $lines);
    }
}
