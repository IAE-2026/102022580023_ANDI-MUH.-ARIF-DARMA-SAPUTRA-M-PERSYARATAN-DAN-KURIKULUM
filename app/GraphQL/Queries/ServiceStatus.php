<?php

namespace App\GraphQL\Queries;

use App\GraphQL\IaeGraphqlResponse;

class ServiceStatus
{
    /**
     * Mengembalikan wrapper respons IAE-T2 untuk health check GraphQL.
     */
    public function __invoke(mixed $_, array $args): array
    {
        return IaeGraphqlResponse::success((object) [], 'Pesan sukses');
    }
}
