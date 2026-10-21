
const API_TICKETS = "../index.php?ruta=tickets";

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
        const respuesta = await fetch(API_TICKETS, {
            method: "GET",
            credentials: "same-origin"
        });

        const json = await respuesta.json();

        if (!respuesta.ok) {
            throw new Error(
                json.mensaje ?? "No se pudieron cargar los tickets."
            );
        }

        if (!Array.isArray(json.datos)) {
            throw new Error("La API no devolvió una lista de tickets.");
        }

        mostrarTickets(json.datos);

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
        const respuesta = await fetch(API_TICKETS, {
            method: "PATCH",
            credentials: "same-origin",
            headers: {
                "Content-Type": "application/json",
                "X-CSRF-Token":
                    sessionStorage.getItem("csrfToken") ?? ""
            },
            body: JSON.stringify({
                id_ticket: idTicket
            })
        });

        const json = await respuesta.json();

        if (!respuesta.ok) {
            throw new Error(
                json.mensaje ?? "No se pudo reparar el ticket."
            );
        }

        await cargarTickets();

    } catch (error) {
        console.error("Error al reparar ticket:", error);
        alert(error.message);

        boton.disabled = false;
        boton.textContent = "Reparar";
    }
}

document.addEventListener("DOMContentLoaded", cargarTickets);
