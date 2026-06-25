<?php

namespace App\Http\Middleware;

use App\Helpers\ApiResponse;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ForceJsonResponse
{
    /**
     * Memastikan request/response API memakai JSON (IAE-T2: Content-Type application/json).
     */
    public function handle(Request $request, Closure $next): Response
    {
        $request->headers->set('Accept', 'application/json');

        if (in_array($request->method(), ['POST', 'PUT', 'PATCH'], true)) {
            $contentType = (string) $request->header('Content-Type', '');

            if ($contentType === '' || ! str_contains(strtolower($contentType), 'application/json')) {
                return ApiResponse::error(
                    'Content-Type harus application/json.',
                    415
                );
            }
        }

        $response = $next($request);
        $response->headers->set('Content-Type', 'application/json; charset=UTF-8');

        return $response;
    }
}
