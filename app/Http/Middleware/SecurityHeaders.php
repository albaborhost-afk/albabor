<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * En-têtes de sécurité manquants sur les réponses.
 *
 * Le serveur envoyait déjà `X-Frame-Options` et `X-Content-Type-Options`,
 * mais rien pour limiter ce qui fuit vers les sites tiers ni ce que la page
 * a le droit de demander au navigateur.
 *
 * Volontairement absent : `Strict-Transport-Security`. Un navigateur retient
 * cet en-tête pendant toute sa durée de validité, donc une erreur ne se
 * corrige pas en redéployant. Il se règle d'un interrupteur côté Cloudflare,
 * qui est déjà devant le site — c'est le bon endroit pour ça.
 */
class SecurityHeaders
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // L'URL complète d'une annonce ne doit pas partir vers un site tiers
        // quand l'utilisateur clique sur un lien sortant ; l'origine suffit.
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');

        // Le site n'utilise ni caméra, ni micro, ni géolocalisation, ni
        // paiement navigateur : autant l'interdire explicitement.
        $response->headers->set(
            'Permissions-Policy',
            'camera=(), microphone=(), geolocation=(), payment=(), usb=(), interest-cohort=()'
        );

        // Bloque les anciens greffons Flash/PDF qui tentent de lire des
        // ressources du domaine via un crossdomain.xml.
        $response->headers->set('X-Permitted-Cross-Domain-Policies', 'none');

        return $response;
    }
}
