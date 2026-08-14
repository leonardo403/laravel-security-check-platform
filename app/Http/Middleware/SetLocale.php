<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Date;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    public const LOCALES = ['pt_BR', 'es', 'en'];

    public function handle(Request $request, Closure $next): Response
    {
        $locale = $request->session()->get('locale');

        if (! in_array($locale, self::LOCALES, true)) {
            $locale = $request->cookie('locale');
        }

        if (! in_array($locale, self::LOCALES, true)) {
            $locale = config('app.locale');
        }

        app()->setLocale($locale);
        Date::setLocale($locale);

        return $next($request);
    }
}
