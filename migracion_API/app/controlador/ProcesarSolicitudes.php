<?php

require_once RUTA_NUCLEO . "/Conexion.php";
require_once RUTA_NUCLEO . "/Sesion.php";
require_once RUTA_NUCLEO . "/Token.php";
require_once RUTA_NUCLEO . "/Validacion.php";
require_once RUTA_DAO . "/SolicitudDAO.php";

/**
 * Controlador del recurso "solicitudes" (pedidos de servicio).
 *
 * Controla GET, POST y PATCH sobre /index.php?ruta=solicitudes
 * El docente pide el servicio, el técnico lo cumple y dirección o
 * administración pueden consultarlo.
 */
class ProcesarSolicitudes
{
    public function gestionar(string $metodo): void
    {
        Sesion::exigirSesion();

        match ($metodo) {
            "GET" => $this->listar(),
            "POST" => $this->registrar(),
            "PATCH" => $this->cumplir(),
            default => RespuestaJSON::metodoNoPermitido("GET, POST, PATCH")
        };
    }

    /**
     * GET /solicitudes -> lista las solicitudes pendientes.
     */
    private function listar(): void
    {
        Sesion::exigirRol("tecnico", "direccion", "administrador");

        $dao = new SolicitudDAO(Conexion::conectar());

        RespuestaJSON::exito($dao->listarPendientes());
    }

    /**
     * POST /solicitudes -> registra una solicitud. Solo el docente.
     */
    private function registrar(): void
    {
        Sesion::exigirRol("docente");
        Token::verificar();

        $datos = $this->cuerpo();

        $fechaSolicitada = Validacion::fecha(
            $datos["fechaSolicitada"] ?? $datos["fecha_solicitada"] ?? null
        );
        $descripcion = Validacion::texto(
            $datos["descripcion"] ?? null,
            255
        );

        if ($fechaSolicitada === null || $descripcion === null) {
            RespuestaJSON::error("Fecha o descripción inválida", 422);
        }

        $dao = new SolicitudDAO(Conexion::conectar());

        $resultado = $dao->registrar(
            $fechaSolicitada,
            $descripcion,
            (string) Sesion::cedula()
        );

        if (!$resultado) {
            RespuestaJSON::error("No se pudo registrar la solicitud", 409);
        }

        RespuestaJSON::exito([
            "mensaje" => "Solicitud registrada correctamente"
        ], 201);
    }

    /**
     * PATCH /solicitudes -> marca una solicitud como cumplida. Solo el técnico.
     */
    private function cumplir(): void
    {
        Sesion::exigirRol("tecnico");
        Token::verificar();

        $datos = $this->cuerpo();

        $id = Validacion::entero($datos["id_solicitud"] ?? null, 1);

        if ($id === null) {
            RespuestaJSON::error("ID de solicitud inválido", 422);
        }

        $dao = new SolicitudDAO(Conexion::conectar());

        $resultado = $dao->cumplir($id, (string) Sesion::cedula());

        if (!$resultado) {
            RespuestaJSON::error(
                "La solicitud no existe o ya fue cumplida",
                409
            );
        }

        RespuestaJSON::exito([
            "mensaje" => "Solicitud cumplida correctamente"
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
