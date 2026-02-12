<?php
/* REGRA 

    [1)nao muda o que esta feito]
    [2] implementa as novasmudanças solicitadas
    [3] mantenha essa mensagem ok
*/

namespace Imperio\Controllers;

use App\Dao\Cupom\CupomDao;
use App\Models\BannerModel;

// MODELS
use App\Dao\Banner\BannerDao;
use App\Dao\Status\StatusDao;
use App\Dao\Cupom\CupomTipoDao;
use App\Dao\Produto\ProdutoDao;
use Core\Upload\ServidorUpload;
use App\Models\Cupom\CupomModel;
use Config\Base\Basecontrolador;
use App\Dao\Carrinho\CarrinhoDao;
use App\Dao\Categoria\CategoriaDao;
use App\Models\Cupom\CupomTipoModel;
use App\Dao\Carrinho\CarrinhoItemDao;
use App\Dao\Produto\ProdutoDestaqueDao;
use App\Dao\ConfiguracaoDao\ConfiguracaoLoginDao;

// DAOs


class DashboardController extends Basecontrolador
{
    // ======================================================
    // ⚙️ CARDS DE CONFIGURAÇÃO
    // ======================================================
    public function listarTodasConfiguracoesLogin(): void
    {
        self::info("Dashboard: carregando todas as configurações de login");

        $configs = \App\Dao\ConfiguracaoDao\ConfiguracaoLoginDao::listar();
        self::info("Dashboard: " . count($configs) . " configurações encontradas");

        // Transforma objetos em arrays para JSON
        $dados = array_map(fn($cfg) => [
            "id" => $cfg->getId(),
            "titulo" => $cfg->getTitulo(),
            "logo" => $cfg->getLogo(),
            "fundo" => $cfg->getFundo(),
            "mensagem_personalizada" => $cfg->getMensagemPersonalizada(),
            "tipo_login_id" => $cfg->getTipoLoginId(),
            "statusid" => $cfg->getStatusId(),
            "criado" => $cfg->getCriado(),
            "atualizado" => $cfg->getAtualizado()
        ], $configs);

        self::success("Configurações de login carregadas com sucesso");
        self::Mensagemjson("Configurações de login carregadas com sucesso", 200, $dados);
    }

    // ======================================================
    // 🛒 CARRINHOS (ADMIN)
    // ======================================================
    // Dentro de DashboardController
    public function listarCarrinhos(): void
    {
        self::info("Carrinho: listando todos os carrinhos");

        // Pega todos os carrinhos
        $carrinhos = \App\Dao\Carrinho\CarrinhoDao::listarTodos();

        $dados = [];

        foreach ($carrinhos as $carrinho) {
            // Pega os itens do carrinho
            $itens = \App\Dao\Carrinho\CarrinhoItemDao::listarPorCarrinho($carrinho['id_carrinho']);

            $dados[] = [
                "id_carrinho" => $carrinho['id_carrinho'],
                "usuario_id" => $carrinho['usuario_id'],
                "itens" => array_map(fn($item) => [
                    "id_item" => $item['id_item'],
                    "produto_id" => $item['produto_id'],
                    "nome_produto" => $item['nome_produto'],
                    "imagem" => $item['imagem'],
                    "quantidade" => $item['quantidade'],
                    "preco_unitario" => $item['preco_unitario']
                ], $itens)
            ];
        }

        self::success(count($dados) . " carrinhos carregados com itens");
        self::Mensagemjson("Carrinhos carregados com sucesso", 200, $dados);
    }


    // ======================================================
    // 📦 PRODUTOS
    // ======================================================
    public function listartudo(): void
    {
        self::info("Produto: listando todos os produtos");
        $produtos = ProdutoDao::listarTodos();
        self::success(count($produtos) . " produtos carregados");
        self::Mensagemjson("Produtos carregados com sucesso", 200, $produtos);
    }

