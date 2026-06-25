<?php

namespace App\Http\Controllers\Api\V1;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Models\Nilai;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Validator;
use OpenApi\Attributes as OA;

class NilaiController extends Controller
{
    #[OA\Get(
        path: "/api/v1/nilai",
        summary: "Lihat daftar semua nilai",
        description: "Mengambil daftar seluruh nilai mahasiswa. Endpoint ini berfungsi sebagai Collection.",
        operationId: "getNilaiList",
        tags: ["Nilai"],
        security: [["X-IAE-KEY" => []]],
        responses: [
            new OA\Response(
                response: 200,
                description: "Berhasil mengambil daftar nilai",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "status", type: "string", example: "success"),
                        new OA\Property(property: "message", type: "string", example: "Data nilai berhasil diambil"),
                        new OA\Property(
                            property: "data",
                            type: "array",
                            items: new OA\Items(
                                properties: [
                                    new OA\Property(property: "nim", type: "string", example: "102022400136"),
                                    new OA\Property(property: "kode_matkul", type: "string", example: "SI101"),
                                    new OA\Property(property: "nama_matkul", type: "string", example: "Algoritma dan Pemrograman"),
                                    new OA\Property(property: "nilai_huruf", type: "string", example: "A"),
                                    new OA\Property(property: "nilai_angka", type: "number", example: 4.0),
                                    new OA\Property(property: "sks", type: "integer", example: 3),
                                    new OA\Property(property: "semester", type: "integer", example: 1),
                                    new OA\Property(property: "tahun_ajaran", type: "string", example: "2024/2025"),
                                ]
                            )
                        ),
                        new OA\Property(
                            property: "meta",
                            type: "object",
                            properties: [
                                new OA\Property(property: "service_name", type: "string", example: "Prasyarat-Kurikulum-Service"),
                                new OA\Property(property: "api_version", type: "string", example: "v1"),
                                new OA\Property(property: "total", type: "integer", example: 5),
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
        $nilais = Nilai::all();

        return ApiResponse::success(
            $nilais,
            'Data nilai berhasil diambil',
            200,
            ['total' => $nilais->count()]
        );
    }

    #[OA\Get(
        path: "/api/v1/nilai/{nim}",
        summary: "Lihat nilai dan IPS semester lalu berdasarkan NIM",
        description: "Mengambil daftar nilai dan menghitung IPS (Indeks Prestasi Semester) mahasiswa. Dipanggil oleh Service B untuk cek syarat ambil matkul.",
        operationId: "getNilaiByNim",
        tags: ["Nilai"],
        security: [["X-IAE-KEY" => []]],
        parameters: [
            new OA\Parameter(
                name: "nim",
                in: "path",
                required: true,
                description: "NIM Mahasiswa",
                schema: new OA\Schema(type: "string", example: "102022400136")
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: "Berhasil mengambil nilai mahasiswa",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "status", type: "string", example: "success"),
                        new OA\Property(property: "message", type: "string", example: "Data nilai mahasiswa berhasil diambil"),
                        new OA\Property(
                            property: "data",
                            type: "object",
                            properties: [
                                new OA\Property(property: "nim", type: "string", example: "102022400136"),
                                new OA\Property(property: "ips", type: "number", format: "float", example: 3.75),
                                new OA\Property(property: "total_sks", type: "integer", example: 20),
                                new OA\Property(property: "semester_terakhir", type: "integer", example: 2),
                                new OA\Property(
                                    property: "nilai",
                                    type: "array",
                                    items: new OA\Items(
                                        properties: [
                                            new OA\Property(property: "kode_matkul", type: "string", example: "SI101"),
                                            new OA\Property(property: "nama_matkul", type: "string", example: "Algoritma dan Pemrograman"),
                                            new OA\Property(property: "nilai_huruf", type: "string", example: "A"),
                                            new OA\Property(property: "nilai_angka", type: "number", example: 4.0),
                                            new OA\Property(property: "sks", type: "integer", example: 3),
                                            new OA\Property(property: "semester", type: "integer", example: 1),
                                        ]
                                    )
                                ),
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
                description: "Data nilai mahasiswa tidak ditemukan",
                content: new OA\JsonContent(ref: "#/components/schemas/IaeT2ErrorResponse")
            ),
            new OA\Response(
                response: 401,
                description: "Unauthorized - API Key tidak valid",
                content: new OA\JsonContent(ref: "#/components/schemas/IaeT2ErrorResponse")
            ),
        ]
    )]
    public function show(string $nim)
    {
        $nilais = Nilai::where('nim', $nim)->get();

        if ($nilais->isEmpty()) {
            return ApiResponse::error('Data nilai untuk NIM ' . $nim . ' tidak ditemukan', 404);
        }

        // Hitung IPS (Indeks Prestasi Semester) dari semester terakhir
        $semesterTerakhir = $nilais->max('semester');
        $nilaiSemesterAkhir = $nilais->where('semester', $semesterTerakhir);

        $totalBobot = $nilaiSemesterAkhir->sum(function ($n) {
            return $n->nilai_angka * $n->sks;
        });
        $totalSks = $nilaiSemesterAkhir->sum('sks');
        $ips = $totalSks > 0 ? round($totalBobot / $totalSks, 2) : 0;

        $data = [
            'nim' => $nim,
            'ips' => $ips,
            'total_sks' => $nilais->sum('sks'),
            'semester_terakhir' => $semesterTerakhir,
            'nilai' => $nilais,
        ];

        return ApiResponse::success($data, 'Data nilai mahasiswa berhasil diambil');
    }

    #[OA\Post(
        path: "/api/v1/nilai",
        summary: "Catat nilai mahasiswa setelah semester selesai",
        description: "Menambahkan data nilai mahasiswa baru.",
        operationId: "storeNilai",
        tags: ["Nilai"],
        security: [["X-IAE-KEY" => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ["nim", "kode_matkul", "nama_matkul", "nilai_huruf", "nilai_angka", "sks", "semester", "tahun_ajaran"],
                properties: [
                    new OA\Property(property: "nim", type: "string", example: "102022400136"),
                    new OA\Property(property: "kode_matkul", type: "string", example: "SI101"),
                    new OA\Property(property: "nama_matkul", type: "string", example: "Algoritma dan Pemrograman"),
                    new OA\Property(property: "nilai_huruf", type: "string", example: "A", description: "Nilai huruf: A, AB, B, BC, C, D, E"),
                    new OA\Property(property: "nilai_angka", type: "number", example: 4.0, description: "Nilai angka: 4.0, 3.5, 3.0, 2.5, 2.0, 1.0, 0.0"),
                    new OA\Property(property: "sks", type: "integer", example: 3),
                    new OA\Property(property: "semester", type: "integer", example: 1),
                    new OA\Property(property: "tahun_ajaran", type: "string", example: "2024/2025"),
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: "Nilai berhasil dicatat",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "status", type: "string", example: "success"),
                        new OA\Property(property: "message", type: "string", example: "Nilai mahasiswa berhasil dicatat"),
                        new OA\Property(
                            property: "data",
                            type: "object",
                            properties: [
                                new OA\Property(property: "id", type: "integer", example: 11),
                                new OA\Property(property: "nim", type: "string", example: "102022580023"),
                                new OA\Property(property: "kode_matkul", type: "string", example: "SI101"),
                                new OA\Property(property: "nilai_huruf", type: "string", example: "A"),
                                new OA\Property(property: "nilai_angka", type: "number", example: 4.0),
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
                response: 422,
                description: "Validasi gagal",
                content: new OA\JsonContent(ref: "#/components/schemas/IaeT2ErrorResponse")
            ),
            new OA\Response(
                response: 400,
                description: "Mahasiswa tidak aktif atau tidak ditemukan di Service A",
                content: new OA\JsonContent(ref: "#/components/schemas/IaeT2ErrorResponse")
            ),
            new OA\Response(
                response: 401,
                description: "Unauthorized - API Key tidak valid",
                content: new OA\JsonContent(ref: "#/components/schemas/IaeT2ErrorResponse")
            ),
        ]
    )]
    public function store(Request $request)
    {
        // Validasi input
        $validator = Validator::make($request->all(), [
            'nim' => 'required|string',
            'kode_matkul' => 'required|string',
            'nama_matkul' => 'required|string',
            'nilai_huruf' => 'required|string|in:A,AB,B,BC,C,D,E',
            'nilai_angka' => 'required|numeric|min:0|max:4',
            'sks' => 'required|integer|min:1|max:6',
            'semester' => 'required|integer|min:1|max:14',
            'tahun_ajaran' => 'required|string',
        ]);

        if ($validator->fails()) {
            return ApiResponse::error('Validasi gagal', 422, $validator->errors()->toArray());
        }

        // Panggil Service A untuk validasi mahasiswa masih aktif
        // URL Service A (dari teman - sesuaikan dengan URL yang benar)
        $serviceAUrl = env('SERVICE_A_URL', 'http://localhost:8001');

        try {
            $response = Http::withHeaders([
                'X-IAE-KEY' => '102022580023',
            ])->get($serviceAUrl . '/api/v1/mahasiswa/' . $request->nim);

            if ($response->successful()) {
                $mahasiswaData = $response->json();

                // Cek apakah status mahasiswa aktif
                if (isset($mahasiswaData['data']['status']) && strtolower($mahasiswaData['data']['status']) !== 'aktif') {
                    return ApiResponse::error(
                        'Mahasiswa dengan NIM ' . $request->nim . ' tidak berstatus aktif. Status: ' . ($mahasiswaData['data']['status'] ?? 'unknown'),
                        400
                    );
                }
            } else {
                // Jika Service A tidak tersedia, log warning tapi tetap lanjut
                \Log::warning('Service A tidak merespons dengan baik untuk NIM: ' . $request->nim . '. Status: ' . $response->status());
            }
        } catch (\Exception $e) {
            // Jika Service A tidak bisa dihubungi, log error tapi tetap lanjut
            \Log::warning('Tidak dapat menghubungi Service A: ' . $e->getMessage());
        }

        $nilai = Nilai::create([
            ...$request->only([
                'nim',
                'kode_matkul',
                'nama_matkul',
                'nilai_huruf',
                'nilai_angka',
                'sks',
                'semester',
                'tahun_ajaran',
            ]),
            'recorded_by' => config('services.iae.api_nim'),
        ]);

        return ApiResponse::success($nilai, 'Nilai mahasiswa berhasil dicatat', 201);
    }
}
