<?php

namespace App\Models\Cupom;

class CupomModel
{
    private ?int $id_cupom = null;
    private string $codigo;
    private string $descricao;

    private int $tipo_id;
    private float $desconto;

    private float $valor_minimo;
    private ?int $limite_uso = null;
    private int $usado = 0;

    private ?string $inicio = null;
    private ?string $expiracao = null;

    private int $statusid;
    private int $publico; // novo campo
    private ?string $criado = null;
    private ?string $atualizado = null;

    /* =========================
     * CONSTRUTOR
     * ========================= */
    public function __construct(
        ?int $id_cupom,
        string $codigo,
        string $descricao,
        int $tipo_id,
        float $desconto,
        float $valor_minimo,
        ?int $limite_uso,
        ?string $inicio,
        ?string $expiracao,
        int $statusid,
        int $publico
    ) {
        $this->id_cupom     = $id_cupom;
        $this->codigo       = strtoupper($codigo);
        $this->descricao    = $descricao;
        $this->tipo_id      = $tipo_id;
        $this->desconto     = $desconto;
        $this->valor_minimo = $valor_minimo;
        $this->limite_uso   = $limite_uso;
        $this->inicio       = $inicio;
        $this->expiracao    = $expiracao;
        $this->statusid     = $statusid;
        $this->publico      = $publico;
    }

    /* =========================
     * GETTERS
     * ========================= */
    public function getIdCupom(): ?int { return $this->id_cupom; }
    public function getCodigo(): string { return $this->codigo; }
    public function getDescricao(): string { return $this->descricao; }
    public function getTipoId(): int { return $this->tipo_id; }
    public function getDesconto(): float { return $this->desconto; }
    public function getValorMinimo(): float { return $this->valor_minimo; }
    public function getLimiteUso(): ?int { return $this->limite_uso; }
    public function getUsado(): int { return $this->usado; }
    public function getInicio(): ?string { return $this->inicio; }
    public function getExpiracao(): ?string { return $this->expiracao; }
    public function getStatusId(): int { return $this->statusid; }
    public function getPublico(): int { return $this->publico; }

    /* =========================
     * SETTERS
     * ========================= */
    public function setIdCupom(?int $id): void { $this->id_cupom = $id; }
    public function setCodigo(string $codigo): void { $this->codigo = strtoupper($codigo); }
    public function setDescricao(string $descricao): void { $this->descricao = $descricao; }
    public function setTipoId(int $tipo_id): void { $this->tipo_id = $tipo_id; }
    public function setDesconto(float $desconto): void { $this->desconto = $desconto; }
    public function setValorMinimo(float $valor): void { $this->valor_minimo = $valor; }
    public function setLimiteUso(?int $limite): void { $this->limite_uso = $limite; }
    public function setUsado(int $usado): void { $this->usado = $usado; }
    public function setInicio(?string $inicio): void { $this->inicio = $inicio; }
    public function setExpiracao(?string $expiracao): void { $this->expiracao = $expiracao; }
    public function setStatusId(int $statusid): void { $this->statusid = $statusid; }
    public function setPublico(int $publico): void { $this->publico = $publico; }
}
