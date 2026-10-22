<?php

require_once RUTA_NUCLEO . "/Sesion.php";
require_once RUTA_NUCLEO . "/Token.php";

/**
 * Controlador del recurso "logout".
 *
 * Controla POST /index.php?ruta=logout
 */
class ProcesarLogout
{
    public function gestionar(string $metodo): void
    {
        if ($metodo !== "POST") {
            RespuestaJSON::metodoNoPermitido("POST");
        }

        // Cerrar sesión es una operación que modifica el estado del usuario,
        // así que también va protegida con el token CSRF.
        Token::verificar();

        Sesion::cerrar();

        RespuestaJSON::exito(["mensaje" => "Sesión cerrada"]);
    }
}
