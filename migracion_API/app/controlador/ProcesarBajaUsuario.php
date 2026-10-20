<?php

require_once __DIR__ . "/../modelo/ConectarPDO.php";
require_once __DIR__ . "/../modelo/AccesoDatosUsuario.php";

// Datos de conexión
$servidor = "localhost";
$usuarioBD = "root";
$contrasenaBD = "";
$baseDatos = "proyectoegreso";

// Crear conexión
$conector = new ConectorPDO(
    $servidor,
    $usuarioBD,
    $contrasenaBD,
    $baseDatos
);

$conexion = $conector->establecerConexion();

if ($conexion === null) {
    die("No se pudo conectar a la base de datos.");
}

// Verificar que la solicitud sea POST
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: ../../public/...");
    exit();
}

// Obtener la cédula
$cedula = $_POST["cedula"] ?? "";

if ($cedula === "") {
    header("Location: ../../public/...");
    exit();
}

// Crear acceso a datos
$accesoDatosUsuario = new AccesoDatosUsuario($conexion);

// Eliminar usuario
$accesoDatosUsuario->eliminarUsuario($cedula);

// Volver al listado
header("Location: /app/controlador/ProcesarUsuarios.php");
exit();