
<?php

class ConectorPDO
{
    private ?PDO $conexion = null;

    public function __construct(
        private string $servername,
        private string $username,
        private string $password,
        private string $dbname,
        private int $port = 3306
    ) {}

    public function establecerConexion(): PDO
    {
        if ($this->conexion === null) {
            $dsn = "mysql:host={$this->servername};port={$this->port};dbname={$this->dbname};charset=utf8mb4";

            $this->conexion = new PDO(
                $dsn,
                $this->username,
                $this->password,
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false
                ]
            );
        }

        return $this->conexion;
    }

    public function desconectar(): void
    {
        $this->conexion = null;
    }
}
