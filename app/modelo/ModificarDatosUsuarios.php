<?php


/**
 * Clase encargada de modificar los datos
 * relacionados con usuarios del sistema.
 */
class ModificarDatosUsuario
{
    private PDO $conexion;


    /**
     * Constructor parametrizado que recibe una conexión
     * a la base de datos.
     *
     * @param PDO $conexion Conexión a la base de datos. PRECONDICIÓN: No debe ser NULL.
     */
    public function __construct(PDO $conexion)
    {
        $this->conexion = $conexion;
    }


    /**
     * Modifica los datos de un usuario y su rol.
     *
     * @param string $cedula Cédula del usuario a modificar.
     * @param string $nombre Nuevo nombre.
     * @param string $apellido Nuevo apellido.
     * @param string $claveHash Nuevo hash de contraseña.
     * @param string $rol Rol seleccionado.
     *
     * @return bool TRUE si la modificación se realiza correctamente, FALSE en caso contrario.
     */
    public function modificarUsuario(string $cedula, string $nombre, string $apellido, string $claveHash, string $rol): bool {
        try {
            $this->conexion->beginTransaction();

            $sqlUsuario = "UPDATE USUARIO SET nombre = :nombre, apellido = :apellido, claveHash = :claveHash WHERE cedula = :cedula";
            $consultaUsuario = $this->conexion->prepare($sqlUsuario);
            $consultaUsuario->execute(["nombre" => $nombre, "apellido" => $apellido, "claveHash" => $claveHash, "cedula" => $cedula]);

            switch ($rol) {
                case "Administrador":
                    //Si anteriormente era Logística, primero se eliminan sus cargos porque CARGO depende de LOGISTICA
                    $sqlCargo = "DELETE FROM CARGO WHERE cedula = :cedula";
                    $consultaCargo = $this->conexion->prepare($sqlCargo);
                    $consultaCargo->execute(["cedula" => $cedula]);

                    //Elimina el posible rol Logística
                    $sqlLogistica = "DELETE FROM LOGISTICA WHERE cedula = :cedula";
                    $consultaLogistica = $this->conexion->prepare($sqlLogistica);
                    $consultaLogistica->execute(["cedula" => $cedula]);

                    //Comprueba si ya es Administrador
                    $sqlBuscarAdministrador = "SELECT cedula FROM ADMINISTRADOR WHERE cedula = :cedula";
                    $consultaBuscarAdministrador = $this->conexion->prepare($sqlBuscarAdministrador);
                    $consultaBuscarAdministrador->execute(["cedula" => $cedula]);
                    $administrador = $consultaBuscarAdministrador->fetch(PDO::FETCH_ASSOC);

                    //Si todavía no era Administrador, registra el nuevo rol
                    if ($administrador === false) {
                        $sqlAdministrador = " INSERT INTO ADMINISTRADOR (cedula) VALUES (:cedula)";
                        $consultaAdministrador = $this->conexion->prepare($sqlAdministrador);
                        $consultaAdministrador->execute(["cedula" => $cedula]);
                    }
                    break;
                case "Logística":
                    //Elimina el posible rol Administrador
                    $sqlAdministrador = "DELETE FROM ADMINISTRADOR WHERE cedula = :cedula";
                    $consultaAdministrador = $this->conexion->prepare($sqlAdministrador);
                    $consultaAdministrador->execute(["cedula" => $cedula]);

                    //Comprueba si ya pertenece a Logística
                    $sqlBuscarLogistica = "SELECT cedula FROM LOGISTICA WHERE cedula = :cedula";
                    $consultaBuscarLogistica = $this->conexion->prepare($sqlBuscarLogistica);
                    $consultaBuscarLogistica->execute(["cedula" => $cedula]);
                    $logistica = $consultaBuscarLogistica->fetch(PDO::FETCH_ASSOC);

                    //Si todavía no era Logística, registra el nuevo rol
                    if ($logistica === false) {
                        $sqlLogistica = "INSERT INTO LOGISTICA (cedula) VALUES (:cedula)";
                        $consultaLogistica = $this->conexion->prepare($sqlLogistica);
                        $consultaLogistica->execute(["cedula" => $cedula]);
                    }

                    break;
                default:
                    $this->conexion->rollBack();
                    return false;
            }

            $this->conexion->commit();
            return true;

        } catch (PDOException $error) {
            if ($this->conexion->inTransaction()) {
                $this->conexion->rollBack();
            }

            return false;
        }
    }
}