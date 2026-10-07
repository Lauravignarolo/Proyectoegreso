<?php

/**
 * Clase encargada de establecer y administrar la conexion PDO con la base de datos.
 * Centraliza los datos de conexion para que no se repitan en otras clases.
 */
class ConectorPDO
{
    private string $servername;
    private string $username;
    private string $password;
    private string $dbname;
    private ?PDO $conexion;

    /**
     * Constructor parametrizado con los datos necesarios para conectar a la base de datos.
     * @param string $servername Servidor donde corre la base de datos.
     * @param string $username Usuario de la base de datos.
     * @param string $password Contrasena del usuario de la base de datos.
     * @param string $dbname Nombre de la base de datos a utilizar.
     */
    public function __construct(
        string $servername,
        string $username,
        string $password,
        string $dbname
    ) {
        $this->servername = $servername;
        $this->username = $username;
        $this->password = $password;
        $this->dbname = $dbname;
        $this->conexion = null;
    }

    /**
     * Abre la conexion PDO con la base de datos configurada.
     * @return PDO|null La conexion establecida, o null si fallo la conexion.
     */
    public function establecerConexion(): ?PDO
    {
        try {

            $this->conexion = new PDO(
                "mysql:host={$this->servername};port=3306;dbname={$this->dbname};charset=utf8mb4",
                $this->username,
                $this->password
            );

            $this->conexion->setAttribute(
                PDO::ATTR_ERRMODE,
                PDO::ERRMODE_EXCEPTION
            );

            return $this->conexion;

        } catch (PDOException $e) {

            echo "ERROR DE CONEXIÓN: " . $e->getMessage();

            return null;
        }
    }

    /**
     * Cierra la conexion con la base de datos.
     */
    public function desconectar(): void
    {
        $this->conexion = null;
    }
}

?>