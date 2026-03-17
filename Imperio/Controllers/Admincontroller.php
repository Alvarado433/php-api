<?php

namespace Imperio\Controllers;

use App\Dao\Banner\BannerDao;
use App\Dao\Campanha\CampanhaDao;
use App\Dao\Carrinho\CarrinhoDao;
use App\Dao\Categoria\CategoriaDao;
use App\Dao\Cupom\CupomDao;
use App\Dao\Produto\ProdutoDao;
use App\Dao\Produto\ProdutoDestaqueDao;
use App\Dao\Status\StatusDao;
use App\Dao\UsuarioDao\UsuarioDao;
use Config\Base\Basecontrolador;
use Core\Email\EmailService;
use Core\Upload\ServidorUpload;
use Imperio\Classes\AdminSidebar;

class Admincontroller extends Basecontrolador
{
    public function index(): void
    {
        self::info("Dashboard: carregando sidebar (com grupos)");

        $sidebar = AdminSidebar::getMenu();

        self::Mensagemjson(
            "Dashboard carregado com sucesso",
            200,
            ["dados" => $sidebar]
        );
    }

    public function listar(): void
    {
        self::info("Dashboard: carregando cards");

        try {

            $cards = AdminSidebar::getCards();

            self::Mensagemjson(
                "Dashboard carregado com sucesso",
                200,
                ["dados" => $cards]
            );
        } catch (\Throwable $th) {

            self::error("Dashboard: erro ao carregar cards - " . $th->getMessage());

            self::Mensagemjson(
                "Erro ao carregar dashboard",
                500
            );
        }
    }

