<?php

/**
 * Controlador de Maletas que permite la  creacion, edicion  y eliminacion de los Maletas del Sistema
 */
class Administracion_maletasController extends Administracion_mainController
{
  public $botonpanel = 5;
  /**
   * $mainModel  instancia del modelo de  base de datos Maletas
   * @var modeloContenidos
   */
  public $mainModel;

  /**
   * $route  url del controlador base
   * @var string
   */
  protected $route;

  /**
   * $pages cantidad de registros a mostrar por pagina]
   * @var integer
   */
  protected $pages;

  /**
   * $namefilter nombre de la variable a la fual se le van a guardar los filtros
   * @var string
   */
  protected $namefilter;

  /**
   * $_csrf_section  nombre de la variable general csrf  que se va a almacenar en la session
   * @var string
   */
  protected $_csrf_section = "administracion_maletas";

  /**
   * $namepages nombre de la pvariable en la cual se va a guardar  el numero de seccion en la paginacion del controlador
   * @var string
   */
  protected $namepages;



  /**
   * Inicializa las variables principales del controlador maletas .
   *
   * @return void.
   */
  public function init()
  {
    $this->mainModel = new Administracion_Model_DbTable_Maletas();
    $this->namefilter = "parametersfiltermaletas";
    $this->route = "/administracion/maletas";
    $this->namepages = "pages_maletas";
    $this->namepageactual = "page_actual_maletas";
    $this->_view->route = $this->route;
    if (Session::getInstance()->get($this->namepages)) {
      $this->pages = Session::getInstance()->get($this->namepages);
    } else {
      $this->pages = 20;
    }
    parent::init();
  }


  /**
   * Recibe la informacion y  muestra un listado de  Maletas con sus respectivos filtros.
   *
   * @return void.
   */
  public function indexAction()
  {
    $title = "Administración de Maletas";
    $this->getLayout()->setTitle($title);
    $this->_view->titlesection = $title;
    $this->filters();
    $this->_view->csrf = Session::getInstance()->get('csrf')[$this->_csrf_section];
    $filters = (object) Session::getInstance()->get($this->namefilter);
    $this->_view->filters = $filters;
    $filters = $this->getFilter();
    $level = Session::getInstance()->get('kt_login_level');

    $puedeOver = Session::getInstance()->get('user_puedeover');

    if ($level == 3) {
      $filters .= " AND maleta_over_tipo != ''"; 
    } elseif ($level == 2 && $puedeOver != 1) {
      $filters .= " AND (maleta_over_tipo = '' OR maleta_over_tipo IS NULL)";
    }

    $order = "CONCAT(maleta_fingreso, ' ', maleta_hingreso) DESC";
    $list = $this->mainModel->getList($filters, $order);
    $amount = $this->pages;
    $page = $this->_getSanitizedParam("page");
    if (!$page && Session::getInstance()->get($this->namepageactual)) {
      $page = Session::getInstance()->get($this->namepageactual);
      $start = ($page - 1) * $amount;
    } else if (!$page) {
      $start = 0;
      $page = 1;
      
      Session::getInstance()->set($this->namepageactual, $page);
    } else {
      Session::getInstance()->set($this->namepageactual, $page);
      $start = ($page - 1) * $amount;
    }
    $this->_view->register_number = count($list);
    $this->_view->pages = $this->pages;
    $this->_view->totalpages = ceil(count($list) / $amount);
    $this->_view->page = $page;
    $this->_view->lists = $this->mainModel->getListPages($filters, $order, $start, $amount);
    $this->_view->csrf_section = $this->_csrf_section;
    $this->_view->list_maleta_aerolinea = $this->getMaletaaerolinea();
    $this->_view->list_maleta_estado = $this->getMaletaestado();
  }

