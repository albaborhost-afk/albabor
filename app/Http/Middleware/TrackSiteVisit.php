<?php

namespace App\Http\Middleware;

use App\Models\SiteVisit;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Compte la fréquentation des pages publiques du site.
 *
 * Volontairement limité : seules les vraies pages consultées par un visiteur
 * sont comptées. Compter les panels d'administration, les images servies par
 * proxy ou les sondages en arrière-plan gonflerait les chiffres et les rendrait
 * inutilisables pour décider quoi que ce soit.
 */
class TrackSiteVisit
{
    /** Préfixes qui ne sont pas des pages consultées par un visiteur. */
    private const IGNORED_PREFIXES = [
        'admin',
        'vendeur',
        'media',
        'stripe',
        'livewire',
        'storage',
        'up',
    ];

    /** Robots courants : ils ne sont pas des visiteurs. */
    private const BOT_PATTERN = '/bot|crawler|spider|crawling|slurp|facebookexternalhit|preview|monitor|curl|wget|python-requests|headless/i';

    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        if ($this->shouldCount($request, $response)) {
            // Après la réponse : le comptage ne doit jamais ralentir la page,
            // ni la faire échouer si l'écriture pose problème.
            try {
                SiteVisit::record(
                    $request->ip() ?? '0.0.0.0',
                    $request->userAgent(),
                    $request->user()?->id,
                );
            } catch (\Throwable) {
                // La fréquentation est une statistique, pas une fonctionnalité :
                // une écriture ratée ne doit pas casser la navigation.
            }
        }

        return $response;
    }

    private function shouldCount(Request $request, Response $response): bool
    {
        if (! $request->isMethod('GET')) {
            return false;
        }

        // Requêtes de fond (sondage des messages, Alpine, fetch) : pas des pages.
        if ($request->ajax() || $request->wantsJson() || $request->isXmlHttpRequest()) {
            return false;
        }

        // Seules les pages effectivement affichées comptent (pas les 404/302).
        if ($response->getStatusCode() !== 200) {
            return false;
        }

        if ($request->is(...self::IGNORED_PREFIXES) || $request->is(...array_map(fn ($p) => $p . '/*', self::IGNORED_PREFIXES))) {
            return false;
        }

        $userAgent = $request->userAgent();

        if (! $userAgent || preg_match(self::BOT_PATTERN, $userAgent)) {
            return false;
        }

        return true;
    }
}
