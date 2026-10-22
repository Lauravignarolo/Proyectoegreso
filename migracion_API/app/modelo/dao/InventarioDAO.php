<?php

/**
 * DAO de la entidad INVENTARIO (componentes y su cantidad).
 */
class InventarioDAO
{
    private PDO $conexion;

    public function __construct(PDO $conexion)
    {
        $this->conexion = $conexion;
    }

    /**
     * Lista el inventario completo.
     */
    public function listar(): array
    {
        $sql = "
            SELECT
                id_inventario,
                componente,
                marca,
                cantidad
            FROM INVENTARIO
            ORDER BY componente ASC, marca ASC
        ";

        $consulta = $this->conexion->prepare($sql);
        $consulta->execute();

        $inventario = $consulta->fetchAll(PDO::FETCH_ASSOC);

        $consulta = null;

        return $inventario;
    }

    /**
     * Agrega un componente al inventario.
     */
    public function registrar(
        string $componente,
        string $marca,
        int $cantidad
    ): bool {
        try {
            $sql = "
                INSERT INTO INVENTARIO (componente, marca, cantidad)
                VALUES (:componente, :marca, :cantidad)
            ";

            $consulta = $this->conexion->prepare($sql);

            return $consulta->execute([
                "componente" => $componente,
                "marca" => $marca,
                "cantidad" => $cantidad
            ]);
        } catch (PDOException $error) {
            error_log("[InventarioDAO] registrar: " . $error->getMessage());

            return false;
        }
    }

    /**
     * Actualiza un componente. Devuelve false si no existía.
     */
    public function editar(
        int $idInventario,
        string $componente,
        string $marca,
        int $cantidad
    ): bool {
        try {
            $sql = "
                UPDATE INVENTARIO
                SET componente = :componente,
                    marca = :marca,
                    cantidad = :cantidad
                WHERE id_inventario = :id
            ";

            $consulta = $this->conexion->prepare($sql);
            $consulta->execute([
                "componente" => $componente,
                "marca" => $marca,
                "cantidad" => $cantidad,
                "id" => $idInventario
            ]);

            // rowCount() da 0 cuando el valor no cambió, así que se
            // comprueba aparte que el registro siga existiendo.
            return $consulta->rowCount() > 0 || $this->existe($idInventario);
        } catch (PDOException $error) {
            error_log("[InventarioDAO] editar: " . $error->getMessage());

            return false;
        }
    }

    /**
     * Elimina un componente. Devuelve false si no existía.
     */
    public function eliminar(int $idInventario): bool
    {
        try {
            $sql = "
                DELETE FROM INVENTARIO
                WHERE id_inventario = :id
            ";

            $consulta = $this->conexion->prepare($sql);
            $consulta->execute(["id" => $idInventario]);

            return $consulta->rowCount() > 0;
        } catch (PDOException $error) {
            error_log("[InventarioDAO] eliminar: " . $error->getMessage());

            return false;
        }
    }

    /**
     * ¿Existe el componente con ese id?
     */
    public function existe(int $idInventario): bool
    {
        $sql = "
            SELECT 1
            FROM INVENTARIO
            WHERE id_inventario = :id
        ";

        $consulta = $this->conexion->prepare($sql);
        $consulta->execute(["id" => $idInventario]);

        $existe = $consulta->fetchColumn() !== false;

        $consulta = null;

        return $existe;
    }
}
