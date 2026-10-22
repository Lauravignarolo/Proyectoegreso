<?php

require_once RUTA_MODELO . "/Usuario.php";

/**
 * DAO de la entidad USUARIO (y sus tablas de roles).
 *
 * Todas las consultas de usuarios y roles pasan por acá. El resto del
 * sistema nunca escribe SQL de USUARIO.
 */
class UsuarioDAO
{
    /**
     * Traduce el nombre del rol que llega del formulario al nombre de la
     * tabla donde se guarda ese rol.
     */
    private const TABLAS_ROL = [
        "administrador" => "ADMINISTRADOR",
        "docente" => "DOCENTE",
        "tecnico" => "TECNICO",
        "direccion" => "DIRECCION"
    ];

    private PDO $conexion;

    public function __construct(PDO $conexion)
    {
        $this->conexion = $conexion;
    }

    /**
     * Busca un usuario por su cédula y arma la entidad Usuario.
     */
    public function buscarPorCedula(string $cedula): ?Usuario
    {
        $sql = "
            SELECT
                u.documento_identidad,
                u.contrasena,
                u.estado,

                CASE WHEN a.documento_identidad IS NOT NULL THEN 1 ELSE 0 END
                    AS administrador,
                CASE WHEN d.documento_identidad IS NOT NULL THEN 1 ELSE 0 END
                    AS docente,
                CASE WHEN di.documento_identidad IS NOT NULL THEN 1 ELSE 0 END
                    AS direccion,
                CASE WHEN t.documento_identidad IS NOT NULL THEN 1 ELSE 0 END
                    AS tecnico

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
        $consulta->execute(["cedula" => $cedula]);

        $datos = $consulta->fetch(PDO::FETCH_ASSOC);

        $consulta = null;

        if ($datos === false) {
            return null;
        }

        return new Usuario(
            $datos["documento_identidad"],
            $datos["contrasena"],
            (bool) $datos["estado"],
            (bool) $datos["administrador"],
            (bool) $datos["docente"],
            (bool) $datos["direccion"],
            (bool) $datos["tecnico"]
        );
    }

