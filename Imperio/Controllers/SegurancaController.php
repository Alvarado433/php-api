<?php

namespace Imperio\Controllers;

use Core\Logs\Logs;

use Config\Base\Basecontrolador;
use App\Dao\Seguraca\SegurancaConfigDAO;

class SegurancaController extends Basecontrolador
{

    /**
     * =======================================================
     * 🔍 CARREGAR CONFIGURAÇÃO
     * =======================================================
     */
    public function config()
    {
        Logs::info("Requisição: /seguranca/config");

        $config = SegurancaConfigDAO::getConfig();

        if (!$config) {
            Logs::warning("Nenhuma configuração de segurança encontrada.");
            return self::Mensagemjson("Nenhuma configuração encontrada.", 404);
        }

        Logs::success("Configuração de segurança carregada com sucesso.");

        return self::Mensagemjson("Configuração carregada.", 200, [
            "config" => $config->toArray()
        ]);
    }



    /**
     * =======================================================
     * 🆕 CADASTRAR CONFIGURAÇÃO INICIAL
     * =======================================================
     */
    public function cadastrar()
    {
        Logs::info("Requisição: /seguranca/cadastrar");

        $dados = self::receberJson();

        if (empty($dados["pin"])) {
            Logs::warning("Tentativa de cadastrar config sem informar PIN.");
            return self::Mensagemjson("Informe o PIN inicial.", 400);
        }

        $pin = trim($dados["pin"]);
        Logs::info("PIN recebido para cadastro inicial.");

        // Verificar se já existe configuração
        $configExistente = SegurancaConfigDAO::getConfig();

        if ($configExistente) {
            Logs::warning("Tentativa de cadastrar nova config, mas já existe configuração no sistema.");
            return self::Mensagemjson(
                "Configuração já existe. Use /seguranca/pin para atualizar.",
                409
            );
        }

        // Criar hash seguro
        $hash = password_hash($pin, PASSWORD_DEFAULT);
        Logs::info("PIN criptografado com sucesso.");

        // Criar registro
        $ok = SegurancaConfigDAO::criarConfig($hash, 3, 1);

        if (!$ok) {
            Logs::error("Erro ao criar a configuração inicial de segurança.");
            return self::Mensagemjson("Erro ao criar configuração.", 500);
        }

        Logs::success("Configuração de segurança criada com sucesso!");

        return self::Mensagemjson("Configuração criada com sucesso!", 201);
    }



    /**
     * =======================================================
     * 🔐 VALIDAR PIN (Camada Anti-Hacker)
     * =======================================================
     */
    public function validarPin()
    {
        Logs::info("Requisição: /seguranca/validar-pin");

        $dados = self::receberJson();

        if (empty($dados["pin"])) {
            Logs::warning("Tentativa de validar PIN sem informar o PIN.");
            return self::Mensagemjson("PIN não informado.", 400);
        }

        $pinDigitado = trim($dados["pin"]);

        Logs::info("PIN recebido para validação.");

        $config = SegurancaConfigDAO::getConfig();

        if (!$config) {
            Logs::error("Falha ao validar PIN: configuração de segurança inexistente.");
            return self::Mensagemjson("Configuração não encontrada.", 500);
        }

        $hash = $config->getPin_sistema();

        if (!password_verify($pinDigitado, $hash)) {
            Logs::warning("PIN incorreto na camada de proteção. PIN Digitado: {$pinDigitado}");
            return self::Mensagemjson("PIN incorreto.", 401);
        }

        Logs::success("PIN validado com sucesso! Bloqueio liberado.");

        return self::Mensagemjson("Desbloqueado!", 200, [
            "autorizado" => true
        ]);
    }



    /**
     * =======================================================
     * 🔄 ATUALIZAR PIN
     * =======================================================
     */
    public function atualizarPin()
    {
        Logs::info("Requisição: /seguranca/pin");

        $dados = self::receberJson();

        if (empty($dados["novo_pin"])) {
            Logs::warning("Tentativa de atualizar PIN sem informar novo PIN.");
            return self::Mensagemjson("Informe o novo PIN.", 400);
        }

        $novo = trim($dados["novo_pin"]);
        Logs::info("Novo PIN recebido para atualização.");

        $hash = password_hash($novo, PASSWORD_DEFAULT);
        Logs::info("Novo PIN criptografado.");

        $ok = SegurancaConfigDAO::atualizarPin($hash);

        if (!$ok) {
            Logs::error("Erro ao atualizar o PIN.");
            return self::Mensagemjson("Erro ao atualizar PIN.", 500);
        }

        Logs::success("PIN atualizado com sucesso!");

        return self::Mensagemjson("PIN atualizado com sucesso!", 200);
    }



    /**
     * =======================================================
     * 🔄 ATUALIZAR TENTATIVAS
     * =======================================================
     */
    public function atualizarTentativas()
    {
        Logs::info("Requisição: /seguranca/tentativas");

        $dados = self::receberJson();

        if (empty($dados["limite"])) {
            Logs::warning("Tentativa de atualizar tentativas sem enviar limite.");
            return self::Mensagemjson("Informe o limite.", 400);
        }

        $limite = (int)$dados["limite"];

        Logs::info("Novo limite de tentativas recebido: {$limite}");

        $ok = SegurancaConfigDAO::atualizarTentativasMax($limite);

        if (!$ok) {
            Logs::error("Erro ao atualizar limite de tentativas.");
            return self::Mensagemjson("Erro ao atualizar limite.", 500);
        }

        Logs::success("Limite de tentativas atualizado!");

        return self::Mensagemjson("Limite atualizado!", 200);
    }



    /**
     * =======================================================
     * 🔄 ATUALIZAR STATUS
     * =======================================================
     */
    public function atualizarStatus()
    {
        Logs::info("Requisição: /seguranca/status");

        $dados = self::receberJson();

        if (empty($dados["statusid"])) {
            Logs::warning("Tentativa de alterar status sem enviar statusid.");
            return self::Mensagemjson("Informe o statusid.", 400);
        }

        $status = (int)$dados["statusid"];

        Logs::info("Novo status recebido: {$status}");

        $ok = SegurancaConfigDAO::atualizarStatus($status);

        if (!$ok) {
            Logs::error("Erro ao alterar status de segurança.");
            return self::Mensagemjson("Erro ao atualizar status.", 500);
        }

        Logs::success("Status de segurança atualizado!");

        return self::Mensagemjson("Status atualizado!", 200);
    }
}
