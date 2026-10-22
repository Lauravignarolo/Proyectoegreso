<?php

require_once RUTA_CONTROLADOR . "/ProcesarLogin.php";
require_once RUTA_CONTROLADOR . "/ProcesarLogout.php";
require_once RUTA_CONTROLADOR . "/ProcesarUsuarios.php";
require_once RUTA_CONTROLADOR . "/ProcesarSalones.php";
require_once RUTA_CONTROLADOR . "/ProcesarEquipos.php";
require_once RUTA_CONTROLADOR . "/ProcesarTickets.php";
require_once RUTA_CONTROLADOR . "/ProcesarSolicitudes.php";
require_once RUTA_CONTROLADOR . "/ProcesarRegistroDiario.php";
require_once RUTA_CONTROLADOR . "/ProcesarInventario.php";

/**
 * Tabla de ruteo de la API.
 *
 * Cada recurso tiene su propio controlador "Procesar<Recurso>" y todos
 * exponen el mismo contrato: un único método público `gestionar($metodo)`.
 * El router no sabe nada de la lógica de negocio, solo traduce la ruta
 * en una instancia de controlador y le pasa el verbo HTTP.
 */
$rutas = [
    "login" => ProcesarLogin::class,
    "logout" => ProcesarLogout::class,
    "usuarios" => ProcesarUsuarios::class,
    "salones" => ProcesarSalones::class,
    "equipos" => ProcesarEquipos::class,
    "tickets" => ProcesarTickets::class,
    "solicitudes" => ProcesarSolicitudes::class,
    "registroDiario" => ProcesarRegistroDiario::class,
    "inventario" => ProcesarInventario::class
];

if (!isset($rutas[$ruta])) {
    RespuestaJSON::error("Ruta no encontrada", 404);
}

$controlador = new $rutas[$ruta]();

$controlador->gestionar($metodo);
