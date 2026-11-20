const ESTADISTICAS = [
  ["vehiculos", "stat-vehiculos"],
  ["clientes", "stat-clientes"],
  ["reservas", "stat-reservas"],
  ["activas", "stat-activas"],
  ["disponibles", "stat-disponibles"],
  ["mantenimiento", "stat-mantenimiento"],
];

const marcarServicio = (id, texto, clase) => {
  const elemento = document.getElementById(id);

  if (!elemento) {
    return;
  }

  elemento.textContent = texto;
  elemento.className = `estado-servicio ${clase}`;
};

const comprobarServicios = () => {
  const servicios = [
    { url: URL_VEHICULOS, id: "estado-vehiculos" },
    { url: URL_CLIENTES, id: "estado-clientes" },
    { url: URL_RESERVAS, id: "estado-reservas" },
  ];

  servicios.forEach(async (servicio) => {
    try {
      const respuesta = await fetch(`${servicio.url}/inicio`);
      marcarServicio(servicio.id, respuesta.ok ? "En línea" : "Con errores", respuesta.ok ? "ok" : "falla");
    } catch (error) {
      marcarServicio(servicio.id, "Sin conexión", "falla");
    }
  });
};

const mostrarUltimasReservas = (reservas) => {
  const tbody = document.querySelector("#ultimasTb tbody");
  tbody.textContent = "";

  if (!reservas.length) {
    const tr = document.createElement("tr");
    const td = document.createElement("td");
    td.colSpan = 5;
    td.className = "vacio";
    td.textContent = "No hay reservas registradas.";
    tr.appendChild(td);
    tbody.appendChild(tr);
    return;
  }

  reservas.slice(0, 6).forEach((reserva) => {
    const tr = document.createElement("tr");
    tr.appendChild(crearCelda(reserva.cliente_nombre));
    tr.appendChild(crearCelda(reserva.vehiculo));
    tr.appendChild(crearCelda(formatearFecha(reserva.fecha_inicio)));
    tr.appendChild(crearCelda(formatearFecha(reserva.fecha_fin)));

    const tdEstado = document.createElement("td");
    tdEstado.appendChild(crearBadge(reserva.estado, `badge-${reserva.estado}`));
    tr.appendChild(tdEstado);

    tbody.appendChild(tr);
  });
};

const cargarPanel = async () => {
  ESTADISTICAS.forEach(([, id]) => {
    const elemento = document.getElementById(id);

    if (elemento) {
      elemento.textContent = "—";
    }
  });

  try {
    const [estadisticas, reservas] = await Promise.all([
      api.get(`${URL_RESERVAS}/reservas/estadisticas`),
      api.get(`${URL_RESERVAS}/reservas`),
    ]);

    ESTADISTICAS.forEach(([campo, id]) => {
      const elemento = document.getElementById(id);

      if (elemento) {
        elemento.textContent = estadisticas[campo] ?? "0";
      }
    });

    mostrarUltimasReservas(reservas);
  } catch (error) {
    mostrarMensaje(error.message, "error");
    mostrarUltimasReservas([]);
  }

  comprobarServicios();
};

document.getElementById("recargarBtn").addEventListener("click", cargarPanel);

cargarPanel();
