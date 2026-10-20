//Tomar tbody de la tabla
const TbodyInventario = document.getElementById("TbodyInventario");

//Cargar inventario desde Local Storage
function CargarInventarioLocal() {

    const InventarioGuardado = localStorage.getItem("inventario");

    if (InventarioGuardado === null) {
        return [];
    }

    return JSON.parse(InventarioGuardado);
}

//Agregar fila a la tabla
function AgregarFilaItemInventario(ItemInventario){

    const fila = document.createElement('tr');

    const columnaComponente = document.createElement('td');
    columnaComponente.textContent = ItemInventario.componente;

    const columnaMarca = document.createElement('td');
    columnaMarca.textContent = ItemInventario.marca;

    const columnaCantidad = document.createElement('td');
    columnaCantidad.textContent = ItemInventario.cantidad;

    fila.appendChild(columnaComponente);
    fila.appendChild(columnaMarca);
    fila.appendChild(columnaCantidad);

    TbodyInventario.appendChild(fila);
}

//Cargar todos los datos en la tabla
function CargarTablaInventario() {

    const Inventario = CargarInventarioLocal();

    Inventario.forEach(ItemInventario => {

        AgregarFilaItemInventario(ItemInventario);

    });
}

//Ejecutar al abrir la página
CargarTablaInventario();