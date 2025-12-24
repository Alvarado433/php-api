<?php

namespace Config\nucleo\nucleologs;

use Config\Base\BaseConfig;
use Core\Logs\Logs;

class nucleo extends BaseConfig
{
    protected static string $diretorio = "Api";

    protected static array $subpasta = [
        "Info",
        "Sucess",
        "Sql",
        "Error",
        "warning"
    ];

    /** Detecta o módulo real */
    protected static function detectarModulo(): string
    {
        $trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);

        foreach ($trace as $item) {

            if (!isset($item['class'])) continue;

            $classe = $item['class'];

            if (in_array($classe, [
                Logs::class,
                self::class,
                BaseConfig::class
            ])) {
                continue;
            }

            $partes = explode("\\", $classe);
            return end($partes) ?: "Geral";
        }

        return "Geral";
    }

    /** Cria Api/Modulo/Subpasta */
    protected static function criarEstrutura(string $sub): string
    {
        $modulo = static::detectarModulo();
        $base = static::caminho();

        $moduloPath = $base . $modulo . DIRECTORY_SEPARATOR;
        static::criarDiretorio($moduloPath);

        return static::criarSubpasta($moduloPath, $sub);
    }

    /** Monta dados do log (AGORA NO NÚCLEO) */
    protected static function montarDados(string $mensagem, string $tipo): array
    {
        $trace = debug_backtrace();

        $arquivo = $trace[2]['file'] ?? "desconhecido";
        $linha   = $trace[2]['line'] ?? 0;

        $modulo = static::detectarModulo();

        $dados = [
            "tipo"           => $tipo,
            "modulo"         => $modulo,
            "mensagem"       => $mensagem,
            "arquivo"        => basename($arquivo),
            "linha"          => $linha,
            "data"           => date("c"),
            "tempo_execucao" =>
                microtime(true) - ($_SERVER["REQUEST_TIME_FLOAT"] ?? microtime(true))
        ];

        // gera hash único
        $dados["id"] = sha1(
            json_encode($dados)
            . microtime(true)
            . random_int(PHP_INT_MIN, PHP_INT_MAX)
        );

        return $dados;
    }

    /** Salvar em JSON */
    protected static function salvarJson(string $subpasta, array $dados): void
    {
        $pasta = static::criarEstrutura($subpasta);

        $arquivo = $pasta . DIRECTORY_SEPARATOR . date("Y-m-d") . ".json";

        $conteudo = [];

        if (file_exists($arquivo)) {
            $conteudo = json_decode(file_get_contents($arquivo), true);
        }

        $conteudo[] = $dados;

        file_put_contents($arquivo, json_encode($conteudo, JSON_PRETTY_PRINT));
    }
}
