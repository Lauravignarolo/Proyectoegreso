<?php
require_once RUTA_CONTROLADOR . "/ProcesarLogin.php";
require_once RUTA_CONTROLADOR . "/UsuarioController.php";
require_once RUTA_CONTROLADOR . "/TicketController.php";

// Espacio donde se encuentran los endpoints que establecen la comunicación con el controlador
switch ($ruta) {
    case "login":
        $controlador = new ProcesarLogin();
        $controlador->autenticar($metodo);
        break;
    case "logout":
        $controlador = new ProcesarLogin();
        $controlador->cerrarSesion($metodo);
        break;
    case "usuarios":
        $controlador = new UsuarioController();
        $controlador->gestionar($metodo);
        break;
    case "tickets":                                    
        $controlador = new TicketController();
        $controlador->gestionar($metodo);
        break;
    default:
        RespuestaJSON::error("Ruta no encontrada", 404);
}