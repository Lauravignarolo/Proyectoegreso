<?php

require_once RUTA_MODELO . "/ConectorPDO.php";
require_once RUTA_MODELO . "/AltaDatosUsuario.php";

//Recupera los datos provenientes del formulario
$cedula = trim($_POST["cedula"] ?? "");
$nombre = trim($_POST["nombre"] ?? "");
$apellido = trim($_POST["apellido"] ?? "");

$clave = $_POST["clave"] ?? "";
$confirmarClave = $_POST["confirmarClave"] ?? "";

$rol = trim($_POST["rol"] ?? "");

//Sección que valida los datos recibidos del formulario
if ($cedula === "" || $nombre === "" || $apellido === "" || $clave === "" || $confirmarClave === "" || $rol === "" ) {
    $mensaje = "No se pudo registrar el empleado: existen campos vacíos. ";
    header("Location: administrador.php?error=" . urlencode($mensaje));
    exit;
}

if (!preg_match("/^[1-9][0-9]{7}$/", $cedula)) {
    $mensaje = "No se pudo registrar el empleado: cédula incorrecta.";

    header("Location: administrador.php?error=" . urlencode($mensaje));
    exit;
}

if (strlen($clave) < 12) {
    $mensaje = "La contraseña debe contener al menos 12 caracteres.";

    header("Location: administrador.php?error=" . urlencode($mensaje));
    exit;
}

if ($clave !== $confirmarClave) {
    $mensaje = "Las contraseñas ingresadas no coinciden.";

    header("Location: administrador.php?error=" . urlencode($mensaje));
    exit;
}

//Hasheo básico de la contraseña para almacenar en la base de datos
$claveHash = password_hash($clave, PASSWORD_DEFAULT);

$conectorPDO = new ConectorPDO($_ENV['DB_HOST'] . ":" . $_ENV['DB_PUERTO'], $_ENV['DB_USUARIO'], $_ENV['DB_CLAVE'], $_ENV['DB_NOMBRE']);
$conexion = $conectorPDO->establecerConexion();

    if ($conexion === null) {
        $mensaje = "No se pudo establecer conexión con la base de datos.";
        header("Location: administrador.php?error=" . urlencode($mensaje));
        exit;
    }

    $altaDatosUsuario = new AltaDatosUsuario($conexion);
    $resultado = $altaDatosUsuario->registrarUsuario($cedula, $nombre, $apellido, $claveHash, $rol);

$conectorPDO->desconectar();


if (!$resultado) {
    $mensaje = "No se pudo registrar el empleado.";

    header("Location: administrador.php?error=" . urlencode($mensaje));
    exit;
}

$mensaje = "Empleado ingresado exitosamente.";
header("Location: administrador.php?resultado=" . urlencode($mensaje));
exit;