    public static function criarProdutoInterno(array $dados, ?array $arquivo = null): void
    {
        try {
            self::info("Produto: iniciando criação");

            if ($arquivo) {
                $caminhoImagem = ServidorUpload::upload($arquivo, 'produtos');
                if ($caminhoImagem) {
                    $dados['imagem'] = $caminhoImagem;
                    self::info("Produto: imagem salva em {$caminhoImagem}");
                }
            }

            if (empty($dados['slug']) && !empty($dados['nome'])) {
                $dados['slug'] = self::gerarSlug($dados['nome']);
                self::info("Produto: slug gerado {$dados['slug']}");
            }

            $ok = ProdutoDao::criar($dados);
            if ($ok) {
                self::success("Produto criado com sucesso");
            } else {
                self::error("Erro ao criar produto");
            }

            self::Mensagemjson(
                $ok ? "Produto criado com sucesso" : "Erro ao criar produto",
                $ok ? 201 : 500
            );
        } catch (\Throwable $th) {
            self::error("Produto: erro ao criar - " . $th->getMessage());
            self::Mensagemjson("Erro ao criar produto", 500);
        }
    }

    public function criarProduto(): void
    {
        $dados = $_POST;
        $arquivo = $_FILES['imagem'] ?? null;
        self::info("Produto: recebendo dados do POST");
        self::criarProdutoInterno($dados, $arquivo);
    }

    public function removerProduto(int $id): void
    {
        try {
            self::info("Produto: removendo produto ID {$id}");
            $produto = ProdutoDao::buscar($id);
            if (!$produto) {
                self::warning("Produto não encontrado ID {$id}");
                self::Mensagemjson("Produto não encontrado", 404);
                return;
            }

            if (!empty($produto['imagem'])) {
                $caminho = __DIR__ . '/../../public/' . $produto['imagem'];
                if (file_exists($caminho)) {
                    unlink($caminho);
                    self::info("Produto: imagem removida {$caminho}");
                }
            }

            $destaque = ProdutoDestaqueDao::buscar($id);
            if ($destaque) {
                ProdutoDestaqueDao::deletar($destaque['id_destaque']);
                self::info("Produto: destaque removido ID {$destaque['id_destaque']}");
            }

            ProdutoDao::deletar($id);
            self::success("Produto removido com sucesso");
            self::Mensagemjson("Produto removido com sucesso", 200);
        } catch (\Throwable $th) {
            self::error("Produto: erro ao remover - " . $th->getMessage());
            self::Mensagemjson("Erro ao remover produto", 500);
        }
    }

    // ======================================================
    // 🔹 CATEGORIAS
    // ======================================================
    public static function listarCategorias(): void
    {
        self::info("Categoria: listando categorias");
        $categorias = CategoriaDao::listar();
        self::success(count($categorias) . " categorias carregadas");
        self::Mensagemjson("Categorias carregadas", 200, $categorias);
    }

    public function criarCategoria(): void
    {
        $dados = $_POST;
        $nome = trim($dados['nome'] ?? '');
        self::info("Categoria: criando categoria '{$nome}'");

        if (empty($nome)) {
            self::warning("Categoria: nome obrigatório não fornecido");
            self::Mensagemjson("Nome obrigatório", 422);
            return;
        }

        CategoriaDao::criar(
            $nome,
            $dados['icone'] ?? '',
            (int)($dados['statusid'] ?? ProdutoDao::status('ativo'))
        );

        self::success("Categoria criada com sucesso: {$nome}");
        self::Mensagemjson("Categoria criada com sucesso", 201);
    }

    public function deletarCategoria(int $id): void
    {
        try {
            self::info("Categoria: removendo categoria ID {$id}");
            ProdutoDao::removerCategoriaDosProdutos($id);
            CategoriaDao::deletar($id);
            self::success("Categoria excluída com sucesso");
            self::Mensagemjson("Categoria excluída com sucesso", 200);
        } catch (\Throwable $th) {
            self::error("Categoria: erro ao remover - " . $th->getMessage());
            self::Mensagemjson("Erro ao remover categoria", 500);
        }
    }

