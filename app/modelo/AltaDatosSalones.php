<?php

class AltaDatosSalones
{
    private PDO $conexion;

    public function __construct(PDO $conexion)
    {
        $this->conexion = $conexion;
    }

    public function registrarSalon(
        string $tipoSalon,
        int $numeroSalon
    ): bool {

        try {

            $sql = "INSERT INTO SALON
                    (tipo_de_salon, numero_de_salon)
                    VALUES
                    (:tipo_de_salon, :numero_de_salon)";

            $consulta = $this->conexion->prepare($sql);

            $consulta->execute([
                "tipo_de_salon" => $tipoSalon,
                "numero_de_salon" => $numeroSalon
            ]);

            return true;

        } catch (PDOException $error) {

            echo "ERROR SQL: " . $error->getMessage();
            exit;
        }
    }
}