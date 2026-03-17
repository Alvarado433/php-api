<?php

namespace Imperio\Controllers;

use App\Dao\Produto\ProdutoDao;
use App\Dao\Produto\ProdutoDestaqueDao;
use App\Dao\Pedido\PedidoDao;
use App\Dao\Pedido\PedidoItemDao;
use App\Models\carrinho\Pedido;
use App\Models\carrinho\PedidoItem;
use Core\Upload\ServidorUpload;
use Config\Base\Basecontrolador;

class ProdutoController extends Basecontrolador
{
    /* ============================================================
     * PRODUTOS
     * ============================================================ */

    public function listar(): void
    {
        try {
            $produtos = ProdutoDao::Todos();
            self::Mensagemjson("Produtos listados com sucesso", 200, $produtos);
        } catch (\Throwable $th) {
            self::Mensagemjson("Erro ao listar produtos: " . $th->getMessage(), 500);
        }
    }

    public function buscar(int $id): void
    {
        try {
            $produto = ProdutoDao::buscar($id);

            if (!$produto) {
                self::Mensagemjson("Produto não encontrado", 404);
                return;
            }

            // ✅ normaliza tipos + garante arrays esperados no front
            $produto = $this->normalizarProduto($produto);

            self::Mensagemjson("Produto encontrado", 200, $produto);
        } catch (\Throwable $th) {
            self::Mensagemjson("Erro ao buscar produto: " . $th->getMessage(), 500);
        }
    }

    public function buscarPorSlug(string $slug): void
    {
        try {
            $produto = ProdutoDao::buscarPorSlug($slug);

            if (!$produto) {
                self::Mensagemjson("Produto não encontrado", 404);
                return;
            }

            // ✅ normaliza tipos + garante arrays esperados no front
            $produto = $this->normalizarProduto($produto);

            self::Mensagemjson("Produto encontrado", 200, $produto);
        } catch (\Throwable $th) {
            self::Mensagemjson("Erro ao buscar produto pelo slug: " . $th->getMessage(), 500);
        }
    }

    public function criar(): void
    {
        try {
            $dados = $_POST;
            $arquivo = $_FILES['imagem'] ?? null;

            if ($arquivo) {
                $caminhoImagem = ServidorUpload::upload($arquivo, 'produtos');
                if ($caminhoImagem) {
                    $dados['imagem'] = $caminhoImagem;
                }
            }

            $ok = ProdutoDao::criar($dados);

            self::Mensagemjson(
                $ok ? "Produto criado com sucesso" : "Erro ao criar produto",
                $ok ? 201 : 500
            );
        } catch (\Throwable $th) {
            self::Mensagemjson("Erro ao criar produto: " . $th->getMessage(), 500);
        }
    }

    public function atualizar(int $id): void
    {
        try {
            $dados = $_POST;
            $arquivo = $_FILES['imagem'] ?? null;

            if ($arquivo) {
                $caminhoImagem = ServidorUpload::upload($arquivo, 'produtos');
                if ($caminhoImagem) {
                    $dados['imagem'] = $caminhoImagem;
                }
            }

            $ok = ProdutoDao::atualizar($id, $dados);

            self::Mensagemjson(
                $ok ? "Produto atualizado com sucesso" : "Erro ao atualizar produto",
                $ok ? 200 : 500
            );
        } catch (\Throwable $th) {
            self::Mensagemjson("Erro ao atualizar produto: " . $th->getMessage(), 500);
        }
    }

    public function deletar(int $id): void
    {
        try {
            $ok = ProdutoDao::deletar($id);

            self::Mensagemjson(
                $ok ? "Produto deletado com sucesso" : "Erro ao deletar produto",
                $ok ? 200 : 500
            );
        } catch (\Throwable $th) {
            self::Mensagemjson("Erro ao deletar produto: " . $th->getMessage(), 500);
        }
    }

    public function pesquisar(): void
    {
        try {
            $termo = $_GET['q'] ?? '';

            if (empty($termo)) {
                self::Mensagemjson("Nenhum termo informado para pesquisa", 400);
                return;
            }

            $produtos = ProdutoDao::buscarPorNome($termo);

            // ✅ normaliza cada item (opcional, mas ajuda o front)
            $produtos = array_map([$this, 'normalizarProduto'], $produtos);

            self::Mensagemjson(count($produtos) . " resultado(s) encontrado(s)", 200, $produtos);
        } catch (\Throwable $th) {
            self::Mensagemjson("Erro ao pesquisar produtos: " . $th->getMessage(), 500);
        }
    }

