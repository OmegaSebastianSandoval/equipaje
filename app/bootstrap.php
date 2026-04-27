<?php


if (isset($_COOKIE['consentimiento']) && $_COOKIE['consentimiento'] === 'aceptado') {
  ini_set('session.cookie_httponly', 1);  // Evita acceso desde JS (protege de XSS)
  ini_set('session.cookie_secure', 1);    // Solo por HTTPS
  ini_set('session.use_strict_mode', 1);  // PHP no acepta IDs de sesión no creados por el servidor
  session_start();
}
define('DS', DIRECTORY_SEPARATOR);
define('ROOT', realpath(dirname(__FILE__)) . DS);
define('FRAMEWORK_PATH', ROOT . '../framework' . DS);
define('APP_PATH', ROOT . '../app' . DS);
define('VIEWS_PATH', APP_PATH . 'View' . DS);
define('LAYOUTS_PATH', APP_PATH . 'layout' . DS);
define('IMAGE_PATH', APP_PATH . "../public/images/");
define('FILE_PATH', APP_PATH . "../public/files/");
define('PUBLIC_PATH', APP_PATH . "../public/");

date_default_timezone_set('America/Bogota');

require_once FRAMEWORK_PATH . 'Config/Config.php';
set_include_path(
  implode(
    PATH_SEPARATOR,
    array(
      get_include_path(),
      FRAMEWORK_PATH
    )
  )
);

function framework_autoload($classname)
{
  $ruta = explode('_', $classname);
  if (substr(end($ruta), -10) == 'Controller') {
    $file = strtolower($ruta[0]) . '/Controllers/' . $ruta[1] . '.php';
    if (file_exists(APP_PATH . 'modules/' . $file)) {
      require_once(APP_PATH . 'modules/' . $file);
    }
  } else if (isset($ruta[1]) && $ruta[1] == 'Model') {
    $file = strtolower($ruta[0]) . "/Models/";
    unset($ruta[0]);
    unset($ruta[1]);
    $file = $file . implode("/", $ruta) . '.php';
    if (file_exists(APP_PATH . 'modules/' . $file)) {
      require_once(APP_PATH . 'modules/' . $file);
    }
  } else {
    $file = implode("/", $ruta) . '.php';
    if (file_exists(APP_PATH . '../framework/' . $file)) {
      require_once($file);
    }
  }
}
spl_autoload_register('framework_autoload');

include(APP_PATH . '/../vendor/autoload.php');
$env = "development";
if (strpos($_SERVER['HTTP_HOST'], "equipajerezagado.opain.co:8443") !== false) {
  $env = "staging";
} else if (strpos($_SERVER['HTTP_HOST'], "...") !== false) {
  $env = "production";
}
define('APPLICATION_ENV', getenv('APPLICATION_ENV') ? getenv('APPLICATION_ENV') : $env);

error_reporting(E_STRICT);
if ($_GET['debug'] == "1") {
  error_reporting(E_ALL);
}
ini_set("display_errors", 1);

if (!file_exists(IMAGE_PATH)) {
  mkdir(IMAGE_PATH, 0777, true);
}

if (!file_exists(FILE_PATH)) {
  mkdir(FILE_PATH, 0777, true);
}

// require_once '../vendor/phpoffice/phpexcel/Classes/PHPExcel.php';
require_once '../vendor/tcpdf/tcpdf.php';
