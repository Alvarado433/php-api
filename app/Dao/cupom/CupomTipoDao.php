<?php

namespace App\Dao\Cupom;

use Config\BaseDao\BaseDao;
use App\Models\Cupom\CupomTipoModel;

class CupomTipoDao extends BaseDao
{
    protected static string $tabela = "cupom_tipo";

    /**
     * Listar todos os tipos de cupom
     */
    public static function listar(): array
    {
        $sql = "
            SELECT *
            FROM " . self::$tabela . "
            ORDER BY nome ASC
        ";

        return self::findAll($sql);
    }

    /**
     * Buscar tipo por ID
     */
    public static function buscarPorId(int $id): ?array
    {
        $sql = "
            SELECT *
            FROM " . self::$tabela . "
            WHERE id_tipo = ?
            LIMIT 1
        ";

        return self::find($sql, [$id]);
    }

    /**
     * Buscar tipo por código
     */
    public static function buscarPorCodigo(string $codigo): ?array
    {
        $sql = "
            SELECT *
            FROM " . self::$tabela . "
            WHERE codigo = ?
            LIMIT 1
        ";

        return self::find($sql, [$codigo]);
    }

    /**
     * Criar novo tipo de cupom
     */
    public static function criar(CupomTipoModel $tipo): bool
    {
        $sql = "
            INSERT INTO " . self::$tabela . " 
            (nome, codigo, descricao, statusid)
            VALUES (?, ?, ?, ?)
        ";

        return self::execute($sql, [
            $tipo->getNome(),
            $tipo->getCodigo(),
            $tipo->getDescricao(),
            $tipo->getStatusId()
        ]);
    }

    /**
     * Atualizar tipo de cupom
     */
    public static function atualizar(CupomTipoModel $tipo): bool
    {
        $sql = "
            UPDATE " . self::$tabela . "
            SET nome = ?, codigo = ?, descricao = ?, statusid = ?
            WHERE id_tipo = ?
        ";

        return self::execute($sql, [
            $tipo->getNome(),
            $tipo->getCodigo(),
            $tipo->getDescricao(),
            $tipo->getStatusId(),
            $tipo->getIdTipo()
        ]);
    }

    /**
     * Alterar status
     */
    public static function alterarStatus(int $id, int $statusid): bool
    {
        $sql = "
            UPDATE " . self::$tabela . "
            SET statusid = ?
            WHERE id_tipo = ?
        ";

        return self::execute($sql, [$statusid, $id]);
    }
}
