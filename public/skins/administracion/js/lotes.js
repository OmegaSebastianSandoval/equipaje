// Registro y reclamo de maletas por lotes (JavaScript puro, sin jQuery).
// El lote se guarda en sessionStorage para no perderlo si se recarga la pagina.
// Las maletas se envian una por una (cada registro tarda varios segundos por el correo y el WhatsApp).
// Bootstrap se carga despues del contenido de la vista, por eso se espera a DOMContentLoaded.
document.addEventListener("DOMContentLoaded", function () {
  const cfg = window.loteConfig || {};
  const esRegistro = cfg.modo === "registro";
  const storageKey = "lote_maletas_" + cfg.modo;
  const byId = function (id) {
    return document.getElementById(id);
  };
  const csrf = (byId("lote-csrf") || {}).value || "";
  let filas = [];
  let enviando = false;
  let cacheVuelos = {};
  let vueloDestino = null; // codigos de las filas a las que se asigna el vuelo del modal
  let vueloModal = null;

  // ---------------------------------------------------------------
  // Utilidades
  // ---------------------------------------------------------------

  // Peticion al servidor. Falla si la respuesta no es 2xx, no es JSON o pasa el tiempo limite.
  function pedir(url, opciones) {
    opciones = opciones || {};
    const metodo = opciones.metodo || "POST";
    const params = new URLSearchParams();
    Object.keys(opciones.data || {}).forEach(function (k) {
      const v = opciones.data[k];
      params.append(k, v == null ? "" : v);
    });
    const init = {
      method: metodo,
      headers: { "X-Requested-With": "XMLHttpRequest" },
      credentials: "same-origin",
    };
    if (metodo === "GET") {
      const qs = params.toString();
      url += qs ? (url.indexOf("?") === -1 ? "?" : "&") + qs : "";
    } else {
      init.headers["Content-Type"] =
        "application/x-www-form-urlencoded; charset=UTF-8";
      init.body = params.toString();
    }
    let timer = null;
    if (opciones.timeout && window.AbortController) {
      const ctrl = new AbortController();
      init.signal = ctrl.signal;
      timer = setTimeout(function () {
        ctrl.abort();
      }, opciones.timeout);
    }
    return fetch(url, init)
      .then(function (res) {
        if (!res.ok) {
          throw new Error("HTTP " + res.status);
        }
        return res.json();
      })
      .finally(function () {
        clearTimeout(timer);
      });
  }

  const ESCAPES = {
    "&": "&amp;",
    "<": "&lt;",
    ">": "&gt;",
    '"': "&quot;",
    "'": "&#39;",
  };

  function esc(texto) {
    return (texto == null ? "" : String(texto)).replace(/[&<>"']/g, function (c) {
      return ESCAPES[c];
    });
  }

  // Busca el .lote-item de un codigo dentro de la lista
  function itemDe(codigo) {
    const items = byId("lote-lista").querySelectorAll(".lote-item");
    for (let i = 0; i < items.length; i++) {
      if (items[i].dataset.codigo === codigo) {
        return items[i];
      }
    }
    return null;
  }

  function codigoDe(el) {
    const item = el.closest(".lote-item");
    return item ? item.dataset.codigo : "";
  }

  // Delegacion de eventos: ejecuta fn cuando el evento ocurre dentro de un elemento que coincide con selector
  function delegar(contenedor, evento, selector, fn) {
    if (!contenedor) {
      return;
    }
    contenedor.addEventListener(evento, function (e) {
      const el = e.target.closest(selector);
      if (el && contenedor.contains(el)) {
        fn.call(el, e);
      }
    });
  }

  function htmlAElemento(html) {
    const t = document.createElement("template");
    t.innerHTML = html.trim();
    return t.content.firstElementChild;
  }

  // ---------------------------------------------------------------
  // Estado guardado
  // ---------------------------------------------------------------
  function guardar() {
    try {
      sessionStorage.setItem(
        storageKey,
        JSON.stringify(
          filas.filter(function (f) {
            return f.estado !== "ok";
          }),
        ),
      );
    } catch (e) {}
  }

  function cargar() {
    try {
      const data = JSON.parse(sessionStorage.getItem(storageKey) || "[]");
      if (Array.isArray(data)) {
        filas = data.map(function (f) {
          if (f.estado === "enviando") {
            f.estado = "pendiente";
          }
          return f;
        });
      }
    } catch (e) {
      filas = [];
    }
  }

  function buscarFila(codigo) {
    for (let i = 0; i < filas.length; i++) {
      if (filas[i].codigo === codigo) {
        return filas[i];
      }
    }
    return null;
  }

  // Aerolinea efectiva: la de conexion si la maleta es de conexion
  function aerolineaEfectiva(f) {
    return f.conexion === "1" ? f.conexionAerolinea || "" : f.aerolinea;
  }

  // ---------------------------------------------------------------
  // Avisos
  // ---------------------------------------------------------------
  let estadoCamaraTimer = null;

  // Mientras la camara esta abierta el modal tapa los avisos de la pagina: se repite el resultado dentro del modal
  function mostrarEnCamara(texto, tipo) {
    const m = byId("videoModal");
    const e = byId("camara-estado");
    if (!m || !e || !m.classList.contains("show")) {
      return;
    }
    const icono = tipo === "ok" ? "fa-circle-check" : "fa-circle-exclamation";
    clearTimeout(estadoCamaraTimer);
    e.classList.remove("aviso-ok", "aviso-error");
    e.classList.add(tipo === "ok" ? "aviso-ok" : "aviso-error", "visible");
    e.innerHTML =
      '<i class="fa-solid ' + icono + '"></i><span>' + esc(texto) + "</span>";
    estadoCamaraTimer = setTimeout(
      function () {
        e.classList.remove("visible");
      },
      tipo === "ok" ? 3000 : 5000,
    );
  }

  // Total de maletas en la lista, visible en el modal de la camara
  function actualizarCamara() {
    const n = filas.length;
    const contador = byId("camara-contador");
    const listo = byId("camara-listo");
    if (contador) {
      contador.textContent =
        n === 0
          ? "Apunta la cámara a cada etiqueta"
          : "En la lista: " + n + (n === 1 ? " maleta" : " maletas");
    }
    if (listo) {
      listo.textContent = n === 0 ? "Listo" : "Listo (" + n + ")";
    }
  }

  let avisoTimer = null;
  let resumenTimer = null;

  // Aviso visible bajo el campo de escaneo. Se oculta solo: los de exito a los 4 s y los de error a los 7 s.
  function aviso(texto, tipo) {
    const a = byId("lote-aviso");
    clearTimeout(avisoTimer);
    if (!a) {
      return;
    }
    a.classList.remove("aviso-ok", "aviso-error");
    if (!texto) {
      a.innerHTML = "";
      return;
    }
    const icono = tipo === "ok" ? "fa-circle-check" : "fa-circle-exclamation";
    a.classList.add(tipo === "ok" ? "aviso-ok" : "aviso-error");
    a.innerHTML =
      '<i class="fa-solid ' + icono + '"></i><span>' + esc(texto) + "</span>";
    mostrarEnCamara(texto, tipo);
    avisoTimer = setTimeout(
      function () {
        a.classList.remove("aviso-ok", "aviso-error");
        a.innerHTML = "";
      },
      tipo === "ok" ? 4000 : 7000,
    );
  }

  // ---------------------------------------------------------------
  // Sonido
  // ---------------------------------------------------------------
  // El audio del navegador solo arranca despues de un toque del usuario: se prepara al abrir la camara
  function prepararSonido() {
    try {
      const Ctx = window.AudioContext || window.webkitAudioContext;
      if (!Ctx) {
        return;
      }
      beep.ctx = beep.ctx || new Ctx();
      if (beep.ctx.state === "suspended") {
        beep.ctx.resume();
      }
    } catch (e) {}
  }

  function beep() {
    try {
      const Ctx = window.AudioContext || window.webkitAudioContext;
      if (Ctx) {
        beep.ctx = beep.ctx || new Ctx();
        if (beep.ctx.state === "suspended") {
          beep.ctx.resume();
        }
        const osc = beep.ctx.createOscillator();
        const gain = beep.ctx.createGain();
        osc.frequency.value = 880;
        gain.gain.value = 0.1;
        osc.connect(gain);
        gain.connect(beep.ctx.destination);
        osc.start();
        osc.stop(beep.ctx.currentTime + 0.12);
      }
    } catch (e) {}
    if (navigator.vibrate) {
      navigator.vibrate(80);
    }
  }

  // ---------------------------------------------------------------
  // Agregar maletas
  // ---------------------------------------------------------------
  function agregarCodigo(codigo) {
    const original = String(codigo || "");
    codigo = original.replace(/[^0-9]/g, "");
    if (codigo.length !== 10) {
      aviso(
        'Se leyó "' +
          (original || "—") +
          '" y no es una etiqueta válida (debe tener 10 dígitos).',
        "error",
      );
      return;
    }
    if (buscarFila(codigo)) {
      aviso("La maleta " + codigo + " ya está en la lista.", "error");
      return;
    }
    const errorConexion = function () {
      aviso(
        "No se pudo consultar la maleta " + codigo + ". Revisa la conexión.",
        "error",
      );
    };
    if (esRegistro) {
      pedir("/administracion/maletas/getAirline", {
        data: { code: codigo.substring(1, 4), full_code: codigo },
      }).then(function (data) {
        if (buscarFila(codigo)) {
          return;
        }
        if (data.status === "success") {
          filas.push({
            codigo: codigo,
            aerolinea: data.airline_code,
            aerolineaNombre: data.airline_name,
            conexion: "0",
            conexionAerolinea: "",
            vuelo: null,
            manual: "",
            seleccionada: false,
            estado: "pendiente",
            mensaje: "",
          });
          aviso(
            "Maleta " + codigo + " agregada (" + data.airline_name + ").",
            "ok",
          );
          beep();
          render();
        } else if (data.status === "ext") {
          aviso("La maleta " + codigo + " ya está registrada.", "error");
        } else {
          aviso(
            "No hay una aerolínea registrada con el código " +
              codigo.substring(1, 4) +
              " (maleta " +
              codigo +
              ").",
            "error",
          );
        }
      }, errorConexion);
    } else {
      pedir("/administracion/maletas/getBag", {
        data: { code: codigo },
      }).then(function (data) {
        if (buscarFila(codigo)) {
          return;
        }
        const fila = {
          codigo: codigo,
          aerolineaNombre: "",
          vueloTexto: "—",
          salida: "",
          tiempo: "",
          urgencia: "ninguna",
          estado: "pendiente",
          mensaje: "",
        };
        if (data.status === "success") {
          fila.aerolineaNombre = data.aerolinea;
          fila.vueloTexto = data.vuelo;
          fila.salida = data.salida;
          fila.tiempo = data.tiempo_restante;
          fila.urgencia = data.urgencia;
          const lugares = { N: "Nacional", I: "Internacional" };
          fila.over = data.over_tipo
            ? "Over " +
              data.over_tipo +
              (data.over_tipo == "1" && lugares[data.over_lugar]
                ? " " + lugares[data.over_lugar]
                : "")
            : "";
          if (data.maleta_estado !== "" && data.maleta_estado !== null) {
            fila.estado = "excluida";
            fila.mensaje = "Ya reclamada";
          }
        } else {
          fila.estado = "excluida";
          fila.mensaje = "No encontrada";
        }
        filas.push(fila);
        if (fila.estado === "excluida") {
          aviso(
            "Maleta " + codigo + ": " + fila.mensaje.toLowerCase() + ".",
            "error",
          );
        } else {
          aviso(
            "Maleta " +
              codigo +
              " agregada" +
              (fila.aerolineaNombre ? " (" + fila.aerolineaNombre + ")" : "") +
              ".",
            "ok",
          );
          beep();
        }
        render();
      }, errorConexion);
    }
  }

  // Para la camara (main.js) en modo continuo
  window.loteAgregarCodigo = agregarCodigo;

  // ---------------------------------------------------------------
  // Render
  // ---------------------------------------------------------------

  // Aerolineas ordenadas por nombre (para encontrarlas facil en el celular). Se ordenan una sola vez.
  const aerolineasOrdenadas = Object.keys(cfg.aerolineas || {}).sort(
    function (a, b) {
      return String(cfg.aerolineas[a]).localeCompare(
        String(cfg.aerolineas[b]),
        "es",
      );
    },
  );

  function opcionesConexion(seleccionada) {
    let html = '<option value="">Selecciona la aerolínea</option>';
    aerolineasOrdenadas.forEach(function (codigo) {
      html +=
        '<option value="' +
        esc(codigo) +
        '"' +
        (codigo === seleccionada ? " selected" : "") +
        ">" +
        esc(cfg.aerolineas[codigo]) +
        "</option>";
    });
    return html;
  }

  // Indicador de estado de la fila. No usa una X para no confundirse con el boton de quitar.
  function iconoEstado(f) {
    if (f.estado === "enviando") {
      return '<i class="fa-solid fa-spinner fa-spin lote-estado" title="Enviando" aria-label="Enviando"></i>';
    }
    if (f.estado === "ok") {
      return '<i class="fa-solid fa-circle-check lote-estado text-success" title="Listo" aria-label="Listo"></i>';
    }
    if (f.estado === "error" || f.estado === "excluida") {
      return (
        '<i class="fa-solid fa-triangle-exclamation lote-estado text-danger" title="' +
        esc(f.mensaje) +
        '" aria-label="' +
        esc(f.mensaje) +
        '"></i>'
      );
    }
    return "";
  }

  function textoVuelo(f) {
    if (f.vuelo) {
      return f.vuelo.texto;
    }
    if (f.manual) {
      return f.manual + " · Vuelo no listado";
    }
    return "";
  }

  // Boton del vuelo: numero en grande y el detalle (destino, hora, puerta) debajo
  function botonVuelo(f, vuelo) {
    const bloqueado = f.conexion === "1" && !f.conexionAerolinea;
    if (!vuelo) {
      return (
        '<button type="button" class="lote-vuelo sin-vuelo"' +
        (bloqueado ? " disabled" : "") +
        ">" +
        '<i class="fa-solid fa-plane-departure"></i>' +
        '<span class="lote-vuelo-texto"><strong>' +
        (bloqueado
          ? "Elige primero la aerolínea de conexión"
          : "Seleccionar vuelo") +
        "</strong></span>" +
        "</button>"
      );
    }
    const partes = String(vuelo).split(" · ");
    return (
      '<button type="button" class="lote-vuelo">' +
      '<i class="fa-solid fa-plane-departure"></i>' +
      '<span class="lote-vuelo-texto"><strong>' +
      esc(partes[0]) +
      "</strong>" +
      (partes.length > 1
        ? "<small>" + esc(partes.slice(1).join(" · ")) + "</small>"
        : "") +
      "</span>" +
      '<span class="lote-vuelo-cambiar">Cambiar</span>' +
      "</button>"
    );
  }

  function filaHtml(f) {
    let clases = "lote-item";
    if (f.estado === "error" || f.estado === "excluida") {
      clases += " lote-item-error";
    } else if (f.estado === "ok") {
      clases += " lote-item-ok";
    }
    let html =
      '<div class="' + clases + '" data-codigo="' + esc(f.codigo) + '">';
    if (esRegistro) {
      const vuelo = textoVuelo(f);
      html +=
        '<div class="lote-item-top">' +
        '<input type="checkbox" class="form-check-input lote-check" aria-label="Seleccionar ' +
        esc(f.codigo) +
        '"' +
        (f.seleccionada ? " checked" : "") +
        (f.estado === "ok" ? " disabled" : "") +
        ">" +
        '<div class="lote-info">' +
        '<strong class="lote-codigo">' +
        esc(f.codigo) +
        "</strong>" +
        '<span class="lote-aerolinea">' +
        esc(f.aerolineaNombre) +
        "</span>" +
        "</div>" +
        iconoEstado(f) +
        '<button type="button" class="lote-quitar" title="Quitar" aria-label="Quitar ' +
        esc(f.codigo) +
        '"' +
        (f.estado === "enviando" ? " disabled" : "") +
        '><i class="fa-solid fa-xmark"></i></button>' +
        "</div>" +
        '<div class="lote-item-campos lote-pasos">' +
        // 1. Conexion
        '<div class="lote-paso">' +
        '<span class="lote-paso-titulo" id="cx-t-' +
        esc(f.codigo) +
        '">¿Es maleta de conexión?</span>' +
        '<div class="lote-sino" role="group" aria-labelledby="cx-t-' +
        esc(f.codigo) +
        '">' +
        '<button type="button" class="lote-conexion-btn' +
        (f.conexion !== "1" ? " activo" : "") +
        '" data-valor="0" aria-pressed="' +
        (f.conexion !== "1") +
        '">No</button>' +
        '<button type="button" class="lote-conexion-btn' +
        (f.conexion === "1" ? " activo" : "") +
        '" data-valor="1" aria-pressed="' +
        (f.conexion === "1") +
        '">Sí</button>' +
        "</div>" +
        "</div>" +
        (f.conexion === "1"
          ? '<div class="lote-paso">' +
            '<label class="lote-paso-titulo" for="cx-a-' +
            esc(f.codigo) +
            '">Aerolínea de conexión</label>' +
            '<select class="form-select lote-conexion-aerolinea' +
            (f.conexionAerolinea ? "" : " pendiente") +
            '" id="cx-a-' +
            esc(f.codigo) +
            '">' +
            opcionesConexion(f.conexionAerolinea) +
            "</select>" +
            "</div>"
          : "") +
        // 2. Vuelo
        '<div class="lote-paso lote-paso-vuelo">' +
        '<span class="lote-paso-titulo">Vuelo</span>' +
        botonVuelo(f, vuelo) +
        "</div>" +
        "</div>";
    } else {
      html +=
        '<div class="lote-item-top">' +
        '<div class="lote-info">' +
        '<strong class="lote-codigo">' +
        esc(f.codigo) +
        "</strong>" +
        '<span class="lote-aerolinea">' +
        esc(f.aerolineaNombre) +
        (f.over ? " · " + esc(f.over) : "") +
        "</span>" +
        "</div>" +
        iconoEstado(f) +
        '<button type="button" class="lote-quitar" title="Quitar" aria-label="Quitar ' +
        esc(f.codigo) +
        '"' +
        (f.estado === "enviando" ? " disabled" : "") +
        '><i class="fa-solid fa-xmark"></i></button>' +
        "</div>" +
        (f.vueloTexto && f.vueloTexto !== "—"
          ? '<div class="lote-item-campos lote-detalle">'
          : '<div class="lote-item-campos lote-detalle d-none">') +
        '<span><i class="fa-solid fa-plane-departure"></i> ' +
        esc(f.vueloTexto || "—") +
        "</span>" +
        (f.salida ? "<span>Salida: " + esc(f.salida) + "</span>" : "") +
        (f.tiempo && f.tiempo !== "—"
          ? '<span class="lote-urgencia-' +
            esc(f.urgencia) +
            '">' +
            esc(f.tiempo) +
            "</span>"
          : "") +
        "</div>";
    }
    if (f.mensaje && f.estado !== "ok") {
      html += '<div class="lote-mensaje">' + esc(f.mensaje) + "</div>";
    }
    html += "</div>";
    return html;
  }

  // Redibuja toda la lista en una sola operacion y conserva el scroll (de la pagina y de la lista)
  function render() {
    const lista = byId("lote-lista");
    const vacio = byId("lote-vacio");
    const scrollPagina = window.scrollY;
    const scrollLista = lista.scrollTop;
    lista.querySelectorAll(".lote-item").forEach(function (el) {
      el.remove();
    });
    if (vacio) {
      vacio.style.display = filas.length === 0 ? "" : "none";
    }
    lista.insertAdjacentHTML("beforeend", filas.map(filaHtml).join(""));
    lista.scrollTop = scrollLista;
    window.scrollTo({
      top: scrollPagina,
      left: window.scrollX,
      behavior: "instant",
    });
    actualizarBotones();
    actualizarCamara();
    guardar();
  }

  // Actualiza solo una fila para no mover la pantalla ni perder el foco
  function renderFila(f, enfocar) {
    const actual = itemDe(f.codigo);
    if (!actual) {
      render();
      return;
    }
    const nueva = htmlAElemento(filaHtml(f));
    actual.replaceWith(nueva);
    if (enfocar) {
      const el = nueva.querySelector(enfocar);
      if (el) {
        el.focus({ preventScroll: true });
      }
    }
    actualizarBotones();
    guardar();
  }

  function listaParaEnviar() {
    return filas.filter(function (f) {
      return f.estado === "pendiente" || f.estado === "error";
    });
  }

  function filaCompleta(f) {
    if (f.conexion === "1" && !f.conexionAerolinea) {
      return false;
    }
    return !!(f.vuelo || f.manual);
  }

  function actualizarBotones() {
    const porEnviar = listaParaEnviar();
    const enviar = byId("lote-enviar");
    const limpiar = byId("lote-limpiar");
    let listo = porEnviar.length > 0 && !enviando;
    if (esRegistro) {
      listo = listo && porEnviar.every(filaCompleta);
      enviar.textContent = "Registrar todas (" + porEnviar.length + ")";
      const sel = filas.filter(function (f) {
        return f.seleccionada && f.estado !== "ok";
      });
      const aerolineas = {};
      sel.forEach(function (f) {
        aerolineas[aerolineaEfectiva(f)] = true;
      });
      const claves = Object.keys(aerolineas);
      const asignar = byId("lote-asignar");
      if (asignar) {
        asignar.disabled =
          enviando ||
          sel.length === 0 ||
          claves.length !== 1 ||
          claves[0] === "";
        asignar.title =
          claves.length > 1
            ? "Las maletas seleccionadas deben ser de la misma aerolínea"
            : "";
      }
    } else {
      enviar.textContent = "Reclamar todas (" + porEnviar.length + ")";
    }
    enviar.disabled = !listo;
    if (limpiar) {
      limpiar.disabled = enviando || filas.length === 0;
    }
  }

  // ---------------------------------------------------------------
  // Seleccion de vuelo
  // ---------------------------------------------------------------
  function rango() {
    const r = document.querySelector('input[name="lote-rango"]:checked');
    return (r && r.value) || "hoy";
  }

  function todos() {
    const t = byId("lote-todos");
    return t && t.checked ? "1" : "0";
  }

  function cargarVuelos(aerolinea) {
    const key = aerolinea + "|" + rango() + "|" + todos();
    if (cacheVuelos[key]) {
      return Promise.resolve(cacheVuelos[key]);
    }
    return pedir("/administracion/maletas/vuelos", {
      metodo: "GET",
      data: { aerolinea: aerolinea, rango: rango(), todos: todos() },
    }).then(function (data) {
      cacheVuelos[key] = data;
      return data;
    });
  }

  function textoOpcionVuelo(v) {
    let t = v.numero + " · " + (v.destino || "") + " · " + v.schedule;
    if (v.actual) {
      t += " (act. " + v.actual + ")";
    }
    if (v.puerta) {
      t += " · Puerta " + v.puerta;
    }
    return t;
  }

  function abrirVuelos(codigos) {
    const f = buscarFila(codigos[0]);
    if (!f) {
      return;
    }
    const aerolinea = aerolineaEfectiva(f);
    if (!aerolinea) {
      aviso(
        "Selecciona primero la aerolínea de conexión de la maleta " +
          f.codigo +
          ".",
        "error",
      );
      return;
    }
    vueloDestino = codigos;
    const nombre = (cfg.aerolineas || {})[aerolinea] || aerolinea;
    byId("vuelo-para").textContent =
      (codigos.length > 1
        ? codigos.length + " maletas"
        : "Maleta " + codigos[0]) +
      " · " +
      nombre;
    byId("vuelo-buscar").value = "";
    byId("vuelo-manual-input").value =
      codigos.length === 1 ? f.manual || "" : "";
    byId("vuelo-lista").innerHTML = estadoVuelos(
      "fa-spinner fa-spin",
      "Cargando vuelos...",
      "",
    );
    vueloModal.show();
    cargarVuelos(aerolinea).then(pintarVuelos, function () {
      byId("vuelo-lista").innerHTML = estadoVuelos(
        "fa-triangle-exclamation",
        "No se pudieron cargar los vuelos.",
        'Revisa la conexión o usa "Vuelo no listado".',
      );
    });
  }

  function pintarVuelos(data) {
    const lista = byId("vuelo-lista");
    if (data.status === "sin_iata") {
      lista.innerHTML = estadoVuelos(
        "fa-circle-info",
        "Esta aerolínea no tiene vuelos configurados (falta el código IATA).",
        'Usa "Vuelo no listado" abajo.',
      );
      return;
    }
    const q = byId("vuelo-buscar").value.toUpperCase().replace(/\s+/g, "");
    const vuelos = (data.vuelos || []).filter(function (v) {
      if (!q) {
        return true;
      }
      return (
        v.numero.replace(/\s+/g, "").indexOf(q) !== -1 ||
        (v.destino || "").toUpperCase().replace(/\s+/g, "").indexOf(q) !== -1
      );
    });
    if (vuelos.length === 0) {
      const sugerencia =
        todos() === "0" || rango() === "hoy"
          ? 'Prueba con "Próximas 24 h" o "Ver todos" en la barra de vuelos, o usa "Vuelo no listado".'
          : 'Usa "Vuelo no listado" abajo.';
      lista.innerHTML = estadoVuelos(
        "fa-plane-slash",
        "No hay vuelos para mostrar.",
        sugerencia,
      );
      return;
    }
    // Vuelo ya asignado (solo cuando se edita una maleta)
    const actual =
      vueloDestino && vueloDestino.length === 1
        ? buscarFila(vueloDestino[0])
        : null;
    const actualId = actual && actual.vuelo ? String(actual.vuelo.id) : "";
    let html = "";
    vuelos.forEach(function (v) {
      const activo = String(v.vuelo_id) === actualId;
      html +=
        '<button type="button" role="listitem" class="vuelo-opcion' +
        (activo ? " activo" : "") +
        '" data-id="' +
        esc(v.vuelo_id) +
        '" data-texto="' +
        esc(textoOpcionVuelo(v)) +
        '">' +
        '<span class="vuelo-hora">' +
        "<strong>" +
        esc(v.actual || v.schedule) +
        "</strong>" +
        (v.actual
          ? '<small class="vuelo-programada">Prog. ' +
            esc(v.schedule) +
            "</small>"
          : "") +
        "</span>" +
        '<span class="vuelo-info">' +
        '<span class="vuelo-numero">' +
        esc(v.numero) +
        "</span>" +
        '<span class="vuelo-destino">' +
        esc(v.destino || "") +
        "</span>" +
        '<span class="vuelo-etiquetas">' +
        (v.puerta ? "<span>Puerta " + esc(v.puerta) + "</span>" : "") +
        (v.status_texto ? "<span>" + esc(v.status_texto) + "</span>" : "") +
        "<span>" +
        (v.tipo === "I" ? "Internacional" : "Nacional") +
        "</span>" +
        "</span>" +
        "</span>" +
        '<i class="fa-solid ' +
        (activo ? "fa-circle-check" : "fa-chevron-right") +
        ' vuelo-flecha"></i>' +
        "</button>";
    });
    lista.innerHTML = html;
  }

  function estadoVuelos(icono, titulo, detalle) {
    return (
      '<div class="vuelo-estado"><i class="fa-solid ' +
      icono +
      '"></i><strong>' +
      esc(titulo) +
      "</strong>" +
      (detalle ? "<span>" + esc(detalle) + "</span>" : "") +
      "</div>"
    );
  }

  function asignarVuelo(vuelo, manual) {
    const destino = vueloDestino || [];
    destino.forEach(function (codigo) {
      const f = buscarFila(codigo);
      if (f && f.estado !== "ok") {
        f.vuelo = vuelo;
        f.manual = vuelo ? "" : manual;
        if (f.estado === "error") {
          f.estado = "pendiente";
          f.mensaje = "";
        }
      }
    });
    const ultimo = destino[destino.length - 1];
    vueloDestino = null;
    // Al cerrar el modal se pasa a la siguiente maleta que falta completar (sin subir al campo de escaneo)
    byId("vueloModal").addEventListener(
      "hidden.bs.modal",
      function () {
        enfocarSiguientePendiente(ultimo);
      },
      { once: true },
    );
    vueloModal.hide();
    render();
  }

  // En el celular no se enfoca el campo de escaneo: subiria la pagina y abriria el teclado
  function enfocarEscaner() {
    if (window.matchMedia && window.matchMedia("(pointer: coarse)").matches) {
      return;
    }
    const campo = byId("lote_codigo");
    if (campo) {
      campo.focus();
    }
  }

  // Lleva la pantalla a la siguiente maleta sin vuelo (o sin aerolinea de conexion) y enfoca ese paso
  function enfocarSiguientePendiente(codigoActual) {
    const i = filas.findIndex(function (f) {
      return f.codigo === codigoActual;
    });
    const orden = filas.slice(i + 1).concat(filas.slice(0, i + 1));
    const siguiente = orden.find(function (f) {
      return (
        (f.estado === "pendiente" || f.estado === "error") && !filaCompleta(f)
      );
    });
    if (!siguiente) {
      enfocarEscaner();
      return;
    }
    const item = itemDe(siguiente.codigo);
    if (!item) {
      return;
    }
    const faltaAerolinea =
      siguiente.conexion === "1" && !siguiente.conexionAerolinea;
    const el = item.querySelector(
      faltaAerolinea ? ".lote-conexion-aerolinea" : ".lote-vuelo",
    );
    if (el) {
      el.focus({ preventScroll: true });
    }
    item.scrollIntoView({ block: "center", behavior: "smooth" });
  }

  // ---------------------------------------------------------------
  // Pantalla de carga mientras se envia el lote
  // ---------------------------------------------------------------
  let wakeLock = null;

  function mostrarCargando(total) {
    let c = byId("lote-cargando");
    if (!c) {
      c = htmlAElemento(
        '<div id="lote-cargando" class="lote-cargando" role="alertdialog" aria-modal="true" aria-labelledby="lote-cargando-titulo" aria-describedby="lote-cargando-detalle">' +
          '<div class="lote-cargando-caja">' +
          '<div class="lote-cargando-spinner" aria-hidden="true"></div>' +
          '<h3 id="lote-cargando-titulo"></h3>' +
          '<p class="lote-cargando-progreso" aria-live="polite"></p>' +
          '<div class="lote-cargando-barra"><span></span></div>' +
          '<p class="lote-cargando-codigo"></p>' +
          '<p id="lote-cargando-detalle" class="lote-cargando-detalle">No cierres ni recargues esta página. Cada maleta tarda unos segundos porque se notifica a la aerolínea.</p>' +
          "</div></div>",
      );
      document.body.appendChild(c);
    }
    c.querySelector("h3").textContent = esRegistro
      ? "Registrando maletas"
      : "Reclamando maletas";
    actualizarCargando(0, total, "");
    c.classList.add("mostrar");
    document.body.classList.add("lote-cargando-abierto");
    // Evita que el celular apague la pantalla durante el envio
    try {
      if (navigator.wakeLock) {
        navigator.wakeLock
          .request("screen")
          .then(function (lock) {
            wakeLock = lock;
          })
          .catch(function () {});
      }
    } catch (e) {}
  }

  function actualizarCargando(hechas, total, codigo) {
    const c = byId("lote-cargando");
    if (!c) {
      return;
    }
    c.querySelector(".lote-cargando-progreso").textContent =
      "Maleta " + Math.min(hechas + 1, total) + " de " + total;
    c.querySelector(".lote-cargando-barra span").style.width =
      Math.round((hechas / total) * 100) + "%";
    c.querySelector(".lote-cargando-codigo").textContent = codigo
      ? "Procesando " + codigo
      : "";
  }

  function ocultarCargando() {
    const c = byId("lote-cargando");
    if (c) {
      c.classList.remove("mostrar");
    }
    document.body.classList.remove("lote-cargando-abierto");
    try {
      if (wakeLock) {
        wakeLock.release();
        wakeLock = null;
      }
    } catch (e) {}
  }

  // ---------------------------------------------------------------
  // Envio (una por una)
  // ---------------------------------------------------------------
  function enviarTodas() {
    const cola = listaParaEnviar();
    if (cola.length === 0 || enviando) {
      return;
    }
    if (esRegistro && !cola.every(filaCompleta)) {
      aviso(
        "Todas las maletas deben tener un vuelo (o un vuelo no listado).",
        "error",
      );
      return;
    }
    enviando = true;
    const resumen = byId("lote-resumen");
    resumen.innerHTML = "";
    mostrarCargando(cola.length);
    let ok = 0;
    let fallidas = 0;
    let i = 0;

    function terminar() {
      enviando = false;
      actualizarCargando(cola.length, cola.length, "");
      ocultarCargando();
      resumen.innerHTML =
        '<div class="lote-resumen ' +
        (fallidas ? "aviso-error" : "aviso-ok") +
        '">' +
        '<i class="fa-solid ' +
        (fallidas ? "fa-circle-exclamation" : "fa-circle-check") +
        '"></i><span>' +
        (esRegistro ? "Registradas: " : "Reclamadas: ") +
        "<strong>" +
        ok +
        "</strong>" +
        (fallidas
          ? " · Con error: <strong>" +
            fallidas +
            "</strong> (quedan en la lista para reintentar)"
          : "") +
        "</span></div>";
      clearTimeout(resumenTimer);
      resumenTimer = setTimeout(
        function () {
          resumen.innerHTML = "";
        },
        fallidas ? 15000 : 8000,
      );
      setTimeout(function () {
        filas = filas.filter(function (f) {
          return f.estado !== "ok";
        });
        render();
      }, 1500);
      render();
      enfocarEscaner();
    }

    function siguiente() {
      if (i >= cola.length) {
        terminar();
        return;
      }
      const f = cola[i];
      actualizarCargando(i, cola.length, f.codigo);
      i++;
      f.estado = "enviando";
      f.mensaje = "";
      // Durante el envio solo se redibuja la fila que cambia
      renderFila(f);
      const data = { csrf: csrf, maleta_codigo: f.codigo };
      let url = "/administracion/maletas/reclamoAjax";
      if (esRegistro) {
        url = "/administracion/maletas/storeAjax";
        data.maleta_aerolinea = f.aerolinea;
        data.connection_select = f.conexion;
        data.connection = f.conexion === "1" ? f.conexionAerolinea : "";
        data.vuelo_id = f.vuelo ? f.vuelo.id : "";
        data.vuelo_manual = f.vuelo ? "" : f.manual;
      }
      pedir(url, { data: data, timeout: 300000 })
        .then(
          function (res) {
            if (res.status === "ok") {
              f.estado = "ok";
              ok++;
            } else {
              f.estado = "error";
              f.mensaje = res.mensaje || "Error";
              fallidas++;
            }
          },
          function () {
            f.estado = "error";
            f.mensaje = "Error de conexión, intenta de nuevo";
            fallidas++;
          },
        )
        .then(function () {
          renderFila(f);
          siguiente();
        });
    }
    siguiente();
  }

  // ---------------------------------------------------------------
  // Eventos
  // ---------------------------------------------------------------
  vueloModal =
    esRegistro && byId("vueloModal")
      ? new bootstrap.Modal(byId("vueloModal"))
      : null;
  cargar();
  render();

  const campoCodigo = byId("lote_codigo");
  if (campoCodigo) {
    campoCodigo.addEventListener("input", function () {
      const valor = this.value.replace(/[^0-9]/g, "");
      if (valor.length >= 10) {
        this.value = "";
        agregarCodigo(valor.substring(0, 10));
        return;
      }
      this.value = valor;
    });
    campoCodigo.addEventListener("keydown", function (e) {
      if (e.key === "Enter") {
        e.preventDefault();
        const valor = this.value;
        if (valor) {
          this.value = "";
          agregarCodigo(valor);
        }
      }
    });
  }

  const lista = byId("lote-lista");

  delegar(lista, "click", ".lote-quitar", function () {
    const codigo = codigoDe(this);
    filas = filas.filter(function (f) {
      return f.codigo !== codigo;
    });
    render();
  });

  byId("lote-limpiar").addEventListener("click", function () {
    if (enviando) {
      return;
    }
    filas = [];
    byId("lote-resumen").innerHTML = "";
    aviso("");
    render();
  });

  byId("lote-enviar").addEventListener("click", enviarTodas);
  document
    .querySelectorAll('[data-bs-target="#videoModal"]')
    .forEach(function (b) {
      b.addEventListener("click", prepararSonido);
    });
  const videoModal = byId("videoModal");
  if (videoModal) {
    videoModal.addEventListener("show.bs.modal", function () {
      const e = byId("camara-estado");
      if (e) {
        e.classList.remove("visible");
      }
      actualizarCamara();
    });
  }

  window.addEventListener("beforeunload", function (e) {
    if (enviando) {
      e.preventDefault();
      e.returnValue = "";
    }
  });

  if (!esRegistro) {
    return;
  }

  delegar(lista, "change", ".lote-check", function () {
    const f = buscarFila(codigoDe(this));
    if (f) {
      f.seleccionada = this.checked;
      actualizarBotones();
      guardar();
    }
  });

  delegar(lista, "click", ".lote-conexion-btn", function () {
    const f = buscarFila(codigoDe(this));
    const valor = this.dataset.valor;
    if (!f || f.conexion === valor) {
      return;
    }
    f.conexion = valor;
    f.conexionAerolinea = "";
    f.vuelo = null;
    renderFila(f, valor === "1" ? ".lote-conexion-aerolinea" : ".lote-vuelo");
  });

  delegar(lista, "change", ".lote-conexion-aerolinea", function () {
    const f = buscarFila(codigoDe(this));
    if (f) {
      f.conexionAerolinea = this.value;
      aviso("");
      f.vuelo = null;
      renderFila(f, ".lote-vuelo");
    }
  });

  delegar(lista, "click", ".lote-vuelo", function () {
    abrirVuelos([codigoDe(this)]);
  });

  byId("lote-asignar").addEventListener("click", function () {
    const codigos = filas
      .filter(function (f) {
        return f.seleccionada && f.estado !== "ok";
      })
      .map(function (f) {
        return f.codigo;
      });
    if (codigos.length) {
      abrirVuelos(codigos);
    }
  });

  document
    .querySelectorAll('input[name="lote-rango"], #lote-todos')
    .forEach(function (el) {
      el.addEventListener("change", function () {
        cacheVuelos = {};
      });
    });

  byId("vuelo-buscar").addEventListener("input", function () {
    const f = vueloDestino ? buscarFila(vueloDestino[0]) : null;
    if (!f) {
      return;
    }
    cargarVuelos(aerolineaEfectiva(f)).then(pintarVuelos, function () {});
  });

  delegar(byId("vuelo-lista"), "click", ".vuelo-opcion", function () {
    asignarVuelo({ id: this.dataset.id, texto: this.dataset.texto }, "");
  });

  const manualInput = byId("vuelo-manual-input");
  byId("vuelo-manual-usar").addEventListener("click", function () {
    const manual = manualInput.value
      .toUpperCase()
      .replace(/[^A-Z0-9 ]/g, "")
      .trim();
    if (!manual) {
      manualInput.focus();
      return;
    }
    asignarVuelo(null, manual);
  });
  manualInput.addEventListener("keydown", function (e) {
    if (e.key === "Enter") {
      e.preventDefault();
      byId("vuelo-manual-usar").click();
    }
  });
});
