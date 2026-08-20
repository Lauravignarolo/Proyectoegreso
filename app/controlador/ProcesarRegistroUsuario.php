<?php

require_once __DIR__ . "/../../vendor/autoload.php";

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . "/../..");
$dotenv->load();

require_once __DIR__ . "/../modelo/ConectarPDO.php";
require_once __DIR__ . "/../modelo/AltaDatosUsuarios.php";

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: /app/vista/agregaruser.php");
    exit;
}

$cedula = trim($_POST["cedula"] ?? "");
$nombre = trim($_POST["nombre"] ?? "");
$apellido = trim($_POST["apellido"] ?? "");
$clave = $_POST["clave"] ?? "";
$rol = $_POST["rol"] ?? "";


if (
    $cedula === "" ||
    $nombre === "" ||
    $apellido === "" ||
    $clave === "" ||
    $rol === ""
) {
    header("Location: /app/vista/agregaruser.php?error=campos");
    exit;
}

$claveHash = password_hash($clave, PASSWORD_DEFAULT);

$conectorPDO = new ConectorPDO(
    $_ENV["DB_HOST"],
    $_ENV["DB_USUARIO"],
    $_ENV["DB_CLAVE"],
    $_ENV["DB_NOMBRE"]
);

$conexion = $conectorPDO->establecerConexion();

if ($conexion === null) {
    header("Location: /app/vista/agregaruser.php?error=conexion");
    exit;
}

$altaDatosUsuario = new AltaDatosUsuarios($conexion);

$registroCorrecto = $altaDatosUsuario->registrarUsuario(
    $cedula,
    $nombre,
    $apellido,
    $claveHash,
    $rol
);

$conectorPDO->desconectar();

if ($registroCorrecto) {
    header("Location: /app/vista/agregaruser.php?exito=1");
    exit;
}

header("Location: /app/vista/agregaruser.php?error=registro");
exit;

?>