  /**
   * Genera la Informacion necesaria para editar o crear un  Maletas  y muestra su formulario
   *
   * @return void.
   */
  public function manageAction()
  {
    $this->_view->route = $this->route;
    $this->_csrf_section = "manage_maletas_" . date("YmdHis");
    $this->_csrf->generateCode($this->_csrf_section);
    $this->_view->csrf_section = $this->_csrf_section;
    $this->_view->csrf = Session::getInstance()->get('csrf')[$this->_csrf_section];
    $this->_view->list_maleta_aerolinea = $this->getMaletaaerolinea();
    $this->_view->list_maleta_estado = $this->getMaletaestado();
    $id = $this->_getSanitizedParam("id");
    if ($id > 0) {
      $content = $this->mainModel->getById($id);
      if ($content->maleta_id) {
        $this->_view->content = $content;
        $this->_view->routeform = $this->route . "/update";
        $title = "Actualizar Maletas";
        $this->getLayout()->setTitle($title);
        $this->_view->titlesection = $title;
      } else {
        $this->_view->routeform = $this->route . "/insert";
        $title = "Crear Maletas";
        $this->getLayout()->setTitle($title);
        $this->_view->titlesection = $title;
      }
    } else {
      $this->_view->routeform = $this->route . "/insert";
      $title = "Crear Maletas";
      $this->getLayout()->setTitle($title);
      $this->_view->titlesection = $title;
    }
  }

  /**
   * Inserta la informacion de un Maletas  y redirecciona al listado de Maletas.
   *
   * @return void.
   */
  public function insertAction()
  {
    $this->setLayout('blanco');
    $csrf = $this->_getSanitizedParam("csrf");
    if (Session::getInstance()->get('csrf')[$this->_getSanitizedParam("csrf_section")] == $csrf) {
      $data = $this->getData();
      $id = $this->mainModel->insert($data);

      $data['maleta_id'] = $id;
      $data['log_log'] = print_r($data, true);
      $data['log_tipo'] = 'CREAR MALETAS';
      $logModel = new Administracion_Model_DbTable_Log();
      $logModel->insert($data);
    }
    header('Location: ' . $this->route . '' . '');
  }

  /**
   * Recibe un identificador  y Actualiza la informacion de un Maletas  y redirecciona al listado de Maletas.
   *
   * @return void.
   */
  public function updateAction()
  {
    $this->setLayout('blanco');
    $csrf = $this->_getSanitizedParam("csrf");
    if (Session::getInstance()->get('csrf')[$this->_getSanitizedParam("csrf_section")] == $csrf) {
      $id = $this->_getSanitizedParam("id");
      $content = $this->mainModel->getById($id);
      if ($content->maleta_id) {
        $data = $this->getData();
        $this->mainModel->update($data, $id);
      }
      $data['maleta_id'] = $id;
      $data['log_log'] = print_r($data, true);
      $data['log_tipo'] = 'EDITAR MALETAS';
      $logModel = new Administracion_Model_DbTable_Log();
      $logModel->insert($data);
    }
    header('Location: ' . $this->route . '' . '');
  }

  /**
   * Recibe un identificador  y elimina un Maletas  y redirecciona al listado de Maletas.
   *
   * @return void.
   */
  public function deleteAction()
  {
    $this->setLayout('blanco');
    $csrf = $this->_getSanitizedParam("csrf");
    if (Session::getInstance()->get('csrf')[$this->_csrf_section] == $csrf) {
      $id = $this->_getSanitizedParam("id");
      if (isset($id) && $id > 0) {
        $content = $this->mainModel->getById($id);
        if (isset($content)) {
          $this->mainModel->deleteRegister($id);
          $data = (array) $content;
          $data['log_log'] = print_r($data, true);
          $data['log_tipo'] = 'BORRAR MALETAS';
          $logModel = new Administracion_Model_DbTable_Log();
          $logModel->insert($data);
        }
      }
    }
    header('Location: ' . $this->route . '' . '');
  }

  /**
   * Recibe la informacion del formulario y la retorna en forma de array para la edicion y creacion de Maletas.
   *
   * @return array con toda la informacion recibida del formulario.
   */
  private function getData()
  {
    $data = array();
    $data['maleta_codigo'] = $this->_getSanitizedParam("maleta_codigo");
    $data['maleta_aerolinea'] = $this->_getSanitizedParam("maleta_aerolinea");
    $data['maleta_prop'] = $this->_getSanitizedParam("maleta_prop");
    $data['maleta_fl'] = $this->_getSanitizedParam("maleta_fl");
    $data['maleta_estado'] = $this->_getSanitizedParam("maleta_estado");
    $data['maleta_fingreso'] = $this->_getSanitizedParam("maleta_fingreso");
    $data['maleta_fsalida'] = $this->_getSanitizedParam("maleta_fsalida");
    $data['maleta_hingreso'] = $this->_getSanitizedParam("maleta_hingreso");
    $data['maleta_hsalida'] = $this->_getSanitizedParam("maleta_hsalida");
    $data['maleta_over_tipo'] = $this->_getSanitizedParam("maleta_over_tipo");
    return $data;
  }

