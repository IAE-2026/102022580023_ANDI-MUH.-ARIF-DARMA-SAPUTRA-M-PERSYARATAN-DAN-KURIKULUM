<?php

namespace App\GraphQL\Mutations;

use App\Models\Nilai;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class CreateNilai
{
    /**
     * Menambahkan data nilai mahasiswa baru (setara POST /api/v1/nilai).
     * Wrapper IAE-T2 ditambahkan otomatis oleh WrapGraphqlIaeResponse middleware.
     */
    public function __invoke(mixed $_, array $args): Nilai
    {
        $input = $args['input'];

        $validator = Validator::make($input, [
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
            throw ValidationException::withMessages($validator->errors()->toArray());
        }

        $validated = $validator->validated();
        $this->validateMahasiswaAktif($validated['nim']);

        return Nilai::create([
            ...$validated,
            'recorded_by' => config('services.iae.api_nim'),
        ]);
    }

    private function validateMahasiswaAktif(string $nim): void
    {
        $serviceAUrl = env('SERVICE_A_URL', 'http://localhost:8001');

        try {
            $response = Http::withHeaders([
                'X-IAE-KEY' => config('services.iae.api_nim', '102022580023'),
            ])->get($serviceAUrl.'/api/v1/mahasiswa/'.$nim);

            if (! $response->successful()) {
                Log::warning('Service A tidak merespons dengan baik untuk NIM: '.$nim.'. Status: '.$response->status());

                return;
            }

            $mahasiswaData = $response->json();
            $status = strtolower($mahasiswaData['data']['status'] ?? '');

            if ($status !== '' && $status !== 'aktif') {
                throw ValidationException::withMessages([
                    'input.nim' => [
                        'Mahasiswa dengan NIM '.$nim.' tidak berstatus aktif. Status: '.($mahasiswaData['data']['status'] ?? 'unknown'),
                    ],
                ]);
            }
        } catch (ValidationException $e) {
            throw $e;
        } catch (\Exception $e) {
            Log::warning('Tidak dapat menghubungi Service A: '.$e->getMessage());
        }
    }
}
