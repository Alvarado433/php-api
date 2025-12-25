<?php

namespace App\Dao\Cupom;

use Config\BaseDao\BaseDao;
use App\Models\Cupom\CupomModel;

class CupomDao extends BaseDao
{
    protected static string $tabela = "cupom";

    // =========================
    // LISTAGEM
    // =========================
    public static function listar(): array
    {
        $sql = "
            SELECT c.*, t.nome AS tipo_nome, t.codigo AS tipo_codigo
            FROM " . self::$tabela . " c
            INNER JOIN cupom_tipo t ON t.id_tipo = c.tipo_id
            ORDER BY c.criado DESC
        ";
        return self::findAll($sql);
    }

    public static function listarAtivos(): array
    {
        $sql = "
            SELECT c.*, t.nome AS tipo_nome, t.codigo AS tipo_codigo
            FROM " . self::$tabela . " c
            INNER JOIN cupom_tipo t ON t.id_tipo = c.tipo_id
            WHERE c.statusid = 1 AND (c.expiracao IS NULL OR c.expiracao >= CURDATE())
            ORDER BY c.criado DESC
        ";
        return self::findAll($sql);
    }

    public static function listarInativos(): array
    {
        $sql = "
            SELECT c.*, t.nome AS tipo_nome, t.codigo AS tipo_codigo
            FROM " . self::$tabela . " c
            INNER JOIN cupom_tipo t ON t.id_tipo = c.tipo_id
            WHERE c.statusid <> 1 OR (c.expiracao IS NOT NULL AND c.expiracao < CURDATE())
            ORDER BY c.criado DESC
        ";
        return self::findAll($sql);
    }

    // =========================
    // BUSCAS
    // =========================
    public static function buscarPorId(int $id): ?array
    {
        return self::find("SELECT * FROM " . self::$tabela . " WHERE id_cupom = ? LIMIT 1", [$id]);
    }

    public static function buscarPorCodigo(string $codigo): ?array
    {
        return self::find("SELECT * FROM " . self::$tabela . " WHERE codigo = ? LIMIT 1", [$codigo]);
    }

    // =========================
    // DEFINIR PUBLICO
    // =========================
    public static function definirPublico(CupomModel $cupom): void
    {
        // 7 = publicado, 8 = desativado
        if ($cupom->getStatusId() === 1 && (!$cupom->getExpiracao() || $cupom->getExpiracao() >= date('Y-m-d'))) {
            $cupom->setPublico(7);
        } else {
            $cupom->setPublico(8);
        }
    }

    // =========================
    // CRIAR / ATUALIZAR / DELETAR
    // =========================
    public static function criar(CupomModel $cupom): bool
    {
        self::definirPublico($cupom);

        $sql = "
            INSERT INTO " . self::$tabela . " 
            (codigo, descricao, tipo_id, desconto, valor_minimo, limite_uso, inicio, expiracao, statusid, publico)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ";
        return self::execute($sql, [
            $cupom->getCodigo(),
            $cupom->getDescricao(),
            $cupom->getTipoId(),
            $cupom->getDesconto(),
            $cupom->getValorMinimo(),
            $cupom->getLimiteUso(),
            $cupom->getInicio(),
            $cupom->getExpiracao(),
            $cupom->getStatusId(),
            $cupom->getPublico()
        ]);
    }

    public static function atualizar(CupomModel $cupom): bool
    {
        self::definirPublico($cupom);

        $sql = "
            UPDATE " . self::$tabela . "
            SET descricao = ?, tipo_id = ?, desconto = ?, valor_minimo = ?, 
                limite_uso = ?, inicio = ?, expiracao = ?, statusid = ?, publico = ?
            WHERE id_cupom = ?
        ";
        return self::execute($sql, [
            $cupom->getDescricao(),
            $cupom->getTipoId(),
            $cupom->getDesconto(),
            $cupom->getValorMinimo(),
            $cupom->getLimiteUso(),
            $cupom->getInicio(),
            $cupom->getExpiracao(),
            $cupom->getStatusId(),
            $cupom->getPublico(),
            $cupom->getIdCupom()
        ]);
    }

    public static function deletar(int $id): bool
    {
        return self::execute("DELETE FROM " . self::$tabela . " WHERE id_cupom = ?", [$id]);
    }

    public static function incrementarUso(int $id): bool
    {
        return self::execute("UPDATE " . self::$tabela . " SET usado = usado + 1 WHERE id_cupom = ?", [$id]);
    }

    public static function alterarStatus(int $id, int $statusid): bool
    {
        return self::execute("UPDATE " . self::$tabela . " SET statusid = ? WHERE id_cupom = ?", [$statusid, $id]);
    }
}
