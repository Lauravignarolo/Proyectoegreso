<?php

/**
 * Clase encargada de registrar nuevos tickets de incidencia y su aviso de estado inicial.
 */
class AltaDatosTickets
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
     * Registra un nuevo ticket de incidencia y su correspondiente aviso de estado,
     * usando una transaccion para garantizar que ambas inserciones se completen juntas.
     * @param string $estudianteACargo Nombre del estudiante a cargo del equipo.
     * @param string $horaDeEntrada Hora de entrada al salon.
     * @param string $horaDeSalida Hora de salida del salon.
     * @param string $tipoDeSalon Tipo/urgencia del salon donde ocurre la incidencia.
     * @param int $numeroDeSalon Numero del salon donde ocurre la incidencia.
     * @param int $numeroDeEquipo Numero del equipo afectado.
     * @param string $asignatura Asignatura que se dictaba al momento de la incidencia.
     * @param string $grupo Grupo correspondiente a la clase.
     * @param string $turno Turno en que ocurrio la incidencia.
     * @param string $estado Estado inicial del ticket.
     * @return bool True si el ticket y su aviso se registraron correctamente.
     */
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

            // Iniciar transaccion
            $this->conexion->beginTransaction();


            // 1. Insertar el ticket
            $sqlTicket = "INSERT INTO TICKETS
                (
                    numero_de_equipo,
                    numero_de_salon,
                    tipo_de_salon,
                    asignatura,
                    hora_de_entrada,
                    hora_de_salida,
                    grupo,
                    turno
                )
                VALUES
                (
                    :numero_de_equipo,
                    :numero_de_salon,
                    :tipo_de_salon,
                    :asignatura,
                    :hora_de_entrada,
                    :hora_de_salida,
                    :grupo,
                    :turno
                )";

            $consultaTicket = $this->conexion->prepare($sqlTicket);

            $consultaTicket->execute([
                "numero_de_equipo" => $numeroDeEquipo,
                "numero_de_salon" => $numeroDeSalon,
                "tipo_de_salon" => $tipoDeSalon,
                "asignatura" => $asignatura,
                "hora_de_entrada" => $horaDeEntrada,
                "hora_de_salida" => $horaDeSalida,
                "grupo" => $grupo,
                "turno" => $turno
            ]);


            // Obtener el ID del ticket recien creado
            $idTicket = $this->conexion->lastInsertId();


            // 2. Insertar aviso de estado
            $sqlAviso = "INSERT INTO AVISO_DE_ESTADO
                (
                    id_ticket,
                    urgencia,
                    numero_de_equipo,
                    estudiante_a_cargo,
                    estado
                )
                VALUES
                (
                    :id_ticket,
                    :urgencia,
                    :numero_de_equipo,
                    :estudiante_a_cargo,
                    :estado
                )";

            $consultaAviso = $this->conexion->prepare($sqlAviso);

            $consultaAviso->execute([
                "id_ticket" => $idTicket,
                "urgencia" => $tipoDeSalon,
                "numero_de_equipo" => $numeroDeEquipo,
                "estudiante_a_cargo" => $estudianteACargo,
                "estado" => $estado
            ]);


            // Confirmar las dos inserciones
            $this->conexion->commit();

            return true;


        } catch (PDOException $error) {

            // Si algo falla, deshacer los INSERT
            if ($this->conexion->inTransaction()) {
                $this->conexion->rollBack();
            }

            echo "ERROR SQL: " . $error->getMessage();
            exit;
        }
    }
}

?>