<?php

class Usuario {
    private string $cedula;
    private string $passwordHash;

    private bool $administrador;
    private bool $docente;
    private bool $direccion;
    private bool $tecnico;

    public function __construct(string $cedula, string $claveHash, bool $sesionActiva, bool $administrador, bool $logistica) {
        $this->cedula = $cedula;
        $this->passwordHash = $passwordHash;

        $this->administrador = $administrador;
        $this->docente = $docente;
        $this->direccion = $direccion;
        $this->tecnico = $tecnico;
    }

    /**
     * @return string La cedula del usuario.
     */
    public function getCedula(): string
    {
        return $this->cedula;
    }

    /**
     * @return string El hash de la contrasena del usuario.
     */
    public function getClaveHash(): string
    {
        return $this->passwordHash;
    }

    /**
     * @return bool True si el usuario tiene el rol Administrador.
     */
    public function esAdministrador(): bool
    {
        return $this->administrador;
    }

    /**
     * @return bool True si el usuario tiene el rol Docente.
     */
    public function esDocente(): bool
    {
        return $this->docente;
    }

    /**
     * @return bool True si el usuario tiene el rol Direccion.
     */
    public function esDireccion(): bool
    {
        return $this->direccion;
    }

    /**
     * @return bool True si el usuario tiene el rol Tecnico.
     */
    public function esTecnico(): bool
    {
        return $this->tecnico;
    }
}