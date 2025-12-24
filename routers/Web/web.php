<?php

namespace Routers\Web;

use Config\Base\BaseRoteamento;
use Routers\Inicio\roteamento;

class web extends BaseRoteamento
{
    public static function capturaweb()
    {
        try {
            self::info("capturando rota da web");
            $url = filter_var($_SERVER['REQUEST_URI'], FILTER_SANITIZE_URL);
            $web = parse_url($url, PHP_URL_PATH);
            self::success("rota da web capturada com sucesso: " . $web);
            return $web;
        } catch (\Throwable $th) {
            self::error("erro ao capturar rota da web: " . $th->getMessage());
            throw $th;
        }
    }
    public static function capturaMetodo(): string
    {
        try {
            $metodo = $_SERVER['REQUEST_METHOD'];;

            self::success("Método HTTP capturado: {$metodo}");
            return strtoupper($metodo);
        } catch (\Throwable $th) {
            self::error("Erro ao capturar método HTTP: " . $th->getMessage());
            return "GET";
        }
    }
}
