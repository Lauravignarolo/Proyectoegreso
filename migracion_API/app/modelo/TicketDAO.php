<?php

/**
 * Clase encargada de consultar y registrar tickets (incidentes de equipos).
 * Solo habla con la base de datos: las validaciones y los permisos
 * los decide TicketController.
 */
class TicketDAO
{
    private PDO $conexion;

    /**
     * @param PDO $conexion Conexión ya establecida. 
     */
    public function __construct(PDO $conexion)
    {
        $this->conexion = $conexion;
    }

    /**
     * Lista todos los tickets, del más nuevo al más viejo.
     * @return array Lista de tickets (cada uno es un arreglo asociativo).
     */
    public function listarTickets(): array
    {
        $sql = "
            SELECT
                t.id_ticket,
                t.numero_de_equipo,
                t.numero_de_salon,
                t.tipo_de_salon,
                t.asignatura,
                t.hora_de_entrada,
                t.hora_de_salida,
                t.grupo,
                t.turno,

                a.estudiante_a_cargo,
                a.estado,
                a.urgencia,

                u.nombre,
                u.apellido

            FROM TICKETS AS t

            LEFT JOIN AVISO_DE_ESTADO AS a
                ON a.id_ticket = t.id_ticket

            LEFT JOIN HACE AS h
                ON h.id_ticket = t.id_ticket

            LEFT JOIN USUARIO AS u
                ON u.documento_identidad = h.documento_identidad

            WHERE a.estado IS NULL OR a.estado <> 'Resuelto'
            ORDER BY t.id_ticket DESC
        ";

        $consulta = $this->conexion->prepare($sql);
        $consulta->execute();

        $tickets = $consulta->fetchAll(PDO::FETCH_ASSOC);

        $consulta = null;

        return $tickets;
    }

    /**
     * Comprueba si existe un salón.
     * @return bool 
     */
    public function existeSalon(string $tipoDeSalon, int $numeroDeSalon): bool
    {
        $sql = "
            SELECT COUNT(*)
            FROM SALON
            WHERE tipo_de_salon = :tipo_de_salon
            AND numero_de_salon = :numero_de_salon
        ";

        $consulta = $this->conexion->prepare($sql);
        $consulta->execute([
            "tipo_de_salon" => $tipoDeSalon,
            "numero_de_salon" => $numeroDeSalon
        ]);

        $cantidad = (int) $consulta->fetchColumn();

        $consulta = null;

        return $cantidad > 0;
    }

    /**
     * Registra un ticket con su aviso de estado y el docente que lo creó.
     * Se usa una transacción, es decir o se guardan las tres cosas, o no se guarda ninguna.
     * @param string $cedulaDocente Cédula del docente que registra el ticket (viene de la sesión).
     * @return bool TRUE si se registró todo correctamente.
     */
    public function registrarTickets(
        string $cedulaDocente,
        string $estudianteACargo,
        string $horaDeEntrada,
        string $horaDeSalida,
        string $tipoDeSalon,
        int $numeroDeSalon,
        int $numeroDeEquipo,
        string $asignatura,
        string $grupo,
        string $turno,
        string $urgencia,
        string $estado
    ): bool {

        try {

            $this->conexion->beginTransaction();

            $sqlTicket = "
                INSERT INTO TICKETS
                    (numero_de_equipo, numero_de_salon, tipo_de_salon, asignatura,
                     hora_de_entrada, hora_de_salida, grupo, turno)
                VALUES
                    (:numero_de_equipo, :numero_de_salon, :tipo_de_salon, :asignatura,
                     :hora_de_entrada, :hora_de_salida, :grupo, :turno)
            ";

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

            $idTicket = $this->conexion->lastInsertId();

            $sqlAviso = "
                INSERT INTO AVISO_DE_ESTADO
                    (id_ticket, urgencia, numero_de_equipo, estudiante_a_cargo, estado)
                VALUES
                    (:id_ticket, :urgencia, :numero_de_equipo, :estudiante_a_cargo, :estado)
            ";

            $consultaAviso = $this->conexion->prepare($sqlAviso);
            $consultaAviso->execute([
                "id_ticket" => $idTicket,
                "urgencia" => $urgencia,
                "numero_de_equipo" => $numeroDeEquipo,
                "estudiante_a_cargo" => $estudianteACargo,
                "estado" => $estado
            ]);

            $sqlHace = "
                INSERT INTO HACE (documento_identidad, id_ticket)
                VALUES (:documento_identidad, :id_ticket)
            ";

            $consultaHace = $this->conexion->prepare($sqlHace);
            $consultaHace->execute([
                "documento_identidad" => $cedulaDocente,
                "id_ticket" => $idTicket
            ]);

            $this->conexion->commit();

            return true;

        } catch (PDOException $error) {

            if ($this->conexion->inTransaction()) {
                $this->conexion->rollBack();
            }

            error_log("ERROR SQL (TicketDAO): " . $error->getMessage());

            return false;
        }
    }
    
public function repararTicket(int $idTicket, string $cedulaTecnico): bool
{
    try {
        $this->conexion->beginTransaction();

        $consulta = $this->conexion->prepare("
            SELECT a.estado
            FROM AVISO_DE_ESTADO AS a
            WHERE a.id_ticket = :id_ticket
            FOR UPDATE
        ");

        $consulta->execute([
            "id_ticket" => $idTicket
        ]);

        $estado = $consulta->fetchColumn();

        if ($estado === false || $estado === "Resuelto") {
            $this->conexion->rollBack();
            return false;
        }

        $actualizar = $this->conexion->prepare("
            UPDATE AVISO_DE_ESTADO
            SET estado = 'Resuelto'
            WHERE id_ticket = :id_ticket
        ");

        $actualizar->execute([
            "id_ticket" => $idTicket
        ]);

        $registrar = $this->conexion->prepare("
            INSERT INTO SOLUCIONA
                (id_ticket, documento_identidad)
            VALUES
                (:id_ticket, :cedula)
        ");

        $registrar->execute([
            "id_ticket" => $idTicket,
            "cedula" => $cedulaTecnico
        ]);

        $this->conexion->commit();
        return true;

    } catch (Throwable $error) {
        if ($this->conexion->inTransaction()) {
            $this->conexion->rollBack();
        }

        error_log(
            "Error al reparar ticket: " . $error->getMessage()
        );

        return false;
    }
}

}
