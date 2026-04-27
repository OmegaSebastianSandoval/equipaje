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
    <div class="row maletas-bx">
      <div class="col-lg-5 col-md-12">
        <div class="d-flex justify-content-center">
          <img src="/skins/administracion/images/maletas.png" alt="">
        </div>
      </div>
      <div class="col-lg-7 col-md-12">
        <form action="/administracion/maletas/store" method="post" class="row">
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
<!--              <a href="/administracion/dashboard">-->
<!--                <i class="fa-solid fa-chart-simple"></i>-->
<!--                Dashboard-->
<!--              </a>-->
            </div>
          </div>
          <div class="col-12 mb-5">
            <h2>
              Registro de Maletas
            </h2>
          </div>
          <div class="col-12">
            <div class="bag-code-bx">
              <label for="codigo">Código de la maleta</label>
              <div class="bag-code-wrapper">
                <input type="text" name="maleta_codigo" id="maleta_codigo" class="form-control"
                  placeholder="Escribe el código de la maleta">
                <select name="connection_select" id="connection-select" class="form-control">
                  <option value="" selected disabled>¿Maleta de conexión?</option>
                  <option value="1">SI</option>
                  <option value="0">NO</option>
                </select>
                <label for="maleta_codigo">
                  <i class="fa-solid fa-barcode"></i>
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
          <div class="col-12" id="ext" style="display: none;">
            <div class="alert alert-danger" role="alert">
              La maleta ya ha sido registrada.
            </div>
          </div>
          <div class="col-12" id="no_ariline" style="display: none;">
            <div class="alert alert-danger" role="alert">
              No se ha encontrado una aerolinea registrada con este codigo.
            </div>
          </div>
          <div class="col-12" id="connection">
            <label for="">Aerolínea de conexión</label>
            <select name="connection" id="" class="form-control">
              <option value="" selected disabled>Selecciona una aerolínea</option>
              <?php foreach ($this->list_maleta_aerolinea as $key => $aerolinea) : ?>
              <option value="<?php echo $key; ?>">
                <?php echo $aerolinea; ?>
              </option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="col-lg-6 col-md-12">
            <label for="">Aerolínea</label>
            <input type="hidden" name="maleta_aerolinea" id="maleta_aerolinea" class="form-control" />
            <input type="text" name="" id="maleta_aerolinea_name" class="form-control" disabled/>
          </div>
          <div class="col-lg-6 col-md-12 d-flex align-items-end">
            <button type="submit" class="registrar" disabled>Registrar</button>
          </div>
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
  #connection{
    display: none;
  }
</style>
