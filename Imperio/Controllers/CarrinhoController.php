<?php

namespace Imperio\Controllers;

use Config\Base\Basecontrolador;
use App\Dao\Carrinho\CarrinhoDao;
use App\Dao\Carrinho\CarrinhoItemDao;
use App\Dao\Carrinho\CarrinhoEnderecoDao;

class CarrinhoController extends Basecontrolador
{
    // Listar itens do carrinho e endereço do usuário
    public static function listar(int $usuarioId): void
    {
        $carrinho = CarrinhoDao::criarOuObter($usuarioId); // garante carrinho
        $itens = CarrinhoItemDao::listarPorCarrinho($carrinho);
        $endereco = CarrinhoEnderecoDao::buscarPorCarrinho($carrinho);

        self::Mensagemjson("Carrinho carregado", 200, [
            "itens" => $itens,
            "endereco" => $endereco
        ]);
    }

    // Adicionar item ao carrinho
    public static function adicionar(): void
    {
        $dados = self::receberJson();

        $carrinhoId = CarrinhoDao::criarOuObter($dados['usuarioId']);

        $sucesso = CarrinhoItemDao::adicionarItem(
            $carrinhoId,
            $dados['produtoId'],
            $dados['quantidade'],
            $dados['precoUnitario']
        );

        if (!$sucesso) {
            self::Mensagemjson("Erro ao adicionar item ao carrinho", 500, []);
            return;
        }

        self::Mensagemjson("Item adicionado ao carrinho", 201, ["success" => true]);
    }

    // Atualizar quantidade de item
    public static function atualizar(int $itemId): void
    {
        $dados = self::receberJson();
        $sucesso = CarrinhoItemDao::atualizarQuantidade($itemId, $dados['quantidade']);

        if (!$sucesso) {
            self::Mensagemjson("Erro ao atualizar item", 500);
            return;
        }

        self::Mensagemjson("Item atualizado com sucesso");
    }

    // Remover item do carrinho
    public static function remover(int $itemId): void
    {
        $sucesso = CarrinhoItemDao::remover($itemId);

        if (!$sucesso) {
            self::Mensagemjson("Erro ao remover item", 500);
            return;
        }

        self::Mensagemjson("Item removido com sucesso");
    }

    // Limpar carrinho completo (itens + endereço)
    public static function limpar(int $usuarioId): void
    {
        $carrinho = CarrinhoDao::buscarPorUsuario($usuarioId);

        if ($carrinho) {
            CarrinhoItemDao::limpar($carrinho['id_carrinho']);
            CarrinhoEnderecoDao::deletarPorCarrinho($carrinho['id_carrinho']);
        }

        self::Mensagemjson("Carrinho limpo com sucesso");
    }

    // Adicionar ou atualizar endereço do carrinho
    public static function salvarEndereco(): void
    {
        $dados = self::receberJson();
        $carrinhoId = CarrinhoDao::criarOuObter($dados['usuarioId']);

        $endereco = new \App\Models\Carrinho\CarrinhoEndereco(
            $carrinhoId,
            $dados['cep'],
            $dados['rua'],
            $dados['numero'],
            $dados['bairro'],
            $dados['cidade'],
            $dados['estado'],
            $dados['complemento'] ?? null
        );

        $existente = CarrinhoEnderecoDao::buscarPorCarrinho($carrinhoId);

        if ($existente) {
            CarrinhoEnderecoDao::atualizar($endereco);
        } else {
            CarrinhoEnderecoDao::criar($endereco);
        }

        self::Mensagemjson("Endereço salvo com sucesso");
    }
}
