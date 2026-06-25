<?php

namespace App\Http\Middleware;

use App\Helpers\ApiResponse;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class GraphqlEmptyQueryHandler
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->is('graphql')) {
            return $next($request);
        }

        $hasQuery = $request->query('query')
            || $request->query('queryId')
            || $request->input('query')
            || $request->input('queryId');

        if ($hasQuery) {
            return $next($request);
        }

        if ($request->isMethod('GET')) {
            return ApiResponse::success(
                (object) [],
                'Pesan sukses'
            );
        }

        return $next($request);
    }
}
