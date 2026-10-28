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
        <form class="row" id="lote-form" data-modo="reclamo" onsubmit="return false;">
          <input type="hidden" id="lote-csrf" value="<?php echo $this->csrf_lote; ?>">
          <div class="col-12 mb-5">
            <div class="row justify-content-start gap-4 buttons">
              <a href="/administracion/maletas">
                <i class="fa-solid fa-angle-left"></i>
                Ver maletas
              </a>
              <a href="/administracion/maletas/registrar" class="bg-warning">
                <i class="fa-solid fa-suitcase-rolling"></i>
                Registrar maletas
              </a>
            </div>
          </div>
          <div class="col-12 mb-4">
            <h2>
              Reclamo de Maletas
            </h2>
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
                Escanea todas las maletas que se entregan. Las que ya fueron reclamadas o no existen se marcan en rojo y
                no se envían.
              </p>
              <div id="lote-aviso" class="lote-aviso" role="status" aria-live="polite"></div>
            </div>
          </div>

          <div class="col-12 mt-2">
            <div class="lote-lista" id="lote-lista">
              <div class="lote-vacio" id="lote-vacio"><i class="fa-solid fa-barcode"></i>Escanea la primera maleta que se entrega.</div>
            </div>
          </div>

          <div class="col-12 lote-resumen-wrap" id="lote-resumen"></div>

          <div class="col-12 lote-acciones mt-2">
            <button type="button" class="lote-limpiar" id="lote-limpiar">Vaciar lista</button>
            <button type="button" class="registrar" id="lote-enviar" disabled>Reclamar todas (0)</button>
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

<script>
  window.loteConfig = { modo: 'reclamo', max: <?php echo (int) $this->max_lote; ?> };
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
    z-index: 10;
    width: 100%;
    top: 0;
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
