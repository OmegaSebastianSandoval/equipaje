<?php

/**
 * Controlador de Publicidad que permite la  creacion, edicion  y eliminacion de los Administrar Banners del Sistema
 */
class Administracion_publicidadController extends Administracion_mainController
{
  public $botonpanel = 2;
  /**
   * $mainModel  instancia del modelo de  base de datos Administrar Banners
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
  protected $_csrf_section = "administracion_publicidad";

  /**
   * $namepages nombre de la pvariable en la cual se va a guardar  el numero de seccion en la paginacion del controlador
   * @var string
   */
  protected $namepages;



  /**
   * Inicializa las variables principales del controlador publicidad .
   *
   * @return void.
   */
  public function init()
  {
    $this->mainModel = new Administracion_Model_DbTable_Publicidad();
    $this->namefilter = "parametersfilterpublicidad";
    $this->route = "/administracion/publicidad";
    $this->namepages = "pages_publicidad";
    $this->namepageactual = "page_actual_publicidad";
    $this->_view->route = $this->route;
    if (Session::getInstance()->get($this->namepages)) {
      $this->pages = Session::getInstance()->get($this->namepages);
    } else {
      $this->pages = 20;
    }
    parent::init();
  }


  /**
   * Recibe la informacion y  muestra un listado de  Administrar Banners con sus respectivos filtros.
   *
   * @return void.
   */
  public function indexAction()
  {
    $title = "Administración de Publicidad";
    $this->getLayout()->setTitle($title);
    $this->_view->titlesection = $title;
    $this->filters();
    $this->_view->csrf = Session::getInstance()->get('csrf')[$this->_csrf_section];
    $filters = (object)Session::getInstance()->get($this->namefilter);
    $this->_view->filters = $filters;
    $filters = $this->getFilter();
    $order = "orden ASC";
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
    $this->_view->list_publicidad_seccion = $this->getPublicidadseccion();
    $this->_view->list_publicidad_estado = $this->getPublicidadestado();
  }

  /**
   * Genera la Informacion necesaria para editar o crear un  Banner  y muestra su formulario
   *
   * @return void.
   */
  public function manageAction()
  {
    $this->_view->route = $this->route;
    $this->_csrf_section = "manage_publicidad_" . date("YmdHis");
    $this->_csrf->generateCode($this->_csrf_section);
    $this->_view->csrf_section = $this->_csrf_section;
    $this->_view->csrf = Session::getInstance()->get('csrf')[$this->_csrf_section];
    $this->_view->list_publicidad_seccion = $this->getPublicidadseccion();
    $this->_view->list_publicidad_posicion = $this->getPublicidadposicion();
    $this->_view->list_publicidad_estado = $this->getPublicidadestado();
    $this->_view->list_publicidad_enlace_alineacion = $this->getPublicidadalineacion();
    $this->_view->list_publicidad_tipo_enlace = $this->getPublicidadtipoenlace();
    $id = $this->_getSanitizedParam("id");
    if ($id > 0) {
      $content = $this->mainModel->getById($id);
      if ($content->publicidad_id) {
        $this->_view->content = $content;
        $this->_view->routeform = $this->route . "/update";
        $title = "Actualizar Publicidad";
        $this->getLayout()->setTitle($title);
        $this->_view->titlesection = $title;
      } else {
        $this->_view->routeform = $this->route . "/insert";
        $title = "Crear Publicidad";
        $this->getLayout()->setTitle($title);
        $this->_view->titlesection = $title;
      }
    } else {
      $this->_view->routeform = $this->route . "/insert";
      $title = "Crear Publicidad";
      $this->getLayout()->setTitle($title);
      $this->_view->titlesection = $title;
    }
  }

  /**
   * Inserta la informacion de un Banner  y redirecciona al listado de Administrar Banners.
   *
   * @return void.
   */
  public function insertAction()
  {
    $this->setLayout('blanco');
    $csrf = $this->_getSanitizedParam("csrf");
    if (Session::getInstance()->get('csrf')[$this->_getSanitizedParam("csrf_section")] == $csrf) {
      $data = $this->getData();
      $uploadImage =  new Core_Model_Upload_Image();
      if ($_FILES['publicidad_imagen']['name'] != '') {
        $data['publicidad_imagen'] = $uploadImage->upload("publicidad_imagen");
      }
      if ($_FILES['publicidad_imagenresponsive']['name'] != '') {
        $data['publicidad_imagenresponsive'] = $uploadImage->upload("publicidad_imagenresponsive");
      }
      $id = $this->mainModel->insert($data);
      $this->mainModel->changeOrder($id, $id);
      $data['publicidad_id'] = $id;
      $data['log_log'] = print_r($data, true);
      $data['log_tipo'] = 'CREAR BANNER';
      $logModel = new Administracion_Model_DbTable_Log();
      $logModel->insert($data);
    }
    header('Location: ' . $this->route . '' . '');
  }

