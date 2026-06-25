<?php

namespace App\OpenApi;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'IaeT2Meta',
    type: 'object',
    properties: [
        new OA\Property(property: 'service_name', type: 'string', example: 'Prasyarat-Kurikulum-Service'),
        new OA\Property(property: 'api_version', type: 'string', example: 'v1'),
    ]
)]
#[OA\Schema(
    schema: 'IaeT2SuccessResponse',
    required: ['status', 'message', 'data'],
    properties: [
        new OA\Property(property: 'status', type: 'string', example: 'success'),
        new OA\Property(property: 'message', type: 'string', example: 'Data retrieved successfully'),
        new OA\Property(property: 'data'),
        new OA\Property(property: 'meta', ref: '#/components/schemas/IaeT2Meta'),
    ]
)]
#[OA\Schema(
    schema: 'IaeT2ErrorResponse',
    required: ['status', 'message'],
    properties: [
        new OA\Property(property: 'status', type: 'string', example: 'error'),
        new OA\Property(property: 'message', type: 'string', example: 'Detail pesan kesalahan'),
        new OA\Property(property: 'errors', nullable: true),
    ]
)]
class IaeT2Schemas
{
}
