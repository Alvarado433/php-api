<?php
namespace App\Models\carrinho;

class CarrinhoItem {
    private int $id_item;
    private int $carrinho_id;
    private int $produto_id;
    private int $quantidade;
    private float $preco_unitario;

    public function __construct(int $carrinho_id, int $produto_id, int $quantidade, float $preco_unitario) {
        $this->carrinho_id = $carrinho_id;
        $this->produto_id = $produto_id;
        $this->quantidade = $quantidade;
        $this->preco_unitario = $preco_unitario;
    }

    // GETTERS
    public function getIdItem(): int {
        return $this->id_item;
    }

    public function getCarrinhoId(): int {
        return $this->carrinho_id;
    }

    public function getProdutoId(): int {
        return $this->produto_id;
    }

    public function getQuantidade(): int {
        return $this->quantidade;
    }

    public function getPrecoUnitario(): float {
        return $this->preco_unitario;
    }

    // SETTERS
    public function setIdItem(int $id_item): void {
        $this->id_item = $id_item;
    }

    public function setCarrinhoId(int $carrinho_id): void {
        $this->carrinho_id = $carrinho_id;
    }

    public function setProdutoId(int $produto_id): void {
        $this->produto_id = $produto_id;
    }

    public function setQuantidade(int $quantidade): void {
        $this->quantidade = $quantidade;
    }

    public function setPrecoUnitario(float $preco_unitario): void {
        $this->preco_unitario = $preco_unitario;
    }
}

