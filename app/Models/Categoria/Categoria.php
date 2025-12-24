<?php

namespace Imperio\Models;

class Categoria
{
    private ?int $id_categoria;
    private string $nome;
    private string $icone;
    private int $statusid;
    private string $criado;

    public function __construct(
        ?int $id_categoria = null,
        string $nome = "",
        string $icone = "",
        int $statusid = 1,
        string $criado = ""
    ) {
        $this->id_categoria = $id_categoria;
        $this->nome = $nome;
        $this->icone = $icone;
        $this->statusid = $statusid;
        $this->criado = $criado;
    }

    public function getIdCategoria(): ?int { return $this->id_categoria; }
    public function getNome(): string { return $this->nome; }
    public function getIcone(): string { return $this->icone; }
    public function getStatusid(): int { return $this->statusid; }
    public function getCriado(): string { return $this->criado; }

    public function toArray(): array
    {
        return [
            "id_categoria" => $this->id_categoria,
            "nome" => $this->nome,
            "icone" => $this->icone,
            "statusid" => $this->statusid,
            "criado" => $this->criado,
        ];
    }
}
