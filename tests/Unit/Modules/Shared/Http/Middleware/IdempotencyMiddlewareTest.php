<?php

declare(strict_types=1);

namespace Tests\Unit\Modules\Shared\Http\Middleware;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Modules\Shared\Http\Middleware\IdempotencyMiddleware;
use Symfony\Component\HttpFoundation\Response;
use Tests\TestCase;

class IdempotencyMiddlewareTest extends TestCase
{
    public function test_it_bypasses_get_requests(): void
    {
        $middleware = new IdempotencyMiddleware;
        $request = Request::create('/test', 'GET');
        $request->headers->set('Idempotency-Key', 'test-key-1');

        $response = clone $middleware->handle($request, function () {
            return new Response('ok', 200);
        });

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertFalse(Cache::has('idempotency:test-key-1'));
    }

    public function test_it_caches_successful_post_requests(): void
    {
        $middleware = new IdempotencyMiddleware;
        $request = Request::create('/test', 'POST');
        $request->headers->set('Idempotency-Key', 'test-key-2');

        $middleware->handle($request, function () {
            return new Response('success', 201);
        });

        $this->assertTrue(Cache::has('idempotency:test-key-2'));
        $cached = Cache::get('idempotency:test-key-2');
        $this->assertEquals(201, $cached['status']);
        $this->assertEquals('success', $cached['content']);
    }

    public function test_it_returns_cached_response(): void
    {
        Cache::put('idempotency:test-key-3', [
            'content' => 'cached-success',
            'status' => 201,
            'headers' => ['X-Cached' => ['true']],
        ], now()->addHour());

        $middleware = new IdempotencyMiddleware;
        $request = Request::create('/test', 'POST');
        $request->headers->set('Idempotency-Key', 'test-key-3');

        /** @var \Illuminate\Http\Response $response */
        $response = $middleware->handle($request, function () {
            return new Response('new-success', 201);
        });

        $this->assertEquals(201, $response->getStatusCode());
        $this->assertEquals('cached-success', $response->getContent());
        $this->assertTrue($response->headers->has('X-Cached'));
    }
}
