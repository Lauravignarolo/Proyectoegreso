<?php

class BajaDatosSalones
{
    private PDO $conexion;

    public function __construct(PDO $conexion)
    {
        $this->conexion = $conexion;
    }

    public function eliminarSalon(
        int $numeroSalon,
        string $tipoSalon
    ): bool {

        try {

            $sql = "DELETE FROM SALON
                    WHERE numero_de_salon = :numero_de_salon
                    AND tipo_de_salon = :tipo_de_salon";

            $consulta = $this->conexion->prepare($sql);

            $consulta->execute([
                "numero_de_salon" => $numeroSalon,
                "tipo_de_salon" => $tipoSalon
            ]);

            return true;

        } catch (PDOException $error) {

            return false;
        }
    }
}