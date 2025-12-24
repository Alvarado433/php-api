<?php
namespace App\Models\Carrinho;

class CarrinhoEndereco {
    private int $id_endereco;
    private int $carrinho_id;
    private string $cep;
    private string $rua;
    private string $numero;
    private ?string $complemento;
    private string $bairro;
    private string $cidade;
    private string $estado;
    private string $criado;
    private string $atualizado;

    public function __construct(
        int $carrinho_id,
        string $cep,
        string $rua,
        string $numero,
        string $bairro,
        string $cidade,
        string $estado,
        ?string $complemento = null,
        string $criado = '',
        string $atualizado = ''
    ) {
        $this->carrinho_id = $carrinho_id;
        $this->cep = $cep;
        $this->rua = $rua;
        $this->numero = $numero;
        $this->bairro = $bairro;
        $this->cidade = $cidade;
        $this->estado = $estado;
        $this->complemento = $complemento;
        $this->criado = $criado;
        $this->atualizado = $atualizado;
    }

    // GETTERS
    public function getIdEndereco(): int {
        return $this->id_endereco;
    }

    public function getCarrinhoId(): int {
        return $this->carrinho_id;
    }

    public function getCep(): string {
        return $this->cep;
    }

    public function getRua(): string {
        return $this->rua;
    }

    public function getNumero(): string {
        return $this->numero;
    }

    public function getComplemento(): ?string {
        return $this->complemento;
    }

    public function getBairro(): string {
        return $this->bairro;
    }

    public function getCidade(): string {
        return $this->cidade;
    }

    public function getEstado(): string {
        return $this->estado;
    }

    public function getCriado(): string {
        return $this->criado;
    }

    public function getAtualizado(): string {
        return $this->atualizado;
    }

    // SETTERS
    public function setIdEndereco(int $id_endereco): void {
        $this->id_endereco = $id_endereco;
    }

    public function setCarrinhoId(int $carrinho_id): void {
        $this->carrinho_id = $carrinho_id;
    }

    public function setCep(string $cep): void {
        $this->cep = $cep;
    }

    public function setRua(string $rua): void {
        $this->rua = $rua;
    }

    public function setNumero(string $numero): void {
        $this->numero = $numero;
    }

    public function setComplemento(?string $complemento): void {
        $this->complemento = $complemento;
    }

    public function setBairro(string $bairro): void {
        $this->bairro = $bairro;
    }

    public function setCidade(string $cidade): void {
        $this->cidade = $cidade;
    }

    public function setEstado(string $estado): void {
        $this->estado = $estado;
    }

    public function setCriado(string $criado): void {
        $this->criado = $criado;
    }

    public function setAtualizado(string $atualizado): void {
        $this->atualizado = $atualizado;
    }
}
