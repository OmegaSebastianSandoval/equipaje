<div class="container h-100">
  <div class="d-flex h-100 align-items-center">
    <div class="row maletas-bx reclamo-bx w-100 mx-0">
      <div class="col-lg-5 col-md-12">
        <div class="d-flex justify-content-center">
          <img src="/skins/administracion/images/maletas.png" alt="">
        </div>
      </div>
      <div class="col-lg-7 col-md-12">
        <form class="row" id="reclamo-form" onsubmit="return false;">
          <div class="col-12 mb-5 d-none d-lg-block">
          </div>
          <div class="col-12 mb-4 mb-lg-5">
            <h2>
              Reclamo de Maletas
            </h2>
          </div>
          <?php if (!$this->consentimiento) { ?>
            <div class="col-12">
              <div class="reclamo-consentimiento">
                <p>Para reclamar maletas este sitio necesita usar cookies que mantienen tu sesión activa.</p>
                <button type="button" id="reclamo-consentimiento">Aceptar</button>
              </div>
            </div>
          <?php } ?>
          <div class="col-12">
            <div class="bag-code-bx">
              <label for="maleta_codigo">Código de la maleta</label>
              <div class="bag-code-wrapper">
                <input type="text" name="maleta_codigo" id="maleta_codigo" class="form-control"
                  placeholder="Escribe el código de la maleta" inputmode="numeric" maxlength="10" autocomplete="off"
                  <?php echo $this->consentimiento ? '' : 'disabled'; ?>>
                <label for="maleta_codigo">
                  <i class="fa-solid fa-gun"></i>
                </label>
                <button type="button" data-bs-toggle="modal" data-bs-target="#videoModal">
                  <i class="fa-solid fa-camera"></i>
                </button>
              </div>
              <p>
                Puedes escanear el código de la maleta usando la pistola lectora de códigos, usando tu cámara o
                escribirlo manualmente. Puedes agregar varias maletas y reclamarlas todas juntas.
              </p>
            </div>
          </div>
          <div class="col-12">
            <div id="reclamo-aviso" class="reclamo-aviso" role="status" aria-live="polite"></div>
          </div>
          <div class="col-12">
            <div id="reclamo-lista" class="reclamo-lista">
              <p id="reclamo-vacio" class="reclamo-vacio">Escanea o escribe los códigos de las maletas que vas a reclamar.</p>
            </div>
          </div>
          <div class="col-12 d-flex gap-2 mt-3 reclamo-acciones">
            <button type="button" id="reclamo-limpiar" class="reclamo-limpiar" disabled>Limpiar</button>
            <button type="button" id="reclamo-enviar" class="registrar" disabled>Reclamar</button>
          </div>
          <input type="hidden" id="reclamo-csrf" value="<?php echo $this->csrf_reclamos; ?>" />
        </form>
      </div>
    </div>
  </div>
</div>

<!-- Modal -->
<div class="modal fade" id="videoModal" tabindex="-1" role="dialog" aria-labelledby="modalTitleId" aria-hidden="true">
  <div class="modal-dialog modal-xl" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <video id="video" width="100%" height="480" autoplay></video>
      </div>
    </div>
  </div>
</div>


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
</style>
<script src="/skins/page/js/reclamos.js?v=<?= filemtime(PUBLIC_PATH . 'skins/page/js/reclamos.js') ?>"></script>
