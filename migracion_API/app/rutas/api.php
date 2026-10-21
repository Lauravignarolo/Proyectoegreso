
<?php

require_once RUTA_CONTROLADOR . "/ProcesarRegistroDiario.php";
require_once RUTA_CONTROLADOR . "/ProcesarVerSolicitudes.php";
require_once RUTA_CONTROLADOR . "/ProcesarLogin.php";
require_once RUTA_CONTROLADOR . "/TicketController.php";
require_once RUTA_CONTROLADOR . "/ProcesarAltaUsuario.php";
require_once RUTA_CONTROLADOR . "/ProcesarSalones.php";
require_once RUTA_CONTROLADOR . "/ProcesarUsuarios.php";
require_once RUTA_CONTROLADOR . "/ProcesarSolicitud.php";

switch ($ruta) {

    case "salones":
        $controlador = new ProcesarSalones();
        if ($metodo === "GET") {
            $controlador->listarSalones($metodo);
        } elseif ($metodo === "POST") {
            $controlador->agregarSalon($metodo);
        } elseif ($metodo === "DELETE") {
            $controlador->eliminarSalon($metodo);
        } else {
            RespuestaJSON::error("Método no permitido", 405);
        }
        break;

    case "altaUsuario":
        $controlador = new ProcesarAltaUsuario();
        $controlador->registrarUsuario($metodo);
        break;

    case "solicitudes":
        if ($metodo === "GET") {
            $controlador = new ProcesarVerSolicitudes();
            $controlador->listarSolicitudes($metodo);
        } elseif ($metodo === "POST") {
            $controlador = new ProcesarSolicitud();
            $controlador->registrarSolicitud($metodo);
        } else {
            header("Allow: GET, POST");
            RespuestaJson::error("Método no permitido", 405);
        }
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
        $controlador->gestionarUsuarios($metodo);
        break;

    case "tickets":
        $controlador = new TicketController();
        $controlador->gestionar($metodo);
        break;
    
    case "registroDiario":
        $controlador = new ProcesarRegistroDiario();
        $controlador->registrar($metodo);
        break;

    default:
        RespuestaJson::error("Ruta no encontrada", 404);
        break;
}
