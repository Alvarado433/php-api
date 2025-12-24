<?php

namespace App\Dao\Status;

use App\Models\StatusModel;
use Config\BaseDao\BaseDao;

class StatusDao extends BaseDao
{
    protected static string $tabela = "status";

    /**
     * Listar todos os status
     */
    public static function listar(): array
    {
        $sql = "SELECT * FROM " . self::$tabela;
        $rows = self::findAll($sql);

        return array_map(function ($row) {
            return [
                "id_status" => $row["id_status"],
                "nome"      => $row["nome"],
                "codigo"    => $row["codigo"],
                "descricao" => $row["descricao"] ?? null
            ];
        }, $rows);
    }

    /**
     * Buscar por ID
     */
    public static function buscarPorId(int $id): ?StatusModel
    {
        $sql = "SELECT * FROM " . self::$tabela . " WHERE id_status = ?";
        $row = self::find($sql, [$id]);

        if (!$row) {
            return null;
        }

        return new StatusModel(
            $row["nome"],
            $row["codigo"],
            $row["descricao"]
        );
    }

    /**
     * Buscar status pelo código (ATIVO, INATIVO, BLOQ, etc)
     */
    public static function buscarPorCodigo(string $codigo): ?StatusModel
    {
        $sql = "SELECT * FROM " . self::$tabela . " WHERE codigo = ?";
        $row = self::find($sql, [$codigo]);

        if (!$row) {
            return null;
        }

        return new StatusModel(
            $row["nome"],
            $row["codigo"],
            $row["descricao"]
        );
    }

    /**
     * Criar novo status
     */
    public static function criar(StatusModel $status): bool
    {
        $sql = "INSERT INTO " . self::$tabela . " (nome, codigo, descricao)
                VALUES (?, ?, ?)";

        return self::execute($sql, [
            $status->getNome(),
            $status->getCodigo(),
            $status->getDescricao()
        ]);
    }

    /**
     * Atualizar status
     */
    public static function atualizar(int $id, StatusModel $status): bool
    {
        $sql = "UPDATE " . self::$tabela . "
                SET nome = ?, codigo = ?, descricao = ?
                WHERE id_status = ?";

        return self::execute($sql, [
            $status->getNome(),
            $status->getCodigo(),
            $status->getDescricao(),
            $id
        ]);
    }

    /**
     * Deletar status
     */
    public static function deletar(int $id): bool
    {
        $sql = "DELETE FROM " . self::$tabela . " WHERE id_status = ?";
        return self::execute($sql, [$id]);
    }
}
