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

### Prasyarat

- [Docker Desktop](https://www.docker.com/products/docker-desktop/) sudah **Running**
- Git terinstall
- Composer terinstall ([getcomposer.org](https://getcomposer.org/))
- Port `8000` tidak dipakai aplikasi lain

---

### Cara cepat (disarankan)

```bash
git clone https://github.com/IAE-2026/102022580023_ANDI-MUH.-ARIF-DARMA-SAPUTRA-M-PERSYARATAN-DAN-KURIKULUM.git
cd 102022580023_ANDI-MUH.-ARIF-DARMA-SAPUTRA-M-PERSYARATAN-DAN-KURIKULUM
chmod +x setup.sh
./setup.sh
```

Script `setup.sh` menjalankan 7 langkah otomatis:

| Langkah | Perintah |
|---|---|
| 1 | `composer install` |
| 2 | Buat/sesuaikan `.env` (MySQL + `CACHE_STORE=file`) |
| 3 | `docker compose down -v` — reset volume MySQL lama |
| 4 | `docker compose up -d --build` |
| 5 | Tunggu MySQL siap (retry hingga 60 detik) |
| 6 | `php artisan key:generate` + `config:clear` |
| 7 | `php artisan migrate:fresh --seed` |

Setelah selesai, buka:

- Swagger UI → http://localhost:8000/api/documentation
- GraphiQL → http://localhost:8000/graphiql *(tempel syntax GraphQL dari bagian bawah README)*

---

### Cara manual (langkah demi langkah)

#### Langkah 1 — Clone repository

```bash
git clone https://github.com/IAE-2026/102022580023_ANDI-MUH.-ARIF-DARMA-SAPUTRA-M-PERSYARATAN-DAN-KURIKULUM.git
cd 102022580023_ANDI-MUH.-ARIF-DARMA-SAPUTRA-M-PERSYARATAN-DAN-KURIKULUM
```

#### Langkah 2 — Install dependency PHP

Wajib **sebelum** `docker compose up`, karena image Docker membutuhkan folder `vendor/`.

```bash
composer install
```

#### Langkah 3 — Siapkan file environment

```bash
cp .env.example .env
```

Pastikan konfigurasi berikut ada di `.env`:

```env
APP_URL=http://localhost:8000
APP_PORT=8000

DB_CONNECTION=mysql
DB_HOST=mysql
DB_PORT=3306
DB_DATABASE=laravel
DB_USERNAME=sail
DB_PASSWORD=password

CACHE_STORE=file
SESSION_DRIVER=file
QUEUE_CONNECTION=sync

SERVICE_NAME=Prasyarat-Kurikulum-Service
IAE_API_NIM=102022580023
L5_SWAGGER_GENERATE_ALWAYS=true
L5_SWAGGER_CONST_HOST=http://localhost:8000
```

> `CACHE_STORE=file` mencegah error GraphQL `Table cache doesn't exist` sebelum migrate selesai.

#### Langkah 4 — Jalankan Docker Compose

```bash
docker compose down -v
docker compose up -d --build
```

Tunggu MySQL siap (~30 detik), lalu cek:

```bash
docker compose ps
```

> **Penting:** `docker compose down -v` wajib saat setup pertama kali atau jika pernah menjalankan `docker compose up` **sebelum** `.env` siap. Tanpa `-v`, volume MySQL lama (password berbeda) akan dipakai ulang → error `Access denied for user 'sail'`.

#### Langkah 5 — Generate APP_KEY & setup database

```bash
docker compose exec laravel.test php artisan key:generate
docker compose exec laravel.test php artisan config:clear
docker compose exec laravel.test php artisan migrate:fresh --seed
```

> Gunakan `migrate:fresh --seed` (bukan `migrate --seed`) agar tidak terjadi error duplicate data.

#### Langkah 6 — Verifikasi service hidup

| Cek | URL | Harus |
|---|---|---|
| Health | http://localhost:8000/up | HTTP 200 |
| Swagger UI | http://localhost:8000/api/documentation | Halaman docs tampil |
| OpenAPI JSON (publik) | http://localhost:8000/docs/openapi.json | JSON valid |
| OpenAPI JSON (Swagger) | http://localhost:8000/api/docs | JSON valid |
| GraphQL Playground | http://localhost:8000/graphiql | UI tampil, bisa jalankan query |
| GraphQL endpoint | http://localhost:8000/graphql | HTTP 200 + JSON `"status":"success"` |

#### Langkah 7 — Tes REST API (IAE-T2)

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

#### Langkah 8 — Tes GraphQL (GraphiQL)

1. Buka **http://localhost:8000/graphiql**
2. **Salin** salah satu syntax di bawah → **tempel** di panel kiri
3. Klik tombol **▶ Execute** (atau `Ctrl+Enter` / `Cmd+Enter`)
4. Lihat hasil di panel kanan

> **Mutation `createNilai`:** cukup tempel syntax input nilai. Respons otomatis format IAE-T2 (`status`, `message`, `data`, `meta`) — **tidak perlu** menulis `{ id }` atau field wrapper.

**1) Health check GraphQL (wrapper IAE-T2):**

```graphql
{
  serviceStatus {
    status
    message
    meta {
      service_name
      api_version
    }
  }
}
```

**2) Daftar kurikulum:**

```graphql
{
  kurikulums {
    kode_matkul
    nama_matkul
    sks
    semester
    prodi
  }
}
```

**3) Detail kurikulum by kode:**

