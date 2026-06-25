<?php

namespace App\Http\Middleware;

use App\Helpers\ApiResponse;
use App\Models\Role;
use App\Models\User;
use App\Services\JwksService;
use Closure;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class VerifyJwtSso
{
    public function __construct(
        private readonly JwksService $jwksService
    ) {}

    public function handle(Request $request, Closure $next): Response
    {
        $authorization = $request->header('Authorization');

        if (! $authorization || ! str_starts_with($authorization, 'Bearer ')) {
            return ApiResponse::error(
                'Unauthorized. Bearer JWT dari IAE SSO diperlukan.',
                401
            );
        }

        $jwt = trim(substr($authorization, 7));

        try {
            $keySet = $this->jwksService->getKeySet();
            $decoded = JWT::decode($jwt, $keySet);
            $payload = (array) $decoded;
        } catch (\Throwable $exception) {
            return ApiResponse::error(
                'JWT tidak valid: '.$exception->getMessage(),
                401
            );
        }

        $email = $payload['sub'] ?? null;

        if (! $email) {
            return ApiResponse::error(
                'JWT tidak memiliki klaim sub (email).',
                401
            );
        }

        $profile = isset($payload['profile']) ? (array) $payload['profile'] : [];
        $roleName = $this->resolveRoleName($email);
        $role = Role::query()->firstOrCreate(['name' => $roleName]);

        $user = User::query()->updateOrCreate(
            ['email' => $email],
            [
                'name' => $profile['name'] ?? $email,
                'password' => bcrypt(str()->random(32)),
                'role_id' => $role->id,
                'sso_sub' => $email,
            ]
        );

        $allowedRoles = config('services.iae.allowed_nilai_roles', ['dosen', 'admin']);

        if (! in_array($user->role?->name, $allowedRoles, true)) {
            return ApiResponse::error(
                'Forbidden. Role '.$user->role?->name.' tidak diizinkan mencatat nilai.',
                403
            );
        }

        $request->attributes->set('iae_user', $user);
        $request->attributes->set('iae_jwt_payload', $payload);

        return $next($request);
    }

    private function resolveRoleName(string $email): string
    {
        $roleMap = config('services.iae.role_map', []);

        if (isset($roleMap[$email])) {
            return $roleMap[$email];
        }

        if (str_contains($email, 'dosen')) {
            return 'dosen';
        }

        return config('services.iae.default_role', 'mahasiswa');
    }
}
