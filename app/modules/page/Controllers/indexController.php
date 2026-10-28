<?php

/**
 *
 */

class Page_indexController extends Page_mainController
{
  /**
   * Maximo de maletas por lote en cada envio del reclamo publico.
   * @var integer
   */
  protected $maxLoteReclamo = 30;


  // Función correspondiente a la vista index, vista principal del aplicativo
  public function indexAction()
  {
    $this->setLayout('blanco');

    header('Location: /administracion/');
    exit;
    $this->_view->error = $this->_getSanitizedParam('error');
  }
  // Función correspondiente a la vista registro
  public function registroAction()
  {
  }
  /**
   * Reclamo de maletas por lotes (vista publica, sin login). Solo envia correo, no WhatsApp.
   */
  public function reclamosAction()
  {
     return $this->inactivarReclamos();
    // La sesion (y con ella el CSRF) solo arranca si el usuario acepto las cookies (ver bootstrap.php)
    $this->_view->consentimiento = ($_COOKIE['consentimiento'] ?? '') === 'aceptado';
    $this->_view->csrf_reclamos = $this->_view->consentimiento ? $this->csrfReclamos() : '';
    $this->_view->max_lote = $this->maxLoteReclamo;
  }

  /**
   * Para inactivar el reclamo publico: descomentar el return al inicio de reclamosAction, getBagAction y reclamoAjaxAction.
   */
  private function inactivarReclamos()
  {
    header('Location: /administracion/');
    exit;
  }

  /**
   * Token CSRF del reclamo publico. Se guarda en la sesion (anonima) y no se regenera en cada peticion
   * para que el envio por lotes no lo invalide.
   */
  private function csrfReclamos()
  {
    if (empty($_SESSION['csrf_reclamos_page'])) {
      Session::getInstance()->set('csrf_reclamos_page', bin2hex(random_bytes(32)));
    }
    return Session::getInstance()->get('csrf_reclamos_page');
  }

  private function csrfReclamosValido()
  {
    $enviado = (string) $this->_getSanitizedParam('csrf');
    return !empty($_SESSION['csrf_reclamos_page']) && hash_equals($_SESSION['csrf_reclamos_page'], $enviado);
  }

  /**
   * Reclamo de una maleta del lote (el frontend envia una por una).
   */
  public function reclamoAjaxAction()
  {
    // return $this->inactivarReclamos();
    $this->setLayout('blanco');
    header('Content-Type: application/json');
    if (!$this->csrfReclamosValido()) {
      die(json_encode(['status' => 'error', 'codigo' => 'sesion', 'mensaje' => 'Sesión vencida, recargue la página']));
    }
    // Una maleta por peticion: tiempo para el correo, terminar aunque cierren la pagina y liberar la sesion
    set_time_limit(300);
    ignore_user_abort(true);
    session_write_close();
    die(json_encode($this->reclamarMaleta($this->_getSanitizedParam("maleta_codigo"))));
  }

  /**
   * Marca una maleta como reclamada y notifica por correo a la aerolinea (la de conexion si aplica).
   * @return array ['status' => 'ok'|'error', 'mensaje' => string]
   */
  private function reclamarMaleta($code)
  {
    if (!preg_match('/^\d{10}$/', (string) $code)) {
      return ['status' => 'error', 'mensaje' => 'El código debe tener 10 dígitos'];
    }
    $maletasModel = new Administracion_Model_DbTable_Maletas();
    $maleta = $maletasModel->getList("maleta_codigo = '$code'", "maleta_fingreso DESC")[0] ?? null;
    if (!$maleta) {
      return ['status' => 'error', 'mensaje' => 'La maleta no está registrada'];
    }
    if ($maleta->maleta_estado != '') {
      return ['status' => 'error', 'codigo' => 'existe', 'mensaje' => 'La maleta ya fue reclamada'];
    }
    $maletasModel->editField($maleta->maleta_id, 'maleta_estado', '1');
    $maletasModel->editField($maleta->maleta_id, 'maleta_fsalida', date("Y-m-d"));
    $maletasModel->editField($maleta->maleta_id, 'maleta_hsalida', date("H:i:s"));

    $aerolineasModel = new Administracion_Model_DbTable_Aerolineas();
    $codigoAerolinea = $maleta->maleta_conexion == '1' ? $maleta->maleta_aerolinea_conexion : $maleta->maleta_aerolinea;
    $aerolinea = $aerolineasModel->getList("aerolinea_codigo = '" . $codigoAerolinea . "'", "")[0] ?? null;
    $esMaletaOver = $maleta->maleta_over_tipo ? true : false;
    if ($aerolinea) {
      $mailModel = new Core_Model_Sendingemail($this->_view);
      $mailModel->notificarReclamo($aerolinea, $code, $esMaletaOver, $maleta->maleta_over_tipo, $maleta->maleta_over_lugar);
    }

    $data = array();
    $data['log_tipo'] = 'RECLAMO' . ($esMaletaOver ? ' OVER' : '');
    $data['log_maleta'] = $maleta->maleta_id . '' . $code;
    $data['log_fecha'] = date("Y-m-d H:i:s");
    $data['log_usuario'] = $_SESSION['kt_login_id'] ?? '';
    $logModel = new Administracion_Model_DbTable_Logmaletas();
    $logModel->insert($data);
    return ['status' => 'ok', 'mensaje' => 'Reclamada'];
  }