  /**
   * Recibe un identificador  y Actualiza la informacion de un Banner  y redirecciona al listado de Administrar Banners.
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
      if ($content->publicidad_id) {
        $data = $this->getData();
        $uploadImage =  new Core_Model_Upload_Image();
        if ($_FILES['publicidad_imagen']['name'] != '') {
          if ($content->publicidad_imagen) {
            $uploadImage->delete($content->publicidad_imagen);
          }
          $data['publicidad_imagen'] = $uploadImage->upload("publicidad_imagen");
        } else {
          $data['publicidad_imagen'] = $content->publicidad_imagen;
        }

        if ($_FILES['publicidad_imagenresponsive']['name'] != '') {
          if ($content->publicidad_imagenresponsive) {
            $uploadImage->delete($content->publicidad_imagenresponsive);
          }
          $data['publicidad_imagenresponsive'] = $uploadImage->upload("publicidad_imagenresponsive");
        } else {
          $data['publicidad_imagenresponsive'] = $content->publicidad_imagenresponsive;
        }
        $this->mainModel->update($data, $id);
      }
      $data['publicidad_id'] = $id;
      $data['log_log'] = print_r($data, true);
      $data['log_tipo'] = 'EDITAR BANNER';
      $logModel = new Administracion_Model_DbTable_Log();
      $logModel->insert($data);
    }
    header('Location: ' . $this->route . '' . '');
  }

  /**
   * Recibe un identificador  y elimina un Banner  y redirecciona al listado de Administrar Banners.
   *
   * @return void.
   */
  public function deleteAction()
  {
    $this->setLayout('blanco');
    $csrf = $this->_getSanitizedParam("csrf");
    if (Session::getInstance()->get('csrf')[$this->_csrf_section] == $csrf) {
      $id =  $this->_getSanitizedParam("id");
      if (isset($id) && $id > 0) {
        $content = $this->mainModel->getById($id);
        if (isset($content)) {
          $uploadImage =  new Core_Model_Upload_Image();
          if (isset($content->publicidad_imagen) && $content->publicidad_imagen != '') {
            $uploadImage->delete($content->publicidad_imagen);
          }
          if (isset($content->publicidad_imagenresponsive) && $content->publicidad_imagenresponsive   != '') {
            $uploadImage->delete($content->publicidad_imagenresponsive);
          }
          $this->mainModel->deleteRegister($id);
          $data = (array)$content;
          $data['log_log'] = print_r($data, true);
          $data['log_tipo'] = 'BORRAR BANNER';
          $logModel = new Administracion_Model_DbTable_Log();
          $logModel->insert($data);
        }
      }
    }
    header('Location: ' . $this->route . '' . '');
  }

  /**
   * Recibe la informacion del formulario y la retorna en forma de array para la edicion y creacion de Publicidad.
   *
   * @return array con toda la informacion recibida del formulario.
   */
  private function getData()
  {
    $data = array();
    if ($this->_getSanitizedParam("publicidad_seccion") == '') {
      $data['publicidad_seccion'] = '0';
    } else {
      $data['publicidad_seccion'] = $this->_getSanitizedParam("publicidad_seccion");
    }
    $data['publicidad_nombre'] = $this->_getSanitizedParam("publicidad_nombre");
    $data['publicidad_nombre2'] = $this->_getSanitizedParam("publicidad_nombre2");
    $data['publicidad_fecha'] = $this->_getSanitizedParam("publicidad_fecha");
    $data['publicidad_nombre_ver'] = $this->_getSanitizedParam("publicidad_nombre_ver");
    $data['publicidad_imagen'] = "";
    $data['publicidad_imagenresponsive'] = "";
    $data['publicidad_video'] = $this->_getSanitizedParam("publicidad_video");
    $data['publicidad_color_fondo'] = $this->_getSanitizedParam("publicidad_color_fondo");
    $data['publicidad_posicion'] = $this->_getSanitizedParam("publicidad_posicion");
    $data['publicidad_descripcion'] = $this->_getSanitizedParamHtml("publicidad_descripcion");
    if ($this->_getSanitizedParam("publicidad_estado") == '') {
      $data['publicidad_estado'] = '0';
    } else {
      $data['publicidad_estado'] = $this->_getSanitizedParam("publicidad_estado");
    }
    if ($this->_getSanitizedParam("publicidad_click") == '') {
      $data['publicidad_click'] = '0';
    } else {
      $data['publicidad_click'] = $this->_getSanitizedParam("publicidad_click");
    }
    $data['publicidad_enlace'] = $this->_getSanitizedParam("publicidad_enlace");
    if ($this->_getSanitizedParam("publicidad_tipo_enlace") == '') {
      $data['publicidad_tipo_enlace'] = '0';
    } else {
      $data['publicidad_tipo_enlace'] = $this->_getSanitizedParam("publicidad_tipo_enlace");
    }
    $data['publicidad_texto_enlace'] = $this->_getSanitizedParam("publicidad_texto_enlace");
    $data['publicidad_enlace_alineacion'] = $this->_getSanitizedParam("publicidad_enlace_alineacion");
    return $data;
  }

