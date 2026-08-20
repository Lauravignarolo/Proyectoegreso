<?php

class AltaDatosUsuarios
{
    private PDO $conexion;

    public function __construct(PDO $conexion)
    {
        $this->conexion = $conexion;
    }

    public function registrarUsuario(
        string $cedula,
        string $nombre,
        string $apellido,
        string $claveHash,
        string $rol
    ): bool {

        try {

            // Inicia la transacción
            $this->conexion->beginTransaction();

            // Registrar usuario
            $sqlUsuario = "
                INSERT INTO USUARIO
                (
                    documento_identidad,
                    contrasena,
                    nombre,
                    apellido
                )
                VALUES
                (
                    :documento_identidad,
                    :contrasena,
                    :nombre,
                    :apellido
                )
            ";

            $consultaUsuario = $this->conexion->prepare($sqlUsuario);

            $consultaUsuario->execute([
                "documento_identidad" => $cedula,
                "contrasena" => $claveHash,
                "nombre" => $nombre,
                "apellido" => $apellido
            ]);


            // Registrar el rol correspondiente
            switch ($rol) {

                case "Administrador":

                    $sqlRol = "
                        INSERT INTO ADMINISTRADOR
                        (documento_identidad)
                        VALUES
                        (:documento_identidad)
                    ";

                    break;


                case "Docente":

                    $sqlRol = "
                        INSERT INTO DOCENTE
                        (documento_identidad)
                        VALUES
                        (:documento_identidad)
                    ";

                    break;


                case "Tecnico":

                    $sqlRol = "
                        INSERT INTO TECNICO
                        (documento_identidad)
                        VALUES
                        (:documento_identidad)
                    ";

                    break;


                case "Direccion":

                    $sqlRol = "
                        INSERT INTO DIRECCION
                        (documento_identidad)
                        VALUES
                        (:documento_identidad)
                    ";

                    break;


                default:

                    $this->conexion->rollBack();

                    return false;
            }


            $consultaRol = $this->conexion->prepare($sqlRol);

            $consultaRol->execute([
                "documento_identidad" => $cedula
            ]);


            // Confirma ambas operaciones
            $this->conexion->commit();

            return true;


        } catch (PDOException $error) {

            // Si algo falla, deshace el INSERT del usuario
            // y el INSERT del rol.
            if ($this->conexion->inTransaction()) {
                $this->conexion->rollBack();
            }

            return false;
        }
    }
}