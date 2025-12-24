<?php

namespace Imperio\Controllers;

use Config\Base\Basecontrolador;
use App\Dao\categoria\CategoriaDao;

class CategoriaController extends Basecontrolador
{
    /**
     * 🔹 Listar todas as categorias
     */
    public static function listar()
    {
        try {
            $dados = CategoriaDao::listar();
            self::info("Categorias listadas com sucesso");

            self::Mensagemjson(
                "Categorias carregadas",
                200,
                $dados
            );
        } catch (\Throwable $th) {
            self::error("Erro ao listar categorias: " . $th->getMessage());

            self::Mensagemjson(
                "Erro ao listar categorias",
                500
            );
        }
    }

    /**
     * 🔹 Listar categorias ativas
     */
    public static function listarAtivas()
    {
        try {
            $dados = CategoriaDao::listarAtivas();
            self::info("Categorias ativas listadas com sucesso");

            self::Mensagemjson(
                "Categorias ativas carregadas",
                200,
                $dados
            );
        } catch (\Throwable $th) {
            self::error("Erro ao listar categorias ativas: " . $th->getMessage());

            self::Mensagemjson(
                "Erro ao listar categorias ativas",
                500
            );
        }
    }

    /**
     * 🔹 Buscar categoria por ID
     */
    public static function buscar($id)
    {
        try {
            $categoria = CategoriaDao::buscarPorId((int)$id);

            if (!$categoria) {
                self::Mensagemjson("Categoria não encontrada", 404);
                return;
            }

            self::info("Categoria {$id} encontrada");

            self::Mensagemjson(
                "Categoria encontrada",
                200,
                $categoria
            );
        } catch (\Throwable $th) {
            self::error("Erro ao buscar categoria: " . $th->getMessage());

            self::Mensagemjson(
                "Erro ao buscar categoria",
                500
            );
        }
    }

    /**
     * 🔹 Criar categoria
     */
    public static function criar()
    {
        try {
            $dados = self::receberJson();

            CategoriaDao::criar(
                $dados['nome'],
                $dados['icone'],
                $dados['statusid'] ?? 1
            );

            self::success("Categoria criada com sucesso");

            self::Mensagemjson(
                "Categoria criada com sucesso",
                201
            );
        } catch (\Throwable $th) {
            self::error("Erro ao criar categoria: " . $th->getMessage());

            self::Mensagemjson(
                "Erro ao criar categoria",
                500
            );
        }
    }

    /**
     * 🔹 Atualizar categoria
     */
    public static function atualizar($id)
    {
        try {
            $dados = self::receberJson();

            CategoriaDao::atualizar(
                (int)$id,
                $dados['nome'],
                $dados['icone'],
                $dados['statusid'] ?? 1
            );

            self::success("Categoria {$id} atualizada");

            self::Mensagemjson(
                "Categoria atualizada com sucesso",
                200
            );
        } catch (\Throwable $th) {
            self::error("Erro ao atualizar categoria: " . $th->getMessage());

            self::Mensagemjson(
                "Erro ao atualizar categoria",
                500
            );
        }
    }

    /**
     * 🔹 Deletar categoria
     */
    public static function deletar($id)
    {
        try {
            CategoriaDao::deletar((int)$id);

            self::warning("Categoria {$id} deletada");

            self::Mensagemjson(
                "Categoria removida com sucesso",
                200
            );
        } catch (\Throwable $th) {
            self::error("Erro ao deletar categoria: " . $th->getMessage());

            self::Mensagemjson(
                "Erro ao deletar categoria",
                500
            );
        }
    }
}
