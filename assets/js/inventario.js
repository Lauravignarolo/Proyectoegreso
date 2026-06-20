//Tomar datos para el cuadro de dialog
const ButtonAgregar = document.getElementById("ButtonAgregarComponente");
const ButtonCerrarGestionarInventario = document.getElementById("ButtonCerrarGestionarInventario");
const DialogGestionarInventario = document.querySelector(".DialogGestionarInventario");

//tomar datos de la tabla del inventario
const TbodyInventario = document.getElementById("TbodyInventario");

//Tomar datos del formulario gestionar Inventario
const FormGestionarInventario = document.getElementById("FormGestionarInventario");

//Tomar datos de los campos del formulario
const EntradaComponente = document.getElementById("Componente");
const EntradaMarca = document.getElementById("Marca");
const EntradaCantidad = document.getElementById("Cantidad");

//Auxiliar para guardar datos vinculados a la modificacion de un componente
let InventarioEnEdicion = null;

//Gestion del estado del formulario

function RecuperarDatosItemInventario(filaItemInventario){
    const componente = filaItemInventario.cells[0].textContent;
    const marca = filaItemInventario.cells[1].textContent;
    const cantidad = filaItemInventario.cells[2].textContent;

    const ItemInventario = {
        componente: componente,
        marca: marca,
        cantidad: cantidad
    }

    return ItemInventario;
}

function ActualizarItemInventario(filaItemInventario, ItemInventario){
    filaItemInventario.cells[0].textContent = ItemInventario.componente;
    filaItemInventario.cells[1].textContent = ItemInventario.marca;
    filaItemInventario.cells[2].textContent = ItemInventario.cantidad;
}

function AbrirEditarIteamInventario(ItemInventario){
    EntradaComponente.value = ItemInventario.componente;
    EntradaMarca.value = ItemInventario.marca;
    EntradaCantidad.value = ItemInventario.cantidad;

    EntradaComponente.readOnly = true;
    EntradaMarca.readOnly =true;

    DialogGestionarInventario.showModal();
}

function EditarItemInventario(EventoEditarIteam) {
    const botonPresionado = EventoEditarIteam.currentTarget;

    const filaItemInventario = botonPresionado.closest('tr');

    InventarioEnEdicion = filaItemInventario;

    const ItemInventario = RecuperarDatosItemInventario(filaItemInventario);

    AbrirEditarIteamInventario(ItemInventario);
}

function EliminarItemInventario(EventoEliminarIteam) {
    const botonPresionado = EventoEliminarIteam.currentTarget;

    const filaItemInventario = botonPresionado.closest('tr');

    filaItemInventario.remove();
}
//
//Limpia todos los campos del formulario
function limpiarEstadoGestionarInventario() {
    InventarioEnEdicion = null;
    EntradaComponente.readOnly = false;
    EntradaMarca.readOnly = false;
    FormGestionarInventario.reset();
}


function AbrirAgregarInventario() {
    limpiarEstadoGestionarInventario();

    //Muestra el diálogo 
    DialogGestionarInventario.showModal();
}


function CerrarGestionarInventario() {
    limpiarEstadoGestionarInventario();

    //Cierra el dialog
    DialogGestionarInventario.close();
}


//Obtener datos del componente
function ObtenerDatosComponente() {


    const NuevoItemInventario = {

        componente: EntradaComponente.value,

        marca: EntradaMarca.value,

        cantidad: EntradaCantidad.value

    };

    return NuevoItemInventario;
}
//Agregar fila iteam inventario 
function AgregarFilaItemInventario(ItemInventario){

    const fila = document.createElement('tr');

    const columnaComponente = document.createElement('td');
    columnaComponente.textContent = ItemInventario.componente;


    const columnaMarca = document.createElement('td');
    columnaMarca.textContent = ItemInventario.marca;


    const columnaCantidad = document.createElement('td');
    columnaCantidad.textContent = ItemInventario.cantidad;


    const columnaAccion = document.createElement('td');
    const DivAccion = document.createElement('div');
    DivAccion.classList.add("DivAccion");

    //Boton Editar

    const botonEditar = document.createElement('button');
    botonEditar.textContent = "Editar";
    botonEditar.type = "button";
    botonEditar.classList.add("ButtonEditar");

    //Evento editar iteam
    botonEditar.addEventListener("click", EditarItemInventario);

    //Boton Eliminar

    const botonEliminar = document.createElement('button');
    botonEliminar.textContent = "Eliminar";
    botonEliminar.type = "button";
    botonEliminar.classList.add("ButtonEliminar");

    //Evento eliminar item
    botonEliminar.addEventListener("click", EliminarItemInventario);

    DivAccion.appendChild(botonEditar);
    DivAccion.appendChild(botonEliminar);

    
    columnaAccion.appendChild(DivAccion);


    fila.appendChild(columnaComponente);
    fila.appendChild(columnaMarca);
    fila.appendChild(columnaCantidad);
    fila.appendChild(columnaAccion);

    TbodyInventario.appendChild(fila);

}
//Ingresar nuevo item al inventario
function IngresarItemInventario(eventoForm){
    eventoForm.preventDefault();

    const ItemInventario = ObtenerDatosComponente();

    if (InventarioEnEdicion === null){
        AgregarFilaItemInventario(ItemInventario);
    } else {
        ActualizarItemInventario(InventarioEnEdicion, ItemInventario);
    }

    CerrarGestionarInventario();

}



//EVENTOS BOTONES MODAL

FormGestionarInventario.addEventListener("submit", IngresarItemInventario);
ButtonAgregar.addEventListener("click", AbrirAgregarInventario);
ButtonCerrarGestionarInventario.addEventListener("click", CerrarGestionarInventario);
DialogGestionarInventario.addEventListener("cancel", limpiarEstadoGestionarInventario);
