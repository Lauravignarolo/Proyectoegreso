
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
}
