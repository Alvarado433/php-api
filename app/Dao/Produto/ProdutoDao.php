<?php

namespace App\Dao\Produto;

use Config\BaseDao\BaseDao;

class ProdutoDao extends BaseDao
{
    protected static string $tabela = "produto";
    protected static string $tabelaImagens = "produto_imagem";

    protected static array $status = [
        'ativo' => 1,
        'inativo' => 2,
        'destaque' => 3,
        'bloqueado' => 4,
        'catalogo_sim' => 5,
        'catalogo_nao' => 6
    ];

    public static function status(string $nome): ?int
    {
        return self::$status[$nome] ?? null;
    }

    /* =====================================================
     * ✅ Helpers internos
     * ===================================================== */

    /**
     * Injeta imagens secundárias (produto_imagem) dentro do produto
     * Retorna ['imagensSecundarias' => ['upload/...', ...]]
     */
    protected static function anexarImagensSecundarias(array $produto): array
    {
        $produtoId = (int)($produto["id_produto"] ?? 0);
        if ($produtoId <= 0) {
            $produto["imagensSecundarias"] = [];
            return $produto;
        }

        $imgs = self::listarImagens($produtoId);

        $produto["imagensSecundarias"] = array_values(array_filter(array_map(function ($row) {
            return $row["imagem"] ?? null;
        }, $imgs)));

        return $produto;
    }

    /* =====================================================
     * ✅ BUSCAS
     * ===================================================== */

    public static function buscar(int $id): ?array
    {
        $sql = "
            SELECT 
                p.*,
                c.nome AS categoria_nome
            FROM " . self::$tabela . " p
            LEFT JOIN categoria c
                ON c.id_categoria = p.categoria_id
            WHERE p.id_produto = ?
            LIMIT 1
        ";

        $produto = self::find($sql, [$id]);
        if (!$produto) return null;

        return self::anexarImagensSecundarias($produto);
    }

    public static function buscarPorSlug(string $slug): ?array
    {
        $sql = "
            SELECT 
                p.*,
                c.nome AS categoria_nome
            FROM " . self::$tabela . " p
            LEFT JOIN categoria c
                ON c.id_categoria = p.categoria_id
            WHERE p.slug = ?
            LIMIT 1
        ";

        $produto = self::find($sql, [$slug]);
        if (!$produto) return null;

        return self::anexarImagensSecundarias($produto);
    }

    public static function buscarPorNome(string $nome): array
    {
        $sql = "
            SELECT 
                p.*,
                c.nome AS categoria_nome
            FROM " . self::$tabela . " p
            LEFT JOIN categoria c
                ON c.id_categoria = p.categoria_id
            WHERE p.nome LIKE ?
            ORDER BY p.criado DESC
        ";

        $termo = "%{$nome}%";
        return self::findAll($sql, [$termo]);
    }

    public static function Todos(): array
    {
        $sql = "SELECT * FROM " . self::$tabela . " ORDER BY criado DESC";
        return self::findAll($sql);
    }

    /**
     * Lista catálogo com nome de categoria
     */
    public static function listarCatalogo(): array
    {
        $sql = "
            SELECT 
                p.*,
                c.nome AS categoria_nome
            FROM " . self::$tabela . " p
            LEFT JOIN categoria c
                ON c.id_categoria = p.categoria_id
            ORDER BY p.criado DESC
        ";
        return self::findAll($sql);
    }

    /**
     * Lista todos + info se é destaque (produto_destaque)
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

    /* =====================================================
     * ✅ CRUD
     * ===================================================== */

