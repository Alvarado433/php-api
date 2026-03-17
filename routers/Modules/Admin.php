<?php

namespace Routers\Modules;

use Routers\Agrupamento\Groups;

class Admin
{
    public static function rotas()
    {
        Groups::prefix("/admin", function () {

            // ==============================
            // DASHBOARD
            // ==============================

            Groups::get("/", "Admincontroller@index");
            Groups::get("/dashboard", "Admincontroller@index");
            Groups::get("/cards", "Admincontroller@listar");


            // ==============================
            // PRODUTOS
            // ==============================

            Groups::get("/produtos", "Admincontroller@listartudo");
            Groups::post("/produto/criar", "Admincontroller@criarProduto");
            Groups::post("/produto/{id}/atualizar", "Admincontroller@atualizarProduto");
            Groups::get("/produtos/status", "Admincontroller@listarStatus");
            Groups::delete("/produto/{id}/remover", "Admincontroller@removerProduto");
            Groups::post("/produtos/unificar", "Admincontroller@unificarProdutosEmCategoria");


            // ==============================
            // PRODUTOS DESTAQUES
            // ==============================

            Groups::get("/produtos/destaques", "Admincontroller@listarAtivos");
            Groups::post("/produtos/destaques/criar", "Admincontroller@criarProdutoDestaque");
            Groups::delete(
                "/produtos/destaques/{id}/remover",
                "Admincontroller@removerProdutoDestaque"
            );


            // ==============================
            // CATÁLOGO
            // ==============================

            Groups::get("/produtos/catalogo", "Admincontroller@listarCatalogo");
            Groups::put("/produtos/{id}/catalogo/sim", "Admincontroller@marcarCatalogoSim");
            Groups::put("/produtos/{id}/catalogo/nao", "Admincontroller@marcarCatalogoNao");


            // ==============================
            // CATEGORIAS
            // ==============================

            Groups::get("/categorias", "Admincontroller@listarCategorias");
            Groups::get("/categorias/ativas", "Admincontroller@listarCategoriasAtivas");
            Groups::get("/categorias/ordenadas", "Admincontroller@listarCategoriasOrdenadas");
            Groups::get("/categorias/{id}", "Admincontroller@buscarCategoria");
            Groups::post("/categorias", "Admincontroller@criarCategoria");
            Groups::put("/categorias/{id}", "Admincontroller@atualizarCategoria");
            Groups::put("/categorias/{id}/desativar", "Admincontroller@desativarCategoria");
            Groups::delete("/categorias/{id}", "Admincontroller@removerCategoria");


            // ==============================
            // CAMPANHAS
            // ==============================
            // IMPORTANTE:
            // rotas específicas DEVEM vir antes das rotas dinâmicas /{id}

            // listar campanhas
            Groups::get("/campanhas", "Admincontroller@listarCampanhas");

            // campanha ativa por slug
            Groups::get(
                "/campanha/ativa/{slug}",
                "Admincontroller@buscarCampanhaAtivaPorSlug"
            );

            // destaques da campanha nível 9
            Groups::get(
                "/campanha/destaques",
                "Admincontroller@listarDestaquesNivel9"
            );

            // buscar campanha por id
            Groups::get("/campanha/{id}", "Admincontroller@buscarCampanha");

            // criar campanha
            Groups::post("/campanhas", "Admincontroller@criarCampanha");

            // atualizar campanha
            Groups::put("/campanha/{id}", "Admincontroller@atualizarCampanha");

            // remover campanha
            Groups::delete("/campanhas/{id}", "Admincontroller@removerCampanha");


            // ==============================
            // CAMPANHA PRODUTOS
            // ==============================

            // listar produtos da campanha
            Groups::get(
                "/campanha/{id}/produtos",
                "Admincontroller@listarProdutosDaCampanha"
            );

            // vincular produtos na campanha
            Groups::post(
                "/campanha/{id}/produtos",
                "Admincontroller@vincularProdutosNaCampanha"
            );

            // remover produto da campanha
            Groups::delete(
                "/campanha/{id}/produto/{produtoId}/remover",
                "Admincontroller@removerProdutoDaCampanha"
            );

            // limpar campanha
            Groups::delete(
                "/campanha/{id}/limpar",
                "Admincontroller@limparCampanhaProdutos"
            );


            // ==============================
            // IMAGENS DO PRODUTO
            // ==============================

            Groups::get(
                "/produto/{id}/imagens",
                "Admincontroller@listarImagensProduto"
            );

            Groups::post(
                "/produto/{id}/imagens",
                "Admincontroller@adicionarImagensProduto"
            );

            Groups::delete(
                "/produto/imagem/{id}/remover",
                "Admincontroller@removerImagemProduto"
            );

            Groups::put(
                "/produto/{id}/imagem/principal",
                "Admincontroller@definirImagemPrincipalProduto"
            );


            // ==============================
            // AUTENTICAÇÃO
            // ==============================

            Groups::auth();
        });
    }
}