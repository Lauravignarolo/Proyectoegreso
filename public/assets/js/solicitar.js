

//Tomar datos del formulario gestionar Inventario
const FormCrearSolicitud = document.getElementById("FormCrearSolicitud");

//Tomar datos de los campos del formulario
const EntradaFechaSoliditada = document.getElementById("FechaSoliditada");
const EntradaDescripcion = document.getElementById("Descripcion");


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
    FormCrearSolicitud.reset();
}

function GuardarSolicitudLocal() {

    let Solicitudes = JSON.parse(localStorage.getItem("Solicitudes")) || [];

    const Solicitud = {
        fechaSoliditada: EntradaFechaSoliditada.value,
        descripcion: EntradaDescripcion.value
    };

        Solicitudes.push(Solicitud);


    localStorage.setItem("Solicitudes", JSON.stringify(Solicitudes));
};

function ObtenerDatosSolicitud() {


    const NuevoSolicitud = {

        fechaSoliditada: EntradaFechaSoliditada.value,

        descripcion: EntradaDescripcion.value

    };

    return NuevoSolicitud;
}



function CrearSolicitud(eventoForm){
    eventoForm.preventDefault();

    GuardarSolicitudLocal();

    LimpiarForm();

    Confirmar()

}

FormCrearSolicitud.addEventListener("submit", CrearSolicitud);


// Aviso de confirmacion
ButtonCerrarConfirmacion.addEventListener("click", CerrarConfirmar);
DialogConfirmacion.addEventListener("cancel", CerrarConfirmar);