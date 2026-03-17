<?php

namespace App\Dao\Banner;

use App\Models\BannerModel;
use Config\BaseDao\BaseDao;

class BannerDao extends BaseDao
{
    protected static string $tabela = "banner";

    // 🔹 Listar todos os banners (retorna MODELS com id_banner)
    public static function listar(): array
    {
        $sql = "SELECT * FROM " . static::$tabela . " ORDER BY id_banner DESC";
        $dados = self::findAll($sql);

        return array_map(fn($b) => BannerModel::fromDb($b), $dados);
    }

    // 🔹 Listar banners ativos
    public static function listarAtivos(): array
    {
        $sql = "
            SELECT *
            FROM " . static::$tabela . "
            WHERE statusid = 1
            ORDER BY id_banner DESC
        ";

        $dados = self::findAll($sql);
        return array_map(fn($b) => BannerModel::fromDb($b), $dados);
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
        return array_map(fn($b) => BannerModel::fromDb($b), $dados);
    }

    // 🔹 Buscar banner por ID (retorna MODEL)
    public static function buscarPorId(int $id): ?BannerModel
    {
        $sql = "SELECT * FROM " . static::$tabela . " WHERE id_banner = ? LIMIT 1";
        $dados = self::find($sql, [$id]);

        if (!$dados) return null;

        return BannerModel::fromDb($dados);
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