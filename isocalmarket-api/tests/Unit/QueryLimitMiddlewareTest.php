<?php

namespace Tests\Unit;

use App\Http\Middleware\QueryLimitMiddleware;
use Illuminate\Http\Request;
use Tests\TestCase;

class QueryLimitMiddlewareTest extends TestCase
{
    public function test_it_caps_limit_to_maximum_of_50(): void
    {
        $middleware = new QueryLimitMiddleware();
        $request = Request::create('/test', 'GET', ['limit' => 100]);

        $middleware->handle($request, function ($req) {
            $this->assertEquals(50, $req->query('limit'));
            $this->assertEquals(50, $req->input('limit'));
            return response('ok');
        });
    }

    public function test_it_defaults_limit_on_negative_value(): void
    {
        $middleware = new QueryLimitMiddleware();
        $request = Request::create('/test', 'GET', ['limit' => -10]);

        $middleware->handle($request, function ($req) {
            $this->assertEquals(10, $req->query('limit'));
            $this->assertEquals(10, $req->input('limit'));
            return response('ok');
        });
    }

    public function test_it_defaults_limit_on_non_numeric_value(): void
    {
        $middleware = new QueryLimitMiddleware();
        $request = Request::create('/test', 'GET', ['limit' => 'abc']);

        $middleware->handle($request, function ($req) {
            $this->assertEquals(10, $req->query('limit'));
            return response('ok');
        });
    }

    public function test_it_defaults_page_on_negative_value(): void
    {
        $middleware = new QueryLimitMiddleware();
        $request = Request::create('/test', 'GET', ['page' => -5]);

        $middleware->handle($request, function ($req) {
            $this->assertEquals(1, $req->query('page'));
            return response('ok');
        });
    }

    public function test_it_defaults_page_on_non_numeric_value(): void
    {
        $middleware = new QueryLimitMiddleware();
        $request = Request::create('/test', 'GET', ['page' => 'xyz']);

        $middleware->handle($request, function ($req) {
            $this->assertEquals(1, $req->query('page'));
            return response('ok');
        });
    }
}
