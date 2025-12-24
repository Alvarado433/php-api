<?php

namespace App\Models\Login;



class ConfiguracaoLogin
{
    private int $id;
    private string $titulo;
    private string $logo;
    private string $fundo;
    private ?string $mensagem_personalizada;
    private int $tipo_login_id;
    private ?TipoLogin $tipoLogin = null;
    private int $statusid;
    private string $criado;
    private string $atualizado;

    public function __construct(
        int $id,
        string $titulo,
        string $logo,
        string $fundo,
        ?string $mensagem_personalizada,
        int $tipo_login_id,
        int $statusid,
        string $criado,
        string $atualizado
    ) {
        $this->id = $id;
        $this->titulo = $titulo;
        $this->logo = $logo;
        $this->fundo = $fundo;
        $this->mensagem_personalizada = $mensagem_personalizada;
        $this->tipo_login_id = $tipo_login_id;
        $this->statusid = $statusid;
        $this->criado = $criado;
        $this->atualizado = $atualizado;
    }

    // -----------------------------
    // GETTERS
    // -----------------------------
    public function getId(): int
    {
        return $this->id;
    }

    public function getTitulo(): string
    {
        return $this->titulo;
    }

    public function getLogo(): string
    {
        return $this->logo;
    }

    public function getFundo(): string
    {
        return $this->fundo;
    }

    public function getMensagemPersonalizada(): ?string
    {
        return $this->mensagem_personalizada;
    }

    public function getTipoLoginId(): int
    {
        return $this->tipo_login_id;
    }

    public function getTipoLogin(): ?TipoLogin
    {
        return $this->tipoLogin;
    }

    public function getStatusId(): int
    {
        return $this->statusid;
    }

    public function getCriado(): string
    {
        return $this->criado;
    }

    public function getAtualizado(): string
    {
        return $this->atualizado;
    }

    // -----------------------------
    // SETTERS / RELACIONAMENTOS
    // -----------------------------
    public function setTipoLogin(TipoLogin $tipoLogin): void
    {
        $this->tipoLogin = $tipoLogin;
    }

    // -----------------------------
    // ARRAY / SERIALIZAÇÃO
    // -----------------------------
    public function toArray(): array
    {
        return [
            "id" => $this->id,
            "titulo" => $this->titulo,
            "logo" => $this->logo,
            "fundo" => $this->fundo,
            "mensagem_personalizada" => $this->mensagem_personalizada,
            "tipo_login_id" => $this->tipo_login_id,
            "tipo_login" => $this->tipoLogin ? $this->tipoLogin->toArray() : null,
            "statusid" => $this->statusid,
            "criado" => $this->criado,
            "atualizado" => $this->atualizado,
        ];
    }
}
