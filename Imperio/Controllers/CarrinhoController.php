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

    // ✅ NOVO: listar endereços salvos do usuário
    public static function listarEnderecos(int $usuarioId): void
    {
        $lista = CarrinhoEnderecoDao::listarPorUsuario($usuarioId);
        self::Mensagemjson("Endereços do usuário", 200, $lista);
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

    // ✅ Ajustado: salvar endereço do carrinho OU aplicar endereço pelo enderecoId
    public static function salvarEndereco(): void
    {
        $dados = self::receberJson();

        if (!isset($dados['usuarioId'])) {
            self::Mensagemjson("usuarioId é obrigatório", 400);
            return;
        }

        $carrinhoId = CarrinhoDao::criarOuObter((int)$dados['usuarioId']);

        // ✅ Se veio enderecoId, usa o endereço salvo e aplica no carrinho (copia)
        if (isset($dados['enderecoId']) && (int)$dados['enderecoId'] > 0) {
            $end = CarrinhoEnderecoDao::buscarPorId((int)$dados['enderecoId']);

            if (!$end) {
                self::Mensagemjson("Endereço selecionado não encontrado", 404);
                return;
            }

            $endereco = new \App\Models\Carrinho\CarrinhoEndereco(
                $carrinhoId,
                $end['cep'] ?? "",
                $end['rua'] ?? "",
                $end['numero'] ?? "",
                $end['bairro'] ?? "",
                $end['cidade'] ?? "",
                $end['estado'] ?? "",
                $end['complemento'] ?? null
            );

            $existente = CarrinhoEnderecoDao::buscarPorCarrinho($carrinhoId);

            if ($existente) {
                CarrinhoEnderecoDao::atualizar($endereco);
            } else {
                CarrinhoEnderecoDao::criar($endereco);
            }

            self::Mensagemjson("Endereço selecionado aplicado ao carrinho", 200);
            return;
        }

        // ✅ Caso normal: salvar endereço pelos campos
        $required = ['cep', 'rua', 'numero', 'bairro', 'cidade', 'estado'];
        foreach ($required as $k) {
            if (!isset($dados[$k]) || trim((string)$dados[$k]) === "") {
                self::Mensagemjson("Campo obrigatório ausente: {$k}", 400);
                return;
            }
        }

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

        self::Mensagemjson("Endereço salvo com sucesso", 200);
    }
}
