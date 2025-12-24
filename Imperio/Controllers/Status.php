<?php

namespace Imperio\Controllers;

use App\Dao\Status\StatusDao;
use App\Models\StatusModel;
use Config\Base\Basecontrolador;

class Status extends Basecontrolador
{
    /**
     * GET /status
     * Retorna todos os status
     */
    public function listar()
    {
        try {
            $status = StatusDao::listar();

            if (empty($status)) {
                self::Mensagemjson("Nenhum status encontrado", 404);
                return;
            }

            $lista = array_map(fn($s) => $s->toArray(), $status);

            self::Mensagemjson("Lista de status", 200, $lista);

        } catch (\Throwable $th) {
            self::Mensagemjson("Erro ao listar status: " . $th->getMessage(), 500);
        }
    }

    /**
     * GET /status/{id}
     */
    public function buscar($id)
    {
        try {
            $status = StatusDao::buscarPorId((int) $id);

            if (!$status) {
                self::Mensagemjson("Status não encontrado", 404);
                return;
            }

            self::Mensagemjson("Status encontrado", 200, $status->toArray());

        } catch (\Throwable $th) {
            self::Mensagemjson("Erro ao buscar status: " . $th->getMessage(), 500);
        }
    }

    /**
     * POST /status
     */
    public function criar()
    {
        $dados = self::receberJson();

        if (!isset($dados["nome"], $dados["codigo"], $dados["descricao"])) {
            self::Mensagemjson("Campos obrigatórios: nome, codigo, descricao", 400);
            return;
        }

        $status = new StatusModel(
            $dados["nome"],
            $dados["codigo"],
            $dados["descricao"]
        );

        if (!StatusDao::criar($status)) {
            self::Mensagemjson("Erro ao criar status", 500);
            return;
        }

        self::Mensagemjson("Status criado com sucesso", 201, $status->toArray());
    }

    /**
     * PUT /status/{id}
     */
    public function atualizar($id)
    {
        $dados = self::receberJson();

        $status = new StatusModel(
            $dados["nome"],
            $dados["codigo"],
            $dados["descricao"]
        );

        if (!StatusDao::atualizar((int) $id, $status)) {
            self::Mensagemjson("Erro ao atualizar status", 500);
            return;
        }

        self::Mensagemjson("Status atualizado", 200, $status->toArray());
    }

    /**
     * DELETE /status/{id}
     */
    public function deletar($id)
    {
        if (!StatusDao::deletar((int) $id)) {
            self::Mensagemjson("Erro ao deletar status", 500);
            return;
        }

        self::Mensagemjson("Status deletado", 200);
    }
}
