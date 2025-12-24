<?php

namespace App\Models\Usuario;

class SessaoModel
{
    private int $id_sessao;
    private int $usuario_id;
    private string $token;
    private string $ip;
    private string $user_agent;
    private int $statusid;
    private string $criado;
    private string $expira_em;

    public function __construct(
        int $id_sessao,
        int $usuario_id,
        string $token,
        string $ip,
        string $user_agent,
        int $statusid,
        string $criado,
        string $expira_em
    ) {
        $this->id_sessao = $id_sessao;
        $this->usuario_id = $usuario_id;
        $this->token = $token;
        $this->ip = $ip;
        $this->user_agent = $user_agent;
        $this->statusid = $statusid;
        $this->criado = $criado;
        $this->expira_em = $expira_em;
    }

    // Getters
    public function getId(): int { return $this->id_sessao; }
    public function getUsuarioId(): int { return $this->usuario_id; }
    public function getToken(): string { return $this->token; }
    public function getIp(): string { return $this->ip; }
    public function getUserAgent(): string { return $this->user_agent; }
    public function getStatusId(): int { return $this->statusid; }
    public function getCriado(): string { return $this->criado; }
    public function getExpiraEm(): string { return $this->expira_em; }

    // Converter para array
    public function toArray(): array
    {
        return [
            'id_sessao' => $this->id_sessao,
            'usuario_id' => $this->usuario_id,
            'token' => $this->token,
            'ip' => $this->ip,
            'user_agent' => $this->user_agent,
            'statusid' => $this->statusid,
            'criado' => $this->criado,
            'expira_em' => $this->expira_em,
        ];
    }
}