// Pide los tickets a la API y dibuja la tabla.
// Antes la tabla la armaba PHP; ahora PHP solo entrega los datos (JSON)
// y este archivo los dibuja.

const RUTA_API_TICKETS = "../index.php?ruta=tickets";
const RUTA_LOGIN = "login.html";
const TbodyTickets = document.querySelector(".TbodyTickets");
const COLUMNAS_TICKETS = 12;

function MostrarMensajeTickets(texto) {
    TbodyTickets.textContent = "";

    const fila = document.createElement("tr");
    const columna = document.createElement("td");
    columna.colSpan = COLUMNAS_TICKETS;
    columna.textContent = texto;

    fila.appendChild(columna);
    TbodyTickets.appendChild(fila);
}

function AgregarFilaTicket(Ticket) {
    const fila = document.createElement("tr");

    const docente = Ticket.nombre ? Ticket.nombre + " " + Ticket.apellido : "Sin registrar";

    const valores = [
        Ticket.estudiante_a_cargo,
        docente,
        Ticket.hora_de_entrada,
        Ticket.hora_de_salida,
        Ticket.tipo_de_salon,
        Ticket.numero_de_salon,
        Ticket.numero_de_equipo,
        Ticket.asignatura,
        Ticket.grupo,
        Ticket.turno,
        Ticket.estado,
        Ticket.urgencia ?? "Sin especificar"
    ];

    valores.forEach(valor => {
        const columna = document.createElement("td");
        columna.textContent = valor ?? "";
        fila.appendChild(columna);
    });

    TbodyTickets.appendChild(fila);
}

async function CargarTickets() {

    try {
        const respuesta = await fetch(RUTA_API_TICKETS, {
            method: "GET",
            credentials: "same-origin"
        });

        if (respuesta.status === 401) {
            window.location.href = RUTA_LOGIN;
            return;
        }

        const resultado = await respuesta.json();

        if (!respuesta.ok) {
            MostrarMensajeTickets(resultado.mensaje ?? "No se pudieron cargar los tickets.");
            return;
        }

        if (resultado.datos.length === 0) {
            MostrarMensajeTickets("No hay tickets registrados.");
            return;
        }

        TbodyTickets.textContent = "";
        resultado.datos.forEach(AgregarFilaTicket);

    } catch (error) {
        MostrarMensajeTickets("No se pudo conectar con el servidor.");
    }
}

CargarTickets();