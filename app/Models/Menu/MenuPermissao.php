<?php

namespace App\Models\Menu;

class MenuPermissao
{
    private int $id_permissao;
    private ?int $menu_id;
    private ?int $menu_item_id;
    private int $nivel_id;
    private string $criado;

    public function __construct(array $dados)
    {
        $this->id_permissao   = (int) ($dados['id_permissao'] ?? 0);
        $this->menu_id        = isset($dados['menu_id']) ? (int) $dados['menu_id'] : null;
        $this->menu_item_id   = isset($dados['menu_item_id']) ? (int) $dados['menu_item_id'] : null;
        $this->nivel_id       = (int) $dados['nivel_id'];
        $this->criado         = $dados['criado'] ?? '';
    }

    public function toArray(): array
    {
        return [
            'id_permissao' => $this->id_permissao,
            'menu_id' => $this->menu_id,
            'menu_item_id' => $this->menu_item_id,
            'nivel_id' => $this->nivel_id,
            'criado' => $this->criado
        ];
    }

    // Getters (opcional, mas recomendado)
    public function getNivelId(): int
    {
        return $this->nivel_id;
    }
}