    // ======================================================
    // ⭐ PRODUTOS EM DESTAQUE
    // ======================================================
    public function listarAtivos(): void
    {
        self::info("ProdutoDestaque: listando destaques");
        $destaques = ProdutoDestaqueDao::listarAtivos();
        self::success(count($destaques) . " destaques carregados");
        self::Mensagemjson("Destaques carregados", 200, $destaques);
    }

    public function criarProdutoDestaque(): void
    {
        $dados = json_decode(file_get_contents("php://input"), true);
        $produtoId = (int)($dados['produto_id'] ?? 0);
        self::info("ProdutoDestaque: adicionando destaque ao produto ID {$produtoId}");

        $idDestaque = ProdutoDestaqueDao::criar([
            'produto_id' => $produtoId,
            'statusid' => ProdutoDao::status('destaque'),
            'ordem' => 1
        ]);

        ProdutoDao::atualizar($produtoId, ['destaque' => $idDestaque]);
        self::success("ProdutoDestaque: produto ID {$produtoId} adicionado ao destaque");
        self::Mensagemjson("Produto adicionado ao destaque", 201);
    }

    public function removerProdutoDestaque(int $id): void
    {
        ProdutoDestaqueDao::deletar($id);
        self::info("ProdutoDestaque: destaque ID {$id} removido");
        self::Mensagemjson("Produto removido do destaque", 200);
    }

    // ======================================================
    // 🖼 BANNERS
    // ======================================================
    public function listarBanners(): void
    {
        self::info("Banner: listando banners");
        $banners = BannerDao::listar();
        $lista = array_map(fn($b) => $b->toArray(), $banners);
        self::success(count($lista) . " banners carregados");
        self::Mensagemjson("Banners carregados", 200, $lista);
    }

    public function criarBanner(): void
    {
        $arquivo = $_FILES['imagem'] ?? null;
        $dados = $_POST;
        self::info("Banner: criando banner '{$dados['titulo']}'");

        $imagem = ServidorUpload::upload($arquivo, 'banners');
        self::info("Banner: imagem salva em {$imagem}");

        BannerDao::criar([
            'titulo' => $dados['titulo'],
            'descricao' => $dados['descricao'] ?? '',
            'imagem' => $imagem,
            'link' => $dados['link'] ?? null,
            'statusid' => ProdutoDao::status('ativo')
        ]);

        self::success("Banner criado com sucesso");
        self::Mensagemjson("Banner criado com sucesso", 201);
    }

    public function removerBanner(int $id): void
    {
        BannerDao::deletar($id);
        self::info("Banner: banner ID {$id} removido");
        self::Mensagemjson("Banner removido com sucesso", 200);
    }

    // ======================================================
    // 🔵 STATUS
    // ======================================================
    public function listarStatus(): void
    {
        self::info("Status: listando status");
        $status = StatusDao::listar();
        self::success(count($status) . " status carregados");
        self::Mensagemjson("Status carregados", 200, $status);
    }

    // ======================================================
    // 🛒 CATÁLOGO
    // ======================================================
    public function marcarCatalogoSim(int $id): void
    {
        ProdutoDao::atualizar($id, ['catalogo' => ProdutoDao::status('catalogo_sim')]);
        self::info("Catálogo: produto ID {$id} marcado como catálogo");
        self::Mensagemjson("Produto marcado como catálogo", 200);
    }

    public function marcarCatalogoNao(int $id): void
    {
        ProdutoDao::atualizar($id, ['catalogo' => ProdutoDao::status('catalogo_nao')]);
        self::info("Catálogo: produto ID {$id} removido do catálogo");
        self::Mensagemjson("Produto removido do catálogo", 200);
    }

    public function listarCatalogo(): void
    {
        self::info("Catálogo: listando produtos do catálogo");
        $produtos = ProdutoDao::listarCatalogo();
        self::success(count($produtos) . " produtos no catálogo");
        self::Mensagemjson("Catálogo carregado", 200, $produtos);
    }

