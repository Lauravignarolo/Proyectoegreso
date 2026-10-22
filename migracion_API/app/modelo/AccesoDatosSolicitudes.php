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
                ON s.id_solicitud = p.id_solicitud
            INNER JOIN USUARIO AS u
                ON p.documento_identidad = u.documento_identidad
            WHERE NOT EXISTS (
                SELECT 1
                FROM SOLICITUD_CUMPLIDA AS c
                WHERE c.id_solicitud = s.id_solicitud
            )
            ORDER BY s.id_solicitud DESC
                    ";

        $consulta = $this->conexion->prepare($sql);

        $consulta->execute();

        $solicitudes = $consulta->fetchAll(PDO::FETCH_ASSOC);

        $consulta = null;

        return $solicitudes;
    }
    
public function cumplirSolicitud(
    int $idSolicitud,
    string $cedulaTecnico
): bool {
    $sql = "
        INSERT INTO SOLICITUD_CUMPLIDA
            (id_solicitud, documento_identidad_tecnico)
        SELECT
            s.id_solicitud,
            :cedula
        FROM SOLICITUD AS s
        WHERE s.id_solicitud = :id
          AND NOT EXISTS (
              SELECT 1
              FROM SOLICITUD_CUMPLIDA AS c
              WHERE c.id_solicitud = s.id_solicitud
          )
    ";

    $consulta = $this->conexion->prepare($sql);

    $consulta->execute([
        "id" => $idSolicitud,
        "cedula" => $cedulaTecnico
    ]);

    return $consulta->rowCount() === 1;
}

}
?>