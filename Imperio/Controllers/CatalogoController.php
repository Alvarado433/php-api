<?php

namespace Imperio\Controllers;

use Config\Base\Basecontrolador;
use App\Dao\Produto\ProdutoDao;

class CatalogoController extends Basecontrolador
{
    /**
     * 🔹 Listagem do catálogo com filtros
     * categoria | preco_min | preco_max
     */
    public function listar(): void
    {
        $categoriaId = $_GET['categoria'] ?? null;
        $precoMin    = $_GET['preco_min'] ?? null;
        $precoMax    = $_GET['preco_max'] ?? null;

        $produtos = ProdutoDao::listarCatalogo();

        if ($categoriaId !== null) {
            $produtos = array_filter($produtos, function ($produto) use ($categoriaId) {
                return isset($produto['categoria_id']) && (int)$produto['categoria_id'] === (int)$categoriaId;
            });
        }

        if ($precoMin !== null) {
            $produtos = array_filter($produtos, function ($produto) use ($precoMin) {
                return isset($produto['preco']) && (float)$produto['preco'] >= (float)$precoMin;
            });
        }

        if ($precoMax !== null) {
            $produtos = array_filter($produtos, function ($produto) use ($precoMax) {
                return isset($produto['preco']) && (float)$produto['preco'] <= (float)$precoMax;
            });
        }

        $produtos = array_values($produtos);

        self::Mensagemjson(
            "Catálogo carregado com sucesso",
            200,
            [
                "total" => count($produtos),
                "filtros" => [
                    "categoria" => $categoriaId,
                    "preco_min" => $precoMin,
                    "preco_max" => $precoMax
                ],
                "produtos" => $produtos
            ]
        );
    }

    /**
     * 🔥 Unificar produtos em uma categoria
     * POST /catalogo/unificar
     * body JSON:
     * {
     *   "categoria_id": 1,
     *   "produtos": [5, 9, 12]
     * }
     */
   
}
