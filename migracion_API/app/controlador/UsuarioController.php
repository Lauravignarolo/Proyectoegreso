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

//a lo que tiene que pasar, repo del profe 

// app/controlador/UsuarioController.php

require_once RUTA_MODELO . "/ConectorPDO.php";
require_once RUTA_MODELO . "/UsuarioDAO.php";
require_once RUTA_VISTA . "/RespuestaJson.php";

class UsuarioController
{

    public function gestionar(string $metodo): void
    {
        if (!isset($_SESSION["cedula"])) {
            RespuestaJson::error("Acceso denegado: sesión no iniciada", 401);
        }
        if (!($_SESSION["administrador"] ?? false)) {
            RespuestaJson::error("Acceso denegado: rol incorrecto", 403);
        }

        //https://www.w3schools.com/php/php_match.asp
        match ($metodo) {
            "GET" => $this->listar(),
            "POST" => $this->alta(),
            //PONER PUT
            "PATCH" => $this->modificar(),
            "DELETE" => $this->baja(),
            default => RespuestaJson::error("Método no permitido", 405),
        };
    }

    private function listar(): void
    {
        $conexion = $this->conectar();
        $dao = new UsuarioDAO($conexion);
        RespuestaJson::exito($dao->listarUsuarios());
    }

    private function alta(): void
    {
        $this->verificarCsrf();

        $datos = json_decode(file_get_contents("php://input"), true) ?? [];

        $cedula = trim($datos["cedula"] ?? "");
        $nombre = trim($datos["nombre"] ?? "");
        $apellido = trim($datos["apellido"] ?? "");
        $clave = $datos["clave"] ?? "";
        $confirmarClave = $datos["confirmarClave"] ?? "";
        $rol = trim($datos["rol"] ?? "");

        if ($cedula === "" || $nombre === "" || $apellido === "" || $clave === "" || $confirmarClave === "" || $rol === "") {
            RespuestaJson::error("Existen campos vacíos", 422);
        }
        if (!preg_match("/^[1-9][0-9]{7}$/", $cedula)) {
            RespuestaJson::error("Cédula incorrecta", 422);
        }
        if (strlen($clave) < 12) {
            RespuestaJson::error("La contraseña debe contener al menos 12 caracteres", 422);
        }
        if ($clave !== $confirmarClave) {
            RespuestaJson::error("Las contraseñas ingresadas no coinciden", 422);
        }

        $claveHash = password_hash($clave, PASSWORD_DEFAULT);

        $conexion = $this->conectar();
        $dao = new UsuarioDAO($conexion);
        $resultado = $dao->registrarUsuario($cedula, $nombre, $apellido, $claveHash, $rol);

        if (!$resultado) {
            RespuestaJson::error("No se pudo registrar el empleado", 400);
        }

        RespuestaJson::exito(["mensaje" => "Empleado ingresado exitosamente"], 201);
    }

    private function modificar(): void
    {
        $this->verificarCsrf();

        $datos = json_decode(file_get_contents("php://input"), true) ?? [];

        $cedula = trim($datos["cedula"] ?? "");
        $nombre = trim($datos["nombre"] ?? "");
        $apellido = trim($datos["apellido"] ?? "");
        $clave = $datos["clave"] ?? "";
        $rol = trim($datos["rol"] ?? "");

        if ($cedula === "" || $nombre === "" || $apellido === "" || $rol === "") {
            RespuestaJson::error("Existen campos vacíos", 422);
        }

        $claveHash = password_hash($clave, PASSWORD_DEFAULT);

        $conexion = $this->conectar();
        $dao = new UsuarioDAO($conexion);
        $resultado = $dao->modificarUsuario($cedula, $nombre, $apellido, $claveHash, $rol);

        if (!$resultado) {
            RespuestaJson::error("No se pudo modificar el empleado", 400);
        }

        RespuestaJson::exito(["mensaje" => "Empleado modificado exitosamente"]);
    }

    private function baja(): void
    {
        $this->verificarCsrf();

        $datos = json_decode(file_get_contents("php://input"), true) ?? [];
        $cedula = trim($datos["cedula"] ?? "");

        if ($cedula === "") {
            RespuestaJson::error("Falta la cédula del empleado", 422);
        }

        $conexion = $this->conectar();
        $dao = new UsuarioDAO($conexion);
        $resultado = $dao->eliminarUsuario($cedula);

        if (!$resultado) {
            RespuestaJson::error("No se pudo eliminar el empleado", 400);
        }

        RespuestaJson::exito(["mensaje" => "Empleado eliminado exitosamente"]);
    }

    private function verificarCsrf(): void
    {
        $token = $_SERVER["HTTP_X_CSRF_TOKEN"] ?? "";
        if (!isset($_SESSION["csrfToken"]) || !hash_equals($_SESSION["csrfToken"], $token)) {
            RespuestaJson::error("Solicitud rechazada", 403);
        }
    }

    private function conectar(): PDO
    {
        $conector = new ConectorPDO($_ENV["DB_HOST"] . ":" . $_ENV["DB_PUERTO"], $_ENV["DB_USUARIO"], $_ENV["DB_CLAVE"], $_ENV["DB_NOMBRE"]);
        $conexion = $conector->establecerConexion();
        if ($conexion === null) {
            RespuestaJson::error("Error de conexión con la base de datos", 500);
        }
        return $conexion;
    }
}

