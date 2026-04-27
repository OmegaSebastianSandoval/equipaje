<link rel="stylesheet" href="/components/bootstrap/css/bootstrap.min.css">
<script src="/components/bootstrap/js/bootstrap.min.js"></script>

<style>
  body {
    margin: 0;
    min-height: 100vh;
    color: #fff;
    background:
      linear-gradient(rgba(255, 255, 255, 0.05), rgba(255, 255, 255, 0.05)),
      linear-gradient(160deg, #111111 0%, #1c1a0a 20%, #3a2e00 80%, #ffd400 150%);
    background-attachment: fixed;
  }

  .modern-cards {
    height: 100%;
  }

  .modern-cards .modern-card {
    border-radius: 1rem;
    box-shadow: 0 6px 18px rgba(13, 38, 59, 0.12);
    transition: transform .25s ease, box-shadow .25s ease;
    aspect-ratio: 1 / 1;
    display: flex;
    align-items: center;
    justify-content: start;
    text-decoration: none;
  }

  .modern-cards .modern-card:hover {
    transform: translateY(-8px);
    box-shadow: 0 18px 40px rgba(13, 38, 59, 0.18)
  }

  .modern-cards .card-inner {
    display: flex;
    align-items: center;
    gap: 1rem;
    padding: 1.25rem;
    justify-content: center;
    width: 100%;
  }

  .modal-content .card-inner {
    gap: 5px;
  }

  .modern-cards .num {
    width: 72px;
    height: 72px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 700;
    font-size: 2.5rem;
    color: #fff;
    flex: 0 0 72px
  }

  .modern-cards .content h5 {
    margin: 0;
    font-weight: 700;
    color: rgba(255, 255, 255, 0.95);
    font-size: 2rem;
  }

  .modern-cards .content p {
    margin: 0;
    color: rgba(255, 255, 255, 0.85);
    font-size: 1rem;
  }

  .btn-maletas {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: .55rem;
    padding: .9rem 1.35rem;
    border-radius: 999px;
    border: 1px solid #ffd400;
    background: linear-gradient(135deg, #111111 0%, #2a2200 45%, #3d3200 100%);
    color: #fff;
    font-weight: 700;
    text-decoration: none;
    box-shadow: 0 10px 24px rgba(0, 0, 0, 0.28), 0 0 0 1px rgba(255, 212, 0, 0.25) inset;
    transition: transform .2s ease, box-shadow .2s ease, background .2s ease;
  }

  .btn-maletas i {
    color: #ffd400;
  }

  .btn-maletas:hover {
    transform: translateY(-2px);
    box-shadow: 0 14px 28px rgba(0, 0, 0, 0.35), 0 0 0 1px rgba(255, 212, 0, 0.45) inset;
    background: linear-gradient(135deg, #161616 0%, #332a00 45%, #4f4100 100%);
    color: #fff;
    text-decoration: none;
  }








  @media (max-width:767.98px) {
    .modern-cards {
      height: auto;
    }
  }
</style>

<div class="container py-5 modern-cards d-flex align-items-center w-100">
  <div class="row gy-4 w-100">
    <div class="col-12 col-md-4">
      <div class="modern-card" data-bs-toggle="modal" data-bs-target="#tipoModal"
        style="background:linear-gradient(135deg,#0b0b0b 0%,#222222 100%);">
        <div class="card-inner">
          <div class="num" style="background:#ffd400;color:#111;">1</div>
          <div class="content">
            <h5>Contingencia</h5>
            <p class="small"></p>
          </div>
        </div>
      </div>
    </div>
    <!-- Modal -->
    <div class="modal fade" id="tipoModal" tabindex="-1" aria-labelledby="tipoModalLabel" aria-hidden="true">
      <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content bg-dark text-white">
          <div class="modal-header border-0">
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
              aria-label="Cerrar"></button>
          </div>
          <div class="modal-body">
            <div class="row g-3">
              <div class="col-12 col-md-6">
                <a href="/administracion/index/seleccion?over=1&step=N" class="modern-card d-flex" style="background: linear-gradient(
                    136deg,
                    #0b0b0b 5%,
                    #151515 25%,
                    #2d2a14 45%,
                    #5f5200 65%,
                    #bfa100 85%,
                    #fff1a8 100%
                  );">
                  <div class="card-inner">
                    <div class="num" style="background:#ffd400;color:#111;">N</div>
                    <div class="content">
                      <p class="small" style="color:rgb(255, 255, 255);">Registro</p>

                      <h5>acional</h5>
                    </div>
                  </div>
                </a>
              </div>
              <div class="col-12 col-md-6">
                <a href="/administracion/index/seleccion?over=1&step=I" class="modern-card d-flex"
                  style="background:linear-gradient(135deg,#ffd400 0%,#ffea70 100%);">
                  <div class="card-inner">
                    <div class="num" style="background:#111;color:#fff;">I</div>
                    <div class="content" style="color:#111;">
                      <p class="small" style="color:rgb(0, 0, 0);">Registro</p>
                      <h5 style="color:#111;">nternacional</h5>
                    </div>
                  </div>
                </a>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
    <div class="col-12 col-md-4">
      <a href="/administracion/index/seleccion?over=2" class="modern-card"
        style="background:linear-gradient(135deg,#ffd400 0%,#ffea70 100%);">
        <div class="card-inner">
          <div class="num" style="background:#111;color:#fff;">2</div>
          <div class="content" style="color:#111;">
            <p class="small" style="color:rgba(0,0,0,0.65);">Registro</p>
            <h5 style="color:#111;">Maletas internacionales</h5>
          </div>
        </div>
      </a>
    </div>

    <div class="col-12 col-md-4">
      <a href="/administracion/index/seleccion?over=3" class="modern-card" style="background: linear-gradient(
          136deg,
          #0b0b0b 5%,
          #151515 25%,
          #2d2a14 45%,
          #5f5200 65%,
          #bfa100 85%,
          #fff1a8 100%
        );">
        <div class="card-inner">
          <div class="num" style="background:#ffd400;color:#111;">3</div>
          <div class="content">
            <p class="small">Registro</p>
            <h5>Maletas nacionales</h5>
          </div>
        </div>
      </a>
    </div>
    <?php if ($_SESSION['kt_login_level'] == 2) { ?>
      <div class="col-12 d-flex justify-content-center mt-2">
        <a href="/administracion/panel" class="btn-maletas">
          <i class="fa-solid fa-plane-circle-check"></i>
          Ir a maletas
        </a>
      </div>
    <?php } ?>

  </div>
</div>