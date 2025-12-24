<?php

namespace Imperio\Controllers;

use Config\Base\Basecontrolador;
use App\Dao\Produto\ProdutoDestaqueDao;

class ProdutoDestaqueController extends Basecontrolador
{
    public function listar(): void
    {
        $destaques = ProdutoDestaqueDao::listar();
        self::Mensagemjson("Produtos em destaque listados com sucesso", 200, $destaques);
    }

    public function listarAtivos(): void
    {
        $ativos = ProdutoDestaqueDao::listarAtivos();
        self::Mensagemjson("Destaques ativos listados com sucesso", 200, $ativos);
    }

    public function listarPorStatusDestaque(): void
    {
        $produtos = ProdutoDestaqueDao::listarProdutosStatusDestaque();
        self::Mensagemjson("Produtos com status destaque listados com sucesso", 200, $produtos);
    }

    public function buscar(int $id): void
    {
        $destaque = ProdutoDestaqueDao::buscar($id);

        if (!$destaque) {
            self::Mensagemjson("Destaque não encontrado", 404);
            return;
        }

        self::Mensagemjson("Destaque encontrado", 200, $destaque);
    }

    public function criar(): void
    {
        $dados = self::receberJson();

        if (empty($dados['produto_id'])) {
            self::Mensagemjson("produto_id é obrigatório", 400);
            return;
        }

        $ok = ProdutoDestaqueDao::criar($dados);

        self::Mensagemjson(
            $ok ? "Destaque criado com sucesso" : "Erro ao criar destaque",
            $ok ? 201 : 500
        );
    }

    public function atualizar(int $id): void
    {
        $dados = self::receberJson();

        $ok = ProdutoDestaqueDao::atualizar($id, $dados);

        self::Mensagemjson(
            $ok ? "Destaque atualizado com sucesso" : "Erro ao atualizar destaque",
            $ok ? 200 : 500
        );
    }

    public function deletar(int $id): void
    {
        $ok = ProdutoDestaqueDao::deletar($id);

        self::Mensagemjson(
            $ok ? "Destaque deletado com sucesso" : "Erro ao deletar destaque",
            $ok ? 200 : 500
        );
    }
}
