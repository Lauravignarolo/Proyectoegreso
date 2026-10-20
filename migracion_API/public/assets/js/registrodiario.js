
//Tomar datos del formulario gestionar Inventario
const FormCrearRegistroDiario = document.getElementById("FormCrearRegistroDiario");

//Tomar datos de los campos del formulario
const EntradaHoraEntrada = document.getElementById("HoraEntrada");
const EntradaHoraSalida = document.getElementById("HoraSalida");
const EntradaTipoDeSalon = document.getElementById("TipoDeSalon");
const EntradaNumeroDelSalon = document.getElementById("NumeroDelSalon");
const EntradaAsignatura = document.getElementById("Asignatura");
const EntradaGrupo = document.getElementById("Grupo");
const EntradaTurno = document.getElementById("Turno");

//Dialog de confirmacion

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

function GuardarRegistroDiarioLocal() {

    let RegistrosDiarios = JSON.parse(localStorage.getItem("RegistrosDiarios")) || [];

    const RegistroDiario = {
        horaEntrada: EntradaHoraEntrada.value,
        horaSalida: EntradaHoraSalida.value,
        tipoDeSalon: EntradaTipoDeSalon.value,
        numeroDelSalon: EntradaNumeroDelSalon.value,
        asignatura: EntradaAsignatura.value,
        grupo: EntradaGrupo.value,
        turno: EntradaTurno.value,
    };

        RegistrosDiarios.push(RegistroDiario);


    localStorage.setItem("RegistrosDiarios", JSON.stringify(RegistrosDiarios));
};

function ObtenerDatosRegistroDiario() {


    const NuevoRegistroDiario = {


        horaEntrada: EntradaHoraEntrada.value,

        horaSalida: EntradaHoraSalida.value,

        tipoDeSalon: EntradaTipoDeSalon.value,

        numeroDelSalon: EntradaNumeroDelSalon.value,

        asignatura: EntradaAsignatura.value,

        grupo: EntradaGrupo.value,

        turno: EntradaTurno.value


    };

    return NuevoRegistroDiario;
}



function CrearRegistroDiario(eventoForm){
    eventoForm.preventDefault();

    GuardarRegistroDiarioLocal();

    LimpiarForm();

    Confirmar()

}

FormCrearRegistroDiario.addEventListener("submit", CrearRegistroDiario);

// Aviso de confirmacion
ButtonCerrarConfirmacion.addEventListener("click", CerrarConfirmar);
DialogConfirmacion.addEventListener("cancel", CerrarConfirmar);