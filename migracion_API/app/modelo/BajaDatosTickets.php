<?php

class BajaDatosTickets
{
    private PDO $conexion;

    public function __construct(PDO $conexion)
    {
        $this->conexion = $conexion;
    }

    public function eliminarTicket(
        int $id_ticket,
        
    ): bool {

        try {

            $sql = "
                DELETE FROM Ticket
                WHERE id_ticket = :id_Ticket
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