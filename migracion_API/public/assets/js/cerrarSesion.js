
const botonCerrarSesion = document.getElementById("cerrarSesion");

async function cerrarSesion(evento) {
    evento.preventDefault();

    try {
        await peticionAPI(API_LOGOUT, {
            metodo: "POST"
        });

        borrarTokenCsrf();

        window.location.replace("./login.html");

    } catch (error) {
        window.alert(error.message);
    }
}

botonCerrarSesion.addEventListener("click", cerrarSesion);
