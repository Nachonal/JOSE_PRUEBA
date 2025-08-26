<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ApiKeyMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        // Lee la clave esperada desde el entorno
        $expected = env('API_KEY');
        $provided = $request->header('X-API-KEY') ?? $request->query('api_key');

        if (!$expected || !$provided || !hash_equals($expected, (string) $provided)) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        return $next($request);
    }
}
