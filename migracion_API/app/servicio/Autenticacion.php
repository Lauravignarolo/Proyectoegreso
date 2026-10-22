<?php

require_once RUTA_MODELO . "/Usuario.php";
require_once RUTA_DAO . "/UsuarioDAO.php";

/**
 * Servicio de autenticación.
 *
 * Contains the authentication rules: check the credentials and determine
 * the role of the user. This is business logic, so it does not live in the
 * controller nor in the DAO.
 */
class Autenticacion
{
    private UsuarioDAO $usuarios;

    public function __construct(UsuarioDAO $usuarios)
    {
        $this->usuarios = $usuarios;
    }

    /**
     * Checks the credentials and returns the authenticated user,
     * or null if the credentials are invalid.
     */
    public function autenticar(string $cedula, string $clave): ?Usuario
    {
        $usuario = $this->usuarios->buscarPorCedula($cedula);

        if ($usuario === null) {
            return null;
        }

        // An inactive user cannot log in even with the right password.
        if (!$usuario->getEstado()) {
            return null;
        }

        if (!password_verify($clave, $usuario->getClaveHash())) {
            return null;
        }

        return $usuario;
    }

    /**
     * Returns the role of the user (the only one it can have),
     * or null if it has none.
     */
    public function rolDe(Usuario $usuario): ?string
    {
        return match (true) {
            $usuario->esAdministrador() => "administrador",
            $usuario->esDocente() => "docente",
            $usuario->esTecnico() => "tecnico",
            $usuario->esDireccion() => "direccion",
            default => null
        };
    }
}
