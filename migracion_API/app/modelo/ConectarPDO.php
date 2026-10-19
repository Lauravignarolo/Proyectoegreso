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
    private ?PDO $conexion = null;

    private static ?self $instancia = null;


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

    
  private function __clone(): void {}

    //Se previene la manipulación del objeto para que no pueda ser deserializado mediante convenciones semi despreciadas
    public function __wakeup(): void
    {
        throw new Exception("No se puede deserializar un Singleton.");
    }

    //Se evita la posibilidad de serializar un objeto
    public function __serialize(): array
    {
        throw new LogicException(
            "El objeto no puede serializarse."
        );
    }

    //Se evita la posibilidad de deserializar un objeto
    public function __unserialize(array $data): void
    {
        throw new LogicException(
            "El objeto no puede deserializarse."
        );
    }

    public static function obtenerInstancia(string $servername, int $port, string $username, string $password, string $dbname): ConectorPDO
    {
        if (self::$instancia === null) {
            self::$instancia = new self ($servername, $port, $username, $password, $dbname);
        }

        return self::$instancia;
    }

    public function establecerConexion(): PDO
    {
        //Si la conexión está funcionando, se la retorna directamente
        if ($this->conexion !== null) {
            return $this->conexion;
        }

        // Siguiendo la documentación y los parámetros definidos por el constructor de PDO, se crea la variable dsn
        // Como buena práctica se define el formato charset=utf8mb4 para evitar problemas con caracteres especiales o letras que ocupen un tamaño de más de un byte, ejemplo #3: https://www.php.net/manual/en/mysqlinfo.concepts.charset.php
        $dsn = "mysql:" . "host={$this->servername};" . "port={$this->port};" . "dbname={$this->dbname};" . "charset=utf8mb4";

        //Se sigue la estructura paramétrica definida por el constructor según la documentación
        $this->conexion = new PDO($dsn, $this->username, $this->password, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);

        return $this->conexion;
    }

    public function desconectar(): void
    {
        $this->conexion = null;
    }
}
