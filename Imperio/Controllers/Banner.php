<?php

namespace Imperio\Controllers;

use App\Models\BannerModel;
use App\Dao\Banner\BannerDao;
use Config\Base\Basecontrolador;

class Banner extends Basecontrolador
{
    // GET /banners
    public function listar()
    {
        try {
            $banners = BannerDao::listar();

            if (empty($banners)) {
                self::Mensagemjson("Nenhum banner encontrado", 404, []);
                return;
            }

            $lista = array_map(fn($b) => $b->toArray(), $banners);
            self::Mensagemjson("Lista de banners", 200, $lista);

        } catch (\Throwable $e) {
            self::Mensagemjson("Erro ao listar banners: " . $e->getMessage(), 500);
        }
    }

    // GET /banners/ativos
    public function ativos()
    {
        try {
            $banners = BannerDao::listarAtivos();

            if (empty($banners)) {
                self::Mensagemjson("Nenhum banner ativo encontrado", 404, []);
                return;
            }

            $lista = array_map(fn($b) => $b->toArray(), $banners);
            self::Mensagemjson("Banners ativos", 200, $lista);

        } catch (\Throwable $e) {
            self::Mensagemjson("Erro ao listar banners ativos: " . $e->getMessage(), 500);
        }
    }

    // GET /banners/destaque
    public function ativosEDestaque()
    {
        try {
            $banners = BannerDao::listarAtivosEDestaque();

            if (empty($banners)) {
                self::Mensagemjson("Nenhum banner ativo ou destaque encontrado", 404, []);
                return;
            }

            $lista = array_map(fn($b) => $b->toArray(), $banners);
            self::Mensagemjson("Banners ativos e destaque", 200, $lista);

        } catch (\Throwable $e) {
            self::Mensagemjson("Erro ao listar banners ativos e destaque: " . $e->getMessage(), 500);
        }
    }

    // GET /banners/{id}
    public function buscar($id)
    {
        try {
            $banner = BannerDao::buscarPorId((int)$id);

            if (!$banner) {
                self::Mensagemjson("Banner não encontrado", 404);
                return;
            }

            self::Mensagemjson("Banner encontrado", 200, $banner->toArray());

        } catch (\Throwable $e) {
            self::Mensagemjson("Erro ao buscar banner: " . $e->getMessage(), 500);
        }
    }

    // PUT /banners/{id}
    public function atualizar($id)
    {
        try {
            $dados = self::receberJson();

            if (empty($dados["titulo"]) || empty($dados["imagem"])) {
                self::Mensagemjson("Título e imagem são obrigatórios", 422);
                return;
            }

            // Cria objeto BannerModel
            $banner = new BannerModel(
                $dados["titulo"],
                $dados["descricao"] ?? '',
                $dados["imagem"],
                $dados["link"] ?? null
            );

            // Passa como array para o DAO
            $ok = BannerDao::atualizar((int)$id, $banner->toArray());

            if (!$ok) {
                self::Mensagemjson("Erro ao atualizar banner", 500);
                return;
            }

            self::Mensagemjson("Banner atualizado com sucesso", 200, $banner->toArray());

        } catch (\Throwable $e) {
            self::Mensagemjson("Erro ao atualizar banner: " . $e->getMessage(), 500);
        }
    }

    // DELETE /banners/{id}
    public function deletar($id)
    {
        try {
            $ok = BannerDao::deletar((int)$id);

            if (!$ok) {
                self::Mensagemjson("Erro ao deletar banner", 500);
                return;
            }

            self::Mensagemjson("Banner deletado com sucesso", 200);

        } catch (\Throwable $e) {
            self::Mensagemjson("Erro ao deletar banner: " . $e->getMessage(), 500);
        }
    }

    // PUT /banners/{id}/view
    public function incrementarVisualizacao($id)
    {
        try {
            $ok = BannerDao::incrementarVisualizacao((int)$id);

            if (!$ok) {
                self::Mensagemjson("Erro ao incrementar visualização", 500);
                return;
            }

            self::Mensagemjson("Visualização incrementada", 200);

        } catch (\Throwable $e) {
            self::Mensagemjson("Erro ao incrementar visualização: " . $e->getMessage(), 500);
        }
    }

    // PUT /banners/{id}/click
    public function incrementarClique($id)
    {
        try {
            $ok = BannerDao::incrementarClique((int)$id);

            if (!$ok) {
                self::Mensagemjson("Erro ao incrementar clique", 500);
                return;
            }

            self::Mensagemjson("Clique incrementado", 200);

        } catch (\Throwable $e) {
            self::Mensagemjson("Erro ao incrementar clique: " . $e->getMessage(), 500);
        }
    }

    // POST /banners
    public function criar()
    {
        try {
            $dados = self::receberJson();

            if (empty($dados["titulo"]) || empty($dados["imagem"])) {
                self::Mensagemjson("Título e imagem são obrigatórios", 422);
                return;
            }

            $banner = new BannerModel(
                $dados["titulo"],
                $dados["descricao"] ?? '',
                $dados["imagem"],
                $dados["link"] ?? null
            );

            $ok = BannerDao::criar($banner->toArray());

            if (!$ok) {
                self::Mensagemjson("Erro ao criar banner", 500);
                return;
            }

            self::Mensagemjson("Banner criado com sucesso", 201, $banner->toArray());

        } catch (\Throwable $e) {
            self::Mensagemjson("Erro ao criar banner: " . $e->getMessage(), 500);
        }
    }
}
