<?php

namespace App\Models;

class BannerModel
{
    private ?int $id_banner;
    private string $titulo;
    private string $descricao;
    private string $imagem;
    private ?string $link;
    private int $statusid;
    private int $visualizacoes;
    private int $cliques;

    public function __construct(
        ?int $id_banner = null,
        string $titulo = "",
        string $descricao = "",
        string $imagem = "",
        ?string $link = null,
        int $statusid = 1,
        int $visualizacoes = 0,
        int $cliques = 0
    ) {
        $this->id_banner = $id_banner;
        $this->titulo = $titulo;
        $this->descricao = $descricao;
        $this->imagem = $imagem;
        $this->link = $link;
        $this->statusid = $statusid;
        $this->visualizacoes = $visualizacoes;
        $this->cliques = $cliques;
    }

    // ===== GETTERS =====
    public function getIdBanner(): ?int
    {
        return $this->id_banner;
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

    public function getStatusid(): int
    {
        return $this->statusid;
    }

    public function getVisualizacoes(): int
    {
        return $this->visualizacoes;
    }

    public function getCliques(): int
    {
        return $this->cliques;
    }

    // ===== SETTERS =====
    public function setIdBanner(?int $id): self
    {
        $this->id_banner = $id;
        return $this;
    }

    public function setTitulo(string $titulo): self
    {
        $this->titulo = $titulo;
        return $this;
    }

    public function setDescricao(string $descricao): self
    {
        $this->descricao = $descricao;
        return $this;
    }

    public function setImagem(string $imagem): self
    {
        $this->imagem = $imagem;
        return $this;
    }

    public function setLink(?string $link): self
    {
        $this->link = $link;
        return $this;
    }

    public function setStatusid(int $statusid): self
    {
        $this->statusid = $statusid;
        return $this;
    }

    public function setVisualizacoes(int $visualizacoes): self
    {
        $this->visualizacoes = $visualizacoes;
        return $this;
    }

    public function setCliques(int $cliques): self
    {
        $this->cliques = $cliques;
        return $this;
    }

    // ===== SERIALIZAÇÃO =====
    public function toArray(): array
    {
        return [
            "id_banner" => $this->id_banner,
            "titulo" => $this->titulo,
            "descricao" => $this->descricao,
            "imagem" => $this->imagem,
            "link" => $this->link,
            "statusid" => $this->statusid,
            "visualizacoes" => $this->visualizacoes,
            "cliques" => $this->cliques,
        ];
    }

    // (opcional) ajudar a criar objeto direto do DB
    public static function fromDb(array $b): self
    {
        return new self(
            isset($b["id_banner"]) ? (int)$b["id_banner"] : null,
            (string)($b["titulo"] ?? ""),
            (string)($b["descricao"] ?? ""),
            (string)($b["imagem"] ?? ""),
            isset($b["link"]) ? (string)$b["link"] : null,
            (int)($b["statusid"] ?? 1),
            (int)($b["visualizacoes"] ?? 0),
            (int)($b["cliques"] ?? 0)
        );
    }
}