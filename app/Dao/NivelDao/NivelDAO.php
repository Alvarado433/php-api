<?php

namespace App\Dao\NivelDao;

use Config\BaseDao\BaseDao;
use App\Models\Nivel;

class NivelDAO extends BaseDao
{
    protected static string $tabela = "nivel";

    /**
     * =======================================================
     * LISTAR TODOS
     * =======================================================
     */
    public static function listar(): array
    {
        $sql = "SELECT * FROM " . self::$tabela . " ORDER BY prioridade ASC";
        $rows = parent::findAll($sql);

        return array_map(fn($r) => new Nivel(
            $r['id_nivel'],
            $r['nome'],
            $r['codigo'],
            $r['prioridade'],
            $r['descricao'],
            $r['criado']
        ), $rows);
    }

    /**
     * =======================================================
     * BUSCAR POR ID
     * =======================================================
     */
    public static function buscar(int $id): ?Nivel
    {
        $sql = "SELECT * FROM " . self::$tabela . " WHERE id_nivel = ? LIMIT 1";
        $r = parent::find($sql, [$id]);

        if (!$r) return null;

        return new Nivel(
            $r['id_nivel'],
            $r['nome'],
            $r['codigo'],
            $r['prioridade'],
            $r['descricao'],
            $r['criado']
        );
    }

    /**
     * =======================================================
     * CRIAR
     * =======================================================
     */
    public static function criar(string $nome, string $codigo, int $prioridade, string $descricao): bool
    {
        $sql = "INSERT INTO " . self::$tabela . " (nome, codigo, prioridade, descricao)
                VALUES (?, ?, ?, ?)";

        return parent::execute($sql, [
            $nome,
            $codigo,
            $prioridade,
            $descricao
        ]);
    }

    /**
     * =======================================================
     * ATUALIZAR
     * =======================================================
     */
    public static function atualizar(int $id, string $nome, string $codigo, int $prioridade, string $descricao): bool
    {
        $sql = "UPDATE " . self::$tabela . " 
                SET nome = ?, codigo = ?, prioridade = ?, descricao = ?
                WHERE id_nivel = ?";

        return parent::execute($sql, [
            $nome,
            $codigo,
            $prioridade,
            $descricao,
            $id
        ]);
    }

    /**
     * =======================================================
     * DELETAR
     * =======================================================
     */
    public static function deletar(int $id): bool
    {
        $sql = "DELETE FROM " . self::$tabela . " WHERE id_nivel = ?";
        return parent::execute($sql, [$id]);
    }
}
