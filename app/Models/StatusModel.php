<?php

namespace App\Models;

class StatusModel
{
    private string $nome;
    private string $codigo;
    private string $descricao;

    public function __construct(string $nome, string $codigo, string $descricao)
    {
        $this->nome = $nome;
        $this->codigo = $codigo;
        $this->descricao = $descricao;
    }

    public function getNome(): string
    {
        return $this->nome;
    }

    public function getCodigo(): string
    {
        return $this->codigo;
    }

    public function getDescricao(): string
    {
        return $this->descricao;
    }

    public function toArray(): array
    {
        return [
            "nome" => $this->nome,
            "codigo" => $this->codigo,
            "descricao" => $this->descricao,
        ];
    }
}