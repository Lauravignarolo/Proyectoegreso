
<?php

require_once __DIR__ . "/../config/config.php";
require_once RUTA_VISTA . "/RespuestaJSON.php";

header("Content-Type: application/json; charset=utf-8");

try {
    $ruta = $_GET["ruta"] ?? "";
    $metodo = $_SERVER["REQUEST_METHOD"] ?? "GET";

    if ($ruta === "") {
        RespuestaJSON::error("Falta indicar la ruta", 400);
    }

    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }

    require_once RUTA_RUTAS . "/api.php";

} catch (Throwable $error) {
    error_log((string) $error);
    RespuestaJSON::error("Error interno del servidor", 500);
}