    /**
     * Lista todos los usuarios con sus roles, ordenados por apellido y nombre.
     */
    public function listar(): array
    {
        $sql = "
            SELECT
                u.documento_identidad AS cedula,
                u.nombre,
                u.apellido,
                u.estado,

                CASE WHEN a.documento_identidad IS NOT NULL THEN 1 ELSE 0 END
                    AS administrador,
                CASE WHEN d.documento_identidad IS NOT NULL THEN 1 ELSE 0 END
                    AS docente,
                CASE WHEN di.documento_identidad IS NOT NULL THEN 1 ELSE 0 END
                    AS direccion,
                CASE WHEN t.documento_identidad IS NOT NULL THEN 1 ELSE 0 END
                    AS tecnico

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

    /**
     * Devuelve un usuario concreto (sin la contraseña) o null si no existe.
     */
    public function listarPorCedula(string $cedula): ?array
    {
        $sql = "
            SELECT
                u.documento_identidad AS cedula,
                u.nombre,
                u.apellido,
                u.estado,

                CASE WHEN a.documento_identidad IS NOT NULL THEN 1 ELSE 0 END
                    AS administrador,
                CASE WHEN d.documento_identidad IS NOT NULL THEN 1 ELSE 0 END
                    AS docente,
                CASE WHEN di.documento_identidad IS NOT NULL THEN 1 ELSE 0 END
                    AS direccion,
                CASE WHEN t.documento_identidad IS NOT NULL THEN 1 ELSE 0 END
                    AS tecnico

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
        $consulta->execute(["cedula" => $cedula]);

        $usuario = $consulta->fetch(PDO::FETCH_ASSOC);

        $consulta = null;

        return $usuario === false ? null : $usuario;
    }

    /**
     * Da de alta un usuario junto con su rol.
     * Se usa una transacción: o se guardan ambas cosas, o ninguna.
     */
    public function registrar(
        string $cedula,
        string $nombre,
        string $apellido,
        string $claveHash,
        string $rol
    ): bool {
        if (!isset(self::TABLAS_ROL[$rol])) {
            return false;
        }

        $tablaRol = self::TABLAS_ROL[$rol];

        try {
            $this->conexion->beginTransaction();

            $sqlUsuario = "
                INSERT INTO USUARIO
                    (documento_identidad, contrasena, nombre, apellido)
                VALUES
                    (:cedula, :contrasena, :nombre, :apellido)
            ";

            $consultaUsuario = $this->conexion->prepare($sqlUsuario);
            $consultaUsuario->execute([
                "cedula" => $cedula,
                "contrasena" => $claveHash,
                "nombre" => $nombre,
                "apellido" => $apellido
            ]);

            $sqlRol = "
                INSERT INTO {$tablaRol} (documento_identidad)
                VALUES (:cedula)
            ";

            $consultaRol = $this->conexion->prepare($sqlRol);
            $consultaRol->execute(["cedula" => $cedula]);

            $this->conexion->commit();

            return true;
        } catch (PDOException $error) {
            if ($this->conexion->inTransaction()) {
                $this->conexion->rollBack();
            }

            error_log("[UsuarioDAO] registrar: " . $error->getMessage());

            return false;
        }
    }

    /**
     * Actualiza los datos de un usuario y reemplaza su rol.
     */
    public function actualizar(
        string $cedula,
        string $nombre,
        string $apellido,
        string $claveHash,
        string $rol
    ): bool {
        if (!isset(self::TABLAS_ROL[$rol])) {
            return false;
        }

        $tablaRol = self::TABLAS_ROL[$rol];

        try {
            $this->conexion->beginTransaction();

            if (!$this->existe($cedula)) {
                $this->conexion->rollBack();

                return false;
            }

            $sqlUsuario = "
                UPDATE USUARIO
                SET contrasena = :contrasena,
                    nombre = :nombre,
                    apellido = :apellido
                WHERE documento_identidad = :cedula
            ";

            $consultaUsuario = $this->conexion->prepare($sqlUsuario);
            $consultaUsuario->execute([
                "contrasena" => $claveHash,
                "nombre" => $nombre,
                "apellido" => $apellido,
                "cedula" => $cedula
            ]);

            $this->borrarRoles($cedula);

            $sqlRol = "
                INSERT INTO {$tablaRol} (documento_identidad)
                VALUES (:cedula)
            ";

            $consultaRol = $this->conexion->prepare($sqlRol);
            $consultaRol->execute(["cedula" => $cedula]);

            $this->conexion->commit();

            return true;
        } catch (PDOException $error) {
            if ($this->conexion->inTransaction()) {
                $this->conexion->rollBack();
            }

            error_log("[UsuarioDAO] actualizar: " . $error->getMessage());

            return false;
        }
    }

    /**
     * Elimina un usuario junto con sus roles.
     */
    public function eliminar(string $cedula): bool
    {
        try {
            $this->conexion->beginTransaction();

            $this->borrarRoles($cedula);

            $sqlUsuario = "
                DELETE FROM USUARIO
                WHERE documento_identidad = :cedula
            ";

            $consultaUsuario = $this->conexion->prepare($sqlUsuario);
            $consultaUsuario->execute(["cedula" => $cedula]);

            if ($consultaUsuario->rowCount() === 0) {
                $this->conexion->rollBack();

                return false;
            }

            $this->conexion->commit();

            return true;
        } catch (PDOException $error) {
            if ($this->conexion->inTransaction()) {
                $this->conexion->rollBack();
            }

            error_log("[UsuarioDAO] eliminar: " . $error->getMessage());

            return false;
        }
    }

    /**
     * Activa o desactiva una cuenta sin borrar el registro.
     *
     * Se prefiere esto a eliminar: el usuario puede tener tickets,
     * solicitudes o registros asociados que no deben perderse.
     */
    public function cambiarEstado(string $cedula, bool $estado): bool
    {
        $sql = "
            UPDATE USUARIO
            SET estado = :estado
            WHERE documento_identidad = :cedula
        ";

        $consulta = $this->conexion->prepare($sql);
        $consulta->execute([
            "estado" => (int) $estado,
            "cedula" => $cedula
        ]);

        if ($consulta->rowCount() > 0) {
            return true;
        }

        // Si el estado ya era el pedido, rowCount() da 0: hay que
        // comprobar aparte que el usuario exista.
        $consulta = null;

        return $this->existe($cedula);
    }

    /**
     * ¿Existe un usuario con esa cédula?
     */
    public function existe(string $cedula): bool
    {
        $sql = "
            SELECT 1
            FROM USUARIO
            WHERE documento_identidad = :cedula
        ";

        $consulta = $this->conexion->prepare($sql);
        $consulta->execute(["cedula" => $cedula]);

        $existe = $consulta->fetchColumn() !== false;

        $consulta = null;

        return $existe;
    }

    /**
     * Borra el usuario de todas las tablas de roles.
     */
    private function borrarRoles(string $cedula): void
    {
        foreach (array_values(self::TABLAS_ROL) as $tabla) {
            $sql = "
                DELETE FROM {$tabla}
                WHERE documento_identidad = :cedula
            ";

            $consulta = $this->conexion->prepare($sql);
            $consulta->execute(["cedula" => $cedula]);
        }
    }
}
