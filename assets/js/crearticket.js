

//Tomar datos del formulario gestionar Inventario
const FormCrearTickets = document.getElementById("FormCrearTickets");

//Tomar datos de los campos del formulario
const EntradaNombreEstudiante = document.getElementById("NombreEstudiante");
const EntradaHoraEntrada = document.getElementById("HoraEntrada");
const EntradaHoraSalida = document.getElementById("HoraSalida");
const EntradaTipoDeSalon = document.getElementById("TipoDeSalon");
const EntradaNumeroDelSalon = document.getElementById("NumeroDelSalon");
const EntradaNumeroDeEquipo = document.getElementById("NumeroDeEquipo");
const EntradaAsignatura = document.getElementById("Asignatura");
const EntradaGrupo = document.getElementById("Grupo");
const EntradaTurno = document.getElementById("Turno");
const EntradaEstadoDelEquipo = document.getElementById("EstadoDelEquipo");

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
    FormCrearTickets.reset();
}

function GuardarTicketLocal() {

    let Tickets = JSON.parse(localStorage.getItem("Tickets")) || [];

    const Ticket = {
        nombreEstudiante: EntradaNombreEstudiante.value,
        horaEntrada: EntradaHoraEntrada.value,
        horaSalida: EntradaHoraSalida.value,
        tipoDeSalon: EntradaTipoDeSalon.value,
        numeroDelSalon: EntradaNumeroDelSalon.value,
        numeroDeEquipo: EntradaNumeroDeEquipo.value,
        asignatura: EntradaAsignatura.value,
        grupo: EntradaGrupo.value,
        turno: EntradaTurno.value,
        estadoDelEquipo: EntradaEstadoDelEquipo.value,
    };

        Tickets.push(Ticket);


    localStorage.setItem("Tickets", JSON.stringify(Tickets));
};

function ObtenerDatosTicket() {


    const NuevoTicket = {

        nombreEstudiante: EntradaNombreEstudiante.value,

        horaEntrada: EntradaHoraEntrada.value,

        horaSalida: EntradaHoraSalida.value,

        tipoDeSalon: EntradaTipoDeSalon.value,

        numeroDelSalon: EntradaNumeroDelSalon.value,

        numeroDeEquipo: EntradaNumeroDeEquipo.value,

        asignatura: EntradaAsignatura.value,

        grupo: EntradaGrupo.value,

        turno: EntradaTurno.value,

        estadoDelEquipo: EntradaEstadoDelEquipo.value

    };

    return NuevoTicket;
}



function CrearTicket(eventoForm){
    eventoForm.preventDefault();

    GuardarTicketLocal();

    LimpiarForm();

    Confirmar();

}

FormCrearTickets.addEventListener("submit", CrearTicket);

// Aviso de confirmacion
ButtonCerrarConfirmacion.addEventListener("click", CerrarConfirmar);
DialogConfirmacion.addEventListener("cancel", CerrarConfirmar);