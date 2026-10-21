
<?php

require_once RUTA_MODELO . "/ConectarPDO.php";
require_once RUTA_MODELO . "/AltaDatosSolicitud.php";

class ProcesarSolicitud
{
    public function registrarSolicitud(string $metodo): void
    {
        if ($metodo !== "POST") {
            header("Allow: POST");
            RespuestaJson::error("Método no permitido", 405);
        }

        if (
            empty($_SESSION["cedula"]) ||
            empty($_SESSION["docente"])
        ) {
            RespuestaJson::error("Acceso denegado", 403);
        }

        $token = $_SERVER["HTTP_X_CSRF_TOKEN"] ?? "";

        if (
            !isset($_SESSION["csrfToken"]) ||
            !hash_equals($_SESSION["csrfToken"], $token)
        ) {
            RespuestaJson::error("Token CSRF inválido", 403);
        }

        $datos = json_decode(
            file_get_contents("php://input"),
            true
        );

        if (!is_array($datos)) {
            RespuestaJson::error("JSON inválido", 400);
        }

        $fechaSolicitada = $datos["fechaSolicitada"] ?? "";
        $descripcion = $datos["descripcion"] ?? "";

        if (
            !is_string($fechaSolicitada) ||
            !is_string($descripcion)
        ) {
            RespuestaJson::error("Datos inválidos", 422);
        }

        $descripcion = trim($descripcion);

        $fecha = DateTimeImmutable::createFromFormat(
            "!Y-m-d",
            $fechaSolicitada
        );

        if (
            !$fecha ||
            $fecha->format("Y-m-d") !== $fechaSolicitada ||
            $descripcion === "" ||
            mb_strlen($descripcion) > 255
        ) {
            RespuestaJson::error(
                "Fecha o descripción inválida",
                422
            );
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

            $modelo = new AltaDatosSolicitud($conexion);

            $resultado = $modelo->registrarSolicitud(
                $fechaSolicitada,
                $descripcion,
                (string) $_SESSION["cedula"]
            );

            $conector->desconectar();

            if (!$resultado) {
                RespuestaJson::error(
                    "No se pudo registrar la solicitud",
                    500
                );
            }

            RespuestaJson::exito([
                "mensaje" => "Solicitud registrada correctamente"
            ], 201);

        } catch (Throwable $error) {
            error_log($error->getMessage());

            RespuestaJson::error(
                "Error interno al registrar la solicitud",
                500
            );
        }
    }
}
