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

        // Impose HTTPS pendant un an sur albabor.com.
        //
        // Volontairement SANS `includeSubDomains` ni `preload` : le premier
        // imposerait HTTPS à des sous-domaines qui n'existent pas encore, le
        // second inscrirait le domaine dans une liste intégrée aux navigateurs
        // dont on ne sort qu'après des mois. En l'état, revenir en arrière se
        // fait en passant `max-age` à 0 et en attendant l'expiration côté
        // visiteurs. Les navigateurs ignorent cet en-tête en HTTP simple.
        $response->headers->set('Strict-Transport-Security', 'max-age=31536000');

        return $response;
    }
}
