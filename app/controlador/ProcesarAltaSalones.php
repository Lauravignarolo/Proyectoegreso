<?php

require_once __DIR__ . "/../../vendor/autoload.php";

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . "/../..");
$dotenv->load();

require_once __DIR__ . "/../modelo/ConectarPDO.php";
require_once __DIR__ . "/../modelo/AltaDatosSalones.php";


if ($_SERVER["REQUEST_METHOD"] !== "POST") {

    header("Location: /app/vista/salonesver.php");
    exit;
}


$tipoSalon = $_POST["tipo_de_salon"] ?? "";
$numeroSalon = $_POST["numero_de_salon"] ?? "";


if ($tipoSalon === "" || $numeroSalon === "") {

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


$altaDatosSalones = new AltaDatosSalones($conexion);


$registroCorrecto = $altaDatosSalones->registrarSalon(
    $tipoSalon,
    (int) $numeroSalon
);


$conectorPDO->desconectar();


if ($registroCorrecto) {

    header("Location: /app/vista/salonesver.php?exito=1");
    exit;
}


header("Location: /app/vista/salonesver.php?error=registro");
exit;