  public function registrarAction()
  {
    $identificacion = $this->_getSanitizedParam('identificacion');
    $usersModel = new Administracion_Model_DbTable_Usuarios();
    $user = $usersModel->getList("identificacion = '$identificacion'", "")[0];

    if ($user) {
      $key = $this->generarClave();
      $user->key = $key;
      $usersModel->editField($user->id, 'password', password_hash($key, PASSWORD_DEFAULT));
      $usersModel->editField($user->id, 'temporal_password', '1');
    }

    $mailModel = new Core_Model_Sendingemail($this->_view);
    if ($mailModel->enviarRegistro($user) == 1) {
      $response = [
        'status' => 'success',
        'message' => 'Se ha enviado un correo con la contraseña temporal'
      ];
    } else {
      $response = [
        'status' => 'error',
        'message' => 'No se ha podido enviar el correo, por favor intente de nuevo'
      ];
    }
    die(json_encode($response));
  }
  private function generarClave()
  {
    $longitud = 8;
    $caracteres = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ!@#$%^&*()';
    $caracteresLongitud = strlen($caracteres);
    $contraseña = '';
    for ($i = 0; $i < $longitud; $i++) {
      $contraseña .= $caracteres[random_int(0, $caracteresLongitud - 1)];
    }
    return $contraseña;
  }
  public function loginAction()
  {
    $user = $this->_getSanitizedParam('user');
    $password = $this->_getSanitizedParam('password');
    $usersModel = new Administracion_Model_DbTable_Usuario();
    $user = $usersModel->getList("user_user = '$user' AND user_state = '1' AND user_level = '3'", "")[0];
    if ($user) {
      if (password_verify($password, $user->user_password)) {
        Session::getInstance()->set('user', $user);
        $response = [
          'status' => 'success',
          'message' => 'Bienvenido',
          'redirect' => '/page/index/reclamos'
        ];
      } else {
        $response = [
          'status' => 'error',
          'message' => 'Contraseña incorrecta'
        ];
      }
    } else {
      $response = [
        'status' => 'error',
        'message' => 'Usuario no encontrado'
      ];
    }
    die(json_encode($response));
  }
  public function getAirlineAction()
  {
    $res = array();
    $code = $this->_getSanitizedParam("code");
    $modelData = new Administracion_Model_DbTable_Aerolineas();
    $data = $modelData->getList("aerolinea_estado = '1' AND aerolinea_codigo = '$code'", "")[0];
    if ($data) {
      $res['status'] = 'success';
      $res['airline_name'] = $data->aerolinea_nombre;
      $res['airline_code'] = $data->aerolinea_codigo;
    } else {
      $res['status'] = 'error';
    }
    die(json_encode($res));
  }
  /**
   * Datos de una maleta para el reclamo por lotes (aerolinea, vuelo, salida y estado).
   */
  public function getBagAction()
  {
    // return $this->inactivarReclamos();
    $code = $this->_getSanitizedParam("code");
    $maleta = (new Administracion_Model_DbTable_Maletas())->getList("maleta_codigo = '$code'", "maleta_fingreso DESC")[0] ?? null;
    if (!$maleta) {
      die(json_encode(['status' => 'error']));
    }
    $aerolineas = array();
    foreach ((new Administracion_Model_DbTable_Aerolineas())->getList() as $value) {
      $aerolineas[$value->aerolinea_codigo] = $value->aerolinea_nombre;
    }
    $vuelo = $maleta->maleta_vuelo_id ? (new Core_Model_Vuelos())->porIds([$maleta->maleta_vuelo_id])[$maleta->maleta_vuelo_id] ?? null : null;
    $info = (new Administracion_Model_DbTable_Informacion())->getById(1);
    $salida = Core_Model_Vuelos::estadoSalida($maleta, $vuelo, (int) $info->info_pagina_alerta_minutos);
    $aerolinea = $aerolineas[$maleta->maleta_aerolinea] ?? $maleta->maleta_aerolinea;
    if ($maleta->maleta_conexion == '1' && isset($aerolineas[$maleta->maleta_aerolinea_conexion])) {
      $aerolinea .= ' → ' . $aerolineas[$maleta->maleta_aerolinea_conexion];
    }
    die(json_encode([
      'status' => 'success',
      'maleta_aerolinea' => $maleta->maleta_aerolinea,
      'maleta_estado' => $maleta->maleta_estado,
      'aerolinea' => $aerolinea,
      'vuelo' => $salida['vuelo'],
      'salida' => $salida['salida'],
      'tiempo_restante' => $salida['tiempo_restante'],
      'urgencia' => $salida['urgencia'],
      'over_tipo' => $maleta->maleta_over_tipo,
    ]));
  }

