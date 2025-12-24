<?php

namespace App\Dao\Produto;

use Config\BaseDao\BaseDao;

class ProdutoDao extends BaseDao
{
    protected static string $tabela = "produto";

    protected static array $status = [
        'ativo' => 1,
        'inativo' => 2,
        'destaque' => 3,
        'bloqueado' => 4,
        'catalogo_sim' => 5,
        'catalogo_nao' => 6
    ];

    /**
     * Retorna o ID do status pelo nome
     */
    public static function status(string $nome): ?int
    {
        return self::$status[$nome] ?? null;
    }

    /**
     * Busca produto pelo ID
     */
    public static function buscar(int $id): ?array
    {
        $sql = "SELECT * FROM " . self::$tabela . " WHERE id_produto = ? LIMIT 1";
        return self::find($sql, [$id]);
    }

    /**
     * Busca produto pelo slug
     */
    public static function buscarPorSlug(string $slug): ?array
    {
        $sql = "SELECT * FROM " . self::$tabela . " WHERE slug = ? LIMIT 1";
        return self::find($sql, [$slug]);
    }

    /**
     * Busca produtos pelo nome (para pesquisa)
     */
    public static function buscarPorNome(string $nome): array
    {
        $sql = "SELECT * FROM " . self::$tabela . " WHERE nome LIKE ? ORDER BY criado DESC";
        $termo = "%{$nome}%";
        return self::findAll($sql, [$termo]);
    }

    /**
     * Retorna todos os produtos
     */
    public static function Todos(): array
    {
        $sql = "SELECT * FROM " . self::$tabela . " ORDER BY criado DESC";
        return self::findAll($sql);
    }

    /**
     * Lista produtos com catálogo
     */
    public static function listarCatalogo(): array
    {
        $sql = "
        WITH ProdutosComCategoria AS (
            SELECT 
                p.* 
            FROM " . self::$tabela . " p
        )
        SELECT 
            pc.*,
            c.nome AS categoria_nome
        FROM ProdutosComCategoria pc
        LEFT JOIN categoria c
            ON c.id_categoria = pc.categoria_id
        ORDER BY pc.criado DESC
        ";
        return self::findAll($sql);
    }

    /**
     * Lista todos os produtos com destaque
     */
    public static function listarTodos(): array
    {
        $sql = "
        SELECT 
            p.*,
            pd.id_destaque,
            pd.statusid AS destaque_statusid,
            pd.ordem AS destaque_ordem,
            CASE WHEN pd.produto_id IS NOT NULL THEN 1 ELSE 0 END AS destaque
        FROM " . self::$tabela . " p
        LEFT JOIN produto_destaque pd 
            ON pd.produto_id = p.id_produto 
            AND pd.statusid = ?
        ORDER BY destaque DESC, p.criado DESC
        ";
        return self::findAll($sql, [self::$status['destaque']]);
    }

    /**
     * Cria um produto
     */
    public static function criar(array $dados): bool
    {
        $sql = "
            INSERT INTO " . self::$tabela . "
            (nome, descricao, preco, slug, imagem, estoque, ilimitado, statusid, catalogo, categoria_id, destaque)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ";
        return self::execute($sql, [
            $dados['nome'],
            $dados['descricao'] ?? null,
            (float) ($dados['preco'] ?? 0),
            $dados['slug'] ?? '',
            $dados['imagem'] ?? null,
            (int) ($dados['estoque'] ?? 0),
            !empty($dados['ilimitado']) ? 1 : 0,
            (int) ($dados['statusid'] ?? self::$status['ativo']),
            (int) ($dados['catalogo'] ?? self::$status['catalogo_nao']),
            $dados['categoria_id'] ?? null,
            $dados['destaque'] ?? null
        ]);
    }

    /**
     * Atualiza um produto
     */
    public static function atualizar(int $id, array $dados): bool
    {
        $atual = self::buscar($id);
        if (!$atual) return false;

        $sql = "
            UPDATE " . self::$tabela . "
            SET
                nome = ?,
                descricao = ?,
                preco = ?,
                slug = ?,
                imagem = ?,
                estoque = ?,
                ilimitado = ?,
                statusid = ?,
                catalogo = ?,
                categoria_id = ?,
                destaque = ?,
                atualizado = NOW()
            WHERE id_produto = ?
        ";

        return self::execute($sql, [
            $dados['nome'] ?? $atual['nome'],
            $dados['descricao'] ?? $atual['descricao'],
            (float) ($dados['preco'] ?? $atual['preco']),
            $dados['slug'] ?? $atual['slug'],
            $dados['imagem'] ?? $atual['imagem'],
            (int) ($dados['estoque'] ?? $atual['estoque']),
            isset($dados['ilimitado']) ? ($dados['ilimitado'] ? 1 : 0) : $atual['ilimitado'],
            (int) ($dados['statusid'] ?? $atual['statusid']),
            (int) ($dados['catalogo'] ?? $atual['catalogo']),
            $dados['categoria_id'] ?? $atual['categoria_id'],
            $dados['destaque'] ?? $atual['destaque'],
            $id
        ]);
    }

    /**
     * Deleta um produto
     */
    public static function deletar(int $id): bool
    {
        $sql = "DELETE FROM " . self::$tabela . " WHERE id_produto = ?";
        return self::execute($sql, [$id]);
    }

    /**
     * Remove categoria dos produtos
     */
    public static function removerCategoriaDosProdutos(int $categoriaId): bool
    {
        $sql = "UPDATE " . self::$tabela . " SET categoria_id = NULL WHERE categoria_id = ?";
        return self::execute($sql, [$categoriaId]);
    }

    /**
     * Unifica produtos em uma categoria
     */
    public static function unificarEmCategoria(int $categoriaId, array $produtos): bool
    {
        if (empty($produtos)) return false;

        $placeholders = implode(',', array_fill(0, count($produtos), '?'));
        $sql = "UPDATE " . self::$tabela . " SET categoria_id = ? WHERE id_produto IN ($placeholders)";
        $params = array_merge([$categoriaId], $produtos);

        return self::execute($sql, $params);
    }
}
