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
        description: "Mengambil daftar seluruh mata kuliah dalam kurikulum. Endpoint ini berfungsi sebagai Collection.",
        operationId: "getKurikulumList",
        tags: ["Kurikulum"],
        security: [["X-IAE-KEY" => []]],
        responses: [
            new OA\Response(
                response: 200,
                description: "Berhasil mengambil daftar kurikulum",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "status", type: "string", example: "success"),
                        new OA\Property(property: "message", type: "string", example: "Data kurikulum berhasil diambil"),
                        new OA\Property(
                            property: "data",
                            type: "array",
                            items: new OA\Items(
                                properties: [
                                    new OA\Property(property: "id", type: "integer", example: 1),
                                    new OA\Property(property: "kode_matkul", type: "string", example: "SI101"),
                                    new OA\Property(property: "nama_matkul", type: "string", example: "Algoritma dan Pemrograman"),
                                    new OA\Property(property: "sks", type: "integer", example: 3),
                                    new OA\Property(property: "semester", type: "integer", example: 1),
                                    new OA\Property(property: "prodi", type: "string", example: "S1 Sistem Informasi"),
                                    new OA\Property(property: "prasyarat", type: "string", nullable: true, example: null),
                                    new OA\Property(property: "deskripsi", type: "string", nullable: true, example: "Mata kuliah dasar pemrograman"),
                                ]
                            )
                        ),
                        new OA\Property(
                            property: "meta",
                            type: "object",
                            properties: [
                                new OA\Property(property: "service_name", type: "string", example: "Prasyarat-Kurikulum-Service"),
                                new OA\Property(property: "api_version", type: "string", example: "v1"),
                                new OA\Property(property: "total", type: "integer", example: 10),
                            ]
                        ),
                    ]
                )
            ),
            new OA\Response(
                response: 401,
                description: "Unauthorized - API Key tidak valid",
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
        description: "Mengambil detail mata kuliah spesifik berdasarkan kode matkul. Dipanggil oleh Service B untuk validasi matkul.",
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
                description: "Berhasil mengambil detail kurikulum",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "status", type: "string", example: "success"),
                        new OA\Property(property: "message", type: "string", example: "Detail kurikulum berhasil diambil"),
                        new OA\Property(
                            property: "data",
                            type: "object",
                            properties: [
                                new OA\Property(property: "id", type: "integer", example: 1),
                                new OA\Property(property: "kode_matkul", type: "string", example: "SI101"),
                                new OA\Property(property: "nama_matkul", type: "string", example: "Algoritma dan Pemrograman"),
                                new OA\Property(property: "sks", type: "integer", example: 3),
                                new OA\Property(property: "semester", type: "integer", example: 1),
                                new OA\Property(property: "prodi", type: "string", example: "S1 Sistem Informasi"),
                                new OA\Property(property: "prasyarat", type: "string", nullable: true, example: null),
                                new OA\Property(property: "deskripsi", type: "string", nullable: true, example: "Mata kuliah dasar pemrograman"),
                            ]
                        ),
                        new OA\Property(
                            property: "meta",
                            ref: "#/components/schemas/IaeT2Meta"
                        ),
                    ]
                )
            ),
            new OA\Response(
                response: 404,
                description: "Kurikulum tidak ditemukan",
                content: new OA\JsonContent(ref: "#/components/schemas/IaeT2ErrorResponse")
            ),
            new OA\Response(
                response: 401,
                description: "Unauthorized - API Key tidak valid",
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
