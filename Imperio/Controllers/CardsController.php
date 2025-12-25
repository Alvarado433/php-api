<?php

namespace Imperio\Controllers;

use App\Dao\cupom\CupomDao;
use App\Dao\Banner\BannerDao;
use App\Dao\Produto\ProdutoDao;
use Config\Base\Basecontrolador;
use App\Dao\Carrinho\CarrinhoDao;
use App\Dao\UsuarioDao\UsuarioDao;
use App\Dao\Categoria\CategoriaDao;
use App\Dao\ConfiguracaoDao\ConfiguracaoLoginDao;



class CardsController extends Basecontrolador
{
    public function listar(): void
    {
        self::info("Dashboard: carregando cards");

        // Contagem de cada item
        $cards = [
            ["titulo" => "Produtos",   "quantidade" => count(ProdutoDao::Todos())],
            ["titulo" => "Categorias", "quantidade" => count(CategoriaDao::listarAtivas())],
            ["titulo" => "Banners",    "quantidade" => count(BannerDao::Todos())],
            ["titulo" => "Usuários",   "quantidade" => UsuarioDao::contar()],
            ["titulo" => "Cupons Ativos", "quantidade" => count(CupomDao::listarAtivos())],
            ["titulo" => "Carrinhos",  "quantidade" => count(CarrinhoDao::listarTodos())] // vamos criar esse método
        ];

        self::Mensagemjson("Dashboard carregado com sucesso", 200, ["dados" => $cards]);
    }

    public function listarCardsConfiguracao(): void
    {
        self::info("Dashboard: carregando cards de configuração");

        $cards = [
            [
                "titulo" => "Configuração de Login",
                "quantidade" => count(\App\Dao\ConfiguracaoDao\ConfiguracaoLoginDao::listar()),
                "rota" => "/admin/configuracoes/gerenciar/login" // cada card terá uma rota própria
            ],

            // Adicione outras configurações se necessário
        ];

        self::Mensagemjson("Cards de configuração carregados com sucesso", 200, $cards);
    }
}
