<?php

/**
 * Representa a un usuario ya autenticado, con sus datos basicos y los roles que posee.
 */
class Usuario
{
    private string $cedula;
    private string $passwordHash;

    private bool $administrador;
    private bool $docente;
    private bool $direccion;
    private bool $tecnico;

    /**
     * Constructor parametrizado que arma el objeto Usuario con los datos recuperados de la base.
     * @param string $cedula Documento de identidad del usuario.
     * @param string $passwordHash Contrasena ya cifrada (hash), nunca en texto plano.
     * @param bool $administrador Indica si el usuario tiene el rol Administrador.
     * @param bool $docente Indica si el usuario tiene el rol Docente.
     * @param bool $direccion Indica si el usuario tiene el rol Direccion.
     * @param bool $tecnico Indica si el usuario tiene el rol Tecnico.
     */
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