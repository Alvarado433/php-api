<?php

namespace App\Models;

class BannerModel
{
    private string $titulo;
    private string $descricao;
    private string $imagem;
    private ?string $link;

    private int $visualizacoes;
    private int $cliques;

    public function __construct(
        string $titulo,
        string $descricao,
        string $imagem,
        ?string $link = null,
        int $visualizacoes = 0,
        int $cliques = 0
    ) {
        $this->titulo = $titulo;
        $this->descricao = $descricao;
        $this->imagem = $imagem;
        $this->link = $link;
        $this->visualizacoes = $visualizacoes;
        $this->cliques = $cliques;
    }

    public function getTitulo(): string
    {
        return $this->titulo;
    }

    public function getDescricao(): string
    {
        return $this->descricao;
    }

    public function getImagem(): string
    {
        return $this->imagem;
    }

    public function getLink(): ?string
    {
        return $this->link;
    }

    public function getVisualizacoes(): int
    {
        return $this->visualizacoes;
    }

    public function getCliques(): int
    {
        return $this->cliques;
    }

    public function toArray(): array
    {
        return [
            "titulo" => $this->titulo,
            "descricao" => $this->descricao,
            "imagem" => $this->imagem,
            "link" => $this->link,
            "visualizacoes" => $this->visualizacoes,
            "cliques" => $this->cliques
        ];
    }
}

