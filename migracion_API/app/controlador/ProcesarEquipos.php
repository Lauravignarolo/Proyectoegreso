
<?php

require_once RUTA_MODELO . "/ConectarPDO.php";
require_once RUTA_MODELO . "/AccesoDatosEquipos.php";
require_once RUTA_VISTA . "/RespuestaJSON.php";

class ProcesarEquipos
{
    public function gestionarEquipos(string $metodo): void
    {
        if (empty($_SESSION["cedula"])) {
            RespuestaJSON::error("Sesión no iniciada", 401);
            return;
        }

        if ($metodo === "GET") {
            $this->listar();
            return;
        }

        if (empty($_SESSION["tecnico"])) {
            RespuestaJSON::error("Acceso denegado", 403);
            return;
        }

        if (!in_array($metodo, ["POST", "DELETE"], true)) {
            RespuestaJSON::error("Método no permitido", 405);
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

        $numeroEquipo = filter_var(
            $datos["numero_de_equipo"] ?? null,
            FILTER_VALIDATE_INT,
            ["options" => ["min_range" => 1]]
        );

        if ($numeroEquipo === false || $numeroEquipo === null) {
            RespuestaJSON::error("Número de PC inválido", 422);
            return;
        }

        if ($metodo === "DELETE") {
            $this->eliminar((int) $numeroEquipo);
            return;
        }

        $tipoSalon = trim((string) ($datos["tipo_de_salon"] ?? ""));

        $numeroSalon = filter_var(
            $datos["numero_de_salon"] ?? null,
            FILTER_VALIDATE_INT,
            ["options" => ["min_range" => 1]]
        );

        if (
            $tipoSalon === "" ||
            $numeroSalon === false ||
            $numeroSalon === null
        ) {
            RespuestaJSON::error("Salón inválido", 422);
            return;
        }

        $this->agregar(
            (int) $numeroEquipo,
            $tipoSalon,
            (int) $numeroSalon
        );
    }

    private function conectar(): PDO
    {
        $conector = new ConectorPDO(
            $_ENV["DB_HOST"],
            $_ENV["DB_USUARIO"],
            $_ENV["DB_CLAVE"],
            $_ENV["DB_NOMBRE"]
        );

        return $conector->establecerConexion();
    }

    private function listar(): void
    {
        try {
            $modelo = new AccesoDatosEquipos($this->conectar());

            RespuestaJSON::exito($modelo->listarEquipos());

        } catch (Throwable $error) {
            error_log("Listar equipos: " . $error->getMessage());
            RespuestaJSON::error("No se pudieron cargar los equipos", 500);
        }
    }

    private function agregar(
        int $numeroEquipo,
        string $tipoSalon,
        int $numeroSalon
    ): void {
        try {
            $modelo = new AccesoDatosEquipos($this->conectar());

            $modelo->agregarEquipo(
                $numeroEquipo,
                $tipoSalon,
                $numeroSalon
            );

            RespuestaJSON::exito([
                "mensaje" => "PC registrada correctamente"
            ], 201);

        } catch (PDOException $error) {
            error_log("Agregar equipo: " . $error->getMessage());

            if ($error->getCode() === "23000") {
                RespuestaJSON::error(
                    "La PC ya existe o el salón seleccionado no es válido",
                    409
                );
                return;
            }

            RespuestaJSON::error("No se pudo registrar la PC", 500);

        } catch (Throwable $error) {
            error_log("Agregar equipo: " . $error->getMessage());
            RespuestaJSON::error("No se pudo registrar la PC", 500);
        }
    }

    private function eliminar(int $numeroEquipo): void
    {
        try {
            $modelo = new AccesoDatosEquipos($this->conectar());

            if (!$modelo->eliminarEquipo($numeroEquipo)) {
                RespuestaJSON::error("La PC no existe", 404);
                return;
            }

            RespuestaJSON::exito([
                "mensaje" => "PC eliminada correctamente"
            ]);

        } catch (PDOException $error) {
            error_log("Eliminar equipo: " . $error->getMessage());

            if ($error->getCode() === "23000") {
                RespuestaJSON::error(
                    "No se puede eliminar la PC porque tiene registros asociados",
                    409
                );
                return;
            }

            RespuestaJSON::error("No se pudo eliminar la PC", 500);

        } catch (Throwable $error) {
            error_log("Eliminar equipo: " . $error->getMessage());
            RespuestaJSON::error("No se pudo eliminar la PC", 500);
        }
    }
}
