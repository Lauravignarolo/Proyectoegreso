<?php

require_once __DIR__ . "/../../vendor/autoload.php";

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . "/../..");
$dotenv->load();

require_once __DIR__ . "/../modelo/ConectarPDO.php";
require_once __DIR__ . "/../modelo/AltaDatosTickets.php";


if ($_SERVER["REQUEST_METHOD"] !== "POST") {

    header("Location: /app/vista/creartickets.php");
    exit;
}


// Recuperar datos del formulario

$estudianteACargo = trim($_POST["estudiante_a_cargo"] ?? "");
$horaDeEntrada = $_POST["hora_de_entrada"] ?? "";
$horaDeSalida = $_POST["hora_de_salida"] ?? "";
$tipoDeSalon = $_POST["tipo_de_salon"] ?? "";
$numeroDeSalon = $_POST["numero_de_salon"] ?? "";
$numeroDeEquipo = $_POST["numero_de_equipo"] ?? "";
$asignatura = trim($_POST["asignatura"] ?? "");
$grupo = trim($_POST["grupo"] ?? "");
$turno = $_POST["turno"] ?? "";
$estado = trim($_POST["estado"] ?? "");


// Validar campos

if (
    $estudianteACargo === "" ||
    $horaDeEntrada === "" ||
    $horaDeSalida === "" ||
    $tipoDeSalon === "" ||
    $numeroDeSalon === "" ||
    $numeroDeEquipo === "" ||
    $asignatura === "" ||
    $grupo === "" ||
    $turno === "" ||
    $estado === ""
) {

    header("Location: /app/vista/creartickets.php?error=campos");
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

    header("Location: /app/vista/creartickets.php?error=conexion");
    exit;
}


// Registrar ticket

$altaDatosTickets = new AltaDatosTickets($conexion);

$registroCorrecto = $altaDatosTickets->registrarTickets(
    $estudianteACargo,
    $horaDeEntrada,
    $horaDeSalida,
    $tipoDeSalon,
    (int) $numeroDeSalon,
    (int) $numeroDeEquipo,
    $asignatura,
    $grupo,
    $turno,
    $estado
);


// Desconectar

$conectorPDO->desconectar();


if ($registroCorrecto) {

    header("Location: /app/vista/creartickets.php?exito=1");
    exit;
}


header("Location: /app/vista/creartickets.php?error=registro");
exit;