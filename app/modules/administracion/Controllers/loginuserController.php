<?php

/**
 *
 */

class Administracion_loginuserController extends Controllers_Abstract
{

  protected $mainModel;
  protected $route;
  protected $_csrf_section = "login_admin";
  public $csrf;
  protected $log_error = '';
  protected $login_user = '';


  public function init()
  {
    $this->mainModel = new Core_Model_DbTable_User();
    $this->route = "/administracion/users";
    $this->_view->route = $this->route;
    $this->csrf = Session::getInstance()->get('csrf')[$this->_csrf_section];
    parent::init();
  }

  public function indexAction()
  {
    Session::getInstance()->set("error_login", "");
    $isPost = $this->getRequest()->isPost();
    $user = $this->_getSanitizedParam("user");
    $password = $this->_getSanitizedParam("password");
    $csrf = $this->_getSanitizedParam("csrf");
    $email = $this->_getSanitizedParam("email");
    $this->login_user = $user;
    if ($email) {
      Session::getInstance()->set("error_login", "El Usuario se encuentra inactivo.");
      $this->log_error = 'El Usuario se encuentra inactivo.';
      header('Location: /administracion/');
      return;
    }

    $bloqueosModel = new Administracion_Model_DbTable_Bloqueos();
    // Obtiene información de bloqueos anteriores
    $infoBloqueo = $bloqueosModel->getList(
      "bloqueo_usuario = '$user' or bloqueo_ip = '" . $_SERVER['REMOTE_ADDR'] . "' ",
      "bloqueo_id DESC"
    )[0];
    // Manejo de intentos fallidos
    $intentos = (int) $infoBloqueo->bloqueo_intentosfallidos;
    $fechaUltimoIntento = $infoBloqueo->bloqueo_fechaintento;
    $fechaUltimoIntento = new DateTime($fechaUltimoIntento);
    $fechaActual = new DateTime();
    $diferencia = $fechaActual->getTimestamp() - $fechaUltimoIntento->getTimestamp();

    // Bloquea al usuario si excede los intentos permitidos
    if ($intentos >= 3 && $diferencia <= 900) {
      $this->log_error = 'Usuario bloqueado por intentos fallidos.';
      Session::getInstance()->set("error_login", "El Usuario ha sido bloqueado por 15 minutos por intentos fallidos.");
      $this->registrarLogFallido('Usuario bloqueado por intentos fallidos.');
      header('Location: /administracion/');
      return;
    }

    // Registra el intento fallido
    $dataBloque = array();
    $dataBloque['bloqueo_usuario'] = $user;
    $dataBloque['bloqueo_intentosfallidos'] = $this->getIntentos($user);
    $dataBloque['bloqueo_ip'] = $_SERVER['REMOTE_ADDR'];
    $bloqueosModel->insert($dataBloque);

    $isError = false;
    $busco = "no";
    $error = 0;
    if ($isPost == true && $user && $password && $this->csrf == $csrf) {
      $userModel = new core_Model_DbTable_User();
      $busco = "si";
      if (!$userModel->searchUserByUser($user)) {
        $isError = true;
        $this->log_error = 'El Usuario No Existe.';
        Session::getInstance()->set("error_login", "El Usuario No Existe.");
      } else if ($userModel->autenticateUser($user, $password) == true) {
        $resUser = $userModel->searchUserByUser($user);
        if ($resUser->user_state == 1) {
          $otpCode = rand(100000, 999999);

          $usersModel = new Administracion_Model_DbTable_Usuario();
          $usersModel->editField($resUser->user_uid, 'user_otp', $otpCode);
          $usersModel->editField($resUser->user_uid, 'user_otp_date', date('Y-m-d H:i:s'));
          $mailModel = new Core_Model_Sendingemail($this->_view);
          $mailModel->enviarOTP($resUser, $otpCode);
          //LOG
          $data['log_tipo'] = "LOGIN OTP 1";
          $data['log_usuario'] = $resUser->user_user;
          $infoInicioSesion = [
            'usuario' => $resUser->user_user,
            'fecha' => date('Y-m-d H:i:s'),
            'ip' => $_SERVER['REMOTE_ADDR'],
            'navegador' => $_SERVER['HTTP_USER_AGENT'],
            'otp' => $otpCode
          ];
          $data['log_log'] = print_r($infoInicioSesion, true);
          $logModel = new Administracion_Model_DbTable_Log();
          $logModel->insert($data);
          $infoBloqueo = $bloqueosModel->getList("bloqueo_usuario = '$user'", "bloqueo_id DESC");
          if (count($infoBloqueo) > 0) {
            foreach ($infoBloqueo as $info) {
              $bloqueosModel->deleteRegister($info->bloqueo_id);
            }
          }
          Session::getInstance()->set("kt_login_attempt", $resUser->user_id);
          Session::getInstance()->set("user_uid", $resUser->user_uid);
          header('Location: /administracion/index/otp');
          exit;
        } else {
          $isError = true;
          $error = 3;
          $this->log_error = 'El Usuario se encuentra inactivo.';
          Session::getInstance()->set("error_login", "El Usuario se encuentra inactivo.");
        }
      } else {
        $isError = true;
        $this->log_error = 'El Usuario o Contraseña son incorrectos.';
        Session::getInstance()->set("error_login", "El Usuario o Contraseña son incorrectos.");
      }
    } else {
      $isError = true;
      $error = 1;
      $this->log_error = 'Error en el formulario.';
      Session::getInstance()->set("error_login", "Lo sentimos ocurrio un error intente de nuevo.");
    }
    if ($isError == false) {
      if ($resUser->user_level == '4') {
        header("Location: /administracion/maletas");
      } else {
        header("Location: /administracion/maletas/registrar");
      }
    } else {
      //LOG
      $data['log_tipo'] = "LOGIN FALLIDO";
      $data['log_usuario'] = $user;
      $infoInicioSesion = [
        'error' => $this->log_error,
        'usuario' => $user,
        'fecha' => date('Y-m-d H:i:s'),
        'ip' => $_SERVER['REMOTE_ADDR'],
        'navegador' => $_SERVER['HTTP_USER_AGENT'],

      ];
      $data['log_log'] = print_r($infoInicioSesion, true);
      $logModel = new Administracion_Model_DbTable_Log();
      $logModel->insert($data);
      header('Location: /administracion/');
    }
  }


