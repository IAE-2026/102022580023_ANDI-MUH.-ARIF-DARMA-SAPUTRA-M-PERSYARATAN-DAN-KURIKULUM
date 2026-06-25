# Service C — Validasi Prasyarat & Kurikulum

**Integrasi Aplikasi Enterprise (IAE) — Standar IAE-T2**

| Item | Detail |
|---|---|
| **Nama** | Andi Muh. Arif Darma Saputra M |
| **NIM** | 102022580023 |
| **Layanan** | Service C (Kurikulum & Nilai Mahasiswa) |
| **Port lokal** | `8000` |
| **X-IAE-KEY** | `102022580023` |

---

## Panduan untuk Dosen / Penilai

Ikuti langkah berikut **berurutan** agar service dapat dijalankan dan discan sesuai rubrik IAE-T2.

### Cara cepat (disarankan)

```bash
git clone https://github.com/IAE-2026/102022580023_ANDI-MUH.-ARIF-DARMA-SAPUTRA-M-PERSYARATAN-DAN-KURIKULUM.git
cd 102022580023_ANDI-MUH.-ARIF-DARMA-SAPUTRA-M-PERSYARATAN-DAN-KURIKULUM
./setup.sh
```

Script `setup.sh` otomatis menjalankan: `composer install` → buat `.env` → **reset volume MySQL** → `docker compose up` → `key:generate` → `migrate:fresh --seed`.

### Prasyarat

