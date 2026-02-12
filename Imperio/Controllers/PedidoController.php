<?php

namespace Imperio\Controllers;

use App\Dao\Pedido\PedidoDao;
use App\Dao\Pedido\PedidoItemDao;
use App\Models\carrinho\Pedido;
use App\Models\carrinho\PedidoItem;
use Config\Base\Basecontrolador;

class PedidoController extends Basecontrolador
{
    public static function criar(): void
    {
        $dados = self::receberJson();

        if (!isset($dados['usuario_id'], $dados['endereco'], $dados['metodo_pagamento'])) {
            self::Mensagemjson("Campos obrigatórios ausentes", 400);
            return;
        }

        $pedido = new Pedido(
            $dados['usuario_id'],
            $dados['statusid'] ?? 1,
            $dados['total'] ?? 0,
            $dados['frete'] ?? 0,
            $dados['endereco'],
            $dados['metodo_pagamento'],
            $dados['pagamento_info'] ?? null
        );

        if (!PedidoDao::criar($pedido)) {
            self::Mensagemjson("Erro ao criar pedido", 500);
            return;
        }

        $pedidoId = PedidoDao::lastInsertId();

        if (!empty($dados['itens']) && is_array($dados['itens'])) {
            foreach ($dados['itens'] as $item) {
                $pedidoItem = new PedidoItem(
                    $pedidoId,
                    $item['produto_id'],
                    $item['quantidade'],
                    $item['preco_unitario']
                );
                PedidoItemDao::adicionar($pedidoItem);
            }
        }

        self::Mensagemjson("Pedido criado com sucesso", 201, ['pedido_id' => $pedidoId]);
    }

    public static function listarPorUsuario(int $usuarioId): void
    {
        $pedidos = PedidoDao::listarPorUsuario($usuarioId);
        self::Mensagemjson("Pedidos do usuário listados", 200, $pedidos);
    }

    public static function buscar(int $pedidoId): void
    {
        $pedido = PedidoDao::buscar($pedidoId); // ✅ usar 'buscar'
        if (!$pedido) {
            self::Mensagemjson("Pedido não encontrado", 404);
            return;
        }

        $itens = PedidoItemDao::listar($pedidoId); // ✅ usar 'listar'
        $pedido['itens'] = $itens;

        self::Mensagemjson("Pedido encontrado", 200, $pedido);
    }

    public static function adicionarItem(): void
    {
        $dados = self::receberJson();

        if (!isset($dados['pedido_id'], $dados['produto_id'], $dados['quantidade'], $dados['preco_unitario'])) {
            self::Mensagemjson("Campos obrigatórios ausentes", 400);
            return;
        }

        $item = new PedidoItem(
            $dados['pedido_id'],
            $dados['produto_id'],
            $dados['quantidade'],
            $dados['preco_unitario']
        );

        if (!PedidoItemDao::adicionar($item)) {
            self::Mensagemjson("Erro ao adicionar item", 500);
            return;
        }

        self::Mensagemjson("Item adicionado com sucesso", 201);
    }

    public static function atualizarStatus(int $pedidoId, int $statusId): void
    {
        if (!PedidoDao::alterarStatus($pedidoId, $statusId)) {
            self::Mensagemjson("Erro ao atualizar status do pedido", 500);
            return;
        }

        self::Mensagemjson("Status do pedido atualizado com sucesso", 200);
    }

    public function finalizar()
    {
        echo "PedidoController::finalizar executado (gerado automaticamente)!";
    }
}