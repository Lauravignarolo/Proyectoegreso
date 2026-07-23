const TbodyEquipos = document.getElementById("TbodyEquipos");

function CargarEquiposLocal() {

    const EquiposGuardados = localStorage.getItem("equipos");

    if (EquiposGuardados === null) {
        return [];
    }

    return JSON.parse(EquiposGuardados);
}

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

    fila.appendChild(columnaPc);
    fila.appendChild(columnaSalon);
    fila.appendChild(columnaEstado);
    fila.appendChild(columnaUrgencia);

    TbodyEquipos.appendChild(fila);
}

function CargarTablaEquipos() {

    const Equipos = CargarEquiposLocal();

    Equipos.forEach(ItemEquipos => {

        AgregarFilaItemEquipos(ItemEquipos);

    });
}

CargarTablaEquipos();