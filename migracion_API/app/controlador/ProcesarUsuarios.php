<?php

require_once RUTA_NUCLEO . "/Conexion.php";
require_once RUTA_NUCLEO . "/Sesion.php";
require_once RUTA_NUCLEO . "/Token.php";
require_once RUTA_NUCLEO . "/Validacion.php";
require_once RUTA_DAO . "/UsuarioDAO.php";

/**
 * Controlador del recurso "usuarios".
 *
 * Controla GET, POST, PUT, PATCH y DELETE sobre /index.php?ruta=usuarios
 * Solo el administrador puede operar sobre este recurso.
 */
class ProcesarUsuarios
{
    /**
     * Roles que se pueden asignar a un usuario.
     * Coinciden con las tablas de roles del DDL.
     */
    private const ROLES = ["administrador", "docente", "tecnico", "direccion"];

    public function gestionar(string $metodo): void
    {
        Sesion::exigirRol("administrador");

        match ($metodo) {
            "GET" => $this->listar(),
            "POST" => $this->alta(),
            "PUT" => $this->modificar(),
            "PATCH" => $this->cambiarEstado(),
            "DELETE" => $this->baja(),
            default => RespuestaJSON::metodoNoPermitido("GET, POST, PUT, PATCH, DELETE")
        };
    }

    /**
     * GET /usuarios            -> lista todos los usuarios
     * GET /usuarios?cedula=... -> lista un usuario
     */
    private function listar(): void
    {
        $dao = new UsuarioDAO(Conexion::conectar());

        if (isset($_GET["cedula"])) {
            $cedula = Validacion::cedula($_GET["cedula"]);

            if ($cedula === null) {
                RespuestaJSON::error("Cédula incorrecta", 422);
            }

            $usuario = $dao->listarPorCedula($cedula);

            if ($usuario === null) {
                RespuestaJSON::error("El usuario no existe", 404);
            }

            RespuestaJSON::exito($usuario);
        }

        RespuestaJSON::exito($dao->listar());
    }

    /**
     * POST /usuarios -> da de alta un usuario con su rol.
     */
    private function alta(): void
    {
        Token::verificar();

        $datos = $this->cuerpo();

        $cedula = Validacion::cedula($datos["cedula"] ?? null);
        $nombre = Validacion::texto($datos["nombre"] ?? null, 50);
        $apellido = Validacion::texto($datos["apellido"] ?? null, 50);
        $rol = Validacion::enLista($datos["rol"] ?? null, self::ROLES);

        $clave = $datos["clave"] ?? null;
        $confirmarClave = $datos["confirmarClave"] ?? null;

        if (
            $cedula === null ||
            $nombre === null ||
            $apellido === null ||
            $rol === null ||
            !is_string($clave) ||
            !is_string($confirmarClave)
        ) {
            RespuestaJSON::error("Existen campos vacíos o inválidos", 422);
        }

        if (strlen($clave) < 12) {
            RespuestaJSON::error(
                "La contraseña debe contener al menos 12 caracteres",
                422
            );
        }

        if ($clave !== $confirmarClave) {
            RespuestaJSON::error(
                "Las contraseñas ingresadas no coinciden",
                422
            );
        }

        $dao = new UsuarioDAO(Conexion::conectar());

        $resultado = $dao->registrar(
            $cedula,
            $nombre,
            $apellido,
            password_hash($clave, PASSWORD_DEFAULT),
            $rol
        );

        if (!$resultado) {
            RespuestaJSON::error(
                "No se pudo registrar el empleado (la cédula ya existe)",
                409
            );
        }

        RespuestaJSON::exito([
            "mensaje" => "Empleado ingresado exitosamente"
        ], 201);
    }

    /**
     * PUT /usuarios -> modifica los datos de un usuario.
     */
    private function modificar(): void
    {
        Token::verificar();

        $datos = $this->cuerpo();

        $cedula = Validacion::cedula($datos["cedula"] ?? null);
        $nombre = Validacion::texto($datos["nombre"] ?? null, 50);
        $apellido = Validacion::texto($datos["apellido"] ?? null, 50);
        $rol = Validacion::enLista($datos["rol"] ?? null, self::ROLES);

        $clave = $datos["clave"] ?? null;

        if (
            $cedula === null ||
            $nombre === null ||
            $apellido === null ||
            $rol === null ||
            !is_string($clave) ||
            $clave === ""
        ) {
            RespuestaJSON::error("Existen campos vacíos o inválidos", 422);
        }

        if (strlen($clave) < 12) {
            RespuestaJSON::error(
                "La contraseña debe contener al menos 12 caracteres",
                422
            );
        }

        $dao = new UsuarioDAO(Conexion::conectar());

        $resultado = $dao->actualizar(
            $cedula,
            $nombre,
            $apellido,
            password_hash($clave, PASSWORD_DEFAULT),
            $rol
        );

        if (!$resultado) {
            RespuestaJSON::error("El usuario no existe", 404);
        }

        RespuestaJSON::exito([
            "mensaje" => "Empleado modificado exitosamente"
        ]);
    }

    /**
     * PATCH /usuarios -> activa o desactiva una cuenta.
     *
     * No se elimina el registro: se cambia la columna `estado`, para no
     * romper las claves foráneas de tickets, solicitudes, etc.
     */
    private function cambiarEstado(): void
    {
        Token::verificar();

        $datos = $this->cuerpo();

        $cedula = Validacion::cedula($datos["cedula"] ?? null);

        if ($cedula === null || !is_bool($datos["estado"] ?? null)) {
            RespuestaJSON::error("Cédula o estado inválido", 422);
        }

        $estado = $datos["estado"];

        // El administrador no puede desactivarse a sí mismo.
        if ($cedula === Sesion::cedula() && !$estado) {
            RespuestaJSON::error(
                "No podés desactivar tu propio usuario",
                403
            );
        }

        $dao = new UsuarioDAO(Conexion::conectar());

        if (!$dao->cambiarEstado($cedula, $estado)) {
            RespuestaJSON::error("El usuario no existe", 404);
        }

        RespuestaJSON::exito([
            "mensaje" => $estado
                ? "Usuario activado correctamente"
                : "Usuario desactivado correctamente",
            "cedula" => $cedula,
            "estado" => $estado
        ]);
    }

    /**
     * DELETE /usuarios -> elimina un usuario.
     */
    private function baja(): void
    {
        Token::verificar();

        $datos = $this->cuerpo();

        $cedula = Validacion::cedula($datos["cedula"] ?? null);

        if ($cedula === null) {
            RespuestaJSON::error("Falta la cédula del empleado", 422);
        }

        // El administrador no puede eliminarse a sí mismo.
        if ($cedula === Sesion::cedula()) {
            RespuestaJSON::error(
                "No podés eliminar tu propio usuario",
                403
            );
        }

        $dao = new UsuarioDAO(Conexion::conectar());

        $resultado = $dao->eliminar($cedula);

        if (!$resultado) {
            RespuestaJSON::error(
                "No se pudo eliminar el usuario. Puede tener registros relacionados.",
                409
            );
        }

        RespuestaJSON::exito([
            "mensaje" => "Empleado eliminado exitosamente"
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
