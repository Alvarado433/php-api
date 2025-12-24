<?php

namespace App\Models;

class SegurancaConfig
{
    private int $id;
    private string $pin_sistema;
    private int $tentativas_max;
    private int $statusid;
    private string $criado;
    private string $atualizado;

    public function __construct(int $id, string $pin_sistema, int $tentativas_max, int $statusid, string $criado, string $atualizado)
    {
        $this->id = $id;
        $this->pin_sistema = $pin_sistema;
        $this->tentativas_max = $tentativas_max;
        $this->statusid = $statusid;
        $this->criado = $criado;
        $this->atualizado = $atualizado;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getPin_sistema(): string
    {
        return $this->pin_sistema;
    }

    public function getTentativas_max(): int
    {
        return $this->tentativas_max;
    }

    public function getStatusid(): int
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

    public function toArray(): array
    {
        return [
            "id" => $this->id,
            "pin_sistema" => $this->pin_sistema,
            "tentativas_max" => $this->tentativas_max,
            "statusid" => $this->statusid,
            "criado" => $this->criado,
            "atualizado" => $this->atualizado,
        ];
    }
}