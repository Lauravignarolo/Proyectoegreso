const RUTA_API_REGISTRO_DIARIO = "../index.php?ruta=registroDiario";
const RUTA_LOGIN = "login.html";

// Formulario
const FormCrearRegistroDiario = document.getElementById("FormCrearRegistroDiario");

// Campos
const EntradaHoraEntrada = document.getElementById("HoraEntrada");
const EntradaHoraSalida = document.getElementById("HoraSalida");
const EntradaTipoDeSalon = document.getElementById("TipoDeSalon");
const EntradaNumeroDelSalon = document.getElementById("NumeroDelSalon");
const EntradaAsignatura = document.getElementById("Asignatura");
const EntradaGrupo = document.getElementById("Grupo");
const EntradaTurno = document.getElementById("Turno");

// Mensaje
const MensajeRegistroDiario = document.getElementById("MensajeRegistroDiario");

// Diálogo de confirmación
const ButtonCerrarConfirmacion = document.getElementById("ButtonCerrarConfirmacion");
const DialogConfirmacion = document.querySelector(".DialogConfirmacion");

function MostrarMensaje(texto) {
    if (MensajeRegistroDiario) {
        MensajeRegistroDiario.textContent = texto;
    } else {
        alert(texto);
    }
}

function ObtenerTokenCsrf() {
    return sessionStorage.getItem("csrfToken") ?? "";
}

function Confirmar() {
    if (DialogConfirmacion) {
        DialogConfirmacion.showModal();
    }
}

function CerrarConfirmar() {
    if (DialogConfirmacion) {
        DialogConfirmacion.close();
    }
}

function LimpiarForm() {
    FormCrearRegistroDiario.reset();
}

async function CrearRegistroDiario(eventoForm) {
    eventoForm.preventDefault();

    const entrada = EntradaHoraEntrada.value;
    const salida = EntradaHoraSalida.value;

    // Validación inmediata del horario
    if (entrada && salida && salida <= entrada) {
        MostrarMensaje(
            "La hora de salida debe ser posterior a la hora de entrada."
        );
        EntradaHoraSalida.focus();
        return;
    }

    MostrarMensaje("");

    const datos = {
        hora_de_entrada: entrada,
        hora_de_salida: salida,
        tipo_de_salon: EntradaTipoDeSalon.value,
        numero_de_salon: EntradaNumeroDelSalon.value,
        asignatura: EntradaAsignatura.value.trim(),
        grupo: EntradaGrupo.value.trim(),
        turno: EntradaTurno.value
    };

    try {
        const respuesta = await fetch(RUTA_API_REGISTRO_DIARIO, {
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
            MostrarMensaje(
                resultado.mensaje ?? "No se pudo registrar el registro diario."
            );
            return;
        }

        LimpiarForm();
        Confirmar();

    } catch (error) {
        console.error("Error al registrar el registro diario:", error);
        MostrarMensaje("No se pudo conectar con el servidor.");
    }
}

if (FormCrearRegistroDiario) {
    FormCrearRegistroDiario.addEventListener("submit", CrearRegistroDiario);
}

if (ButtonCerrarConfirmacion) {
    ButtonCerrarConfirmacion.addEventListener("click", CerrarConfirmar);
}

if (DialogConfirmacion) {
    DialogConfirmacion.addEventListener("cancel", CerrarConfirmar);
}