<?php

/**
 * Clase encargada de registrar nuevas solicitudes de servicio en la base de datos.
 */
class AltaDatosSolicitud
{
    private PDO $conexion;

    /**
     * Constructor parametrizado que recibe una conexion ya establecida a la base de datos.
     * @param PDO $conexion La conexion a la base de datos. PRECONDICION: No debe ser NULL.
     */
    public function __construct(PDO $conexion)
    {
        $this->conexion = $conexion;
    }

    /**
     * Registra una nueva solicitud de servicio en la base de datos.
     * @param string $fechaSolicitada Fecha para la cual se solicita el servicio.
     * @param string $descripcion Descripcion de la solicitud realizada.
     * @return bool True si la solicitud se registro correctamente.
     */
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
        }
    }
}

?>