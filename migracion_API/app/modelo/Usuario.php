<?php

class Usuario
{
    public function __construct(
        private string $cedula,
        private string $passwordHash,
        private bool $estado,
        private bool $administrador,
        private bool $docente,
        private bool $direccion,
        private bool $tecnico
    ) {}

    public function getCedula(): string
    {
        return $this->cedula;
    }

    public function getClaveHash(): string
    {
        return $this->passwordHash;
    }

    public function getEstado(): bool
    {
        return $this->estado;
    }

    public function esAdministrador(): bool
    {
        return $this->administrador;
    }

    public function esDocente(): bool
    {
        return $this->docente;
    }

    public function esDireccion(): bool
    {
        return $this->direccion;
    }

    public function esTecnico(): bool
    {
        return $this->tecnico;
    }
}