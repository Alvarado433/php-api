<?php

namespace App\Models\cupom;

class CupomTipoModel
{
    private ?int $id_tipo = null;
    private string $nome;
    private string $codigo;
    private ?string $descricao = null;
    private int $statusid;
    private ?string $criado = null;
    private ?string $atualizado = null;

    /* =========================
     * GETTERS
     * ========================= */

    public function getIdTipo(): ?int
    {
        return $this->id_tipo;
    }

    public function getNome(): string
    {
        return $this->nome;
    }

    public function getCodigo(): string
    {
        return $this->codigo;
    }

    public function getDescricao(): ?string
    {
        return $this->descricao;
    }

    public function getStatusId(): int
    {
        return $this->statusid;
    }

    public function getCriado(): ?string
    {
        return $this->criado;
    }

    public function getAtualizado(): ?string
    {
        return $this->atualizado;
    }

    /* =========================
     * SETTERS
     * ========================= */

    public function setIdTipo(?int $id): void
    {
        $this->id_tipo = $id;
    }

    public function setNome(string $nome): void
    {
        $this->nome = $nome;
    }

    public function setCodigo(string $codigo): void
    {
        $this->codigo = $codigo;
    }

    public function setDescricao(?string $descricao): void
    {
        $this->descricao = $descricao;
    }

    public function setStatusId(int $statusid): void
    {
        $this->statusid = $statusid;
    }

    public function setCriado(?string $criado): void
    {
        $this->criado = $criado;
    }

    public function setAtualizado(?string $atualizado): void
    {
        $this->atualizado = $atualizado;
    }
}
