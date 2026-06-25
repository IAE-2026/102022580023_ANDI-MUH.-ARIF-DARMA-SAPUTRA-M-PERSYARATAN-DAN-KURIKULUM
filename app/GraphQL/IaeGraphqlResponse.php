<?php

namespace App\GraphQL;

class IaeGraphqlResponse
{
    /**
     * Wrapper respons IAE-T2 untuk resolver GraphQL.
     */
    public static function success(mixed $data, string $message = 'Data retrieved successfully', array $meta = []): array
    {
        return [
            'status' => 'success',
            'message' => $message,
            'data' => $data,
            'meta' => array_merge([
                'service_name' => config('services.iae.service_name', 'Prasyarat-Kurikulum-Service'),
                'api_version' => 'v1',
            ], $meta),
        ];
    }
}
