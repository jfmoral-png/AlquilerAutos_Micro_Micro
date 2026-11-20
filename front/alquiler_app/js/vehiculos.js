const tablaVehiculos = document.getElementById("vehiculosTb");
const formVehiculo = document.forms["vehiculoForm"];
const tituloModal = document.getElementById("modal-titulo");
let idEditar = null;
let idBorrar = null;

const mostrarVehiculos = (vehiculos) => {
  const tbody = tablaVehiculos.tBodies[0];
  tbody.textContent = "";

  if (!vehiculos.length) {
    const tr = document.createElement("tr");
    const td = document.createElement("td");
    td.colSpan = 7;
    td.className = "vacio";
    td.textContent = "No hay vehículos registrados.";
    tr.appendChild(td);
    tbody.appendChild(tr);
    return;
  }

  vehiculos.forEach((vehiculo, indice) => {
    const tr = document.createElement("tr");
    tr.appendChild(crearCelda(indice + 1));
    tr.appendChild(crearCelda(vehiculo.marca));
    tr.appendChild(crearCelda(vehiculo.modelo));
    tr.appendChild(crearCelda(vehiculo.anio));
    tr.appendChild(crearCelda(vehiculo.categoria || "—"));

    const tdEstado = document.createElement("td");
    tdEstado.appendChild(crearBadge(vehiculo.estado, `badge-${vehiculo.estado}`));
    tr.appendChild(tdEstado);

    const tdAcciones = document.createElement("td");
    const acciones = document.createElement("div");
    acciones.className = "acciones";
    acciones.appendChild(crearBoton("Editar", "btn-secondary", () => abrirEditar(vehiculo)));
    acciones.appendChild(crearBoton("Eliminar", "btn-danger", () => abrirBorrar(vehiculo)));
    tdAcciones.appendChild(acciones);
    tr.appendChild(tdAcciones);

    tbody.appendChild(tr);
  });
};

const cargarVehiculos = async () => {
  try {
    const vehiculos = await api.get(`${URL_VEHICULOS}/vehiculos`);
    mostrarVehiculos(vehiculos);
  } catch (error) {
    mostrarMensaje(error.message, "error");
    mostrarVehiculos([]);
  }
};

const abrirNuevo = () => {
  idEditar = null;
  formVehiculo.reset();
  formVehiculo.estado.value = "disponible";
  tituloModal.textContent = "Registrar vehículo";
  abrirModal("modal-vehiculo");
};

const abrirEditar = (vehiculo) => {
  idEditar = vehiculo.id;
  formVehiculo.marca.value = vehiculo.marca;
  formVehiculo.modelo.value = vehiculo.modelo;
  formVehiculo.anio.value = vehiculo.anio;
  formVehiculo.categoria.value = vehiculo.categoria || "";
  formVehiculo.estado.value = vehiculo.estado;
  tituloModal.textContent = "Editar vehículo";
  abrirModal("modal-vehiculo");
};

const abrirBorrar = (vehiculo) => {
  idBorrar = vehiculo.id;
  document.getElementById("borrar-nombre").textContent = `${vehiculo.marca} ${vehiculo.modelo}`;
  abrirModal("modal-borrar");
};

const guardarVehiculo = async () => {
  const datos = {
    marca: formVehiculo.marca.value.trim(),
    modelo: formVehiculo.modelo.value.trim(),
    anio: Number(formVehiculo.anio.value),
    categoria: formVehiculo.categoria.value.trim(),
    estado: formVehiculo.estado.value,
  };

  if (!datos.marca || !datos.modelo || !datos.anio) {
    mostrarMensaje("Complete la marca, el modelo y el año del vehículo.", "error");
    return;
  }

  try {
    if (idEditar === null) {
      await api.post(`${URL_VEHICULOS}/vehiculos`, datos);
      mostrarMensaje("Vehículo registrado correctamente.", "exito");
    } else {
      await api.put(`${URL_VEHICULOS}/vehiculos/${idEditar}`, datos);
      mostrarMensaje("Vehículo actualizado correctamente.", "exito");
    }

    cerrarModal("modal-vehiculo");
    await cargarVehiculos();
  } catch (error) {
    mostrarMensaje(error.message, "error");
  }
};

const borrarVehiculo = async () => {
  if (idBorrar === null) {
    return;
  }

  try {
    await api.del(`${URL_VEHICULOS}/vehiculos/${idBorrar}`);
    mostrarMensaje("Vehículo eliminado correctamente.", "exito");
    await cargarVehiculos();
  } catch (error) {
    mostrarMensaje(error.message, "error");
  }

  cerrarModal("modal-borrar");
};

formVehiculo.addEventListener("submit", (evento) => {
  evento.preventDefault();
  guardarVehiculo();
});

document.getElementById("nuevoBtn").addEventListener("click", abrirNuevo);
document.getElementById("recargarBtn").addEventListener("click", cargarVehiculos);
document.getElementById("borrar-confirmar").addEventListener("click", borrarVehiculo);

cargarVehiculos();
