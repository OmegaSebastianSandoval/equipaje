<?php

/**
 *
 */

class Page_indexController extends Page_mainController
{


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
  public function reclamosAction()
  {
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
  public function getBagAction()
  {
    $res = array();
    $code = $this->_getSanitizedParam("code");
    $modelData = new Administracion_Model_DbTable_Maletas();
    $data = $modelData->getList("maleta_codigo = '$code'", "maleta_fingreso DESC")[0];
    if ($data) {
      $res['status'] = 'success';
      $res['maleta_aerolinea'] = $data->maleta_aerolinea;
      $res['maleta_estado'] = $data->maleta_estado;
    } else {
      $res['status'] = 'error';
    }
    die(json_encode($res));
  }
  public function updateAction()
  {
    $data = array();
    $maletasModel = new Administracion_Model_DbTable_Maletas();
    $code = $this->_getSanitizedParam("maleta_codigo");
    $maleta = $maletasModel->getList("maleta_codigo = '$code'", "")[0];
    if ($maleta->maleta_estado == '') {
      $maletasModel->editField($maleta->maleta_id, 'maleta_estado', '1');
      $maletasModel->editField($maleta->maleta_id, 'maleta_fsalida', date("Y-m-d"));
      $maletasModel->editField($maleta->maleta_id, 'maleta_hsalida', date("H:i:s"));
      $aerolineasModel = new Administracion_Model_DbTable_Aerolineas();
      $aerolinea = $aerolineasModel->getList("aerolinea_codigo = '" . $maleta->maleta_aerolinea . "'", "")[0];
      $mailModel = new Core_Model_Sendingemail($this->_view);


      
      $esMaletaOver = $maleta->maleta_over_tipo ? true : false;
      $overTipo = $maleta->maleta_over_tipo;
      $overLugar = $maleta->maleta_over_lugar;
      $mail = $mailModel->notificarReclamo($aerolinea, $code, $esMaletaOver, $overTipo, $overLugar);
	  
      $data['log_tipo'] = 'RECLAMO'. $esMaletaOver ? ' OVER' : '';
      $data['log_maleta'] = $maleta->maleta_id . '' . $code;
      $data['log_fecha'] = date("Y-m-d H:i:s");
      $data['log_usuario'] = Session::getInstance()->get('user')->user_id;
      $logModel = new Administracion_Model_DbTable_Logmaletas();
      $logModel->insert($data);
    }
    header("Location: /page/index/reclamos/");
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
    $maletasModel = new Administracion_Model_DbTable_Maletas();
    $aerolineasModal = new Administracion_Model_DbTable_Aerolineas();
    $maletas = $maletasModel->getList_maletas_aerolineas("(maleta_over_tipo  = '' OR maleta_over_tipo IS NULL) AND maleta_estado = ''
         OR (maleta_over_tipo  = '' OR maleta_over_tipo IS NULL) AND (maleta_estado = '1' AND CONCAT(maleta_fsalida, ' ', maleta_hsalida) >= NOW() - INTERVAL 5 MINUTE)", "CONCAT(maleta_fingreso, ' ', maleta_hingreso) ASC");

    $response = array('status' => 'success', 'data' => array());

    foreach ($maletas as $key => $value) {
      if ($value->maleta_conexion == '1') {
        $aerolinea_conexion = $aerolineasModal->getList("aerolinea_codigo = '" . $value->maleta_aerolinea_conexion . "'", "")[0];
        $value->aerolinea_nombre = $aerolinea_conexion->aerolinea_nombre;
        $value->aerolinea_imagen = $aerolinea_conexion->aerolinea_imagen;
      }
      if (!$value->aerolinea_nombre) {
        $value->aerolinea_nombre = 'Aerolínea no registrada';
      }
      if ($value->maleta_estado == '') {
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

      $response['data'][] = array(
        'maleta_codigo' => $value->maleta_codigo,
        'aerolinea_imagen' => $value->aerolinea_imagen,
        'aerolinea_nombre' => $value->aerolinea_nombre,
        'maleta_fingreso' => $value->maleta_fingreso,
        'maleta_hingreso' => $value->maleta_hingreso,
        'maleta_estado' => $value->maleta_estado,
        'maleta_color' => $value->maleta_color,
        'tiempo_espera' => $tiempo_espera // Agregar el tiempo de espera calculado
      );
    }

    die(json_encode($response));
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
    $maletasModel = new Administracion_Model_DbTable_Maletas();
    $aerolineasModal = new Administracion_Model_DbTable_Aerolineas();
    $maletas = $maletasModel->getList_maletas_aerolineas("maleta_over_tipo  = '1' AND maleta_estado = ''
         OR (maleta_over_tipo  = '1' AND maleta_estado = '1' AND CONCAT(maleta_fsalida, ' ', maleta_hsalida) >= NOW() - INTERVAL 5 MINUTE)", "CONCAT(maleta_fingreso, ' ', maleta_hingreso) ASC");

    $response = array('status' => 'success', 'data' => array());

    foreach ($maletas as $key => $value) {
      if ($value->maleta_conexion == '1') {
        $aerolinea_conexion = $aerolineasModal->getList("aerolinea_codigo = '" . $value->maleta_aerolinea_conexion . "'", "")[0];
        $value->aerolinea_nombre = $aerolinea_conexion->aerolinea_nombre;
        $value->aerolinea_imagen = $aerolinea_conexion->aerolinea_imagen;
      }
      if (!$value->aerolinea_nombre) {
        $value->aerolinea_nombre = 'Aerolínea no registrada';
      }
      if ($value->maleta_estado == '') {
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
        'maleta_over_lugar' => $value->maleta_over_lugar
      );
    }

    die(json_encode($response));
  }
  public function getNoReclamadasOverDosAction()
  {
    $maletasModel = new Administracion_Model_DbTable_Maletas();
    $aerolineasModal = new Administracion_Model_DbTable_Aerolineas();
    $maletas = $maletasModel->getList_maletas_aerolineas("maleta_over_tipo  = '2' AND maleta_estado = ''
         OR   ( maleta_over_tipo  = '2' AND  maleta_estado = '1' AND CONCAT(maleta_fsalida, ' ', maleta_hsalida) >= NOW() - INTERVAL 5 MINUTE)", "CONCAT(maleta_fingreso, ' ', maleta_hingreso) ASC");

    $response = array('status' => 'success', 'data' => array());

    foreach ($maletas as $key => $value) {
      if ($value->maleta_conexion == '1') {
        $aerolinea_conexion = $aerolineasModal->getList("aerolinea_codigo = '" . $value->maleta_aerolinea_conexion . "'", "")[0];
        $value->aerolinea_nombre = $aerolinea_conexion->aerolinea_nombre;
        $value->aerolinea_imagen = $aerolinea_conexion->aerolinea_imagen;
      }
      if (!$value->aerolinea_nombre) {
        $value->aerolinea_nombre = 'Aerolínea no registrada';
      }
      if ($value->maleta_estado == '') {
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
      );
    }

    die(json_encode($response));
  }
  public function getNoReclamadasOverTresAction()
  {
    $maletasModel = new Administracion_Model_DbTable_Maletas();
    $aerolineasModal = new Administracion_Model_DbTable_Aerolineas();
    $maletas = $maletasModel->getList_maletas_aerolineas("maleta_over_tipo  = '3' AND maleta_estado = ''
         OR   ( maleta_over_tipo  = '3' AND  maleta_estado = '1' AND CONCAT(maleta_fsalida, ' ', maleta_hsalida) >= NOW() - INTERVAL 5 MINUTE)", "CONCAT(maleta_fingreso, ' ', maleta_hingreso) ASC");

    $response = array('status' => 'success', 'data' => array());

    foreach ($maletas as $key => $value) {
      if ($value->maleta_conexion == '1') {
        $aerolinea_conexion = $aerolineasModal->getList("aerolinea_codigo = '" . $value->maleta_aerolinea_conexion . "'", "")[0];
        $value->aerolinea_nombre = $aerolinea_conexion->aerolinea_nombre;
        $value->aerolinea_imagen = $aerolinea_conexion->aerolinea_imagen;
      }
      if (!$value->aerolinea_nombre) {
        $value->aerolinea_nombre = 'Aerolínea no registrada';
      }
      if ($value->maleta_estado == '') {
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
      );
    }

    die(json_encode($response));
  }
}
