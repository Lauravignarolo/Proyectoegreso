<?php

/**
 * DAO de la entidad EQUIPO (las computadoras de cada salón).
 */
class EquipoDAO
{
    private PDO $conexion;

    public function __construct(PDO $conexion)
    {
        $this->conexion = $conexion;
    }

    /**
     * Lista todos los equipos con el salón al que pertenecen.
     */
    public function listar(): array
    {
        $sql = "
            SELECT
                numero_de_equipo,
                tipo_de_salon,
                numero_de_salon
            FROM EQUIPO
            ORDER BY tipo_de_salon, numero_de_salon, numero_de_equipo
        ";

        $consulta = $this->conexion->prepare($sql);
        $consulta->execute();

        $equipos = $consulta->fetchAll(PDO::FETCH_ASSOC);

        $consulta = null;

        return $equipos;
    }

    /**
     * Registra un equipo en un salón.
     */
    public function registrar(
        int $numeroEquipo,
        string $tipoSalon,
        int $numeroSalon
    ): bool {
        try {
            $sql = "
                INSERT INTO EQUIPO
                    (numero_de_equipo, tipo_de_salon, numero_de_salon)
                VALUES
                    (:equipo, :tipo, :salon)
            ";

            $consulta = $this->conexion->prepare($sql);

            return $consulta->execute([
                "equipo" => $numeroEquipo,
                "tipo" => $tipoSalon,
                "salon" => $numeroSalon
            ]);
        } catch (PDOException $error) {
            error_log("[EquipoDAO] registrar: " . $error->getMessage());

            return false;
        }
    }

    /**
     * Elimina un equipo. Devuelve false si no existía.
     */
    public function eliminar(int $numeroEquipo): bool
    {
        try {
            $sql = "
                DELETE FROM EQUIPO
                WHERE numero_de_equipo = :equipo
            ";

            $consulta = $this->conexion->prepare($sql);
            $consulta->execute(["equipo" => $numeroEquipo]);

            return $consulta->rowCount() > 0;
        } catch (PDOException $error) {
            error_log("[EquipoDAO] eliminar: " . $error->getMessage());

            return false;
        }
    }
}
