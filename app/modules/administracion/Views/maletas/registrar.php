<?php
$overSesion = Session::getInstance()->get('over');
$stepSesion = Session::getInstance()->get('step');
$tipoMaleta = 'Maletas normales';
if ($overSesion) {
  $tipoMaleta = 'Over ' . $overSesion;
  if ($overSesion == '1') {
    $tipoMaleta .= $stepSesion == 'I' ? ' Internacional' : ' Nacional';
  }
}
?>
<div class="header">
  <div class="d-flex align-items-center justify-content-between p-3">
    <img src="/skins/administracion/images/dorado-logo.png" alt="" width="200px">

    <div class="d-flex align-items-center pe-3">
      <div class="d-flex pe-5 flex-column header-user-info" style="border-right: 3px solid #fff;">
        <strong>Bienvenido</strong>
        <span><?php echo Session::getInstance()->get('kt_login_name'); ?></span>
      </div>
      <a href="/administracion/loginuser/logout" class="d-flex align-items-center ps-3" style="text-decoration: none;">
        <i class="fa-solid fa-right-from-bracket me-2"></i>
        <span>Salir</span>
      </a>
    </div>
  </div>
</div>
<div class="container h-100">
  <div class="d-flex h-100 align-items-center">
    <div class="row maletas-bx w-100 mx-0">
      <div class="col-xl-3 d-none d-xl-flex align-items-center justify-content-center">
        <img src="/skins/administracion/images/maletas.png" alt="" class="lote-ilustracion">
      </div>
      <div class="col-xl-9 col-12">
        <form class="row" id="lote-form" data-modo="registro" onsubmit="return false;">
          <input type="hidden" id="lote-csrf" value="<?php echo $this->csrf_lote; ?>">
          <div class="col-12 mb-5">
            <div class="row justify-content-start gap-4 buttons">
              <a href="/administracion/maletas">
                <i class="fa-solid fa-angle-left"></i>
                Ver maletas
              </a>
              <a href="/administracion/maletas/reclamar" class="bg-warning">
                <i class="fa-solid fa-suitcase-rolling"></i>
                Reclamar maletas
              </a>
            </div>
          </div>
          <div class="col-12 mb-4">
            <h2>
              Registro de Maletas
            </h2>
            <span class="lote-tipo"><?php echo $tipoMaleta; ?></span>
          </div>
          <div class="col-12">
            <div class="bag-code-bx">
              <label for="lote_codigo">Código de la maleta</label>
              <div class="bag-code-wrapper">
                <input type="text" id="lote_codigo" class="form-control" inputmode="numeric" autocomplete="off"
                  placeholder="Escanea o escribe el código de la maleta" autofocus>
                <label for="lote_codigo">
                  <i class="fa-solid fa-barcode"></i>
                </label>
                <button type="button" data-bs-toggle="modal" data-bs-target="#videoModal">
                  <i class="fa-solid fa-camera"></i>
                </button>
              </div>
              <p>
                Escanea todas las maletas seguidas con la pistola, la cámara o escribiendo el código. Cada código se
                agrega a la lista de abajo.
              </p>
              <div id="lote-aviso" class="lote-aviso" role="status" aria-live="polite"></div>
            </div>
          </div>

          <div class="col-12 mt-2">
            <div class="lote-toolbar">
              <span class="lote-toolbar-titulo"><i class="fa-solid fa-plane-departure"></i> Vuelos</span>
              <div class="lote-segmento" role="radiogroup" aria-label="Rango de horas">
                <input type="radio" name="lote-rango" id="lote-rango-hoy" value="hoy" checked>
                <label for="lote-rango-hoy">Hoy</label>
                <input type="radio" name="lote-rango" id="lote-rango-24h" value="24h">
                <label for="lote-rango-24h">Próximas 24 h</label>
              </div>
              <div class="form-check form-switch lote-switch">
                <input class="form-check-input" type="checkbox" role="switch" id="lote-todos">
                <label for="lote-todos">Ver todos
                  <span class="text-muted">(por defecto <?php echo $this->tipo_vuelo == 'I' ? 'internacionales' : 'nacionales'; ?>)</span></label>
              </div>
              <button type="button" class="lote-boton" id="lote-asignar" disabled>
                Asignar vuelo a seleccionadas
              </button>
            </div>
          </div>

          <div class="col-12 mt-2">
            <div class="lote-lista" id="lote-lista">
              <div class="lote-vacio" id="lote-vacio"><i class="fa-solid fa-barcode"></i>Escanea la primera maleta para empezar el lote.</div>
            </div>
          </div>

          <div class="col-12 lote-resumen-wrap" id="lote-resumen"></div>

          <div class="col-12 lote-acciones mt-2">
            <button type="button" class="lote-limpiar" id="lote-limpiar">Vaciar lista</button>
            <button type="button" class="registrar" id="lote-enviar" disabled>Registrar todas (0)</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>

