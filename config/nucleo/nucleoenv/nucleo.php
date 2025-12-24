<?php

namespace Config\nucleo\nucleoenv;

use Core\Logs\Logs;
use Config\Base\BaseDiretorios;

class nucleo extends Logs
{
    protected static string $arquivo; // definido no FILHO

    /**
     * Verifica se é possível ler o arquivo .env
     */
    protected static function arquivoPodeSerLido(string $caminho): bool
    {
        return is_readable($caminho);
    }

    /**
     * Verifica se o servidor permite criar arquivos
     */
    protected static function arquivoPodeSerCriado(string $caminho): bool
    {
        $dir = dirname($caminho);
        return is_writable($dir);
    }

    /**
     * Detecta se o servidor está em modo restrito
     */
    protected static function modoServidorRestrito(string $caminho): bool
    {
        if (!static::arquivoPodeSerCriado($caminho)) {
            static::warning("Servidor sem permissão para criar o arquivo .env");
            return true;
        }
        return false;
    }

    /**
     * Cria o arquivo .env caso não exista
     */
    protected static function criarArquivo(): void
    {
        try {
            BaseDiretorios::timezone();
            $caminho = BaseDiretorios::path(static::$arquivo);

            // Servidor sem permissão de escrita
            if (static::modoServidorRestrito($caminho)) {
                static::warning("Usando fallback, pois o servidor não permite criação do .env");
                return;
            }

            if (!file_exists($caminho)) {

                file_put_contents($caminho, static::conteudoPadrao());
                static::info("Arquivo .env criado com sucesso: {$caminho}");

            } else {
                static::info("Arquivo .env já existe: {$caminho}");
            }

        } catch (\Throwable $th) {
            static::error("Erro ao criar arquivo .env: " . $th->getMessage());
        }
    }

    /**
     * Conteúdo inicial do arquivo criado
     */
    protected static function conteudoPadrao(): string
    {
        $data = date("Y-m-d H:i:s");

        return <<<ENV
# =====================================================
# Arquivo .env criado automaticamente por AlvaradoTech
# Data: {$data}
# =====================================================



ENV;
    }

    /**
     * Fallback para ambientes bloqueados
     */
    protected static function envFallback(): array
    {
        static::warning("Usando fallback de variáveis ENV (modo restrito).");

        return [
            "APP_NAME" => "App",
            "APP_ENV"  => "production",
            "APP_DEBUG" => false,

            "DB_HOST" => "localhost",
            "DB_USER" => "root",
            "DB_PASS" => "",
            "DB_NAME" => "banco",
        ];
    }

    /**
     * Lê o arquivo .env
     */
    protected static function lerEnv(): array
    {
        try {
            BaseDiretorios::timezone();
            $caminho = BaseDiretorios::path(static::$arquivo);

            // Servidor sem permissão de leitura
            if (!static::arquivoPodeSerLido($caminho)) {
                static::warning("Sem permissão para ler .env — fallback ativado.");
                return static::envFallback();
            }

            if (!file_exists($caminho)) {
                static::warning("Arquivo .env não encontrado — fallback ativado.");
                return static::envFallback();
            }

            $linhas = file($caminho, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            $env = [];

            foreach ($linhas as $linha) {

                // Ignorar comentários
                if (str_starts_with(trim($linha), '#')) {
                    continue;
                }

                // Processar chave=valor
                if (strpos($linha, '=') !== false) {
                    list($chave, $valor) = explode('=', $linha, 2);

                    $env[trim($chave)] = trim($valor, " \"'");
                }
            }

            static::info(".env carregado com sucesso");
            return $env;

        } catch (\Throwable $th) {

            static::error("Erro ao ler .env: " . $th->getMessage());
            return static::envFallback();
        }
    }
}