    public static function criar(array $dados): bool
    {
        $sql = "
            INSERT INTO " . self::$tabela . "
            (nome, descricao, preco, preco_promocional, slug, imagem, estoque, ilimitado, statusid, catalogo, categoria_id, destaque, sku, modelo, parcelamento)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ";

        return self::execute($sql, [
            $dados['nome'],
            $dados['descricao'] ?? null,
            (float)($dados['preco'] ?? 0),
            isset($dados['preco_promocional']) ? (float)$dados['preco_promocional'] : null,
            $dados['slug'] ?? '',
            $dados['imagem'] ?? null,
            (int)($dados['estoque'] ?? 0),
            !empty($dados['ilimitado']) ? 1 : 0,
            (int)($dados['statusid'] ?? self::$status['ativo']),
            (int)($dados['catalogo'] ?? self::$status['catalogo_nao']),
            $dados['categoria_id'] ?? null,
            $dados['destaque'] ?? null,
            $dados['sku'] ?? null,
            $dados['modelo'] ?? null,
            $dados['parcelamento'] ?? null
        ]);
    }

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
                preco_promocional = ?,
                slug = ?,
                imagem = ?,
                estoque = ?,
                ilimitado = ?,
                statusid = ?,
                catalogo = ?,
                categoria_id = ?,
                destaque = ?,
                sku = ?,
                modelo = ?,
                parcelamento = ?,
                atualizado = NOW()
            WHERE id_produto = ?
        ";

