<?php

session_start();

if (!isset($_SESSION["cedula"])) {
    $mensaje = "Acceso Denegado: Sesión no iniciada";
    header("Location: login.php?" . "error=" . $mensaje);
    exit;
}

if ( !isset($_SESSION["direccion"]) || $_SESSION["direccion"] !== true ) {
    $mensaje = "Acceso Denegado: Rol incorrecto";
    header("Location: login.php?" . "error=" . $mensaje);
    exit;
}

require_once __DIR__ . "/../public/direccion/vista/direccion.html";

?>