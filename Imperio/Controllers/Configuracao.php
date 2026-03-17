<?php

namespace Imperio\Controllers;

use App\Models\Login\TipoLogin;
use App\Models\Login\ConfiguracaoLogin;
use Config\Base\Basecontrolador;
use App\Dao\ConfiguracaoDao\LoginTipoDao;
use App\Dao\ConfiguracaoDao\ConfiguracaoLoginDao;

// Imports do Status e Nivel fundidos
use App\Dao\Status\StatusDao;
use App\Models\StatusModel;
use App\Dao\NivelDao\NivelDAO;

class Configuracao extends Basecontrolador
{
    // ============================================================
    // LOGIN CONFIG (RETORNA A CONFIG ATIVA)
    // ============================================================
    public function loginAtiva()
    {
        $config = ConfiguracaoLoginDao::buscarAtiva();

        if (!$config) {
            return self::Mensagemjson("Nenhuma configuração ativa encontrada", 404);
        }

        return self::Mensagemjson("Configuração carregada", 200, $config->toArray());
    }

    // ============================================================
    // LISTAR TODAS CONFIGURAÇÕES
    // ============================================================
    public function listarLogin()
    {
        $lista = ConfiguracaoLoginDao::listar();

        return self::Mensagemjson(
            "Lista de configurações", 
            200, 
            array_map(fn($x) => $x->toArray(), $lista)
        );
    }

    // ============================================================
    // CRIAR CONFIGURAÇÃO
    // ============================================================
    public function salvarLogin()
    {
        $dados = self::receberJson();

        $config = new ConfiguracaoLogin(
            id: 0,
            titulo: $dados["titulo"],
            logo: $dados["logo"],
            fundo: $dados["fundo"],
            mensagem_personalizada: $dados["mensagem_personalizada"] ?? null,
            tipo_login_id: intval($dados["tipo_login_id"]),
            statusid: intval($dados["statusid"]),
            criado: "",
            atualizado: ""
        );

        $ok = ConfiguracaoLoginDao::criar($config);

        if (!$ok) {
            return self::Mensagemjson("Erro ao criar configuração", 500);
        }

        return self::Mensagemjson("Configuração criada com sucesso", 201, $config->toArray());
    }

    // ============================================================
    // ATUALIZAR CONFIGURAÇÃO
    // ============================================================
    public function atualizarLogin($id)
    {
        $dados = self::receberJson();

        $config = new ConfiguracaoLogin(
            id: $id,
            titulo: $dados["titulo"],
            logo: $dados["logo"],
            fundo: $dados["fundo"],
            mensagem_personalizada: $dados["mensagem_personalizada"] ?? null,
            tipo_login_id: intval($dados["tipo_login_id"]),
            statusid: intval($dados["statusid"]),
            criado: "",
            atualizado: ""
        );

        $ok = ConfiguracaoLoginDao::atualizar($id, $config);

        if (!$ok) {
            return self::Mensagemjson("Erro ao atualizar configuração", 500);
        }

        return self::Mensagemjson("Configuração atualizada", 200, $config->toArray());
    }

    // ============================================================
    // TIPOS DE LOGIN (CRUD COMPLETO)
    // ============================================================
    public function listarTiposLogin()
    {
        $lista = LoginTipoDao::listar();

        return self::Mensagemjson(
            "Tipos de login", 
            200, 
            array_map(fn($x) => $x->toArray(), $lista)
        );
    }

    public function criarTipoLogin()
    {
        $d = self::receberJson();

        $tipo = new TipoLogin(
            id_tipo: 0,
            nome: $d["nome"],
            descricao: $d["descricao"] ?? null,
            criado: ""
        );

        $ok = LoginTipoDao::criar($tipo);

        if (!$ok) return self::Mensagemjson("Erro ao criar tipo de login", 500);

        return self::Mensagemjson("Tipo criado", 201, $tipo->toArray());
    }

    public function atualizarTipoLogin($id)
    {
        $d = self::receberJson();

        $tipo = new TipoLogin(
            id_tipo: $id,
            nome: $d["nome"],
            descricao: $d["descricao"] ?? null,
            criado: ""
        );

        $ok = LoginTipoDao::atualizar($id, $tipo);

        if (!$ok) return self::Mensagemjson("Erro ao atualizar tipo de login", 500);

        return self::Mensagemjson("Tipo atualizado", 200, $tipo->toArray());
    }

    public function deletarTipoLogin($id)
    {
        $ok = LoginTipoDao::deletar($id);

        if (!$ok) return self::Mensagemjson("Erro ao deletar tipo de login", 500);

        return self::Mensagemjson("Tipo deletado", 200);
    }

    // ============================================================
    // STATUS (CRUD COMPLETO)
    // ============================================================
    public function listarStatus()
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

    public function buscarStatus($id)
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

    public function criarStatus()
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

    public function atualizarStatus($id)
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

    public function deletarStatus($id)
    {
        if (!StatusDao::deletar((int) $id)) {
            self::Mensagemjson("Erro ao deletar status", 500);
            return;
        }

        self::Mensagemjson("Status deletado", 200);
    }

    // ============================================================
    // NÍVEIS (CRUD COMPLETO)
    // ============================================================
    public function listarNivel()
    {
        $niveis = NivelDAO::listar();

        return self::Mensagemjson(
            "Lista de níveis carregada.",
            200,
            array_map(fn($n) => $n->toArray(), $niveis)
        );
    }

    public function buscarNivel($id)
    {
        $nivel = NivelDAO::buscar((int)$id);

        if (!$nivel) {
            return self::Mensagemjson("Nível não encontrado.", 404);
        }

        return self::Mensagemjson("Nível encontrado.", 200, $nivel->toArray());
    }

    public function criarNivel()
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

    public function atualizarNivel($id)
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

    public function deletarNivel($id)
    {
        $ok = NivelDAO::deletar((int)$id);

        if (!$ok) {
            return self::Mensagemjson("Erro ao deletar nível.", 500);
        }

        return self::Mensagemjson("Nível removido com sucesso.", 200);
    }
}
