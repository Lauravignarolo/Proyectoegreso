<?php

/**
 * DAO de la entidad SALON.
 */
class SalonDAO
{
    private PDO $conexion;

    public function __construct(PDO $conexion)
    {
        $this->conexion = $conexion;
    }

    /**
     * Lista todos los salones, ordenados por tipo y número.
     */
    public function listar(): array
    {
        $sql = "
            SELECT
                tipo_de_salon,
                numero_de_salon
            FROM SALON
            ORDER BY tipo_de_salon, numero_de_salon
        ";

        $consulta = $this->conexion->prepare($sql);
        $consulta->execute();

        $salones = $consulta->fetchAll(PDO::FETCH_ASSOC);

        $consulta = null;

        return $salones;
    }

    /**
     * Registra un salón. Devuelve false si ya existía.
     */
    public function registrar(string $tipoSalon, int $numeroSalon): bool
    {
        try {
            $sql = "
                INSERT INTO SALON (tipo_de_salon, numero_de_salon)
                VALUES (:tipo_de_salon, :numero_de_salon)
            ";

            $consulta = $this->conexion->prepare($sql);

            return $consulta->execute([
                "tipo_de_salon" => $tipoSalon,
                "numero_de_salon" => $numeroSalon
            ]);
        } catch (PDOException $error) {
            error_log("[SalonDAO] registrar: " . $error->getMessage());

            return false;
        }
    }

    /**
     * Elimina un salón. Devuelve false si no existía.
     */
    public function eliminar(int $numeroSalon, string $tipoSalon): bool
    {
        try {
            $sql = "
                DELETE FROM SALON
                WHERE numero_de_salon = :numero_de_salon
                  AND tipo_de_salon = :tipo_de_salon
            ";

            $consulta = $this->conexion->prepare($sql);
            $consulta->execute([
                "numero_de_salon" => $numeroSalon,
                "tipo_de_salon" => $tipoSalon
            ]);

            return $consulta->rowCount() > 0;
        } catch (PDOException $error) {
            error_log("[SalonDAO] eliminar: " . $error->getMessage());

            return false;
        }
    }

    /**
     * ¿Existe el salón indicado?
     */
    public function existe(string $tipoSalon, int $numeroSalon): bool
    {
        $sql = "
            SELECT 1
            FROM SALON
            WHERE tipo_de_salon = :tipo_de_salon
              AND numero_de_salon = :numero_de_salon
        ";

        $consulta = $this->conexion->prepare($sql);
        $consulta->execute([
            "tipo_de_salon" => $tipoSalon,
            "numero_de_salon" => $numeroSalon
        ]);

        $existe = $consulta->fetchColumn() !== false;

        $consulta = null;

        return $existe;
    }
}
