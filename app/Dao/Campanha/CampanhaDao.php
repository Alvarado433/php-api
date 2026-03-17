<?php

namespace App\Dao\Campanha;

use Config\BaseDao\BaseDao;

class CampanhaDao extends BaseDao
{
    protected static string $tabela = "campanha";
    protected static string $tabelaVinculo = "campanha_produto";

    // =========================
    // CAMPANHA (CRUD)
    // =========================

    public static function criar(array $dados): int
    {
        $sql = "
            INSERT INTO " . self::$tabela . "
            (titulo, slug, descricao, banner, statusid, inicio, fim, criado)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)
        ";

        $ok = self::execute($sql, [
            (string) $dados["titulo"],
            (string) $dados["slug"],
            $dados["descricao"] ?? null,
            $dados["banner"] ?? null,
            (int) $dados["statusid"],
            $dados["inicio"] ?? null,
            $dados["fim"] ?? null,
            $dados["criado"]
        ]);

        if (!$ok) {
            return 0;
        }

        return (int) self::lastInsertId();
    }

    public static function atualizar(int $id, array $dados): bool
    {
        $sql = "
            UPDATE " . self::$tabela . "
            SET
                titulo = ?,
                slug = ?,
                descricao = ?,
                banner = ?,
                statusid = ?,
                inicio = ?,
                fim = ?,
                atualizado = ?
            WHERE id_campanha = ?
        ";

        return self::execute($sql, [
            (string) $dados["titulo"],
            (string) $dados["slug"],
            $dados["descricao"] ?? null,
            $dados["banner"] ?? null,
            (int) $dados["statusid"],
            $dados["inicio"] ?? null,
            $dados["fim"] ?? null,
            $dados["atualizado"] ?? null,
            $id
        ]);
    }

    public static function buscar(int $id): ?array
    {
        $sql = "SELECT * FROM " . self::$tabela . " WHERE id_campanha = ? LIMIT 1";
        return self::find($sql, [$id]);
    }

    public static function buscarPorSlug(string $slug): ?array
    {
        $sql = "SELECT * FROM " . self::$tabela . " WHERE slug = ? LIMIT 1";
        return self::find($sql, [$slug]);
    }

    public static function listar(): array
    {
        $sql = "
            SELECT c.*, s.nome AS status_nome, s.codigo AS status_codigo
            FROM " . self::$tabela . " c
            LEFT JOIN status s ON s.id_status = c.statusid
            ORDER BY c.criado DESC
        ";

        return self::findAll($sql);
    }

    public static function deletar(int $id): bool
    {
        try {
            self::limparCampanha($id);

            $sql = "DELETE FROM " . self::$tabela . " WHERE id_campanha = ?";
            return self::execute($sql, [$id]);
        } catch (\Throwable $e) {
            return false;
        }
    }

    // =========================
    // CAMPANHA ATIVA (COM STATUS)
    // =========================

    public static function buscarAtivaPorSlug(string $slug, string $statusCodigo = "destaque"): ?array
    {
        $sql = "
            SELECT c.*, s.nome AS status_nome, s.codigo AS status_codigo
            FROM " . self::$tabela . " c
            JOIN status s ON s.id_status = c.statusid
            WHERE c.slug = ?
              AND s.codigo = ?
              AND (c.inicio IS NULL OR c.inicio <= NOW())
              AND (c.fim IS NULL OR c.fim >= NOW())
            LIMIT 1
        ";

        return self::find($sql, [$slug, $statusCodigo]);
    }

    public static function listarAtivas(string $statusCodigo = "destaque"): array
    {
        $sql = "
            SELECT c.*, s.nome AS status_nome, s.codigo AS status_codigo
            FROM " . self::$tabela . " c
            JOIN status s ON s.id_status = c.statusid
            WHERE s.codigo = ?
              AND (c.inicio IS NULL OR c.inicio <= NOW())
              AND (c.fim IS NULL OR c.fim >= NOW())
            ORDER BY c.criado DESC
        ";

        return self::findAll($sql, [$statusCodigo]);
    }

    // =========================
    // CAMPANHA ATIVA (SEM depender de status.codigo)
    // =========================

    public static function buscarAtivaPorSlugSemStatus(string $slug): ?array
    {
        $sql = "
            SELECT c.*, s.nome AS status_nome, s.codigo AS status_codigo
            FROM " . self::$tabela . " c
            LEFT JOIN status s ON s.id_status = c.statusid
            WHERE c.slug = ?
              AND (c.inicio IS NULL OR c.inicio <= NOW())
              AND (c.fim IS NULL OR c.fim >= NOW())
            LIMIT 1
        ";

        return self::find($sql, [$slug]);
    }

    public static function listarProdutosDaCampanhaPorSlugAtiva(string $slug): array
    {
        $sql = "
            SELECT
                c.id_campanha,
                c.titulo,
                c.slug,
                c.banner,
                cp.id,
                cp.produto_id,
                cp.ordem,
                cp.criado AS vinculo_criado,
                p.*
            FROM " . self::$tabela . " c
            JOIN " . self::$tabelaVinculo . " cp ON cp.campanha_id = c.id_campanha
            JOIN produto p ON p.id_produto = cp.produto_id
            WHERE c.slug = ?
              AND (c.inicio IS NULL OR c.inicio <= NOW())
              AND (c.fim IS NULL OR c.fim >= NOW())
            ORDER BY cp.ordem ASC, cp.id ASC
        ";

        return self::findAll($sql, [$slug]);
    }

    /**
     * Método que estava faltando.
     * Mantém compatibilidade com chamadas como:
     * CampanhaDao::listarProdutosDaCampanhaAtiva($slug)
     */
    public static function listarProdutosDaCampanhaAtiva(string $slug): array
    {
        return self::listarProdutosDaCampanhaPorSlugAtiva($slug);
    }

    // =========================
    // DESTAQUE POR CAMPANHA NÍVEL 9
    // =========================

    public static function buscarCampanhaNivel9Ativa(): ?array
    {
        $sql = "
            SELECT c.*, s.nome AS status_nome, s.codigo AS status_codigo
            FROM " . self::$tabela . " c
            LEFT JOIN status s ON s.id_status = c.statusid
            WHERE c.statusid = 9
              AND (c.inicio IS NULL OR c.inicio <= NOW())
              AND (c.fim IS NULL OR c.fim >= NOW())
            ORDER BY c.criado DESC
            LIMIT 1
        ";

        return self::find($sql);
    }

    public static function listarDestaquesNivel9(): array
    {
        $campanha = self::buscarCampanhaNivel9Ativa();

        if (!$campanha || empty($campanha["id_campanha"])) {
            return [
                "campanha" => null,
                "produtos" => []
            ];
        }

        $produtos = self::listarProdutosDaCampanha((int) $campanha["id_campanha"]);

        return [
            "campanha" => $campanha,
            "produtos" => $produtos
        ];
    }

    // =========================
    // VÍNCULO CAMPANHA x PRODUTO
    // =========================

    public static function vincularProduto(int $campanhaId, int $produtoId, int $ordem, string $criado): bool
    {
        $sql = "
            INSERT INTO " . self::$tabelaVinculo . " (campanha_id, produto_id, ordem, criado)
            VALUES (?, ?, ?, ?)
            ON DUPLICATE KEY UPDATE ordem = VALUES(ordem)
        ";

        return self::execute($sql, [
            $campanhaId,
            $produtoId,
            $ordem,
            $criado
        ]);
    }

    public static function vincularProdutos(int $campanhaId, array $produtos, int $ordemInicial, string $criado): int
    {
        if ($campanhaId <= 0) {
            return 0;
        }

        $lista = [];

        foreach ($produtos as $p) {
            if (is_array($p) && isset($p["id_produto"])) {
                $p = $p["id_produto"];
            }

            $id = (int) $p;

            if ($id > 0) {
                $lista[] = $id;
            }
        }

        $lista = array_values(array_unique($lista));

        if (count($lista) === 0) {
            return 0;
        }

        $ordem = max(1, $ordemInicial);
        $total = 0;

        foreach ($lista as $produtoId) {
            $ok = self::vincularProduto($campanhaId, $produtoId, $ordem, $criado);

            if ($ok) {
                $total++;
            }

            $ordem++;
        }

        return $total;
    }

    public static function removerVinculo(int $campanhaId, int $produtoId): bool
    {
        $sql = "
            DELETE FROM " . self::$tabelaVinculo . "
            WHERE campanha_id = ?
              AND produto_id = ?
        ";

        return self::execute($sql, [$campanhaId, $produtoId]);
    }

    public static function limparCampanha(int $campanhaId): bool
    {
        $sql = "
            DELETE FROM " . self::$tabelaVinculo . "
            WHERE campanha_id = ?
        ";

        return self::execute($sql, [$campanhaId]);
    }

    public static function listarProdutosDaCampanha(int $campanhaId): array
    {
        $sql = "
            SELECT
                cp.id,
                cp.campanha_id,
                cp.produto_id,
                cp.ordem,
                cp.criado AS vinculo_criado,
                p.*
            FROM " . self::$tabelaVinculo . " cp
            JOIN produto p ON p.id_produto = cp.produto_id
            WHERE cp.campanha_id = ?
            ORDER BY cp.ordem ASC, cp.id ASC
        ";

        return self::findAll($sql, [$campanhaId]);
    }
}