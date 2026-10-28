// Reclamo publico de maletas por lotes (/page/index/reclamos).
// Se agregan varias maletas a una lista y se reclaman una por una (cada reclamo envia correo a la aerolinea).
$(document).ready(function () {
  const $codigo = $("#maleta_codigo");
  if (!$codigo.length || !$("#reclamo-lista").length) {
    return;
  }
  // Misma cookie que crea el login de administracion; al recargar, bootstrap.php inicia la sesion
  $("#reclamo-consentimiento").on("click", function () {
    const fechaExpiracion = new Date();
    fechaExpiracion.setFullYear(fechaExpiracion.getFullYear() + 1);
    document.cookie = "consentimiento=aceptado; expires=" + fechaExpiracion.toUTCString() + "; path=/; SameSite=Lax";
    window.location.reload();
  });
  const csrf = $("#reclamo-csrf").val();
  if (!csrf) {
    return;
  }
  let filas = [];
  let enviando = false;
  let avisoTimer = null;
  // Maximo de maletas por lote (lo define el controlador)
  const MAX_LOTE = parseInt($("#reclamo-max").val(), 10) > 0 ? parseInt($("#reclamo-max").val(), 10) : 30;
  // Tiempo maximo por maleta: el correo tiene 30 s de limite, con margen
  const TIEMPO_POR_MALETA = 90000;
  let detener = false;
  let continuarPausa = null; // envio en pausa esperando conexion

  function esc(texto) {
    return $("<div>")
      .text(texto == null ? "" : String(texto))
      .html();
  }

  // fijo: el aviso no se oculta solo (resultados importantes del envio)
  function aviso(texto, tipo, fijo) {
    const $a = $("#reclamo-aviso");
    clearTimeout(avisoTimer);
    if (!texto) {
      $a.attr("class", "reclamo-aviso").text("");
      return;
    }
    $a.attr("class", "reclamo-aviso aviso-" + tipo).text(texto);
    if (!fijo) {
      avisoTimer = setTimeout(function () {
        $a.attr("class", "reclamo-aviso").text("");
      }, 6000);
    }
  }

  function loteLleno(codigo) {
    if (porEnviar().length < MAX_LOTE) {
      return false;
    }
    aviso("Máximo " + MAX_LOTE + " maletas por lote. Reclama estas primero y luego sigue con " + codigo + ".", "error");
    return true;
  }

  // ---------------------------------------------------------------
  // Captura del codigo (pistola, camara o manual)
  // ---------------------------------------------------------------
  $codigo.on("input", function () {
    const valor = $(this).val().replace(/[^0-9]/g, "").substring(0, 10);
    $(this).val(valor);
    if (valor.length === 10) {
      agregar(valor);
    }
  });
  // La camara (main.js) escribe el codigo y dispara change; la pistola suele terminar con Enter
  $codigo.on("change", function () {
    agregar($(this).val());
  });
  $codigo.on("keydown", function (e) {
    if (e.key === "Enter") {
      e.preventDefault();
      agregar($(this).val());
    }
  });

  function agregar(codigo) {
    codigo = String(codigo || "").replace(/[^0-9]/g, "");
    if (codigo === "" || enviando) {
      return;
    }
    $codigo.val("");
    if (codigo.length !== 10) {
      aviso("El código " + codigo + " debe tener 10 dígitos.", "error");
      return;
    }
    if (filas.some(function (f) { return f.codigo === codigo; })) {
      aviso("La maleta " + codigo + " ya está en la lista.", "error");
      return;
    }
    if (loteLleno(codigo)) {
      return;
    }
    $.ajax({
      url: "/page/index/getBag",
      method: "POST",
      data: { code: codigo },
      dataType: "json",
    })
      .done(function (data) {
        if (data.status !== "success") {
          aviso("La maleta " + codigo + " no está registrada.", "error");
          return;
        }
        if (data.maleta_estado !== "" && data.maleta_estado !== null) {
          aviso("La maleta " + codigo + " ya fue reclamada.", "error");
          return;
        }
        if (filas.some(function (f) { return f.codigo === codigo; }) || loteLleno(codigo)) {
          return;
        }
        filas.unshift({
          codigo: codigo,
          estado: "pendiente",
          mensaje: "",
          aerolinea: data.aerolinea || "",
          over: data.over_tipo ? "Over " + data.over_tipo : "",
          vuelo: data.vuelo || "—",
          salida: data.salida || "",
          tiempo: data.tiempo_restante || "",
          urgencia: data.urgencia || "ninguna",
        });
        aviso("Maleta " + codigo + " agregada" + (data.aerolinea ? " (" + data.aerolinea + ")" : "") + ".", "ok");
        render();
      })
      .fail(function () {
        aviso("No se pudo consultar la maleta " + codigo + ", intenta de nuevo.", "error");
      });
  }

  // ---------------------------------------------------------------
  // Lista
  // ---------------------------------------------------------------
  function iconoEstado(f) {
    if (f.estado === "enviando") {
      return '<i class="fa-solid fa-spinner fa-spin reclamo-estado" title="Enviando"></i>';
    }
    if (f.estado === "ok") {
      return '<i class="fa-solid fa-circle-check reclamo-estado text-success" title="Reclamada"></i>';
    }
    if (f.estado === "error") {
      return '<i class="fa-solid fa-triangle-exclamation reclamo-estado text-danger" title="' + esc(f.mensaje) + '"></i>';
    }
    return "";
  }

  function htmlFila(f) {
    let clases = "reclamo-item";
    if (f.estado === "error") {
      clases += " reclamo-item-error";
    } else if (f.estado === "ok") {
      clases += " reclamo-item-ok";
    }
    let detalle = "";
    if (f.vuelo && f.vuelo !== "—") {
      detalle =
        '<div class="reclamo-detalle">' +
        '<span><i class="fa-solid fa-plane-departure"></i> ' + esc(f.vuelo) + "</span>" +
        (f.salida ? "<span>Salida: " + esc(f.salida) + "</span>" : "") +
        (f.tiempo && f.tiempo !== "—" ? '<span class="reclamo-urgencia-' + esc(f.urgencia) + '">' + esc(f.tiempo) + "</span>" : "") +
        "</div>";
    }
    return (
      '<div class="' + clases + '" data-codigo="' + esc(f.codigo) + '">' +
      '<div class="reclamo-item-top">' +
      '<div class="reclamo-info">' +
      '<strong class="reclamo-codigo">' + esc(f.codigo) + "</strong>" +
      '<span class="reclamo-aerolinea">' + esc(f.aerolinea) + (f.over ? " · " + esc(f.over) : "") + "</span>" +
      "</div>" +
      iconoEstado(f) +
      '<button type="button" class="reclamo-quitar" title="Quitar" aria-label="Quitar ' + esc(f.codigo) + '"' +
      (enviando ? " disabled" : "") + '><i class="fa-solid fa-xmark"></i></button>' +
      "</div>" +
      detalle +
      (f.mensaje && f.estado !== "ok" ? '<div class="reclamo-mensaje">' + esc(f.mensaje) + "</div>" : "") +
      "</div>"
    );
  }

  function render() {
    const $lista = $("#reclamo-lista");
    $lista.find(".reclamo-item").remove();
    $("#reclamo-vacio").toggle(filas.length === 0);
    $lista.append(filas.map(htmlFila).join(""));
    actualizarBotones();
  }

  function renderFila(f) {
    $('#reclamo-lista .reclamo-item[data-codigo="' + f.codigo + '"]').replaceWith(htmlFila(f));
  }

  function porEnviar() {
    return filas.filter(function (f) { return f.estado !== "ok"; });
  }

  function actualizarBotones() {
    const cola = porEnviar();
    $("#reclamo-enviar")
      .prop("disabled", enviando || cola.length === 0)
      .text(cola.length > 1 ? "Reclamar todas (" + cola.length + ")" : "Reclamar");
    $("#reclamo-limpiar").prop("disabled", enviando || filas.length === 0);
  }

  $("#reclamo-lista").on("click", ".reclamo-quitar", function () {
    if (enviando) {
      return;
    }
    const codigo = String($(this).closest(".reclamo-item").data("codigo"));
    filas = filas.filter(function (f) { return f.codigo !== codigo; });
    render();
  });

  $("#reclamo-limpiar").on("click", function () {
    if (enviando) {
      return;
    }
    filas = [];
    render();
    $codigo.trigger("focus");
  });

  // ---------------------------------------------------------------
  // Envio (una por una)
  // ---------------------------------------------------------------
  $("#reclamo-enviar").on("click", function () {
    let cola = porEnviar();
    if (cola.length === 0 || enviando) {
      return;
    }
    cola = cola.slice(0, MAX_LOTE);
    enviando = true;
    detener = false;
    $("#reclamo-detener").prop("hidden", false).prop("disabled", false).text("Detener");
    render();
    let ok = 0;
    let fallidas = 0;
    let i = 0;
    let fin = ""; // "", "detenido" o "sesion"
    const yaEstaban = []; // se cortaron en un intento anterior pero si quedaron reclamadas

    function terminar() {
      enviando = false;
      continuarPausa = null;
      $("#reclamo-detener").prop("hidden", true);
      const pendientes = filas.filter(function (f) { return f.estado === "pendiente"; }).length;
      let texto = "Reclamadas: " + ok;
      if (fin === "sesion") {
        texto = "Sesión vencida: recarga la página. Las maletas pendientes siguen en la lista. " + texto;
      } else if (fin === "detenido") {
        texto = "Envío detenido. " + texto + " · Sin enviar: " + pendientes + " (siguen en la lista)";
      }
      if (fallidas) {
        texto += " · Con error: " + fallidas + " (quedan en la lista para reintentar)";
      }
      if (yaEstaban.length) {
        texto += ". " + (yaEstaban.length === 1 ? "La maleta " : "Las maletas ") + yaEstaban.join(", ") +
          " ya estaba" + (yaEstaban.length === 1 ? " reclamada" : "n reclamadas") +
          " de un intento anterior que se cortó: confirma que la aerolínea recibió el aviso.";
      }
      const esError = fallidas > 0 || fin === "sesion" || yaEstaban.length > 0;
      // Los avisos importantes no se ocultan solos
      aviso(texto, esError ? "error" : "ok", esError || fin !== "");
      render();
      setTimeout(function () {
        if (enviando) {
          return;
        }
        filas = filas.filter(function (f) { return f.estado !== "ok"; });
        render();
      }, 1500);
      $codigo.trigger("focus");
    }

    function siguiente() {
      if (detener) {
        fin = "detenido";
      }
      if (fin || i >= cola.length) {
        terminar();
        return;
      }
      if (navigator.onLine === false) {
        esperarConexion(siguiente);
        return;
      }
      const f = cola[i];
      $("#reclamo-enviar").text("Reclamando " + (i + 1) + " de " + cola.length + "…");
      f.estado = "enviando";
      f.mensaje = "";
      renderFila(f);
      let repetir = false;
      $.ajax({
        url: "/page/index/reclamoAjax",
        method: "POST",
        data: { csrf: csrf, maleta_codigo: f.codigo },
        dataType: "json",
        timeout: TIEMPO_POR_MALETA,
      })
        .done(function (res) {
          if (res.status === "ok") {
            f.estado = "ok";
            ok++;
          } else if (res.codigo === "existe" && f.intentos) {
            // Un intento anterior se corto pero el servidor si la reclamo
            f.estado = "ok";
            ok++;
            yaEstaban.push(f.codigo);
          } else if (res.codigo === "sesion") {
            f.estado = "pendiente";
            fin = "sesion";
          } else {
            f.estado = "error";
            f.mensaje = res.mensaje || "Error";
            fallidas++;
          }
        })
        .fail(function (xhr, estado) {
          // El servidor pudo reclamarla aunque la respuesta no llego
          f.intentos = (f.intentos || 0) + 1;
          if (navigator.onLine === false) {
            // Se cayo la red: se repite esta misma maleta cuando vuelva
            f.estado = "pendiente";
            repetir = true;
          } else {
            f.estado = "error";
            f.mensaje = estado === "timeout"
              ? "El servidor tardó demasiado. Reintenta: si sí quedó reclamada se marcará sola."
              : "Error de conexión, intenta de nuevo";
            fallidas++;
          }
        })
        .always(function () {
          if (!repetir) {
            i++;
          }
          renderFila(f);
          siguiente();
        });
    }
    siguiente();
  });

  // Detiene el lote al terminar la maleta en curso (o de inmediato si esta en pausa)
  $("#reclamo-detener").on("click", function () {
    detener = true;
    $(this).prop("disabled", true).text(continuarPausa ? "Deteniendo…" : "Deteniendo… termina la actual");
    reanudar();
  });

  function esperarConexion(continuar) {
    continuarPausa = continuar;
    $("#reclamo-enviar").text("Sin conexión, esperando señal…");
    aviso("Sin conexión. El envío sigue solo cuando vuelva la señal.", "error", true);
    const alVolver = function () {
      window.removeEventListener("online", alVolver);
      reanudar();
    };
    window.addEventListener("online", alVolver);
  }

  function reanudar() {
    const continuar = continuarPausa;
    continuarPausa = null;
    if (continuar) {
      aviso("", "ok");
      continuar();
    }
  }
});
