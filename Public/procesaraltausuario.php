<?php

require_once __DIR__ . "/../config/config.php";

session_start();

if (!isset($_SESSION["cedula"])) {
    $mensaje = "Acceso Denegado: Sesión no iniciada";

    header("Location: login.php?error=" . urlencode($mensaje));
    exit;
}

if (!($_SESSION["administrador"] ?? false)) {
    $mensaje = "Acceso Denegado: Rol incorrecto";

    header("Location: login.php?error=" . urlencode($mensaje));
    exit;
}

//Comprueba que el formulario haya sido enviado mediante POST
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    $mensaje = "Acceso Denegado: Petición incorrecta";

    header("Location: administrador.php?error=" . urlencode($mensaje));
    exit;
}

//Verifica la existencia del token
if (!isset($_SESSION["csrfToken"])) {
    /**
     * Si el usuario no posee los permisos de acceso correctos por falta
     * de un token, devuelve el estado 403 que es forbidden.
     * 
     * Estados HTTP: https://developer.mozilla.org/en-US/docs/Web/HTTP/Reference/Status#client_error_responses
     */
    http_response_code(403);
    exit("Solicitud Rechazada...");
}

//Si existe el token lo recupera
$csrfToken = $_POST["csrfToken"] ?? "";

//Compara con hash_equals() ambos token (los recibidos por la solicitud y la existente en el servidor)
if (!hash_equals($_SESSION["csrfToken"], $csrfToken)) {
    http_response_code(403);
    exit("Solicitud Rechazada...");
}

require_once RUTA_CONTROLADOR . "/procesarAltaUsuario.php";