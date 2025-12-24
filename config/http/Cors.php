<?php

namespace Config\Http;

class Cors
{
    public static function aplicar(): void
    {
        $origin = $_SERVER['HTTP_ORIGIN'] ?? '';

        $originsPermitidos = [
            'http://localhost:3000',
            'http://imperio.com.br',
            'http://www.imperioloja.com.br',
            'https://imperio.com.br',
            'https://www.imperioloja.com.br',
        ];

        if (in_array($origin, $originsPermitidos, true)) {
            header("Access-Control-Allow-Origin: $origin");
            header("Access-Control-Allow-Credentials: true");
        }

        header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
        header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");
        header("Access-Control-Max-Age: 86400");

        // ⚠️ RESPONDER PREFLIGHT
        if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
            http_response_code(204);
            exit;
        }
    }
}
