
<?php

class BajaDatosUsuario
{
    private PDO $conexion;

    public function __construct(PDO $conexion)
    {
        $this->conexion = $conexion;
    }

    public function eliminarUsuario(string $cedula): bool
    {
        try {
            $this->conexion->beginTransaction();

            // Eliminar relaciones de roles
            $tablasRoles = [
                "ADMINISTRADOR",
                "DOCENTE",
                "TECNICO",
                "DIRECCION"
            ];

            foreach ($tablasRoles as $tabla) {
                $sql = "DELETE FROM $tabla
                        WHERE documento_identidad = :cedula";

                $consulta = $this->conexion->prepare($sql);
                $consulta->execute(["cedula" => $cedula]);
            }

            // Eliminar usuario
            $sqlUsuario = "DELETE FROM USUARIO
                           WHERE documento_identidad = :cedula";

            $consultaUsuario = $this->conexion->prepare($sqlUsuario);
            $consultaUsuario->execute(["cedula" => $cedula]);

            if ($consultaUsuario->rowCount() === 0) {
                $this->conexion->rollBack();
                return false;
            }

            $this->conexion->commit();
            return true;

        } catch (PDOException $error) {

            if ($this->conexion->inTransaction()) {
                $this->conexion->rollBack();
            }

            error_log($error->getMessage());
            return false;
        }
    }
}
