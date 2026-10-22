<?php

class AccesoDatosInventario
{

      private PDO $conexion;

    public function __construct(PDO $conexion)
    {
        $this->conexion = $conexion;
    }

    public function listarInventario(): array
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

        return $consulta->fetchAll(PDO::FETCH_ASSOC);
    }

    public function agregarInventario(
        string $componente,
        string $marca,
        int $cantidad
    ): bool {
        $sql = "
            INSERT INTO INVENTARIO
                (componente, marca, cantidad)
            VALUES
                (:componente, :marca, :cantidad)
        ";

        $consulta = $this->conexion->prepare($sql);

        return $consulta->execute([
            "componente" => $componente,
            "marca" => $marca,
            "cantidad" => $cantidad
        ]);
    }

    public function editarInventario(
        int $idInventario,
        string $componente,
        string $marca,
        int $cantidad
    ): bool {
        $sql = "
            UPDATE INVENTARIO
            SET
                componente = :componente,
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

        return $consulta->rowCount() > 0
            || $this->existeInventario($idInventario);
    }

    public function eliminarInventario(int $idInventario): bool
    {
        $sql = "
            DELETE FROM INVENTARIO
            WHERE id_inventario = :id
        ";

        $consulta = $this->conexion->prepare($sql);

        $consulta->execute([
            "id" => $idInventario
        ]);

        return $consulta->rowCount() > 0;
    }

    private function existeInventario(int $idInventario): bool
    {
        $sql = "
            SELECT 1
            FROM INVENTARIO
            WHERE id_inventario = :id
        ";

        $consulta = $this->conexion->prepare($sql);

        $consulta->execute([
            "id" => $idInventario
        ]);

        return $consulta->fetchColumn() !== false;
    }
}