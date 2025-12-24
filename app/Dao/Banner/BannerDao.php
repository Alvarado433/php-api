<?php

namespace App\Dao\Banner;

use App\Models\BannerModel;
use Config\BaseDao\BaseDao;

class BannerDao extends BaseDao
{
    protected static string $tabela = "banner";

    // 🔹 Listar todos os banners
    public static function listar(): array
    {
        $sql = "SELECT * FROM " . static::$tabela . " ORDER BY id_banner DESC";
        $dados = self::findAll($sql);

        return array_map(fn($b) => new BannerModel(
            $b['titulo'],
            $b['descricao'],
            $b['imagem'],
            $b['link'] ?? null,
            $b['visualizacoes'] ?? 0,
            $b['cliques'] ?? 0
        ), $dados);
    }

    
    // 🔹 Listar banners ativos
    public static function listarAtivos(): array
    {
        // CTE para filtrar apenas banners com statusid = 1
        $sql = "
        WITH StatusAtivo AS (
            SELECT 1 AS id_status
        )
        SELECT b.*
        FROM " . static::$tabela . " b
        INNER JOIN StatusAtivo s ON s.id_status = b.statusid
        ORDER BY b.id_banner DESC
    ";

        $dados = self::findAll($sql);

        return array_map(fn($b) => new BannerModel(
            $b['titulo'],
            $b['descricao'],
            $b['imagem'],
            $b['link'] ?? null,
            $b['visualizacoes'] ?? 0,
            $b['cliques'] ?? 0
        ), $dados);
    }
public static function Todos(): array
    {
        $sql = "SELECT * FROM " . self::$tabela . " ORDER BY criado DESC";
        return self::findAll($sql);
    }

    // 🔹 Listar banners ativos e destaque
    public static function listarAtivosEDestaque(): array
    {
        $sql = "
            SELECT b.*
            FROM " . static::$tabela . " b
            INNER JOIN status s ON s.id_status = b.statusid
            WHERE s.codigo IN ('ATIVO', 'DESTAQUE')
            ORDER BY b.id_banner DESC
        ";

        $dados = self::findAll($sql);
        return array_map(fn($b) => new BannerModel(
            $b['titulo'],
            $b['descricao'],
            $b['imagem'],
            $b['link'] ?? null,
            $b['visualizacoes'] ?? 0,
            $b['cliques'] ?? 0
        ), $dados);
    }

    // 🔹 Buscar banner por ID
    public static function buscarPorId(int $id): ?BannerModel
    {
        $sql = "SELECT * FROM " . static::$tabela . " WHERE id_banner = ? LIMIT 1";
        $dados = self::find($sql, [$id]);

        if (!$dados) return null;

        return new BannerModel(
            $dados['titulo'],
            $dados['descricao'],
            $dados['imagem'],
            $dados['link'] ?? null,
            $dados['visualizacoes'] ?? 0,
            $dados['cliques'] ?? 0
        );
    }

    // 🔹 Criar novo banner
    public static function criar(array $dados): bool
    {
        $sql = "
            INSERT INTO " . static::$tabela . "
            (titulo, descricao, imagem, link, statusid, visualizacoes, cliques)
            VALUES (?, ?, ?, ?, ?, 0, 0)
        ";

        return self::execute($sql, [
            $dados['titulo'],
            $dados['descricao'] ?? '',
            $dados['imagem'],
            $dados['link'] ?? null,
            $dados['statusid'] ?? 1
        ]);
    }

    // 🔹 Atualizar banner
    public static function atualizar(int $id, array $dados): bool
    {
        $sql = "
            UPDATE " . static::$tabela . " SET
                titulo = ?,
                descricao = ?,
                imagem = ?,
                link = ?,
                statusid = ?
            WHERE id_banner = ?
        ";

        return self::execute($sql, [
            $dados['titulo'],
            $dados['descricao'] ?? '',
            $dados['imagem'] ?? '',
            $dados['link'] ?? null,
            $dados['statusid'] ?? 1,
            $id
        ]);
    }

    // 🔹 Deletar banner
    public static function deletar(int $id): bool
    {
        return self::execute(
            "DELETE FROM " . static::$tabela . " WHERE id_banner = ?",
            [$id]
        );
    }

    // 🔹 Incrementar visualizações
    public static function incrementarVisualizacao(int $id): bool
    {
        return self::execute(
            "UPDATE " . static::$tabela . " SET visualizacoes = visualizacoes + 1 WHERE id_banner = ?",
            [$id]
        );
    }

    // 🔹 Incrementar cliques
    public static function incrementarClique(int $id): bool
    {
        return self::execute(
            "UPDATE " . static::$tabela . " SET cliques = cliques + 1 WHERE id_banner = ?",
            [$id]
        );
    }
}
