<?php

namespace App\Dao\pedido;

use Config\BaseDao\BaseDao;
use App\Models\Carrinho\Pedido;


class PedidoDao extends BaseDao
{
    protected static string $tabela = 'pedido';
    protected static string $tabelaItens = 'pedido_item';

    /**
     * =========================
     * PEDIDO
     * =========================
     */

    public static function criar(Pedido $pedido): bool
    {
        $sql = "INSERT INTO " . self::$tabela . "
            (usuario_id, statusid, total, frete, carrinho_endereco_id, metodo_pagamento, pagamento_info)
            VALUES (?, ?, ?, ?, ?, ?, ?)";

        $pagamentoInfo = $pedido->getPagamentoInfo();

        if (is_array($pagamentoInfo) || is_object($pagamentoInfo)) {
            $pagamentoInfo = json_encode($pagamentoInfo, JSON_UNESCAPED_UNICODE);
        }

        return self::execute($sql, [
            $pedido->getUsuarioId(),
            $pedido->getStatusId(),
            $pedido->getTotal(),
            $pedido->getFrete(),
            $pedido->getEnderecoId(),
            $pedido->getMetodoPagamento(),
            $pagamentoInfo
        ]);
    }
    public static function salvarPagamentoInfo(int $pedidoId, $info): bool
    {
        if (is_array($info)) {
            $info = json_encode($info, JSON_UNESCAPED_UNICODE);
        }

        $sql = "UPDATE pedido SET pagamento_info = ? WHERE id_pedido = ?";

        return self::execute($sql, [$info, $pedidoId]);
    }

    public static function buscar(int $id): ?array
    {
        $sql = "SELECT * FROM " . self::$tabela . " WHERE id_pedido = ?";
        return self::find($sql, [$id]);
    }

    public static function listarPorUsuario(int $usuarioId): array
    {
        $sql = "SELECT * FROM " . self::$tabela . "
                WHERE usuario_id = ?
                ORDER BY id_pedido DESC";

        return self::findAll($sql, [$usuarioId]);
    }

    public static function alterarStatus(int $id, int $statusId): bool
    {
        $sql = "UPDATE " . self::$tabela . "
                SET statusid = ?
                WHERE id_pedido = ?";

        return self::execute($sql, [$statusId, $id]);
    }

    public static function deletar(int $id): bool
    {
        // primeiro apaga os itens do pedido
        self::deletarItensPorPedido($id);

        // depois apaga o pedido
        $sql = "DELETE FROM " . self::$tabela . " WHERE id_pedido = ?";
        return self::execute($sql, [$id]);
    }

    /**
     * =========================
     * ITENS DO PEDIDO
     * =========================
     */

    public static function adicionarItem(\App\Models\Carrinho\PedidoItem $item): bool
    {
        $sql = "INSERT INTO " . self::$tabelaItens . "
            (pedido_id, produto_id, quantidade, preco_unitario)
            VALUES (?, ?, ?, ?)";

        return self::execute($sql, [
            $item->getPedidoId(),
            $item->getProdutoId(),
            $item->getQuantidade(),
            $item->getPrecoUnitario()
        ]);
    }

    public static function listarItens(int $pedidoId): array
    {
        $sql = "SELECT * FROM " . self::$tabelaItens . " WHERE pedido_id = ?";
        return self::findAll($sql, [$pedidoId]);
    }

    public static function deletarItensPorPedido(int $pedidoId): bool
    {
        $sql = "DELETE FROM " . self::$tabelaItens . " WHERE pedido_id = ?";
        return self::execute($sql, [$pedidoId]);
    }
}
