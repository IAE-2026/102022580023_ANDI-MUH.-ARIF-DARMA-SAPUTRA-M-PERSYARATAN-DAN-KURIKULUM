<?php

namespace App\GraphQL\Queries;

class ServiceStatus
{
    /**
     * Mengembalikan wrapper respons IAE-T2 untuk health check GraphQL.
     */
    public function __invoke(mixed $_, array $args): array
    {
        return [
            'status' => 'success',
            'message' => 'Pesan sukses',
            'data' => (object) [],
            'meta' => [
                'service_name' => config('services.iae.service_name', 'Prasyarat-Kurikulum-Service'),
                'api_version' => 'v1',
            ],
        ];
    }
}
