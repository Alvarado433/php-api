<?php

namespace App\Models\Menu;

class MenuItemConfigModel
{
    private int $menuId;
    private string $tipo;    // 'logo', 'search', 'link', 'icon'
    private int $posicao;    // Ordem de exibição

    public function __construct(int $menuId, string $tipo, int $posicao)
    {
        $this->menuId = $menuId;
        $this->tipo = $tipo;
        $this->posicao = $posicao;
    }

    public function getMenuId(): int
    {
        return $this->menuId;
    }

    public function getTipo(): string
    {
        return $this->tipo;
    }

    public function getPosicao(): int
    {
        return $this->posicao;
    }

    public function toArray(): array
    {
        return [
            "menu_id" => $this->menuId,
            "tipo" => $this->tipo,
            "posicao" => $this->posicao
        ];
    }
}
