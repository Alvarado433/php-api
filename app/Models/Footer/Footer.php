<?php

namespace App\Models\Footer;

class Footer
{
    private ?int $id_footer;
    private ?string $logo;
    private string $titulo;
    private ?string $descricao;
    private ?string $endereco;
    private ?string $icone; // Novo campo ícone
    private int $statusid;
    private string $criado;
    private ?string $atualizado;

    // Relacionamento: links do footer
    private array $links = [];

    public function __construct(
        ?int $id_footer = null,
        ?string $logo = null,
        string $titulo = "",
        ?string $descricao = null,
        ?string $endereco = null,
        ?string $icone = null,
        int $statusid = 1,
        string $criado = "",
        ?string $atualizado = null,
        array $links = []
    ) {
        $this->id_footer = $id_footer;
        $this->logo = $logo;
        $this->titulo = $titulo;
        $this->descricao = $descricao;
        $this->endereco = $endereco;
        $this->icone = $icone;
        $this->statusid = $statusid;
        $this->criado = $criado;
        $this->atualizado = $atualizado;
        $this->links = $links;
    }

    // Getters
    public function getId(): ?int { return $this->id_footer; }
    public function getLogo(): ?string { return $this->logo; }
    public function getTitulo(): string { return $this->titulo; }
    public function getDescricao(): ?string { return $this->descricao; }
    public function getEndereco(): ?string { return $this->endereco; }
    public function getIcone(): ?string { return $this->icone; }
    public function getStatusId(): int { return $this->statusid; }
    public function getCriado(): string { return $this->criado; }
    public function getAtualizado(): ?string { return $this->atualizado; }
    public function getLinks(): array { return $this->links; }

    // Adicionar link
    public function addLink(FooterLink $link): void {
        $this->links[] = $link;
    }

    // Converter para array
    public function toArray(): array {
        return [
            'id_footer' => $this->id_footer,
            'logo' => $this->logo,
            'titulo' => $this->titulo,
            'descricao' => $this->descricao,
            'endereco' => $this->endereco,
            'icone' => $this->icone,
            'statusid' => $this->statusid,
            'criado' => $this->criado,
            'atualizado' => $this->atualizado,
            'links' => array_map(fn($link) => $link->toArray(), $this->links)
        ];
    }
}
