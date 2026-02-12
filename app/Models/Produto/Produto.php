<?php

namespace App\Models\Produto;

class Produto
{
    private ?int $id_produto;
    private string $nome;
    private ?string $descricao;
    private float $preco;
    private ?float $preco_promocional; // novo
    private string $slug;
    private ?string $imagem;
    private int $estoque;
    private bool $ilimitado;
    private int $statusid;
    private int $catalogo;
    private ?int $categoria_id;
    private ?int $destaque;
    private ?string $sku; // novo
    private ?string $modelo; // novo
    private ?string $parcelamento; // novo
    private ?string $criado;
    private ?string $atualizado;

    public function __construct(
        ?int $id_produto = null,
        string $nome = "",
        ?string $descricao = null,
        float $preco = 0.00,
        ?float $preco_promocional = null,
        string $slug = "",
        ?string $imagem = null,
        int $estoque = 0,
        bool $ilimitado = false,
        int $statusid = 1,
        int $catalogo = 6,
        ?int $categoria_id = null,
        ?int $destaque = null,
        ?string $sku = null,
        ?string $modelo = null,
        ?string $parcelamento = null,
        ?string $criado = null,
        ?string $atualizado = null
    ) {
        $this->id_produto = $id_produto;
        $this->nome = $nome;
        $this->descricao = $descricao;
        $this->preco = $preco;
        $this->preco_promocional = $preco_promocional;
        $this->slug = $slug;
        $this->imagem = $imagem;
        $this->estoque = $estoque;
        $this->ilimitado = $ilimitado;
        $this->statusid = $statusid;
        $this->catalogo = $catalogo;
        $this->categoria_id = $categoria_id;
        $this->destaque = $destaque;
        $this->sku = $sku;
        $this->modelo = $modelo;
        $this->parcelamento = $parcelamento;
        $this->criado = $criado;
        $this->atualizado = $atualizado;
    }

    // Getters
    public function getIdProduto(): ?int { return $this->id_produto; }
    public function getNome(): string { return $this->nome; }
    public function getDescricao(): ?string { return $this->descricao; }
    public function getPreco(): float { return $this->preco; }
    public function getPrecoPromocional(): ?float { return $this->preco_promocional; }
    public function getSlug(): string { return $this->slug; }
    public function getImagem(): ?string { return $this->imagem; }
    public function getEstoque(): int { return $this->estoque; }
    public function isIlimitado(): bool { return $this->ilimitado; }
    public function getStatusid(): int { return $this->statusid; }
    public function getCatalogo(): int { return $this->catalogo; }
    public function getCategoriaId(): ?int { return $this->categoria_id; }
    public function getDestaque(): ?int { return $this->destaque; }
    public function getSku(): ?string { return $this->sku; }
    public function getModelo(): ?string { return $this->modelo; }
    public function getParcelamento(): ?string { return $this->parcelamento; }
    public function getCriado(): ?string { return $this->criado; }
    public function getAtualizado(): ?string { return $this->atualizado; }

    public function toArray(): array
    {
        return [
            "id_produto" => $this->id_produto,
            "nome" => $this->nome,
            "descricao" => $this->descricao,
            "preco" => $this->preco,
            "preco_promocional" => $this->preco_promocional,
            "slug" => $this->slug,
            "imagem" => $this->imagem,
            "estoque" => $this->estoque,
            "ilimitado" => $this->ilimitado,
            "statusid" => $this->statusid,
            "catalogo" => $this->catalogo,
            "categoria_id" => $this->categoria_id,
            "destaque" => $this->destaque,
            "sku" => $this->sku,
            "modelo" => $this->modelo,
            "parcelamento" => $this->parcelamento,
            "criado" => $this->criado,
            "atualizado" => $this->atualizado,
        ];
    }
}
