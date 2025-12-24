<?php

namespace App\Models\Menu;

class MenuModel
{
    private string $nome;
    private ?string $icone;
    private ?string $rota;
    private ?string $pesquisaPlaceholder;

    public function __construct(
        string $nome,
        ?string $icone = null,
        ?string $rota = null,
        ?string $pesquisaPlaceholder = null
    ) {
        $this->nome = $nome;
        $this->icone = $icone;
        $this->rota = $rota;
        $this->pesquisaPlaceholder = $pesquisaPlaceholder;
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

    public function toArray(): array
    {
        return [
            "nome" => $this->nome,
            "icone" => $this->icone,
            "rota" => $this->rota,
            "pesquisa_placeholder" => $this->pesquisaPlaceholder
        ];
    }
}
