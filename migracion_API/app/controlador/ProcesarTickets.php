<?php

require_once RUTA_NUCLEO . "/Conexion.php";
require_once RUTA_NUCLEO . "/Sesion.php";
require_once RUTA_NUCLEO . "/Token.php";
require_once RUTA_NUCLEO . "/Validacion.php";
require_once RUTA_DAO . "/TicketDAO.php";

/**
 * Controlador del recurso "tickets" (incidencias de los equipos).
 *
 * Controla GET, POST y PATCH sobre /index.php?ruta=tickets
 * El docente reporta el ticket, el técnico lo ve y lo repara.
 */
class ProcesarTickets
{
    private const TIPOS_DE_SALON = ["Taller", "Laboratorio"];
    private const TURNOS = ["M", "V", "N"];
    private const URGENCIAS = ["Nula", "Poca", "Media", "Alta"];

    public function gestionar(string $metodo): void
    {
        Sesion::exigirSesion();

        match ($metodo) {
            "GET" => $this->listar(),
            "POST" => $this->registrar(),
            "PATCH" => $this->reparar(),
            default => RespuestaJSON::metodoNoPermitido("GET, POST, PATCH")
        };
    }

    /**
     * GET /tickets -> lista los tickets pendientes. Solo el técnico.
     */
    private function listar(): void
    {
        Sesion::exigirRol("tecnico");

        $dao = new TicketDAO(Conexion::conectar());

        RespuestaJSON::exito($dao->listarPendientes());
    }

    /**
     * POST /tickets -> registra una incidencia. Solo el docente.
     */
    private function registrar(): void
    {
        Sesion::exigirRol("docente");
        Token::verificar();

        $datos = $this->cuerpo();

        $estudianteACargo = Validacion::texto(
            $datos["estudiante_a_cargo"] ?? null,
            100
        );
        $horaDeEntrada = Validacion::hora($datos["hora_de_entrada"] ?? null);
        $horaDeSalida = Validacion::hora($datos["hora_de_salida"] ?? null);
        $tipoDeSalon = Validacion::enLista(
            $datos["tipo_de_salon"] ?? null,
            self::TIPOS_DE_SALON
        );
        $numeroDeSalon = Validacion::entero(
            $datos["numero_de_salon"] ?? null,
            1
        );
        $numeroDeEquipo = Validacion::entero(
            $datos["numero_de_equipo"] ?? null,
            1
        );
        $asignatura = Validacion::texto($datos["asignatura"] ?? null, 100);
        $grupo = Validacion::texto($datos["grupo"] ?? null, 50);
        $turno = Validacion::enLista($datos["turno"] ?? null, self::TURNOS);
        $urgencia = Validacion::enLista(
            $datos["urgencia"] ?? null,
            self::URGENCIAS
        );
        $estado = Validacion::texto($datos["estado"] ?? null, 50);

        if (
            $estudianteACargo === null ||
            $horaDeEntrada === null ||
            $horaDeSalida === null ||
            $tipoDeSalon === null ||
            $numeroDeSalon === null ||
            $numeroDeEquipo === null ||
            $asignatura === null ||
            $grupo === null ||
            $turno === null ||
            $urgencia === null ||
            $estado === null
        ) {
            RespuestaJSON::error("Existen campos vacíos o inválidos", 422);
        }

        if ($horaDeSalida < $horaDeEntrada) {
            RespuestaJSON::error(
                "La hora de salida no puede ser anterior a la de entrada",
                422
            );
        }

        $dao = new TicketDAO(Conexion::conectar());

        if (!$dao->existeSalon($tipoDeSalon, $numeroDeSalon)) {
            RespuestaJSON::error("El salón seleccionado no existe", 422);
        }

        $resultado = $dao->registrar(
            (string) Sesion::cedula(),
            $estudianteACargo,
            $horaDeEntrada,
            $horaDeSalida,
            $tipoDeSalon,
            $numeroDeSalon,
            $numeroDeEquipo,
            $asignatura,
            $grupo,
            $turno,
            $urgencia,
            $estado
        );

        if (!$resultado) {
            RespuestaJSON::error("No se pudo registrar el ticket", 400);
        }

        RespuestaJSON::exito([
            "mensaje" => "Ticket registrado correctamente"
        ], 201);
    }

    /**
     * PATCH /tickets -> marca un ticket como reparado. Solo el técnico.
     */
    private function reparar(): void
    {
        Sesion::exigirRol("tecnico");
        Token::verificar();

        $datos = $this->cuerpo();

        $idTicket = Validacion::entero($datos["id_ticket"] ?? null, 1);

        if ($idTicket === null) {
            RespuestaJSON::error("ID de ticket inválido", 422);
        }

        $dao = new TicketDAO(Conexion::conectar());

        $resultado = $dao->reparar($idTicket, (string) Sesion::cedula());

        if (!$resultado) {
            RespuestaJSON::error(
                "El ticket no existe, ya fue resuelto o no pudo actualizarse",
                409
            );
        }

        RespuestaJSON::exito([
            "mensaje" => "Ticket reparado correctamente"
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
