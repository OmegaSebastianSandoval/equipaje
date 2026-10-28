// Resalta las maletas cuyo vuelo esta por salir (o ya salio) sin ser reclamadas
function claseUrgencia(urgencia) {
  if (urgencia === "alta" || urgencia === "salio") {
    return "table-danger";
  }
  if (urgencia === "media") {
    return "table-warning";
  }
  return "";
}

$(document).ready(function () {
  const intervalTime = 10000; // 10 segundos
  let currentPage = 0;
  let baggageList = [];

  function fetchBaggage() {
    $.ajax({
      url: "/page/index/getNoReclamadas",
      method: "post",
      dataType: "json",
      success: function (response) {
        if (response.status === "success") {
          baggageList = response.data; // Ajusta según la estructura de tu respuesta
          currentPage = 0;
          displayPage();
        } else {
          console.error("Error fetching data");
        }
      },
      error: function () {
        console.error("Error fetching data");
      },
    });
  }

  function displayPage() {
    const itemsPerPage = Math.floor($(window).height() / 60) - 2; // Asume 50px por fila
    const start = currentPage * itemsPerPage;
    const end = start + itemsPerPage;
    const pageItems = baggageList.slice(start, end);

    $("#baggageTbody").empty();
    pageItems.forEach((item) => {
      $("#baggageTbody").append(`
                <tr class="${claseUrgencia(item.urgencia)}">
                    <td>${item.maleta_codigo}</td>
                    <td>${item.aerolinea_imagen ? `<img src="/images/${item.aerolinea_imagen}" alt="">` : item.aerolinea_nombre}</td>
                    <td>${item.vuelo !== "—" ? `<strong>${item.vuelo}</strong>` : item.vuelo}</td>
                    <td>${item.salida || "—"}</td>
                    <td>${item.tiempo_restante}</td>
                    <td><h5><span class="badge text-bg-${item.maleta_color}">${item.maleta_estado}</span></h5></td>
                    <td>${item.tiempo_espera}</td>
                    <td class="fecha">${item.maleta_fingreso}</td>
                    <td>${item.maleta_hingreso}</td>
                </tr>
            `);
    });

    currentPage++;
    if (start + itemsPerPage >= baggageList.length) {
      currentPage = 0;
      setTimeout(fetchBaggage, intervalTime);
    } else {
      setTimeout(displayPage, intervalTime);
    }
  }

  fetchBaggage();

  // REGION OVER NUEVO

  // **************************************
  // OVER 1 INICIO
  // **************************************
  let currentPageOverUno = 0;
  let baggageListOverUno = [];

  function fetchBaggageOverUno() {
    $.ajax({
      url: "/page/index/getNoReclamadasOverUno",
      method: "post",
      dataType: "json",
      success: function (response) {
        if (response.status === "success") {
          baggageListOverUno = response.data; // Ajusta según la estructura de tu respuesta
          currentPageOverUno = 0;
          displayPageOverUno();
        } else {
          console.error("Error fetching data");
        }
      },
      error: function () {
        console.error("Error fetching data");
      },
    });
  }
  function displayPageOverUno() {
    const itemsPerPage = Math.floor($(window).height() / 60) - 2; // Asume 50px por fila
    const start = currentPageOverUno * itemsPerPage;
    const end = start + itemsPerPage;
    const pageItemsOverUno = baggageListOverUno.slice(start, end);

    $("#baggageTbodyOverUno").empty();
    pageItemsOverUno.forEach((item) => {
      $("#baggageTbodyOverUno").append(`
                <tr class="${claseUrgencia(item.urgencia)}">
                    <td>${item.maleta_codigo}</td>
                    <td>${item.aerolinea_imagen ? `<img src="/images/${item.aerolinea_imagen}" alt="">` : item.aerolinea_nombre}</td>
                    <td>${item.vuelo !== "—" ? `<strong>${item.vuelo}</strong>` : item.vuelo}</td>
                    <td>${item.salida || "—"}</td>
                    <td>${item.tiempo_restante}</td>
                    <td><h5><span class="badge text-bg-${item.maleta_color}">${item.maleta_estado}</span></h5></td>
                    <td>${item.tiempo_espera}</td>
                    <td class="fecha">${item.maleta_fingreso}</td>
                    <td>${item.maleta_hingreso}</td>
                      <td>${item.maleta_over_tipo}</td>
                </tr>
            `);
    });

    currentPageOverUno++;
    if (start + itemsPerPage >= baggageListOverUno.length) {
      currentPageOverUno = 0;
      setTimeout(fetchBaggageOverUno, intervalTime);
    } else {
      setTimeout(displayPageOverUno, intervalTime);
    }
  }

  fetchBaggageOverUno();

  // **************************************
  // OVER 1 FIN
  // **************************************

  // **************************************
  // OVER 2 INICIO
  // **************************************
  let currentPageOverDos = 0;
  let baggageListOverDos = [];

  function fetchBaggageOverDos() {
    $.ajax({
      url: "/page/index/getNoReclamadasOverDos",
      method: "post",
      dataType: "json",
      success: function (response) {
        if (response.status === "success") {
          baggageListOverDos = response.data; // Ajusta según la estructura de tu respuesta
          currentPageOverDos = 0;
          displayPageOverDos();
        } else {
          console.error("Error fetching data");
        }
      },
      error: function () {
        console.error("Error fetching data");
      },
    });
  }
  function displayPageOverDos() {
    const itemsPerPage = Math.floor($(window).height() / 60) - 2; // Asume 50px por fila
    const start = currentPageOverDos * itemsPerPage;
    const end = start + itemsPerPage;
    const pageItemsOverDos = baggageListOverDos.slice(start, end);

    $("#baggageTbodyOverDos").empty();
    pageItemsOverDos.forEach((item) => {
      $("#baggageTbodyOverDos").append(`
                <tr class="${claseUrgencia(item.urgencia)}">
                    <td>${item.maleta_codigo}</td>
                    <td>${item.aerolinea_imagen ? `<img src="/images/${item.aerolinea_imagen}" alt="">` : item.aerolinea_nombre}</td>
                    <td>${item.vuelo !== "—" ? `<strong>${item.vuelo}</strong>` : item.vuelo}</td>
                    <td>${item.salida || "—"}</td>
                    <td>${item.tiempo_restante}</td>
                    <td><h5><span class="badge text-bg-${item.maleta_color}">${item.maleta_estado}</span></h5></td>
                    <td>${item.tiempo_espera}</td>
                    <td class="fecha">${item.maleta_fingreso}</td>
                    <td>${item.maleta_hingreso}</td>
                    <td>${item.maleta_over_tipo}</td>
                </tr>
            `);
    });

    currentPageOverDos++;
    if (start + itemsPerPage >= baggageListOverDos.length) {
      currentPageOverDos = 0;
      setTimeout(fetchBaggageOverDos, intervalTime);
    } else {
      setTimeout(displayPageOverDos, intervalTime);
    }
  }

  fetchBaggageOverDos();

  // **************************************
  // OVER 2 FIN
  // **************************************

  // **************************************
  // OVER 3 INICIO
  // **************************************
  let currentPageOverTres = 0;
  let baggageListOverTres = [];

  function fetchBaggageOverTres() {
    $.ajax({
      url: "/page/index/getNoReclamadasOverTres",
      method: "post",
      dataType: "json",
      success: function (response) {
        if (response.status === "success") {
          baggageListOverTres = response.data; // Ajusta según la estructura de tu respuesta
          currentPageOverTres = 0;
          displayPageOverTres();
        } else {
          console.error("Error fetching data");
        }
      },
      error: function () {
        console.error("Error fetching data");
      },
    });
  }
  function displayPageOverTres() {
    const itemsPerPage = Math.floor($(window).height() / 60) - 2; // Asume 50px por fila
    const start = currentPageOverTres * itemsPerPage;
    const end = start + itemsPerPage;
    const pageItemsOverTres = baggageListOverTres.slice(start, end);

    $("#baggageTbodyOverTres").empty();
    pageItemsOverTres.forEach((item) => {
      $("#baggageTbodyOverTres").append(`
                <tr class="${claseUrgencia(item.urgencia)}">
                    <td>${item.maleta_codigo}</td>
                    <td>${item.aerolinea_imagen ? `<img src="/images/${item.aerolinea_imagen}" alt="">` : item.aerolinea_nombre}</td>
                    <td>${item.vuelo !== "—" ? `<strong>${item.vuelo}</strong>` : item.vuelo}</td>
                    <td>${item.salida || "—"}</td>
                    <td>${item.tiempo_restante}</td>
                    <td><h5><span class="badge text-bg-${item.maleta_color}">${item.maleta_estado}</span></h5></td>
                    <td>${item.tiempo_espera}</td>
                    <td class="fecha">${item.maleta_fingreso}</td>
                    <td>${item.maleta_hingreso}</td>
                    <td>${item.maleta_over_tipo}</td>
                </tr>
            `);
    });

    currentPageOverTres++;
    if (start + itemsPerPage >= baggageListOverTres.length) {
      currentPageOverTres = 0;
      setTimeout(fetchBaggageOverTres, intervalTime);
    } else {
      setTimeout(displayPageOverTres, intervalTime);
    }
  }

  fetchBaggageOverTres();

  // **************************************
  // OVER 3 FIN
  // **************************************
});
// El reclamo de maletas (#maleta_codigo) se maneja en reclamos.js

