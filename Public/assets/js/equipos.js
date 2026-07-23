//Tomar datos para el cuadro de dialog
const ButtonAgregar = document.getElementById("ButtonAgregarPC");
const ButtonCerrarGestionarEquipos = document.getElementById("ButtonCerrarGestionarEquipos");
const DialogGestionarEquipos = document.querySelector(".DialogGestionarEquipos");

//tomar datos de la tabla de equipos
const TbodyEquipos = document.getElementById("TbodyEquipos");

//Tomar datos del formulario gestionar Equipos
const FormGestionarEquipos = document.getElementById("FormGestionarEquipos");

//Tomar datos de los campos del formulario
const EntradaPc = document.getElementById("Pc");
const EntradaSalon = document.getElementById("Salon");
const EntradaEstado = document.getElementById("Estado");
const EntradaUrgencia = document.getElementById("Urgencia");

//Auxiliar para guardar datos vinculados a la modificacion de un equipo
let EquiposEnEdicion = null;


//LocalStorage

function GuardarEquiposLocal() {

    const filas = TbodyEquipos.querySelectorAll("tr");

    const Equipos = [];

    filas.forEach(fila => {

        const Equipo = {
            pc: fila.cells[0].textContent,
            salon: fila.cells[1].textContent,
            estado: fila.cells[2].textContent,
            urgencia: fila.cells[3].textContent
        };

        Equipos.push(Equipo);

    });

    localStorage.setItem("equipos", JSON.stringify(Equipos));
}

function CargarEquiposLocal() {

    const EquiposGuardados = localStorage.getItem("equipos");

    if (EquiposGuardados === null) {
        return [];
    }

    return JSON.parse(EquiposGuardados);
}

function CargarTablaEquipos() {

    const Equipos = CargarEquiposLocal();

    Equipos.forEach(Equipo => {
        AgregarFilaItemEquipos(Equipo);
    });

}

//Gestion del estado del formulario

function RecuperarDatosItemEquipos(filaItemEquipos){
    const pc = filaItemEquipos.cells[0].textContent;
    const salon = filaItemEquipos.cells[1].textContent;
    const estado = filaItemEquipos.cells[2].textContent;
    const urgencia = filaItemEquipos.cells[3].textContent;

    const ItemEquipos = {
        pc: pc,
        salon: salon,
        estado: estado,
        urgencia: urgencia
    }

    return ItemEquipos;
}

function ActualizarItemEquipos(filaItemEquipos, ItemEquipos){
    filaItemEquipos.cells[0].textContent = ItemEquipos.pc;
    filaItemEquipos.cells[1].textContent = ItemEquipos.salon;
    filaItemEquipos.cells[2].textContent = ItemEquipos.estado;
    filaItemEquipos.cells[3].textContent = ItemEquipos.urgencia;
}

function AbrirEditarItemEquipos(ItemEquipos){
    EntradaPc.value = ItemEquipos.pc;
    EntradaSalon.value = ItemEquipos.salon;
    EntradaEstado.value = ItemEquipos.estado;
    EntradaUrgencia.value = ItemEquipos.urgencia;

    EntradaPc.readOnly = true;
    EntradaSalon.readOnly = true;

    DialogGestionarEquipos.showModal();
}

function EditarItemEquipos(EventoEditarIteam) {
    const botonPresionado = EventoEditarIteam.currentTarget;

    const filaItemEquipos = botonPresionado.closest('tr');

    EquiposEnEdicion = filaItemEquipos;

    const ItemEquipos = RecuperarDatosItemEquipos(filaItemEquipos);

    AbrirEditarItemEquipos(ItemEquipos);
}

function EliminarItemEquipos(EventoEliminarIteam) {
    const botonPresionado = EventoEliminarIteam.currentTarget;

    const filaItemEquipos = botonPresionado.closest('tr');

    filaItemEquipos.remove();

    GuardarEquiposLocal();
}

//Limpia todos los campos del formulario
function limpiarEstadoGestionarEquipos() {
    EquiposEnEdicion = null;
    EntradaPc.readOnly = false;
    EntradaSalon.readOnly = false;
    FormGestionarEquipos.reset();
}

function AbrirAgregarEquipos() {
    limpiarEstadoGestionarEquipos();

    //Muestra el diálogo
    DialogGestionarEquipos.showModal();
}

function CerrarGestionarEquipos() {
    limpiarEstadoGestionarEquipos();

    //Cierra el dialog
    DialogGestionarEquipos.close();
}

//Obtener datos del equipo
function ObtenerDatosEquipo() {

    const NuevoItemEquipos = {

        pc: EntradaPc.value,

        salon: EntradaSalon.value,

        estado: EntradaEstado.value,

        urgencia: EntradaUrgencia.value

    };

    return NuevoItemEquipos;
}

//Agregar fila item equipos
function AgregarFilaItemEquipos(ItemEquipos){

    const fila = document.createElement('tr');

    const columnaPc = document.createElement('td');
    columnaPc.textContent = ItemEquipos.pc;


    const columnaSalon = document.createElement('td');
    columnaSalon.textContent = ItemEquipos.salon;


    const columnaEstado = document.createElement('td');
    columnaEstado.textContent = ItemEquipos.estado;


    const columnaUrgencia = document.createElement('td');
    columnaUrgencia.textContent = ItemEquipos.urgencia;


    const columnaAccion = document.createElement('td');
    const DivAccion = document.createElement('div');
    DivAccion.classList.add("DivAccion");

    //Boton Editar

    const botonEditar = document.createElement('button');
    botonEditar.textContent = "Editar";
    botonEditar.type = "button";
    botonEditar.classList.add("ButtonEditar");

    //Evento editar item
    botonEditar.addEventListener("click", EditarItemEquipos);

    //Boton Eliminar

    const botonEliminar = document.createElement('button');
    botonEliminar.textContent = "Eliminar";
    botonEliminar.type = "button";
    botonEliminar.classList.add("ButtonEliminar");

    //Evento eliminar item
    botonEliminar.addEventListener("click", EliminarItemEquipos);

    DivAccion.appendChild(botonEditar);
    DivAccion.appendChild(botonEliminar);

    
    columnaAccion.appendChild(DivAccion);


    fila.appendChild(columnaPc);
    fila.appendChild(columnaSalon);
    fila.appendChild(columnaEstado);
    fila.appendChild(columnaUrgencia);
    fila.appendChild(columnaAccion);

    TbodyEquipos.appendChild(fila);

}

//Ingresar nuevo item al equipo
function IngresarItemEquipos(eventoForm){
    eventoForm.preventDefault();

    const ItemEquipos = ObtenerDatosEquipo();

    if (EquiposEnEdicion === null){
        AgregarFilaItemEquipos(ItemEquipos);
    } else {
        ActualizarItemEquipos(EquiposEnEdicion, ItemEquipos);
    }

    GuardarEquiposLocal();

    CerrarGestionarEquipos();

}



//EVENTOS BOTONES MODAL

FormGestionarEquipos.addEventListener("submit", IngresarItemEquipos);
ButtonAgregar.addEventListener("click", AbrirAgregarEquipos);
ButtonCerrarGestionarEquipos.addEventListener("click", CerrarGestionarEquipos);
DialogGestionarEquipos.addEventListener("cancel", limpiarEstadoGestionarEquipos);

CargarTablaEquipos();