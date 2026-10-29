<?php

require_once RUTA_NUCLEO . "/Conexion.php";
require_once RUTA_NUCLEO . "/Sesion.php";
require_once RUTA_NUCLEO . "/Token.php";
require_once RUTA_NUCLEO . "/Validacion.php";
require_once RUTA_DAO . "/SalonDAO.php";
require_once RUTA_NUCLEO . "/TokenJWT.php";

/**
 * Controlador del recurso "salones".
 *
 * Controla GET, POST y DELETE sobre /index.php?ruta=salones
 * Cualquier usuario autenticado puede consultar; solo el administrador
 * puede crear o eliminar salones.
 */
class ProcesarSalones
{
    public function gestionar(string $metodo): void
    {
        match ($metodo) {
            "GET" => $this->listar(),
            "POST" => $this->agregar(),
            "DELETE" => $this->eliminar(),
            default => RespuestaJSON::metodoNoPermitido("GET, POST, DELETE")
        };
    }

    /**
     * GET /salones -> lista todos los salones.
     */
    private function listar(): void
    {
        Sesion::exigirSesion();

        $dao = new SalonDAO(Conexion::conectar());

        RespuestaJSON::exito($dao->listar());
    }

    /**
     * POST /salones -> registra un salón.
     */
    private function agregar(): void
    {
        TokenJWT::exigirRol("administrador")

        $datos = $this->cuerpo();

        $tipo = Validacion::texto($datos["tipo_de_salon"] ?? null, 50);
        $numero = Validacion::entero($datos["numero_de_salon"] ?? null, 1);

        if ($tipo === null || $numero === null) {
            RespuestaJSON::error("Datos del salón inválidos", 422);
        }

        $dao = new SalonDAO(Conexion::conectar());

        if (!$dao->registrar($tipo, $numero)) {
            RespuestaJSON::error(
                "No se pudo registrar el salón. Verificá que no exista.",
                409
            );
        }

        RespuestaJSON::exito([
            "mensaje" => "Salón registrado correctamente"
        ], 201);
    }

    /**
     * DELETE /salones -> elimina un salón.
     */
    private function eliminar(): void
    {
        Sesion::exigirSesion();
        Sesion::exigirRol("administrador");
        Token::verificar();

        $datos = $this->cuerpo();

        $tipo = Validacion::texto($datos["tipo_de_salon"] ?? null, 50);
        $numero = Validacion::entero($datos["numero_de_salon"] ?? null, 1);

        if ($tipo === null || $numero === null) {
            RespuestaJSON::error("Datos del salón inválidos", 422);
        }

        $dao = new SalonDAO(Conexion::conectar());

        if (!$dao->eliminar($numero, $tipo)) {
            RespuestaJSON::error("No se pudo eliminar el salón", 404);
        }

        RespuestaJSON::exito([
            "mensaje" => "Salón eliminado correctamente"
        ]);
    }

    /**
     * Lee y valida el cuerpo JSON de la petición.
     */
    private function cuerpo(): array
    {
        $datos = json_decode(file_get_contents("php://input"), true);

        if (!is_array($datos)) {
            RespuestaJSON::error("JSON inválido", 400);
        }

        return $datos;
    }
}
