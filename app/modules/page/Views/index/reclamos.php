<div class="container h-100">
  <div class="d-flex h-100 align-items-center">
    <div class="row maletas-bx">
      <div class="col-lg-5 col-md-12">
        <div class="d-flex justify-content-center">
          <img src="/skins/administracion/images/maletas.png" alt="">
        </div>
      </div>
      <div class="col-lg-7 col-md-12">
        <form action="/page/index/update" method="post" class="row">
          <div class="col-12 mb-5">
          </div>
          <div class="col-12 mb-5">
            <h2>
              Reclamo de Maletas
            </h2>
          </div>
          <div class="col-12">
            <div class="bag-code-bx">
              <label for="codigo">Código de la maleta</label>
              <div class="bag-code-wrapper">
                <input type="text" name="maleta_codigo" id="maleta_codigo" class="form-control"
                  placeholder="Escribe el código de la maleta">
                <label for="maleta_codigo">
                  <i class="fa-solid fa-gun"></i>
                </label>
                <button type="button" data-bs-toggle="modal" data-bs-target="#videoModal">
                  <i class="fa-solid fa-camera"></i>
                </button>
              </div>
              <p>
                Puedes escanear el código de la maleta usando la pistola lectora de códigos, usando tu cámara o
                escribirlo manualmente
              </p>
            </div>
          </div>
          <div class="col-lg-6 col-md-12">
            <label for="">Aerolínea</label>
            <input type="hidden" name="maleta_aerolinea" id="maleta_aerolinea" class="form-control" />
            <input type="text" name="" id="maleta_aerolinea_name" class="form-control" disabled/>
          </div>
          <div class="col-lg-6 col-md-12">
            <label for="">Estado</label>
            <select type="text" name="maleta_estado" id="maleta_estado" class="form-control" disabled>
              <option value="3" selected disabled></option>
              <option value="">Sin reclamar</option>
              <option value="1">Reclamada</option>
              <option value="2">maleta no encontrada</option>
            </select>
          </div>
          <div class="col-md-12 d-flex align-items-end mt-3">
            <button type="submit" class="registrar" disabled>Reclamar</button>
          </div>
          <input type="hidden" name="_csrf" value="<?php echo md5(date("Y-m-d")."_OMEGA"); ?>" />
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