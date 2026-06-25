#!/usr/bin/env bash
set -euo pipefail

echo "========================================"
echo " Setup Service C — IAE-T2 (102022580023)"
echo "========================================"

wait_for_mysql() {
    echo "      Menunggu MySQL siap menerima koneksi..."
    local max_attempts=30
    local attempt=1
    local db_password="${DB_PASSWORD:-password}"

    while [ "$attempt" -le "$max_attempts" ]; do
        if docker compose exec -T mysql mysqladmin ping -h localhost -p"${db_password}" --silent >/dev/null 2>&1 \
            && docker compose exec -T laravel.test php artisan tinker --execute="DB::connection()->getPdo();" >/dev/null 2>&1; then
            echo "      MySQL siap (percobaan ${attempt}/${max_attempts})."
            return 0
        fi

        echo "      MySQL belum siap, coba lagi (${attempt}/${max_attempts})..."
        sleep 2
        attempt=$((attempt + 1))
    done

    echo "ERROR: MySQL tidak merespons setelah ${max_attempts} percobaan."
    echo "       Coba jalankan: docker compose ps"
    exit 1
}

if ! command -v docker >/dev/null 2>&1; then
    echo "ERROR: Docker belum terinstall."
    exit 1
fi

if ! docker info >/dev/null 2>&1; then
    echo "ERROR: Docker Desktop belum running."
    exit 1
fi

echo ""
echo "[1/7] composer install..."
composer install --no-interaction

echo ""
echo "[2/7] Siapkan .env..."
if [ ! -f .env ]; then
    cp .env.example .env
    echo "      .env dibuat dari .env.example"
else
    echo "      .env sudah ada, dilewati"
fi

echo "      Menyesuaikan cache/session agar tidak butuh tabel database..."
for pair in "CACHE_STORE=file" "SESSION_DRIVER=file" "QUEUE_CONNECTION=sync"; do
    key="${pair%%=*}"
    val="${pair#*=}"
    if grep -q "^${key}=" .env; then
        sed -i.bak "s/^${key}=.*/${key}=${val}/" .env
    else
        echo "${key}=${val}" >> .env
    fi
done
rm -f .env.bak

echo ""
echo "[3/7] Hapus container & volume MySQL lama (jika ada)..."
docker compose down -v --remove-orphans 2>/dev/null || true

echo ""
echo "[4/7] docker compose up -d --build..."
docker compose up -d --build

if docker compose up --help 2>/dev/null | grep -q '\-\-wait'; then
    docker compose up -d --wait 2>/dev/null || true
fi

echo ""
echo "[5/7] Tunggu MySQL..."
wait_for_mysql

echo ""
echo "[6/7] php artisan key:generate..."
docker compose exec -T laravel.test php artisan key:generate --force
docker compose exec -T laravel.test php artisan config:clear

echo ""
echo "[7/7] php artisan migrate:fresh --seed..."
docker compose exec -T laravel.test php artisan migrate:fresh --seed --force

echo ""
echo "========================================"
echo " SETUP SELESAI"
echo "========================================"
echo " Swagger UI  : http://localhost:8000/api/documentation"
echo " GraphiQL    : http://localhost:8000/graphiql"
echo " X-IAE-KEY   : 102022580023"
echo "========================================"