    public function listarCatalogo(): void
    {
        try {
            $produtos = ProdutoDao::listarCatalogo();

            // ✅ normaliza lista
            $produtos = array_map([$this, 'normalizarProduto'], $produtos);

            self::Mensagemjson("Produtos em catálogo listados com sucesso", 200, $produtos);
        } catch (\Throwable $th) {
            self::Mensagemjson("Erro ao listar catálogo: " . $th->getMessage(), 500);
        }
    }

    /* ============================================================
     * PRODUTOS EM DESTAQUE
     * ============================================================ */

    public function listarDestaques(): void
    {
        $destaques = ProdutoDestaqueDao::listar();
        self::Mensagemjson("Produtos em destaque listados com sucesso", 200, $destaques);
    }

    public function listarDestaquesAtivos(): void
    {
        $ativos = ProdutoDestaqueDao::listarAtivos();
        self::Mensagemjson("Destaques ativos listados com sucesso", 200, $ativos);
    }

    public function listarProdutosStatusDestaque(): void
    {
        $produtos = ProdutoDestaqueDao::listarProdutosStatusDestaque();
        self::Mensagemjson("Produtos com status destaque listados com sucesso", 200, $produtos);
    }

    public function buscarDestaque(int $id): void
    {
        $destaque = ProdutoDestaqueDao::buscar($id);

        if (!$destaque) {
            self::Mensagemjson("Destaque não encontrado", 404);
            return;
        }

        self::Mensagemjson("Destaque encontrado", 200, $destaque);
    }

    public function criarDestaque(): void
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

    public function atualizarDestaque(int $id): void
    {
        $dados = self::receberJson();

        $ok = ProdutoDestaqueDao::atualizar($id, $dados);

        self::Mensagemjson(
            $ok ? "Destaque atualizado com sucesso" : "Erro ao atualizar destaque",
            $ok ? 200 : 500
        );
    }

    public function deletarDestaque(int $id): void
    {
        $ok = ProdutoDestaqueDao::deletar($id);

        self::Mensagemjson(
            $ok ? "Destaque deletado com sucesso" : "Erro ao deletar destaque",
            $ok ? 200 : 500
        );
    }

    /* ============================================================
     * ✅ NORMALIZAÇÃO (para o front receber sempre consistente)
     * ============================================================ */

    /**
     * Garante tipos e campos esperados pelo front:
     * - preco float
     * - estoque int
     * - ilimitado int
     * - imagensSecundarias sempre array
     * - categoria_nome e status_nome ficam se existirem
     */
    private function normalizarProduto(array $produto): array
    {
        $produto['preco'] = isset($produto['preco']) ? (float)$produto['preco'] : 0.0;
        $produto['estoque'] = isset($produto['estoque']) ? (int)$produto['estoque'] : 0;
        $produto['ilimitado'] = isset($produto['ilimitado']) ? (int)$produto['ilimitado'] : 0;

        // preco_promocional pode vir string
        if (isset($produto['preco_promocional'])) {
            $produto['preco_promocional'] = (float)$produto['preco_promocional'];
        }

        // ✅ sempre array
        if (!isset($produto['imagensSecundarias']) || !is_array($produto['imagensSecundarias'])) {
            $produto['imagensSecundarias'] = [];
        }

        // (opcional) limpa nulos dentro do array
        $produto['imagensSecundarias'] = array_values(array_filter($produto['imagensSecundarias'], function ($v) {
            return is_string($v) && trim($v) !== "";
        }));

        return $produto;
    }
    public function listarPorCategoria($id)
    {
        try {

            if (!is_numeric($id)) {
                return self::Mensagemjson("Categoria inválida", 422);
            }

            $produtos = \App\Dao\Produto\ProdutoDao::listarAtivosPorCategoria((int)$id);

            return self::Mensagemjson(
                "Produtos da categoria carregados",
                200,
                $produtos
            );
        } catch (\Throwable $e) {

            return self::Mensagemjson(
                "Erro ao listar produtos da categoria",
                500,
                ["erro" => $e->getMessage()]
            );
        }
    }

    /* ============================================================
     * PEDIDOS (Movidos do PedidoController)
     * ============================================================ */

    public static function criarPedido(): void
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

    public static function listarPedidosPorUsuario(int $usuarioId): void
    {
        $pedidos = PedidoDao::listarPorUsuario($usuarioId);
        self::Mensagemjson("Pedidos do usuário listados", 200, $pedidos);
    }

    public static function buscarPedido(int $pedidoId): void
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

    public static function adicionarItemPedido(): void
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

    public static function atualizarStatusPedido(int $pedidoId, int $statusId): void
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
