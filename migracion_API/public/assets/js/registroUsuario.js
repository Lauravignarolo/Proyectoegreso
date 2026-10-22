
const formularioRegistroUsuario =
    document.getElementById("formularioRegistroUsuario");

async function registrarUsuario(evento) {

    evento.preventDefault();

    const formulario = new FormData(formularioRegistroUsuario);

    const datos = {
        cedula: formulario.get("cedula")?.trim(),
        nombre: formulario.get("nombre")?.trim(),
        apellido: formulario.get("apellido")?.trim(),
        clave: formulario.get("clave"),
        confirmarClave: formulario.get("confirmarClave"),
        rol: formulario.get("rol")
    };

    if (datos.clave !== datos.confirmarClave) {
        alert("Las contraseñas no coinciden.");
        return;
    }

    const botonEnviar =
        formularioRegistroUsuario.querySelector(
            'button[type="submit"]'
        );

    botonEnviar.disabled = true;

    try {

        await peticionAPI(API_USUARIOS, {
            metodo: "POST",
            datos: datos
        });

        alert("Usuario registrado correctamente.");

        formularioRegistroUsuario.reset();

        window.location.href = "administrador.html";

    } catch (error) {

        alert(error.message);
        console.error("Error al registrar usuario:", error);

    } finally {

        botonEnviar.disabled = false;
    }
}

formularioRegistroUsuario.addEventListener(
    "submit",
    registrarUsuario
);
