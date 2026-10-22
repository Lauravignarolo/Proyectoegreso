<?php

require_once RUTA_NUCLEO . "/Conexion.php";
require_once RUTA_NUCLEO . "/Sesion.php";
require_once RUTA_NUCLEO . "/Token.php";
require_once RUTA_NUCLEO . "/Validacion.php";
require_once RUTA_DAO . "/InventarioDAO.php";

/**
 * Controlador del recurso "inventario".
 *
 * Controla GET, POST, PUT y DELETE sobre /index.php?ruta=inventario
 * Cualquier usuario autenticado puede consultar; solo el técnico puede
 * modificar el inventario.
 */
class ProcesarInventario
{
    public function gestionar(string $metodo): void
    {
        Sesion::exigirSesion();

        match ($metodo) {
            "GET" => $this->listar(),
            "POST" => $this->agregar(),
            "PUT" => $this->editar(),
            "DELETE" => $this->eliminar(),
            default => RespuestaJSON::metodoNoPermitido("GET, POST, PUT, DELETE")
        };
    }

    /**
     * GET /inventario -> lista el inventario.
     */
    private function listar(): void
    {
        $dao = new InventarioDAO(Conexion::conectar());

        RespuestaJSON::exito($dao->listar());
    }

    /**
     * POST /inventario -> agrega un componente.
     */
    private function agregar(): void
    {
        Sesion::exigirRol("tecnico");
        Token::verificar();

        $datos = $this->cuerpo();

        $componente = Validacion::texto($datos["componente"] ?? null, 100);
        $marca = Validacion::texto($datos["marca"] ?? null, 100);
        $cantidad = Validacion::entero($datos["cantidad"] ?? null, 0);

        if ($componente === null || $marca === null || $cantidad === null) {
            RespuestaJSON::error(
                "Componente, marca o cantidad inválidos",
                422
            );
        }

        $dao = new InventarioDAO(Conexion::conectar());

        if (!$dao->registrar($componente, $marca, $cantidad)) {
            RespuestaJSON::error("No se pudo agregar el componente", 409);
        }

        RespuestaJSON::exito([
            "mensaje" => "Componente agregado correctamente"
        ], 201);
    }

    /**
     * PUT /inventario -> actualiza un componente.
     */
    private function editar(): void
    {
        Sesion::exigirRol("tecnico");
        Token::verificar();

        $datos = $this->cuerpo();

        $id = Validacion::entero($datos["id_inventario"] ?? null, 1);
        $componente = Validacion::texto($datos["componente"] ?? null, 100);
        $marca = Validacion::texto($datos["marca"] ?? null, 100);
        $cantidad = Validacion::entero($datos["cantidad"] ?? null, 0);

        if ($id === null || $componente === null || $marca === null || $cantidad === null) {
            RespuestaJSON::error(
                "ID, componente, marca o cantidad inválidos",
                422
            );
        }

        $dao = new InventarioDAO(Conexion::conectar());

        if (!$dao->editar($id, $componente, $marca, $cantidad)) {
            RespuestaJSON::error("El componente no existe", 404);
        }

        RespuestaJSON::exito([
            "mensaje" => "Componente actualizado correctamente"
        ]);
    }

    /**
     * DELETE /inventario -> elimina un componente.
     */
    private function eliminar(): void
    {
        Sesion::exigirRol("tecnico");
        Token::verificar();

        $datos = $this->cuerpo();

        $id = Validacion::entero($datos["id_inventario"] ?? null, 1);

        if ($id === null) {
            RespuestaJSON::error("ID de inventario inválido", 422);
        }

        $dao = new InventarioDAO(Conexion::conectar());

        if (!$dao->eliminar($id)) {
            RespuestaJSON::error("El componente no existe", 404);
        }

        RespuestaJSON::exito([
            "mensaje" => "Componente eliminado correctamente"
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
