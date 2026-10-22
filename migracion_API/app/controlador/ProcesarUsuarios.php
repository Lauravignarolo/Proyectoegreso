
<?php


require_once RUTA_MODELO . "/ConectarPDO.php";
require_once RUTA_MODELO . "/AccesoDatosUsuario.php";
require_once RUTA_MODELO . "/BajaDatosUsuarios.php";
require_once RUTA_VISTA . "/RespuestaJSON.php";

class ProcesarUsuarios
{
    public function gestionarUsuarios(string $metodo): void
    {
        if ($metodo !== "GET" && $metodo !== "DELETE") {
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

        if ($metodo === "GET") {
            $modelo = new AccesoDatosUsuario($conexion);
            $usuarios = $modelo->listarUsuarios();

            $conectorPDO->desconectar();
            RespuestaJSON::exito($usuarios);
            return;
        }

        // Verificar token CSRF para DELETE
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

        $cedula = trim((string) ($datos["cedula"] ?? ""));

        if ($cedula === "") {
            RespuestaJSON::error("Falta la cédula", 400);
            return;
        }

        // Evitar que el administrador elimine su cuenta
        if ($cedula === (string) $_SESSION["cedula"]) {
            RespuestaJSON::error(
                "No podés eliminar tu propio usuario",
                403
            );
            return;
        }

        $modelo = new BajaDatosUsuario($conexion);
        $resultado = $modelo->eliminarUsuario($cedula);

        $conectorPDO->desconectar();

        if (!$resultado) {
            RespuestaJSON::error(
                "No se pudo eliminar el usuario. Puede tener registros relacionados.",
                409
            );
            return;
        }

        RespuestaJSON::exito([
            "mensaje" => "Usuario eliminado correctamente"
        ]);
    }
}
