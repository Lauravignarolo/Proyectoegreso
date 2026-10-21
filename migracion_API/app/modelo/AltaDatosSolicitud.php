
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
        string $descripcion,
        string $cedulaDocente
    ): bool {
        try {
            $this->conexion->beginTransaction();

            $sql = "
                INSERT INTO SOLICITUD
                    (fecha_solicitada, descripcion)
                VALUES
                    (:fecha, :descripcion)
            ";

            $consulta = $this->conexion->prepare($sql);

            $consulta->execute([
                "fecha" => $fechaSolicitada,
                "descripcion" => $descripcion
            ]);

            $idSolicitud = $this->conexion->lastInsertId();

            $sqlPide = "
                INSERT INTO PIDE
                    (id_solicitud, documento_identidad)
                VALUES
                    (:idSolicitud, :cedula)
            ";

            $consultaPide = $this->conexion->prepare($sqlPide);

            $consultaPide->execute([
                "idSolicitud" => $idSolicitud,
                "cedula" => $cedulaDocente
            ]);

            $this->conexion->commit();

            return true;

        } catch (Throwable $error) {
            if ($this->conexion->inTransaction()) {
                $this->conexion->rollBack();
            }

            error_log(
                "Error al registrar solicitud: " .
                $error->getMessage()
            );

            return false;
        }
    }
}
