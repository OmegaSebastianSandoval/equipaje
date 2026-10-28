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

  function esc(texto) {
    return $("<div>")
      .text(texto == null ? "" : String(texto))
      .html();
  }

  function aviso(texto, tipo) {
    const $a = $("#reclamo-aviso");
    $a.attr("class", "reclamo-aviso aviso-" + tipo).text(texto);
    clearTimeout(avisoTimer);
    avisoTimer = setTimeout(function () {
      $a.attr("class", "reclamo-aviso").text("");
    }, 6000);
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
        if (filas.some(function (f) { return f.codigo === codigo; })) {
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
    const cola = porEnviar();
    if (cola.length === 0 || enviando) {
      return;
    }
    enviando = true;
    render();
    let ok = 0;
    let fallidas = 0;
    let i = 0;

    function terminar() {
      enviando = false;
      aviso(
        "Reclamadas: " + ok + (fallidas ? " · Con error: " + fallidas + " (quedan en la lista para reintentar)" : ""),
        fallidas ? "error" : "ok"
      );
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
      if (i >= cola.length) {
        terminar();
        return;
      }
      const f = cola[i];
      i++;
      $("#reclamo-enviar").text("Reclamando " + i + " de " + cola.length + "…");
      f.estado = "enviando";
      f.mensaje = "";
      renderFila(f);
      $.ajax({
        url: "/page/index/reclamoAjax",
        method: "POST",
        data: { csrf: csrf, maleta_codigo: f.codigo },
        dataType: "json",
        timeout: 300000,
      })
        .done(function (res) {
          if (res.status === "ok") {
            f.estado = "ok";
            ok++;
          } else {
            f.estado = "error";
            f.mensaje = res.mensaje || "Error";
            fallidas++;
          }
        })
        .fail(function () {
          f.estado = "error";
          f.mensaje = "Error de conexión, intenta de nuevo";
          fallidas++;
        })
        .always(function () {
          renderFila(f);
          siguiente();
        });
    }
    siguiente();
  });
});
