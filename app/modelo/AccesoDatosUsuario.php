<?php

class AccesoDatosUsuario
{
    private PDO $conexion;

    public function __construct(PDO $conexion)
    {
        $this->conexion = $conexion;
    }

    public function buscarUsuario(string $cedula): ?Usuario
    {
        $sql = "
            SELECT
                u.documento_identidad,
                u.contrasena,

                CASE
                    WHEN a.documento_identidad IS NOT NULL THEN 1
                    ELSE 0
                END AS administrador,

                CASE
                    WHEN d.documento_identidad IS NOT NULL THEN 1
                    ELSE 0
                END AS docente,

                CASE
                    WHEN di.documento_identidad IS NOT NULL THEN 1
                    ELSE 0
                END AS direccion,

                CASE
                    WHEN t.documento_identidad IS NOT NULL THEN 1
                    ELSE 0
                END AS tecnico

            FROM USUARIO AS u

            LEFT JOIN ADMINISTRADOR AS a
                ON a.documento_identidad = u.documento_identidad

            LEFT JOIN DOCENTE AS d
                ON d.documento_identidad = u.documento_identidad

            LEFT JOIN DIRECCION AS di
                ON di.documento_identidad = u.documento_identidad

            LEFT JOIN TECNICO AS t
                ON t.documento_identidad = u.documento_identidad

            WHERE u.documento_identidad = :cedula
        ";

        $consulta = $this->conexion->prepare($sql);

        $consulta->execute([
            "cedula" => $cedula
        ]);

        $datos = $consulta->fetch(PDO::FETCH_ASSOC);

        $consulta = null;

        if ($datos === false) {
            return null;
        }

        return new Usuario(
            $datos["documento_identidad"],
            $datos["contrasena"],
            (bool) $datos["administrador"],
            (bool) $datos["docente"],
            (bool) $datos["direccion"],
            (bool) $datos["tecnico"]
        );
    }

    public function listarUsuarios(): array
{
    $sql = "
        SELECT
            u.documento_identidad AS cedula,
            u.nombre,
            u.apellido,

            CASE
                WHEN a.documento_identidad IS NOT NULL THEN 1
                ELSE 0
            END AS administrador,

            CASE
                WHEN d.documento_identidad IS NOT NULL THEN 1
                ELSE 0
            END AS docente,

            CASE
                WHEN di.documento_identidad IS NOT NULL THEN 1
                ELSE 0
            END AS direccion,

            CASE
                WHEN t.documento_identidad IS NOT NULL THEN 1
                ELSE 0
            END AS tecnico

        FROM USUARIO AS u

        LEFT JOIN ADMINISTRADOR AS a
            ON a.documento_identidad = u.documento_identidad

        LEFT JOIN DOCENTE AS d
            ON d.documento_identidad = u.documento_identidad

        LEFT JOIN DIRECCION AS di
            ON di.documento_identidad = u.documento_identidad

        LEFT JOIN TECNICO AS t
            ON t.documento_identidad = u.documento_identidad

        ORDER BY u.apellido, u.nombre
    ";

    $consulta = $this->conexion->prepare($sql);
    $consulta->execute();

    $usuarios = $consulta->fetchAll(PDO::FETCH_ASSOC);

    $consulta = null;

    return $usuarios;
}
public function eliminarUsuario(string $cedula): void
{
    try {

        $this->conexion->beginTransaction();

        // Eliminar el rol de administrador, si existe
        $sql = "
            DELETE FROM ADMINISTRADOR
            WHERE documento_identidad = :cedula
        ";

        $consulta = $this->conexion->prepare($sql);
        $consulta->execute([
            "cedula" => $cedula
        ]);

        // Eliminar el rol de docente, si existe
        $sql = "
            DELETE FROM DOCENTE
            WHERE documento_identidad = :cedula
        ";

        $consulta = $this->conexion->prepare($sql);
        $consulta->execute([
            "cedula" => $cedula
        ]);

        // Eliminar el rol de dirección, si existe
        $sql = "
            DELETE FROM DIRECCION
            WHERE documento_identidad = :cedula
        ";

        $consulta = $this->conexion->prepare($sql);
        $consulta->execute([
            "cedula" => $cedula
        ]);

        // Eliminar el rol de técnico, si existe
        $sql = "
            DELETE FROM TECNICO
            WHERE documento_identidad = :cedula
        ";

        $consulta = $this->conexion->prepare($sql);
        $consulta->execute([
            "cedula" => $cedula
        ]);

        // Finalmente eliminar el usuario
        $sql = "
            DELETE FROM USUARIO
            WHERE documento_identidad = :cedula
        ";

        $consulta = $this->conexion->prepare($sql);
        $consulta->execute([
            "cedula" => $cedula
        ]);

        $this->conexion->commit();

    } catch (PDOException $e) {

        if ($this->conexion->inTransaction()) {
            $this->conexion->rollBack();
        }

        throw $e;
    }
}
}

?>