- [Docker Desktop](https://www.docker.com/products/docker-desktop/) sudah **Running**
- Git terinstall
- Composer terinstall ([getcomposer.org](https://getcomposer.org/))
- Port `8000` tidak dipakai aplikasi lain

### Langkah 1 — Clone repository

```bash
git clone https://github.com/IAE-2026/102022580023_ANDI-MUH.-ARIF-DARMA-SAPUTRA-M-PERSYARATAN-DAN-KURIKULUM.git
cd 102022580023_ANDI-MUH.-ARIF-DARMA-SAPUTRA-M-PERSYARATAN-DAN-KURIKULUM
```

### Langkah 2 — Install dependency PHP

Wajib dijalankan **sebelum** `docker compose up`, karena image Docker membutuhkan folder `vendor/`.

```bash
composer install
```

### Langkah 3 — Siapkan file environment

```bash
cp .env.example .env
```

File `.env.example` sudah dikonfigurasi untuk **MySQL + Docker**. Pastikan bagian berikut ada:

```env
APP_URL=http://localhost:8000
APP_PORT=8000

DB_CONNECTION=mysql
DB_HOST=mysql
DB_PORT=3306
DB_DATABASE=laravel
DB_USERNAME=sail
DB_PASSWORD=password

SERVICE_NAME=Prasyarat-Kurikulum-Service
IAE_API_NIM=102022580023
L5_SWAGGER_GENERATE_ALWAYS=true
L5_SWAGGER_CONST_HOST=http://localhost:8000
```

### Langkah 4 — Jalankan Docker Compose

```bash
docker compose down -v
docker compose up -d --build --wait
```

> **Penting:** `docker compose down -v` wajib dijalankan saat setup pertama kali atau jika pernah menjalankan `docker compose up` sebelum `.env` siap. Tanpa `-v`, volume MySQL lama (dengan password berbeda) akan dipakai ulang dan menyebabkan error `Access denied for user 'sail'`.

### Langkah 5 — Generate APP_KEY & setup database

```bash
docker compose exec laravel.test php artisan key:generate
docker compose exec laravel.test php artisan migrate:fresh --seed
```

> Gunakan `migrate:fresh --seed` (bukan `migrate --seed`) agar tidak terjadi error duplicate data.

### Langkah 6 — Verifikasi service hidup

| Cek | URL | Harus |
|---|---|---|
| Health | http://localhost:8000/up | HTTP 200 |
| Swagger UI | http://localhost:8000/api/documentation | Halaman docs tampil |
| OpenAPI JSON (publik) | http://localhost:8000/docs/openapi.json | JSON valid |
| OpenAPI JSON (Swagger) | http://localhost:8000/api/docs | JSON valid |
| GraphQL Playground | http://localhost:8000/graphiql | UI tampil, bisa jalankan query |
| GraphQL endpoint | http://localhost:8000/graphql | HTTP 200 + JSON `"status":"success"` |

### Langkah 7 — Tes REST API (IAE-T2)

**GET — tanpa key (harus 401):**

```bash
curl -i http://localhost:8000/api/v1/kurikulum
```

**GET — dengan X-IAE-KEY (harus 200 + wrapper IAE-T2):**

```bash
curl -i -H "X-IAE-KEY: 102022580023" http://localhost:8000/api/v1/kurikulum
```

Response sukses wajib memiliki struktur:

```json
{
  "status": "success",
  "message": "...",
  "data": [ ... ],
  "meta": {
    "service_name": "Prasyarat-Kurikulum-Service",
    "api_version": "v1"
  }
}
```

Header response wajib: `Content-Type: application/json; charset=UTF-8`

**POST — tanpa Content-Type (harus 415):**

```bash
curl -i -X POST -H "X-IAE-KEY: 102022580023" http://localhost:8000/api/v1/nilai -d '{}'
```

**POST — dengan Content-Type JSON (harus 201):**

```bash
curl -i -X POST http://localhost:8000/api/v1/nilai \
  -H "X-IAE-KEY: 102022580023" \
  -H "Content-Type: application/json" \
  -d '{
    "nim": "102022580023",
    "kode_matkul": "SI501",
    "nama_matkul": "Keamanan Sistem Informasi",
    "nilai_huruf": "A",
    "nilai_angka": 4,
    "sks": 3,
    "semester": 5,
    "tahun_ajaran": "2025/2026"
  }'
```

### Langkah 8 — Tes via Swagger UI

1. Buka http://localhost:8000/api/documentation
2. Klik **Authorize** → isi `102022580023` pada **X-IAE-KEY**
3. Coba endpoint **GET /api/v1/kurikulum** → Execute → harus **200**
4. Coba **POST /api/v1/nilai** dengan body JSON → harus **201**

### Menstop service

```bash
docker compose down
```

### Alternatif: Laravel Sail

Jika lebih nyaman memakai Sail, langkah setara:

```bash
./vendor/bin/sail up -d --build
./vendor/bin/sail artisan key:generate
./vendor/bin/sail artisan migrate:fresh --seed
```

---

## Checklist Rubrik IAE-T2

| No | Rubrik | Lokasi verifikasi |
|---|---|---|
| 1 | Docker / docker-compose | `docker-compose.yml` + `docker compose ps` |
| 2 | Endpoint `/api/v1/*` | Swagger UI / `openapi.json` |
| 3 | Wrapper IAE-T2 (runtime) | curl GET `/api/v1/kurikulum` + header key |
| 4 | Wrapper IAE-T2 (dokumentasi) | Schemas `IaeT2SuccessResponse` di OpenAPI |
| 5 | Spesifikasi OpenAPI | `/docs/openapi.json` |
| 6 | Konfigurasi Swagger UI | `/api/documentation` + `config/l5-swagger.php` |
| 7 | Schema GraphQL | `graphql/schema.graphql` |
| 8 | GraphQL Playground | `/graphiql` |
| 9 | Header X-IAE-KEY | `102022580023` |
| 10 | Content-Type (runtime) | Header response + POST tanpa JSON → 415 |
| 11 | Content-Type (dokumentasi) | OpenAPI header + `info.description` |

---

## Endpoint REST `/api/v1/*`

| Method | Endpoint | Fungsi |
|---|---|---|
| GET | `/api/v1/kurikulum` | Daftar kurikulum |
| GET | `/api/v1/kurikulum/{kode}` | Detail kurikulum |
| GET | `/api/v1/nilai` | Daftar nilai |
| GET | `/api/v1/nilai/{nim}` | Nilai + IPS per NIM |
| POST | `/api/v1/nilai` | Catat nilai baru |

Semua endpoint di atas wajib header:

```
X-IAE-KEY: 102022580023
Content-Type: application/json   (khusus POST/PUT/PATCH)
```

---

## Standar Response IAE-T2

**Sukses (2xx):**

```json
{
  "status": "success",
  "message": "Pesan sukses",
  "data": {},
  "meta": {
    "service_name": "Prasyarat-Kurikulum-Service",
    "api_version": "v1"
  }
}
```

**Error (4xx/5xx):**

```json
{
  "status": "error",
  "message": "Pesan error",
  "errors": null
}
```

---

## GraphQL

**Playground (UI interaktif):** http://localhost:8000/graphiql

**Endpoint API:** http://localhost:8000/graphql

Akses GET `/graphql` tanpa query akan merespons **HTTP 200** (bukan error). Di browser, otomatis redirect ke `/graphiql`. Untuk menjalankan query, gunakan GraphiQL atau kirim POST JSON.

**Tes endpoint via curl:**

```bash
curl -X POST http://localhost:8000/graphql \
  -H "Content-Type: application/json" \
  -d '{"query":"{ kurikulums { kode_matkul nama_matkul sks } }"}'
```

**Contoh query di GraphiQL:**

```graphql
{
  kurikulums {
    kode_matkul
    nama_matkul
    sks
  }
}
```

```graphql
{
  nilaiByNim(nim: "102022580023") {
    kode_matkul
    nama_matkul
    nilai_huruf
    nilai_angka
  }
}
```

---

## Stack Teknologi

- Laravel 12 (PHP 8.5)
- MySQL 8.4
- Docker Compose (Laravel Sail)
- L5-Swagger (OpenAPI 3.0)
- Lighthouse GraphQL + GraphiQL

---

## Troubleshooting

| Masalah | Solusi |
|---|---|
| `Docker is not running` | Nyalakan Docker Desktop |
| Build gagal / vendor tidak ada | Jalankan `composer install` dulu |
| Error koneksi MySQL | Pastikan `.env` pakai `DB_HOST=mysql`, bukan `127.0.0.1` |
| Duplicate entry saat seed | Pakai `migrate:fresh --seed`, bukan `migrate --seed` |
| `Access denied for user 'sail'` | Volume MySQL masih pakai password lama — lihat solusi di bawah |
| GraphQL error `must include query` | Sudah ditangani — refresh halaman `/graphql` atau buka `/graphiql` |
| GraphQL `Table cache doesn't exist` | Jalankan `./setup.sh` atau `migrate:fresh --seed`; pastikan `.env` pakai `CACHE_STORE=file` |
| `Connection refused` saat migrate | MySQL belum siap — tunggu 30 detik lalu ulangi, atau jalankan `./setup.sh` |
| Swagger "Failed to load API definition" | Pastikan `/api/docs` → HTTP 200, lalu refresh browser |
| Port 8000 sudah dipakai | Ubah `APP_PORT=8001` di `.env`, lalu `docker compose up -d` |

### Error: `Access denied for user 'sail'`

Penyebab umum: volume MySQL Docker (`sail-mysql`) sudah pernah dibuat dengan password **berbeda** dari `.env` saat ini. Ini sering terjadi jika:

- `docker compose up` dijalankan **sebelum** `cp .env.example .env`
- Clone repo ke folder lain tapi **nama folder sama** → Docker Compose pakai volume lama yang sama
- `.env` diubah setelah MySQL sudah pernah jalan

MySQL **hanya** membuat user & password saat volume **pertama kali** dibuat. Mengganti `.env` saja tidak mengubah password di dalam volume.

**Solusi:**

```bash
docker compose down -v
cp .env.example .env
docker compose up -d --build --wait
docker compose exec laravel.test php artisan key:generate
docker compose exec laravel.test php artisan migrate:fresh --seed
```

Atau cukup jalankan ulang:

```bash
./setup.sh
```

---

*Dibuat untuk memenuhi tugas mata kuliah Integrasi Aplikasi Enterprise — Standar IAE-T2.*
