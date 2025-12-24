<?php

namespace Imperio\Controllers;

use App\Models\Login\TipoLogin;
use App\Models\Login\ConfiguracaoLogin;
use Config\Base\Basecontrolador;
use App\Dao\ConfiguracaoDao\LoginTipoDao;
use App\Dao\ConfiguracaoDao\ConfiguracaoLoginDao;

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
}