  /**
   * Cierra la sesion (el boton Salir del encabezado de page) y vuelve al login de administracion.
   */
  public function logoutAction()
  {
    if (session_status() === PHP_SESSION_ACTIVE) {
      $_SESSION = array();
      session_destroy();
    }
    header('Location: /administracion/');
    exit;
  }

  /**
   * Formulario anterior (una maleta, sin CSRF). Ya no se usa: el reclamo se hace por lotes con reclamoAjax.
   */
  public function updateAction()
  {
    header("Location: /page/index/reclamos/");
    exit;
  }

  public function noreclamadasAction()
  {
    $maletasModel = new Administracion_Model_DbTable_Maletas();
    $maletas = $maletasModel->getList_maletas_aerolineas("maleta_estado = ''", "maleta_fingreso, maleta_codigo DESC");
    $this->_view->maletas = $maletas;
  }
  //  public function getNoReclamadasAction()
  //  {
  //    $response = array();
  //    $maletasModel = new Administracion_Model_DbTable_Maletas();
  //    $maletas = $maletasModel->getList_maletas_aerolineas("maleta_estado = ''", "maleta_fingreso, maleta_codigo DESC");
  //    $html = '';
  //    foreach ($maletas as $key => $value) {
  //      $html .= '
  //        <tr>
  //          <td>'.$value->maleta_codigo.'</td>
  //          ';
  //      if($value->aerolinea_imagen != ''){
  //        $html .= '
  //            <td><img src="/images/'.$value->aerolinea_imagen.'" alt=""></td>
  //            ';
  //        }else{
  //        $html .= '
  //            <td>'.$value->aerolinea_nombre.'</td>
  //             ';
  //        }
  //        $html .= '
  //          <td>'.$value->maleta_fingreso.'</td>
  //          <td>'.$value->maleta_hingreso.'</td>
  //        </tr>
  //      ';
  //    }
  //    $response['html'] = $html;
  //    die(json_encode($response));
  //  }
  public function getNoReclamadasAction()
  {
    $limite = date('Y-m-d H:i:s', strtotime('-5 minutes'));
    $this->responderNoReclamadas("(maleta_over_tipo  = '' OR maleta_over_tipo IS NULL) AND maleta_estado = ''
         OR (maleta_over_tipo  = '' OR maleta_over_tipo IS NULL) AND (maleta_estado = '1' AND CONCAT(maleta_fsalida, ' ', maleta_hsalida) >= '$limite')");
  }

  //*****************************************
  //NUEVO OVER
  //*****************************************
  public function noreclamadasoverunoAction()
  {
    $maletasModel = new Administracion_Model_DbTable_Maletas();
    $maletas = $maletasModel->getList_maletas_aerolineas("maleta_over_tipo  = '1' AND maleta_estado = ''", "maleta_fingreso, maleta_codigo DESC");
    $this->_view->maletas = $maletas;
  }
  public function noreclamadasoverdosAction()
  {
    $maletasModel = new Administracion_Model_DbTable_Maletas();
    $maletas = $maletasModel->getList_maletas_aerolineas("maleta_over_tipo  = '2' AND maleta_estado = ''", "maleta_fingreso, maleta_codigo DESC");
    $this->_view->maletas = $maletas;
  }
  public function noreclamadasovertresAction()
  {
    $maletasModel = new Administracion_Model_DbTable_Maletas();
    $maletas = $maletasModel->getList_maletas_aerolineas("maleta_over_tipo  = '3' AND maleta_estado = ''", "maleta_fingreso, maleta_codigo DESC");
    $this->_view->maletas = $maletas;
  }
  public function getNoReclamadasOverUnoAction()
  {
    $limite = date('Y-m-d H:i:s', strtotime('-5 minutes'));
    $this->responderNoReclamadas("maleta_over_tipo  = '1' AND maleta_estado = ''
         OR (maleta_over_tipo  = '1' AND maleta_estado = '1' AND CONCAT(maleta_fsalida, ' ', maleta_hsalida) >= '$limite')");
  }

  public function getNoReclamadasOverDosAction()
  {
    $limite = date('Y-m-d H:i:s', strtotime('-5 minutes'));
    $this->responderNoReclamadas("maleta_over_tipo  = '2' AND maleta_estado = ''
         OR   ( maleta_over_tipo  = '2' AND  maleta_estado = '1' AND CONCAT(maleta_fsalida, ' ', maleta_hsalida) >= '$limite')");
  }

  public function getNoReclamadasOverTresAction()
  {
    $limite = date('Y-m-d H:i:s', strtotime('-5 minutes'));
    $this->responderNoReclamadas("maleta_over_tipo  = '3' AND maleta_estado = ''
         OR   ( maleta_over_tipo  = '3' AND  maleta_estado = '1' AND CONCAT(maleta_fsalida, ' ', maleta_hsalida) >= '$limite')");
  }


  /**
   * Respuesta JSON de las pantallas publicas (normal y over). Los filtros los define cada accion;
   * aqui se agregan el vuelo, la salida, el tiempo restante y la urgencia.
   */
  private function responderNoReclamadas($filtro)
  {
    $maletasModel = new Administracion_Model_DbTable_Maletas();
    $aerolineasModal = new Administracion_Model_DbTable_Aerolineas();
    $maletas = $maletasModel->getList_maletas_aerolineas($filtro, "CONCAT(maleta_fingreso, ' ', maleta_hingreso) ASC");
    $vuelos = (new Core_Model_Vuelos())->porIds(array_map(function ($m) {
      return $m->maleta_vuelo_id;
    }, $maletas));
    $alertaMinutos = (int) $this->_view->infopage->info_pagina_alerta_minutos;
    $conexiones = array();

    $response = array('status' => 'success', 'data' => array());

    foreach ($maletas as $key => $value) {
      if ($value->maleta_conexion == '1') {
        if (!array_key_exists($value->maleta_aerolinea_conexion, $conexiones)) {
          $conexiones[$value->maleta_aerolinea_conexion] = $aerolineasModal->getList("aerolinea_codigo = '" . $value->maleta_aerolinea_conexion . "'", "")[0];
        }
        $aerolinea_conexion = $conexiones[$value->maleta_aerolinea_conexion];
        $value->aerolinea_nombre = $aerolinea_conexion->aerolinea_nombre;
        $value->aerolinea_imagen = $aerolinea_conexion->aerolinea_imagen;
      }
      if (!$value->aerolinea_nombre) {
        $value->aerolinea_nombre = 'Aerolínea no registrada';
      }
      $pendiente = $value->maleta_estado == '';
      if ($pendiente) {
        $value->maleta_estado = 'En espera';
        $value->maleta_color = 'danger';
      } else {
        $value->maleta_estado = 'Reclamada';
        $value->maleta_color = 'success';
      }

      // Calcular el tiempo de espera
      $fingreso = $value->maleta_fingreso;
      $hingreso = $value->maleta_hingreso;
      $ingreso_datetime = new DateTime("$fingreso $hingreso");
      $now = new DateTime();
      $interval = $now->diff($ingreso_datetime);

      // Formatear el tiempo de espera
      if ($interval->days > 0) {
        $tiempo_espera = $interval->days . ' días, ' . $interval->h . ' horas, ' . $interval->i . ' minutos';
      } elseif ($interval->h > 0) {
        $tiempo_espera = $interval->h . ' horas, ' . $interval->i . ' minutos';
      } elseif ($interval->i > 0) {
        $tiempo_espera = $interval->i . ' minutos';
      } else {
        $tiempo_espera = 'menos de un minuto';
      }

      $salida = Core_Model_Vuelos::estadoSalida($value, $vuelos[$value->maleta_vuelo_id] ?? null, $alertaMinutos);

      $response['data'][] = array(
        'maleta_codigo' => $value->maleta_codigo,
        'aerolinea_imagen' => $value->aerolinea_imagen,
        'aerolinea_nombre' => $value->aerolinea_nombre,
        'maleta_fingreso' => $value->maleta_fingreso,
        'maleta_hingreso' => $value->maleta_hingreso,
        'maleta_estado' => $value->maleta_estado,
        'maleta_color' => $value->maleta_color,
        'tiempo_espera' => $tiempo_espera,
        'maleta_over_tipo' => $value->maleta_over_tipo,
        'maleta_over_lugar' => $value->maleta_over_lugar,
        'vuelo' => $salida['vuelo'],
        'salida' => $salida['salida'],
        'tiempo_restante' => $pendiente ? $salida['tiempo_restante'] : '—',
        // Solo se resaltan las maletas que siguen sin reclamar
        'urgencia' => $pendiente ? $salida['urgencia'] : 'ninguna',
      );
    }

    die(json_encode($response));
  }
}
