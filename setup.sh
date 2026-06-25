#!/usr/bin/env bash
set -euo pipefail

echo "========================================"
echo " Setup Service C — IAE-T2 (102022580023)"
echo "========================================"

if ! command -v docker >/dev/null 2>&1; then
    echo "ERROR: Docker belum terinstall."
    exit 1
fi

if ! docker info >/dev/null 2>&1; then
    echo "ERROR: Docker Desktop belum running."
    exit 1
fi

echo ""
echo "[1/6] composer install..."
composer install --no-interaction

echo ""
echo "[2/6] Siapkan .env..."
if [ ! -f .env ]; then
    cp .env.example .env
    echo "      .env dibuat dari .env.example"
else
    echo "      .env sudah ada, dilewati"
fi

echo ""
echo "[3/6] Hapus container & volume MySQL lama (jika ada)..."
docker compose down -v --remove-orphans 2>/dev/null || true

echo ""
echo "[4/6] docker compose up -d --build --wait..."
docker compose up -d --build --wait

echo ""
echo "[5/6] php artisan key:generate..."
docker compose exec -T laravel.test php artisan key:generate --force

echo ""
echo "[6/6] php artisan migrate:fresh --seed..."
docker compose exec -T laravel.test php artisan migrate:fresh --seed --force

echo ""
echo "========================================"
echo " SETUP SELESAI"
echo "========================================"
echo " Swagger UI  : http://localhost:8000/api/documentation"
echo " GraphiQL    : http://localhost:8000/graphiql"
echo " X-IAE-KEY   : 102022580023"
echo "========================================"
