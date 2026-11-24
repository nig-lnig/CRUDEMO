<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Les endpoints de démonstration, l'inspection SQL et la réinitialisation
 * n'existent qu'en environnement local (ou de test).
 */
class EnsureLocalEnvironment
{
    public function handle(Request $request, Closure $next): Response
    {
        abort_unless(app()->environment('local', 'testing'), 404);

        return $next($request);
    }
}
