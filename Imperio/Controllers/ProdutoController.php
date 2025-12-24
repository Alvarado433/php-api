<?php

namespace Imperio\Controllers;

use App\Dao\Produto\ProdutoDao;
use Core\Upload\ServidorUpload;
use Config\Base\Basecontrolador;

class ProdutoController extends Basecontrolador
{
    /**
     * Lista todos os produtos
     */
    public function listar(): void
    {
        try {
            $produtos = ProdutoDao::Todos();
            self::Mensagemjson("Produtos listados com sucesso", 200, $produtos);
        } catch (\Throwable $th) {
            self::Mensagemjson("Erro ao listar produtos: " . $th->getMessage(), 500);
        }
    }

    /**
     * Busca um produto pelo ID
     */
    public function buscar(int $id): void
    {
        try {
            $produto = ProdutoDao::buscar($id);

            if (!$produto) {
                self::Mensagemjson("Produto não encontrado", 404);
                return;
            }

            self::Mensagemjson("Produto encontrado", 200, $produto);
        } catch (\Throwable $th) {
            self::Mensagemjson("Erro ao buscar produto: " . $th->getMessage(), 500);
        }
    }

    /**
     * Busca um produto pelo slug
     */
    public function buscarPorSlug(string $slug): void
    {
        try {
            $produto = ProdutoDao::buscarPorSlug($slug);

            if (!$produto) {
                self::Mensagemjson("Produto não encontrado", 404);
                return;
            }

            // força tipos:
            if (isset($produto['preco'])) {
                $produto['preco'] = (float) $produto['preco'];
            } else {
                $produto['preco'] = 0.0;
            }

            if (isset($produto['estoque'])) {
                $produto['estoque'] = (int) $produto['estoque'];
            } else {
                $produto['estoque'] = 0;
            }

            if (!isset($produto['ilimitado'])) {
                $produto['ilimitado'] = 0;
            } else {
                $produto['ilimitado'] = (int) $produto['ilimitado'];
            }

            self::Mensagemjson("Produto encontrado", 200, $produto);
        } catch (\Throwable $th) {
            self::Mensagemjson("Erro ao buscar produto pelo slug: " . $th->getMessage(), 500);
        }
    }


    /**
     * Cria um novo produto
     */
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

            if ($ok) {
                self::Mensagemjson("Produto criado com sucesso", 201);
            } else {
                self::Mensagemjson("Erro ao criar produto", 500);
            }
        } catch (\Throwable $th) {
            self::Mensagemjson("Erro ao criar produto: " . $th->getMessage(), 500);
        }
    }

    /**
     * Atualiza um produto pelo ID
     */
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

            if ($ok) {
                self::Mensagemjson("Produto atualizado com sucesso", 200);
            } else {
                self::Mensagemjson("Erro ao atualizar produto", 500);
            }
        } catch (\Throwable $th) {
            self::Mensagemjson("Erro ao atualizar produto: " . $th->getMessage(), 500);
        }
    }

    /**
     * Deleta um produto pelo ID
     */
    public function deletar(int $id): void
    {
        try {
            $ok = ProdutoDao::deletar($id);

            if ($ok) {
                self::Mensagemjson("Produto deletado com sucesso", 200);
            } else {
                self::Mensagemjson("Erro ao deletar produto", 500);
            }
        } catch (\Throwable $th) {
            self::Mensagemjson("Erro ao deletar produto: " . $th->getMessage(), 500);
        }
    }

    /**
     * Pesquisa produtos pelo nome
     */
    public function pesquisar(): void
    {
        try {
            $termo = $_GET['q'] ?? '';

            if (empty($termo)) {
                self::Mensagemjson("Nenhum termo informado para pesquisa", 400);
                return;
            }

            $produtos = ProdutoDao::buscarPorNome($termo);

            self::Mensagemjson(count($produtos) . " resultado(s) encontrado(s)", 200, $produtos);
        } catch (\Throwable $th) {
            self::Mensagemjson("Erro ao pesquisar produtos: " . $th->getMessage(), 500);
        }
    }

    /**
     * Lista produtos com catálogo
     */
    public function listarCatalogo(): void
    {
        try {
            $produtos = ProdutoDao::listarCatalogo();
            self::Mensagemjson("Produtos em catálogo listados com sucesso", 200, $produtos);
        } catch (\Throwable $th) {
            self::Mensagemjson("Erro ao listar catálogo: " . $th->getMessage(), 500);
        }
    }
}
