<?php

namespace Imperio\Models;

class ProdutoCategoria
{
    private ?int $id;
    private int $produto_id;
    private int $categoria_id;

    public function __construct(
        ?int $id = null,
        int $produto_id = 0,
        int $categoria_id = 0
    ) {
        $this->id = $id;
        $this->produto_id = $produto_id;
        $this->categoria_id = $categoria_id;
    }

    public function getId(): ?int { return $this->id; }
    public function getProdutoId(): int { return $this->produto_id; }
    public function getCategoriaId(): int { return $this->categoria_id; }

    public function toArray(): array
    {
        return [
            "id" => $this->id,
            "produto_id" => $this->produto_id,
            "categoria_id" => $this->categoria_id,
        ];
    }
}
