<?php

namespace Routers\Generator;

use Core\Logs\Logs;
use Core\Env\IndexEnv;

class AutoModelGenerator extends Logs
{
    public static function gerar(string $model, array $propriedades): string
    {
        $env = IndexEnv::carregar();
        $namespaceBase = $env["APP_NAMESPACE"] ?? "App";

        // raiz do projeto (pasta IMPERIO)
        $root = realpath(__DIR__ . "/../../");

        // Diretório correto: /Imperio/Models
        $dir = "{$root}/Models";

        if (!is_dir($dir)) {
            mkdir($dir, 0777, true);
            self::success("Pasta Models criada: {$dir}");
        }

        // Caminho do arquivo
        $arquivo = "{$dir}/{$model}.php";

        // Namespace correto
        $namespace = "{$namespaceBase}\\Models";

        // Conteúdo do Model
        $conteudo = self::criarConteudo($namespace, $model, $propriedades);

        file_put_contents($arquivo, $conteudo);

        self::success("Model criado: {$arquivo}");

        return $arquivo;
    }


    private static function criarConteudo(string $namespace, string $model, array $props): string
    {
        $atributos = "";
        $constructorParams = "";
        $constructorBody = "";
        $getters = "";
        $toArrayBody = "";

        foreach ($props as $nome => $tipo) {

            // private string $nome;
            $atributos .= "    private {$tipo} \${$nome};\n";

            // __construct(string $nome)
            $constructorParams .= "{$tipo} \${$nome}, ";

            // $this->nome = $nome;
            $constructorBody .= "        \$this->{$nome} = \${$nome};\n";

            // getter
            $upper = ucfirst($nome);

            $getters .= <<<PHP

    public function get{$upper}(): {$tipo}
    {
        return \$this->{$nome};
    }

PHP;

            // toArray fields
            $toArrayBody .= "            \"{$nome}\" => \$this->{$nome},\n";
        }

        $constructorParams = rtrim($constructorParams, ", ");

        return <<<PHP
<?php

namespace {$namespace};

class {$model}
{
{$atributos}
    public function __construct({$constructorParams})
    {
{$constructorBody}    }
{$getters}
    public function toArray(): array
    {
        return [
{$toArrayBody}        ];
    }
}
PHP;
    }
}
