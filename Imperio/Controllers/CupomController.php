<?php

namespace Imperio\Controllers;

use Config\Base\Basecontrolador;
use App\Dao\Cupom\CupomDao;

class CupomController extends Basecontrolador
{
    /**
     * 🔹 Listar todos os cupons
     * GET /cupons
     */
    public function listar(): void
    {
        try {
            $cupons = CupomDao::listar();

            self::Mensagemjson(
                "Cupons listados com sucesso",
                200,
                $cupons
            );
        } catch (\Throwable $th) {
            self::error("Erro ao listar cupons: " . $th->getMessage());
            self::Mensagemjson("Erro ao listar cupons", 500);
        }
    }

    /**
     * 🔹 Listar apenas cupons ativos
     * GET /cupons/ativos
     */
    public function listarAtivos(): void
    {
        try {
            $cupons = CupomDao::listarAtivos();

            self::Mensagemjson(
                "Cupons ativos listados com sucesso",
                200,
                $cupons
            );
        } catch (\Throwable $th) {
            self::error("Erro ao listar cupons ativos: " . $th->getMessage());
            self::Mensagemjson("Erro ao listar cupons ativos", 500);
        }
    }

    /**
     * 🔹 Listar apenas cupons inativos
     * GET /cupons/inativos
     */
    public function listarInativos(): void
    {
        try {
            $cupons = CupomDao::listarInativos();

            self::Mensagemjson(
                "Cupons inativos listados com sucesso",
                200,
                $cupons
            );
        } catch (\Throwable $th) {
            self::error("Erro ao listar cupons inativos: " . $th->getMessage());
            self::Mensagemjson("Erro ao listar cupons inativos", 500);
        }
    }

    /**
     * 🔹 Buscar cupom por código (ex: aplicar no carrinho)
     * GET /cupom/{codigo}
     */
    public function buscarPorCodigo(string $codigo): void
    {
        try {
            $cupom = CupomDao::buscarPorCodigo($codigo);

            if (!$cupom) {
                self::Mensagemjson(
                    "Cupom não encontrado ou inválido",
                    404
                );
                return;
            }

            self::Mensagemjson(
                "Cupom encontrado",
                200,
                $cupom
            );
        } catch (\Throwable $th) {
            self::error("Erro ao buscar cupom: " . $th->getMessage());
            self::Mensagemjson("Erro ao buscar cupom", 500);
        }
    }
}
