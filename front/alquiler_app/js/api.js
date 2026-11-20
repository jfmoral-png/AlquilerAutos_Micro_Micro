const URL_VEHICULOS = "http://127.0.0.1:8001";
const URL_CLIENTES = "http://127.0.0.1:8002";
const URL_RESERVAS = "http://127.0.0.1:8003";

const api = {
  async request(url, opciones = {}) {
    let respuesta;

    try {
      respuesta = await fetch(url, opciones);
    } catch (error) {
      throw new Error(
        "No se pudo conectar con el servicio. Verifique que los microservicios estén encendidos."
      );
    }

    if (!respuesta.ok) {
      let mensaje = `Error ${respuesta.status} en el servicio`;

      try {
        const datos = await respuesta.json();
        if (datos && datos.error) {
          mensaje = datos.error;
        }
      } catch (error) {
        // El servicio no devolvió JSON.
      }

      throw new Error(mensaje);
    }

    if (respuesta.status === 204) {
      return null;
    }

    try {
      return await respuesta.json();
    } catch (error) {
      return null;
    }
  },

  get(url) {
    return this.request(url);
  },

  post(url, datos) {
    return this.request(url, {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify(datos),
    });
  },

  put(url, datos) {
    return this.request(url, {
      method: "PUT",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify(datos),
    });
  },

  del(url) {
    return this.request(url, { method: "DELETE" });
  },
};

let temporizadorMensaje = null;

const mostrarMensaje = (texto, tipo = "error") => {
  const contenedor = document.getElementById("mensaje");

  if (!contenedor) {
    return;
  }

  contenedor.textContent = texto;
  contenedor.className = `mensaje ${tipo}`;

  clearTimeout(temporizadorMensaje);
  temporizadorMensaje = setTimeout(() => {
    contenedor.className = "mensaje";
  }, 6000);
};

const formatearFecha = (fecha) => {
  if (!fecha) {
    return "";
  }

  const partes = String(fecha).substring(0, 10).split("-");

  if (partes.length !== 3) {
    return String(fecha);
  }

  return `${partes[2]}/${partes[1]}/${partes[0]}`;
};

const hoyISO = () => {
  const hoy = new Date();
  const mes = String(hoy.getMonth() + 1).padStart(2, "0");
  const dia = String(hoy.getDate()).padStart(2, "0");

  return `${hoy.getFullYear()}-${mes}-${dia}`;
};

const sumarDiasISO = (dias) => {
  const fecha = new Date();
  fecha.setDate(fecha.getDate() + dias);
  const mes = String(fecha.getMonth() + 1).padStart(2, "0");
  const dia = String(fecha.getDate()).padStart(2, "0");

  return `${fecha.getFullYear()}-${mes}-${dia}`;
};

const crearCelda = (texto) => {
  const td = document.createElement("td");
  td.textContent = texto;
  return td;
};

const crearBadge = (texto, clase) => {
  const span = document.createElement("span");
  span.className = `badge ${clase}`;
  span.textContent = texto;
  return span;
};

const crearBoton = (texto, clase, alHacerClic) => {
  const boton = document.createElement("button");
  boton.type = "button";
  boton.className = `btn btn-small ${clase}`;
  boton.textContent = texto;
  boton.addEventListener("click", alHacerClic);
  return boton;
};

const crearOpcion = (valor, texto) => {
  const opcion = document.createElement("option");
  opcion.value = valor;
  opcion.textContent = texto;
  return opcion;
};

const abrirModal = (id) => {
  const modal = document.getElementById(id);

  if (modal) {
    modal.classList.add("open");
  }
};

const cerrarModal = (id) => {
  const modal = document.getElementById(id);

  if (modal) {
    modal.classList.remove("open");
  }
};

const cerrarModales = () => {
  document.querySelectorAll(".modal.open").forEach((modal) => {
    modal.classList.remove("open");
  });
};

document.querySelectorAll("[data-close]").forEach((boton) => {
  boton.addEventListener("click", () => cerrarModal(boton.dataset.close));
});

document.querySelectorAll(".modal").forEach((modal) => {
  modal.addEventListener("click", (evento) => {
    if (evento.target === modal) {
      modal.classList.remove("open");
    }
  });
});

document.addEventListener("keydown", (evento) => {
  if (evento.key === "Escape") {
    cerrarModales();
  }
});
