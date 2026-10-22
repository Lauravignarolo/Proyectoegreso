
<?php

require_once RUTA_MODELO . "/AccesoDatosInventario.php";
require_once RUTA_MODELO . "/ConectarPDO.php";
require_once RUTA_VISTA . "/RespuestaJSON.php";

class ProcesarInventario
{
    public function gestionarInventario(string $metodo): void
{
    if (empty($_SESSION["cedula"])) {
        RespuestaJSON::error("Sesión no iniciada", 401);
        return;
    }

    // Dirección y técnico pueden consultar el inventario.
    if ($metodo === "GET") {
        $this->listarInventario();
        return;
    }

    // Solamente el técnico puede agregar, editar o eliminar.
    if (empty($_SESSION["tecnico"])) {
        RespuestaJSON::error(
            "Solo el técnico puede modificar el inventario",
            403
        );
        return;
    }

        if (!in_array($metodo, ["POST", "PUT", "DELETE"], true)) {
            header("Allow: GET, POST, PUT, DELETE");
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

        $datos = json_decode(
            file_get_contents("php://input"),
            true
        );

        if (!is_array($datos)) {
            RespuestaJSON::error("JSON inválido", 400);
            return;
        }

        if ($metodo === "DELETE") {
            $this->eliminarInventario($datos);
            return;
        }

        $componente = $datos["componente"] ?? null;
        $marca = $datos["marca"] ?? null;
        $cantidadOriginal = $datos["cantidad"] ?? null;

        if (
            !is_string($componente) ||
            !is_string($marca) ||
            !is_scalar($cantidadOriginal)
        ) {
            RespuestaJSON::error("Datos inválidos", 422);
            return;
        }

        $componente = trim($componente);
        $marca = trim($marca);

        $cantidad = filter_var(
            $cantidadOriginal,
            FILTER_VALIDATE_INT,
            ["options" => ["min_range" => 0]]
        );

        if (
            $componente === "" ||
            $marca === "" ||
            mb_strlen($componente) > 100 ||
            mb_strlen($marca) > 100 ||
            $cantidad === false
        ) {
            RespuestaJSON::error(
                "Componente, marca o cantidad inválidos",
                422
            );
            return;
        }

        if ($metodo === "POST") {
            $this->agregarInventario(
                $componente,
                $marca,
                (int) $cantidad
            );
            return;
        }

        $id = $this->validarId($datos["id_inventario"] ?? null);

        if ($id === null) {
            RespuestaJSON::error(
                "ID de inventario inválido",
                422
            );
            return;
        }

        $this->editarInventario(
            $id,
            $componente,
            $marca,
            (int) $cantidad
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

    private function validarId($valor): ?int
    {
        if (!is_int($valor) && !is_string($valor)) {
            return null;
        }

        $id = filter_var(
            $valor,
            FILTER_VALIDATE_INT,
            ["options" => ["min_range" => 1]]
        );

        return $id === false ? null : (int) $id;
    }

    private function listarInventario(): void
    {
        try {
            $modelo = new AccesoDatosInventario($this->conectar());

            RespuestaJSON::exito(
                $modelo->listarInventario()
            );

        } catch (Throwable $error) {
    error_log("ERROR INVENTARIO GET: " . $error->getMessage());

    RespuestaJSON::error(
        "Error al listar inventario: " . $error->getMessage(),
        500
    );
        }
    }

    private function agregarInventario(
        string $componente,
        string $marca,
        int $cantidad
    ): void {
        try {
            $modelo = new AccesoDatosInventario($this->conectar());

            $resultado = $modelo->agregarInventario(
                $componente,
                $marca,
                $cantidad
            );

            if (!$resultado) {
                RespuestaJSON::error(
                    "No se pudo agregar el componente",
                    500
                );
                return;
            }

            RespuestaJSON::exito([
                "mensaje" => "Componente agregado correctamente"
            ], 201);

        } catch (Throwable $error) {
    error_log("ERROR INVENTARIO: " . $error->getMessage());

    RespuestaJSON::error(
        "Error al agregar el componente: " . $error->getMessage(),
        500
    );
}
    }

    private function editarInventario(
        int $id,
        string $componente,
        string $marca,
        int $cantidad
    ): void {
        try {
            $modelo = new AccesoDatosInventario($this->conectar());

            $resultado = $modelo->editarInventario(
                $id,
                $componente,
                $marca,
                $cantidad
            );

            if (!$resultado) {
                RespuestaJSON::error(
                    "El componente no existe",
                    404
                );
                return;
            }

            RespuestaJSON::exito([
                "mensaje" => "Componente actualizado correctamente"
            ]);

        } catch (Throwable $error) {
            error_log($error->getMessage());

            RespuestaJSON::error(
                "Error al editar el componente",
                500
            );
        }
    }

    private function eliminarInventario(array $datos): void
    {
        $id = $this->validarId(
            $datos["id_inventario"] ?? null
        );

        if ($id === null) {
            RespuestaJSON::error(
                "ID de inventario inválido",
                422
            );
            return;
        }

        try {
            $modelo = new AccesoDatosInventario($this->conectar());

            $resultado = $modelo->eliminarInventario($id);

            if (!$resultado) {
                RespuestaJSON::error(
                    "El componente no existe",
                    404
                );
                return;
            }

            RespuestaJSON::exito([
                "mensaje" => "Componente eliminado correctamente"
            ]);

        } catch (Throwable $error) {
            error_log($error->getMessage());

            RespuestaJSON::error(
                "Error al eliminar el componente",
                500
            );
        }
    }
}