document.addEventListener("DOMContentLoaded", function () {
  var videoModal = document.getElementById("videoModal");

  // Evento que se dispara cuando el modal se está mostrando
  videoModal?.addEventListener("show.bs.modal", function () {
    startVideoStream();
  });

  // Evento que se dispara cuando el modal se cierra
  videoModal?.addEventListener("hidden.bs.modal", function () {
    stopVideoStream();
  });

  function startVideoStream() {
    if (navigator.mediaDevices && navigator.mediaDevices.getUserMedia) {
      navigator.mediaDevices
        .getUserMedia({ video: { facingMode: "environment" } })
        .then(function (stream) {
          var videoElement = document.getElementById("video");
          videoElement.srcObject = stream;
          startBarcodeReader(videoElement);
        })
        .catch(function (error) {
          console.error("Error accessing the camera: ", error);
          alert("Error accessing the camera: " + error.message);
        });
    } else {
      alert("Your browser does not support media devices.");
      return;
    }
  }

  function stopVideoStream() {
    var videoElement = document.getElementById("video");
    if (videoElement.srcObject) {
      videoElement.srcObject.getTracks().forEach((track) => track.stop());
      videoElement.srcObject = null;
    }
  }

  function startBarcodeReader(videoElement) {
    Quagga.init(
      {
        inputStream: {
          name: "Live",
          type: "LiveStream",
          target: videoElement,
        },
        decoder: {
          readers: [
            "code_128_reader",
            "ean_reader",
            "ean_8_reader",
            "code_39_reader",
            "code_39_vin_reader",
            "codabar_reader",
            "upc_reader",
            "upc_e_reader",
            "i2of5_reader",
          ],
        },
      },
      function (err) {
        if (err) {
          console.error("Error al inicializar Quagga: ", err);
          return;
        }
        Quagga.start();
      },
    );

    var lastCode = null;
    var repeatCount = 0;

    Quagga.onDetected(function (data) {
      var code = data.codeResult.code;
      if (code === lastCode) {
        repeatCount++;
        if (repeatCount >= 3) {
          // Asegura que el mismo código ha sido leído consecutivamente 3 veces
          document.getElementById("maleta_codigo").value = code; // Asignar el código al input
          // console.log("Código IATA detectado con baja tasa de error:", code);
          $("#videoModal").modal("hide"); // Cerrar el modal
          $("#maleta_codigo").change(); // Disparar el evento change para que se ejecute la lógica de la aerolínea
          Quagga.stop(); // Detener Quagga al cerrar el modal
        }
      } else {
        lastCode = code;
        repeatCount = 0;
      }
    });
    videoModal.addEventListener("hidden.bs.modal", function () {
      if (Quagga) {
        Quagga.stop(); // Asegúrate de que Quagga se detenga cuando el modal se oculte
      }
      stopVideoStream(); // Detiene el flujo de video
    });
  }
});

