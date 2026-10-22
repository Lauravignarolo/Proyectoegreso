<?php

require_once RUTA_NUCLEO . "/Conexion.php";
require_once RUTA_NUCLEO . "/Sesion.php";
require_once RUTA_NUCLEO . "/Token.php";
require_once RUTA_NUCLEO . "/Validacion.php";
require_once RUTA_DAO . "/RegistroDiarioDAO.php";

/**
 * Controlador del recurso "registroDiario" (clases registradas por los
 * docentes).
 *
 * Controla GET, POST y DELETE sobre /index.php?ruta=registroDiario
 */
class ProcesarRegistroDiario
{
    private const TIPOS_DE_SALON = ["Taller", "Laboratorio"];
    private const TURNOS = ["M", "V", "N"];

    public function gestionar(string $metodo): void
    {
        Sesion::exigirSesion();

        match ($metodo) {
            "GET" => $this->listar(),
            "POST" => $this->registrar(),
            "DELETE" => $this->eliminar(),
            default => RespuestaJSON::metodoNoPermitido("GET, POST, DELETE")
        };
    }

    /**
     * GET /registroDiario -> lista los registros.
     * El docente ve los suyos; dirección y administración ven todos.
     */
    private function listar(): void
    {
        Sesion::exigirRol("docente", "direccion", "administrador");

        $dao = new RegistroDiarioDAO(Conexion::conectar());

        if (Sesion::rol() === "docente") {
            RespuestaJSON::exito(
                $dao->listarPorDocente((string) Sesion::cedula())
            );
        }

        RespuestaJSON::exito($dao->listar());
    }

    /**
     * POST /registroDiario -> registra una clase. Solo el docente.
     */
    private function registrar(): void
    {
        Sesion::exigirRol("docente");
        Token::verificar();

        $datos = $this->cuerpo();

        $entrada = Validacion::hora($datos["hora_de_entrada"] ?? null);
        $salida = Validacion::hora($datos["hora_de_salida"] ?? null);
        $tipo = Validacion::enLista(
            $datos["tipo_de_salon"] ?? null,
            self::TIPOS_DE_SALON
        );
        $numero = Validacion::entero($datos["numero_de_salon"] ?? null, 1);
        $asignatura = Validacion::texto($datos["asignatura"] ?? null, 100);
        $grupo = Validacion::texto($datos["grupo"] ?? null, 50);
        $turno = Validacion::enLista($datos["turno"] ?? null, self::TURNOS);

        if (
            $entrada === null ||
            $salida === null ||
            $tipo === null ||
            $numero === null ||
            $asignatura === null ||
            $grupo === null ||
            $turno === null
        ) {
            RespuestaJSON::error("Faltan campos obligatorios o son inválidos", 422);
        }

        if ($salida <= $entrada) {
            RespuestaJSON::error("Horario inválido", 422);
        }

        $dao = new RegistroDiarioDAO(Conexion::conectar());

        $resultado = $dao->registrar(
            (string) Sesion::cedula(),
            $entrada,
            $salida,
            $tipo,
            $numero,
            $asignatura,
            $grupo,
            $turno
        );

        if (!$resultado) {
            RespuestaJSON::error("No se pudo guardar el registro diario", 409);
        }

        RespuestaJSON::exito([
            "mensaje" => "Registro diario guardado"
        ], 201);
    }

    /**
     * DELETE /registroDiario -> elimina un registro.
     * El docente solo puede borrar los propios.
     */
    private function eliminar(): void
    {
        Sesion::exigirRol("docente", "administrador");
        Token::verificar();

        $datos = $this->cuerpo();

        $id = Validacion::entero($datos["id"] ?? null, 1);

        if ($id === null) {
            RespuestaJSON::error("ID de registro inválido", 422);
        }

        $dao = new RegistroDiarioDAO(Conexion::conectar());

        $registro = $dao->buscarPorId($id);

        if ($registro === null) {
            RespuestaJSON::error("El registro no existe", 404);
        }

        if (
            Sesion::rol() === "docente" &&
            $registro["documento_identidad"] !== Sesion::cedula()
        ) {
            RespuestaJSON::error(
                "Solo podés eliminar tus propios registros",
                403
            );
        }

        if (!$dao->eliminar($id)) {
            RespuestaJSON::error("No se pudo eliminar el registro", 409);
        }

        RespuestaJSON::exito([
            "mensaje" => "Registro diario eliminado"
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
