
<?php

require_once RUTA_MODELO . "/ConectarPDO.php";
require_once RUTA_MODELO . "/AccesoDatosSolicitudes.php";

class ProcesarVerSolicitudes
{
    public function listarSolicitudes(string $metodo): void
    {
        if ($metodo !== "GET") {
            header("Allow: GET");
            RespuestaJson::error("Método no permitido", 405);
        }

        if (empty($_SESSION["cedula"])) {
            RespuestaJson::error("Sesión no iniciada", 401);
        }

        if (
            empty($_SESSION["tecnico"]) &&
            empty($_SESSION["direccion"]) &&
            empty($_SESSION["administrador"])
        ) {
            RespuestaJson::error("No tenés permiso para ver solicitudes", 403);
        }

        try {
            $conector = new ConectorPDO(
                $_ENV["DB_HOST"],
                $_ENV["DB_USUARIO"],
                $_ENV["DB_CLAVE"],
                $_ENV["DB_NOMBRE"],
                (int) ($_ENV["DB_PUERTO"] ?? 3306)
            );

            $conexion = $conector->establecerConexion();

            $modelo = new AccesoDatosSolicitudes($conexion);
            $solicitudes = $modelo->listarSolicitudes();

            $conector->desconectar();

            RespuestaJson::exito($solicitudes);

        } catch (Throwable $error) {
            error_log(
                "Error al consultar solicitudes: " .
                $error->getMessage()
            );

            RespuestaJson::error(
                "No se pudieron obtener las solicitudes",
                500
            );
        }
    }
    
public function cumplirSolicitud(string $metodo): void
{
    if ($metodo !== "PATCH") {
        RespuestaJSON::error("Método no permitido", 405);
        return;
    }

    if (
        empty($_SESSION["cedula"]) ||
        empty($_SESSION["tecnico"])
    ) {
        RespuestaJSON::error(
            "Solo un técnico puede cumplir solicitudes",
            403
        );
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

    $datos = json_decode(
        file_get_contents("php://input"),
        true
    );

    if (!is_array($datos)) {
        RespuestaJSON::error("JSON inválido", 400);
        return;
    }

    $id = filter_var(
        $datos["id_solicitud"] ?? null,
        FILTER_VALIDATE_INT,
        ["options" => ["min_range" => 1]]
    );

    if ($id === false || $id === null) {
        RespuestaJSON::error("ID de solicitud inválido", 422);
        return;
    }

    try {
        $conector = new ConectorPDO(
            $_ENV["DB_HOST"],
            $_ENV["DB_USUARIO"],
            $_ENV["DB_CLAVE"],
            $_ENV["DB_NOMBRE"]
        );

        $conexion = $conector->establecerConexion();

        $modelo = new AccesoDatosSolicitudes($conexion);

        $resultado = $modelo->cumplirSolicitud(
            (int) $id,
            (string) $_SESSION["cedula"]
        );

        if (!$resultado) {
            RespuestaJSON::error(
                "La solicitud no existe o ya fue cumplida",
                409
            );
            return;
        }

        RespuestaJSON::exito([
            "mensaje" => "Solicitud cumplida correctamente"
        ]);

    } catch (Throwable $error) {
    error_log(
        "Error al cumplir solicitud: " . $error->getMessage()
    );

    RespuestaJSON::error(
        "Error al cumplir solicitud: " . $error->getMessage(),
        500
    );
}

}
}
