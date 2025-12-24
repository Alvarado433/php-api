<?php

namespace Imperio\Controllers;

use Config\Base\Basecontrolador;
use App\Dao\Footer\FooterDao;
use App\Models\Footer\Footer;

class FooterController extends Basecontrolador
{
    /**
     * Listar todos os footers
     */
    public static function listar(): void
    {
        try {
            self::info("Listando todos os footers");

            $footers = FooterDao::getAll();
            $dados = array_map(fn(Footer $f) => $f->toArray(), $footers);

            self::success("Footers listados com sucesso: " . count($dados) . " itens");
            self::Mensagemjson("Footers listados com sucesso", 200, $dados);
        } catch (\Throwable $th) {
            self::error("Erro ao listar footers: " . $th->getMessage());
            self::Mensagemjson("Erro ao listar footers", 500);
        }
    }

    /**
     * Buscar footer por ID
     */
    public static function buscar(int $id): void
    {
        try {
            self::info("Buscando footer_id: {$id}");

            $footer = FooterDao::getById($id);
            if (!$footer) {
                self::warning("Footer não encontrado: id {$id}");
                self::Mensagemjson("Footer não encontrado", 404);
                return;
            }

            self::success("Footer encontrado: id {$id}");
            self::Mensagemjson("Footer encontrado", 200, [$footer->toArray()]);
        } catch (\Throwable $th) {
            self::error("Erro ao buscar footer: " . $th->getMessage());
            self::Mensagemjson("Erro ao buscar footer", 500);
        }
    }

    /**
     * Criar novo footer
     */
    public static function criar(): void
    {
        try {
            $dados = self::receberJson();
            self::info("Criando novo footer com título: {$dados['titulo']}");

            $footer = new Footer(
                null,
                $dados['logo'] ?? null,
                $dados['titulo'] ?? "",
                $dados['descricao'] ?? null,
                $dados['endereco'] ?? null,
                $dados['icone'] ?? null,
                $dados['statusid'] ?? 1,
                date('Y-m-d H:i:s'),
                null
            );

            $id = FooterDao::insert($footer);

            if ($id) {
                self::success("Footer criado com sucesso: id {$id}");
                self::Mensagemjson("Footer criado com sucesso", 201, ["id_footer" => $id]);
            } else {
                self::warning("Erro ao criar footer");
                self::Mensagemjson("Erro ao criar footer", 500);
            }

        } catch (\Throwable $th) {
            self::error("Erro ao criar footer: " . $th->getMessage());
            self::Mensagemjson("Erro ao criar footer", 500);
        }
    }

    /**
     * Atualizar footer
     */
    public static function atualizar(int $id): void
    {
        try {
            $dados = self::receberJson();
            self::info("Atualizando footer_id: {$id}");

            $footer = FooterDao::getById($id);
            if (!$footer) {
                self::warning("Footer não encontrado: id {$id}");
                self::Mensagemjson("Footer não encontrado", 404);
                return;
            }

            $footer = new Footer(
                $id,
                $dados['logo'] ?? $footer->getLogo(),
                $dados['titulo'] ?? $footer->getTitulo(),
                $dados['descricao'] ?? $footer->getDescricao(),
                $dados['endereco'] ?? $footer->getEndereco(),
                $dados['icone'] ?? $footer->getIcone(),
                $dados['statusid'] ?? $footer->getStatusid(),
                $footer->getCriado(),
                date('Y-m-d H:i:s')
            );

            $ok = FooterDao::update($footer);

            if ($ok) {
                self::success("Footer atualizado com sucesso: id {$id}");
                self::Mensagemjson("Footer atualizado com sucesso", 200);
            } else {
                self::warning("Erro ao atualizar footer: id {$id}");
                self::Mensagemjson("Erro ao atualizar footer", 500);
            }

        } catch (\Throwable $th) {
            self::error("Erro ao atualizar footer: " . $th->getMessage());
            self::Mensagemjson("Erro ao atualizar footer", 500);
        }
    }

    /**
     * Deletar footer
     */
    public static function deletar(int $id): void
    {
        try {
            self::info("Deletando footer_id: {$id}");

            $ok = FooterDao::delete($id);

            if ($ok) {
                self::success("Footer deletado com sucesso: id {$id}");
                self::Mensagemjson("Footer deletado com sucesso", 200);
            } else {
                self::warning("Erro ao deletar footer: id {$id}");
                self::Mensagemjson("Erro ao deletar footer", 500);
            }

        } catch (\Throwable $th) {
            self::error("Erro ao deletar footer: " . $th->getMessage());
            self::Mensagemjson("Erro ao deletar footer", 500);
        }
    }
}
