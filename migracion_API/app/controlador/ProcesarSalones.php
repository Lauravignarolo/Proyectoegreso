
<?php

require_once RUTA_MODELO . "/AltaDatosSalones.php";
require_once RUTA_MODELO . "/BajaDatosSalones.php";
require_once RUTA_MODELO . "/ConectarPDO.php";
require_once RUTA_MODELO . "/AccesoDatosSalones.php";
require_once RUTA_VISTA . "/RespuestaJSON.php";

error_log("AGREGAR SALON - Método recibido: " . $metodo);

class ProcesarSalones
{
    public function listarSalones(string $metodo): void
    {
        if ($metodo !== "GET") {
            RespuestaJSON::error("Método no permitido", 405);
            return;
        }

        if (empty($_SESSION["cedula"])) {
            RespuestaJSON::error("Sesión no iniciada", 401);
            return;
        }

        $conector = new ConectorPDO(
            $_ENV["DB_HOST"],
            $_ENV["DB_USUARIO"],
            $_ENV["DB_CLAVE"],
            $_ENV["DB_NOMBRE"]
        );

        $conexion = $conector->establecerConexion();

        $accesoDatosSalones = new AccesoDatosSalones($conexion);
        $salones = $accesoDatosSalones->listarSalones();

        RespuestaJSON::exito($salones);
    }
    public function eliminarSalon(string $metodo): void
{
    if ($metodo !== "DELETE") {
        RespuestaJSON::error("Método no permitido", 405);
        return;
    }

    if (empty($_SESSION["cedula"])) {
        RespuestaJSON::error("Sesión no iniciada", 401);
        return;
    }

    if (empty($_SESSION["administrador"])) {
        RespuestaJSON::error("Acceso denegado", 403);
        return;
    }

    $token = $_SERVER["HTTP_X_CSRF_TOKEN"] ?? "";

if (
    empty($_SESSION["csrfToken"]) ||
    !hash_equals($_SESSION["csrfToken"], $token)
) {
    RespuestaJSON::error("Token CSRF inválido", 403);
    return;
}

    $datos = json_decode(file_get_contents("php://input"), true);

    if (!is_array($datos)) {
        RespuestaJSON::error("JSON inválido", 400);
        return;
    }

    $tipo = trim((string) ($datos["tipo_de_salon"] ?? ""));
    $numero = $datos["numero_de_salon"] ?? null;

    if ($tipo === "" || !is_scalar($numero) ||
        !ctype_digit((string) $numero)) {
        RespuestaJSON::error("Datos del salón inválidos", 422);
        return;
    }

    $conector = new ConectorPDO(
        $_ENV["DB_HOST"],
        $_ENV["DB_USUARIO"],
        $_ENV["DB_CLAVE"],
        $_ENV["DB_NOMBRE"]
    );

    $conexion = $conector->establecerConexion();

    $bajaDatosSalones = new BajaDatosSalones($conexion);

$resultado = $bajaDatosSalones->eliminarSalon(
    (int) $numero,
    $tipo
);

    if (!$resultado) {
        RespuestaJSON::error("No se pudo eliminar el salón", 400);
        return;
    }

    RespuestaJSON::exito(["mensaje" => "Salón eliminado correctamente"]);
}

public function agregarSalon(string $metodo): void
{
    if ($metodo !== "POST") {
        RespuestaJSON::error("Método no permitido", 405);
        return;
    }

    if (empty($_SESSION["cedula"])) {
        RespuestaJSON::error("Sesión no iniciada", 401);
        return;
    }

    if (empty($_SESSION["administrador"])) {
        RespuestaJSON::error("Acceso denegado", 403);
        return;
    }

    // Validar token CSRF
    $token = $_SERVER["HTTP_X_CSRF_TOKEN"] ?? "";

    if (
        empty($_SESSION["csrfToken"]) ||
        !hash_equals($_SESSION["csrfToken"], $token)
    ) {
        RespuestaJSON::error("Token CSRF inválido", 403);
        return;
    }

    // Recuperar los datos enviados desde JavaScript
    $datos = json_decode(file_get_contents("php://input"), true);

    if (!is_array($datos)) {
        RespuestaJSON::error("JSON inválido", 400);
        return;
    }

    $tipo = trim((string) ($datos["tipo_de_salon"] ?? ""));
    $numero = $datos["numero_de_salon"] ?? null;

    if (
        $tipo === "" ||
        !is_scalar($numero) ||
        !ctype_digit((string) $numero) ||
        (int) $numero <= 0
    ) {
        RespuestaJSON::error("Datos del salón inválidos", 422);
        return;
    }

    // Conectar con MySQL
    $conector = new ConectorPDO(
        $_ENV["DB_HOST"],
        $_ENV["DB_USUARIO"],
        $_ENV["DB_CLAVE"],
        $_ENV["DB_NOMBRE"]
    );

    $conexion = $conector->establecerConexion();

    if ($conexion === null) {
        RespuestaJSON::error(
            "No se pudo conectar con la base de datos",
            500
        );
        return;
    }

    // Utilizar el modelo original
    $altaDatosSalones = new AltaDatosSalones($conexion);

    $resultado = $altaDatosSalones->registrarSalon(
        $tipo,
        (int) $numero
    );

    $conector->desconectar();

    if (!$resultado) {
        RespuestaJSON::error(
            "No se pudo registrar el salón. Verificá que no exista.",
            409
        );
        return;
    }

    RespuestaJSON::exito([
        "mensaje" => "Salón registrado correctamente"
    ], 201);
}

}
