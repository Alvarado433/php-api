<?php

namespace App\Models\Produto;

class ProdutoDestaque
{
    private ?int $id_destaque;
    private int $produto_id;
    private int $ordem;
    private int $statusid;
    private ?string $criado;

    public function __construct(
        ?int $id_destaque = null,
        int $produto_id = 0,
        int $ordem = 0,
        int $statusid = 1,
        ?string $criado = null
    ) {
        $this->id_destaque = $id_destaque;
        $this->produto_id = $produto_id;
        $this->ordem = $ordem;
        $this->statusid = $statusid;
        $this->criado = $criado;
    }

    public function getIdDestaque(): ?int
    {
        return $this->id_destaque;
    }

    public function getProdutoId(): int
    {
        return $this->produto_id;
    }

    public function getOrdem(): int
    {
        return $this->ordem;
    }

    public function getStatusid(): int
    {
        return $this->statusid;
    }

    public function getCriado(): ?string
    {
        return $this->criado;
    }

    public function toArray(): array
    {
        return [
            "id_destaque" => $this->id_destaque,
            "produto_id" => $this->produto_id,
            "ordem" => $this->ordem,
            "statusid" => $this->statusid,
            "criado" => $this->criado,
        ];
    }
}
