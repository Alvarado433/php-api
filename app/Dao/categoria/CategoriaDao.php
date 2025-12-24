<?php

namespace App\Dao\Categoria;

use Config\BaseDao\BaseDao;

class CategoriaDao extends BaseDao
{
    protected static string $tabela = "categoria";

    protected static array $status = [
        'ativo'     => 1,
        'inativo'   => 2,
        'bloqueado' => 4,
        'destaque'  => 3
    ];

    /**
     * 🔹 Listar todas as categorias com contagem de produtos
     */
    public static function listar(): array
    {
        $sql = "
            SELECT 
                c.id_categoria,
                c.nome,
                c.icone,
                c.statusid,
                c.criado,
                COUNT(p.id_produto) AS total_produtos
            FROM categoria c
            LEFT JOIN produto p 
                ON p.categoria_id = c.id_categoria
            GROUP BY 
                c.id_categoria,
                c.nome,
                c.icone,
                c.statusid,
                c.criado
            ORDER BY c.nome ASC
        ";

        return self::findAll($sql);
    }

    /**
     * 🔹 Listar categorias ativas com contagem de produtos ativos
     */
    public static function listarAtivas(): array
    {
        $sql = "
            SELECT 
                c.id_categoria,
                c.nome,
                c.icone,
                c.statusid,
                c.criado,
                COUNT(p.id_produto) AS total_produtos
            FROM categoria c
            LEFT JOIN produto p 
                ON p.categoria_id = c.id_categoria
                AND p.statusid = ?  -- só produtos ativos
            WHERE c.statusid = ?    -- só categorias ativas
            GROUP BY 
                c.id_categoria,
                c.nome,
                c.icone,
                c.statusid,
                c.criado
            ORDER BY c.nome ASC
        ";

        return self::findAll($sql, [
            self::$status['ativo'], // produtos ativos
            self::$status['ativo']  // categorias ativas
        ]);
    }

    /**
     * 🔹 Listar categorias ordenadas (menu / home)
     */
    public static function listarOrdenadas(): array
    {
        $sql = "
            SELECT 
                c.id_categoria,
                c.nome,
                c.icone,
                c.statusid,
                COALESCE(co.ordem, 999) AS ordem
            FROM categoria c
            LEFT JOIN categoria_ordem co 
                ON co.categoria_id = c.id_categoria
            WHERE c.statusid = ?
            ORDER BY ordem ASC, c.nome ASC
        ";

        return self::findAll($sql, [self::$status['ativo']]);
    }

    /**
     * 🔹 Buscar categoria por ID
     */
    public static function buscar(int $id): ?array
    {
        $sql = "
            SELECT 
                id_categoria,
                nome,
                icone,
                statusid,
                criado
            FROM " . self::$tabela . "
            WHERE id_categoria = ?
            LIMIT 1
        ";

        return self::find($sql, [$id]);
    }

    /**
     * 🔹 Criar categoria
     */
    public static function criar(string $nome, string $icone, int $statusid = 1): bool
    {
        $sql = "
            INSERT INTO " . self::$tabela . "
                (nome, icone, statusid)
            VALUES (?, ?, ?)
        ";

        return self::execute($sql, [$nome, $icone, $statusid]);
    }

    /**
     * 🔹 Atualizar categoria
     */
    public static function atualizar(
        int $id,
        string $nome,
        string $icone,
        int $statusid
    ): bool {
        $sql = "
            UPDATE " . self::$tabela . "
            SET 
                nome = ?,
                icone = ?,
                statusid = ?
            WHERE id_categoria = ?
        ";

        return self::execute($sql, [
            $nome,
            $icone,
            $statusid,
            $id
        ]);
    }

    /**
     * 🔹 Desativar categoria (soft delete)
     */
    public static function desativar(int $id): bool
    {
        $sql = "
            UPDATE " . self::$tabela . "
            SET statusid = ?
            WHERE id_categoria = ?
        ";

        return self::execute($sql, [self::$status['inativo'], $id]);
    }

    /**
     * 🔹 Excluir categoria (DELETE real)
     */
    public static function deletar(int $id): bool
    {
        $sql = "
            DELETE FROM " . self::$tabela . "
            WHERE id_categoria = ?
        ";

        return self::execute($sql, [$id]);
    }
}
