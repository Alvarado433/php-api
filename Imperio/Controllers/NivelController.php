<?php

namespace Imperio\Controllers;

use App\Dao\NivelDao\NivelDAO;
use Config\Base\Basecontrolador;

class NivelController extends Basecontrolador
{
    /**
     * =======================================================
     * LISTAR TODOS
     * =======================================================
     */
    public function listar()
    {
        $niveis = NivelDAO::listar();

        return self::Mensagemjson(
            "Lista de níveis carregada.",
            200,
            array_map(fn($n) => $n->toArray(), $niveis)
        );
    }

    /**
     * =======================================================
     * BUSCAR POR ID
     * =======================================================
     */
    public function buscar($id)
    {
        $nivel = NivelDAO::buscar((int)$id);

        if (!$nivel) {
            return self::Mensagemjson("Nível não encontrado.", 404);
        }

        return self::Mensagemjson("Nível encontrado.", 200, $nivel->toArray());
    }

    /**
     * =======================================================
     * CRIAR
     * =======================================================
     */
    public function criar()
    {
        $dados = self::receberJson();

        if (empty($dados["nome"]) || empty($dados["codigo"])) {
            return self::Mensagemjson("Informe nome e código.", 400);
        }

        $nome = trim($dados["nome"]);
        $codigo = trim($dados["codigo"]);
        $prioridade = (int)($dados["prioridade"] ?? 1);
        $descricao = $dados["descricao"] ?? "";

        $ok = NivelDAO::criar($nome, $codigo, $prioridade, $descricao);

        if (!$ok) {
            return self::Mensagemjson("Erro ao criar nível.", 500);
        }

        return self::Mensagemjson("Nível criado com sucesso.", 201);
    }

    /**
     * =======================================================
     * ATUALIZAR
     * =======================================================
     */
    public function atualizar($id)
    {
        $dados = self::receberJson();

        if (empty($dados["nome"]) || empty($dados["codigo"])) {
            return self::Mensagemjson("Informe nome e código.", 400);
        }

        $nome = trim($dados["nome"]);
        $codigo = trim($dados["codigo"]);
        $prioridade = (int)($dados["prioridade"] ?? 1);
        $descricao = $dados["descricao"] ?? "";

        $ok = NivelDAO::atualizar((int)$id, $nome, $codigo, $prioridade, $descricao);

        if (!$ok) {
            return self::Mensagemjson("Erro ao atualizar nível.", 500);
        }

        return self::Mensagemjson("Nível atualizado com sucesso.", 200);
    }

    /**
     * =======================================================
     * DELETAR
     * =======================================================
     */
    public function deletar($id)
    {
        $ok = NivelDAO::deletar((int)$id);

        if (!$ok) {
            return self::Mensagemjson("Erro ao deletar nível.", 500);
        }

        return self::Mensagemjson("Nível removido com sucesso.", 200);
    }
}
