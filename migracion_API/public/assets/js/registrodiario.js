// Formulario del registro diario
const FormCrearRegistroDiario = document.getElementById("FormCrearRegistroDiario");

// Campos del formulario
const EntradaHoraEntrada = document.getElementById("HoraEntrada");
const EntradaHoraSalida = document.getElementById("HoraSalida");
const EntradaTipoDeSalon = document.getElementById("TipoDeSalon");
const EntradaNumeroDelSalon = document.getElementById("NumeroDelSalon");
const EntradaAsignatura = document.getElementById("Asignatura");
const EntradaGrupo = document.getElementById("Grupo");
const EntradaTurno = document.getElementById("Turno");

// Mensaje de error o aviso
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

/**
 * Arma el cuerpo de la petición con los valores del formulario.
 */
function ObtenerDatosRegistroDiario() {
    return {
        hora_de_entrada: EntradaHoraEntrada.value,
        hora_de_salida: EntradaHoraSalida.value,
        tipo_de_salon: EntradaTipoDeSalon.value,
        numero_de_salon: Number(EntradaNumeroDelSalon.value),
        asignatura: EntradaAsignatura.value.trim(),
        grupo: EntradaGrupo.value.trim(),
        turno: EntradaTurno.value
    };
}

async function CrearRegistroDiario(eventoForm) {
    eventoForm.preventDefault();

    const datos = ObtenerDatosRegistroDiario();

    if (
        !datos.hora_de_entrada ||
        !datos.hora_de_salida ||
        !datos.tipo_de_salon ||
        !Number.isInteger(datos.numero_de_salon) ||
        datos.numero_de_salon <= 0 ||
        !datos.asignatura ||
        !datos.grupo ||
        !datos.turno
    ) {
        MostrarMensaje("Completá todos los campos del registro diario.");
        return;
    }

    // Las horas vienen como "HH:MM", así que se pueden comparar como texto.
    if (datos.hora_de_salida <= datos.hora_de_entrada) {
        MostrarMensaje(
            "La hora de salida debe ser posterior a la hora de entrada."
        );
        EntradaHoraSalida.focus();
        return;
    }

    MostrarMensaje("");

    const botonEnviar = document.getElementById("ButtonEnviar");

    botonEnviar.disabled = true;

    try {
        await peticionAPI(API_REGISTRO_DIARIO, {
            metodo: "POST",
            datos: datos
        });

        LimpiarForm();
        Confirmar();

    } catch (error) {
        console.error("Error al guardar el registro diario:", error);
        MostrarMensaje(error.message);

    } finally {
        botonEnviar.disabled = false;
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
