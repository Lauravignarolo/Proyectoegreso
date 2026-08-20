<?php

class Usuario
{
    private string $cedula;
    private string $passwordHash;

    private bool $administrador;
    private bool $docente;
    private bool $direccion;
    private bool $tecnico;

    public function __construct(
        string $cedula,
        string $passwordHash,
        bool $administrador,
        bool $docente,
        bool $direccion,
        bool $tecnico
    ) {
        $this->cedula = $cedula;
        $this->passwordHash = $passwordHash;

        $this->administrador = $administrador;
        $this->docente = $docente;
        $this->direccion = $direccion;
        $this->tecnico = $tecnico;
    }

    public function getCedula(): string
    {
        return $this->cedula;
    }

    public function getClaveHash(): string
    {
        return $this->passwordHash;
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

?>