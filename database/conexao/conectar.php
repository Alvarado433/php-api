<?php

namespace Database\conexao;

use Core\Logs\Logs;
use Core\Env\IndexEnv;

class conectar extends Logs
{
    /**
     * Instância da conexão mysqli
     */
    private static ?\mysqli $conexao = null;

    /**
     * Inicia conexão lendo do .env
     */
    public static function conectar(): \mysqli
    {
        try {
            if (self::$conexao !== null) {
                return self::$conexao;
            }

            // Carrega variáveis do .env
            $env = IndexEnv::carregar();

            $host = $env["DB_HOST"] ?? "localhost";
            $user = $env["DB_USER"] ?? "root";
            $pass = $env["DB_PASS"] ?? "";
            $name = $env["DB_NAME"] ?? "";
            $port = $env["DB_PORT"] ?? 3306;

            self::info("Conectando ao banco de dados MySQL...");

            $mysqli = new \mysqli($host, $user, $pass, $name, $port);

            // Verifica erro
            if ($mysqli->connect_errno) {
                self::error("Erro ao conectar no banco: " . $mysqli->connect_error);
                throw new \Exception("Falha ao conectar ao banco: " . $mysqli->connect_error);
            }

            self::success("Conexão MySQL realizada com sucesso!");

            self::$conexao = $mysqli;
            return self::$conexao;

        } catch (\Throwable $e) {
            self::error("Erro conectar(): " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Fecha a conexão
     */
    public static function fechar(): void
    {
        try {
            if (self::$conexao !== null) {
                self::$conexao->close();
                self::$conexao = null;

                self::success("Conexão MySQL encerrada.");
            }
        } catch (\Throwable $e) {
            self::error("Erro ao fechar conexão: " . $e->getMessage());
        }
    }
}
