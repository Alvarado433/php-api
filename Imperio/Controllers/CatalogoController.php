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
        // 🔹 Parâmetros da URL (?categoria=1&preco_min=100&preco_max=500)
        $categoriaId = $_GET['categoria'] ?? null;
        $precoMin    = $_GET['preco_min'] ?? null;
        $precoMax    = $_GET['preco_max'] ?? null;

        // 🔹 Lista todos os produtos com catálogo
        $produtos = ProdutoDao::listarCatalogo();

        // 🔹 Filtro por categoria (se existir)
        if ($categoriaId !== null) {
            $produtos = array_filter($produtos, function ($produto) use ($categoriaId) {
                return isset($produto['categoria_id']) && (int)$produto['categoria_id'] === (int)$categoriaId;
            });
        }

        // 🔹 Filtro por preço mínimo
        if ($precoMin !== null) {
            $produtos = array_filter($produtos, function ($produto) use ($precoMin) {
                return isset($produto['preco']) && $produto['preco'] >= (float)$precoMin;
            });
        }

        // 🔹 Filtro por preço máximo
        if ($precoMax !== null) {
            $produtos = array_filter($produtos, function ($produto) use ($precoMax) {
                return isset($produto['preco']) && $produto['preco'] <= (float)$precoMax;
            });
        }

        // 🔹 Reindexa o array
        $produtos = array_values($produtos);

        // 🔹 Retorna JSON
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
}
