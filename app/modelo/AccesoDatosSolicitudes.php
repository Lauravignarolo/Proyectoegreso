<?php

class AccesoDatosSolicitudes
{
    private PDO $conexion;

    public function __construct(PDO $conexion)
    {
        $this->conexion = $conexion;
    }

    public function listarSolicitudes(): array
    {
        $sql = "
            SELECT
                s.id_solicitud,
                s.descripcion,
                s.fecha_solicitada,
                u.nombre,
                u.apellido

            FROM SOLICITUD AS s

            INNER JOIN PIDE AS p
                ON p.id_solicitud = s.id_solicitud

            INNER JOIN USUARIO AS u
                ON u.documento_identidad = p.documento_identidad

            ORDER BY s.fecha_solicitada DESC
        ";

        $consulta = $this->conexion->prepare($sql);

        $consulta->execute();

        $solicitudes = $consulta->fetchAll(PDO::FETCH_ASSOC);

        $consulta = null;

        return $solicitudes;
    }
}
?>