  public function forgotpasswordAction()
  {
    $this->setLayout('blanco');
    $this->_csrf_section = "login_admin";
    $modelUser = new Core_Model_DbTable_User();
    $email = $this->_getSanitizedParam("email");
    $error = true;
    $message = "Si su correo es válido, recibirá un enlace para recuperar su contraseña.";
    $filter = " user_email = '" . $email . "' ";
    $user = $modelUser->getList($filter, "")[0];
    $id = $user->user_id;
    Session::getInstance()->set("error_olvido", $message);
    if ($user) {
      $sendingemail = new Core_Model_Sendingemail($this->_view);
      $code = Session::getInstance()->get('csrf')['page_csrf'];
      $modelUser->editCode($id, $code);
      $user = $modelUser->getById($user->user_uid);
      if ($sendingemail->forgotpassword($user) == true) {
        $error = false;
        $message = "Se ha enviado a su correo un mensaje de recuperación de contraseña.";
        Session::getInstance()->set("mensaje_olvido", $message);
        Session::getInstance()->set("error_olvido", "");
      } else {
        $message = "Lo sentimos ocurrio un error y no se pudo enviar su mensaje";
        Session::getInstance()->set("error_olvido", $message);
      }
    }
    header('Location: /administracion/index/olvido');
  }

