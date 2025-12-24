<?php

namespace Config\BaseDao;

use Core\Logs\Logs;
use Database\conexao\conectar;
use mysqli_stmt;

class BaseDao extends Logs
{
    protected static string $tabela;

    /**
     * Retorna a conexão MySQLi
     */
    protected static function db()
    {
        return conectar::conectar();
    }

    /**
     * Retorna o último ID inserido
     */
    public static function lastInsertId(): ?int
    {
        $con = self::db();
        $id = $con->insert_id ?: null;

        Logs::info("lastInsertId -> $id");

        return $id;
    }

    /**
     * SELECT retornando múltiplos registros
     */
    protected static function findAll(string $sql, array $params = []): array
    {
        Logs::sql("SQL findAll: {$sql}");
        Logs::info("Parâmetros: " . json_encode($params));

        $stmt = self::prepareAndBind($sql, $params);
        $stmt->execute();

        $result = $stmt->get_result();
        $dados = $result->fetch_all(MYSQLI_ASSOC);

        Logs::success("findAll retornou " . count($dados) . " registros");

        $stmt->close();
        return $dados;
    }

    /**
     * SELECT retornando um registro
     */
    protected static function find(string $sql, array $params = []): ?array
    {
        Logs::sql("SQL find: {$sql}");
        Logs::info("Parâmetros: " . json_encode($params));

        $stmt = self::prepareAndBind($sql, $params);
        $stmt->execute();

        $result = $stmt->get_result();
        $linha = $result->fetch_assoc();

        Logs::success("find retornou " . ($linha ? "1 registro" : "nenhum registro"));

        $stmt->close();
        return $linha ?: null;
    }

    /**
     * INSERT, UPDATE e DELETE
     */
    protected static function execute(string $sql, array $params = []): bool
    {
        Logs::sql("SQL execute: {$sql}");
        Logs::info("Parâmetros: " . json_encode($params));

        $stmt = self::prepareAndBind($sql, $params);

        $ok = $stmt->execute();

        if (!$ok) {
            Logs::error("Erro ao executar SQL: {$stmt->error}");
        } else {
            Logs::success("SQL executado com sucesso");
        }

        $stmt->close();
        return $ok;
    }

    /**
     * Preparar e fazer bind automático
     */
    protected static function prepareAndBind(string $sql, array $params = []): mysqli_stmt
    {
        $con = self::db();
        $stmt = $con->prepare($sql);

        if (!$stmt) {
            Logs::error("Erro ao preparar SQL: {$con->error}");
            throw new \Exception("Erro ao preparar SQL");
        }

        Logs::info("Preparando SQL com parâmetros...");

        if (!empty($params)) {
            $tipos = self::getParamTypes($params);
            $stmt->bind_param($tipos, ...$params);
            Logs::info("Bind types: {$tipos}");
        }

        return $stmt;
    }

    /**
     * Detecta automaticamente os tipos dos parâmetros
     */
    protected static function getParamTypes(array $params): string
    {
        $tipos = "";

        foreach ($params as $p) {
            if (is_int($p))      $tipos .= "i";
            elseif (is_float($p)) $tipos .= "d";
            else                  $tipos .= "s";
        }

        return $tipos;
    }
}
