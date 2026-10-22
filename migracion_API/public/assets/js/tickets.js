
const tablaTickets = document.getElementById("TbodyTickets");
const mensajeTickets = document.getElementById("mensajeTickets");

function mostrarMensaje(texto) {
    tablaTickets.replaceChildren();

    if (mensajeTickets) {
        mensajeTickets.textContent = texto;
    }
}

function mostrarTickets(tickets) {
    tablaTickets.replaceChildren();

    if (tickets.length === 0) {
        mostrarMensaje("No hay tickets pendientes.");
        return;
    }

    if (mensajeTickets) {
        mensajeTickets.textContent = "";
    }

    tickets.forEach((ticket) => {
        const fila = document.createElement("tr");

        const docente = [
            ticket.nombre,
            ticket.apellido
        ].filter(Boolean).join(" ") || "Sin registrar";

        const columnas = [
            ticket.id_ticket,
            ticket.estudiante_a_cargo,
            docente,
            ticket.hora_de_entrada,
            ticket.hora_de_salida,
            ticket.tipo_de_salon,
            ticket.numero_de_salon,
            ticket.numero_de_equipo,
            ticket.asignatura,
            ticket.grupo,
            ticket.turno,
            ticket.estado
        ];

        columnas.forEach((valor) => {
            const celda = document.createElement("td");
            celda.textContent = valor ?? "";
            fila.appendChild(celda);
        });

        const celdaAccion = document.createElement("td");

        const botonReparar = document.createElement("button");
        botonReparar.type = "button";
        botonReparar.className = "ButtonReparar";
        botonReparar.textContent = "Reparar";

        botonReparar.addEventListener("click", async () => {
            await repararTicket(ticket.id_ticket, botonReparar);
        });

        celdaAccion.appendChild(botonReparar);
        fila.appendChild(celdaAccion);

        tablaTickets.appendChild(fila);
    });
}

async function cargarTickets() {
    mostrarMensaje("Cargando tickets...");

    try {
        const tickets = await peticionAPI(API_TICKETS, {
            metodo: "GET"
        });

        if (!Array.isArray(tickets)) {
            throw new Error("La API no devolvió una lista de tickets.");
        }

        mostrarTickets(tickets);

    } catch (error) {
        console.error("Error al cargar tickets:", error);
        mostrarMensaje(error.message);
    }
}

async function repararTicket(idTicket, boton) {
    const confirmar = confirm(
        `¿Querés marcar el ticket ${idTicket} como reparado?`
    );

    if (!confirmar) {
        return;
    }

    boton.disabled = true;
    boton.textContent = "Reparando...";

    try {
        await peticionAPI(API_TICKETS, {
            metodo: "PATCH",
            datos: {
                id_ticket: idTicket
            }
        });

        await cargarTickets();

    } catch (error) {
        console.error("Error al reparar ticket:", error);
        alert(error.message);

        boton.disabled = false;
        boton.textContent = "Reparar";
    }
}

document.addEventListener("DOMContentLoaded", cargarTickets);
