
$(document).ready(function () {
  $('#connection-select').on('change', function (){
    if($(this).val() == '1'){
      $('#connection').show(300);
      // Hacer requerido al campo
      $('#connection select').attr('required', true);
    }else{
      $('#connection').hide(300);
      // Quitar requerido al campo
      $('#connection select').attr('required', false);
    }
  })
  $('#maleta_codigo_reclamo').on('input', function () {
    // Remueve cualquier carácter que no sea numérico y limita la longitud a 10
    var valor = $(this).val().replace(/[^0-9]/g, '');
    if (valor.length > 10) {
      valor = valor.substring(0, 10);
    }
    $(this).val(valor);
  });
  $('#maleta_codigo_reclamo').on('change', function () {
    let codigo = $(this).val();
    //Obtener aerolinea de los ultimos digitos 2 al 4
    let aerolinea = codigo.substring(1, 4);
    console.log(aerolinea);
    getAirlineReclamo(aerolinea);
    getBagReclamo(codigo);
  });
  function getAirlineReclamo($code) {
    $.ajax({
      url: '/page/index/getAirline',
      method: 'POST',
      data: {
        code: $code
      },
      dataType: 'json',
      success: function (data) {
        if (data.status == 'success') {
          $('#maleta_aerolinea').val(data.airline_code);
          $('#maleta_aerolinea_name').val(data.airline_name);
          $('.registrar').attr('disabled', false);
          $('#no_ariline').hide();
        } else {
          $('#maleta_aerolinea').val('');
          $('#maleta_aerolinea_name').val('');
          $('#no_ariline').show(300);
          $('.registrar').attr('disabled', true);
        }
      }
    })
  }
  function getBagReclamo($code) {
    $.ajax({
      url: '/page/index/getBag',
      method: 'POST',
      data: {
        code: $code
      },
      dataType: 'json',
      success: function (data) {
        if (data.status == 'success') {
          $('#maleta_estado').val(data.maleta_estado);
          if(data.maleta_estado != 1 && data.maleta_estado != 2){
            $('.registrar').attr('disabled', false);
          }else if(data.maleta_estado == 1){
            $('.registrar').attr('disabled', true);
          }
        } else {
          $('#maleta_estado').val('2');
          $('.registrar').attr('disabled', true);
        }
      }
    })
  }

  $('#maleta_codigo').on('input', function () {
    // Remueve cualquier carácter que no sea numérico y limita la longitud a 10
    var valor = $(this).val().replace(/[^0-9]/g, '');
    if (valor.length > 10) {
      valor = valor.substring(0, 10);
    }
    $(this).val(valor);
  });
  $('#maleta_codigo').on('change', function () {
    let codigo = $(this).val();
    //Obtener aerolinea de los ultimos digitos 2 al 4
    let aerolinea = codigo.substring(1, 4);
    console.log(aerolinea);
    getAirline(aerolinea, codigo);
  });
  function getAirline($code, full_code) {
    $.ajax({
      url: '/administracion/maletas/getAirline',
      method: 'POST',
      data: {
        code: $code,
        full_code: full_code
      },
      dataType: 'json',
      success: function (data) {
        if (data.status == 'success') {
          $('#maleta_aerolinea').val(data.airline_code);
          $('#maleta_aerolinea_name').val(data.airline_name);
          $('.registrar').attr('disabled', false);
          $('#ext').hide();
          $('#no_ariline').hide();
        }else if(data.status == 'ext'){
          $('#maleta_aerolinea').val('');
          $('#maleta_aerolinea_name').val('');
          $('#no_ariline').hide();
          $('#ext').show(300);
          $('.registrar').attr('disabled', true);
        } else {
          $('#maleta_aerolinea').val('');
          $('#maleta_aerolinea_name').val('');
          $('#ext').hide();
          $('#no_ariline').show(300);
          $('.registrar').attr('disabled', true);
        }
      }
    })
  }
})


