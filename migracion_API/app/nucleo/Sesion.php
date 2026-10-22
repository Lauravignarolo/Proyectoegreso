<?php

require_once RUTA_VISTA . "/RespuestaJSON.php";
require_once RUTA_MODELO . "/Usuario.php";

/**
 * Manejo de la sesión del usuario autenticado.
 *
 * La sesión guarda: cédula, rol y los cuatro flags de rol. Los flags se
 * derivan del rol único que tiene el usuario, para que no puedan quedar
 * desincronizados (que es lo que pasaba antes con "logistica", un rol que
 * ni siquiera existe en la base de datos).
 */
class Sesion
{
    /**
     * Roles válidos del sistema. Coinciden con las tablas de roles del DDL.
     */
    public const ROLES = ["administrador", "docente", "direccion", "tecnico"];

    /**
     * Abre la sesión del usuario y regenera el identificador de sesión
     * (protección contra session fixation).
     */
    public static function iniciar(Usuario $usuario, string $rol): void
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }

        session_regenerate_id(true);

        $_SESSION["cedula"] = $usuario->getCedula();
        $_SESSION["rol"] = $rol;
        $_SESSION["csrfToken"] = bin2hex(random_bytes(32));

        foreach (self::ROLES as $rolGuardado) {
            $_SESSION[$rolGuardado] = ($rolGuardado === $rol);
        }
    }

    /**
     * Destruye la sesión y expira la cookie del navegador.
     */
    public static function cerrar(): void
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            return;
        }

        $_SESSION = [];

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

    /**
     * ¿Hay una sesión iniciada?
     */
    public static function haySesion(): bool
    {
        return isset($_SESSION["cedula"]) && $_SESSION["cedula"] !== "";
    }

    /**
     * Cédula del usuario de la sesión, o null si no hay sesión.
     */
    public static function cedula(): ?string
    {
        if (!self::haySesion()) {
            return null;
        }

        return (string) $_SESSION["cedula"];
    }

    /**
     * Rol del usuario de la sesión, o null si no hay sesión.
     */
    public static function rol(): ?string
    {
        if (!isset($_SESSION["rol"])) {
            return null;
        }

        return (string) $_SESSION["rol"];
    }

    /**
     * Corta la petición si no hay sesión iniciada (401).
     */
    public static function exigirSesion(): void
    {
        if (!self::haySesion()) {
            RespuestaJSON::error("Acceso denegado: sesión no iniciada", 401);
        }
    }

    /**
     * Corta la petición si no hay sesión (401) o si el rol de la sesión no
     * está entre los permitidos (403).
     *
     * @param string ...$roles Roles que pueden operar sobre el recurso.
     */
    public static function exigirRol(string ...$roles): void
    {
        self::exigirSesion();

        $rolActual = self::rol();

        if ($rolActual === null || !in_array($rolActual, $roles, true)) {
            RespuestaJSON::error("Acceso denegado: rol incorrecto", 403);
        }
    }
}
