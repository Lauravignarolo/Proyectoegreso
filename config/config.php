
<?php

require_once __DIR__ . '/../clases/ConectorPDO.php';

$servidor = "localhost";
$usuario = "root";
$password = "";
$baseDatos = "proyectoegreso";

$conexionBD = new ConectorPDO(
    $servidor,
    $usuario,
    $password,
    $baseDatos
);

$conexion = $conexionBD->establecerConexion();

if ($conexion === null) {
    die("Error: no se pudo conectar a la base de datos.");
}
