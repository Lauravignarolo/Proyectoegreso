
const API_ALTA_USUARIO = "../index.php?ruta=altaUsuario";

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

        const respuesta = await fetch(API_ALTA_USUARIO, {
            method: "POST",
            credentials: "same-origin",
            headers: {
                "Content-Type": "application/json",
                "X-CSRF-Token":
                    sessionStorage.getItem("csrfToken") ?? ""
            },
            body: JSON.stringify(datos)
        });

        const json = await respuesta.json();

        if (!respuesta.ok) {
            throw new Error(
                json.mensaje ?? "No se pudo registrar el usuario."
            );
        }

        alert(
            json.datos?.mensaje ??
            "Usuario registrado correctamente."
        );

        formularioRegistroUsuario.reset();

        window.location.href = "Administrador.html";

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
