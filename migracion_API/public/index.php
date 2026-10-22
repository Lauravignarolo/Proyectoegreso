<?php

require_once __DIR__ . "/../config/config.php";
require_once RUTA_VISTA . "/RespuestaJSON.php";

header("Content-Type: application/json; charset=utf-8");
header("X-Content-Type-Options: nosniff");

/**
 * Front controller de la API.
 *
 * Toda petición entra por acá: se normaliza la ruta y el verbo, se abre la
 * sesión con cookies endurecidas y se delega en la tabla de ruteo.
 *
 * La ruta y el verbo son las dos únicas entradas del protocolo, así que
 * el router queda como una tabla `ruta -> controlador`.
 */
try {
    $ruta = trim((string) ($_GET["ruta"] ?? ""));
    $metodo = strtoupper((string) ($_SERVER["REQUEST_METHOD"] ?? "GET"));

    if ($ruta === "") {
        RespuestaJSON::error("Falta indicar la ruta", 400);
    }

    // La sesión viaja en una cookie: se endurecen los parámetros para
    // que no pueda leerse desde JavaScript ni viajar en claro.
    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_set_cookie_params([
            "lifetime" => 0,
            "path" => "/",
            "secure" => !empty($_SERVER["HTTPS"]),
            "httponly" => true,
            "samesite" => "Strict"
        ]);

        session_start();
    }

    require_once RUTA_RUTAS . "/api.php";
} catch (Throwable $error) {
    error_log("[API] " . $error->getMessage() . " en " . $error->getFile() . ":" . $error->getLine());

    // El detalle del error solo se muestra cuando APP_DEBUG está activo
    // en el .env. En producción el cliente recibe un mensaje genérico.
    $debug = filter_var($_ENV["APP_DEBUG"] ?? "false", FILTER_VALIDATE_BOOLEAN);

    if ($debug) {
        RespuestaJSON::error(
            "Error: " . $error->getMessage() .
            " | Archivo: " . $error->getFile() .
            " | Línea: " . $error->getLine(),
            500
        );
    }

    RespuestaJSON::error("Error interno del servidor", 500);
}
