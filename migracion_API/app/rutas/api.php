
<?php

require_once RUTA_CONTROLADOR . "/ProcesarAltaUsuario.php";
require_once RUTA_CONTROLADOR . "/ProcesarSalones.php";
require_once RUTA_CONTROLADOR . "/ProcesarLogin.php";
require_once RUTA_CONTROLADOR . "/ProcesarUsuarios.php";

switch ($ruta) {
    case "altaUsuario":
    $controlador = new ProcesarAltaUsuario();
    $controlador->registrarUsuario($metodo);
    break;
    
    case "salones":
    $controlador = new ProcesarSalones();

    if ($metodo === "GET") {
        $controlador->listarSalones($metodo);
    } elseif ($metodo === "DELETE") {
        $controlador->eliminarSalon($metodo);
    } else {
        RespuestaJSON::error("Método no permitido", 405);
    }
    break;
    
    case "salones":
    $controlador = new ProcesarSalones();
    $controlador->listarSalones($metodo);
    break;

    case "login":
        $controlador = new ProcesarLogin();
        $controlador->autenticar($metodo);
        break;

    case "logout":
        $controlador = new ProcesarLogin();
        $controlador->cerrarSesion($metodo);
        break;

    case "usuarios":
        $controlador = new ProcesarUsuarios();
        $controlador->listarUsuarios($metodo);
        break;

    default:
        RespuestaJSON::error("Ruta no encontrada", 404);
}
