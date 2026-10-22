
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

// Dialog de confirmacion
const ButtonCerrarConfirmacion = document.getElementById("ButtonCerrarConfirmacion");
const DialogConfirmacion = document.querySelector(".DialogConfirmacion");

//Confirmar
function Confirmar(){

    DialogConfirmacion.showModal();

}
function CerrarConfirmar(){

    DialogConfirmacion.close();

}

function LimpiarForm(){
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

async function CrearRegistroDiario(eventoForm){
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
        alert("Completá todos los campos del registro diario.");
        return;
    }

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
        alert(error.message);

    } finally {
        botonEnviar.disabled = false;
    }
}

FormCrearRegistroDiario.addEventListener("submit", CrearRegistroDiario);

// Aviso de confirmacion
ButtonCerrarConfirmacion.addEventListener("click", CerrarConfirmar);
DialogConfirmacion.addEventListener("cancel", CerrarConfirmar);
