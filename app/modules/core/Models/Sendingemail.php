<?php

/**
 * Modelo del modulo Core que se encarga de  enviar todos los correos nesesarios del sistema.
 */
class Core_Model_Sendingemail
{
  /**
   * Intancia de la calse emmail
   * @var class
   */
  protected $email;

  protected $_view;

  public function __construct($view)
  {
    $this->email = new Core_Model_Mail();
    $this->_view = $view;
  }


  public function forgotpassword($user)
  {
    if ($user) {
      $code = [];
      $code['user'] = $user->user_id;
      $code['code'] = $user->code;
      $codeEmail = base64_encode(json_encode($code));
      $this->_view->url = "http://" . $_SERVER['HTTP_HOST'] . "/administracion/index/changepassword?code=" . $codeEmail;
      $this->_view->host = "http://" . $_SERVER['HTTP_HOST'] . "/";
      $this->_view->nombre = $user->user_names . " " . $user->user_lastnames;
      $this->_view->usuario = $user->user_user;
      /*fin parametros de la vista */
      //$this->email->getMail()->setFrom("desarrollo4@omegawebsystems.com","Intranet Coopcafam");
      $this->email->getMail()->addAddress($user->user_email, $user->user_names . " " . $user->user_lastnames);
      $content = $this->_view->getRoutPHP('/../app/modules/core/Views/templatesemail/forgotpassword.php');
      $this->email->getMail()->Subject = "Recuperación de Contraseña Gestor de Contenidos";
      $this->email->getMail()->msgHTML($content);
      $this->email->getMail()->AltBody = $content;
      if ($this->email->sed() == true) {
        return true;
      } else {
        return false;
      }
    }
  }
  public function sendMailContact($data, $mail)
  {
    $this->_view->data = $data;
    $this->email->getMail()->addAddress($mail, "");
    $content = $this->_view->getRoutPHP('/../app/modules/core/Views/templatesemail/');
    $this->email->getMail()->Subject = '';
    $this->email->getMail()->msgHTML($content);
    $this->email->getMail()->AltBody = $content;
    // $this->email->getMail()->addBCC($informacion->info_pagina_correo_oculto);
    if ($this->email->sed() == true) {
      return 1;
    } else {
      return 2;
    }
  }
  public function enviarRegistro($user)
  {
    $this->_view->user = $user;
    $this->email->getMail()->addAddress($user->email, $user->razon_social);
    $content = $this->_view->getRoutPHP('/../app/modules/core/Views/templatesemail/enviarRegistro.php');
    $this->email->getMail()->Subject = 'Contraseña temporal Opain';
    $this->email->getMail()->msgHTML($content);
    $this->email->getMail()->AltBody = $content;
    // $this->email->getMail()->addBCC($informacion->info_pagina_correo_oculto);
    if ($this->email->sed() == true) {
      return 1;
    } else {
      return 2;
    }
  }
  public function enviarRecuperacion($user, $token)
  {
    $this->_view->user = $user;
    $this->_view->token = $token;
    $this->email->getMail()->addAddress($user->email, $user->razon_social);
    $content = $this->_view->getRoutPHP('/../app/modules/core/Views/templatesemail/enviarRecuperacion.php');
    $this->email->getMail()->Subject = 'Recuperación de contraseña Opain';
    $this->email->getMail()->msgHTML($content);
    $this->email->getMail()->AltBody = $content;
    $this->email->getMail()->addBCC("desarrollo2@omegawebsystems.com", "desarrollo2");
    if ($this->email->sed() == true) {
      return 1;
    } else {
      return 2;
    }
  }
  public function notificarRegistro($user, $code, $overTipo = null, $overLugar = null)
  {

    $this->_view->user = $user;
    $this->_view->code = $code;
    $this->_view->overTipo = $overTipo;
    $this->_view->overLugar = $overLugar;
    if (!Session::getInstance()->get('over')) {
      $correos = explode(",", $user->aerolinea_correo);
    } else {
      $correos = explode(",", $user->aerolinea_correo_over);
    }

    if (!$correos)
      return 2;
    if (APPLICATION_ENV != 'development') {
      foreach ($correos as $correo) {
        $this->email->getMail()->addAddress($correo, $user->aerolinea_nombre);
      }
    }

    $this->email->getMail()->addBCC('desarrollo8+2@omegawebsystems.com', 'Desarrollo Omega');
    $content = $this->_view->getRoutPHP('/../app/modules/core/Views/templatesemail/notificarRegistro.php');
    $this->email->getMail()->Subject = 'Notificación Ingreso de Maletas';
    $this->email->getMail()->msgHTML($content);
    $this->email->getMail()->AltBody = $content;
    // $this->email->getMail()->addBCC($informacion->info_pagina_correo_oculto);
    //$this->email->getMail()->addBCC("desarrollo2@omegawebsystems.com", "desarrollo2");
    if ($this->email->sed() == true) {
      return 1;
    } else {
      return 2;
    }
  }
  public function notificarReclamo($user, $code, $esOver = false, $overTipo = null, $overLugar = null)
  {
    $this->_view->user = $user;
    $this->_view->code = $code;
    $this->_view->overTipo = $overTipo;
    $this->_view->overLugar = $overLugar;
    if (!Session::getInstance()->get('over') && !$esOver) {
      $correos = explode(",", $user->aerolinea_correo);
    } else {
      $esOver = true;
      $correos = explode(",", $user->aerolinea_correo_over);
    }
    $this->_view->esOver = $esOver;
    if (!$correos)
      return 2;
    if (APPLICATION_ENV != 'development') {
      foreach ($correos as $correo) {
        $this->email->getMail()->addAddress($correo, $user->aerolinea_nombre);
      }
    }
    $this->email->getMail()->addBCC("desarrollo8+2@omegawebsystems.com", "desarrollo");

    $content = $this->_view->getRoutPHP('/../app/modules/core/Views/templatesemail/notificarReclamo.php');
    $this->email->getMail()->Subject = 'Notificación Salida de Maletas';
    $this->email->getMail()->msgHTML($content);
    $this->email->getMail()->AltBody = $content;
    // $this->email->getMail()->addBCC($informacion->info_pagina_correo_oculto);
    if ($this->email->sed() == true) {
      return 1;
    } else {
      return 2;
    }
  }
  public function enviarOTP($user, $code)
  {
    $this->_view->user = $user;
    $this->_view->code = $code;
    $correos = explode(',', $user->user_email);
    if (APPLICATION_ENV === 'production') {
      foreach ($correos as $correo) {
        $this->email->getMail()->addAddress($correo, $user->user_names);
      }
      $this->email->getMail()->addCC("notificacionesnivel3@eldorado.aero", "Código de verificación");

    }

    $this->email->getMail()->addBCC("desarrollo8@omegawebsystems.com", "desarrollo");

    $content = $this->_view->getRoutPHP('/../app/modules/core/Views/templatesemail/enviarOTP.php');
    $this->email->getMail()->Subject = 'Código de verificación';
    $this->email->getMail()->msgHTML($content);
    $this->email->getMail()->AltBody = $content;
    // $this->email->getMail()->addBCC($informacion->info_pagina_correo_oculto);
    if ($this->email->sed() == true) {
      return 1;
    } else {
      return 2;
    }
  }
}
