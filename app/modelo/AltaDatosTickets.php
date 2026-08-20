<?php

class AltaDatosTickets
{
    private PDO $conexion;

    public function __construct(PDO $conexion)
    {
        $this->conexion = $conexion;
    }

    public function registrarTickets(
        string $estudianteACargo,
        string $horaDeEntrada,
        string $horaDeSalida,
        string $tipoDeSalon,
        int $numeroDeSalon,
        int $numeroDeEquipo,
        string $asignatura,
        string $grupo,
        string $turno,
        string $estado
    ): bool {

        try {

            // 1. Insertar el ticket
            $sqlTicket = "INSERT INTO TICKETS
                (numero_de_equipo, numero_de_salon, asignatura,
                 hora_de_entrada, hora_de_salida, grupo, turno)
                VALUES
                (:numero_de_equipo, :numero_de_salon, :asignatura,
                 :hora_de_entrada, :hora_de_salida, :grupo, :turno)";

            $consultaTicket = $this->conexion->prepare($sqlTicket);

            $consultaTicket->execute([
                "numero_de_equipo" => $numeroDeEquipo,
                "numero_de_salon" => $numeroDeSalon,
                "asignatura" => $asignatura,
                "hora_de_entrada" => $horaDeEntrada,
                "hora_de_salida" => $horaDeSalida,
                "grupo" => $grupo,
                "turno" => $turno
            ]);

            // Obtener el ID del ticket recién creado
            $idTicket = $this->conexion->lastInsertId();


            // 2. Insertar aviso de estado
            $sqlAviso = "INSERT INTO AVISO_DE_ESTADO
                (id_ticket, urgencia, numero_de_equipo,
                 estudiante_a_cargo, estado)
                VALUES
                (:id_ticket, :urgencia, :numero_de_equipo,
                 :estudiante_a_cargo, :estado)";

            $consultaAviso = $this->conexion->prepare($sqlAviso);

            $consultaAviso->execute([
                "id_ticket" => $idTicket,
                "urgencia" => $tipoDeSalon,
                "numero_de_equipo" => $numeroDeEquipo,
                "estudiante_a_cargo" => $estudianteACargo,
                "estado" => $estado
            ]);

            return true;

        } catch (PDOException $error) {

            echo "ERROR SQL: " . $error->getMessage();
            exit;
        }
    }
}