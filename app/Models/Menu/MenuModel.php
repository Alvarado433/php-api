<?php

namespace App\Models\Menu;

class MenuModel
{
    private ?int $id;
    private string $nome;
    private ?string $icone;
    private ?string $rota;
    private ?string $pesquisaPlaceholder;

    public function __construct(
        ?int $id,
        string $nome,
        ?string $icone = null,
        ?string $rota = null,
        ?string $pesquisaPlaceholder = null
    ) {
        $this->id = $id;
        $this->nome = $nome;
        $this->icone = $icone;
        $this->rota = $rota;
        $this->pesquisaPlaceholder = $pesquisaPlaceholder;
    }

    // =====================
    // GETTERS
    // =====================
    public function getId(): ?int
    {
        return $this->id;
    }

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

    public function getPesquisaPlaceholder(): ?string
    {
        return $this->pesquisaPlaceholder;
    }

    // =====================
    // ARRAY (API / JSON)
    // =====================
    public function toArray(): array
    {
        return [
            "id_menu" => $this->id,
            "nome" => $this->nome,
            "icone" => $this->icone,
            "rota" => $this->rota,
            "pesquisa_placeholder" => $this->pesquisaPlaceholder
        ];
    }
}
