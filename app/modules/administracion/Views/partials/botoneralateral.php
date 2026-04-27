<?php
$session = Session::getInstance();
$loginLevel = (string) $session->get('kt_login_level');
$canOver = (int) $session->get('user_puedeover') === 1;
$over = $session->get('over');
$step = $session->get('step');

$isAdmin = $loginLevel === '1';
$isLevel2 = $loginLevel === '2';
$isLevel3 = $loginLevel === '3';
$isLevel4 = $loginLevel === '4';

// Dashboard
$canSeeDashboard = $isAdmin || $isLevel2 || $isLevel3;
$canSeeDashboardOver = $isAdmin || $isLevel3 || ($isLevel2 && $canOver);

// Over
$canEnterOrChangeOver = $isLevel3 || ($isLevel2 && $canOver);
$hasActiveOver = $over !== null;
$canExitOver = $isLevel2 && $canOver && $hasActiveOver;
$overActionText = ($isLevel3 || ($isLevel2 && $canOver && $hasActiveOver))
  ? 'Seleccionar/Cambiar Over'
  : 'Entrar y Seleccionar Over';
?>

<style>
  .over-action-item a {
    border: 1px solid #93c5fd;
    border-left: 4px solid #2563eb;
    background: linear-gradient(180deg, #f0f7ff 0%, #e1efff 100%);
    border-radius: 10px;
    box-shadow: 0 6px 16px rgba(37, 99, 235, 0.12);
    margin: 2px 0;
    font-weight: 600;
  }

  .over-action-item a:hover,
  .over-action-item.activo a {
    border-color: #60a5fa;
    border-left-color: #1d4ed8;
    background: linear-gradient(180deg, #e6f1ff 0%, #cfe4ff 100%);
    box-shadow: 0 10px 22px rgba(37, 99, 235, 0.2);
  }

  .over-action-item .over-pill {
    display: inline-block;
    margin-left: 8px;
    padding: 2px 8px;
    font-size: 10px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    color: #1d4ed8;
    background: #dbeafe;
    border-radius: 999px;
    vertical-align: middle;
    margin-left: auto;
  }
</style>

<ul>
  <?php if ($isAdmin) { ?>
    <li <?php if ($this->botonpanel == 1) { ?>class="activo" <?php } ?>>
      <a href="/administracion/panel">
        <i class="fas fa-info-circle"></i>
        Información Página
      </a>
    </li>
  <?php } ?>
  <!-- <li <?php if ($this->botonpanel == 2) { ?>class="activo" <?php } ?>>
    <a href="/administracion/publicidad">
      <i class="far fa-images"></i> 
      Administrar Publicidad
    </a>
  </li>
  <li <?php if ($this->botonpanel == 3) { ?>class="activo" <?php } ?>>
    <a href="/administracion/contenido">
      <i class="fas fa-file-invoice"></i> 
      Administrar Contenidos
    </a>
  </li> -->
  <?php if ($isAdmin) { ?>
    <li <?php if ($this->botonpanel == 4) { ?>class="activo" <?php } ?>>
      <a href="/administracion/usuario">
        <i class="fas fa-users"></i>
        Administrar Usuarios
      </a>
    </li>
  <?php } ?>
  <?php if ($canSeeDashboard) { ?>
    <li <?php if ($this->botonpanel == 7) { ?>class="activo" <?php } ?>>
      <a href="/administracion/dashboard">
        <i class="fa-solid fa-chart-simple"></i>
        Dashboard
      </a>
    </li>
  <?php } ?>
  <?php if ($canSeeDashboardOver) { ?>
    <li <?php if ($this->botonpanel == 8) { ?>class="activo" <?php } ?>>
      <a href="/administracion/dashboard/over">
        <i class="fa-solid fa-chart-pie"></i>
        Dashboard Over
      </a>
    </li>
  <?php } ?>
  <?php if ($isAdmin || $isLevel2 || $isLevel3 || $isLevel4) { ?>
    <li <?php if ($this->botonpanel == 5) { ?>class="activo" <?php } ?>>
      <a href="/administracion/maletas">
        <i class="fa-solid fa-suitcase"></i>
        Administrar Maletas
      </a>
    </li>
  <?php } ?>
  <?php if ($isAdmin || $isLevel2 || $isLevel3) { ?>
    <li <?php if ($this->botonpanel == 0) { ?>class="activo" <?php } ?>>
      <a href="/administracion/maletas/registrar">
        <i class="fa-solid fa-suitcase"></i>
        Registrar Maletas
      </a>
    </li>

    <li <?php if ($this->botonpanel == 0) { ?>class="activo" <?php } ?>>
      <a href="/administracion/maletas/reclamar">
        <i class="fa-solid fa-suitcase"></i>
        Reclamar Maletas
      </a>
    </li>
  <?php } ?>
  <?php if ($isAdmin) { ?>
    <li <?php if ($this->botonpanel == 6) { ?>class="activo" <?php } ?>>
      <a href="/administracion/aerolineas">
        <i class="fa-solid fa-plane-circle-check"></i>
        Administrar Aerolíneas
      </a>
    </li>
  <?php } ?>
  <?php if ($canEnterOrChangeOver) { ?>
    <li class="over-action-item <?php if ($this->botonpanel == 6) { ?>activo<?php } ?>">
      <a href="/administracion/index/seleccionarover?change=1">
        <i class="fa-solid fa-plane-circle-check"></i>
        <?php echo $overActionText; ?>
        <span class="over-pill">Over</span>
      </a>
    </li>
  <?php } ?>

  <?php if ($canExitOver) { ?>
    <li class="over-action-item <?php if ($this->botonpanel == 6) { ?>activo<?php } ?>">
      <a href="/administracion/index/salirover">
        <i class="fa-solid fa-lock-open"></i>
        Salir de Over
        <span class="over-pill">Over</span>
      </a>
    </li>
  <?php } ?>

</ul>
<?php if ($isLevel3 || $canExitOver) { ?>
  <div style="padding:16px;">
    <style>
      .over-card {
        background: linear-gradient(180deg, #ffffff 0%, #f9fafb 100%);
        border-radius: 16px;
        padding: 16px 18px;
        box-shadow:
          0 10px 25px rgba(15, 23, 42, 0.08),
          0 2px 6px rgba(15, 23, 42, 0.04);
        display: flex;
        flex-direction: column;
        gap: 8px;
        align-items: center;

        color: #0f172a;
      }



      .over-card .label {
        font-size: 11px;
        font-weight: 500;
        letter-spacing: 0.04em;
        text-transform: uppercase;
        color: #64748b;
      }

      .over-card .value {
        font-size: 28px;
        font-weight: 700;
        line-height: 1;
        color: #020617;
        background-color: var(--yellow);
        padding: 5px;
        border-radius: 50%;
        aspect-ratio: 1/1;
        text-align: center;
      }

      .over-card .meta {
        margin-top: 4px;
        font-size: 13px;
        font-weight: 500;
        color: #475569;
        background: #f1f5f9;
        padding: 4px 10px;
        border-radius: 999px;
      }
    </style>

    <div class="over-card">
      <div class="label">Over actual</div>
      <div class="value"><?php echo $over ?></div>

      <?php if (intval($over) === 1 && !empty($step)) { ?>
        <div class="meta">
          <?php echo $step === 'N' ? 'Nacional' : ($step === 'I' ? 'Internacional' : ''); ?>
        </div>
      <?php } ?>
    </div>
  </div>
<?php } ?>