<?php
namespace App\Dao\Pedido;

use Config\BaseDao\BaseDao;
use App\Models\carrinho\PedidoItem;

class PedidoItemDao extends BaseDao
{
    protected static string $tabela = 'pedido_item';

    public static function adicionar(PedidoItem $item): bool
    {
        $sql = "INSERT INTO " . self::$tabela . " 
            (pedido_id, produto_id, quantidade, preco_unitario) 
            VALUES (?, ?, ?, ?)";

        return self::execute($sql, [
            $item->getPedidoId(),
            $item->getProdutoId(),
            $item->getQuantidade(),
            $item->getPrecoUnitario()
        ]);
    }

    public static function listar(int $pedidoId): array
    {
        $sql = "SELECT * FROM " . self::$tabela . " WHERE pedido_id = ?";
        return self::findAll($sql, [$pedidoId]);
    }

    public static function deletarPorPedido(int $pedidoId): bool
    {
        $sql = "DELETE FROM " . self::$tabela . " WHERE pedido_id = ?";
        return self::execute($sql, [$pedidoId]);
    }
}
