<?php

namespace App\Http\Middleware;

use App\Helpers\ApiResponse;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckIaeKey
{
    /**
     * Memvalidasi header X-IAE-KEY pada setiap request API.
     * Value yang valid: NIM mahasiswa (102022580023).
     */
    public function handle(Request $request, Closure $next): Response
    {
        $expectedKey = (string) config('services.iae.api_nim', '102022580023');
        $providedKey = (string) $request->header('X-IAE-KEY', '');

        if ($providedKey === '' || ! hash_equals($expectedKey, $providedKey)) {
            return ApiResponse::error(
                'Unauthorized. Header X-IAE-KEY tidak valid atau tidak ditemukan.',
                401
            );
        }

        return $next($request);
    }
}
