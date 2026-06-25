<?php

namespace App\Http\Controllers;

use OpenApi\Attributes as OA;

#[OA\Info(
    title: "Service C - Prasyarat dan Kurikulum API",
    version: "1.0.0",
    description: "API Service C mengikuti standar IAE-T2 Response Wrapper. Semua endpoint REST /api/v1/* wajib membungkus respons dengan format IAE-T2 (success: status, message, data, meta; error: status, message, errors). Semua request dan response REST wajib menggunakan Content-Type: application/json; charset=UTF-8.",
    contact: new OA\Contact(
        name: "Andi Muh. Arif Darma Saputra M",
        email: "102022580023@student.telkomuniversity.ac.id"
    )
)]
#[OA\Server(
    url: "http://localhost:8000",
    description: "Local Development Server"
)]
#[OA\SecurityScheme(
    securityScheme: "X-IAE-KEY",
    type: "apiKey",
    in: "header",
    name: "X-IAE-KEY",
    description: "Masukkan NIM sebagai API Key (102022580023)"
)]
abstract class Controller
{
    //
}
