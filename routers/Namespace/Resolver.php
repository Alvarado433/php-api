<?php

namespace Routers\Namespace;

use Core\Env\IndexEnv;
use Config\Base\BaseRoteamento;
use Routers\Generator\AutoControllerGenerator;
use Routers\Middleware\AuthMiddleware;

class Resolver extends BaseRoteamento
{
    protected static string $namespace = "";


    /**
     * Inicializa namespace base dos controllers
     */
    public static function init(): void
    {
        $env = IndexEnv::carregar();

        $base = $env['APP_NAMESPACE'] ?? "Imperio";

        static::$namespace = rtrim($base, "\\") . "\\Controllers\\";

        self::info("Namespace carregado: " . static::$namespace);
    }


    /**
     * Resolve e cria controller/método dinamicamente quando necessário
     */
    public static function resolver(string $controller, string $metodo): array
    {
        try {
            if (empty(static::$namespace)) {
                static::init();
            }

            $className = static::$namespace . $controller;

            // Criar controller automaticamente se não existir
            if (!class_exists($className)) {

                $arquivo = AutoControllerGenerator::gerar($controller, $metodo);

                if (file_exists($arquivo)) {
                    require_once $arquivo;
                }
            }

            if (!class_exists($className)) {
                throw new \Exception("Falha ao gerar controller {$controller}, classe não encontrada.");
            }

            $instancia = new $className();

            // Criar método automaticamente se não existir
            if (!method_exists($instancia, $metodo)) {

                $arquivo = AutoControllerGenerator::gerar($controller, $metodo);

                if (file_exists($arquivo)) {
                    require_once $arquivo;
                }

                $instancia = new $className();
            }

            return [$instancia, $metodo];

        } catch (\Throwable $th) {
            self::error("Erro resolver namespace: " . $th->getMessage());
            throw $th;
        }
    }


    /**
     * Executa a ação da rota e aplica middlewares (ex: auth)
     */
    public static function executarAcao(string $rota): void
    {
        // ----------------------------------------
        // 🔥 1. Verifica se rota possui middleware
        // ----------------------------------------
        if (str_starts_with($rota, "auth|")) {

            // middleware obrigatorio
            AuthMiddleware::verificar();

            // remove "auth|" para capturar controller@metodo correto
            $rota = substr($rota, 5);
        }

        // ----------------------------------------
        // 🔥 2. Obter Controller e Método (Ex: Banner@listar)
        // ----------------------------------------
        [$classe, $metodo] = explode('@', $rota);

        // ----------------------------------------
        // 🔥 3. Resolver/gerar controller e método
        // ----------------------------------------
        [$controller, $action] = self::resolver($classe, $metodo);

        // ----------------------------------------
        // 🔥 4. Executar a ação final
        // ----------------------------------------
        $controller->$action();
    }
}
