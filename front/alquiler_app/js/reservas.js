const tablaReservas = document.getElementById("reservasTb");
const formReserva = document.forms["reservaForm"];
const tituloModal = document.getElementById("modal-titulo");
let clientes = [];
let vehiculos = [];
let reservas = [];
let idEditar = null;
let accionConfirmada = null;

const mostrarReservas = (lista) => {
  const tbody = tablaReservas.tBodies[0];
  tbody.textContent = "";

  if (!lista.length) {
    const tr = document.createElement("tr");
    const td = document.createElement("td");
    td.colSpan = 7;
    td.className = "vacio";
    td.textContent = "No hay reservas registradas.";
    tr.appendChild(td);
    tbody.appendChild(tr);
    return;
  }

  lista.forEach((reserva, indice) => {
    const tr = document.createElement("tr");
    tr.appendChild(crearCelda(indice + 1));
    tr.appendChild(crearCelda(reserva.cliente_nombre));
    tr.appendChild(crearCelda(reserva.vehiculo));
    tr.appendChild(crearCelda(formatearFecha(reserva.fecha_inicio)));
    tr.appendChild(crearCelda(formatearFecha(reserva.fecha_fin)));

    const tdEstado = document.createElement("td");
    tdEstado.appendChild(crearBadge(reserva.estado, `badge-${reserva.estado}`));
    tr.appendChild(tdEstado);

    const tdAcciones = document.createElement("td");
    const acciones = document.createElement("div");
    acciones.className = "acciones";

    if (reserva.estado === "activa") {
      acciones.appendChild(crearBoton("Devolver", "btn-secondary", () => confirmarDevolucion(reserva)));
      acciones.appendChild(crearBoton("Cancelar", "btn-secondary", () => confirmarCancelacion(reserva)));
      acciones.appendChild(crearBoton("Editar", "btn-secondary", () => abrirEditar(reserva)));
    }

    acciones.appendChild(crearBoton("Eliminar", "btn-danger", () => confirmarEliminacion(reserva)));
    tdAcciones.appendChild(acciones);
    tr.appendChild(tdAcciones);

    tbody.appendChild(tr);
  });
};

const llenarSelects = () => {
  const selectClientes = formReserva.cliente_id;
  const selectVehiculos = formReserva.vehiculo_id;

  selectClientes.textContent = "";
  selectClientes.appendChild(crearOpcion("", "Seleccione un cliente"));
  clientes.forEach((cliente) => {
    selectClientes.appendChild(crearOpcion(cliente.id, cliente.nombre));
  });

  selectVehiculos.textContent = "";
  selectVehiculos.appendChild(crearOpcion("", "Seleccione un vehículo"));
  vehiculos.forEach((vehiculo) => {
    selectVehiculos.appendChild(
      crearOpcion(vehiculo.id, `${vehiculo.marca} ${vehiculo.modelo} (${vehiculo.estado})`)
    );
  });
};

const cargarReservas = async () => {
  try {
    const [listaClientes, listaVehiculos, listaReservas] = await Promise.all([
      api.get(`${URL_CLIENTES}/clientes`),
      api.get(`${URL_VEHICULOS}/vehiculos`),
      api.get(`${URL_RESERVAS}/reservas`),
    ]);

    clientes = listaClientes;
    vehiculos = listaVehiculos;
    reservas = listaReservas;

    llenarSelects();
    mostrarReservas(reservas);
  } catch (error) {
    mostrarMensaje(error.message, "error");
    mostrarReservas([]);
  }
};

const abrirNueva = () => {
  idEditar = null;
  formReserva.reset();
  formReserva.fecha_inicio.value = hoyISO();
  formReserva.fecha_fin.value = sumarDiasISO(1);
  tituloModal.textContent = "Nueva reserva";
  abrirModal("modal-reserva");
};

const abrirEditar = (reserva) => {
  idEditar = reserva.id;
  formReserva.cliente_id.value = reserva.cliente_id;
  formReserva.vehiculo_id.value = reserva.vehiculo_id;
  formReserva.fecha_inicio.value = String(reserva.fecha_inicio).substring(0, 10);
  formReserva.fecha_fin.value = String(reserva.fecha_fin).substring(0, 10);
  tituloModal.textContent = "Editar reserva";
  abrirModal("modal-reserva");
};

