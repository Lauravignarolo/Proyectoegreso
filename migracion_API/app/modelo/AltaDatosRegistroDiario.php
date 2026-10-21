
<?php

class AltaDatosRegistroDiario
{
    private PDO $conexion;

    public function __construct(PDO $conexion)
    {
        $this->conexion = $conexion;
    }

    public function registrar(
        string $cedula,
        string $horaEntrada,
        string $horaSalida,
        string $tipoSalon,
        int $numeroSalon,
        string $asignatura,
        string $grupo,
        string $turno
    ): bool {
        try {
            $sql = "
                INSERT INTO REGISTRO_DIARIO_DOCENTE (
                    documento_identidad,
                    hora_de_entrada,
                    hora_de_salida,
                    tipo_de_salon,
                    numero_de_salon,
                    asignatura,
                    grupo,
                    turno
                ) VALUES (
                    :cedula,
                    :entrada,
                    :salida,
                    :tipo,
                    :numero,
                    :asignatura,
                    :grupo,
                    :turno
                )
            ";

            $consulta = $this->conexion->prepare($sql);

            return $consulta->execute([
                "cedula" => $cedula,
                "entrada" => $horaEntrada,
                "salida" => $horaSalida,
                "tipo" => $tipoSalon,
                "numero" => $numeroSalon,
                "asignatura" => $asignatura,
                "grupo" => $grupo,
                "turno" => $turno
            ]);

        } catch (PDOException $error) {
            error_log(
                "Error al registrar diario: " .
                $error->getMessage()
            );

            return false;
        }
    }
}