        return self::execute($sql, [
            $dados['nome'] ?? $atual['nome'],
            $dados['descricao'] ?? ($atual['descricao'] ?? null),
            (float)($dados['preco'] ?? ($atual['preco'] ?? 0)),
            isset($dados['preco_promocional'])
                ? (float)$dados['preco_promocional']
                : ($atual['preco_promocional'] ?? null),
            $dados['slug'] ?? ($atual['slug'] ?? ''),
            $dados['imagem'] ?? ($atual['imagem'] ?? null),
            (int)($dados['estoque'] ?? ($atual['estoque'] ?? 0)),
            isset($dados['ilimitado'])
                ? ($dados['ilimitado'] ? 1 : 0)
                : (int)($atual['ilimitado'] ?? 0),
            (int)($dados['statusid'] ?? ($atual['statusid'] ?? self::$status['ativo'])),
            (int)($dados['catalogo'] ?? ($atual['catalogo'] ?? self::$status['catalogo_nao'])),
            $dados['categoria_id'] ?? ($atual['categoria_id'] ?? null),
            $dados['destaque'] ?? ($atual['destaque'] ?? null),
            $dados['sku'] ?? ($atual['sku'] ?? null),
            $dados['modelo'] ?? ($atual['modelo'] ?? null),
            $dados['parcelamento'] ?? ($atual['parcelamento'] ?? null),
            $id
        ]);
    }

    public static function deletar(int $id): bool
    {
        $sql = "DELETE FROM " . self::$tabela . " WHERE id_produto = ?";
        return self::execute($sql, [$id]);
    }

    public static function removerCategoriaDosProdutos(int $categoriaId): bool
    {
        $sql = "UPDATE " . self::$tabela . " SET categoria_id = NULL WHERE categoria_id = ?";
        return self::execute($sql, [$categoriaId]);
    }

    public static function unificarEmCategoria(int $categoriaId, array $produtos): bool
    {
        if (empty($produtos)) return false;

        $placeholders = implode(',', array_fill(0, count($produtos), '?'));
        $sql = "UPDATE " . self::$tabela . " SET categoria_id = ? WHERE id_produto IN ($placeholders)";
        $params = array_merge([$categoriaId], $produtos);

        return self::execute($sql, $params);
    }

    /* =====================================================
     * ✅ IMAGENS EXTRAS DO PRODUTO (produto_imagem)
     * ===================================================== */

    /**
     * Adiciona uma imagem extra para um produto
     */
    public static function adicionarImagem(int $produtoId, string $imagem, int $ordem = 1): bool
    {
        $imagem = trim($imagem);
        if ($produtoId <= 0 || $imagem === "") return false;

        $sql = "INSERT INTO " . self::$tabelaImagens . " (produto_id, imagem, ordem) VALUES (?, ?, ?)";
        return self::execute($sql, [$produtoId, $imagem, (int)$ordem]);
    }

    /**
     * Lista imagens extras do produto (ordenadas)
     */
    public static function listarImagens(int $produtoId): array
    {
        $sql = "
            SELECT id_imagem, produto_id, imagem, ordem, criado
            FROM " . self::$tabelaImagens . "
            WHERE produto_id = ?
            ORDER BY ordem ASC, id_imagem ASC
        ";
        return self::findAll($sql, [$produtoId]);
    }

    /**
     * Remove uma imagem extra pelo id_imagem
     */
    public static function removerImagem(int $idImagem): bool
    {
        $sql = "DELETE FROM " . self::$tabelaImagens . " WHERE id_imagem = ?";
        return self::execute($sql, [$idImagem]);
    }

    /**
     * Remove todas as imagens extras de um produto (opcional)
     */
    public static function removerTodasImagens(int $produtoId): bool
    {
        $sql = "DELETE FROM " . self::$tabelaImagens . " WHERE produto_id = ?";
        return self::execute($sql, [$produtoId]);
    }

    /**
     * Define a imagem principal do produto (campo produto.imagem)
     */
    public static function definirImagemPrincipal(int $produtoId, string $imagem): bool
    {
        $imagem = trim($imagem);
        if ($produtoId <= 0 || $imagem === "") return false;

        $sql = "UPDATE " . self::$tabela . " SET imagem = ?, atualizado = NOW() WHERE id_produto = ?";
        return self::execute($sql, [$imagem, $produtoId]);
    }

    /**
     * Reordenar imagens: recebe array [id_imagem => ordem]
     */
    public static function reordenarImagens(int $produtoId, array $ordens): bool
    {
        if ($produtoId <= 0 || empty($ordens)) return false;

        foreach ($ordens as $idImagem => $ordem) {
            $idImagem = (int)$idImagem;
            $ordem = (int)$ordem;
            if ($idImagem <= 0) continue;

            $sql = "UPDATE " . self::$tabelaImagens . " SET ordem = ? WHERE id_imagem = ? AND produto_id = ?";
            self::execute($sql, [$ordem, $idImagem, $produtoId]);
        }

        return true;
    }

    /* =====================================================
     * ✅ GALERIA (listagem admin / contagens)
     * ===================================================== */

    public static function contarImagensGaleria(bool $somenteGaleria = false): int
    {
        if ($somenteGaleria) {
            $sql = "SELECT COUNT(*) AS total
                FROM " . self::$tabelaImagens . "
                WHERE imagem LIKE ?";
            $row = self::find($sql, ["upload/produtos/galeria/%"]);
        } else {
            $sql = "SELECT COUNT(*) AS total
                FROM " . self::$tabelaImagens;
            $row = self::find($sql);
        }

        return (int)($row["total"] ?? 0);
    }

    public static function listarGaleria(bool $somenteGaleria = true, int $limit = 50, int $offset = 0): array
    {
        $limit = max(1, (int)$limit);
        $offset = max(0, (int)$offset);

        $where = "";
        $params = [];

        if ($somenteGaleria) {
            $where = "WHERE pi.imagem LIKE ?";
            $params[] = "upload/produtos/galeria/%";
        }

        $sql = "
            SELECT 
                pi.id_imagem,
                pi.produto_id,
                pi.imagem,
                pi.ordem,
                pi.criado,
                p.nome AS produto_nome
            FROM " . self::$tabelaImagens . " pi
            LEFT JOIN " . self::$tabela . " p ON p.id_produto = pi.produto_id
            $where
            ORDER BY pi.criado DESC, pi.id_imagem DESC
            LIMIT $limit OFFSET $offset
        ";

        return self::findAll($sql, $params);
    }
    /**
 * ✅ Listar produtos ativos por categoria
 */
public static function listarAtivosPorCategoria(int $categoriaId): array
{
    $sql = "
        SELECT 
            p.*,
            c.nome AS categoria_nome
        FROM " . self::$tabela . " p
        LEFT JOIN categoria c
            ON c.id_categoria = p.categoria_id
        WHERE p.categoria_id = ?
          AND p.statusid = ?
        ORDER BY p.criado DESC
    ";

    return self::findAll($sql, [$categoriaId, self::$status['ativo']]);
}
}