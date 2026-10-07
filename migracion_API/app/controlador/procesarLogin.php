<?php

require_once __DIR__ . "/../../vendor/autoload.php";

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . "/../..");
$dotenv->load();

require_once __DIR__ . "/../modelo/ConectarPDO.php";
require_once __DIR__ . "/../modelo/AccesoDatosUsuario.php";
require_once __DIR__ . "/../modelo/users.php";
require_once __DIR__ . "/../modelo/Login.php";


// Comprueba que el formulario haya sido enviado mediante POST
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    $mensaje = "Acceso Denegado: Petición incorrecta";
    header("Location: /public/login.php?error=" . urlencode($mensaje));
    exit;
}


// Recupera las credenciales provenientes del formulario
$cedula = trim($_POST["cedula"] ?? "");
$clave = $_POST["clave"] ?? "";


// Conexión a la base de datos
$conectorPDO = new ConectorPDO(
    $_ENV["DB_HOST"],
    $_ENV["DB_USUARIO"],
    $_ENV["DB_CLAVE"],
    $_ENV["DB_NOMBRE"]
);

$conexion = $conectorPDO->establecerConexion();


if ($conexion === null) {
    $mensaje = "Acceso Denegado: Problemas con la conexión.";
    header("Location: /public/login.php?error=" . urlencode($mensaje));
    exit;
}


// Crea los objetos para realizar la autenticación
$accesoDatosUsuario = new AccesoDatosUsuario($conexion);
$login = new Login($accesoDatosUsuario);


// Autentica al usuario
$usuario = $login->autenticar($cedula, $clave);


// Cierra la conexión
$conectorPDO->desconectar();


// Si las credenciales no coinciden
if ($usuario === null) {
    $mensaje = "Acceso Denegado: La cédula o la contraseña son incorrectas.";
    header("Location: /public/login.php?error=" . urlencode($mensaje));
    exit;
}


// Inicia la sesión
session_start();
session_regenerate_id(true);


// Guarda los datos del usuario en la sesión
$_SESSION["cedula"] = $usuario->getCedula();
$_SESSION["administrador"] = $usuario->esAdministrador();
$_SESSION["tecnico"] = $usuario->esTecnico();
$_SESSION["docente"] = $usuario->esDocente();
$_SESSION["direccion"] = $usuario->esDireccion();


// Redirección según el rol
if ($usuario->esAdministrador()) {
    header("Location: /app/vista/administrador.php");
    exit;
}

if ($usuario->esDocente()) {
    header("Location: /app/vista/docente.php");
    exit;
}

if ($usuario->esTecnico()) {
    header("Location: /app/vista/tecnico.php");
    exit;
}

if ($usuario->esDireccion()) {
    header("Location: /app/vista/direccion.php");
    exit;
}


// Si no tiene ningún rol
header("Location: /public/login.php");
exit;

?>
