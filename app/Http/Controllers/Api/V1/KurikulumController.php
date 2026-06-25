<?php

namespace App\Http\Controllers\Api\V1;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Models\Kurikulum;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

class KurikulumController extends Controller
{
    #[OA\Get(
        path: "/api/v1/kurikulum",
        summary: "Lihat daftar semua kurikulum",
        description: "Mengambil daftar seluruh mata kuliah dalam kurikulum. Response mengikuti IAE-T2 Success Response Wrapper.",
        operationId: "getKurikulumList",
        tags: ["Kurikulum"],
        security: [["X-IAE-KEY" => []]],
        responses: [
            new OA\Response(
                response: 200,
                description: "IAE-T2 Success Response Wrapper",
                headers: [
                    new OA\Header(
                        header: "Content-Type",
                        description: "Content-Type: application/json; charset=UTF-8",
                        schema: new OA\Schema(type: "string", example: "application/json; charset=UTF-8")
                    ),
                ],
                content: new OA\JsonContent(ref: "#/components/schemas/IaeT2SuccessResponse")
            ),
            new OA\Response(
                response: 401,
                description: "IAE-T2 Error Response Wrapper",
                headers: [
                    new OA\Header(
                        header: "Content-Type",
                        description: "Content-Type: application/json; charset=UTF-8",
                        schema: new OA\Schema(type: "string", example: "application/json; charset=UTF-8")
                    ),
                ],
                content: new OA\JsonContent(ref: "#/components/schemas/IaeT2ErrorResponse")
            ),
        ]
    )]
    public function index()
    {
        $kurikulums = Kurikulum::all();

        return ApiResponse::success(
            $kurikulums,
            'Data kurikulum berhasil diambil',
            200,
            ['total' => $kurikulums->count()]
        );
    }

    #[OA\Get(
        path: "/api/v1/kurikulum/{kode}",
        summary: "Lihat detail kurikulum berdasarkan kode matkul",
        description: "Mengambil detail mata kuliah spesifik berdasarkan kode matkul. Response mengikuti IAE-T2 Success Response Wrapper.",
        operationId: "getKurikulumByKode",
        tags: ["Kurikulum"],
        security: [["X-IAE-KEY" => []]],
        parameters: [
            new OA\Parameter(
                name: "kode",
                in: "path",
                required: true,
                description: "Kode mata kuliah",
                schema: new OA\Schema(type: "string", example: "SI101")
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: "IAE-T2 Success Response Wrapper",
                headers: [
                    new OA\Header(
                        header: "Content-Type",
                        description: "Content-Type: application/json; charset=UTF-8",
                        schema: new OA\Schema(type: "string", example: "application/json; charset=UTF-8")
                    ),
                ],
                content: new OA\JsonContent(ref: "#/components/schemas/IaeT2SuccessResponse")
            ),
            new OA\Response(
                response: 404,
                description: "IAE-T2 Error Response Wrapper",
                headers: [
                    new OA\Header(
                        header: "Content-Type",
                        description: "Content-Type: application/json; charset=UTF-8",
                        schema: new OA\Schema(type: "string", example: "application/json; charset=UTF-8")
                    ),
                ],
                content: new OA\JsonContent(ref: "#/components/schemas/IaeT2ErrorResponse")
            ),
            new OA\Response(
                response: 401,
                description: "IAE-T2 Error Response Wrapper",
                headers: [
                    new OA\Header(
                        header: "Content-Type",
                        description: "Content-Type: application/json; charset=UTF-8",
                        schema: new OA\Schema(type: "string", example: "application/json; charset=UTF-8")
                    ),
                ],
                content: new OA\JsonContent(ref: "#/components/schemas/IaeT2ErrorResponse")
            ),
        ]
    )]
    public function show(string $kode)
    {
        $kurikulum = Kurikulum::where('kode_matkul', $kode)->first();

        if (!$kurikulum) {
            return ApiResponse::error('Kurikulum dengan kode ' . $kode . ' tidak ditemukan', 404);
        }

        return ApiResponse::success($kurikulum, 'Detail kurikulum berhasil diambil');
    }
}
