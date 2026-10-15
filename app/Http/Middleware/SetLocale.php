<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    public function handle(Request $request, Closure $next): Response
    {
        $supported = ['fr', 'en', 'es'];
        // Web pages keep the explicit choice across navigation and sessions.
        // API requests use their language header instead of a web session.
        $locale = $request->hasSession()
            ? $request->session()->get('locale', $request->cookie('locale', config('app.locale', 'fr')))
            : $request->getPreferredLanguage($supported);

        if (!in_array($locale, $supported, true)) {
            $locale = 'fr';
        }

        App::setLocale($locale);
        \Illuminate\Support\Carbon::setLocale($locale);

        $response = $next($request);
        if (!$request->hasSession()) {
            $response->setVary('Accept-Language', false);
        }

        return $response;
    }
}
