<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class QueryLimitMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->has('limit')) {
            $limit = $request->query('limit');
            if (!is_numeric($limit) || (int)$limit <= 0) {
                $limit = 10;
            } else {
                $limit = min((int)$limit, 100);
            }
            $request->query->set('limit', $limit);
            $request->merge(['limit' => $limit]);
        }

        if ($request->has('page')) {
            $page = $request->query('page');
            if (!is_numeric($page) || (int)$page <= 0) {
                $page = 1;
            } else {
                $page = (int)$page;
            }
            $request->query->set('page', $page);
            $request->merge(['page' => $page]);
        }

        return $next($request);
    }
}
