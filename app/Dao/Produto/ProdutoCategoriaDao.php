<?php

namespace App\Dao\Produto;

use Config\BaseDao\BaseDao;

class ProdutoCategoriaDao extends BaseDao
{
    protected static string $tabela = "produto_categoria";

    public static function vincular(int $produtoId, int $categoriaId): bool
    {
        $sql = "INSERT INTO " . self::$tabela . " (produto_id, categoria_id)
                VALUES (?, ?)";
        return self::execute($sql, [$produtoId, $categoriaId]);
    }

    public static function listarPorCategoria(int $categoriaId): array
    {
        $sql = "SELECT p.*
                FROM produto p
                INNER JOIN produto_categoria pc ON pc.produto_id = p.id_produto
                WHERE pc.categoria_id = ? AND p.statusid = 1";

        return self::findAll($sql, [$categoriaId]);
    }
}
