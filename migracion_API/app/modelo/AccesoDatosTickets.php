<?php

class AccesoDatosTickets
{
    private PDO $conexion;

    public function __construct(PDO $conexion)
    {
        $this->conexion = $conexion;
    }

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

            ORDER BY t.id_ticket DESC
        ";

        $consulta = $this->conexion->prepare($sql);

        $consulta->execute();

        $tickets = $consulta->fetchAll(PDO::FETCH_ASSOC);

        $consulta = null;

        return $tickets;
    }
}
?>