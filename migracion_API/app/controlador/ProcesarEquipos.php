<?php

require_once RUTA_NUCLEO . "/Conexion.php";
require_once RUTA_NUCLEO . "/Sesion.php";
require_once RUTA_NUCLEO . "/Token.php";
require_once RUTA_NUCLEO . "/Validacion.php";
require_once RUTA_DAO . "/EquipoDAO.php";

/**
 * Controlador del recurso "equipos" (las computadoras de los salones).
 *
 * Controla GET, POST y DELETE sobre /index.php?ruta=equipos
 * Cualquier usuario autenticado puede consultar; solo el técnico puede
 * registrar o eliminar equipos.
 */
class ProcesarEquipos
{
    public function gestionar(string $metodo): void
    {
        Sesion::exigirSesion();

        match ($metodo) {
            "GET" => $this->listar(),
            "POST" => $this->agregar(),
            "DELETE" => $this->eliminar(),
            default => RespuestaJSON::metodoNoPermitido("GET, POST, DELETE")
        };
    }

    /**
     * GET /equipos -> lista todos los equipos.
     */
    private function listar(): void
    {
        $dao = new EquipoDAO(Conexion::conectar());

        RespuestaJSON::exito($dao->listar());
    }

    /**
     * POST /equipos -> registra un equipo en un salón.
     */
    private function agregar(): void
    {
        Sesion::exigirRol("tecnico");
        Token::verificar();

        $datos = $this->cuerpo();

        $numeroEquipo = Validacion::entero(
            $datos["numero_de_equipo"] ?? null,
            1
        );
        $tipoSalon = Validacion::texto(
            $datos["tipo_de_salon"] ?? null,
            50
        );
        $numeroSalon = Validacion::entero(
            $datos["numero_de_salon"] ?? null,
            1
        );

        if ($numeroEquipo === null || $tipoSalon === null || $numeroSalon === null) {
            RespuestaJSON::error("Datos del equipo inválidos", 422);
        }

        $dao = new EquipoDAO(Conexion::conectar());

        if (!$dao->registrar($numeroEquipo, $tipoSalon, $numeroSalon)) {
            RespuestaJSON::error(
                "La PC ya existe o el salón seleccionado no es válido",
                409
            );
        }

        RespuestaJSON::exito([
            "mensaje" => "PC registrada correctamente"
        ], 201);
    }

    /**
     * DELETE /equipos -> elimina un equipo.
     */
    private function eliminar(): void
    {
        Sesion::exigirRol("tecnico");
        Token::verificar();

        $datos = $this->cuerpo();

        $numeroEquipo = Validacion::entero(
            $datos["numero_de_equipo"] ?? null,
            1
        );

        if ($numeroEquipo === null) {
            RespuestaJSON::error("Número de PC inválido", 422);
        }

        $dao = new EquipoDAO(Conexion::conectar());

        if (!$dao->eliminar($numeroEquipo)) {
            RespuestaJSON::error("La PC no existe", 404);
        }

        RespuestaJSON::exito([
            "mensaje" => "PC eliminada correctamente"
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
