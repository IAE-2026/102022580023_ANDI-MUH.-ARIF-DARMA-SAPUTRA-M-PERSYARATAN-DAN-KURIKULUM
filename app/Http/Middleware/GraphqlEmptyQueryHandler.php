<?php

namespace App\Http\Middleware;

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
            return response()->json([
                'status' => 'success',
                'message' => 'GraphQL endpoint aktif.',
                'data' => [
                    'endpoint' => url('/graphql'),
                    'playground' => url('/graphiql'),
                    'hint' => 'Kirim POST dengan body {"query":"{ ... }"} atau buka /graphiql.',
                ],
            ], 200, [
                'Content-Type' => 'application/json; charset=UTF-8',
            ]);
        }

        return $next($request);
    }
}
