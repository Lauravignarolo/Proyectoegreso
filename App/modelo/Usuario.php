<?php

class Usuario {
    private int $cedula;
    private string $claveHash;
    private bool $activo;
    private bool $administrador;
    private bool $logistica;

    public function __construct(int $cedula, string $claveHash, bool $activo, bool $administrador, bool $logistica) {
        $this->cedula = $cedula;
        $this->claveHash = $claveHash;
        $this->activo = $activo;
        $this->administrador = $administrador;
        $this->logistica = $logistica;
    }

    public function getCedula(): int {
        return $this->cedula;
    }

    public function getClaveHash(): string {
        return $this->claveHash;
    }

    public function estaActivo(): bool {
        return $this->activo;
    }

    public function esAdministrador(): bool {
        return $this->administrador;
    }

    public function esLogistica(): bool {
        return $this->logistica;
    }
}

?>