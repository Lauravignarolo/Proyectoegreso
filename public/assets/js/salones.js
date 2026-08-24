// Botón para agregar salón
const ButtonAgregarSalon =
    document.getElementById("ButtonAgregarSalon");


// Botón para cerrar
const ButtonCerrarGestionarSalones =
    document.getElementById(
        "ButtonCerrarGestionarSalones"
    );


// Dialog
const DialogGestionarSalones =
    document.querySelector(
        ".DialogGestionarSalones"
    );


// Formulario
const FormGestionarSalones =
    document.getElementById(
        "FormGestionarSalones"
    );


// Abrir dialog
function AbrirAgregarSalon() {

    FormGestionarSalones.reset();

    DialogGestionarSalones.showModal();
}


// Cerrar dialog
function CerrarGestionarSalones() {

    FormGestionarSalones.reset();

    DialogGestionarSalones.close();
}


// Confirmación de eliminación
function confirmarEliminacionSalon(evento) {

    const confirmacion = confirm(
        "¿Está seguro de eliminar este salón?"
    );

    if (!confirmacion) {

        evento.preventDefault();
    }
}


// Antes de enviar el formulario de alta
function gestionarSalon() {

    FormGestionarSalones.action =
        "/public/procesarAltaSalones.php";
}


// Eventos

ButtonAgregarSalon.addEventListener(
    "click",
    AbrirAgregarSalon
);


ButtonCerrarGestionarSalones.addEventListener(
    "click",
    CerrarGestionarSalones
);


DialogGestionarSalones.addEventListener(
    "cancel",
    CerrarGestionarSalones
);


FormGestionarSalones.addEventListener(
    "submit",
    gestionarSalon
);


// Botones eliminar

const FormulariosEliminarSalon =
    document.querySelectorAll(
        ".FormularioEliminarSalon"
    );


for (
    const formulario of FormulariosEliminarSalon
) {

    formulario.addEventListener(
        "submit",
        confirmarEliminacionSalon
    );
}