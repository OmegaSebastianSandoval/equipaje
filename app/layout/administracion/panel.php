<!doctype html>
<html>

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>
        <?= $this->_titlepage ?>
    </title>
    <script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyBWYVxdF4VwIPfmB65X2kMt342GbUXApwQ&sensor=true">
    </script>
    <?php $infopageModel = new Page_Model_DbTable_Informacion();
    $infopage = $infopageModel->getById(1);
    ?>
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="/components/bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" href="/components/bootstrap-switch/dist/css/bootstrap3/bootstrap-switch.min.css" rel="stylesheet">
    <link rel="stylesheet" href="/components/bootstrap-datepicker/css/bootstrap-datepicker3.standalone.min.css">
    <!-- Fileinput -->
    <link rel="stylesheet" href="/components/bootstrap-fileinput/css/fileinput.css">
    <!-- FontAwesome -->
    <link rel="stylesheet" href="/components/Font-Awesome/css/all.css">
    <!-- Colorpicker -->
    <link rel="stylesheet" href="/components/bootstrap-colorpicker/dist/css/bootstrap-colorpicker.min.css">
    <!-- Global CSS -->
    <link rel="stylesheet" href="/skins/administracion/css/global.css?v=1">
    <link rel="shortcut icon" href="/images/<?= $infopage->info_pagina_favicon; ?>">

    <script type="text/javascript">
        var map;
        var longitude = 0;
        var latitude = 0;
        var icon = '/skins/administracion/images/ubicacion.png';
        var point = false;
        var zoom = 10;

        function setValuesMap(longitud, latitud, punto, zoomm, icono) {
            longitude = longitud;
            latitude = latitud;
            if (punto) {
                point = punto;
            }
            if (zoomm) {
                zoom = zoomm;
            }
            if (icono) {
                icon = icono
            }
        }

        function initializeMap() {
            var mapOptions = {
                zoom: parseInt(zoom),
                center: new google.maps.LatLng(longitude, longitude),
            };
            // Place a draggable marker on the map
            map = new google.maps.Map(document.getElementById('map'), mapOptions);
            if (point == true) {
                var marker = new google.maps.Marker({
                    position: new google.maps.LatLng(longitude, latitude),
                    map: map,
                    icon: icon
                });
            }
            map.setCenter(new google.maps.LatLng(longitude, latitude));
        }
    </script>
</head>

<body>
    <header>
        <div class="container-fluid">
            <div class="row align-items-center">
                <div class="col-3">
                    <img src="/skins/administracion/images/dorado-logo.png" class="logo-blanco" style="height: 60px;">
                </div>
                <div class="col-9">
                    <?= $this->_data['panel_header']; ?>
                </div>
            </div>
        </div>
    </header>
    <div class="container-fluid">
        <div class="row">
            <nav id="panel-botones" class="col-2">
                <?= $this->_data['panel_botones']; ?>
            </nav>
            <article id="contenido_panel" class="col-10">
                <section id="contenido_general">
                    <div class="panel-titulo"><b>Dashboard</b> Versión 6.0</div>
                    <?= $this->_content ?>
                </section>
            </article>
        </div>
    </div>
    <footer class="panel-derechos col-md-12">&copy;Todos los Derechos Reservados <?php echo date('Y'); ?> - Diseñado por Omega Soluciones Web
    </footer>
    <!-- Jquery -->
    <script src="/components/jquery/jquery-3.6.0.min.js"></script>
    <!-- Popper -->
    <!--<script src="https://unpkg.com/@popperjs/core@2"></script> -->
	  <script src="/components/umd/popper.min.js"></script>
    <!-- Bootstrap Js -->
    <script src="/components/bootstrap/js/bootstrap.min.js"></script>
    <script src="/components/bootstrap-datepicker/js/bootstrap-datepicker.min.js">
    </script>
	        <!-- Charts -->
    <script src="/components/chart/chart.min.js"></script>
    <script src="/components/chart/chartjs-plugin-datalabels@2.js"></script>
    <script src="/components/bootstrap-datepicker/locales/bootstrap-datepicker.es.min.js">
    </script>
    <script src="/components/bootstrap-validator/dist/validator.min.js">
    </script>
    <!-- File Input -->
    <script src="/components/bootstrap-fileinput/js/fileinput.min.js"></script>
    <script src="/components/bootstrap-fileinput/js/locales/es.js"></script>
    <!-- Tiny -->
    <script src="/components/tinymce/tinymce.min.js"></script>
    <script src="/components/bootstrap-switch/dist/js/bootstrap-switch.min.js"></script>
    <script src="/components/bootstrap-colorpicker/dist/js/bootstrap-colorpicker.min.js"></script>
    <!--<script src="https://cdnjs.cloudflare.com/ajax/libs/quagga/0.12.1/quagga.min.js"></script>-->
	<script src="/components/quagga/quagga.min.js"></script>

    <!-- main Js -->
    <script src="/skins/administracion/js/main.js?v=1"></script>
    <!-- <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-maskmoney/3.0.2/jquery.maskMoney.min.js" integrity="sha512-Rdk63VC+1UYzGSgd3u2iadi0joUrcwX0IWp2rTh6KXFoAmgOjRS99Vynz1lJPT8dLjvo6JZOqpAHJyfCEZ5KoA==" crossorigin="anonymous"></script> -->
	 <script src="/components/mask/jquery.maskMoney.min.js"></script>
</body>

</html>
