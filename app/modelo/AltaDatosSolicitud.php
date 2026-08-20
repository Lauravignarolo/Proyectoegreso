<?php

class AltaDatosSolicitud
{
    private PDO $conexion;

    public function __construct(PDO $conexion)
    {
        $this->conexion = $conexion;
    }

    public function registrarSolicitud(
        string $fechaSolicitada,
        string $descripcion
    ): bool {

        try {

            $sql = "INSERT INTO SOLICITUD 
                    (fecha_Solicitada, descripcion)
                    VALUES 
                    (:fecha_Solicitada, :descripcion)";

            $consulta = $this->conexion->prepare($sql);

            $consulta->execute([
                "fecha_Solicitada" => $fechaSolicitada,
                "descripcion" => $descripcion
            ]);

            return true;

        } catch (PDOException $error) {

    echo "ERROR SQL: " . $error->getMessage();
    exit;
}//catch (PDOException $error) {
//
  //          return false;
    //    }
    }
}