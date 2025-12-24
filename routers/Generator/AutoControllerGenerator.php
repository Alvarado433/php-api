<?php

namespace Routers\Generator;

use Core\Logs\Logs;
use Core\Env\IndexEnv;

class AutoControllerGenerator extends Logs
{
    /**
     * Gera o controller automaticamente conforme o APP_NAMESPACE do .env
     */
    public static function gerar(string $controller, string $metodo): string
    {
        $env = IndexEnv::carregar();

        // Pasta base definida no .env (ex: Imperio)
        $namespaceBase = $env["APP_NAMESPACE"] ?? "App";

        // Caminho real até a raiz do projeto
        $root = realpath(__DIR__ . "/../../");

        // Diretório do namespace (ex: /Imperio)
        $namespaceDir = $root . DIRECTORY_SEPARATOR . $namespaceBase;

        if (!is_dir($namespaceDir)) {
            mkdir($namespaceDir, 0777, true);
            self::success("Pasta do namespace criada: {$namespaceDir}");
        }

        // Diretório Controllers (ex: /Imperio/Controllers)
        $controllersDir = $namespaceDir . DIRECTORY_SEPARATOR . "Controllers";

        if (!is_dir($controllersDir)) {
            mkdir($controllersDir, 0777, true);
            self::success("Pasta Controllers criada: {$controllersDir}");
        }

        // Caminho do arquivo final
        $arquivo = $controllersDir . DIRECTORY_SEPARATOR . "{$controller}.php";

        // Namespace completo do controller
        $namespaceCompleto = "{$namespaceBase}\\Controllers";

        // Criar arquivo caso não exista
        if (!file_exists($arquivo)) {
            $conteudo = self::conteudoController($namespaceCompleto, $controller, $metodo);
            file_put_contents($arquivo, $conteudo);
            self::success("Controller criado: {$arquivo}");
        }

        // Adicionar método (se faltar)
        self::garantirMetodo($arquivo, $controller, $metodo);

        return $arquivo;
    }



    /**
     * Conteúdo inicial do controller GERADO automaticamente
     */
    private static function conteudoController(string $namespace, string $controller, string $metodo): string
    {
        return <<<PHP
<?php

namespace {$namespace};

use Config\\Base\\Basecontrolador;

class {$controller} extends Basecontrolador
{
    public function {$metodo}()
    {
        echo "{$controller}::{$metodo} executado automaticamente!";
    }
}

PHP;
    }



    /**
     * Adiciona método ao controller se não existir
     */
    private static function garantirMetodo(string $arquivo, string $controller, string $metodo)
    {
        $conteudo = file_get_contents($arquivo);

        // Já existe o método? então não adiciona
        if (strpos($conteudo, "function {$metodo}(") !== false) {
            return;
        }

        $novoMetodo = <<<PHP

    public function {$metodo}()
    {
        echo "{$controller}::{$metodo} executado (gerado automaticamente)!";
    }

PHP;

        // Insere antes do último }
        $conteudo = preg_replace('/}\s*$/', $novoMetodo . "}", $conteudo);

        file_put_contents($arquivo, $conteudo);

        self::success("Método {$metodo} adicionado em {$arquivo}");
    }
}
