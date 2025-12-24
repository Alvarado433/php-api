<?php

namespace App\Models\Usuario;

class UsuarioModel
{
    private int $id_usuario;
    private string $nome;
    private string $email;
    private string $senha;  // hash da senha
    private ?string $pin;
    private int $nivel_id;
    private int $statusid;
    private ?string $telefone;
    private ?string $cpf;
    private string $criado;
    private string $atualizado;

    public function __construct(
        int $id_usuario,
        string $nome,
        string $email,
        string $senha,
        ?string $pin,
        int $nivel_id,
        int $statusid,
        ?string $telefone,
        ?string $cpf,
        string $criado,
        string $atualizado
    ) {
        $this->id_usuario = $id_usuario;
        $this->nome = $nome;
        $this->email = $email;
        $this->senha = $senha;
        $this->pin = $pin;
        $this->nivel_id = $nivel_id;
        $this->statusid = $statusid;
        $this->telefone = $telefone;
        $this->cpf = $cpf;
        $this->criado = $criado;
        $this->atualizado = $atualizado;
    }

    // Getters
    public function getId(): int { return $this->id_usuario; }
    public function getNome(): string { return $this->nome; }
    public function getEmail(): string { return $this->email; }
    public function getSenha(): string { return $this->senha; }
    public function getPin(): ?string { return $this->pin; }
    public function getNivelId(): int { return $this->nivel_id; }
    public function getStatusId(): int { return $this->statusid; }
    public function getTelefone(): ?string { return $this->telefone; }
    public function getCpf(): ?string { return $this->cpf; }
    public function getCriado(): string { return $this->criado; }
    public function getAtualizado(): string { return $this->atualizado; }

    // Converter para array
    public function toArray(): array
    {
        return [
            'id_usuario' => $this->id_usuario,
            'nome' => $this->nome,
            'email' => $this->email,
            'senha' => $this->senha,
            'pin' => $this->pin,
            'nivel_id' => $this->nivel_id,
            'statusid' => $this->statusid,
            'telefone' => $this->telefone,
            'cpf' => $this->cpf,
            'criado' => $this->criado,
            'atualizado' => $this->atualizado,
        ];
    }
}
