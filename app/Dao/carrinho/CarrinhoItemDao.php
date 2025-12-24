<?php
namespace App\Dao\Carrinho;

use Config\BaseDao\BaseDao;

class CarrinhoItemDao extends BaseDao
{
    protected static string $tabela = 'carrinho_item';

    public static function listarPorCarrinho(int $carrinhoId): array
    {
        $sql = "
            SELECT 
                ci.id_item,
                ci.carrinho_id,
                ci.produto_id,
                ci.quantidade,
                ci.preco_unitario,
                p.nome AS nome_produto,
                p.imagem
            FROM " . self::$tabela . " ci
            INNER JOIN produto p ON p.id_produto = ci.produto_id
            WHERE ci.carrinho_id = ?
        ";

        return self::findAll($sql, [$carrinhoId]);
    }

    public static function adicionarItem(
        int $carrinhoId,
        int $produtoId,
        int $quantidade,
        float $precoUnitario
    ): bool {
        $sql = "
            INSERT INTO " . self::$tabela . " 
            (carrinho_id, produto_id, quantidade, preco_unitario)
            VALUES (?, ?, ?, ?)
        ";

        return self::execute($sql, [
            $carrinhoId,
            $produtoId,
            $quantidade,
            $precoUnitario
        ]);
    }

    public static function atualizarQuantidade(int $id, int $quantidade): bool
    {
        $sql = "UPDATE " . self::$tabela . " SET quantidade = ? WHERE id_item = ?";
        return self::execute($sql, [$quantidade, $id]);
    }

    public static function remover(int $id): bool
    {
        $sql = "DELETE FROM " . self::$tabela . " WHERE id_item = ?";
        return self::execute($sql, [$id]);
    }

    public static function limpar(int $carrinhoId): bool
    {
        $sql = "DELETE FROM " . self::$tabela . " WHERE carrinho_id = ?";
        return self::execute($sql, [$carrinhoId]);
    }
}
