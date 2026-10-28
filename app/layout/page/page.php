<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="UTF-8">
  <title><?= $this->_titlepage ?></title>
  <?php $infopageModel = new Page_Model_DbTable_Informacion();
  $infopage = $infopageModel->getById(1);
  ?>

  <!-- Bootstrap CSS -->
  <link rel="stylesheet" href="/components/bootstrap/css/bootstrap.min.css">

  <!-- Global CSS -->
  <link rel="stylesheet" href="/skins/page/css/global.css?v=<?= filemtime(PUBLIC_PATH . 'skins/page/css/global.css') ?>">
  <link rel="stylesheet" href="/skins/page/css/responsive.css?v=<?= filemtime(PUBLIC_PATH . 'skins/page/css/responsive.css') ?>">

  <!-- FontAwesome -->
  <link rel="stylesheet" href="/components/Font-Awesome/css/all.css">

  <link rel="shortcut icon" href="/images/<?= $infopage->info_pagina_favicon; ?>">


  <script type="text/javascript" id="www-widgetapi-script" src="https://s.ytimg.com/yts/jsbin/www-widgetapi-vflS50iB-/www-widgetapi.js" async=""></script>

  <!-- Jquery -->
  <script src="/components/jquery/jquery-3.6.0.min.js"></script>
  <!-- Popper -->
   <script src="/components/umd/popper.min.js"></script>
  <!-- Bootstrap Js -->
  <script src="/components/bootstrap/js/bootstrap.min.js"></script>



  <script src="/components/jquery-knob/js/jquery.knob.js"></script>

  <!-- SweetAlert -->
  <script src="/components/sweetalert/sweetalert2@11.js"></script>

  <!-- Quagga -->
	<script src="/components/quagga/quagga.min.js"></script>

  <!-- Main Js -->
  <script src="/skins/page/js/main.js?v=<?= filemtime(PUBLIC_PATH . 'skins/page/js/main.js') ?>"></script>

  <!-- Recaptcha -->
  <script src='https://www.google.com/recaptcha/api.js'></script>
  <meta name="description" content="<?= $this->_data['meta_description']; ?>" />
    <meta name=" keywords" content="<?= $this->_data['meta_keywords']; ?>" />
  <?php echo $this->_data['scripts'];  ?>
  <script src="https://www.google.com/recaptcha/api.js" async defer></script>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="format-detection" content="telephone=no, date=no, address=no, email=no">
 
</head>

<body>
  <header>
    <?= $this->_data['header']; ?>
  </header>
  <div class="contenedor-general"><?= $this->_content ?></div>
  <footer>
    <?= $this->_data['footer']; ?>
  </footer>
  <?= $this->_data['adicionales']; ?>

</body>

</html>
