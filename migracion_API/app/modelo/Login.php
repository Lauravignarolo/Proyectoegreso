<?php

require_once __DIR__ . "/AccesoDatosUsuario.php";

/**
 * Clase encargada de autenticar a un usuario, validando su cedula y contrasena.
 */
class Login
{
    private AccesoDatosUsuario $accesoDatosUsuario;

    /**
     * Constructor parametrizado que recibe el objeto de acceso a datos de usuario.
     * @param AccesoDatosUsuario $accesoDatosUsuario Objeto usado para buscar al usuario en la base.
     */
    public function __construct(AccesoDatosUsuario $accesoDatosUsuario)
    {
        $this->accesoDatosUsuario = $accesoDatosUsuario;
    }

    /**
     * Verifica que la cedula exista y que la contrasena ingresada coincida con el hash guardado.
     * @param string $cedula Cedula ingresada por el usuario.
     * @param string $clave Contrasena ingresada por el usuario, en texto plano.
     * @return Usuario|null El usuario autenticado si las credenciales son correctas, null en caso contrario.
     */
    public function autenticar(string $cedula, string $clave): ?Usuario
    {
        $usuario = $this->accesoDatosUsuario->buscarUsuario($cedula);

        if ($usuario === null) {
            return null;
        }

        if (!password_verify($clave, $usuario->getClaveHash())) {
            return null;
        }

        return $usuario;
    }
}

?>