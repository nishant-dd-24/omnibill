<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Modules\Shared\Domain\Context\CorrelationId;
use Symfony\Component\HttpFoundation\Response;

class CorrelationIdMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Use client provided correlation ID if valid, otherwise generate a new one (UUIDv7)
        $id = $request->header('X-Correlation-ID');
        if (! $id) {
            $id = (string) Str::uuid();
        }

        // Set in context for logging and background jobs
        app(CorrelationId::class)->setId($id);

        $response = $next($request);

        // Always return the correlation ID to the client
        $response->headers->set('X-Correlation-ID', $id);

        return $response;
    }
}
