<?php

namespace Config\Base;

use Core\Logs\Logs;
use Core\View\templates;
use Routers\Generator\AutoModelGenerator;

class Basecontrolador extends Logs
{
    /**
     * Última resposta gerada pelo controller
     */
    protected static array $resposta = [
        "status"   => 200,
        "mensagem" => "",
        "dados"    => []
    ];
    /**
     * Gera slug amigável a partir do nome
     */
    protected static function gerarSlug(string $nome): string
    {
        $slug = strtolower(trim($nome));
        $slug = preg_replace('/[^a-z0-9]+/i', '-', $slug);
        $slug = trim($slug, '-');
        return $slug;
    }
    /**
     * Envia resposta JSON padronizada
     */
    protected static function Mensagemjson(string $mensagem, int $codigo = 200, array $dados = []): void
    {
        try {
            self::$resposta["status"]   = $codigo;
            self::$resposta["mensagem"] = $mensagem;
            self::$resposta["dados"]    = $dados;

            http_response_code($codigo);
            header('Content-Type: application/json; charset=utf-8');

            echo json_encode(self::$resposta, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        } catch (\Throwable $th) {
            self::error("Erro ao enviar JSON: " . $th->getMessage());
        }
    }


    /**
     * Criar Model automaticamente com propriedades definidas
     */
    protected static function criarModel(string $model, array $props): string
    {
        try {
            $arquivo = AutoModelGenerator::gerar($model, $props);

            if (file_exists($arquivo)) {
                require_once $arquivo;
            }

            self::success("Model {$model} carregado.");

            return $arquivo;
        } catch (\Throwable $th) {
            self::error("Erro ao criar Model {$model}: " . $th->getMessage());
            throw $th;
        }
    }

    /**
     * Retorna a última resposta montada
     */
    public static function getResposta(): array
    {
        return self::$resposta;
    }

    /**
     * Recebe e valida JSON enviado no corpo da requisição
     */
    protected static function receberJson(): array
    {
        try {
            $json = file_get_contents("php://input");

            if (empty($json)) {
                self::Mensagemjson("Nenhum JSON foi enviado", 400);
                exit;
            }

            $dados = json_decode($json, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                self::Mensagemjson("JSON inválido: " . json_last_error_msg(), 400);
                exit;
            }

            return $dados;
        } catch (\Throwable $th) {
            self::error("Erro ao receber JSON: " . $th->getMessage());
            self::Mensagemjson("Erro ao processar JSON", 500);
            exit;
        }
    }
}
