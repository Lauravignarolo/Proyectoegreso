<?php

require_once __DIR__ . "/../../vendor/autoload.php";

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . "/../..");
$dotenv->load();

require_once __DIR__ . "/../modelo/ConectarPDO.php";
require_once __DIR__ . "/../modelo/AltaDatosSolicitud.php";


if ($_SERVER["REQUEST_METHOD"] !== "POST") {

    header("Location: /app/vista/solicitar.php");
    exit;
}


// Recuperar datos del formulario

$fechaSolicitada = $_POST["fechaSolicitada"] ?? "";
$descripcion = trim($_POST["descripcion"] ?? "");


// Validar campos

if ($fechaSolicitada === "" || $descripcion === "") {

    header("Location: /app/vista/solicitar.php?error=campos");
    exit;
}


// Conectar a la base de datos

$conectorPDO = new ConectorPDO(
    $_ENV["DB_HOST"],
    $_ENV["DB_USUARIO"],
    $_ENV["DB_CLAVE"],
    $_ENV["DB_NOMBRE"]
);

$conexion = $conectorPDO->establecerConexion();


if ($conexion === null) {

    header("Location: /app/vista/solicitar.php?error=conexion");
    exit;
}


// Registrar solicitud

$altaDatosSolicitud = new AltaDatosSolicitud($conexion);

$registroCorrecto = $altaDatosSolicitud->registrarSolicitud(
    $fechaSolicitada,
    $descripcion
);


// Desconectar

$conectorPDO->desconectar();


if ($registroCorrecto) {

    header("Location: /app/vista/solicitar.php?exito=1");
    exit;
}


header("Location: /app/vista/solicitar.php?error=registro");
exit;