    // ======================================================
    // 🎟 CUPONS
    // ======================================================
    public function listarCupons(): void
    {
        self::info("Cupom: listando cupons");
        $cupons = CupomDao::listar();
        self::success(count($cupons) . " cupons carregados");
        self::Mensagemjson("Cupons carregados", 200, $cupons);
    }

    public function listarCuponsAtivos(): void
    {
        self::info("Cupom: listando cupons ativos");
        $cupons = CupomDao::listarAtivos();
        self::success(count($cupons) . " cupons ativos carregados");
        self::Mensagemjson("Cupons ativos carregados", 200, $cupons);
    }

    public function listarCuponsInativos(): void
    {
        self::info("Cupom: listando cupons inativos");
        $cupons = CupomDao::listarInativos();
        self::success(count($cupons) . " cupons inativos carregados");
        self::Mensagemjson("Cupons inativos carregados", 200, $cupons);
    }

    public function criarCupom(): void
    {
        try {
            $dados = self::receberJson();
            self::info("Cupom: criando cupom '{$dados['codigo']}'");

            $publico = 0; // valor inicial

            $cupom = new CupomModel(
                null,
                $dados['codigo'],
                $dados['descricao'] ?? '',
                (int)$dados['tipo_id'],
                $dados['desconto'],
                $dados['valor_minimo'] ?? 0,
                $dados['limite_uso'] ?? null,
                $dados['inicio'] ?? null,
                $dados['expiracao'] ?? null,
                $dados['statusid'] ?? 1,
                $publico
            );

            CupomDao::criar($cupom);
            self::success("Cupom criado: {$dados['codigo']}");
            self::Mensagemjson("Cupom criado com sucesso", 201);
        } catch (\Throwable $th) {
            self::error("Cupom: erro ao criar - " . $th->getMessage());
            self::Mensagemjson("Erro ao criar cupom", 500);
        }
    }

    public function atualizarCupom(int $id): void
    {
        try {
            $dados = self::receberJson();
            self::info("Cupom: atualizando cupom ID {$id}");

            $cupom = CupomDao::buscarPorId($id);
            if (!$cupom) {
                self::warning("Cupom ID {$id} não encontrado");
                self::Mensagemjson("Cupom não encontrado", 404);
                return;
            }

            $modelo = new CupomModel(
                $cupom['id_cupom'],
                $cupom['codigo'],
                $dados['descricao'] ?? $cupom['descricao'],
                $dados['tipo_id'] ?? $cupom['tipo_id'],
                $dados['desconto'] ?? $cupom['desconto'],
                $dados['valor_minimo'] ?? $cupom['valor_minimo'],
                $dados['limite_uso'] ?? $cupom['limite_uso'],
                $dados['inicio'] ?? $cupom['inicio'],
                $dados['expiracao'] ?? $cupom['expiracao'],
                $dados['statusid'] ?? $cupom['statusid'],
                $dados['publico'] ?? $cupom['publico']
            );

            CupomDao::atualizar($modelo);
            self::success("Cupom ID {$id} atualizado");
            self::Mensagemjson("Cupom atualizado com sucesso", 200);
        } catch (\Throwable $th) {
            self::error("Cupom: erro ao atualizar - " . $th->getMessage());
            self::Mensagemjson("Erro ao atualizar cupom", 500);
        }
    }

    public function alterarStatusCupom(int $id, int $statusid): void
    {
        CupomDao::alterarStatus($id, $statusid);
        self::info("Cupom: status do cupom ID {$id} alterado para {$statusid}");
        self::Mensagemjson("Status do cupom alterado", 200);
    }

    public function deletarCupom(int $id): void
    {
        try {
            self::info("Cupom: removendo cupom ID {$id}");
            $cupom = CupomDao::buscarPorId($id);

            if (!$cupom) {
                self::warning("Cupom ID {$id} não encontrado");
                self::Mensagemjson("Cupom não encontrado", 404);
                return;
            }

            CupomDao::deletar($id);
            self::success("Cupom ID {$id} removido com sucesso");
            self::Mensagemjson("Cupom removido com sucesso", 200);
        } catch (\Throwable $th) {
            self::error("Cupom: erro ao remover - " . $th->getMessage());
            self::Mensagemjson("Erro interno ao remover cupom", 500);
        }
    }

