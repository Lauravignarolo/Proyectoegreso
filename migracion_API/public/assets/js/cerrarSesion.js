
const botonCerrarSesion = document.getElementById("cerrarSesion");

async function cerrarSesion(evento) {
    evento.preventDefault();

    const token = sessionStorage.getItem("csrfToken");

    try {
        const respuesta = await fetch("../index.php?ruta=logout", {
            method: "POST",
            headers: {
                "X-CSRF-Token": token ?? ""
            },
            credentials: "same-origin"
        });

        const datos = await respuesta.json();

        if (!respuesta.ok) {
            throw new Error(datos.mensaje ?? "No se pudo cerrar sesión.");
        }

        sessionStorage.removeItem("csrfToken");

        window.location.replace("./login.html");

    } catch (error) {
        window.alert(error.message);
    }
}

botonCerrarSesion.addEventListener("click", cerrarSesion);
