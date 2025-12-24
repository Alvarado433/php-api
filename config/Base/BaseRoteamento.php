<?php

namespace Config\Base;

use Core\Logs\Logs;
use Routers\Web\web;

class BaseRoteamento extends Logs
{
    protected static array $rotas = [
        'get' => [],
        'post' => [],
        'put' => [],
        'delete' => []
    ];

    protected static string $prefixoAtual = "";


    /*
    |--------------------------------------------------------------------------
    | Rotas Estilo Express/Next
    |--------------------------------------------------------------------------
    */

    public static function get(string $url, string $acao): void
    {
        $url = static::$prefixoAtual . $url;
        static::$rotas['get'][$url] = $acao;
        self::info("GET registrado: {$url} => {$acao}");
    }

    public static function post(string $url, string $acao): void
    {
        $url = static::$prefixoAtual . $url;
        static::$rotas['post'][$url] = $acao;
        self::info("POST registrado: {$url} => {$acao}");
    }

    public static function put(string $url, string $acao): void
    {
        $url = static::$prefixoAtual . $url;
        static::$rotas['put'][$url] = $acao;
        self::info("PUT registrado: {$url} => {$acao}");
    }

    public static function delete(string $url, string $acao): void
    {
        $url = static::$prefixoAtual . $url;
        static::$rotas['delete'][$url] = $acao;
        self::info("DELETE registrado: {$url} => {$acao}");
    }



    /*
    |--------------------------------------------------------------------------
    | NEXT - Autocarrega módulo + cria arquivo automaticamente
    |--------------------------------------------------------------------------
    */

    public static function next(string $prefixo, string $routerClass): void
    {
        static::$prefixoAtual = $prefixo;

        $modulesDir = dirname(__DIR__, 2) . "/Routers/Modules";

        // Criar pasta Modules automaticamente
        if (!is_dir($modulesDir)) {
            mkdir($modulesDir, 0777, true);
            self::success("Pasta Modules criada automaticamente: {$modulesDir}");
        }

        $filePath = $modulesDir . "/{$routerClass}.php";
        $namespace = "Routers\\Modules";

        // Criar arquivo automaticamente se não existir
        if (!file_exists($filePath)) {
            $conteudo = <<<PHP
<?php

namespace {$namespace};

use Routers\\Inicio\\roteamento;

class {$routerClass}
{
    public static function rotas()
    {
        // Exemplo de rotas (modifique como quiser)
        roteamento::get("/", "Home@index");
       
    }
}
PHP;

            file_put_contents($filePath, $conteudo);
            self::success("Router criado automaticamente: {$routerClass}.php");
        }

        $class = "{$namespace}\\{$routerClass}";

        if (!class_exists($class)) {
            require_once $filePath;
        }

        // Executar o arquivo de rotas
        $class::rotas();

        static::$prefixoAtual = "";
    }



    /*
    |--------------------------------------------------------------------------
    | FUNÇÕES WEB
    |--------------------------------------------------------------------------
    */
    protected static function capturarweb()
    {
        return web::capturaweb();
    }

    protected static function metodo()
    {
        return strtolower(web::capturaMetodo());
    }
}
