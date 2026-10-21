
<?php

require_once RUTA_MODELO . "/ConectarPDO.php";
require_once RUTA_MODELO . "/AltaDatosRegistroDiario.php";

class ProcesarRegistroDiario
{
    public function registrar(string $metodo): void
    {
        if ($metodo !== "POST") {
            header("Allow: POST");
            RespuestaJSON::error("Método no permitido", 405);
            return;
        }

        if (empty($_SESSION["cedula"])) {
            RespuestaJSON::error("Sesión no iniciada", 401);
            return;
        }

        if (empty($_SESSION["docente"])) {
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

        $datos = json_decode(
            file_get_contents("php://input"),
            true
        );

        if (!is_array($datos)) {
            RespuestaJSON::error("JSON inválido", 400);
            return;
        }

        $campos = [
            "hora_de_entrada",
            "hora_de_salida",
            "tipo_de_salon",
            "numero_de_salon",
            "asignatura",
            "grupo",
            "turno"
        ];

        foreach ($campos as $campo) {
            if (
                !isset($datos[$campo]) ||
                !is_scalar($datos[$campo]) ||
                trim((string) $datos[$campo]) === ""
            ) {
                RespuestaJSON::error(
                    "Falta o es inválido: " . $campo,
                    422
                );
                return;
            }
        }

        $entrada = (string) $datos["hora_de_entrada"];
        $salida = (string) $datos["hora_de_salida"];

        $patronHora = '/^(?:[01]\d|2[0-3]):[0-5]\d$/';

        if (
            !preg_match($patronHora, $entrada) ||
            !preg_match($patronHora, $salida) ||
            $salida <= $entrada
        ) {
            RespuestaJSON::error("Horario inválido", 422);
            return;
        }

        $tipo = (string) $datos["tipo_de_salon"];
        $turno = (string) $datos["turno"];

        if (
            !in_array($tipo, ["Taller", "Laboratorio"], true) ||
            !in_array($turno, ["M", "V", "N"], true)
        ) {
            RespuestaJSON::error(
                "Tipo de salón o turno inválido",
                422
            );
            return;
        }

        $numero = filter_var(
            $datos["numero_de_salon"],
            FILTER_VALIDATE_INT,
            ["options" => ["min_range" => 1]]
        );

        if ($numero === false) {
            RespuestaJSON::error(
                "Número de salón inválido",
                422
            );
            return;
        }

        $asignatura = trim((string) $datos["asignatura"]);
        $grupo = trim((string) $datos["grupo"]);

        if (
            mb_strlen($asignatura) > 100 ||
            mb_strlen($grupo) > 50
        ) {
            RespuestaJSON::error(
                "Asignatura o grupo demasiado largo",
                422
            );
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

            $modelo = new AltaDatosRegistroDiario($conexion);

            $guardado = $modelo->registrar(
                (string) $_SESSION["cedula"],
                $entrada,
                $salida,
                $tipo,
                (int) $numero,
                $asignatura,
                $grupo,
                $turno
            );

            $conector->desconectar();

            if (!$guardado) {
                RespuestaJSON::error(
                    "No se pudo guardar el registro diario",
                    409
                );
                return;
            }

            RespuestaJSON::exito([
                "mensaje" => "Registro diario guardado"
            ], 201);

        } catch (Throwable $error) {
            error_log($error->getMessage());

            RespuestaJSON::error(
                "Error interno al registrar el diario",
                500
            );
        }
    }
}
