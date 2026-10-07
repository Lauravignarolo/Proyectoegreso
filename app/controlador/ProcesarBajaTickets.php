<?php

require_once __DIR__ . "/../../vendor/autoload.php";

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . "/../..");
$dotenv->load();

require_once __DIR__ . "/../modelo/ConectarPDO.php";
require_once __DIR__ . "/../modelo/BajaDatosSalones.php";


if ($_SERVER["REQUEST_METHOD"] !== "POST") {

    header("Location: /app/vista/salonesver.php");
    exit;
}


$numeroSalon = $_POST["numero_de_salon"] ?? "";
$tipoSalon = $_POST["tipo_de_salon"] ?? "";


if ($numeroSalon === "" || $tipoSalon === "") {

    header("Location: /app/vista/salonesver.php?error=campos");
    exit;
}


$conectorPDO = new ConectorPDO(
    $_ENV["DB_HOST"],
    $_ENV["DB_USUARIO"],
    $_ENV["DB_CLAVE"],
    $_ENV["DB_NOMBRE"]
);

$conexion = $conectorPDO->establecerConexion();


if ($conexion === null) {

    header("Location: /app/vista/salonesver.php?error=conexion");
    exit;
}


$bajaDatosSalones = new BajaDatosSalones($conexion);


$eliminado = $bajaDatosSalones->eliminarSalon(
    (int) $numeroSalon,
    $tipoSalon
);


$conectorPDO->desconectar();


if ($eliminado) {

    header("Location: /app/vista/salonesver.php?exito=baja");
    exit;
}

header("Location: /app/vista/salonesver.php?error=salonEnUso");
exit;