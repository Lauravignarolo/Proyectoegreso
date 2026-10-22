<?php

/**
 * DAO de la entidad REGISTRO_DIARIO_DOCENTE.
 *
 * Es el registro de uso de un salón por parte de un docente (clase).
 */
class RegistroDiarioDAO
{
    private PDO $conexion;

    public function __construct(PDO $conexion)
    {
        $this->conexion = $conexion;
    }

    /**
     * Lista todos los registros, del más reciente al más antiguo.
     */
    public function listar(): array
    {
        return $this->consultar("");
    }

    /**
     * Lista solo los registros de un docente.
     */
    public function listarPorDocente(string $cedula): array
    {
        return $this->consultar("AND r.documento_identidad = :cedula", [
            "cedula" => $cedula
        ]);
    }

    /**
     * Registra la clase de un docente en un salón.
     */
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
            error_log("[RegistroDiarioDAO] registrar: " . $error->getMessage());

            return false;
        }
    }

    /**
     * Elimina un registro. Devuelve false si no existía.
     */
    public function eliminar(int $id): bool
    {
        try {
            $sql = "
                DELETE FROM REGISTRO_DIARIO_DOCENTE
                WHERE id = :id
            ";

            $consulta = $this->conexion->prepare($sql);
            $consulta->execute(["id" => $id]);

            return $consulta->rowCount() > 0;
        } catch (PDOException $error) {
            error_log("[RegistroDiarioDAO] eliminar: " . $error->getMessage());

            return false;
        }
    }

    /**
     * Devuelve el registro con ese id, o null si no existe.
     */
    public function buscarPorId(int $id): ?array
    {
        $sql = "
            SELECT id, documento_identidad
            FROM REGISTRO_DIARIO_DOCENTE
            WHERE id = :id
        ";

        $consulta = $this->conexion->prepare($sql);
        $consulta->execute(["id" => $id]);

        $registro = $consulta->fetch(PDO::FETCH_ASSOC);

        $consulta = null;

        return $registro === false ? null : $registro;
    }

    /**
     * Consulta común de listado.
     *
     * @param string $filtro    Condición extra (ya viene con el AND escrito).
     * @param array  $parametros Valores para la condición extra.
     */
    private function consultar(string $filtro, array $parametros = []): array
    {
        $sql = "
            SELECT
                r.id,
                r.documento_identidad AS cedula,
                r.hora_de_entrada,
                r.hora_de_salida,
                r.tipo_de_salon,
                r.numero_de_salon,
                r.asignatura,
                r.grupo,
                r.turno,
                r.creado_en,
                u.nombre,
                u.apellido

            FROM REGISTRO_DIARIO_DOCENTE AS r
            INNER JOIN USUARIO AS u
                ON u.documento_identidad = r.documento_identidad

            WHERE 1 = 1
            {$filtro}

            ORDER BY r.creado_en DESC, r.id DESC
        ";

        $consulta = $this->conexion->prepare($sql);
        $consulta->execute($parametros);

        $registros = $consulta->fetchAll(PDO::FETCH_ASSOC);

        $consulta = null;

        return $registros;
    }
}
