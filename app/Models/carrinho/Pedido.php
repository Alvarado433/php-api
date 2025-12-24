<?php
namespace App\Models\Carrinho;

class Pedido {
    private int $id_pedido;
    private int $usuario_id;
    private int $statusid;
    private float $total;
    private float $frete;
    private int $endereco_id;          // NOVO: vincula ao endereço
    private string $metodo_pagamento;
    private string $pagamento_info;
    private string $criado;
    private string $atualizado;

    public function __construct(
        int $usuario_id,
        int $statusid,
        float $total,
        float $frete,
        int $endereco_id,            // NOVO
        string $metodo_pagamento,
        string $pagamento_info,
        string $criado = '',
        string $atualizado = ''
    ) {
        $this->usuario_id = $usuario_id;
        $this->statusid = $statusid;
        $this->total = $total;
        $this->frete = $frete;
        $this->endereco_id = $endereco_id;  // NOVO
        $this->metodo_pagamento = $metodo_pagamento;
        $this->pagamento_info = $pagamento_info;
        $this->criado = $criado;
        $this->atualizado = $atualizado;
    }

    // GETTERS
    public function getIdPedido(): int {
        return $this->id_pedido;
    }

    public function getUsuarioId(): int {
        return $this->usuario_id;
    }

    public function getStatusId(): int {
        return $this->statusid;
    }

    public function getTotal(): float {
        return $this->total;
    }

    public function getFrete(): float {
        return $this->frete;
    }

    public function getEnderecoId(): int {   // NOVO
        return $this->endereco_id;
    }

    public function getMetodoPagamento(): string {
        return $this->metodo_pagamento;
    }

    public function getPagamentoInfo(): string {
        return $this->pagamento_info;
    }

    public function getCriado(): string {
        return $this->criado;
    }

    public function getAtualizado(): string {
        return $this->atualizado;
    }

    // SETTERS
    public function setIdPedido(int $id_pedido): void {
        $this->id_pedido = $id_pedido;
    }

    public function setUsuarioId(int $usuario_id): void {
        $this->usuario_id = $usuario_id;
    }

    public function setStatusId(int $statusid): void {
        $this->statusid = $statusid;
    }

    public function setTotal(float $total): void {
        $this->total = $total;
    }

    public function setFrete(float $frete): void {
        $this->frete = $frete;
    }

    public function setEnderecoId(int $endereco_id): void {   // NOVO
        $this->endereco_id = $endereco_id;
    }

    public function setMetodoPagamento(string $metodo_pagamento): void {
        $this->metodo_pagamento = $metodo_pagamento;
    }

    public function setPagamentoInfo(string $pagamento_info): void {
        $this->pagamento_info = $pagamento_info;
    }

    public function setCriado(string $criado): void {
        $this->criado = $criado;
    }

    public function setAtualizado(string $atualizado): void {
        $this->atualizado = $atualizado;
    }
}
