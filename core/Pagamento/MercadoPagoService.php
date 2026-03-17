<?php

namespace Core\Pagamento;

use Core\Env\IndexEnv;
use MercadoPago\Client\Payment\PaymentClient;
use MercadoPago\Client\Preference\PreferenceClient;
use MercadoPago\Exceptions\MPApiException;
use MercadoPago\MercadoPagoConfig;

class MercadoPagoService
{
    private string $accessToken;

    public function __construct()
    {
        $env = IndexEnv::carregar();

        $this->accessToken = $env["MP_ACCESS_TOKEN"] ?? "";

        if (!$this->accessToken) {
            throw new \Exception("MP_ACCESS_TOKEN não configurado no .env");
        }

        MercadoPagoConfig::setAccessToken($this->accessToken);
    }

    public function criarPagamento(array $itens, array $cliente, array $urls): array
    {
        try {
            $client = new PreferenceClient();

            $preference = $client->create([
                "items" => $itens,
                "payer" => [
                    "name" => $cliente["nome"] ?? "",
                    "email" => $cliente["email"] ?? ""
                ],
                "back_urls" => [
                    "success" => $urls["success"] ?? "",
                    "failure" => $urls["failure"] ?? "",
                    "pending" => $urls["pending"] ?? ""
                ],
                "auto_return" => "approved"
            ]);

            return [
                "id" => $preference->id ?? null,
                "url" => $preference->init_point ?? null,
                "sandbox_url" => $preference->sandbox_init_point ?? null,
            ];
        } catch (MPApiException $e) {
            $apiResponse = $e->getApiResponse();

            throw new \Exception(json_encode([
                "message" => $e->getMessage(),
                "status_code" => $apiResponse ? $apiResponse->getStatusCode() : null,
                "content" => $apiResponse ? $apiResponse->getContent() : null,
            ], JSON_UNESCAPED_UNICODE));
        } catch (\Throwable $e) {
            throw new \Exception($e->getMessage());
        }
    }

    public function criarPagamentoPix(
        float $valor,
        string $descricao,
        array $cliente,
        ?string $externalReference = null
    ): array {
        try {
            if ($valor <= 0) {
                throw new \Exception("Valor inválido para pagamento PIX.");
            }

            $client = new PaymentClient();

            $payment = $client->create([
                "transaction_amount" => round($valor, 2),
                "description" => $descricao,
                "payment_method_id" => "pix",
                "external_reference" => $externalReference,
                "payer" => [
                    "email" => $cliente["email"] ?? "",
                    "first_name" => $cliente["nome"] ?? "Cliente",
                ]
            ]);

            $transactionData = $payment->point_of_interaction->transaction_data ?? null;

            return [
                "id" => $payment->id ?? null,
                "status" => $payment->status ?? null,
                "status_detail" => $payment->status_detail ?? null,
                "transaction_amount" => $payment->transaction_amount ?? $valor,
                "qr_code" => $transactionData->qr_code ?? null,
                "qr_code_base64" => $transactionData->qr_code_base64 ?? null,
                "ticket_url" => $transactionData->ticket_url ?? null,
            ];
        } catch (MPApiException $e) {
            $apiResponse = $e->getApiResponse();

            throw new \Exception(json_encode([
                "message" => $e->getMessage(),
                "status_code" => $apiResponse ? $apiResponse->getStatusCode() : null,
                "content" => $apiResponse ? $apiResponse->getContent() : null,
            ], JSON_UNESCAPED_UNICODE));
        } catch (\Throwable $e) {
            throw new \Exception($e->getMessage());
        }
    }
}