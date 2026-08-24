<?php

require_once "../modelo/ConectarPDO.php";
require_once "../modelo/AccesoDatosUsuario.php";
require_once "../modelo/users.php";

// Datos de conexión
$servidor = "localhost";
$usuario = "root";
$contrasena = "";
$baseDatos = "proyectoegreso";

// Crear el conector
$conector = new ConectorPDO(
    $servidor,
    $usuario,
    $contrasena,
    $baseDatos
);

// Establecer conexión
$conexion = $conector->establecerConexion();

if ($conexion === null) {
    die("No se pudo conectar a la base de datos.");
}

// Crear acceso a datos
$accesoDatosUsuario = new AccesoDatosUsuario($conexion);

// Obtener usuarios
$usuarios = $accesoDatosUsuario->listarUsuarios();

// Cargar vista
require_once "../vista/verusers.php";