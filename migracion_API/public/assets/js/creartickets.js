const RUTA_LOGIN = "login.html";

const FormCrearTickets = document.getElementById("FormCrearTickets");
const MensajeTicket = document.getElementById("MensajeTicket");
const NombreEstudiante = document.getElementById("NombreEstudiante");

// Mostrar un mensaje cuando el nombre sea obligatorio y esté vacío
if (NombreEstudiante) {
    NombreEstudiante.addEventListener("invalid", () => {
        MostrarMensajeTicket("Debe ingresar el nombre del estudiante.");
    });

    NombreEstudiante.addEventListener("input", () => {
        if (NombreEstudiante.value.trim() !== "") {
            MostrarMensajeTicket("");
        }
    });
}

const dialogConfirmacion = document.getElementById("dialogConfirmacion");
const cerrarConfirmacion = document.getElementById("cerrarConfirmacion");

function MostrarMensajeTicket(texto) {
    if (MensajeTicket) {
        MensajeTicket.textContent = texto;
    }
}

async function CrearTicket(eventoForm) {
    eventoForm.preventDefault();

    // Validar el nombre del estudiante
    if (!NombreEstudiante.value.trim()) {
        MostrarMensajeTicket("Debe ingresar el nombre del estudiante.");

        NombreEstudiante.focus();
        return;
    }

    // Si el campo es válido, continuar con el registro
    MostrarMensajeTicket("");

    const datos = Object.fromEntries(new FormData(FormCrearTickets));

    try {
        await peticionAPI(API_TICKETS, {
            metodo: "POST",
            datos: datos
        });

        // El ticket se registró correctamente
        FormCrearTickets.reset();

        // Mostrar diálogo de confirmación
        if (dialogConfirmacion) {
            dialogConfirmacion.showModal();
        } else {
            MostrarMensajeTicket("Ticket registrado correctamente.");
        }

    } catch (error) {
        console.error("Error al registrar ticket:", error);

        if (error.message.includes("sesión")) {
            window.location.href = RUTA_LOGIN;
            return;
        }

        MostrarMensajeTicket(error.message);
    }
}

// Cerrar diálogo
if (cerrarConfirmacion && dialogConfirmacion) {
    cerrarConfirmacion.addEventListener("click", () => {
        dialogConfirmacion.close();
    });
}

// Enviar formulario
if (FormCrearTickets) {
    FormCrearTickets.addEventListener("submit", CrearTicket);
}