document.addEventListener('DOMContentLoaded', function () {
  var videoModal = document.getElementById('videoModal');

  // Evento que se dispara cuando el modal se está mostrando
  videoModal.addEventListener('show.bs.modal', function () {
    startVideoStream();
  });

  // Evento que se dispara cuando el modal se cierra
  videoModal.addEventListener('hidden.bs.modal', function () {
    stopVideoStream();
  });

  function startVideoStream() {
    if (navigator.mediaDevices && navigator.mediaDevices.getUserMedia) {
      navigator.mediaDevices.getUserMedia({ video: { facingMode: "environment" } })
        .then(function (stream) {
          var videoElement = document.getElementById('video');
          videoElement.srcObject = stream;
          startBarcodeReader(videoElement);
        }).catch(function (error) {
          console.error("Error accessing the camera: ", error);
          alert('Error accessing the camera: ' + error.message);
        });
    } else {
      alert('Your browser does not support media devices.');
      return;
    }
  }

  function stopVideoStream() {
    var videoElement = document.getElementById('video');
    if (videoElement.srcObject) {
      videoElement.srcObject.getTracks().forEach(track => track.stop());
      videoElement.srcObject = null;
    }
  }

  function startBarcodeReader(videoElement) {
    Quagga.init({
      inputStream: {
        name: "Live",
        type: "LiveStream",
        target: videoElement
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
          "i2of5_reader"
        ],
      }
    }, function (err) {
      if (err) {
        console.error("Error al inicializar Quagga: ", err);
        return;
      }
      Quagga.start();
    });

    var lastCode = null;
    var repeatCount = 0;

    Quagga.onDetected(function (data) {
      var code = data.codeResult.code;
      if (code === lastCode) {
        repeatCount++;
        if (repeatCount >= 3) { // Asegura que el mismo código ha sido leído consecutivamente 3 veces
          console.log(code);
          if(document.getElementById('maleta_codigo')){
            document.getElementById('maleta_codigo').value = code; // Asignar el código al input
          }
          if(document.getElementById('maleta_codigo_reclamo')){
            document.getElementById('maleta_codigo_reclamo').value = code; // Asignar el código al input
          }
          // console.log("Código IATA detectado con baja tasa de error:", code);
          $('#videoModal').modal('hide'); // Cerrar el modal
          $('#maleta_codigo').change(); // Disparar el evento change para que se ejecute la lógica de la aerolínea
          $('#maleta_codigo_reclamo').change(); // Disparar el evento change para que se ejecute la lógica de la aerolínea
          Quagga.stop(); // Detener Quagga al cerrar el modal
        }
      } else {
        lastCode = code;
        repeatCount = 0;
      }
    });
    videoModal.addEventListener('hidden.bs.modal', function () {
      if (Quagga) {
        Quagga.stop(); // Asegúrate de que Quagga se detenga cuando el modal se oculte
      }
      stopVideoStream(); // Detiene el flujo de video
    });

  }
});