<!-- Modal camara -->
<div class="modal fade camara-modal" id="videoModal" tabindex="-1" role="dialog" aria-labelledby="camara-contador" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-xl modal-fullscreen-sm-down" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <span class="me-auto" id="camara-contador">Apunta la cámara a cada etiqueta</span>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
      </div>
      <div class="modal-body">
        <div id="camara-visor" class="camara-visor"></div>
        <div class="camara-guia" aria-hidden="true"><span>Centra el código de barras aquí</span></div>
        <div id="camara-estado" class="camara-estado" role="status" aria-live="polite"></div>
      </div>
      <div class="modal-footer camara-footer">
        <button type="button" class="camara-listo" id="camara-listo" data-bs-dismiss="modal">Listo</button>
      </div>
    </div>
  </div>
</div>

<!-- Modal seleccion de vuelo -->
<div class="modal fade vuelo-modal" id="vueloModal" tabindex="-1" aria-labelledby="vueloModalTitulo" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable modal-fullscreen-sm-down">
    <div class="modal-content">
      <div class="modal-header">
        <div>
          <h5 class="modal-title" id="vueloModalTitulo"><i class="fa-solid fa-plane-departure"></i> Seleccionar vuelo</h5>
          <div class="vuelo-para" id="vuelo-para"></div>
        </div>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
      </div>
      <div class="vuelo-buscador">
        <i class="fa-solid fa-magnifying-glass"></i>
        <input type="search" id="vuelo-buscar" placeholder="Buscar por número de vuelo o destino" autocomplete="off" aria-label="Buscar vuelo">
      </div>
      <div class="modal-body">
        <div id="vuelo-lista" class="vuelo-lista" role="list"></div>
      </div>
      <div class="vuelo-manual">
        <label for="vuelo-manual-input"><strong>¿Vuelo no listado?</strong> Escribe el número</label>
        <div class="vuelo-manual-campos">
          <input type="text" id="vuelo-manual-input" maxlength="15" placeholder="Ej: AV 1234" autocomplete="off">
          <button type="button" id="vuelo-manual-usar">Usar este vuelo</button>
        </div>
        <small>Las maletas con vuelo no listado quedan sin hora de salida y sin alerta.</small>
      </div>
    </div>
  </div>
</div>

<script>
  window.loteConfig = {
    modo: 'registro',
    aerolineas: <?php echo json_encode($this->list_maleta_aerolinea, JSON_HEX_TAG | JSON_UNESCAPED_UNICODE); ?>,
    aerolineasIata: <?php echo json_encode($this->aerolineas_iata, JSON_HEX_TAG); ?>
  };
</script>
<script src="/skins/administracion/js/lotes.js?v=<?= filemtime(PUBLIC_PATH . 'skins/administracion/js/lotes.js') ?>"></script>

<style>
  #contenido_general {
    height: 100%;
  }

  .panel-titulo {
    display: none;
  }

  #contenido_panel {
    width: 100% !important;
    min-height: calc(100vh - 41px) !important;
  }

  #panel-botones {
    display: none;
  }

  header {
    display: none;
  }

  body {
    min-width: inherit;
  }
  video{
    position: relative;
  }
  canvas.drawing,
  canvas.drawingBuffer {
    position: absolute;
    left: 0;
    top: 0;
  }
  #contenido_general{
    background-image: url("/skins/administracion/images/dorado-01.jpg");
    background-size: cover;
    background-position: center;
  }
  .header{
    position: fixed;
    width: 100%;
    top: 0;
    z-index: 10;
   background: rgb(0,0,0);
    background: linear-gradient(180deg, rgba(0,0,0,1) 15%, rgba(0,0,0,0.4738270308123249) 79%, rgba(255,255,255,0) 100%);
  }
  .header *{
    color: #ffd04f;
  }
    @media screen and (max-width: 986px){
    .container{
      padding-top: 50px;
    }
    .header-user-info{
      display: none !important;
    }
  }
</style>
