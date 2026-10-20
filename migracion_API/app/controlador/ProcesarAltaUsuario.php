
<?php

require_once RUTA_MODELO . "/ConectarPDO.php";
require_once RUTA_MODELO . "/AltaDatosUsuarios.php";
require_once RUTA_VISTA . "/RespuestaJSON.php";

class ProcesarAltaUsuario
{
    public function registrarUsuario(string $metodo): void
    {
        if ($metodo !== "POST") {
            RespuestaJSON::error("Método no permitido", 405);
            return;
        }

        // Verificar sesión de administrador
        if (
            empty($_SESSION["cedula"]) ||
            empty($_SESSION["administrador"])
        ) {
            RespuestaJSON::error("Acceso denegado", 403);
            return;
        }

        // Verificar token CSRF
        $token = $_SERVER["HTTP_X_CSRF_TOKEN"] ?? "";

        if (
            empty($_SESSION["csrfToken"]) ||
            !hash_equals($_SESSION["csrfToken"], $token)
        ) {
            RespuestaJSON::error("Token CSRF inválido", 403);
            return;
        }

        // Recuperar datos enviados desde JavaScript
        $datos = json_decode(file_get_contents("php://input"), true);

        if (!is_array($datos)) {
            RespuestaJSON::error("JSON inválido", 400);
            return;
        }

        $cedula = trim((string) ($datos["cedula"] ?? ""));
        $nombre = trim((string) ($datos["nombre"] ?? ""));
        $apellido = trim((string) ($datos["apellido"] ?? ""));
        $clave = (string) ($datos["clave"] ?? "");
        $confirmarClave = (string) ($datos["confirmarClave"] ?? "");
        $rol = trim((string) ($datos["rol"] ?? ""));

        // Validar campos
        if (
            $cedula === "" ||
            $nombre === "" ||
            $apellido === "" ||
            $clave === "" ||
            $confirmarClave === "" ||
            $rol === ""
        ) {
            RespuestaJSON::error("Existen campos vacíos", 422);
            return;
        }

        if (!preg_match("/^[1-9][0-9]{7}$/", $cedula)) {
            RespuestaJSON::error("Cédula incorrecta", 422);
            return;
        }

        if (strlen($clave) < 12) {
            RespuestaJSON::error(
                "La contraseña debe contener al menos 12 caracteres",
                422
            );
            return;
        }

        if ($clave !== $confirmarClave) {
            RespuestaJSON::error(
                "Las contraseñas ingresadas no coinciden",
                422
            );
            return;
        }

        $rolesPermitidos = [
            "Administrador",
            "Docente",
            "Tecnico",
            "Direccion"
        ];

        if (!in_array($rol, $rolesPermitidos, true)) {
            RespuestaJSON::error("Rol inválido", 422);
            return;
        }

        // Hashear contraseña
        $claveHash = password_hash($clave, PASSWORD_DEFAULT);

        // Conexión con MySQL
        $conectorPDO = new ConectorPDO(
            $_ENV["DB_HOST"],
            $_ENV["DB_USUARIO"],
            $_ENV["DB_CLAVE"],
            $_ENV["DB_NOMBRE"]
        );

        $conexion = $conectorPDO->establecerConexion();

        if ($conexion === null) {
            RespuestaJSON::error(
                "No se pudo conectar con la base de datos",
                500
            );
            return;
        }

        // Utilizar tu modelo original
        $altaDatosUsuario = new AltaDatosUsuarios($conexion);

        $resultado = $altaDatosUsuario->registrarUsuario(
            $cedula,
            $nombre,
            $apellido,
            $claveHash,
            $rol
        );

        if (!$resultado) {
            RespuestaJSON::error(
                "No se pudo registrar el empleado",
                400
            );
            return;
        }

        RespuestaJSON::exito([
            "mensaje" => "Empleado ingresado exitosamente"
        ], 201);
    }
}
