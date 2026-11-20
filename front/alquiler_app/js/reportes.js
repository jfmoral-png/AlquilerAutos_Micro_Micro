const selectVehiculo = document.getElementById("vehiculoSelect");
const selectCliente = document.getElementById("clienteSelect");
const tablaDisponibles = document.getElementById("disponiblesTb");
const tablaHistorialVehiculo = document.getElementById("historialVehiculoTb");
const tablaHistorialCliente = document.getElementById("historialClienteTb");

const filaVacia = (tabla, columnas, texto) => {
  const tbody = tabla.tBodies[0];
  tbody.textContent = "";
  const tr = document.createElement("tr");
  const td = document.createElement("td");
  td.colSpan = columnas;
  td.className = "vacio";
  td.textContent = texto;
  tr.appendChild(td);
  tbody.appendChild(tr);
};

const mostrarDisponibles = (vehiculos) => {
  const tbody = tablaDisponibles.tBodies[0];
  tbody.textContent = "";

  if (!vehiculos.length) {
    filaVacia(tablaDisponibles, 5, "No hay vehículos disponibles en este momento.");
    return;
  }

  vehiculos.forEach((vehiculo, indice) => {
    const tr = document.createElement("tr");
    tr.appendChild(crearCelda(indice + 1));
    tr.appendChild(crearCelda(`${vehiculo.marca} ${vehiculo.modelo}`));
    tr.appendChild(crearCelda(vehiculo.anio));
    tr.appendChild(crearCelda(vehiculo.categoria || "—"));

    const tdEstado = document.createElement("td");
    tdEstado.appendChild(crearBadge(vehiculo.estado, `badge-${vehiculo.estado}`));
    tr.appendChild(tdEstado);

    tbody.appendChild(tr);
  });
};

const mostrarHistorial = (tabla, lista, tipo) => {
  const tbody = tabla.tBodies[0];
  tbody.textContent = "";

  if (!lista.length) {
    filaVacia(tabla, 5, "No hay reservas registradas para este registro.");
    return;
  }

  lista.forEach((reserva, indice) => {
    const tr = document.createElement("tr");
    tr.appendChild(crearCelda(indice + 1));
    tr.appendChild(crearCelda(tipo === "vehiculo" ? reserva.cliente_nombre : reserva.vehiculo));
    tr.appendChild(crearCelda(formatearFecha(reserva.fecha_inicio)));
    tr.appendChild(crearCelda(formatearFecha(reserva.fecha_fin)));

    const tdEstado = document.createElement("td");
    tdEstado.appendChild(crearBadge(reserva.estado, `badge-${reserva.estado}`));
    tr.appendChild(tdEstado);

    tbody.appendChild(tr);
  });
};

const cargarDisponibles = async () => {
  try {
    const disponibles = await api.get(`${URL_RESERVAS}/reservas/disponibles`);
    mostrarDisponibles(disponibles);
  } catch (error) {
    mostrarMensaje(error.message, "error");
    filaVacia(tablaDisponibles, 5, "No se pudo cargar la disponibilidad.");
  }
};

const cargarHistorialVehiculo = async () => {
  const id = selectVehiculo.value;

  if (!id) {
    filaVacia(tablaHistorialVehiculo, 5, "Seleccione un vehículo.");
    return;
  }

  try {
    const historial = await api.get(`${URL_RESERVAS}/reservas/historial/vehiculo/${id}`);
    mostrarHistorial(tablaHistorialVehiculo, historial, "vehiculo");
  } catch (error) {
    mostrarMensaje(error.message, "error");
    filaVacia(tablaHistorialVehiculo, 5, "No se pudo cargar el historial.");
  }
};

const cargarHistorialCliente = async () => {
  const id = selectCliente.value;

  if (!id) {
    filaVacia(tablaHistorialCliente, 5, "Seleccione un cliente.");
    return;
  }

  try {
    const historial = await api.get(`${URL_RESERVAS}/reservas/historial/cliente/${id}`);
    mostrarHistorial(tablaHistorialCliente, historial, "cliente");
  } catch (error) {
    mostrarMensaje(error.message, "error");
    filaVacia(tablaHistorialCliente, 5, "No se pudo cargar el historial.");
  }
};

const cargarCatalogos = async () => {
  try {
    const [vehiculos, clientes] = await Promise.all([
      api.get(`${URL_VEHICULOS}/vehiculos`),
      api.get(`${URL_CLIENTES}/clientes`),
    ]);

    selectVehiculo.textContent = "";
    selectVehiculo.appendChild(crearOpcion("", "Seleccione un vehículo"));
    vehiculos.forEach((vehiculo) => {
      selectVehiculo.appendChild(
        crearOpcion(vehiculo.id, `${vehiculo.marca} ${vehiculo.modelo} (${vehiculo.anio})`)
      );
    });

    selectCliente.textContent = "";
    selectCliente.appendChild(crearOpcion("", "Seleccione un cliente"));
    clientes.forEach((cliente) => {
      selectCliente.appendChild(crearOpcion(cliente.id, cliente.nombre));
    });

    if (vehiculos.length) {
      selectVehiculo.value = vehiculos[0].id;
    }

    if (clientes.length) {
      selectCliente.value = clientes[0].id;
    }

    await Promise.all([cargarHistorialVehiculo(), cargarHistorialCliente()]);
  } catch (error) {
    mostrarMensaje(error.message, "error");
  }
};

const cargarReportes = async () => {
  await Promise.all([cargarCatalogos(), cargarDisponibles()]);
};

selectVehiculo.addEventListener("change", cargarHistorialVehiculo);
selectCliente.addEventListener("change", cargarHistorialCliente);
document.getElementById("recargarBtn").addEventListener("click", cargarReportes);

cargarReportes();