const abrirConfirmacion = (titulo, texto, textoBoton, claseBoton, accion) => {
  document.getElementById("confirmar-titulo").textContent = titulo;
  document.getElementById("confirmar-texto").textContent = texto;

  const boton = document.getElementById("confirmar-btn");
  boton.textContent = textoBoton;
  boton.className = `btn ${claseBoton}`;

  accionConfirmada = accion;
  abrirModal("modal-confirmar");
};

const confirmarDevolucion = (reserva) => {
  abrirConfirmacion(
    "Registrar devolución",
    `¿Registrar la devolución del vehículo ${reserva.vehiculo} para ${reserva.cliente_nombre}?`,
    "Registrar devolución",
    "btn-primary",
    async () => {
      await api.put(`${URL_RESERVAS}/reservas/${reserva.id}/devolver`, {});
      mostrarMensaje("Devolución registrada correctamente.", "exito");
      await cargarReservas();
    }
  );
};

const confirmarCancelacion = (reserva) => {
  abrirConfirmacion(
    "Cancelar reserva",
    `¿Cancelar la reserva de ${reserva.cliente_nombre} para el vehículo ${reserva.vehiculo}?`,
    "Cancelar reserva",
    "btn-danger",
    async () => {
      await api.put(`${URL_RESERVAS}/reservas/${reserva.id}/cancelar`, {});
      mostrarMensaje("Reserva cancelada correctamente.", "exito");
      await cargarReservas();
    }
  );
};

const confirmarEliminacion = (reserva) => {
  abrirConfirmacion(
    "Eliminar reserva",
    `¿Eliminar definitivamente la reserva de ${reserva.cliente_nombre}? Esta acción no se puede deshacer.`,
    "Eliminar",
    "btn-danger",
    async () => {
      await api.del(`${URL_RESERVAS}/reservas/${reserva.id}`);
      mostrarMensaje("Reserva eliminada correctamente.", "exito");
      await cargarReservas();
    }
  );
};

const guardarReserva = async () => {
  const datos = {
    cliente_id: Number(formReserva.cliente_id.value),
    vehiculo_id: Number(formReserva.vehiculo_id.value),
    fecha_inicio: formReserva.fecha_inicio.value,
    fecha_fin: formReserva.fecha_fin.value,
  };

  if (!datos.cliente_id || !datos.vehiculo_id || !datos.fecha_inicio || !datos.fecha_fin) {
    mostrarMensaje("Seleccione el cliente, el vehículo y ambas fechas.", "error");
    return;
  }

  if (datos.fecha_fin < datos.fecha_inicio) {
    mostrarMensaje("La fecha de fin no puede ser anterior a la fecha de inicio.", "error");
    return;
  }

  try {
    if (idEditar === null) {
      await api.post(`${URL_RESERVAS}/reservas`, datos);
      mostrarMensaje("Reserva creada correctamente.", "exito");
    } else {
      await api.put(`${URL_RESERVAS}/reservas/${idEditar}`, datos);
      mostrarMensaje("Reserva actualizada correctamente.", "exito");
    }

    cerrarModal("modal-reserva");
    await cargarReservas();
  } catch (error) {
    mostrarMensaje(error.message, "error");
  }
};

document.getElementById("confirmar-btn").addEventListener("click", async () => {
  if (!accionConfirmada) {
    return;
  }

  const accion = accionConfirmada;
  accionConfirmada = null;
  cerrarModal("modal-confirmar");

  try {
    await accion();
  } catch (error) {
    mostrarMensaje(error.message, "error");
  }
});

formReserva.addEventListener("submit", (evento) => {
  evento.preventDefault();
  guardarReserva();
});

document.getElementById("nuevoBtn").addEventListener("click", abrirNueva);
document.getElementById("recargarBtn").addEventListener("click", cargarReservas);

cargarReservas();
