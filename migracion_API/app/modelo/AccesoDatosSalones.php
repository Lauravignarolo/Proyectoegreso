<?php

class AccesoDatosSalones
{
    private PDO $conexion;

    public function __construct(PDO $conexion)
    {
        $this->conexion = $conexion;
    }

    public function listarSalones(): array
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
}
?>