$(document).ready(function () {
  tinymce.init({
    selector: 'textarea.tinyeditor',  // change this value according to your HTML
    plugins: [
      'advlist', 'autolink', 'lists', 'link', 'image', 'charmap', 'preview',
      'anchor', 'searchreplace', 'visualblocks', 'code', 'fullscreen',
      'insertdatetime', 'media', 'table', 'help', 'wordcount', 'code', 'file-manager'
    ],
    toolbar: 'undo redo | blocks | ' +
      'bold italic backcolor | alignleft aligncenter ' +
      'alignright alignjustify | bullist numlist outdent indent | ' +
      'removeformat | Upload Flmngr ImgPen | code | help ',
    Flmngr: {
      urlFileManager: '/components/flmngr/flmngr.php',
      urlFiles: '/upload',
      acceptextensions: ["zip", "psd", "html", "doc", "xml", "pdf", "js", "txt"]
    },
    image_advtab: true,
    a_plugin_option: true,
    language: 'es',
    a_configuration_option: 400,
    browser_spellcheck: true,
    contextmenu: false,
    skin: 'oxide-dark',
    content_css: 'tinymce-5',
  });
  $('.deletePolDoc').on('click', function () {
    let id = $(this).attr('data-id');
    $.ajax({
      url: '/administracion/politicas/deletedocument',
      method: 'POST',
      data: {
        id: id
      },
      dataType: 'json',
      success: function (data) {
        if (data == 'ok') {
          $('#doc_' + id).remove();
        }
      }
    })
  })
  $(function () {
    $('#fecha_tipo_bloqueo').on('change', function () {
      let _val = $(this).val()
      if (_val == '1') {
        $('#fecha_ciudad').show(300)
        $('#fecha_empleado').val('')
        $('#fecha_empleado').hide(300)
      } else if (_val == '2') {
        $('#fecha_empleado').show(300)
        $('#fecha_ciudad').val('')
        $('#fecha_ciudad').hide(300)
      } else if (_val == '3') {
        $('#fecha_empleado').val('')
        $('#fecha_empleado').hide(300)
        $('#fecha_ciudad').val('')
        $('#fecha_ciudad').hide(300)
      }
    })
  })
  // tinyMCE.init({
  //   mode: "specific_textareas",
  //   editor_selector: "tinyeditor",
  //   theme: "modern",
  //   // color_picker_callback: function (callback, value) {
  //   //   callback('#FF0000');
  //   // },
  //   block_formats: 'Parrafo=p;Titulo 1=h2;Titulo 2=h3;Titulo 3=h4;Titulo 4=h5',
  //   language_url: "/scripts/tinymce/langs/es.js",
  //   language: "es",
  //   plugins: "contextmenu,textcolor,colorpicker,link ,responsivefilemanager, table ,visualblocks,code,paste,image, charmap, print, preview, anchor,advlist,media, table, contextmenu, paste, lists ",
  //   external_filemanager_path: "/scripts/tinymce/plugins/filemanager/",
  //   filemanager_title: "Responsive Filemanager",
  //   external_plugins: {
  //     "filemanager": "/scripts/tinymce/plugins/filemanager/plugin.min.js",
  //     "responsivefilemanager": "/scripts/tinymce/plugins/responsivefilemanager/plugin.min.js"
  //   },
  //   theme_modern_toolbar_location: "bottom",
  //   paste_auto_cleanup_on_paste: true,

  //   fontsize_formats: '12px 14px 16px 18px 20px 22px 24px 26px 28px 30px 32px 36px 38px 40px 45px 50px 55px 60px 65px 70px 75px',
  //   toolbar: "mybutton,|,formatselect,|,fontsizeselect,forecolor,|,bold,italic,underline,|,alignleft, aligncenter, alignright, alignjustify,bullist,numlist,|,link,unlink,image,media,responsivefilemanager,|,removeformat,code",
  //   menubar: false,
  //   resize: true,
  //   browser_spellcheck: true,
  //   statusbar: true,
  //   relative_urls: false,
  //   image_title: true,
  //   image_advtab: true,
  //   style_formats: [{
  //     title: 'Image Left',
  //     selector: 'img',
  //     styles: {
  //       'float': 'left',
  //       'margin': '0 10px 0 10px'
  //     }
  //   },
  //   {
  //     title: 'Image Right',
  //     selector: 'img',
  //     styles: {
  //       'float': 'right',
  //       'margin': '0 10px 0 10px'
  //     }
  //   }
  //   ],
  //   setup: function (editor) {
  //     editor.on('init', function (e) {
  //       editor.getDoc().body.style.fontSize = '16px';
  //     });
  //     editor.addButton('mybutton', {
  //       type: 'listbox',
  //       text: 'Tema Claro',
  //       icon: false,
  //       onselect: function (e) {
  //         editor.getWin().document.body.style.backgroundColor = this.value();
  //       },
  //       values: [{
  //         text: 'Tema Claro',
  //         value: "#FFFFFF"
  //       },
  //       {
  //         text: 'Tema Oscuro',
  //         value: "#333333"
  //       },
  //       ]
  //     });

  //   }
  // });
  $(".file-image").fileinput({
    maxFileSize: 10000,
    previewFileType: "image",
    allowedFileExtensions: ["jpg", "jpeg", "gif", "png", "ico"],
    browseClass: "btn  btn-verde",
    showUpload: false,
    showRemove: false,
    browseIcon: "<i class=\"fas fa-image\"></i> ",
    browseLabel: "Imagen",
    language: "es",
    dropZoneEnabled: false
  });

  $(".file-document").fileinput({
    maxFileSize: 2048,
    previewFileType: "image",
    browseLabel: "Archivo",
    browseClass: "btn  btn-cafe",
    allowedFileExtensions: ["pdf", "xlsx", "xls", "doc", "docx", "ico"],
    showUpload: false,
    showRemove: false,
    browseIcon: "<i class=\"fas fa-folder-open\"></i> ",
    language: "es",
    dropZoneEnabled: false
  });

  $(".file-robot").fileinput({
    maxFileSize: 2048,
    previewFileType: "image",
    allowedFileExtensions: ["txt", ".txt"],
    browseClass: "btn btn-success btn-file-robot",
    showUpload: false,
    showRemove: false,
    browseLabel: "Robot",
    browseIcon: "<i class=\"fas fa-robot\"></i> ",
    language: "es",
    dropZoneEnabled: false,
    showPreview: false
  });

  $(".file-sitemap").fileinput({
    maxFileSize: 2048,
    previewFileType: "image",
    allowedFileExtensions: ["xml", ".xml"],
    browseClass: "btn btn-success btn-file-sitemap",
    showUpload: false,
    showRemove: false,
    browseLabel: "SiteMap",
    browseIcon: "<i class=\"fas fa-sitemap\"></i> ",
    language: "es",
    dropZoneEnabled: false,
    showPreview: false
  });
  $('[ data-bs-toggle="tooltip"]').tooltip();
  $(".up_table,.down_table").click(function () {
    var row = $(this).parents("tr:first");
    var value = row.find("input").val();
    var content1 = row.find("input").attr("id");
    var content2 = 0;
    if ($(this).is(".up_table")) {
      if (row.prev().find("input").val() > 0) {
        row.find("input").val(row.prev().find("input").val());
        row.prev().find("input").val(value);
        content2 = row.prev().find("input").attr("id");
        row.insertBefore(row.prev());
      }
    } else {
      if (row.next().find("input").val() > 0) {
        row.find("input").val(row.next().find("input").val());
        row.next().find("input").val(value);
        content2 = row.next().find("input").attr("id");
        row.insertAfter(row.next());
      }
    }
    var route = $("#order-route").val();
    var csrf = $("#csrf").val();
    if (route != "") {
      $.post(route, {
        'csrf': csrf,
        'id1': content1,
        'id2': content2
      });
    }
  });


  $(".selectpagination").change(function () {
    var route = $("#page-route").val();
    var pages = $(this).val();
    $.post(route, {
      'pages': pages
    }, function () {
      location.reload();
    });
  });

  $(".changetheme").on("change", function () {
    var color = "#FFFFFF";

    var contenedor = $(this).attr("data-campo-tiny");
    if ($(this).val() == 1) {
      color = "#333333";
    }
    var editor = window.tinyMCE.get(contenedor);
    editor.getWin().document.body.style.backgroundColor = color;

  });
  $(".switch-form").bootstrapSwitch({
    "onText": "Si",
    "offText": "No"
  });
  // let text = document.getElementById('contenido_tipo').value;

  // function aparecercolumna() {
  //   let id_columna = document.getElementById('contenido_tipo').value;
  //   if (id_columna == '5' || id_columna == '6') {
  //     $(".no-colum").attr("style", "display:block!important")

  //   } else {

  //     $(".no-colum").attr("style", "display:none!important")

  //   }
  // }
  // $("#contenido_tipo").on("change", function () {
  //   var value = $(this).val();
  //   if (parseInt(value) == 1) {
  //     //Si es un banner
  //     $(".no-seccion").hide();
  //     $(".no-banner").hide();
  //     $(".no-contenido").hide();
  //     $(".si-banner").show();
  //   } else if (parseInt(value) == 2) {
  //     //Si es un Contenedor
  //     $(".no-seccion").hide();
  //     $(".no-banner").hide();
  //     $(".no-contenido").hide();
  //     $(".si-seccion").show();
  //   } else if (parseInt(value) == 3) {
  //     //Si es un contenido simple
  //     $(".no-seccion").hide();
  //     $(".no-banner").hide();
  //     $(".no-contenido").hide();
  //     $(".si-contenido").show();
  //   } else if (parseInt(value) == 5) {
  //     //Si es un contenido de Contenedor
  //     $(".no-acordion").hide();
  //     $(".no-carrousel").hide();
  //     $(".no-contenido2").hide();
  //     $(".si-contenido2").show();
  //   } else if (parseInt(value) == 6) {
  //     //Si es un contenido de Contenedor
  //     $(".no-contenido2").hide();
  //     $(".no-acordion").show();
  //     $(".no-carrousel").hide();
  //     $(".si-carrousel").show();
  //   } else if (parseInt(value) == 7) {
  //     //Si es un banner
  //     $(".no-acordion").hide();
  //     $(".no-contenido2").hide();
  //     $(".no-acordion").hide();
  //     $(".no-carrousel").hide();
  //     $(".si-acordion").show();
  //   }
  // });
  $(".colorpicker").colorpicker({
    onChange: function (e) {
      console.log("entro");
    }
  }).on('colorpickerChange colorpickerCreate', function (e) {
    console.log("entro");
    // console.log( e.colorpicker.picker.parents('.input-group'));
    //e.colorpicker.picker.parents('.input-group').find('input').css('background-color', e.value);
  }).on('create', function (e) {
    var val = $(this).val();
    $(this).css({
      backgroundColor: $(this).val()
    });
  }).on('change', function (e) {
    var val = $(this).val();
    $(this).css({
      backgroundColor: $(this).val()
    });
  });
});

