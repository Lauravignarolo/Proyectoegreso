<?php
require_once RUTA_MODELO . "/Usuario.php";
class Sesion
{
    public static function iniciar(Usuario $usuario): void
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }

        session_regenerate_id(true);

        $_SESSION["cedula"] = $usuario->getCedula();
        $_SESSION["administrador"] = $usuario->esAdministrador();
        $_SESSION["logistica"] = $usuario->esLogistica();
    }

    public static function cerrar(): void
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }

        $_SESSION = [];
        //https://www.php.net/manual/es/function.session-destroy.php
        if (ini_get("session.use_cookies")) {
            $parametros = session_get_cookie_params();

            setcookie(
                session_name(),
                "",
                time() - 42000,
                $parametros["path"],
                $parametros["domain"],
                $parametros["secure"],
                $parametros["httponly"]
            );
        }

        session_destroy();
    }

    public static function verificarRol(string $rol): void
    {
        if (!isset($_SESSION["cedula"])) {
            RespuestaJSON::error("Acceso denegado: sesión no iniciada", 401);
        }
        
        if (!($_SESSION[$rol] ?? false)) {
            RespuestaJSON::error("Acceso denegado: rol incorrecto", 403);
        }

    }

}