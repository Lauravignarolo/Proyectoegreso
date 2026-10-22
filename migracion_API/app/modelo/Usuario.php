<?php

/**
 * Entidad Usuario: representa a una persona registrada en el sistema,
 * con sus datos básicos y los roles que posee.
 */
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

    /**
     * @return string La cédula del usuario.
     */
    public function getCedula(): string
    {
        return $this->cedula;
    }

    /**
     * @return string El hash de la contraseña (nunca en texto plano).
     */
    public function getClaveHash(): string
    {
        return $this->passwordHash;
    }

    /**
     * @return bool True si el usuario está activo.
     */
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
