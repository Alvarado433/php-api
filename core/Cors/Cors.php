<?php

namespace Core\Cors;

class Cors
{
    public static function handle(
        array $allowedOrigins,
        bool $allowCredentials = true,
        string $methods = 'GET, POST, PUT, DELETE, OPTIONS',
        string $headers = 'Content-Type, Authorization, X-Requested-With, Accept, Origin',
        int $maxAge = 86400
    ): void {
        $origin = $_SERVER['HTTP_ORIGIN'] ?? '';

        header("Access-Control-Allow-Methods: {$methods}");
        header("Access-Control-Allow-Headers: {$headers}");
        header("Access-Control-Max-Age: {$maxAge}");
        header("Vary: Origin");

        // Só libera se a origin estiver na whitelist
        if ($origin !== '' && in_array($origin, $allowedOrigins, true)) {
            header("Access-Control-Allow-Origin: {$origin}");
            if ($allowCredentials) {
                header("Access-Control-Allow-Credentials: true");
            }
        }

        // Preflight
        if (($_SERVER['REQUEST_METHOD'] ?? '') === 'OPTIONS') {
            http_response_code(204);
            exit;
        }
    }
}
