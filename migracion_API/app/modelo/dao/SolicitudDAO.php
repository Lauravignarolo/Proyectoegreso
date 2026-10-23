<?php

/**
 * DAO de la entidad SOLICITUD (pedidos de servicio de los docentes).
 *
 * Una solicitud se guarda junto con el docente que la pidió (PIDE) y, cuando
 * un técnico la resuelve, con el técnico que la cumplió (SOLICITUD_CUMPLIDA).
 */
class SolicitudDAO
{
    private PDO $conexion;

    public function __construct(PDO $conexion)
    {
        $this->conexion = $conexion;
    }

    /**
     * Lista las solicitudes que todavía no fueron cumplidas.
     */
    public function listarPendientes(): array
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

    /**
     * Registra una solicitud y la vincula al docente que la hizo.
     */
    public function registrar(
        string $fechaSolicitada,
        string $descripcion,
        string $cedulaDocente
    ): bool {
        try {
            $this->conexion->beginTransaction();

            $sqlSolicitud = "
                INSERT INTO SOLICITUD (fecha_solicitada, descripcion)
                VALUES (:fecha, :descripcion)
            ";

            $consultaSolicitud = $this->conexion->prepare($sqlSolicitud);
            $consultaSolicitud->execute([
                "fecha" => $fechaSolicitada,
                "descripcion" => $descripcion
            ]);

            $idSolicitud = $this->conexion->lastInsertId();

            $sqlPide = "
                INSERT INTO PIDE (id_solicitud, documento_identidad)
                VALUES (:id_solicitud, :cedula)
            ";

            $consultaPide = $this->conexion->prepare($sqlPide);
            $consultaPide->execute([
                "id_solicitud" => $idSolicitud,
                "cedula" => $cedulaDocente
            ]);

            $this->conexion->commit();

            return true;
        } catch (Throwable $error) {
            if ($this->conexion->inTransaction()) {
                $this->conexion->rollBack();
            }

            error_log("[SolicitudDAO] registrar: " . $error->getMessage());

            return false;
        }
    }

    /**
     * Marca la solicitud como cumplida. Devuelve false si ya lo estaba.
     */
    public function cumplir(int $idSolicitud, string $cedulaTecnico): bool
    {
        try {
            $sql = "
                INSERT INTO SOLICITUD_CUMPLIDA
                    (id_solicitud, documento_identidad_tecnico)
                SELECT
                    s.id_solicitud, :cedula
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
        } catch (PDOException $error) {
            error_log("[SolicitudDAO] cumplir: " . $error->getMessage());

            return false;
        }
    }
}
