<?php

require_once __DIR__ . "/../../vendor/autoload.php";

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . "/../..");
$dotenv->load();

require_once __DIR__ . "/../modelo/ConectarPDO.php";
require_once __DIR__ . "/../modelo/AccesoDatosUsuario.php";
require_once __DIR__ . "/../modelo/users.php";
require_once __DIR__ . "/../modelo/Login.php";

//Comprueba que el formulario haya sido enviado mediante POST
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    $mensaje = "Acceso Denegado: Petición incorrecta";
    header("Location: login.php?" . "error=" . $mensaje);
    exit;
}

//Recupera las credenciales provenientes del formulario
$cedula = trim($_POST["cedula"] ?? "");
$clave = $_POST["clave"] ?? "";

$conectorPDO = new ConectorPDO(
    $_ENV['DB_HOST'],
    $_ENV['DB_USUARIO'],
    $_ENV['DB_CLAVE'],
    $_ENV['DB_NOMBRE']
);
$conexion = $conectorPDO->establecerConexion();

if ($conexion === null) {
        $mensaje = "Acceso Denegado: Problemas con la conexión.";
        header("Location: login.php?error=" . urlencode($mensaje));
        exit;
    }


    $accesoDatosUsuario = new AccesoDatosUsuario($conexion);
    $login = new Login($accesoDatosUsuario);

$usuario = $login->autenticar($cedula, $clave);


$conectorPDO->desconectar();

//Si las credenciales no coinciden, muestra el error y detiene el proceso
if ($usuario === null) {
    $mensaje = "Acceso Denegado: La cédula o la contraseña son incorrectas.";
    header("Location: login.php?error=" . urlencode($mensaje));
    exit;
}

//negar usuarios

if ($usuario->esAdministrador()) {
    header("Location: ../vista/administrador.php");
    exit;
}

if ($usuario->esDocente()) {
    header("Location: ../vista/docente.php");
    exit;
}

if ($usuario->esTecnico()) {
    header("Location: ../vista/tecnico.php");
    exit;
}

if ($usuario->esDireccion()) {
    header("Location: ../vista/direccion.php");
    exit;
}

header("Location: login.php");
exit;

session_start();
session_regenerate_id(true);

$_SESSION["cedula"] = $usuario->getCedula();
$_SESSION["administrador"] = $usuario->esAdministrador();
$_SESSION["tecnico"] = $usuario->esTecnico();
$_SESSION["docente"] = $usuario->esDocente();
$_SESSION["direccion"] = $usuario->esDireccion();

if ($_SESSION["administrador"]) {
    header("Location: panelRoles.php");
} elseif ($_SESSION["administrador"]) {
    header("Location: administrador.php");
}

exit;

?>