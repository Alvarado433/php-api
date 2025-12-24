<?php

namespace App\Models\Footer;

class FooterLink
{
    private ?int $id_link;
    private int $footer_id;
    private string $titulo;
    private string $url;
    private ?string $icone;
    private int $ordem;
    private int $statusid;
    private string $criado;
    private ?string $atualizado;

    public function __construct(
        ?int $id_link = null,
        int $footer_id = 0,
        string $titulo = "",
        string $url = "",
        ?string $icone = null,
        int $ordem = 0,
        int $statusid = 1,
        string $criado = "",
        ?string $atualizado = null
    ) {
        $this->id_link = $id_link;
        $this->footer_id = $footer_id;
        $this->titulo = $titulo;
        $this->url = $url;
        $this->icone = $icone;
        $this->ordem = $ordem;
        $this->statusid = $statusid;
        $this->criado = $criado;
        $this->atualizado = $atualizado;
    }

    // Getters
    public function getId(): ?int { return $this->id_link; }
    public function getFooterId(): int { return $this->footer_id; }
    public function getTitulo(): string { return $this->titulo; }
    public function getUrl(): string { return $this->url; }
    public function getIcone(): ?string { return $this->icone; }
    public function getOrdem(): int { return $this->ordem; }
    public function getStatusId(): int { return $this->statusid; }
    public function getCriado(): string { return $this->criado; }
    public function getAtualizado(): ?string { return $this->atualizado; }

    // Converter para array
    public function toArray(): array {
        return [
            'id_link' => $this->id_link,
            'footer_id' => $this->footer_id,
            'titulo' => $this->titulo,
            'url' => $this->url,
            'icone' => $this->icone,
            'ordem' => $this->ordem,
            'statusid' => $this->statusid,
            'criado' => $this->criado,
            'atualizado' => $this->atualizado
        ];
    }
}
