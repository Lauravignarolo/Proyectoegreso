<?php

require_once __DIR__ . "/../../vendor/autoload.php";

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . "/../..");
$dotenv->load();

require_once __DIR__ . "/../modelo/ConectarPDO.php";
require_once __DIR__ . "/../modelo/AccesoDatosTickets.php";

$conectorPDO = new ConectorPDO(
    $_ENV["DB_HOST"],
    $_ENV["DB_USUARIO"],
    $_ENV["DB_CLAVE"],
    $_ENV["DB_NOMBRE"]
);

$conexion = $conectorPDO->establecerConexion();

$accesoDatosTickets = new AccesoDatosTickets($conexion);

$tickets = $accesoDatosTickets->listarTickets();

$conectorPDO->desconectar();

require_once __DIR__ . "/../vista/tickets.php";