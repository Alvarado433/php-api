<?php

namespace App\Models;

class Nivel
{
    private int $id_nivel;
    private string $nome;
    private string $codigo;
    private int $prioridade;
    private string $descricao;
    private string $criado;

    public function __construct(
        int $id_nivel,
        string $nome,
        string $codigo,
        int $prioridade,
        string $descricao,
        string $criado
    ) {
        $this->id_nivel = $id_nivel;
        $this->nome = $nome;
        $this->codigo = $codigo;
        $this->prioridade = $prioridade;
        $this->descricao = $descricao;
        $this->criado = $criado;
    }

    public function toArray(): array
    {
        return [
            'id_nivel' => $this->id_nivel,
            'nome' => $this->nome,
            'codigo' => $this->codigo,
            'prioridade' => $this->prioridade,
            'descricao' => $this->descricao,
            'criado' => $this->criado,
        ];
    }

    public function getId(): int { return $this->id_nivel; }
    public function getNome(): string { return $this->nome; }
    public function getCodigo(): string { return $this->codigo; }
    public function getPrioridade(): int { return $this->prioridade; }
    public function getDescricao(): string { return $this->descricao; }
}