    // ======================================================
    // 🎟 CUPOM TIPOS
    // ======================================================
    public function listarCupomTipos(): void
    {
        try {
            self::info("CupomTipo: listando tipos de cupom");
            $tipos = \App\Dao\Cupom\CupomTipoDao::listar();
            self::success(count($tipos) . " tipos de cupom carregados");
            self::Mensagemjson("Tipos de cupom carregados com sucesso", 200, $tipos);
        } catch (\Throwable $th) {
            self::error("CupomTipo: erro ao listar - " . $th->getMessage());
            self::Mensagemjson("Erro ao listar tipos de cupom", 500);
        }
    }
    // ======================================================
    // 🎟 CUPOM TIPOS
    // ======================================================
    public function criarCupomTipo(): void
    {
        try {
            $dados = self::receberJson(); // recebe JSON do React
            self::info("CupomTipo: criando novo tipo '{$dados['nome']}'");

            if (empty($dados['nome']) || empty($dados['codigo'])) {
                self::warning("CupomTipo: nome ou código obrigatório não fornecido");
                self::Mensagemjson("Nome e código são obrigatórios", 422);
                return;
            }

            $tipo = new CupomTipoModel(
                null,                  // id_tipo
                $dados['nome'],        // nome
                $dados['codigo'],      // codigo
                $dados['descricao'] ?? '',  // descricao
                $dados['statusid'] ?? 1      // statusid
            );

            $ok = \App\Dao\Cupom\CupomTipoDao::criar($tipo);

            if ($ok) {
                self::success("Tipo de cupom criado: {$dados['nome']}");
                self::Mensagemjson("Tipo de cupom criado com sucesso", 201);
            } else {
                self::error("Erro ao criar tipo de cupom");
                self::Mensagemjson("Erro ao criar tipo de cupom", 500);
            }
        } catch (\Throwable $th) {
            self::error("CupomTipo: erro ao criar - " . $th->getMessage());
            self::Mensagemjson("Erro ao criar tipo de cupom", 500);
        }
    }
    // ======================================================
    // 📂 MENU (ADMIN)
    // ======================================================
    public function listarMenus(): void
    {
        try {
            self::info("Menu (Admin): listando menus com itens (JOIN)");

            $rows = \App\Dao\Menu\MenuDao::listarComItens();

            $menus = [];

            foreach ($rows as $row) {
                $menuId = $row["id_menu"];

                // 🔹 Se menu ainda não existe no array, cria
                if (!isset($menus[$menuId])) {
                    $menus[$menuId] = [
                        "id_menu" => $menuId,
                        "nome" => $row["menu_nome"],
                        "icone" => $row["menu_icone"],
                        "rota" => $row["menu_rota"],
                        "pesquisa_placeholder" => $row["pesquisa_placeholder"],
                        "itens" => []
                    ];
                }

                // 🔹 Se existir item, adiciona
                if (!empty($row["item_nome"])) {
                    $menus[$menuId]["itens"][] = [
                        "id_item" => $row["id_item"],
                        "nome" => $row["item_nome"],
                        "icone" => $row["item_icone"],
                        "rota" => $row["item_rota"],
                        "posicao" => $row["posicao"]
                    ];
                }
            }

            // 🔹 Remove índice associativo
            $dados = array_values($menus);

            self::success(count($dados) . " menus carregados com itens");
            self::Mensagemjson(
                "Menus carregados com sucesso",
                200,
                $dados
            );
        } catch (\Throwable $th) {
            self::error("Menu (Admin): erro ao listar - " . $th->getMessage());

            self::Mensagemjson(
                "Erro ao listar menus",
                500
            );
        }
    }
    public function buscarCategoria(int $id): void
    {
        try {
            self::info("Categoria (Admin): buscando categoria ID {$id}");
            $cat = \App\Dao\Categoria\CategoriaDao::buscar($id);

            if (!$cat) {
                self::warning("Categoria (Admin): não encontrada ID {$id}");
                self::Mensagemjson("Categoria não encontrada", 404);
                return;
            }

            // Se você quiser devolver total_produtos igual no front:
            // (caso sua query já traga, ignore)
            if (!isset($cat['total_produtos'])) {
                // fallback simples: conta no produto
                $produtos = ProdutoDao::Todos();
                $total = 0;
                foreach ($produtos as $p) {
                    if ((int)($p['categoria_id'] ?? 0) === (int)$id) $total++;
                }
                $cat['total_produtos'] = $total;
            }

            self::success("Categoria (Admin): carregada");
            self::Mensagemjson("Categoria carregada", 200, $cat);
        } catch (\Throwable $th) {
            self::error("Categoria (Admin): erro - " . $th->getMessage());
            self::Mensagemjson("Erro ao buscar categoria", 500);
        }
    }

