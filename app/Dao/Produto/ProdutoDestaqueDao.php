<?php

namespace App\Dao\Produto;

use Config\BaseDao\BaseDao;
use App\Models\Produto\ProdutoDestaque;

class ProdutoDestaqueDao extends BaseDao
{
    protected static string $tabela = "produto_destaque";

    // 🔹 Listar todos os destaques
    public static function listar(): array
    {
        $sql = "
            SELECT 
                pd.*,
                p.nome AS produto_nome,
                p.slug AS produto_slug,
                p.imagem AS produto_imagem,
                p.preco AS produto_preco,
                p.descricao AS produto_descricao
            FROM " . self::$tabela . " pd
            INNER JOIN produto p 
                ON p.id_produto = pd.produto_id
            ORDER BY pd.ordem ASC, pd.criado DESC
        ";

        return self::findAll($sql);
    }

    // 🔹 Listar destaques ativos (statusid = 1)
    public static function listarAtivos(): array
    {
        $sql = "
            SELECT 
                pd.*,
                p.nome AS produto_nome,
                p.slug AS produto_slug,
                p.imagem AS produto_imagem,
                p.preco AS produto_preco,
                p.descricao AS produto_descricao
            FROM " . self::$tabela . " pd
            INNER JOIN produto p 
                ON p.id_produto = pd.produto_id
            WHERE pd.statusid = 1
            ORDER BY pd.ordem ASC
        ";

        return self::findAll($sql);
    }

    // 🔹 Listar produtos com status destaque (statusid = 3)
    public static function listarProdutosStatusDestaque(): array
    {
        $sql = "
            SELECT 
                pd.*,
                p.nome AS produto_nome,
                p.slug AS produto_slug,
                p.imagem AS produto_imagem,
                p.preco AS produto_preco,
                p.descricao AS produto_descricao,
                p.statusid AS produto_statusid
            FROM " . self::$tabela . " pd
            INNER JOIN produto p 
                ON p.id_produto = pd.produto_id
            WHERE pd.statusid = 3
            ORDER BY pd.ordem ASC, pd.criado DESC
        ";

        return self::findAll($sql);
    }

    // 🔹 Buscar destaque por ID
    public static function buscar(int $id): ?array
    {
        $sql = "
            SELECT *
            FROM " . self::$tabela . "
            WHERE id_destaque = ?
            LIMIT 1
        ";

        return self::find($sql, [$id]);
    }

    // 🔹 Criar novo destaque
    public static function criar(array $dados): bool
    {
        $destaque = new ProdutoDestaque(
            null,
            (int) $dados['produto_id'],
            (int) ($dados['ordem'] ?? 0),
            (int) ($dados['statusid'] ?? 1)
        );

        $sql = "
            INSERT INTO " . self::$tabela . "
                (produto_id, ordem, statusid)
            VALUES (?, ?, ?)
        ";

        return self::execute($sql, [
            $destaque->getProdutoId(),
            $destaque->getOrdem(),
            $destaque->getStatusid(),
        ]);
    }

    // 🔹 Atualizar destaque
    public static function atualizar(int $id, array $dados): bool
    {
        $existente = self::buscar($id);
        if (!$existente) {
            return false;
        }

        $sql = "
            UPDATE " . self::$tabela . "
            SET
                produto_id = ?,
                ordem = ?,
                statusid = ?
            WHERE id_destaque = ?
        ";

        return self::execute($sql, [
            (int) ($dados['produto_id'] ?? $existente['produto_id']),
            (int) ($dados['ordem'] ?? $existente['ordem']),
            (int) ($dados['statusid'] ?? $existente['statusid']),
            $id
        ]);
    }

    // 🔹 Deletar destaque
    public static function deletar(int $id): bool
    {
        $sql = "
            DELETE FROM " . self::$tabela . "
            WHERE id_destaque = ?
        ";

        return self::execute($sql, [$id]);
    }
}
