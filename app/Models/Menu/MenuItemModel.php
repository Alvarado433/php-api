<?php

namespace App\Models\Menu;

class MenuItemModel
{
    private string $nome;
    private ?string $icone;
    private ?string $rota;
    private ?int $posicao;
    private ?int $menuId; // referência ao menu pai (para dropdown)

    public function __construct(
        string $nome,
        ?string $icone = null,
        ?string $rota = null,
        ?int $posicao = null,
        ?int $menuId = null
    ) {
        $this->nome = $nome;
        $this->icone = $icone;
        $this->rota = $rota;
        $this->posicao = $posicao;
        $this->menuId = $menuId;
    }

    // --------------------------
    // GETTERS
    // --------------------------
    public function getNome(): string
    {
        return $this->nome;
    }

    public function getIcone(): ?string
    {
        return $this->icone;
    }

    public function getRota(): ?string
    {
        return $this->rota;
    }

    public function getPosicao(): ?int
    {
        return $this->posicao;
    }

    public function getMenuId(): ?int
    {
        return $this->menuId;
    }

    // --------------------------
    // TO ARRAY
    // --------------------------
    public function toArray(): array
    {
        return [
            "nome" => $this->nome,
            "icone" => $this->icone,
            "rota" => $this->rota,
            "posicao" => $this->posicao,
            "menu_id" => $this->menuId
        ];
    }
}
