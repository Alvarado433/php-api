<?php

namespace App\Models\Produto;

class Produto
{
    private ?int $id_produto;
    private string $nome;
    private ?string $descricao;
    private float $preco;
    private string $slug;
    private ?string $imagem;
    private int $estoque;
    private bool $ilimitado;
    private int $statusid;
    private int $catalogo;
    private ?int $categoria_id;
    private ?int $destaque;      // novo: referencia produto_destaque.id_destaque
    private ?string $criado;
    private ?string $atualizado;

    public function __construct(
        ?int $id_produto = null,
        string $nome = "",
        ?string $descricao = null,
        float $preco = 0.00,
        string $slug = "",
        ?string $imagem = null,
        int $estoque = 0,
        bool $ilimitado = false,
        int $statusid = 1,
        int $catalogo = 6,       // default Catálogo Não
        ?int $categoria_id = null,
        ?int $destaque = null,   // novo
        ?string $criado = null,
        ?string $atualizado = null
    ) {
        $this->id_produto = $id_produto;
        $this->nome = $nome;
        $this->descricao = $descricao;
        $this->preco = $preco;
        $this->slug = $slug;
        $this->imagem = $imagem;
        $this->estoque = $estoque;
        $this->ilimitado = $ilimitado;
        $this->statusid = $statusid;
        $this->catalogo = $catalogo;
        $this->categoria_id = $categoria_id;
        $this->destaque = $destaque; // novo
        $this->criado = $criado;
        $this->atualizado = $atualizado;
    }

    // Getters
    public function getIdProduto(): ?int { return $this->id_produto; }
    public function getNome(): string { return $this->nome; }
    public function getDescricao(): ?string { return $this->descricao; }
    public function getPreco(): float { return $this->preco; }
    public function getSlug(): string { return $this->slug; }
    public function getImagem(): ?string { return $this->imagem; }
    public function getEstoque(): int { return $this->estoque; }
    public function isIlimitado(): bool { return $this->ilimitado; }
    public function getStatusid(): int { return $this->statusid; }
    public function getCatalogo(): int { return $this->catalogo; }
    public function getCategoriaId(): ?int { return $this->categoria_id; }
    public function getDestaque(): ?int { return $this->destaque; } // novo
    public function getCriado(): ?string { return $this->criado; }
    public function getAtualizado(): ?string { return $this->atualizado; }

    public function toArray(): array
    {
        return [
            "id_produto" => $this->id_produto,
            "nome" => $this->nome,
            "descricao" => $this->descricao,
            "preco" => $this->preco,
            "slug" => $this->slug,
            "imagem" => $this->imagem,
            "estoque" => $this->estoque,
            "ilimitado" => $this->ilimitado,
            "statusid" => $this->statusid,
            "catalogo" => $this->catalogo,
            "categoria_id" => $this->categoria_id,
            "destaque" => $this->destaque, // novo
            "criado" => $this->criado,
            "atualizado" => $this->atualizado,
        ];
    }

    // ✅ Método público para pegar um status
    public static function status(string $nome): ?int
    {
        return self::$status[$nome] ?? null;
    }
}
