
const FormCrearSolicitud =
    document.getElementById("FormCrearSolicitud");

const DialogConfirmacion =
    document.querySelector(".DialogConfirmacion");

const ButtonCerrarConfirmacion =
    document.getElementById("ButtonCerrarConfirmacion");

FormCrearSolicitud.addEventListener(
    "submit",
    async function (evento) {
        evento.preventDefault();

        const botonEnviar =
            document.getElementById("ButtonEnviar");

        const fechaSolicitada =
            document.getElementById("FechaSoliditada").value;

        const descripcion =
            document.getElementById("Descripcion").value.trim();

        botonEnviar.disabled = true;

        try {
            const respuesta = await fetch(
                "../index.php?ruta=solicitudes",
                {
                    method: "POST",
                    credentials: "same-origin",
                    headers: {
                        "Content-Type": "application/json",
                        "X-CSRF-Token":
                            sessionStorage.getItem("csrfToken") ?? ""
                    },
                    body: JSON.stringify({
                        fechaSolicitada,
                        descripcion
                    })
                }
            );

            const json = await respuesta.json();

            if (!respuesta.ok) {
                throw new Error(
                    json.mensaje ??
                    "No se pudo registrar la solicitud"
                );
            }

            FormCrearSolicitud.reset();

            if (DialogConfirmacion) {
                DialogConfirmacion.showModal();
            } else {
                alert("Solicitud registrada correctamente");
            }

        } catch (error) {
            console.error(error);
            alert(error.message);

        } finally {
            botonEnviar.disabled = false;
        }
    }
);

ButtonCerrarConfirmacion?.addEventListener(
    "click",
    function () {
        DialogConfirmacion.close();
    }
);