// function aparecercolumna() {
//   let id_columna = document.getElementById('contenido_tipo').value;
//   if (id_columna == '5' || id_columna == '6') {
//     $(".no-colum").attr("style", "display:block!important")

//   } else {

//     $(".no-colum").attr("style", "display:none!important")

//   }
// }
// aparecercolumna();

function eliminarImagen(campo, ruta) {
  var csrf = $("#csrf").val();
  var csrf_section = $("#csrf_section").val();
  var id = $("#id").val();
  if (confirm("¿Esta seguro de borrar esta imagen?") == true) {
    $.post(ruta, {
      "id": id,
      "csrf": csrf,
      "csrf_section": csrf_section,
      "campo": campo
    }, function (data) {
      if (parseInt(data.elimino) == 1) {
        $("#imagen_" + campo).hide();
      }
    });

  }
  return false;
}

function eliminararchivo(campo, ruta) {
  var csrf = $("#csrf").val();
  var csrf_section = $("#csrf_section").val();
  var id = $("#id").val();
  if (confirm("¿Esta seguro de borrar este Archivo?") == true) {
    $.post(ruta, {
      "id": id,
      "csrf": csrf,
      "csrf_section": csrf_section,
      "campo": campo
    }, function (data) {
      if (parseInt(data.elimino) == 1) {
        $("#archivo_" + campo).hide();
      }
    });

  }
  return false;
}
