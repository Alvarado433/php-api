<?php

namespace Imperio\Controllers;

use Config\Base\Basecontrolador;
use App\Dao\Footer\FooterLinkDao;
use App\Models\Footer\FooterLink;

class FooterLinkController extends Basecontrolador
{
    /**
     * Listar todos os links de um footer
     */
    public static function listarPorFooter(int $footerId): void
    {
        try {
            self::info("Listando links do footer_id: {$footerId}");

            $links = FooterLinkDao::getByFooterId($footerId);
            $dados = array_map(fn(FooterLink $l) => $l->toArray(), $links);

            self::success("Links listados com sucesso: " . count($dados) . " itens");
            self::Mensagemjson("Links listados com sucesso", 200, $dados);
        } catch (\Throwable $th) {
            self::error("Erro ao listar links: " . $th->getMessage());
            self::Mensagemjson("Erro ao listar links", 500);
        }
    }

    /**
     * Criar novo link
     */
    public static function criar(): void
    {
        try {
            $dados = self::receberJson();
            self::info("Criando link para footer_id: {$dados['footer_id']}");

            $link = new FooterLink(
                null,
                $dados['footer_id'],
                $dados['titulo'] ?? "",
                $dados['url'] ?? "",
                $dados['icone'] ?? null,
                $dados['ordem'] ?? 0,
                $dados['statusid'] ?? 1,
                date('Y-m-d H:i:s'),
                null
            );

            $id = FooterLinkDao::insert($link);

            if ($id) {
                self::success("Link criado com sucesso: id {$id}");
                self::Mensagemjson("Link criado com sucesso", 201, ["id_link" => $id]);
            } else {
                self::warning("Erro ao criar link");
                self::Mensagemjson("Erro ao criar link", 500);
            }

        } catch (\Throwable $th) {
            self::error("Erro ao criar link: " . $th->getMessage());
            self::Mensagemjson("Erro ao criar link", 500);
        }
    }

    /**
     * Atualizar link
     */
    public static function atualizar(int $id): void
    {
        try {
            $dados = self::receberJson();
            self::info("Atualizando link_id: {$id}");

            $link = FooterLinkDao::getById($id);
            if (!$link) {
                self::warning("Link não encontrado: id {$id}");
                self::Mensagemjson("Link não encontrado", 404);
                return;
            }

            $link = new FooterLink(
                $id,
                $dados['footer_id'] ?? $link->getFooterId(),
                $dados['titulo'] ?? $link->getTitulo(),
                $dados['url'] ?? $link->getUrl(),
                $dados['icone'] ?? $link->getIcone(),
                $dados['ordem'] ?? $link->getOrdem(),
                $dados['statusid'] ?? $link->getStatusId(),
                $link->getCriado(),
                date('Y-m-d H:i:s')
            );

            $ok = FooterLinkDao::update($link);

            if ($ok) {
                self::success("Link atualizado com sucesso: id {$id}");
                self::Mensagemjson("Link atualizado com sucesso", 200);
            } else {
                self::warning("Erro ao atualizar link: id {$id}");
                self::Mensagemjson("Erro ao atualizar link", 500);
            }

        } catch (\Throwable $th) {
            self::error("Erro ao atualizar link: " . $th->getMessage());
            self::Mensagemjson("Erro ao atualizar link", 500);
        }
    }

    /**
     * Deletar link
     */
    public static function deletar(int $id): void
    {
        try {
            self::info("Deletando link_id: {$id}");

            $ok = FooterLinkDao::delete($id);

            if ($ok) {
                self::success("Link deletado com sucesso: id {$id}");
                self::Mensagemjson("Link deletado com sucesso", 200);
            } else {
                self::warning("Erro ao deletar link: id {$id}");
                self::Mensagemjson("Erro ao deletar link", 500);
            }

        } catch (\Throwable $th) {
            self::error("Erro ao deletar link: " . $th->getMessage());
            self::Mensagemjson("Erro ao deletar link", 500);
        }
    }
}
