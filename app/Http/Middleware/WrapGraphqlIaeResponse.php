<?php

namespace App\Http\Middleware;

use App\Helpers\ApiResponse;
use App\Models\Nilai;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class WrapGraphqlIaeResponse
{
    /**
     * Membungkus respons GraphQL mutation createNilai ke format IAE-T2 otomatis.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        if (! $request->is('graphql') || ! in_array($request->method(), ['POST', 'PUT', 'PATCH'], true)) {
            return $response;
        }

        if (! $this->isCreateNilaiMutation($request)) {
            return $response;
        }

        $payload = json_decode($response->getContent(), true);

        if (! is_array($payload)) {
            return $response;
        }

        if (isset($payload['errors'])) {
            $firstError = $payload['errors'][0] ?? [];
            $message = $firstError['message'] ?? 'Terjadi kesalahan pada permintaan GraphQL.';
            $validation = $firstError['extensions']['validation'] ?? null;

            return ApiResponse::error($message, 422, $validation ?? $payload['errors']);
        }

        if (! isset($payload['data']['createNilai'])) {
            return $response;
        }

        $nilaiData = $payload['data']['createNilai'];

        if (is_array($nilaiData) && isset($nilaiData['status'], $nilaiData['meta'])) {
            return ApiResponse::success(
                $nilaiData['data'] ?? $nilaiData,
                $nilaiData['message'] ?? 'Nilai mahasiswa berhasil dicatat',
                201,
                is_array($nilaiData['meta'] ?? null) ? $nilaiData['meta'] : []
            );
        }

        if (is_array($nilaiData) && isset($nilaiData['id'])) {
            $nilai = Nilai::find($nilaiData['id']);
            if ($nilai) {
                $nilaiData = $nilai->toArray();
            }
        }

        return ApiResponse::success(
            $nilaiData,
            'Nilai mahasiswa berhasil dicatat',
            201
        );
    }

    private function isCreateNilaiMutation(Request $request): bool
    {
        $query = $request->input('query', '');

        if (! is_string($query) || $query === '') {
            return false;
        }

        return (bool) preg_match('/\bcreateNilai\s*\(/', $query);
    }
}
