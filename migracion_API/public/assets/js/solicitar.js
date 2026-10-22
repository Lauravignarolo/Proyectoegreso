
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
            await peticionAPI(API_SOLICITUDES, {
                metodo: "POST",
                datos: {
                    fechaSolicitada,
                    descripcion
                }
            });

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
