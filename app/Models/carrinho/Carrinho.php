<?php
namespace App\Models\Carrinho;

class Carrinho {
    private int $id_carrinho;
    private int $usuario_id;
    private string $criado;
    private string $atualizado;

    public function __construct(int $usuario_id, string $criado = '', string $atualizado = '') {
        $this->usuario_id = $usuario_id;
        $this->criado = $criado;
        $this->atualizado = $atualizado;
    }

    // GETTERS
    public function getIdCarrinho(): int {
        return $this->id_carrinho;
    }

    public function getUsuarioId(): int {
        return $this->usuario_id;
    }

    public function getCriado(): string {
        return $this->criado;
    }

    public function getAtualizado(): string {
        return $this->atualizado;
    }

    // SETTERS
    public function setIdCarrinho(int $id_carrinho): void {
        $this->id_carrinho = $id_carrinho;
    }

    public function setUsuarioId(int $usuario_id): void {
        $this->usuario_id = $usuario_id;
    }

    public function setCriado(string $criado): void {
        $this->criado = $criado;
    }

    public function setAtualizado(string $atualizado): void {
        $this->atualizado = $atualizado;
    }
}

