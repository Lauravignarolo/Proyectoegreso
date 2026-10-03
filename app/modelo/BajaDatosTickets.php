<?php

class BajaDatosTickets
{
    private PDO $conexion;

    public function __construct(PDO $conexion)
    {
        $this->conexion = $conexion;
    }

    public function eliminarTicket(
        int $idTicket
    ): bool {

        try {

            $sql = "
                DELETE FROM TICKET
                WHERE id_ticket = :id_ticket
            ";

            $consulta = $this->conexion->prepare($sql);

            $consulta->execute([
                "id_ticket" => $idTicket
            ]);

            return true;

        } catch (PDOException $error) {

            return false;
        }
    }
}
?>