
<?php

require_once RUTA_MODELO . "/ConectarPDO.php";
require_once RUTA_MODELO . "/AccesoDatosUsuario.php";
require_once RUTA_VISTA . "/RespuestaJSON.php";

class ProcesarUsuarios
{
    public function listarUsuarios(string $metodo): void
    {
        if ($metodo !== "GET") {
            RespuestaJSON::error("Método no permitido", 405);
        }

        if (empty($_SESSION["cedula"])) {
            RespuestaJSON::error("Sesión no iniciada", 401);
        }

        if (empty($_SESSION["administrador"])) {
            RespuestaJSON::error("Acceso denegado", 403);
        }

        $conectorPDO = new ConectorPDO(
            $_ENV["DB_HOST"],
            $_ENV["DB_USUARIO"],
            $_ENV["DB_CLAVE"],
            $_ENV["DB_NOMBRE"]
        );

        $conexion = $conectorPDO->establecerConexion();

        $accesoDatosUsuario = new AccesoDatosUsuario($conexion);

        $usuarios = $accesoDatosUsuario->listarUsuarios();

        $conectorPDO->desconectar();

        RespuestaJSON::exito($usuarios);
    }
}
