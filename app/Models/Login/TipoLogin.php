<?php

namespace App\Models\Login;

class TipoLogin
{
    private int $id_tipo;
    private string $nome;
    private ?string $descricao;
    private string $criado;

    public function __construct(int $id_tipo, string $nome, ?string $descricao = null, string $criado = "")
    {
        $this->id_tipo = $id_tipo;
        $this->nome = $nome;
        $this->descricao = $descricao;
        $this->criado = $criado ?: date("Y-m-d H:i:s");
    }

    public function getId(): int
    {
        return $this->id_tipo;
    }

    public function getNome(): string
    {
        return $this->nome;
    }

    public function getDescricao(): ?string
    {
        return $this->descricao;
    }

    public function getCriado(): string
    {
        return $this->criado;
    }

    public function toArray(): array
    {
        return [
            'id_tipo' => $this->id_tipo,
            'nome' => $this->nome,
            'descricao' => $this->descricao,
            'criado' => $this->criado,
        ];
    }
}
