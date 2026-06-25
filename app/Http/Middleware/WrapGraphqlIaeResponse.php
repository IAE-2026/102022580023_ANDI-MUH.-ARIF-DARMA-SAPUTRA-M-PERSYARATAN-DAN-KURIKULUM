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
     * Membungkus semua respons GraphQL (GET query & POST mutation) ke format IAE-T2.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        if (! $request->is('graphql') || ! $this->hasGraphqlQuery($request)) {
            return $response;
        }

        $payload = json_decode($response->getContent(), true);

        if (! is_array($payload)) {
            return $response;
        }

        // Sudah format IAE-T2 (cegah double-wrap).
        if (isset($payload['status'], $payload['message'], $payload['meta'])) {
            return $response;
        }

        if (isset($payload['errors'])) {
            return $this->wrapErrors($payload['errors']);
        }

        if (! isset($payload['data']) || ! is_array($payload['data'])) {
            return $response;
        }

        return $this->wrapSuccess($request, $payload['data']);
    }

    private function hasGraphqlQuery(Request $request): bool
    {
        return (bool) (
            $request->query('query')
            || $request->query('queryId')
            || $request->input('query')
            || $request->input('queryId')
        );
    }

    private function wrapErrors(array $errors): Response
    {
        $firstError = $errors[0] ?? [];
        $message = $firstError['message'] ?? 'Terjadi kesalahan pada permintaan GraphQL.';
        $validation = $firstError['extensions']['validation'] ?? null;

        return ApiResponse::error($message, 422, $validation ?? $errors);
    }

    private function wrapSuccess(Request $request, array $data): Response
    {
        if ($this->isCreateNilaiMutation($request)) {
            return $this->wrapCreateNilai($data);
        }

        if (isset($data['serviceStatus']) && is_array($data['serviceStatus'])) {
            return $this->wrapExistingIaePayload($data['serviceStatus']);
        }

        if (count($data) === 1) {
            $operation = array_key_first($data);
            $value = $data[$operation];

            if (is_array($value) && isset($value['status'], $value['meta'])) {
                return $this->wrapExistingIaePayload($value);
            }

            [$message, $code, $meta] = $this->messageForOperation($operation, $value);

            return ApiResponse::success($value, $message, $code, $meta);
        }

        return ApiResponse::success($data, 'Data retrieved successfully', 200);
    }

    private function wrapCreateNilai(array $data): Response
    {
        if (! isset($data['createNilai'])) {
            return ApiResponse::success($data, 'Data retrieved successfully', 200);
        }

        $nilaiData = $data['createNilai'];

        if (is_array($nilaiData) && isset($nilaiData['status'], $nilaiData['meta'])) {
            return $this->wrapExistingIaePayload($nilaiData, 201);
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

    private function wrapExistingIaePayload(array $payload, int $code = 200): Response
    {
        return ApiResponse::success(
            $payload['data'] ?? (object) [],
            $payload['message'] ?? 'Data retrieved successfully',
            $code,
            is_array($payload['meta'] ?? null) ? $payload['meta'] : []
        );
    }

    /**
     * @return array{0: string, 1: int, 2: array<string, mixed>}
     */
    private function messageForOperation(string $operation, mixed $value): array
    {
        $meta = [];

        if (in_array($operation, ['kurikulums', 'nilais'], true) && is_array($value)) {
            $meta['total'] = count($value);
        }

        $message = match ($operation) {
            'kurikulums' => 'Data kurikulum berhasil diambil',
            'kurikulum' => 'Detail kurikulum berhasil diambil',
            'nilais' => 'Data nilai berhasil diambil',
            'nilaiByNim' => 'Data nilai mahasiswa berhasil diambil',
            'serviceStatus' => 'Data retrieved successfully',
            default => 'Data retrieved successfully',
        };

        return [$message, 200, $meta];
    }

    private function isCreateNilaiMutation(Request $request): bool
    {
        $query = $this->getQueryString($request);

        return $query !== '' && (bool) preg_match('/\bcreateNilai\s*\(/', $query);
    }

    private function getQueryString(Request $request): string
    {
        $query = $request->input('query', $request->query('query', ''));

        return is_string($query) ? $query : '';
    }
}