  /**
   * Genera los valores del campo Aerolinea.
   *
   * @return array cadena con los valores del campo Aerolinea.
   */
  private function getMaletaaerolinea()
  {
    $modelData = new Administracion_Model_DbTable_Dependaerolineas();
    $data = $modelData->getList();
    $array = array();
    foreach ($data as $key => $value) {
      $array[$value->aerolinea_codigo] = $value->aerolinea_nombre;
    }
    return $array;
  }


  /**
   * Genera los valores del campo Estado.
   *
   * @return array cadena con los valores del campo Estado.
   */
  private function getMaletaestado()
  {
    $array = array();
    $array[''] = 'Sin reclamar';
    $array['1'] = 'Reclamada';
    return $array;
  }

  /**
   * Genera la consulta con los filtros de este controlador.
   *
   * @return array cadena con los filtros que se van a asignar a la base de datos
   */
  protected function getFilter()
  {
    $filtros = " 1 = 1 ";
    if (Session::getInstance()->get($this->namefilter) != "") {
      $filters = (object) Session::getInstance()->get($this->namefilter);
      if ($filters->maleta_codigo != '') {
        $filtros = $filtros . " AND maleta_codigo = '" . $filters->maleta_codigo . "'";
      }
      if ($filters->maleta_aerolinea != '') {
        $filtros = $filtros . " AND maleta_aerolinea = '" . $filters->maleta_aerolinea . "'";
      }
      if ($filters->maleta_estado != '') {
        if ($filters->maleta_estado == '2') {
          $filtros = $filtros . " AND maleta_estado =''";
        } else if ($filters->maleta_estado == '1') {
          $filtros = $filtros . " AND maleta_estado ='1'";
        }
      }
      if ($filters->maleta_fingreso != '') {
        $filtros = $filtros . " AND maleta_fingreso LIKE '%" . $filters->maleta_fingreso . "%'";
      }
      if ($filters->maleta_fsalida != '') {
        $filtros = $filtros . " AND maleta_fsalida LIKE '%" . $filters->maleta_fsalida . "%'";
      }
      if ($filters->maleta_hingreso != '') {
        $filtros = $filtros . " AND maleta_hingreso LIKE '" . $filters->maleta_hingreso . "%'";
      }
      if ($filters->maleta_hsalida != '') {
        $filtros = $filtros . " AND maleta_hsalida LIKE '" . $filters->maleta_hsalida . "%'";
      }
    }
    if (Session::getInstance()->get('kt_login_level') == '4') {
      $filtros = $filtros . " AND maleta_aerolinea = '" . Session::getInstance()->get('kt_login_aerolinea') . "'";
    }
    return $filtros;
  }

  /**
   * Recibe y asigna los filtros de este controlador
   *
   * @return void
   */
  protected function filters()
  {
    if ($this->getRequest()->isPost() == true) {
      Session::getInstance()->set($this->namepageactual, 1);
      $parramsfilter = array();
      $parramsfilter['maleta_codigo'] = $this->_getSanitizedParam("maleta_codigo");
      $parramsfilter['maleta_aerolinea'] = $this->_getSanitizedParam("maleta_aerolinea");
      $parramsfilter['maleta_estado'] = $this->_getSanitizedParam("maleta_estado");
      $parramsfilter['maleta_fingreso'] = $this->_getSanitizedParam("maleta_fingreso");
      $parramsfilter['maleta_fsalida'] = $this->_getSanitizedParam("maleta_fsalida");
      $parramsfilter['maleta_hingreso'] = $this->_getSanitizedParam("maleta_hingreso");
      $parramsfilter['maleta_hsalida'] = $this->_getSanitizedParam("maleta_hsalida");
      Session::getInstance()->set($this->namefilter, $parramsfilter);
    }
    if ($this->_getSanitizedParam("cleanfilter") == 1) {
      Session::getInstance()->set($this->namefilter, '');
      Session::getInstance()->set($this->namepageactual, 1);
    }
  }

  public function registrarAction()
  {
    if (
      Session::getInstance()->get('kt_login_level') == '1' || Session::getInstance()->get('kt_login_level') == '2' || Session::getInstance()->get('kt_login_level') == '3'
    ) {
    } else {
      header("Location: /administracion/maletas/");
    }
    $aerolineasModel = new Administracion_Model_DbTable_Aerolineas();
    $this->_view->list_maleta_aerolinea = $this->getMaletaaerolinea();
  }