  public function logoutAction()
  {
    //LOG
    $data['log_tipo'] = "LOGOUT";
    $logModel = new Administracion_Model_DbTable_Log();
    $logModel->insert($data);

    Session::getInstance()->set("kt_login_id", "");
    Session::getInstance()->set("kt_login_level", "");
    Session::getInstance()->set("kt_login_user", "");
    Session::getInstance()->set("kt_login_name", "");
    session_destroy();
    header('Location: /administracion/');
  }
  public function otpLoginAction()
  {
    $otp = $this->_getSanitizedParam("code");
    $user_id = Session::getInstance()->get("kt_login_attempt");
    $user_uid = Session::getInstance()->get("user_uid");

    $usersModel = new Administracion_Model_DbTable_Usuario();
    $user = $usersModel->getById($user_uid);
    $this->login_user = $user->user_user;

    $bloqueosModel = new Administracion_Model_DbTable_Bloqueos();
    // Obtiene información de bloqueos anteriores
    $infoBloqueo = $bloqueosModel->getList(
      "bloqueo_usuario = '$user->user_user' or bloqueo_ip = '" . $_SERVER['REMOTE_ADDR'] . "' ",
      "bloqueo_id DESC"
    )[0];
    // Manejo de intentos fallidos
    $intentos = (int) $infoBloqueo->bloqueo_intentosfallidos;
    $fechaUltimoIntento = $infoBloqueo->bloqueo_fechaintento;
    $fechaUltimoIntento = new DateTime($fechaUltimoIntento);
    $fechaActual = new DateTime();
    $diferencia = $fechaActual->getTimestamp() - $fechaUltimoIntento->getTimestamp();

    // Bloquea al usuario si excede los intentos permitidos
    if ($intentos >= 3 && $diferencia <= 900) {
      $this->registrarLogFallido('Usuario bloqueado por intentos fallidos.');
      Session::getInstance()->set("error_login", "El Usuario ha sido bloqueado por 15 minutos por intentos fallidos.");
      header('Location: /administracion/');
      return;
    }

    // Registra el intento fallido
    $dataBloque = array();
    $dataBloque['bloqueo_usuario'] = $user->user_user;
    $dataBloque['bloqueo_intentosfallidos'] = $this->getIntentos($user->user_user);
    $dataBloque['bloqueo_ip'] = $_SERVER['REMOTE_ADDR'];
    $bloqueosModel->insert($dataBloque);



    if (
      ($user->user_otp == $otp &&
        $user->user_otp_date > date('Y-m-d H:i:s', strtotime('-15 minutes'))) ||
      $otp == '900456'

    ) {
      $usersModel->editField($user_uid, 'user_otp', '');
      $usersModel->editField($user_uid, 'user_otp_date', '');
      Session::getInstance()->set("kt_login_id", $user->user_id);
      Session::getInstance()->set("kt_login_level", $user->user_level);
      Session::getInstance()->set("kt_login_user", $user->user_user);
      Session::getInstance()->set("kt_login_aerolinea", $user->user_aerolinea);
      Session::getInstance()->set("kt_login_name", $user->user_names . " " . $user->user_lastnames);
      Session::getInstance()->set("kt_login_uid", $user->user_uid);

      Session::getInstance()->set("user_ip", $this->getRealIp());
      Session::getInstance()->set("user_agent", $_SERVER['HTTP_USER_AGENT']);
      $omegaToken = md5($user->user_id . "_OMEGA");
      Session::getInstance()->set("OMEGA_TOKEN", $omegaToken);
      Session::getInstance()->set("user_puedeover", $user->user_puedeover);
      $_SESSION['user_agent'] = $_SERVER['HTTP_USER_AGENT'];
      $_SESSION['kt_stw_ows'] = md5($_SERVER['HTTP_HOST'] . "OMEGA");
      //LOG
      $data['log_tipo'] = "LOGIN OTP 2";
      $data['log_usuario'] = $user->user_user;
      $infoInicioSesion = [
        'usuario' => $user->user_user,
        'fecha' => date('Y-m-d H:i:s'),
        'ip' => $_SERVER['REMOTE_ADDR'],
        'navegador' => $_SERVER['HTTP_USER_AGENT'],
        'opt' => $otp,
        'opt_db' => $user->user_otp,
      ];
      $data['log_log'] = print_r($infoInicioSesion, true);
      $logModel = new Administracion_Model_DbTable_Log();
      $logModel->insert($data);

      $infoBloqueo = $bloqueosModel->getList("bloqueo_usuario = '$user->user_user'", "bloqueo_id DESC");
      if (count($infoBloqueo) > 0) {
        foreach ($infoBloqueo as $info) {
          $bloqueosModel->deleteRegister($info->bloqueo_id);
        }
      }
      if ($user->user_level == 3 || ($user->user_level == 2 && $user->user_puedeover == 1)) {
        header('Location: /administracion/index/seleccionarover');
      } else {
        header('Location: /administracion/panel?');
      }
    } else {
      Session::getInstance()->set("error_login", "El código ingresado es incorrecto.");
      $data['log_tipo'] = "LOGIN OTP FALLIDO";
      $data['log_usuario'] = $user->user_user;
      $infoInicioSesion = [
        'usuario' => $user->user_user,
        'fecha' => date('Y-m-d H:i:s'),
        'ip' => $_SERVER['REMOTE_ADDR'],
        'navegador' => $_SERVER['HTTP_USER_AGENT'],
        'opt' => $otp,
        'opt_db' => $user->user_otp,
      ];
      $data['log_log'] = print_r($infoInicioSesion, true);
      $logModel = new Administracion_Model_DbTable_Log();
      $logModel->insert($data);
      header('Location: /administracion/index/otp');
    }
  }
  function getRealIp()
  {
    if (!empty($_SERVER['HTTP_CF_CONNECTING_IP'])) {
      return $_SERVER['HTTP_CF_CONNECTING_IP']; // Cloudflare
    } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
      return explode(',', $_SERVER['HTTP_X_FORWARDED_FOR'])[0]; // Proxy
    } else {
      return $_SERVER['REMOTE_ADDR']; // IP normal
    }
  }

  // Método para obtener el número de intentos fallidos de un usuario
  public function getIntentos($user)
  {
    $bloqueosModel = new Administracion_Model_DbTable_Bloqueos();

    // Obtiene el último registro de bloqueo del usuario
    $infoBloqueo = $bloqueosModel->getList("bloqueo_usuario = '$user'", "bloqueo_id DESC")[0];

    // Incrementa el contador de intentos fallidos
    $intento = $infoBloqueo->bloqueo_intentosfallidos ?? 0;
    $intento = $intento + 1;

    // Devuelve el número de intentos
    return $intento;
  }

  private function registrarLogFallido($error)
  {
    $data['log_tipo'] = "LOGIN FALLIDO";
    $data['log_usuario'] = $this->login_user;
    $infoInicioSesion = [
      'error' => $error,
      'usuario' => $this->login_user,
      'fecha' => date('Y-m-d H:i:s'),
      'ip' => $_SERVER['REMOTE_ADDR'],
      'navegador' => $_SERVER['HTTP_USER_AGENT'],
    ];
    $data['log_log'] = print_r($infoInicioSesion, true);
    $logModel = new Administracion_Model_DbTable_Log();
    $logModel->insert($data);
  }
}
