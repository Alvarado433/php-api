<?php

namespace App\Models;
class UsuarioLoginModel
{
    private int $id_usuario;
    private string $senha;
    private ?string $ultimo_login;
    private int $tentativas;
    private int $statusid;
    private string $atualizado;
    private string $criado;

    public function __construct(
        int $id_usuario,
        string $senha,
        ?string $ultimo_login,
        int $tentativas,
        int $statusid,
        string $atualizado,
        string $criado
    ){
        $this->id_usuario = $id_usuario;
        $this->senha = $senha;
        $this->ultimo_login = $ultimo_login;
        $this->tentativas = $tentativas;
        $this->statusid = $statusid;
        $this->atualizado = $atualizado;
        $this->criado = $criado;
    }

    public function getId_usuario(): int { return $this->id_usuario; }
    public function getSenha(): string { return $this->senha; }
    public function getUltimo_login(): ?string { return $this->ultimo_login; }
    public function getTentativas(): int { return $this->tentativas; }
    public function getStatusid(): int { return $this->statusid; }
    public function getAtualizado(): string { return $this->atualizado; }
    public function getCriado(): string { return $this->criado; }
}
