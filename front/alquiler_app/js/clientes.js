const tablaClientes = document.getElementById("clientesTb");
const formCliente = document.forms["clienteForm"];
const tituloModal = document.getElementById("modal-titulo");
let idEditar = null;
let idBorrar = null;

const mostrarClientes = (clientes) => {
  const tbody = tablaClientes.tBodies[0];
  tbody.textContent = "";

  if (!clientes.length) {
    const tr = document.createElement("tr");
    const td = document.createElement("td");
    td.colSpan = 6;
    td.className = "vacio";
    td.textContent = "No hay clientes registrados.";
    tr.appendChild(td);
    tbody.appendChild(tr);
    return;
  }

  clientes.forEach((cliente, indice) => {
    const tr = document.createElement("tr");
    tr.appendChild(crearCelda(indice + 1));
    tr.appendChild(crearCelda(cliente.nombre));
    tr.appendChild(crearCelda(cliente.telefono || "—"));
    tr.appendChild(crearCelda(cliente.correo || "—"));
    tr.appendChild(crearCelda(cliente.numero_licencia || "—"));

    const tdAcciones = document.createElement("td");
    const acciones = document.createElement("div");
    acciones.className = "acciones";
    acciones.appendChild(crearBoton("Editar", "btn-secondary", () => abrirEditar(cliente)));
    acciones.appendChild(crearBoton("Eliminar", "btn-danger", () => abrirBorrar(cliente)));
    tdAcciones.appendChild(acciones);
    tr.appendChild(tdAcciones);

    tbody.appendChild(tr);
  });
};

const cargarClientes = async () => {
  try {
    const clientes = await api.get(`${URL_CLIENTES}/clientes`);
    mostrarClientes(clientes);
  } catch (error) {
    mostrarMensaje(error.message, "error");
    mostrarClientes([]);
  }
};

const abrirNuevo = () => {
  idEditar = null;
  formCliente.reset();
  tituloModal.textContent = "Registrar cliente";
  abrirModal("modal-cliente");
};

const abrirEditar = (cliente) => {
  idEditar = cliente.id;
  formCliente.nombre.value = cliente.nombre;
  formCliente.telefono.value = cliente.telefono || "";
  formCliente.correo.value = cliente.correo || "";
  formCliente.numero_licencia.value = cliente.numero_licencia || "";
  tituloModal.textContent = "Editar cliente";
  abrirModal("modal-cliente");
};

const abrirBorrar = (cliente) => {
  idBorrar = cliente.id;
  document.getElementById("borrar-nombre").textContent = cliente.nombre;
  abrirModal("modal-borrar");
};

const guardarCliente = async () => {
  const datos = {
    nombre: formCliente.nombre.value.trim(),
    telefono: formCliente.telefono.value.trim(),
    correo: formCliente.correo.value.trim(),
    numero_licencia: formCliente.numero_licencia.value.trim(),
  };

  if (!datos.nombre) {
    mostrarMensaje("El nombre del cliente es obligatorio.", "error");
    return;
  }

  try {
    if (idEditar === null) {
      await api.post(`${URL_CLIENTES}/clientes`, datos);
      mostrarMensaje("Cliente registrado correctamente.", "exito");
    } else {
      await api.put(`${URL_CLIENTES}/clientes/${idEditar}`, datos);
      mostrarMensaje("Cliente actualizado correctamente.", "exito");
    }

    cerrarModal("modal-cliente");
    await cargarClientes();
  } catch (error) {
    mostrarMensaje(error.message, "error");
  }
};

const borrarCliente = async () => {
  if (idBorrar === null) {
    return;
  }

  try {
    await api.del(`${URL_CLIENTES}/clientes/${idBorrar}`);
    mostrarMensaje("Cliente eliminado correctamente.", "exito");
    await cargarClientes();
  } catch (error) {
    mostrarMensaje(error.message, "error");
  }

  cerrarModal("modal-borrar");
};

formCliente.addEventListener("submit", (evento) => {
  evento.preventDefault();
  guardarCliente();
});

document.getElementById("nuevoBtn").addEventListener("click", abrirNuevo);
document.getElementById("recargarBtn").addEventListener("click", cargarClientes);
document.getElementById("borrar-confirmar").addEventListener("click", borrarCliente);

cargarClientes();