  public function reclamarAction()
  {
    if (
      Session::getInstance()->get('kt_login_level') == '1' || Session::getInstance()->get('kt_login_level') == '2' || Session::getInstance()->get('kt_login_level') == '3'
    ) {
    } else {
      header("Location: /administracion/maletas/");
    }
  }
  public function storeAction()
  {
    $data = array();
    $maletasModel = new Administracion_Model_DbTable_Maletas();
    $code = $this->_getSanitizedParam("maleta_codigo");
    $maleta = $maletasModel->getList("maleta_codigo = '$code'", "")[0];

    $data['maleta_codigo'] = $this->_getSanitizedParam("maleta_codigo");
    $data['maleta_aerolinea'] = $this->_getSanitizedParam("maleta_aerolinea");
    $data["maleta_fingreso"] = date("Y-m-d");
    $data["maleta_hingreso"] = date("H:i:s");
    $data["maleta_conexion"] = $this->_getSanitizedParam("connection_select") != '' ? $this->_getSanitizedParam("connection_select") : '0';
    $data["maleta_aerolinea_conexion"] = $this->_getSanitizedParam("connection") != '' ? $this->_getSanitizedParam("connection") : '0';

    if (!$maleta) {
      $id = $maletasModel->insert($data);
      if ($id) {
        $aerolineasModel = new Administracion_Model_DbTable_Aerolineas();
        $aerolinea = $aerolineasModel->getList("aerolinea_codigo = '" . $data['maleta_aerolinea'] . "'", "")[0];
        if ($this->_getSanitizedParam('connection_select') == '1') {
          $aerolinea = $aerolineasModel->getList("aerolinea_codigo = '" . $this->_getSanitizedParam('connection') . "'", "")[0];
        }
        $mailModel = new Core_Model_Sendingemail($this->_view);
        $mail = $mailModel->notificarRegistro($aerolinea, $data['maleta_codigo']);
        $telefonos = explode(",", $aerolinea->aerolinea_telefono);
        foreach ($telefonos as $key => $value) {
          $this->notificarDorabot($value, $aerolinea->aerolinea_nombre, $data['maleta_codigo']);
        }
      }
    }
    header("Location: /administracion/maletas/");
  }
  public function getAirlineAction()
  {
    $res = array();
    $code = $this->_getSanitizedParam("code");
    $full_code = $this->_getSanitizedParam("full_code");
    $modelData = new Administracion_Model_DbTable_Aerolineas();
    $maletasModel = new Administracion_Model_DbTable_Maletas();
    $data = $modelData->getList("aerolinea_estado = '1' AND aerolinea_codigo = '$code'", "")[0];
    if ($data) {
      $res['status'] = 'success';
      $res['airline_name'] = $data->aerolinea_nombre;
      $res['airline_code'] = $data->aerolinea_codigo;
      $maleta = $maletasModel->getList("maleta_codigo = '" . $full_code . "'", "");
      if (is_countable($maleta) && count($maleta) > 0) {
        $res['status'] = 'ext';
      }
    } else {
      $res['status'] = 'error';
    }
    die(json_encode($res));
  }
  public function reclamoUpdateAction()
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
      if ($maleta->maleta_conexion == '1') {
        $aerolinea = $aerolineasModel->getList("aerolinea_codigo = '" . $maleta->maleta_aerolinea_conexion . "'", "")[0];
      }
      $mail = $mailModel->notificarReclamo($aerolinea, $code);
      $telefonos = explode(",", $aerolinea->aerolinea_telefono);
      foreach ($telefonos as $key => $value) {
        $this->notificarReclamoDorabot($value, $aerolinea->aerolinea_nombre, $code);
      }
      $data['log_tipo'] = 'RECLAMO';
      $data['log_maleta'] = $maleta->maleta_id . '' . $code;
      $data['log_fecha'] = date("Y-m-d H:i:s");
      $data['log_usuario'] = Session::getInstance()->get('user')->user_id;
      $logModel = new Administracion_Model_DbTable_Logmaletas();
      $logModel->insert($data);
    }
    header("Location: /administracion/maletas/reclamar/");
  }
  private function notificarDorabot($phone, $aerolinea, $codigoEquipaje)
  {
    if (APPLICATION_ENV != 'production') {
      $phone = '573142054891';
    }
    $url = 'https://api.botmaker.com/v2.0/chats-actions/trigger-intent';
    $accessToken = 'eyJhbGciOiJIUzUxMiJ9.eyJidXNpbmVzc0lkIjoiZG9yYWJvdCIsIm5hbWUiOiJBbGVqYW5kcmEgR2lyYWxkbyIsImFwaSI6dHJ1ZSwiaWQiOiJFNllSTzIxN3JhZERGQWZXVTRwc3pPeWRiVDUyIiwiZXhwIjoxODY1NjI0MjYzLCJqdGkiOiJFNllSTzIxN3JhZERGQWZXVTRwc3pPeWRiVDUyIn0.hHfUFmldSMQzuKYErjM6qjad0M22wzQtHRizq924KC2IuJxla6b62Gc2gH2ta57w4lE-SsoYyoSrDVZU9ixsWA';
    $data = [
      "chat" => [
        "channelId" => "dorabot-whatsapp-573228220300",
        "contactId" => "57" . trim($phone)
      ],
      "intentIdOrName" => "notificaciones_dorabot_nivel_5v2",
      "variables" => [
        "aerolinea" => $aerolinea,
        "codigoEquipaje" => $codigoEquipaje
      ],
      "tags" => [
        "tagName" => true,
        "anotherTag" => false
      ],
      "webhookPayload" => "string"
    ];

    $payload = json_encode($data);

    $ch = curl_init($url);

    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
      'Content-Type: application/json',
      'access-token: ' . $accessToken
    ]);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);

    $response = curl_exec($ch);

    if (curl_errno($ch)) {
      echo 'Error:' . curl_error($ch);
      $logModel = new Administracion_Model_DbTable_Logdorabot();
      $logData = array(
        "date" => date('Y-m-d'),
        "log" => curl_error($ch),
        "code" => $codigoEquipaje
      );
    } else {
      echo 'Response:' . $response;
    }

    curl_close($ch);
  }
  private function notificarReclamoDorabot($phone, $aerolinea, $codigoEquipaje)
  {

    $url = 'https://api.botmaker.com/v2.0/chats-actions/trigger-intent';
    $accessToken = 'eyJhbGciOiJIUzUxMiJ9.eyJidXNpbmVzc0lkIjoiZG9yYWJvdCIsIm5hbWUiOiJBbGVqYW5kcmEgR2lyYWxkbyIsImFwaSI6dHJ1ZSwiaWQiOiJFNllSTzIxN3JhZERGQWZXVTRwc3pPeWRiVDUyIiwiZXhwIjoxODY1NjI0MjYzLCJqdGkiOiJFNllSTzIxN3JhZERGQWZXVTRwc3pPeWRiVDUyIn0.hHfUFmldSMQzuKYErjM6qjad0M22wzQtHRizq924KC2IuJxla6b62Gc2gH2ta57w4lE-SsoYyoSrDVZU9ixsWA';
    $data = [
      "chat" => [
        "channelId" => "dorabot-whatsapp-573228220300",
        "contactId" => "57" . trim($phone)
      ],
      "intentIdOrName" => "notificaciones_dorabot_nivel5_3v3",
      "variables" => [
        "aerolinea" => $aerolinea,
        "codigoEquipaje" => $codigoEquipaje
      ],
      "tags" => [
        "tagName" => true,
        "anotherTag" => false
      ],
      "webhookPayload" => "string"
    ];

    $payload = json_encode($data);

    $ch = curl_init($url);

    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
      'Content-Type: application/json',
      'access-token: ' . $accessToken
    ]);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);

    $response = curl_exec($ch);

    if (curl_errno($ch)) {
      echo 'Error:' . curl_error($ch);
      $logModel = new Administracion_Model_DbTable_Logdorabot();
      $logData = array(
        "date" => date('Y-m-d'),
        "log" => curl_error($ch),
        "code" => $codigoEquipaje
      );
    } else {
      echo 'Response:' . $response;
    }

    curl_close($ch);
  }
  public function testing1Action()
  {
    echo "hola";
    //enviar msj dorabot
    $this->notificarDorabot('3124624763', 'AerolineaTest', 'test123');
  }
  public function testing2Action()
  {
    echo "hola";
    //enviar msj dorabot
    $this->notificarReclamoDorabot('3124624763', 'AerolineaTest', 'test123');
  }
}
