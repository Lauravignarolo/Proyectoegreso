<?php

/**
 * DAO de la entidad TICKETS (incidencias de los equipos).
 *
 * Un ticket se guarda junto con su aviso de estado (AVISO_DE_ESTADO) y
 * con el docente que lo reportó (HACE). Eso se hace en una transacción.
 */
class TicketDAO
{
    private PDO $conexion;

    public function __construct(PDO $conexion)
    {
        $this->conexion = $conexion;
    }

    /**
     * Lista los tickets que todavía no fueron resueltos.
     */
    public function listarPendientes(): array
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
     * Lista todos los tickets, resueltos o no.
     */
    public function listarTodos(): array
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

            ORDER BY t.id_ticket DESC
        ";

        $consulta = $this->conexion->prepare($sql);
        $consulta->execute();

        $tickets = $consulta->fetchAll(PDO::FETCH_ASSOC);

        $consulta = null;

        return $tickets;
    }

    /**
     * ¿Existe el salón donde se quiere registrar el ticket?
     */
    public function existeSalon(string $tipoSalon, int $numeroSalon): bool
    {
        $sql = "
            SELECT 1
            FROM SALON
            WHERE tipo_de_salon = :tipo_de_salon
              AND numero_de_salon = :numero_de_salon
        ";

        $consulta = $this->conexion->prepare($sql);
        $consulta->execute([
            "tipo_de_salon" => $tipoSalon,
            "numero_de_salon" => $numeroSalon
        ]);

        $existe = $consulta->fetchColumn() !== false;

        $consulta = null;

        return $existe;
    }

    /**
     * Registra un ticket con su aviso de estado y el docente que lo reportó.
     */
    public function registrar(
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

            error_log("[TicketDAO] registrar: " . $error->getMessage());

            return false;
        }
    }

    /**
     * Marca el ticket como resuelto y registra al técnico que lo reparó.
     */
    public function reparar(int $idTicket, string $cedulaTecnico): bool
    {
        try {
            $this->conexion->beginTransaction();

            $consulta = $this->conexion->prepare("
                SELECT a.estado
                FROM AVISO_DE_ESTADO AS a
                WHERE a.id_ticket = :id_ticket
                FOR UPDATE
            ");

            $consulta->execute(["id_ticket" => $idTicket]);

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

            $actualizar->execute(["id_ticket" => $idTicket]);

            $registrar = $this->conexion->prepare("
                INSERT INTO SOLUCIONA (id_ticket, documento_identidad)
                VALUES (:id_ticket, :cedula)
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

            error_log("[TicketDAO] reparar: " . $error->getMessage());

            return false;
        }
    }
}