    public function listartudo(): void
    {
        self::info("Produto: listando todos os produtos");
        $produtos = ProdutoDao::listarTodos();
        self::success(count($produtos) . " produtos carregados");
        self::Mensagemjson("Produtos carregados com sucesso", 200, $produtos);
    }
    public function listarStatus(): void
    {
        self::info("Status: listando status");
        $status = StatusDao::listar();
        self::success(count($status) . " status carregados");
        self::Mensagemjson("Status carregados", 200, $status);
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

    public function marcarCatalogoSim(int $id): void
    {
        ProdutoDao::atualizar($id, ['catalogo' => 1]);
        self::info("Catálogo: produto ID {$id} marcado como catálogo");
        self::Mensagemjson("Produto marcado como catálogo", 200);
    }

    public function marcarCatalogoNao(int $id): void
    {
        ProdutoDao::atualizar($id, ['catalogo' => 0]);
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

    // =========================
    // ✅ CATEGORIAS (ADMIN)
    // =========================

    public function listarCategorias(): void
    {
        try {
            self::info("Categoria: listando todas");
            $categorias = CategoriaDao::listar(); // inclui contagem de produtos
            self::success(count($categorias) . " categorias carregadas");
            self::Mensagemjson("Categorias carregadas", 200, $categorias);
        } catch (\Throwable $th) {
            self::error("Categoria: erro ao listar - " . $th->getMessage());
            self::Mensagemjson("Erro ao listar categorias", 500);
        }
    }

    public function listarCategoriasAtivas(): void
    {
        try {
            self::info("Categoria: listando ativas");
            $categorias = CategoriaDao::listarAtivas();
            self::success(count($categorias) . " categorias ativas carregadas");
            self::Mensagemjson("Categorias ativas carregadas", 200, $categorias);
        } catch (\Throwable $th) {
            self::error("Categoria: erro ao listar ativas - " . $th->getMessage());
            self::Mensagemjson("Erro ao listar categorias ativas", 500);
        }
    }

    public function listarCategoriasOrdenadas(): void
    {
        try {
            self::info("Categoria: listando ordenadas (menu/home)");
            $categorias = CategoriaDao::listarOrdenadas();
            self::success(count($categorias) . " categorias ordenadas carregadas");
            self::Mensagemjson("Categorias ordenadas carregadas", 200, $categorias);
        } catch (\Throwable $th) {
            self::error("Categoria: erro ao listar ordenadas - " . $th->getMessage());
            self::Mensagemjson("Erro ao listar categorias ordenadas", 500);
        }
    }

    public function buscarCategoria(int $id): void
    {
        try {
            self::info("Categoria: buscando ID {$id}");
            $cat = CategoriaDao::buscar($id);

            if (!$cat) {
                self::warning("Categoria não encontrada ID {$id}");
                self::Mensagemjson("Categoria não encontrada", 404);
                return;
            }

            self::Mensagemjson("Categoria encontrada", 200, $cat);
        } catch (\Throwable $th) {
            self::error("Categoria: erro ao buscar - " . $th->getMessage());
            self::Mensagemjson("Erro ao buscar categoria", 500);
        }
    }

    public function criarCategoria(): void
    {
        try {
            $dados = json_decode(file_get_contents("php://input"), true) ?? [];

            $nome = trim((string)($dados["nome"] ?? ""));
            $icone = trim((string)($dados["icone"] ?? ""));
            $statusid = (int)($dados["statusid"] ?? 1);

            if ($nome === "") {
                self::Mensagemjson("Informe o nome da categoria", 422);
                return;
            }

            if ($icone === "") {
                // se quiser permitir vazio, remova esse if
                self::Mensagemjson("Informe o ícone da categoria", 422);
                return;
            }

            self::info("Categoria: criando '{$nome}'");
            $ok = CategoriaDao::criar($nome, $icone, $statusid);

            self::Mensagemjson(
                $ok ? "Categoria criada com sucesso" : "Erro ao criar categoria",
                $ok ? 201 : 500
            );
        } catch (\Throwable $th) {
            self::error("Categoria: erro ao criar - " . $th->getMessage());
            self::Mensagemjson("Erro ao criar categoria", 500);
        }
    }

    public function atualizarCategoria(int $id): void
    {
        try {
            $dados = json_decode(file_get_contents("php://input"), true) ?? [];

            $cat = CategoriaDao::buscar($id);
            if (!$cat) {
                self::warning("Categoria não encontrada ID {$id}");
                self::Mensagemjson("Categoria não encontrada", 404);
                return;
            }

            // ✅ usa valores antigos como fallback
            $nome = trim((string)($dados["nome"] ?? $cat["nome"] ?? ""));
            $icone = trim((string)($dados["icone"] ?? $cat["icone"] ?? ""));
            $statusid = (int)($dados["statusid"] ?? $cat["statusid"] ?? 1);

            if ($nome === "") {
                self::Mensagemjson("Nome inválido", 422);
                return;
            }

            if ($icone === "") {
                self::Mensagemjson("Ícone inválido", 422);
                return;
            }

            self::info("Categoria: atualizando ID {$id}");
            $ok = CategoriaDao::atualizar($id, $nome, $icone, $statusid);

            self::Mensagemjson(
                $ok ? "Categoria atualizada com sucesso" : "Erro ao atualizar categoria",
                $ok ? 200 : 500
            );
        } catch (\Throwable $th) {
            self::error("Categoria: erro ao atualizar - " . $th->getMessage());
            self::Mensagemjson("Erro ao atualizar categoria", 500);
        }
    }

    public function desativarCategoria(int $id): void
    {
        try {
            $cat = CategoriaDao::buscar($id);
            if (!$cat) {
                self::warning("Categoria não encontrada ID {$id}");
                self::Mensagemjson("Categoria não encontrada", 404);
                return;
            }

            self::info("Categoria: desativando ID {$id}");
            $ok = CategoriaDao::desativar($id);

            self::Mensagemjson(
                $ok ? "Categoria desativada com sucesso" : "Erro ao desativar categoria",
                $ok ? 200 : 500
            );
        } catch (\Throwable $th) {
            self::error("Categoria: erro ao desativar - " . $th->getMessage());
            self::Mensagemjson("Erro ao desativar categoria", 500);
        }
    }

    public function removerCategoria(int $id): void
    {
        try {
            $cat = CategoriaDao::buscar($id);
            if (!$cat) {
                self::warning("Categoria não encontrada ID {$id}");
                self::Mensagemjson("Categoria não encontrada", 404);
                return;
            }

            // ✅ 1) Desvincula os produtos dessa categoria (evita erro de FK)
            self::info("Categoria: removendo vínculo dos produtos (categoria_id={$id})");
            $okVinculo = \App\Dao\Produto\ProdutoDao::removerCategoriaDosProdutos($id);

            if (!$okVinculo) {
                self::warning("Categoria: falha ao desvincular produtos (categoria_id={$id})");
                self::Mensagemjson("Erro ao desvincular produtos da categoria", 500);
                return;
            }

            // ✅ 2) Agora pode deletar a categoria sem estourar 500
            self::info("Categoria: deletando ID {$id}");
            $ok = CategoriaDao::deletar($id);

            self::Mensagemjson(
                $ok ? "Categoria removida com sucesso" : "Erro ao remover categoria",
                $ok ? 200 : 500
            );
        } catch (\Throwable $th) {
            self::error("Categoria: erro ao remover - " . $th->getMessage());
            self::Mensagemjson("Erro ao remover categoria", 500);
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
    public function listarBanners(): void
    {
        self::info("Banner: listando banners");
        $banners = BannerDao::listar();
        $lista = array_map(fn($b) => $b->toArray(), $banners);
        self::success(count($lista) . " banners carregados");
        self::Mensagemjson("Banners carregados", 200, $lista);
    }

    // ==============================
    // USUÁRIOS (ADMIN)
    // ==============================

    public function listarUsuarios(): void
    {
        try {
            self::info("Usuário: listando usuários");

            $usuariosModel = \App\Dao\UsuarioDao\UsuarioDao::listar();

            $usuarios = array_map(
                fn($u) => ($u instanceof \App\Models\Usuario\UsuarioModel) ? $u->toArray() : (array)$u,
                $usuariosModel
            );

            // (opcional) esconder senha no retorno:
            $usuarios = array_map(function ($u) {
                unset($u["senha"]);
                return $u;
            }, $usuarios);

            self::Mensagemjson("Usuários carregados", 200, [
                "total" => count($usuarios),
                "usuarios" => $usuarios
            ]);
        } catch (\Throwable $th) {
            self::error("Usuário: erro ao listar - " . $th->getMessage());
            self::Mensagemjson("Erro ao listar usuários", 500);
        }
    }

    public function buscarUsuario(int $id): void
    {
        try {
            self::info("Usuário: buscando ID {$id}");

            $u = \App\Dao\UsuarioDao\UsuarioDao::buscarPorId($id);
            if (!$u) {
                self::Mensagemjson("Usuário não encontrado", 404);
                return;
            }

            $arr = $u->toArray();
            unset($arr["senha"]); // não retornar senha

            self::Mensagemjson("Usuário encontrado", 200, $arr);
        } catch (\Throwable $th) {
            self::error("Usuário: erro ao buscar - " . $th->getMessage());
            self::Mensagemjson("Erro ao buscar usuário", 500);
        }
    }

    public function criarUsuario(): void
    {
        try {
            $dados = json_decode(file_get_contents("php://input"), true) ?? [];

            $nome = trim((string)($dados["nome"] ?? ""));
            $email = trim((string)($dados["email"] ?? ""));
            $senhaRaw = (string)($dados["senha"] ?? "");
            $pin = isset($dados["pin"]) && $dados["pin"] !== "" ? (string)$dados["pin"] : null;
            $nivel_id = (int)($dados["nivel_id"] ?? 2);
            $statusid = (int)($dados["statusid"] ?? 1);
            $telefone = isset($dados["telefone"]) ? (string)$dados["telefone"] : null;
            $cpf = isset($dados["cpf"]) ? (string)$dados["cpf"] : null;

            if ($nome === "" || $email === "" || $senhaRaw === "") {
                self::Mensagemjson("Informe nome, email e senha", 422);
                return;
            }

            $agora = date("Y-m-d H:i:s");

            $usuario = new \App\Models\Usuario\UsuarioModel(
                0,
                $nome,
                $email,
                password_hash($senhaRaw, PASSWORD_DEFAULT),
                $pin,
                $nivel_id,
                $statusid,
                $telefone,
                $cpf,
                $agora,
                $agora
            );

            $id = \App\Dao\UsuarioDao\UsuarioDao::criar($usuario);

            if ($id) {
                self::Mensagemjson("Usuário criado com sucesso", 201, [
                    "id_usuario" => $id
                ]);
                return;
            }

            self::Mensagemjson("Erro ao criar usuário", 500);
        } catch (\Throwable $th) {
            self::error("Usuário: erro ao criar - " . $th->getMessage());
            self::Mensagemjson("Erro ao criar usuário", 500);
        }
    }

    public function atualizarUsuario(int $id): void
    {
        try {
            $existente = \App\Dao\UsuarioDao\UsuarioDao::buscarPorId($id);
            if (!$existente) {
                self::Mensagemjson("Usuário não encontrado", 404);
                return;
            }

            // impede editar usuário do sistema
            if ($existente->getNivelId() === 1) {
                self::Mensagemjson("Usuário do sistema não pode ser alterado", 403);
                return;
            }

            $dados = json_decode(file_get_contents("php://input"), true) ?? [];

            $nome = trim((string)($dados["nome"] ?? $existente->getNome()));
            $email = trim((string)($dados["email"] ?? $existente->getEmail()));

            // senha: só atualiza se vier no body
            $senha = $existente->getSenha();
            if (array_key_exists("senha", $dados) && (string)$dados["senha"] !== "") {
                $senha = password_hash((string)$dados["senha"], PASSWORD_DEFAULT);
            }

            // pin: se vier no body, atualiza (pode ser null)
            $pin = $existente->getPin();
            if (array_key_exists("pin", $dados)) {
                $pinBody = $dados["pin"];
                $pin = ($pinBody === null || $pinBody === "") ? null : (string)$pinBody;
            }

            $nivel_id = (int)($dados["nivel_id"] ?? $existente->getNivelId());
            $statusid = (int)($dados["statusid"] ?? $existente->getStatusId());
            $telefone = array_key_exists("telefone", $dados) ? ($dados["telefone"] ?? null) : $existente->getTelefone();
            $cpf = array_key_exists("cpf", $dados) ? ($dados["cpf"] ?? null) : $existente->getCpf();

            if ($nome === "" || $email === "") {
                self::Mensagemjson("Nome ou email inválido", 422);
                return;
            }

            $usuarioAtualizado = new \App\Models\Usuario\UsuarioModel(
                $id,
                $nome,
                $email,
                $senha,
                $pin,
                $nivel_id,
                $statusid,
                $telefone ? (string)$telefone : null,
                $cpf ? (string)$cpf : null,
                $existente->getCriado(),
                date("Y-m-d H:i:s")
            );

            $ok = \App\Dao\UsuarioDao\UsuarioDao::atualizar($id, $usuarioAtualizado);

            self::Mensagemjson(
                $ok ? "Usuário atualizado com sucesso" : "Erro ao atualizar usuário",
                $ok ? 200 : 500
            );
        } catch (\Throwable $th) {
            self::error("Usuário: erro ao atualizar - " . $th->getMessage());
            self::Mensagemjson("Erro ao atualizar usuário", 500);
        }
    }

    public function removerUsuario(int $id): void
    {
        try {
            $u = \App\Dao\UsuarioDao\UsuarioDao::buscarPorId($id);
            if (!$u) {
                self::Mensagemjson("Usuário não encontrado", 404);
                return;
            }

            if ($u->getNivelId() === 1) {
                self::Mensagemjson("Usuário do sistema não pode ser excluído", 403);
                return;
            }

            $ok = \App\Dao\UsuarioDao\UsuarioDao::deletar($id);

            self::Mensagemjson(
                $ok ? "Usuário removido com sucesso" : "Erro ao remover usuário",
                $ok ? 200 : 500
            );
        } catch (\Throwable $th) {
            self::error("Usuário: erro ao remover - " . $th->getMessage());
            self::Mensagemjson("Erro ao remover usuário", 500);
        }
    }

    public function resetPinUsuario(int $id): void
    {
        try {
            $existente = \App\Dao\UsuarioDao\UsuarioDao::buscarPorId($id);
            if (!$existente) {
                self::Mensagemjson("Usuário não encontrado", 404);
                return;
            }

            if ($existente->getNivelId() === 1) {
                self::Mensagemjson("Usuário do sistema não pode alterar PIN", 403);
                return;
            }

            $novoPin = (string)random_int(1000, 9999);

            $usuarioAtualizado = new \App\Models\Usuario\UsuarioModel(
                $id,
                $existente->getNome(),
                $existente->getEmail(),
                $existente->getSenha(),
                $novoPin,
                $existente->getNivelId(),
                $existente->getStatusId(),
                $existente->getTelefone(),
                $existente->getCpf(),
                $existente->getCriado(),
                date("Y-m-d H:i:s")
            );

            $ok = \App\Dao\UsuarioDao\UsuarioDao::atualizar($id, $usuarioAtualizado);

            self::Mensagemjson(
                $ok ? "PIN resetado com sucesso" : "Erro ao resetar PIN",
                $ok ? 200 : 500,
                $ok ? ["pin" => $novoPin] : []
            );
        } catch (\Throwable $th) {
            self::error("Usuário: erro ao resetar PIN - " . $th->getMessage());
            self::Mensagemjson("Erro ao resetar PIN", 500);
        }
    }
    public function enviarEmail(): void
    {
        try {
            $dados = json_decode(file_get_contents("php://input"), true) ?? [];

            $para = trim((string)($dados["para"] ?? ""));
            $assunto = trim((string)($dados["assunto"] ?? ""));
            $mensagem = (string)($dados["mensagem"] ?? "");

            // por padrão texto simples
            EmailService::enviar($para, $assunto, $mensagem, false);

            self::Mensagemjson("Email enviado com sucesso", 200);
        } catch (\InvalidArgumentException $e) {
            self::Mensagemjson($e->getMessage(), 422);
        } catch (\Throwable $th) {
            self::error("Email: erro ao enviar - " . $th->getMessage());
            self::Mensagemjson("Erro ao enviar email", 500);
        }
    }

    // ✅ IMAGENS EXTRAS DO PRODUTO
    // ==============================

    public function listarImagensProduto(int $id): void
    {
        try {
            $produto = ProdutoDao::buscar($id);
            if (!$produto) {
                self::Mensagemjson("Produto não encontrado", 404);
                return;
            }

            $imagens = ProdutoDao::listarImagens($id);

            self::Mensagemjson("Imagens carregadas", 200, [
                "produto_id" => $id,
                "imagem_principal" => $produto["imagem"] ?? null,
                "imagens" => $imagens
            ]);
        } catch (\Throwable $th) {
            self::error("ProdutoImagem: erro ao listar - " . $th->getMessage());
            self::Mensagemjson("Erro ao listar imagens do produto", 500);
        }
    }

    /**
     * Upload múltiplo:
     * Envie multipart/form-data com:
     * - imagens[] (várias)
     * Opcional:
     * - ordem_inicial (int)
     */
    public function adicionarImagensProduto(int $id): void
    {
        try {
            $produto = ProdutoDao::buscar($id);
            if (!$produto) {
                self::Mensagemjson("Produto não encontrado", 404);
                return;
            }

            if (!isset($_FILES["imagens"])) {
                self::Mensagemjson("Envie as imagens no campo 'imagens[]'", 422);
                return;
            }

            $ordemInicial = isset($_POST["ordem_inicial"]) ? (int)$_POST["ordem_inicial"] : 1;

            // Pega quantas já existem para continuar ordem
            $existentes = ProdutoDao::listarImagens($id);
            $ordem = max($ordemInicial, count($existentes) + 1);

            $arquivos = $_FILES["imagens"];

            $count = is_array($arquivos["name"]) ? count($arquivos["name"]) : 0;
            if ($count <= 0) {
                self::Mensagemjson("Nenhuma imagem recebida", 422);
                return;
            }

            $salvas = [];
            $falhas = 0;

            for ($i = 0; $i < $count; $i++) {
                if (empty($arquivos["name"][$i])) continue;

                $file = [
                    "name" => $arquivos["name"][$i],
                    "type" => $arquivos["type"][$i] ?? "",
                    "tmp_name" => $arquivos["tmp_name"][$i],
                    "error" => $arquivos["error"][$i],
                    "size" => $arquivos["size"][$i] ?? 0,
                ];

                if (($file["error"] ?? UPLOAD_ERR_OK) !== UPLOAD_ERR_OK) {
                    $falhas++;
                    continue;
                }

                // ✅ AGORA SALVA EM: upload/produtos/galeria
                // ✅ E com prefixo diferente pra ficar organizado
                $caminho = ServidorUpload::upload($file, "produtos/galeria", "gal_");

                if (!$caminho) {
                    $falhas++;
                    continue;
                }

                // Salva no banco (produto_imagem)
                $ok = ProdutoDao::adicionarImagem($id, $caminho, $ordem);

                if ($ok) {
                    $salvas[] = [
                        "imagem" => $caminho,
                        "ordem" => $ordem
                    ];

                    // Se o produto não tem imagem principal, define a primeira enviada como principal
                    if (empty($produto["imagem"])) {
                        ProdutoDao::definirImagemPrincipal($id, $caminho);
                        $produto["imagem"] = $caminho;
                    }

                    $ordem++;
                } else {
                    $falhas++;
                }
            }

            self::Mensagemjson("Upload concluído", 200, [
                "produto_id" => $id,
                "salvas" => $salvas,
                "falhas" => $falhas
            ]);
        } catch (\Throwable $th) {
            self::error("ProdutoImagem: erro ao adicionar - " . $th->getMessage());
            self::Mensagemjson("Erro ao adicionar imagens no produto", 500);
        }
    }

    public function removerImagemProduto(int $id): void
    {
        try {
            // Se você quiser apagar o arquivo físico também, você precisa buscar o caminho da imagem antes.
            // Como a gente não criou método buscarImagem por id_imagem, vamos remover só no banco (rápido).
            $ok = ProdutoDao::removerImagem($id);

            self::Mensagemjson(
                $ok ? "Imagem removida com sucesso" : "Erro ao remover imagem",
                $ok ? 200 : 500
            );
        } catch (\Throwable $th) {
            self::error("ProdutoImagem: erro ao remover - " . $th->getMessage());
            self::Mensagemjson("Erro ao remover imagem", 500);
        }
    }

    /**
     * Body JSON:
     * { "imagem": "upload/produtos/xxx.jpg" }
     */
    public function definirImagemPrincipalProduto(int $id): void
    {
        try {
            $produto = ProdutoDao::buscar($id);
            if (!$produto) {
                self::Mensagemjson("Produto não encontrado", 404);
                return;
            }

            $dados = json_decode(file_get_contents("php://input"), true) ?? [];
            $imagem = trim((string)($dados["imagem"] ?? ""));

            if ($imagem === "") {
                self::Mensagemjson("Informe o campo 'imagem' no body", 422);
                return;
            }

            $ok = ProdutoDao::definirImagemPrincipal($id, $imagem);

            self::Mensagemjson(
                $ok ? "Imagem principal definida" : "Erro ao definir imagem principal",
                $ok ? 200 : 500
            );
        } catch (\Throwable $th) {
            self::error("ProdutoImagem: erro ao definir principal - " . $th->getMessage());
            self::Mensagemjson("Erro ao definir imagem principal", 500);
        }
    }
    public function listarGaleria(): void
    {
        try {
            $somenteGaleria = isset($_GET["somente_galeria"]) ? (int)$_GET["somente_galeria"] : 1;

            // paginação opcional
            $page  = max(1, (int)($_GET["page"] ?? 1));
            $limit = max(1, min(100, (int)($_GET["limit"] ?? 50)));
            $offset = ($page - 1) * $limit;

            $imagens = ProdutoDao::listarGaleria($somenteGaleria === 1, $limit, $offset);
            $total   = ProdutoDao::contarImagensGaleria($somenteGaleria === 1);

            self::Mensagemjson("Galeria carregada", 200, [
                "page" => $page,
                "limit" => $limit,
                "total" => $total,
                "imagens" => $imagens
            ]);
        } catch (\Throwable $th) {
            self::error("Galeria: erro ao listar - " . $th->getMessage());
            self::Mensagemjson("Erro ao listar galeria", 500);
        }
    }

    public function contarGaleria(): void
    {
        try {
            $somenteGaleria = isset($_GET["somente_galeria"]) ? (int)$_GET["somente_galeria"] : 1;

            $total = ProdutoDao::contarImagensGaleria($somenteGaleria === 1);

            self::Mensagemjson("Contagem carregada", 200, [
                "somente_galeria" => $somenteGaleria === 1,
                "total" => $total
            ]);
        } catch (\Throwable $th) {
            self::error("Galeria: erro ao contar - " . $th->getMessage());
            self::Mensagemjson("Erro ao contar galeria", 500);
        }
    }

    // =======================================
    // ✅ CAMPANHAS (2 tabelas: campanha + campanha_produto)
    // =======================================

    public function listarCampanhas(): void
    {
        try {
            $lista = CampanhaDao::listar();
            self::Mensagemjson("Campanhas carregadas", 200, [
                "total" => count($lista),
                "campanhas" => $lista
            ]);
        } catch (\Throwable $th) {
            self::error("Campanha: erro ao listar - " . $th->getMessage());
            self::Mensagemjson("Erro ao listar campanhas", 500);
        }
    }

    /**
     * GET /admin/campanha/{id}
     */
    public function buscarCampanha($id): void
    {
        try {
            $id = (int)$id;

            if ($id <= 0) {
                self::Mensagemjson("ID inválido", 422);
                return;
            }

            $campanha = CampanhaDao::buscar($id);
            if (!$campanha) {
                self::Mensagemjson("Campanha não encontrada", 404);
                return;
            }

            self::Mensagemjson("Campanha encontrada", 200, $campanha);
        } catch (\Throwable $th) {
            self::error("Campanha: erro ao buscar - " . $th->getMessage());
            self::Mensagemjson("Erro ao buscar campanha", 500);
        }
    }

    /**
     * POST /admin/campanha/criar
     * Body JSON:
     * {
     *  "titulo": "...",
     *  "slug": "mes-da-mulher",
     *  "descricao": "...",
     *  "banner": "upload/....jpg" (opcional),
     *  "statusid": 3, // id do status (destaque)
     *  "inicio": "2026-03-01 00:00:00" (opcional),
     *  "fim": "2026-03-31 23:59:59" (opcional)
     * }
     */
    public function criarCampanha(): void
    {
        try {
            $dados = self::receberJson();

            $titulo = trim((string)($dados["titulo"] ?? ""));
            $slug = trim((string)($dados["slug"] ?? ""));
            $statusid = (int)($dados["statusid"] ?? 0);

            if ($titulo === "" || $slug === "" || $statusid <= 0) {
                self::Mensagemjson("Informe titulo, slug e statusid", 422);
                return;
            }

            // evita duplicar slug
            $existe = CampanhaDao::buscarPorSlug($slug);
            if ($existe) {
                self::Mensagemjson("Já existe campanha com esse slug", 409);
                return;
            }

            $agora = date("Y-m-d H:i:s");

            $id = CampanhaDao::criar([
                "titulo" => $titulo,
                "slug" => $slug,
                "descricao" => $dados["descricao"] ?? null,
                "banner" => $dados["banner"] ?? null,
                "statusid" => $statusid,
                "inicio" => $dados["inicio"] ?? null,
                "fim" => $dados["fim"] ?? null,
                "criado" => $agora
            ]);

            if ($id <= 0) {
                self::Mensagemjson("Erro ao criar campanha", 500);
                return;
            }

            self::Mensagemjson("Campanha criada com sucesso", 201, [
                "id_campanha" => $id
            ]);
        } catch (\Throwable $th) {
            self::error("Campanha: erro ao criar - " . $th->getMessage());
            self::Mensagemjson("Erro ao criar campanha", 500);
        }
    }

    /**
     * PUT /admin/campanha/{id}
     * Body JSON igual ao criar.
     */
    public function atualizarCampanha(int $id): void
    {
        try {
            $existente = CampanhaDao::buscar($id);
            if (!$existente) {
                self::Mensagemjson("Campanha não encontrada", 404);
                return;
            }

            $dados = self::receberJson();

            $titulo = trim((string)($dados["titulo"] ?? $existente["titulo"] ?? ""));
            $slug = trim((string)($dados["slug"] ?? $existente["slug"] ?? ""));
            $statusid = (int)($dados["statusid"] ?? ($existente["statusid"] ?? 0));

            if ($titulo === "" || $slug === "" || $statusid <= 0) {
                self::Mensagemjson("titulo/slug/statusid inválidos", 422);
                return;
            }

            // se trocou slug, valida duplicado
            $outra = CampanhaDao::buscarPorSlug($slug);
            if ($outra && (int)$outra["id_campanha"] !== (int)$id) {
                self::Mensagemjson("Já existe campanha com esse slug", 409);
                return;
            }

            $agora = date("Y-m-d H:i:s");

            $ok = CampanhaDao::atualizar($id, [
                "titulo" => $titulo,
                "slug" => $slug,
                "descricao" => $dados["descricao"] ?? ($existente["descricao"] ?? null),
                "banner" => $dados["banner"] ?? ($existente["banner"] ?? null),
                "statusid" => $statusid,
                "inicio" => array_key_exists("inicio", $dados) ? $dados["inicio"] : ($existente["inicio"] ?? null),
                "fim" => array_key_exists("fim", $dados) ? $dados["fim"] : ($existente["fim"] ?? null),
                "atualizado" => $agora
            ]);

            self::Mensagemjson($ok ? "Campanha atualizada" : "Erro ao atualizar campanha", $ok ? 200 : 500);
        } catch (\Throwable $th) {
            self::error("Campanha: erro ao atualizar - " . $th->getMessage());
            self::Mensagemjson("Erro ao atualizar campanha", 500);
        }
    }

    /**
     * DELETE /admin/campanha/{id}/remover
     */
    public function removerCampanha(int $id): void
    {
        try {
            $existente = CampanhaDao::buscar($id);
            if (!$existente) {
                self::Mensagemjson("Campanha não encontrada", 404);
                return;
            }

            $ok = CampanhaDao::deletar($id);
            self::Mensagemjson($ok ? "Campanha removida" : "Erro ao remover campanha", $ok ? 200 : 500);
        } catch (\Throwable $th) {
            self::error("Campanha: erro ao remover - " . $th->getMessage());
            self::Mensagemjson("Erro ao remover campanha", 500);
        }
    }

    /**
     * GET /admin/campanha/{id}/produtos
     */
    public function listarProdutosDaCampanha(int $id): void
    {
        try {
            $campanha = CampanhaDao::buscar($id);
            if (!$campanha) {
                self::Mensagemjson("Campanha não encontrada", 404);
                return;
            }

            $produtos = CampanhaDao::listarProdutosDaCampanha($id);

            self::Mensagemjson("Produtos da campanha carregados", 200, [
                "campanha" => $campanha,
                "total" => count($produtos),
                "produtos" => $produtos
            ]);
        } catch (\Throwable $th) {
            self::error("Campanha: erro ao listar produtos - " . $th->getMessage());
            self::Mensagemjson("Erro ao listar produtos da campanha", 500);
        }
    }

    /**
     * POST /admin/campanha/{id}/produtos
     * Body JSON:
     * { "produtos": [6,7,15], "ordem_inicial": 1 }
     */
    public function vincularProdutosNaCampanha(int $id): void
    {
        try {
            $campanha = CampanhaDao::buscar($id);
            if (!$campanha) {
                self::Mensagemjson("Campanha não encontrada", 404);
                return;
            }

            $dados = self::receberJson();
            $produtos = $dados["produtos"] ?? [];
            $ordemInicial = (int)($dados["ordem_inicial"] ?? 1);

            if (!is_array($produtos) || count($produtos) === 0) {
                self::Mensagemjson("Informe produtos[]", 422);
                return;
            }

            $agora = date("Y-m-d H:i:s");

            $total = CampanhaDao::vincularProdutos($id, $produtos, $ordemInicial, $agora);

            self::Mensagemjson("Produtos vinculados", 200, [
                "campanha_id" => $id,
                "total_vinculados" => $total
            ]);
        } catch (\Throwable $th) {
            self::error("Campanha: erro ao vincular produtos - " . $th->getMessage());
            self::Mensagemjson("Erro ao vincular produtos na campanha", 500);
        }
    }

    /**
     * DELETE /admin/campanha/{id}/produto/{produtoId}/remover
     */
    public function removerProdutoDaCampanha(int $id, int $produtoId): void
    {
        try {
            $campanha = CampanhaDao::buscar($id);
            if (!$campanha) {
                self::Mensagemjson("Campanha não encontrada", 404);
                return;
            }

            $ok = CampanhaDao::removerVinculo($id, $produtoId);

            self::Mensagemjson($ok ? "Produto removido da campanha" : "Erro ao remover vínculo", $ok ? 200 : 500);
        } catch (\Throwable $th) {
            self::error("Campanha: erro ao remover vínculo - " . $th->getMessage());
            self::Mensagemjson("Erro ao remover produto da campanha", 500);
        }
    }

    /**
     * DELETE /admin/campanha/{id}/limpar
     */
    public function limparCampanhaProdutos(int $id): void
    {
        try {
            $campanha = CampanhaDao::buscar($id);
            if (!$campanha) {
                self::Mensagemjson("Campanha não encontrada", 404);
                return;
            }

            $ok = CampanhaDao::limparCampanha($id);

            self::Mensagemjson($ok ? "Campanha limpa" : "Erro ao limpar campanha", $ok ? 200 : 500);
        } catch (\Throwable $th) {
            self::error("Campanha: erro ao limpar - " . $th->getMessage());
            self::Mensagemjson("Erro ao limpar campanha", 500);
        }
    }

    /**
     * GET /admin/campanha/ativa/{slug}
     * Ex: /admin/campanha/ativa/mes-da-mulher?status=destaque
     */
    public function buscarCampanhaAtivaPorSlug(string $slug): void
    {
        try {
            $statusCodigo = isset($_GET["status"]) ? (string)$_GET["status"] : "destaque";

            $campanha = CampanhaDao::buscarAtivaPorSlug($slug, $statusCodigo);
            if (!$campanha) {
                self::Mensagemjson("Nenhuma campanha ativa encontrada", 404);
                return;
            }

            $produtos = CampanhaDao::listarProdutosDaCampanhaAtiva($slug, $statusCodigo);

            self::Mensagemjson("Campanha ativa carregada", 200, [
                "campanha" => $campanha,
                "total_produtos" => count($produtos),
                "produtos" => $produtos
            ]);
        } catch (\Throwable $th) {
            self::error("Campanha: erro ao buscar ativa - " . $th->getMessage());
            self::Mensagemjson("Erro ao buscar campanha ativa", 500);
        }
    }
    /**
     * GET /admin/campanha/destaques
     * Retorna campanha nível 9 ativa + produtos vinculados (campanha_produto)
     * ✅ NÃO usa produto.destaque / produto.statusid
     */
    public function listarDestaquesNivel9(): void
    {
        try {
            $payload = CampanhaDao::listarDestaquesNivel9();

            self::Mensagemjson("Destaques carregados", 200, [
                "campanha" => $payload["campanha"],
                "total_produtos" => count($payload["produtos"]),
                "produtos" => $payload["produtos"]
            ]);
        } catch (\Throwable $th) {
            self::error("Campanha: erro ao listar destaques - " . $th->getMessage());
            self::Mensagemjson("Erro ao listar destaques", 500);
        }
    }

    public function atualizarProduto(int $id): void
    {
        try {
            self::info("Produto: atualizando produto ID {$id}");

            $produto = ProdutoDao::buscar($id);
            if (!$produto) {
                self::Mensagemjson("Produto não encontrado", 404);
                return;
            }

            $dados = $_POST;
            $arquivo = $_FILES["imagem"] ?? null;

            if ($arquivo && !empty($arquivo["name"])) {
                $caminhoImagem = ServidorUpload::upload($arquivo, "produtos");
                if ($caminhoImagem) {
                    $dados["imagem"] = $caminhoImagem;
                }
            }

            if (empty($dados["slug"]) && !empty($dados["nome"])) {
                $dados["slug"] = self::gerarSlug($dados["nome"]);
            }

            $ok = ProdutoDao::atualizar($id, $dados);

            self::Mensagemjson(
                $ok ? "Produto atualizado com sucesso" : "Erro ao atualizar produto",
                $ok ? 200 : 500
            );
        } catch (\Throwable $th) {
            self::error("Produto: erro ao atualizar - " . $th->getMessage());
            self::Mensagemjson("Erro ao atualizar produto", 500);
        }
    }
}
