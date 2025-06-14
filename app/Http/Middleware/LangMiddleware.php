<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class LangMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        if($request->session()->has('mylocale') &&
        array_key_exists($request->session()->get('mylocale'), config('app.languages'))){
            app()->setLocale($request->session()->get('mylocale'));
        }
        else
            app()->setLocale(config('app.locale'));

        return $next($request);
    }
}
