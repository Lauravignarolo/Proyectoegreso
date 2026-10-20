// Envía el formulario de "Registrar estado del equipo" a la API.
// Antes el formulario recargaba la página; ahora se envía en segundo plano
// y se muestra el resultado en un mensaje.

const RUTA_API_TICKETS = "../index.php?ruta=tickets";
const RUTA_LOGIN = "login.html";
const FormCrearTickets = document.getElementById("FormCrearTickets");
const MensajeTicket = document.getElementById("MensajeTicket");

function ObtenerTokenCsrf() {
    return sessionStorage.getItem("csrfToken") ?? "";
}

function MostrarMensajeTicket(texto) {
    MensajeTicket.textContent = texto;
}

async function CrearTicket(eventoForm) {
    eventoForm.preventDefault();

    const datos = Object.fromEntries(new FormData(FormCrearTickets));

    try {
        const respuesta = await fetch(RUTA_API_TICKETS, {
            method: "POST",
            credentials: "same-origin",
            headers: {
                "Content-Type": "application/json",
                "X-CSRF-TOKEN": ObtenerTokenCsrf()
            },
            body: JSON.stringify(datos)
        });

        if (respuesta.status === 401) {
            window.location.href = RUTA_LOGIN;
            return;
        }

        const resultado = await respuesta.json();

        if (!respuesta.ok) {
            MostrarMensajeTicket(resultado.mensaje ?? "No se pudo registrar el equipo.");
            return;
        }

        MostrarMensajeTicket(resultado.datos.mensaje);
        FormCrearTickets.reset();

    } catch (error) {
        MostrarMensajeTicket("No se pudo conectar con el servidor.");
    }
}

FormCrearTickets.addEventListener("submit", CrearTicket);