  /**
   * Genera los valores del campo Seccion.
   *
   * @return array cadena con los valores del campo Seccion.
   */
  private function getPublicidadseccion()
  {
    $array = array();

    // GENERALES
    $array['100'] = 'Botones Flotantes';
    $array['101'] = 'PopUp';

    // HOME
    $array['1'] = 'Banners Home';

    return $array;
  }


  /**
   * Genera los valores del campo Posicion.
   *
   * @return array cadena con los valores del campo Posicion.
   */
  private function getPublicidadposicion()
  {
    $array = array();
    $array['align-items-center'] = 'Centro';
    $array['align-items-start'] = 'Superior';
    $array['align-items-end'] = 'Inferior';
    return $array;
  }


  /**
   * Genera los valores del campo Estado.
   *
   * @return array cadena con los valores del campo Estado.
   */
  private function getPublicidadestado()
  {
    $array = array();
    $array['1'] = 'Activo';
    $array['2'] = 'Inactivo';
    return $array;
  }

  private function getPublicidadalineacion()
  {
    $array = array();
    $array['justify-content-start'] = 'Izquierda';
    $array['justify-content-center'] = 'Centro';
    $array['justify-content-end'] = 'Derecha';
    return $array;
  }

  /**
   * Genera los valores del campo Tipo de enlace.
   *
   * @return array cadena con los valores del campo Tipo de enlace.
   */
  private function getPublicidadtipoenlace()
  {
    $array = array();
    $array['1'] = 'Nueva Ventana';
    $array['2'] = 'Ventana Actual';
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
      $filters = (object)Session::getInstance()->get($this->namefilter);
      if ($filters->publicidad_seccion != '') {
        $filtros = $filtros . " AND publicidad_seccion ='" . $filters->publicidad_seccion . "'";
      }
      if ($filters->publicidad_nombre != '') {
        $filtros = $filtros . " AND publicidad_nombre LIKE '%" . $filters->publicidad_nombre . "%'";
      }
      if ($filters->publicidad_fecha != '') {
        $filtros = $filtros . " AND publicidad_fecha LIKE '%" . $filters->publicidad_fecha . "%'";
      }
      if ($filters->publicidad_imagen != '') {
        $filtros = $filtros . " AND publicidad_imagen LIKE '%" . $filters->publicidad_imagen . "%'";
      }
      if ($filters->publicidad_imagen != '') {
        $filtros = $filtros . " AND publicidad_imagenresponsive LIKE '%" . $filters->publicidad_imagenresponsive . "%'";
      }
      if ($filters->publicidad_video != '') {
        $filtros = $filtros . " AND publicidad_video LIKE '%" . $filters->publicidad_video . "%'";
      }
      if ($filters->publicidad_estado != '') {
        $filtros = $filtros . " AND publicidad_estado ='" . $filters->publicidad_estado . "'";
      }
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
      $parramsfilter['publicidad_seccion'] =  $this->_getSanitizedParam("publicidad_seccion");
      $parramsfilter['publicidad_nombre'] =  $this->_getSanitizedParam("publicidad_nombre");
      $parramsfilter['publicidad_fecha'] =  $this->_getSanitizedParam("publicidad_fecha");
      $parramsfilter['publicidad_imagen'] =  $this->_getSanitizedParam("publicidad_imagen");
      $parramsfilter['publicidad_imagenresponsive'] =  $this->_getSanitizedParam("publicidad_imagenresponsive");
      $parramsfilter['publicidad_video'] =  $this->_getSanitizedParam("publicidad_video");
      $parramsfilter['publicidad_estado'] =  $this->_getSanitizedParam("publicidad_estado");
      Session::getInstance()->set($this->namefilter, $parramsfilter);
    }
    if ($this->_getSanitizedParam("cleanfilter") == 1) {
      Session::getInstance()->set($this->namefilter, '');
      Session::getInstance()->set($this->namepageactual, 1);
    }
  }
}
