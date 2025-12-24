<?php
namespace App\Dao\Pedido;

use Config\BaseDao\BaseDao;
use App\Models\Carrinho\Pedido;

class PedidoDao extends BaseDao
{
    protected static string $tabela = 'pedido';

    public static function criar(Pedido $pedido): bool
    {
        $sql = "INSERT INTO " . self::$tabela . " 
            (usuario_id, statusid, total, frete, endereco_id, metodo_pagamento, pagamento_info) 
            VALUES (?, ?, ?, ?, ?, ?, ?)";

        return self::execute($sql, [
            $pedido->getUsuarioId(),
            $pedido->getStatusId(),
            $pedido->getTotal(),
            $pedido->getFrete(),
            $pedido->getEnderecoId(),
            $pedido->getMetodoPagamento(),
            $pedido->getPagamentoInfo()
        ]);
    }

    public static function listarPorUsuario(int $usuarioId): array
    {
        $sql = "SELECT * FROM " . self::$tabela . " WHERE usuario_id = ?";
        return self::findAll($sql, [$usuarioId]);
    }

    public static function buscar(int $id): ?array
    {
        $sql = "SELECT * FROM " . self::$tabela . " WHERE id_pedido = ?";
        return self::find($sql, [$id]);
    }

    public static function alterarStatus(int $id, int $statusId): bool
    {
        $sql = "UPDATE " . self::$tabela . " SET statusid = ? WHERE id_pedido = ?";
        return self::execute($sql, [$statusId, $id]);
    }

    public static function deletar(int $id): bool
    {
        $sql = "DELETE FROM " . self::$tabela . " WHERE id_pedido = ?";
        return self::execute($sql, [$id]);
    }
}
