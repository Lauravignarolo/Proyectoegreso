<?php

require_once RUTA_MODELO . "/ConectarPDO.php";
require_once RUTA_MODELO . "/AccesoDatosUsuario.php";
require_once RUTA_VISTA . "/RespuestaJSON.php";

class ProcesarUsuarios
{
    public function gestionarUsuarios(string $metodo): void
    {
        if ($metodo !== "GET" && $metodo !== "PATCH") {
            header("Allow: GET, PATCH");
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

        $modelo = new AccesoDatosUsuario($conexion);

        if ($metodo === "GET") {
            $usuarios = $modelo->listarUsuarios();

            $conectorPDO->desconectar();

            RespuestaJSON::exito($usuarios);
            return;
        }

        // Verificar el token CSRF.
        $token = $_SERVER["HTTP_X_CSRF_TOKEN"] ?? "";

        if (
            empty($_SESSION["csrfToken"]) ||
            !is_string($token) ||
            !hash_equals($_SESSION["csrfToken"], $token)
        ) {
            $conectorPDO->desconectar();
            RespuestaJSON::error("Token CSRF inválido", 403);
            return;
        }

        // Leer los datos enviados en formato JSON.
        $datos = json_decode(file_get_contents("php://input"), true);

        if (!is_array($datos)) {
            $conectorPDO->desconectar();
            RespuestaJSON::error("JSON inválido", 400);
            return;
        }

        $cedula = trim((string) ($datos["cedula"] ?? ""));

        if (
            !preg_match("/^[1-9][0-9]{7}$/", $cedula) ||
            !array_key_exists("estado", $datos) ||
            !is_bool($datos["estado"])
        ) {
            $conectorPDO->desconectar();
            RespuestaJSON::error(
                "Cédula o estado inválido",
                422
            );
            return;
        }

        $estado = $datos["estado"];

        // No permitir que el administrador desactive su propia cuenta.
        if (
            $cedula === (string) $_SESSION["cedula"] &&
            !$estado
        ) {
            $conectorPDO->desconectar();
            RespuestaJSON::error(
                "No podés desactivar tu propio usuario",
                403
            );
            return;
        }

        $resultado = $modelo->cambiarEstado($cedula, $estado);

        $conectorPDO->desconectar();

        if (!$resultado) {
            RespuestaJSON::error(
                "El usuario no existe",
                404
            );
            return;
        }

        RespuestaJSON::exito([
            "mensaje" => $estado
                ? "Usuario activado correctamente"
                : "Usuario desactivado correctamente",
            "cedula" => $cedula,
            "estado" => $estado
        ]);
    }
}