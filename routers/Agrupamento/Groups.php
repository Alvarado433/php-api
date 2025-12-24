<?php

namespace Routers\Agrupamento;

use Config\Base\BaseRoteamento;

class Groups extends BaseRoteamento
{
    protected static bool $usarAuth = false;

    /**
     * Inicia um grupo de rotas com prefixo
     */
    public static function prefix(string $prefixo, callable $callback): void
    {
        $prefixoAnterior = static::$prefixoAtual;
        $authAnterior = static::$usarAuth;

        // aplica prefixo
        static::$prefixoAtual .= $prefixo;

        self::info("Iniciando grupo de rotas: " . static::$prefixoAtual);

        // executa rotas internas do grupo
        $callback();

        // restaura prefixo e auth anterior
        static::$prefixoAtual = $prefixoAnterior;
        static::$usarAuth = $authAnterior;

        self::info("Finalizando grupo de rotas.");
    }

    /**
     * Ativa AUTH para todas rotas dentro do grupo atual
     */
    public static function auth(): void
    {
        static::$usarAuth = true;
        self::info("AUTH ativado para o grupo atual.");
    }

    /*
    |--------------------------------------------------------------------------
    | Métodos HTTP com suporte ao AUTH
    |--------------------------------------------------------------------------
    */

    public static function get(string $url, string $acao): void
    {
        if (static::$usarAuth) {
            parent::$rotas['get'][static::$prefixoAtual.$url] = "auth|" . $acao;
        } else {
            parent::get($url, $acao);
        }
    }

    public static function post(string $url, string $acao): void
    {
        if (static::$usarAuth) {
            parent::$rotas['post'][static::$prefixoAtual.$url] = "auth|" . $acao;
        } else {
            parent::post($url, $acao);
        }
    }

    public static function put(string $url, string $acao): void
    {
        if (static::$usarAuth) {
            parent::$rotas['put'][static::$prefixoAtual.$url] = "auth|" . $acao;
        } else {
            parent::put($url, $acao);
        }
    }

    public static function delete(string $url, string $acao): void
    {
        if (static::$usarAuth) {
            parent::$rotas['delete'][static::$prefixoAtual.$url] = "auth|" . $acao;
        } else {
            parent::delete($url, $acao);
        }
    }
}
