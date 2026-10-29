<?php

require_once RUTA_NUCLEO . "/Conexion.php";
require_once RUTA_NUCLEO . "/Sesion.php";
require_once RUTA_NUCLEO . "/Token.php";
require_once RUTA_NUCLEO . "/Validacion.php";
require_once RUTA_DAO . "/UsuarioDAO.php";
require_once RUTA_SERVICIO . "/Autenticacion.php";
require_once __DIR__ . "/../../vendor/autoload.php";

/**
 * Controlador del recurso "login".
 *
 * Controla POST /index.php?ruta=login
 */
class ProcesarLogin
{
    public function gestionar(string $metodo): void
    {
        if ($metodo !== "POST") {
            RespuestaJSON::metodoNoPermitido("POST");
        }

        $datos = json_decode(file_get_contents("php://input"), true);

        if (!is_array($datos)) {
            RespuestaJSON::error("JSON inválido", 400);
        }

        $cedula = Validacion::cedula($datos["cedula"] ?? null);
        $clave = $datos["clave"] ?? null;

        if ($cedula === null || !is_string($clave) || $clave === "") {
            RespuestaJSON::error("Credenciales inválidas", 422);
        }

        $dao = new UsuarioDAO(Conexion::conectar());
        $autenticacion = new Autenticacion($dao);

        $usuario = $autenticacion->autenticar($cedula, $clave);

        if ($usuario === null) {
            RespuestaJSON::error("Cédula o contraseña incorrecta", 401);
        }

        if (!$usuario->getEstado()) {
            RespuestaJSON::error("La cuenta está desactivada", 403);
        }

        $rol = $autenticacion->rolDe($usuario);

        if ($rol === null) {
            RespuestaJSON::error("Usuario sin rol autorizado", 403);
        }

        $config = require __DIR__ . "/../../config/secretos.php";
        $claveSecreta = $config["secreto"];

        $ahora = time();

        $claims = [
        "sub" => $usuario->getCedula(), // ID del usuario autenticado
        "rol" => $rol, // Rol del usuario
        "exp" => $ahora + 3600, // Fecha de vencimiento del token
        "ver" => 1 // Versión de la API
        ];

        $token = JWT::encode($claims, $claveSecreta, "HS256");

        RespuestaJSON::exito([
            "cedula" => $usuario->getCedula(),
            "rol" => $rol,
            "csrfToken" => Token::generar()
        ]);
    }
}