$(document).ready(function () {
  $("#login").on("submit", function (e) {
    e.preventDefault();
    if ($("#terms").is(":checked") == false) {
      alert("Debe aceptar los terminos y condiciones para poder continuar.");
      return false;
    }
    let data = $(this).serialize();
    $.ajax({
      url: $(this).attr("action"),
      type: $(this).attr("method"),
      data: data,
      dataType: "json",
      success: function (response) {
        if (response.status == "success") {
          window.location.href = "/page/index/reclamos";
        } else {
          Swal.fire({
            icon: "error",
            title: "Error",
            text: "Usuario o contraseña incorrectos, por favor intente nuevamente.",
          });
        }
      },
    });
  });
});
var videos = [];
$(document).ready(function () {
  $(".dropdown-toggle").dropdown();
  $(".carouselsection").carousel({
    quantity: 4,
    sizes: {
      900: 3,
      500: 1,
    },
  });

  $(".banner-video-youtube").each(function () {
    // console.log($(this).attr('data-video'));
    const datavideo = $(this).attr("data-video");
    const idvideo = $(this).attr("id");
    const playerDefaults = {
      autoplay: 0,
      autohide: 1,
      modestbranding: 0,
      rel: 0,
      showinfo: 0,
      controls: 0,
      disablekb: 1,
      enablejsapi: 0,
      iv_load_policy: 3,
    };
    const video = {
      videoId: datavideo,
      suggestedQuality: "hd1080",
    };
    videos[videos.length] = new YT.Player(idvideo, {
      videoId: datavideo,
      playerVars: playerDefaults,
      events: {
        onReady: onAutoPlay,
        onStateChange: onFinish,
      },
    });
  });

  function onAutoPlay(event) {
    event.target.playVideo();
    event.target.mute();
  }

  function onFinish(event) {
    if (event.data === 0) {
      event.target.playVideo();
    }
  }
  const tooltipTriggerList = document.querySelectorAll(
    '[data-bs-toggle="tooltip"]',
  );
  const tooltipList = [...tooltipTriggerList].map(
    (tooltipTriggerEl) => new bootstrap.Tooltip(tooltipTriggerEl),
  );
});