```graphql
{
  kurikulum(kode_matkul: "SI501") {
    kode_matkul
    nama_matkul
    sks
    prasyarat
    deskripsi
  }
}
```

**4) Nilai by NIM:**

```graphql
{
  nilaiByNim(nim: "102022580023") {
    kode_matkul
    nama_matkul
    nilai_huruf
    nilai_angka
    sks
    semester
    tahun_ajaran
  }
}
```

**5) Input nilai baru (mutation — copy paste langsung):**

```graphql
mutation InputNilai {
  createNilai(
    input: {
      nim: "102022580023"
      kode_matkul: "SI501"
      nama_matkul: "Keamanan Sistem Informasi"
      nilai_huruf: "A"
      nilai_angka: 4
      sks: 3
      semester: 5
      tahun_ajaran: "2025/2026"
    }
  )
}
```

Respons sukses otomatis (format IAE-T2):

```json
{
  "status": "success",
  "message": "Nilai mahasiswa berhasil dicatat",
  "data": {
    "nim": "102022580023",
    "kode_matkul": "SI501",
    "nama_matkul": "Keamanan Sistem Informasi",
    "nilai_huruf": "A",
    "nilai_angka": 4,
    "sks": 3,
    "semester": 5,
    "tahun_ajaran": "2025/2026"
  },
  "meta": {
    "service_name": "Prasyarat-Kurikulum-Service",
    "api_version": "v1"
  }
}
```

**Tes via curl (opsional):**

```bash
curl -i http://localhost:8000/graphql
```

```bash
curl -X POST http://localhost:8000/graphql \
  -H "Content-Type: application/json" \
  -d '{"query":"{ kurikulums { kode_matkul nama_matkul sks } }"}'
```

#### Langkah 9 — Tes via Swagger UI

1. Buka http://localhost:8000/api/documentation
2. Klik **Authorize** → isi `102022580023` pada **X-IAE-KEY**
3. Coba **GET /api/v1/kurikulum** → Execute → harus **200**
4. Coba **POST /api/v1/nilai** dengan body JSON → harus **201**

#### Menstop service

```bash
docker compose down
```

Hapus sekalian volume MySQL (reset database):

