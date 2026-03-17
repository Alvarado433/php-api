<?php

namespace Imperio\Controllers;

use Core\Pagamento\MercadoPagoService;
use Config\Base\Basecontrolador;

class PagamentoTesteController extends Basecontrolador
{
    public function teste()
    {
        try {
            $mp = new MercadoPagoService();

            $preferencia = $mp->criarPagamento(
                [
                    [
                        "title" => "Produto Teste Universo Império",
                        "quantity" => 1,
                        "currency_id" => "BRL",
                        "unit_price" => 1.00
                    ]
                ],
                [
                    "nome" => "Cliente Teste",
                    "email" => "seu-email-real@exemplo.com"
                ],
                [
                    "success" => "https://www.universoimperio.com.br/pagamento/sucesso",
                    "failure" => "https://www.universoimperio.com.br/pagamento/falha",
                    "pending" => "https://www.universoimperio.com.br/pagamento/pendente"
                ]
            );

            return self::Mensagemjson("Pagamento criado com sucesso", 200, [
                "preferencia_id" => $preferencia["id"] ?? null,
                "url_pagamento" => $preferencia["url"] ?? null,
                "sandbox_url" => $preferencia["sandbox_url"] ?? null,
            ]);
        } catch (\Throwable $e) {
            return self::Mensagemjson("Erro ao criar pagamento", 500, [
                "erro" => $e->getMessage(),
                "classe" => get_class($e),
                "arquivo" => $e->getFile(),
                "linha" => $e->getLine()
            ]);
        }
    }

    /**
     * WEBHOOK DO MERCADO PAGO
     */
    public function webhook()
    {
        try {

            // captura dados enviados pelo MercadoPago
            $body = file_get_contents("php://input");

            $headers = getallheaders();

            // salva log para teste
            file_put_contents(
                __DIR__ . "/../../webhook_mercadopago.log",
                date("Y-m-d H:i:s") . PHP_EOL .
                "HEADERS: " . json_encode($headers) . PHP_EOL .
                "BODY: " . $body . PHP_EOL .
                "--------------------------" . PHP_EOL,
                FILE_APPEND
            );

            http_response_code(200);

            echo json_encode([
                "status" => "ok"
            ]);

        } catch (\Throwable $e) {

            http_response_code(500);

            echo json_encode([
                "erro" => $e->getMessage()
            ]);
        }
    }
}