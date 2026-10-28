<!doctype html>
<html>

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="format-detection" content="telephone=no">
  <title><?= $this->_titlepage ?></title>
  <?php $infopageModel = new Page_Model_DbTable_Informacion();
    $infopage = $infopageModel->getById(1);
    ?>

  <!-- Bootsrap CSS -->
  <link rel="stylesheet" href="/components/bootstrap/css/bootstrap.min.css">
  <!-- Font Awesome -->
  <link rel="stylesheet" href="/components/Font-Awesome/css/all.css">
  <!-- CSS Global -->
  <link rel="stylesheet" href="/skins/administracion/css/global.css?v=5">

  <link rel="shortcut icon" href="/images/<?= $infopage->info_pagina_favicon; ?>">

</head>

<body class="login-fondo">
  <div class="login-caja">
    <h1><?= $this->_titlepage ?></h1>
    <div class="login-logo"><img src="/skins/administracion/images/dorado-logo.png"></div>
    <div class="login-content"><?= $this->_content ?></div>
  </div>

  <div class="login-derechos">&copy;Todos los Derechos Reservados <?php echo date('Y'); ?> - Diseñado por Omega Soluciones Web
  </div>
  <script src="https://www.google.com/recaptcha/api.js" async defer></script>


  <!-- Jquery -->
  <script src="/components/jquery/jquery-3.6.0.min.js"></script>
  <!-- Popper -->
   <script src="/components/umd/popper.min.js"></script>
  <!-- Bootstrap Js -->
  <script src="/components/bootstrap/js/bootstrap.min.js"></script>
  <script src="/components/bootstrap-validator/dist/validator.min.js"></script>
  <!-- Main Js -->
  <script src="/skins/administracion/js/main.js"></script>
  <!-- MaskMoney -->
 <script src="/components/mask/jquery.maskMoney.min.js"></script>
</body>

</html>