```bash
docker compose down -v
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

## GraphQL — Panduan GraphiQL untuk Dosen

**Playground:** http://localhost:8000/graphiql

### Cara pakai (3 langkah)

1. Buka link GraphiQL di atas
2. Hapus isi panel kiri, **tempel** syntax dari daftar di bawah
3. Klik **▶ Execute**

| URL | Fungsi |
|---|---|
| `/graphiql` | UI interaktif — tempel syntax di sini |
| `/graphql` | Endpoint API (health check GET → HTTP 200) |

---

### Syntax siap tempel

#### A. Health check service (IAE-T2)

```graphql
{
  serviceStatus {
    status
    message
    meta {
      service_name
      api_version
    }
  }
}
```

#### B. Lihat semua kurikulum

```graphql
{
  kurikulums {
    kode_matkul
    nama_matkul
    sks
    semester
    prodi
  }
}
```

#### C. Lihat detail satu mata kuliah

Ganti `"SI501"` dengan kode matkul lain jika perlu.

```graphql
{
  kurikulum(kode_matkul: "SI501") {
    kode_matkul
    nama_matkul
    sks
    semester
    prasyarat
    deskripsi
  }
}
```

#### D. Lihat nilai mahasiswa by NIM

Ganti NIM jika perlu.

```graphql
{
  nilaiByNim(nim: "102022580023") {
    kode_matkul
    nama_matkul
    nilai_huruf
    nilai_angka
    sks
    semester
    tahun_ajaran
  }
}
```

#### E. Input nilai baru ⭐ (mutation)

**Tempel langsung** — tidak perlu `{ id }`, tidak perlu tulis `status` / `meta`:

```graphql
mutation InputNilai {
  createNilai(
    input: {
      nim: "102022580023"
      kode_matkul: "SI501"
      nama_matkul: "Keamanan Sistem Informasi"
      nilai_huruf: "A"
      nilai_angka: 4
      sks: 3
      semester: 5
      tahun_ajaran: "2025/2026"
    }
  )
}
```

| Field input | Aturan |
|---|---|
| `nilai_huruf` | `A`, `AB`, `B`, `BC`, `C`, `D`, `E` |
| `nilai_angka` | 0 – 4 |
| `sks` | 1 – 6 |
| `semester` | 1 – 14 |
| `kode_matkul` | Harus ada di data kurikulum (mis. `SI501`) |

Respons HTTP otomatis format IAE-T2:

```json
{
  "status": "success",
  "message": "Nilai mahasiswa berhasil dicatat",
  "data": { "..." },
  "meta": {
    "service_name": "Prasyarat-Kurikulum-Service",
    "api_version": "v1"
  }
}
```

> Jika NIM + kode matkul sudah pernah diinput, bisa muncul error duplicate. Ganti NIM atau `kode_matkul` untuk tes ulang.

---

### GET `/graphql` (tanpa query)

Buka di browser atau curl — harus HTTP 200:

```bash
curl -i http://localhost:8000/graphql
```

---

## Stack Teknologi

- Laravel 12 (PHP 8.5)
- MySQL 8.4
- Docker Compose (Laravel Sail) — project name: `iae-kurikulum-102022580023`
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
| `Access denied for user 'sail'` | Jalankan `./setup.sh` atau lihat solusi di bawah |
| `Connection refused` saat migrate | MySQL belum siap — jalankan `./setup.sh` (sudah ada wait otomatis) |
| GraphQL `Table cache doesn't exist` | Pastikan `.env` pakai `CACHE_STORE=file`, lalu `config:clear` |
| GraphQL error `must include query` | Normal jika versi lama — update repo & refresh `/graphql` |
| Swagger "Failed to load API definition" | Pastikan `/api/docs` → HTTP 200, lalu refresh browser |
| Port 8000 sudah dipakai | Ubah `APP_PORT=8001` di `.env`, lalu `docker compose up -d` |

### Error: `Access denied for user 'sail'`

Volume MySQL Docker hanya membuat user & password saat **pertama kali** dibuat. Jika `.env` diubah setelah MySQL pernah jalan, password di volume tidak ikut berubah.

Penyebab umum:

- `docker compose up` dijalankan sebelum `cp .env.example .env`
- Clone ke folder lain dengan **nama folder sama** → volume lama terpakai ulang

**Solusi tercepat:**

```bash
./setup.sh
```

**Solusi manual:**

```bash
docker compose down -v
cp .env.example .env
docker compose up -d --build
# tunggu ~30 detik
docker compose exec laravel.test php artisan key:generate
docker compose exec laravel.test php artisan config:clear
docker compose exec laravel.test php artisan migrate:fresh --seed
```

---

*Dibuat untuk memenuhi tugas mata kuliah Integrasi Aplikasi Enterprise — Standar IAE-T2.*
