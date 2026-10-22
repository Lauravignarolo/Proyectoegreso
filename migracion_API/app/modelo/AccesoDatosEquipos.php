
<?php

class AccesoDatosEquipos
{
    private PDO $conexion;

    public function __construct(PDO $conexion)
    {
        $this->conexion = $conexion;
    }

    public function listarEquipos(): array
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

        return $consulta->fetchAll(PDO::FETCH_ASSOC);
    }

    public function agregarEquipo(
        int $numeroEquipo,
        string $tipoSalon,
        int $numeroSalon
    ): bool {
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
    }

    public function eliminarEquipo(int $numeroEquipo): bool
    {
        $sql = "
            DELETE FROM EQUIPO
            WHERE numero_de_equipo = :equipo
        ";

        $consulta = $this->conexion->prepare($sql);

        $consulta->execute([
            "equipo" => $numeroEquipo
        ]);

        return $consulta->rowCount() > 0;
    }
}