    public function unificarProdutosEmCategoria(): void
    {
        try {
            self::info("Unificação: recebendo request para unificar produtos em categoria");

            $dados = self::receberJson();

            $categoriaId = (int)($dados['categoria_id'] ?? 0);
            $produtos = $dados['produtos'] ?? [];

            if ($categoriaId <= 0) {
                self::warning("Unificação: categoria_id inválido");
                self::Mensagemjson("categoria_id inválido", 422);
                return;
            }

            if (!is_array($produtos) || count($produtos) === 0) {
                self::warning("Unificação: lista de produtos vazia");
                self::Mensagemjson("Selecione pelo menos um produto", 422);
                return;
            }

            // Sanitiza IDs (garante int e remove lixo/duplicados)
            $produtos = array_values(array_unique(array_map('intval', $produtos)));
            $produtos = array_filter($produtos, fn($id) => $id > 0);

            if (count($produtos) === 0) {
                self::warning("Unificação: lista de produtos inválida após sanitização");
                self::Mensagemjson("Lista de produtos inválida", 422);
                return;
            }

            // (Opcional, mas recomendado) Confere se a categoria existe
            $cat = \App\Dao\Categoria\CategoriaDao::buscar($categoriaId);
            if (!$cat) {
                self::warning("Unificação: categoria não encontrada ID {$categoriaId}");
                self::Mensagemjson("Categoria não encontrada", 404);
                return;
            }

            // Regra do seu front: só deixa selecionar produtos SEM categoria.
            // Então aqui vamos aplicar a mesma regra no back por segurança:
            // Filtra apenas os que ainda estão com categoria_id NULL.
            $todos = ProdutoDao::Todos();
            $permitidos = [];

            foreach ($todos as $p) {
                if (
                    isset($p['id_produto']) &&
                    in_array((int)$p['id_produto'], $produtos, true) &&
                    (empty($p['categoria_id']) || $p['categoria_id'] === null)
                ) {
                    $permitidos[] = (int)$p['id_produto'];
                }
            }

            if (count($permitidos) === 0) {
                self::warning("Unificação: nenhum produto elegível (talvez já tenham categoria)");
                self::Mensagemjson("Nenhum produto elegível para unificação", 422);
                return;
            }

            $ok = ProdutoDao::unificarEmCategoria($categoriaId, $permitidos);

            if ($ok) {
                self::success("Unificação: " . count($permitidos) . " produtos unificados na categoria {$categoriaId}");
                self::Mensagemjson("Produtos unificados com sucesso", 200, [
                    "categoria_id" => $categoriaId,
                    "total_unificados" => count($permitidos),
                    "produtos" => $permitidos
                ]);
                return;
            }

            self::error("Unificação: falha ao atualizar produtos no banco");
            self::Mensagemjson("Erro ao unificar produtos", 500);
        } catch (\Throwable $th) {
            self::error("Unificação: erro - " . $th->getMessage());
            self::Mensagemjson("Erro ao unificar produtos", 500);
        }
    }
}
