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

  protected $namepageactual;


  /**
   * Inicializa las variables principales del controlador maletas .
   *
   * @return void.
   */
  public function init()
  {
    // Tiempo y memoria para todo el controlador: el registro y el reclamo envian correo y WhatsApp
    // sincronos (Dorabot tarda hasta 15s por telefono) y el listado/exportacion puede manejar muchas maletas.
    ini_set('max_execution_time', '300');
    set_time_limit(300);
    $memoria = ini_get('memory_limit');
    if ($memoria != '-1' && (int) $memoria < 256 && stripos($memoria, 'G') === false) {
      ini_set('memory_limit', '256M');
    }
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
    // Salida actualizada y urgencia: los vuelos de la pagina se traen en una sola consulta
    $vuelos = (new Core_Model_Vuelos())->porIds(array_map(function ($m) {
      return $m->maleta_vuelo_id;
    }, $this->_view->lists));
    $info = (new Administracion_Model_DbTable_Informacion())->getById(1);
    $salidas = array();
    foreach ($this->_view->lists as $maleta) {
      $salidas[$maleta->maleta_id] = Core_Model_Vuelos::estadoSalida($maleta, $vuelos[$maleta->maleta_vuelo_id] ?? null, (int) $info->info_pagina_alerta_minutos);
    }
    $this->_view->salidas = $salidas;
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
      if ($filters->maleta_vuelo_numero != '') {
        $filtros = $filtros . " AND maleta_vuelo_numero LIKE '%" . $filters->maleta_vuelo_numero . "%'";
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
      $parramsfilter['maleta_vuelo_numero'] = $this->_getSanitizedParam("maleta_vuelo_numero");
      Session::getInstance()->set($this->namefilter, $parramsfilter);
    }
    if ($this->_getSanitizedParam("cleanfilter") == 1) {
      Session::getInstance()->set($this->namefilter, '');
      Session::getInstance()->set($this->namepageactual, 1);
    }
  }

  public function registrarAction()
  {
    if (!$this->puedeOperar()) {
      header("Location: /administracion/maletas/");
      exit;
    }
    $this->_view->list_maleta_aerolinea = $this->getMaletaaerolinea();
    $this->_view->aerolineas_iata = $this->getAerolineasIata();
    $this->_view->csrf_lote = $this->csrfLote();
    $this->_view->tipo_vuelo = Core_Model_Vuelos::tipoPorSesion();
  }

  public function reclamarAction()
  {
    if (!$this->puedeOperar()) {
      header("Location: /administracion/maletas/");
      exit;
    }
    $this->_view->csrf_lote = $this->csrfLote();
  }

  /**
   * Registro desde el formulario anterior (una maleta, sin vuelo). Se conserva para no romper nada.
   */
  public function storeAction()
  {
    $this->registrarMaleta([
      'maleta_codigo' => $this->_getSanitizedParam("maleta_codigo"),
      'maleta_aerolinea' => $this->_getSanitizedParam("maleta_aerolinea"),
      'connection_select' => $this->_getSanitizedParam("connection_select"),
      'connection' => $this->_getSanitizedParam("connection"),
    ], false);
    header("Location: /administracion/maletas/");
  }

  /**
   * Registro de una maleta del lote. El frontend envia las maletas una por una porque
   * cada registro tarda varios segundos (correo y WhatsApp sincronos).
   */
  public function storeAjaxAction()
  {
    $this->setLayout('blanco');
    header('Content-Type: application/json');
    if (!$this->puedeOperar() || !$this->csrfLoteValido()) {
      die(json_encode(['status' => 'error', 'mensaje' => 'Sesión vencida, recargue la página']));
    }
    $this->prepararProcesoLote();
    $res = $this->registrarMaleta([
      'maleta_codigo' => $this->_getSanitizedParam("maleta_codigo"),
      'maleta_aerolinea' => $this->_getSanitizedParam("maleta_aerolinea"),
      'connection_select' => $this->_getSanitizedParam("connection_select"),
      'connection' => $this->_getSanitizedParam("connection"),
      'vuelo_id' => $this->_getSanitizedParam("vuelo_id"),
      'vuelo_manual' => $this->_getSanitizedParam("vuelo_manual"),
    ], true);
    die(json_encode($res));
  }

  /**
   * Registra una maleta y envia las notificaciones (correo y Dorabot) igual que antes.
   * @param array $input maleta_codigo, maleta_aerolinea, connection_select, connection, vuelo_id, vuelo_manual
   * @param bool $exigirVuelo si es obligatorio asociar un vuelo (registro por lotes)
   * @return array ['status' => 'ok'|'error', 'mensaje' => string, 'maleta_id' => int]
   */
  private function registrarMaleta(array $input, $exigirVuelo)
  {
    $maletasModel = new Administracion_Model_DbTable_Maletas();
    $aerolineasModel = new Administracion_Model_DbTable_Aerolineas();
    $code = $input['maleta_codigo'];

    if ($exigirVuelo) {
      if (!preg_match('/^\d{10}$/', $code)) {
        return ['status' => 'error', 'mensaje' => 'El código debe tener 10 dígitos'];
      }
      $aerolineaEtiqueta = $aerolineasModel->getList("aerolinea_estado = '1' AND aerolinea_codigo = '" . $input['maleta_aerolinea'] . "'", "")[0] ?? null;
      if (!$aerolineaEtiqueta || substr($code, 1, 3) != $input['maleta_aerolinea']) {
        return ['status' => 'error', 'mensaje' => 'No se encontró la aerolínea de la etiqueta'];
      }
    }
    $maleta = $maletasModel->getList("maleta_codigo = '$code'", "")[0] ?? null;
    if ($maleta) {
      return ['status' => 'error', 'mensaje' => 'La maleta ya está registrada'];
    }

    $data = array();
    $data['maleta_codigo'] = $code;
    $data['maleta_aerolinea'] = $input['maleta_aerolinea'];
    $data["maleta_fingreso"] = date("Y-m-d");
    $data["maleta_hingreso"] = date("H:i:s");
    $data["maleta_conexion"] = $input['connection_select'] != '' ? $input['connection_select'] : '0';
    $data["maleta_aerolinea_conexion"] = $input['connection'] != '' ? $input['connection'] : '0';

    $aerolinea = $aerolineasModel->getList("aerolinea_codigo = '" . $data['maleta_aerolinea'] . "'", "")[0] ?? null;
    if ($input['connection_select'] == '1') {
      $aerolinea = $aerolineasModel->getList("aerolinea_codigo = '" . $input['connection'] . "'", "")[0] ?? null;
      if ($exigirVuelo && !$aerolinea) {
        return ['status' => 'error', 'mensaje' => 'Seleccione la aerolínea de conexión'];
      }
    }

    if ($exigirVuelo) {
      $vueloId = (int) ($input['vuelo_id'] ?? 0);
      $vueloManual = strtoupper(trim(preg_replace('/[^A-Za-z0-9 ]/', '', $input['vuelo_manual'] ?? '')));
      if ($vueloId > 0) {
        $vuelo = (new Core_Model_Vuelos())->validar($vueloId, $aerolinea->aerolinea_iata ?? '');
        if (!$vuelo) {
          return ['status' => 'error', 'mensaje' => 'El vuelo no es válido para esta aerolínea o ya salió'];
        }
        $salida = Core_Model_Vuelos::salida($vuelo);
        $data['maleta_vuelo_id'] = $vuelo->vuelo_id;
        $data['maleta_vuelo_numero'] = Core_Model_Vuelos::numero($vuelo);
        $data['maleta_vuelo_salida'] = $salida;
        $data['maleta_vuelo_destino'] = $vuelo->vuelo_destino;
        $data['maleta_vuelo_manual'] = 0;
        $data['maleta_minutos_disponibles'] = (int) floor((strtotime($salida) - strtotime($data['maleta_fingreso'] . ' ' . $data['maleta_hingreso'])) / 60);
      } elseif ($vueloManual != '') {
        $data['maleta_vuelo_numero'] = substr($vueloManual, 0, 15);
        $data['maleta_vuelo_manual'] = 1;
      } else {
        return ['status' => 'error', 'mensaje' => 'Seleccione el vuelo'];
      }
    }

    $id = $maletasModel->insert($data);
    if (!$id) {
      return ['status' => 'error', 'mensaje' => 'No se pudo guardar la maleta'];
    }
    if ($aerolinea) {
      $dataMaletaActualizada = $maletasModel->getById($id);
      $mailModel = new Core_Model_Sendingemail($this->_view);
      $mail = $mailModel->notificarRegistro($aerolinea, $data['maleta_codigo'], $dataMaletaActualizada->maleta_over_tipo, $dataMaletaActualizada->maleta_over_lugar);
      if (!Session::getInstance()->get('over')) {
        $telefonos = explode(",", $aerolinea->aerolinea_telefono);
      } else {
        $telefonos = explode(",", $aerolinea->aerolinea_telefono_over);
      }

      $dorabot = new Core_Model_Dorabot();
      foreach ($telefonos as $telefono) {
        $dorabot->notificar($telefono, 'registro', $aerolinea->aerolinea_nombre, $data['maleta_codigo'], $dataMaletaActualizada->maleta_over_tipo);
      }
    }
    return ['status' => 'ok', 'mensaje' => 'Registrada', 'maleta_id' => $id];
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
      $res['airline_iata'] = $data->aerolinea_iata;
      $maleta = $maletasModel->getList("maleta_codigo = '" . $full_code . "'", "");
      if (is_countable($maleta) && count($maleta) > 0) {
        $res['status'] = 'ext';
      }
    } else {
      $res['status'] = 'error';
    }
    die(json_encode($res));
  }

  /**
   * Vuelos de salida disponibles para la aerolinea efectiva de una maleta.
   * /administracion/maletas/vuelos?aerolinea=XXX&rango=hoy|24h&todos=0|1&q=texto
   */
  public function vuelosAction()
  {
    $this->setLayout('blanco');
    header('Content-Type: application/json');
    if (!$this->puedeOperar()) {
      die(json_encode(['status' => 'error', 'vuelos' => []]));
    }
    $codigo = $this->_getSanitizedParam("aerolinea");
    $aerolinea = (new Administracion_Model_DbTable_Aerolineas())->getList("aerolinea_codigo = '$codigo'", "")[0] ?? null;
    if (!$aerolinea || !$aerolinea->aerolinea_iata) {
      die(json_encode(['status' => 'sin_iata', 'vuelos' => []]));
    }
    $vuelosModel = new Core_Model_Vuelos();
    $vuelosModel->sincronizarSiViejo(3);
    $rango = $this->_getSanitizedParam("rango") == '24h' ? '24h' : 'hoy';
    $tipo = $this->_getSanitizedParam("todos") == '1' ? null : Core_Model_Vuelos::tipoPorSesion();
    $vuelos = $vuelosModel->disponibles($aerolinea->aerolinea_iata, $rango, $tipo, $this->_getSanitizedParam("q"));
    $hoy = date('Y-m-d');
    $hora = function ($fecha) use ($hoy) {
      return date('Y-m-d', strtotime($fecha)) == $hoy ? date('H:i', strtotime($fecha)) : date('d/m H:i', strtotime($fecha));
    };
    $res = [];
    foreach ($vuelos as $vuelo) {
      $schedule = $hora($vuelo->vuelo_schedule);
      $actual = $vuelo->vuelo_actual ? $hora($vuelo->vuelo_actual) : '';
      $res[] = [
        'vuelo_id' => (int) $vuelo->vuelo_id,
        'numero' => Core_Model_Vuelos::numero($vuelo),
        'destino' => $vuelo->vuelo_destino,
        'schedule' => $schedule,
        'actual' => $actual != $schedule ? $actual : '',
        'status_texto' => $vuelo->vuelo_status_texto,
        'puerta' => $vuelo->vuelo_puerta,
        'tipo' => $vuelo->vuelo_tipo,
      ];
    }
    die(json_encode(['status' => 'success', 'vuelos' => $res]));
  }

  /**
   * Datos de una maleta para el reclamo por lotes (aerolinea, vuelo, salida y estado).
   */
  public function getBagAction()
  {
    $this->setLayout('blanco');
    header('Content-Type: application/json');
    if (!$this->puedeOperar()) {
      die(json_encode(['status' => 'error']));
    }
    $code = $this->_getSanitizedParam("code");
    $maleta = (new Administracion_Model_DbTable_Maletas())->getList("maleta_codigo = '$code'", "maleta_fingreso DESC")[0] ?? null;
    if (!$maleta) {
      die(json_encode(['status' => 'error']));
    }
    $aerolineas = $this->getMaletaaerolinea();
    $vuelo = $maleta->maleta_vuelo_id ? (new Core_Model_Vuelos())->porIds([$maleta->maleta_vuelo_id])[$maleta->maleta_vuelo_id] ?? null : null;
    $info = (new Administracion_Model_DbTable_Informacion())->getById(1);
    $salida = Core_Model_Vuelos::estadoSalida($maleta, $vuelo, (int) $info->info_pagina_alerta_minutos);
    $aerolinea = $aerolineas[$maleta->maleta_aerolinea] ?? $maleta->maleta_aerolinea;
    if ($maleta->maleta_conexion == '1' && isset($aerolineas[$maleta->maleta_aerolinea_conexion])) {
      $aerolinea .= ' → ' . $aerolineas[$maleta->maleta_aerolinea_conexion];
    }
    die(json_encode([
      'status' => 'success',
      'maleta_estado' => $maleta->maleta_estado,
      'aerolinea' => $aerolinea,
      'vuelo' => $salida['vuelo'],
      'salida' => $salida['salida'],
      'tiempo_restante' => $salida['tiempo_restante'],
      'urgencia' => $salida['urgencia'],
      'over_tipo' => $maleta->maleta_over_tipo,
      'over_lugar' => $maleta->maleta_over_lugar,
    ]));
  }

  /**
   * Reclamo desde el formulario anterior (una maleta). Se conserva para no romper nada.
   */
  public function reclamoUpdateAction()
  {
    $this->reclamarMaleta($this->_getSanitizedParam("maleta_codigo"));
    header("Location: /administracion/maletas/reclamar/");
  }

  /**
   * Reclamo de una maleta del lote (el frontend envia una por una).
   */
  public function reclamoAjaxAction()
  {
    $this->setLayout('blanco');
    header('Content-Type: application/json');
    if (!$this->puedeOperar() || !$this->csrfLoteValido()) {
      die(json_encode(['status' => 'error', 'mensaje' => 'Sesión vencida, recargue la página']));
    }
    $this->prepararProcesoLote();
    die(json_encode($this->reclamarMaleta($this->_getSanitizedParam("maleta_codigo"))));
  }

  /**
   * Marca una maleta como reclamada y envia las notificaciones igual que antes.
   * @return array ['status' => 'ok'|'error', 'mensaje' => string]
   */
  private function reclamarMaleta($code)
  {
    $data = array();
    $maletasModel = new Administracion_Model_DbTable_Maletas();
    $maleta = $maletasModel->getList("maleta_codigo = '$code'", "")[0] ?? null;
    if (!$maleta) {
      return ['status' => 'error', 'mensaje' => 'No encontrada'];
    }
    if ($maleta->maleta_estado != '') {
      return ['status' => 'error', 'mensaje' => 'Ya reclamada'];
    }
    $maletasModel->editField($maleta->maleta_id, 'maleta_estado', '1');
    $maletasModel->editField($maleta->maleta_id, 'maleta_fsalida', date("Y-m-d"));
    $maletasModel->editField($maleta->maleta_id, 'maleta_hsalida', date("H:i:s"));
    $aerolineasModel = new Administracion_Model_DbTable_Aerolineas();
    $aerolinea = $aerolineasModel->getList("aerolinea_codigo = '" . $maleta->maleta_aerolinea . "'", "")[0];
    $mailModel = new Core_Model_Sendingemail($this->_view);
    if ($maleta->maleta_conexion == '1') {
      $aerolinea = $aerolineasModel->getList("aerolinea_codigo = '" . $maleta->maleta_aerolinea_conexion . "'", "")[0];
    }

    $esMaletaOver = $maleta->maleta_over_tipo ? true : false;
    $overTipo = $maleta->maleta_over_tipo;
    $overLugar = $maleta->maleta_over_lugar;
    $mail = $mailModel->notificarReclamo($aerolinea, $code, $esMaletaOver, $overTipo, $overLugar);
    if (!Session::getInstance()->get('over')) {
      $telefonos = explode(",", $aerolinea->aerolinea_telefono);
    } else {
      $telefonos = explode(",", $aerolinea->aerolinea_telefono_over);
    }
    $dorabot = new Core_Model_Dorabot();
    foreach ($telefonos as $telefono) {
      $dorabot->notificar($telefono, 'reclamo', $aerolinea->aerolinea_nombre, $code, $overTipo);
    }
    $data['log_tipo'] = 'RECLAMO';
    $data['log_maleta'] = $maleta->maleta_id . '' . $code;
    $data['log_fecha'] = date("Y-m-d H:i:s");
    $data['log_usuario'] = Session::getInstance()->get('user')->user_id;
    $logModel = new Administracion_Model_DbTable_Logmaletas();
    $logModel->insert($data);
    return ['status' => 'ok', 'mensaje' => 'Reclamada'];
  }

  /**
   * Solo los niveles 1, 2 y 3 registran y reclaman maletas.
   */
  private function puedeOperar()
  {
    return in_array(Session::getInstance()->get('kt_login_level'), ['1', '2', '3']);
  }

  /**
   * CSRF propio de las pantallas de lote. No se regenera en cada request (como el general del controlador)
   * para que las peticiones AJAX del lote no lo invaliden.
   */
  private function csrfLote()
  {
    $csrf = Session::getInstance()->get('csrf');
    if (!is_array($csrf) || empty($csrf['maletas_lote'])) {
      $this->_csrf->generateCode('maletas_lote');
      $csrf = Session::getInstance()->get('csrf');
    }
    return $csrf['maletas_lote'];
  }

  /**
   * Ajustes para cada peticion del lote (una maleta, con correo y WhatsApp sincronos).
   * El tiempo y la memoria ya se ajustan en init() para todo el controlador.
   * - Si el usuario cierra la pagina, la maleta en curso termina de guardarse y notificarse.
   * - Libera la sesion para no bloquear las demas peticiones del usuario mientras se notifica.
   *   Despues de esto la sesion se puede leer pero no escribir.
   */
  private function prepararProcesoLote()
  {
    set_time_limit(300);
    ignore_user_abort(true);
    session_write_close();
  }

  private function csrfLoteValido()
  {
    $csrf = Session::getInstance()->get('csrf');
    $enviado = (string) $this->getRequest()->_getParam('csrf', '');
    return is_array($csrf) && !empty($csrf['maletas_lote']) && hash_equals($csrf['maletas_lote'], $enviado);
  }

  /**
   * Codigo de 3 digitos => IATA, para que el frontend sepa si la aerolinea de conexion tiene vuelos.
   */
  private function getAerolineasIata()
  {
    $array = array();
    foreach ((new Administracion_Model_DbTable_Aerolineas())->getList() as $value) {
      $array[$value->aerolinea_codigo] = $value->aerolinea_iata;
    }
    return $array;
  }

  public function testing1Action()
  {
    echo "hola";
    //enviar msj dorabot
    (new Core_Model_Dorabot())->notificar('3142054891', 'registro', 'AerolineaTest', 'test123');
  }
  public function testing2Action()
  {
    echo "hola";
    //enviar msj dorabot
    (new Core_Model_Dorabot())->notificar('3142054891', 'reclamo', 'AerolineaTest', 'test123');
  }
  public function testing3Action()
  {
    echo "hola";
    //enviar msj dorabot
    (new Core_Model_Dorabot())->notificar('3142054891', 'registro', 'AerolineaTest', 'test123', '2');
  }
  public function testing4Action()
  {
    echo "hola";
    //enviar msj dorabot
    (new Core_Model_Dorabot())->notificar('3142054891', 'reclamo', 'AerolineaTest', 'test123', '2');
  }

}
