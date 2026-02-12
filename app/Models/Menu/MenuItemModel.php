<?php

namespace App\Models\Menu;

class MenuItemModel
{
    private ?int $id;
    private int $menuId;
    private string $nome;
    private ?string $rota;
    private ?string $icone;
    private ?int $posicao;

    public function __construct(
        ?int $id,
        int $menuId,
        string $nome,
        ?string $rota = null,
        ?string $icone = null,
        ?int $posicao = 0
    ) {
        $this->id = $id;
        $this->menuId = $menuId;
        $this->nome = $nome;
        $this->rota = $rota;
        $this->icone = $icone;
        $this->posicao = $posicao;
    }

    // --------------------------
    // GETTERS
    // --------------------------
    public function getId(): ?int
    {
        return $this->id;
    }

    public function getMenuId(): int
    {
        return $this->menuId;
    }

    public function getNome(): string
    {
        return $this->nome;
    }

    public function getRota(): ?string
    {
        return $this->rota;
    }

    public function getIcone(): ?string
    {
        return $this->icone;
    }

    public function getPosicao(): ?int
    {
        return $this->posicao;
    }

    // --------------------------
    // TO ARRAY (🔥 OBRIGATÓRIO PRA API)
    // --------------------------
    public function toArray(): array
    {
        return [
            "id_item" => $this->id,
            "menu_id" => $this->menuId,
            "nome" => $this->nome,
            "rota" => $this->rota,
            "icone" => $this->icone,
            "posicao" => $this->posicao
        ];
    }
}
