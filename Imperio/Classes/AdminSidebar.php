<?php

namespace Imperio\Classes;

use App\Dao\Banner\BannerDao;
use App\Dao\Campanha\CampanhaDao;
use App\Dao\Carrinho\CarrinhoDao;
use App\Dao\Categoria\CategoriaDao;
use App\Dao\Cupom\CupomDao;
use App\Dao\Produto\ProdutoDao;
use App\Dao\UsuarioDao\UsuarioDao;

class AdminSidebar
{
    public static function getMenu(): array
    {
        return [
            [
                "type"  => "link",
                "label" => "Dashboard",
                "href"  => "/painel",
                "icon"  => "fa-solid fa-chart-line",
                "match" => "/painel",
            ],
            [
                "type"  => "link",
                "label" => "Emails",
                "href"  => "/painel/email",
                "icon"  => "fa-solid fa-envelope",
                "match" => "/email",
            ],

            [
                "type"  => "group",
                "label" => "Gestão",
                "icon"  => "fa-solid fa-grid-2",
                "children" => [
                    [
                        "label" => "Usuários",
                        "href"  => "/painel/usuarios",
                        "icon"  => "fa-solid fa-users",
                        "match" => "/usuarios",
                    ],
                    [
                        "label" => "Banners",
                        "href"  => "/painel/banners",
                        "icon"  => "fa-solid fa-image",
                        "match" => "/banners",
                    ],
                ],
            ],

            [
                "type"  => "group",
                "label" => "Catálogo",
                "icon"  => "fa-solid fa-boxes-stacked",
                "children" => [
                    [
                        "label" => "Produtos",
                        "href"  => "/painel/produtos",
                        "icon"  => "fa-solid fa-box",
                        "match" => "/produtos",
                    ],
                    [
                        "label" => "adicionar Produtos",
                        "href"  => "/painel/produtos/adicionar",
                        "icon"  => "fa-solid fa-box",
                        "match" => "/produtos/adicionar",
                    ],
                    [
                        "label" => "Categorias",
                        "href"  => "/painel/categorias",
                        "icon"  => "fa-solid fa-tags",
                        "match" => "/categorias",
                    ],
                    [
                        "label" => "Campanhas",
                        "href"  => "/painel/campanhas",
                        "icon"  => "fa-solid fa-bullhorn",
                        "match" => "/campanhas",
                    ],
                ],
            ],
        ];
    }

    public static function getCards(): array
    {
        return [

            [
                "titulo" => "Produtos",
                "quantidade" => count(ProdutoDao::Todos())
            ],

            [
                "titulo" => "Categorias",
                "quantidade" => count(CategoriaDao::listarAtivas())
            ],

            [
                "titulo" => "Banners",
                "quantidade" => count(BannerDao::Todos())
            ],

            [
                "titulo" => "Usuários",
                "quantidade" => UsuarioDao::contar()
            ],

            [
                "titulo" => "Cupons Ativos",
                "quantidade" => count(CupomDao::listarAtivos())
            ],

            [
                "titulo" => "Carrinhos",
                "quantidade" => count(CarrinhoDao::listarTodos())
            ],

            [
                "titulo" => "Imagens Galeria",
                "quantidade" => ProdutoDao::contarImagensGaleria(true)
            ],

            [
                "titulo" => "Campanhas",
                "quantidade" => count(CampanhaDao::listar())
            ],

        ];
    }
}
