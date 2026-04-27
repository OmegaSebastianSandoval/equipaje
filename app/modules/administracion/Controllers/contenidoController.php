<?php

/**
 * Controlador de Contenido que permite la  creacion, edicion  y eliminacion de los Contenidos del Sistema
 */
class Administracion_contenidoController extends Administracion_mainController
{
  public $botonpanel = 3;
  /**
   * $mainModel  instancia del modelo de  base de datos Contenidos
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
  protected $_csrf_section = "administracion_contenido";

  /**
   * $namepages nombre de la pvariable en la cual se va a guardar  el numero de seccion en la paginacion del controlador
   * @var string
   */
  protected $namepages;



  /**
   * Inicializa las variables principales del controlador contenido .
   *
   * @return void.
   */
  public function init()
  {
    $this->mainModel = new Administracion_Model_DbTable_Contenido();
    $this->namefilter = "parametersfiltercontenido";
    $this->route = "/administracion/contenido";
    $this->namepages = "pages_contenido";
    $this->namepageactual = "page_actual_contenido";
    $this->_view->route = $this->route;
    if (Session::getInstance()->get($this->namepages)) {
      $this->pages = Session::getInstance()->get($this->namepages);
    } else {
      $this->pages = 20;
    }
    parent::init();
  }


  /**
   * Recibe la informacion y  muestra un listado de  Contenidos con sus respectivos filtros.
   *
   * @return void.
   */
  public function indexAction()
  {
    $title = "Administración de Contenidos";
    $this->getLayout()->setTitle($title);
    $this->_view->titlesection = $title;
    $this->filters();
    $this->_view->csrf = Session::getInstance()->get('csrf')[$this->_csrf_section];
    $filters = (object) Session::getInstance()->get($this->namefilter);
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
    $this->_view->list_contenido_seccion = $this->getContenidoseccion();
    $this->_view->padre = $this->_getSanitizedParam("padre");
    $this->_view->list_contenido_tipo = $this->getContenidotipo($this->_getSanitizedParam("padre"));
  }

  /**
   * Genera la Informacion necesaria para editar o crear un  Contenido  y muestra su formulario
   *
   * @return void.
   */
  public function manageAction()
  {
    $this->_view->route = $this->route;
    $this->_csrf_section = "manage_contenido_" . date("YmdHis");
    $this->_csrf->generateCode($this->_csrf_section);
    $this->_view->csrf_section = $this->_csrf_section;
    $this->_view->csrf = Session::getInstance()->get('csrf')[$this->_csrf_section];
    $this->_view->list_contenido_seccion = $this->getContenidoseccion();
    $this->_view->list_contenido_fondo_imagen_tipo = $this->getContenidofondoimagentipo();
    $this->_view->list_contenido_columna_espacios = $this->getContenidocolumnaespacios();
    $this->_view->list_contenido_columna_alineacion = $this->getContenidocolumnaalineacion();
    $this->_view->list_contenido_enlace_abrir = $this->getContenidoenlaceabrir();
    $padre = $this->_getSanitizedParam("padre");
    $id = $this->_getSanitizedParam("id");
    if ($id > 0) {
      $content = $this->mainModel->getById($id);
      if ($content->contenido_id) {
        $this->_view->content = $content;
        $this->_view->routeform = $this->route . "/update";
        $title = "Actualizar Contenido";
        $this->getLayout()->setTitle($title);
        $this->_view->titlesection = $title;
        $padre = $content->contenido_padre;
      } else {
        $this->_view->routeform = $this->route . "/insert";
        $title = "Crear Contenido";
        $this->getLayout()->setTitle($title);
        $this->_view->titlesection = $title;
      }
    } else {
      $this->_view->routeform = $this->route . "/insert";
      $title = "Crear Contenido";
      $this->getLayout()->setTitle($title);
      $this->_view->titlesection = $title;
    }
    $contentpadre = $this->mainModel->getById($padre);
    if ($contentpadre->contenido_tipo == 1) {
      $this->_view->tipo = 4;
    } else if ($contentpadre->contenido_tipo == 2) {
      $this->_view->mostrartipos = 1;
    } else if ($contentpadre->contenido_tipo == 8) {
      $this->_view->tipo = 10;
    } else if ($contentpadre->contenido_tipo == 7) {
      $this->_view->tipo = 9;
    } else if ($contentpadre->contenido_tipo == 15) {
      $this->_view->tipo = 16;
    } else if ($contentpadre->contenido_tipo == 16) {
      $this->_view->tipo = 17;
    } else if ($contentpadre->contenido_tipo == 17) {
      $this->_view->tipo = 18;
    } else if ($contentpadre->contenido_tipo == 18) {
      $this->_view->tipo = 19;
    }
    if ($contentpadre->contenido_seccion) {
      $this->_view->seccion = $contentpadre->contenido_seccion;
    }
    $this->_view->padre = $padre;
    $this->_view->contentpadre = $contentpadre;
    $this->_view->list_contenido_tipo = $this->getContenidotipo($padre);
  }

  /**
   * Inserta la informacion de un Contenido  y redirecciona al listado de Contenidos.
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
      $uploadDocument =  new Core_Model_Upload_Document();

      if ($_FILES['contenido_imagen']['name'] != '') {
        $data['contenido_imagen'] = $uploadImage->upload("contenido_imagen");
      }
      if ($_FILES['contenido_icono']['name'] != '') {
        $data['contenido_icono'] = $uploadImage->upload("contenido_icono");
      }
      if ($_FILES['contenido_archivo']['name'] != '') {
        $data['contenido_archivo'] = $uploadDocument->upload("contenido_archivo");
      }
      if ($_FILES['contenido_fondo_imagen']['name'] != '') {
        $data['contenido_fondo_imagen'] = $uploadImage->upload("contenido_fondo_imagen");
      }

      $id = $this->mainModel->insert($data);
      $this->mainModel->changeOrder($id, $id);
      $data['contenido_id'] = $id;
      $data['log_log'] = print_r($data, true);
      $data['log_tipo'] = 'CREAR CONTENIDO';
      $logModel = new Administracion_Model_DbTable_Log();
      $logModel->insert($data);
    }
    $rutaadicional = "";
    $padre = $this->_getSanitizedParam("contenido_padre");
    if ($padre > 0) {
      $rutaadicional = "?padre=" . $padre;
    }
    header('Location: ' . $this->route . $rutaadicional);
  }

  /**
   * Recibe un identificador  y Actualiza la informacion de un Contenido  y redirecciona al listado de Contenidos.
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
      if ($content->contenido_id) {
        $data = $this->getData();
        $uploadImage =  new Core_Model_Upload_Image();
        if ($_FILES['contenido_imagen']['name'] != '') {
          if ($content->contenido_imagen) {
            $uploadImage->delete($content->contenido_imagen);
          }
          $data['contenido_imagen'] = $uploadImage->upload("contenido_imagen");
        } else {
          $data['contenido_imagen'] = $content->contenido_imagen;
        }
        if ($_FILES['contenido_icono']['name'] != '') {
          if ($content->contenido_icono) {
            $uploadImage->delete($content->contenido_icono);
          }
          $data['contenido_icono'] = $uploadImage->upload("contenido_icono");
        } else {
          $data['contenido_icono'] = $content->contenido_icono;
        }
        $uploadDocument =  new Core_Model_Upload_Document();
        if ($_FILES['contenido_archivo']['name'] != '') {
          if ($content->contenido_archivo) {
            $uploadDocument->delete($content->contenido_archivo);
          }
          $data['contenido_archivo'] = $uploadDocument->upload("contenido_archivo");
        } else {
          $data['contenido_archivo'] = $content->contenido_archivo;
        }
        if ($_FILES['contenido_fondo_imagen']['name'] != '') {
          if ($content->contenido_fondo_imagen) {
            $uploadImage->delete($content->contenido_fondo_imagen);
          }
          $data['contenido_fondo_imagen'] = $uploadImage->upload("contenido_fondo_imagen");
        } else {
          $data['contenido_fondo_imagen'] = $content->contenido_fondo_imagen;
        }
        $this->mainModel->update($data, $id);
      }
      $data['contenido_id'] = $id;
      $data['log_log'] = print_r($data, true);
      $data['log_tipo'] = 'EDITAR CONTENIDO';
      $logModel = new Administracion_Model_DbTable_Log();
      $logModel->insert($data);
    }
    $rutaadicional = "";
    $padre = $this->_getSanitizedParam("contenido_padre");
    if ($padre > 0) {
      $rutaadicional = "?padre=" . $padre;
    }
    header('Location: ' . $this->route . $rutaadicional);
  }

  /**
   * Recibe un identificador  y elimina un Contenido  y redirecciona al listado de Contenidos.
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
          if (isset($content->contenido_imagen) && $content->contenido_imagen != '') {
            $uploadImage->delete($content->contenido_imagen);
          }
          $uploadDocument =  new Core_Model_Upload_Document();
          if (isset($content->contenido_archivo) && $content->contenido_archivo != '') {
            $uploadDocument->delete($content->contenido_archivo);
          }
          if (isset($content->contenido_fondo_imagen) && $content->contenido_fondo_imagen != '') {
            $uploadImage->delete($content->contenido_fondo_imagen);
          }
          $this->mainModel->deleteRegister($id);
          $data = (array) $content;
          $data['log_log'] = print_r($data, true);
          $data['log_tipo'] = 'BORRAR CONTENIDO';
          $logModel = new Administracion_Model_DbTable_Log();
          $logModel->insert($data);
        }
      }
    }
    $rutaadicional = "";
    $padre = $this->_getSanitizedParam("padre");
    if ($padre > 0) {
      $rutaadicional = "?padre=" . $padre;
    }
    header('Location: ' . $this->route . $rutaadicional);
  }

  /**
   * Recibe la informacion del formulario y la retorna en forma de array para la edicion y creacion de Contenido.
   *
   * @return array con toda la informacion recibida del formulario.
   */
  private function getData()
  {
    $data = array();
    if ($this->_getSanitizedParam("contenido_seccion") == '') {
      $data['contenido_seccion'] = '0';
    } else {
      $data['contenido_seccion'] = $this->_getSanitizedParam("contenido_seccion");
    }
    if ($this->_getSanitizedParam("contenido_tipo") == '') {
      $data['contenido_tipo'] = '0';
    } else {
      $data['contenido_tipo'] = $this->_getSanitizedParam("contenido_tipo");
    }
    if ($this->_getSanitizedParam("contenido_padre") == '') {
      $data['contenido_padre'] = '0';
    } else {
      $data['contenido_padre'] = $this->_getSanitizedParam("contenido_padre");
    }
    $data['contenido_columna'] = $this->_getSanitizedParam("contenido_columna");
    if ($this->_getSanitizedParam("contenido_columna_espacios") == '') {
      $data['contenido_columna_espacios'] = '0';
    } else {
      $data['contenido_columna_espacios'] = $this->_getSanitizedParam("contenido_columna_espacios");
    }
    if ($this->_getSanitizedParam("contenido_columna_alineacion") == '') {
      $data['contenido_columna_alineacion'] = '0';
    } else {
      $data['contenido_columna_alineacion'] = $this->_getSanitizedParam("contenido_columna_alineacion");
    }
    if ($this->_getSanitizedParam("contenido_disenio") == '') {
      $data['contenido_disenio'] = '0';
    } else {
      $data['contenido_disenio'] = $this->_getSanitizedParam("contenido_disenio");
    }
    if ($this->_getSanitizedParam("contenido_borde") == '') {
      $data['contenido_borde'] = '0';
    } else {
      $data['contenido_borde'] = $this->_getSanitizedParam("contenido_borde");
    }
    if ($this->_getSanitizedParam("contenido_estado") == '') {
      $data['contenido_estado'] = '0';
    } else {
      $data['contenido_estado'] = $this->_getSanitizedParam("contenido_estado");
    }
    $data['contenido_fecha'] = $this->_getSanitizedParam("contenido_fecha");
    $data['contenido_caja'] = $this->_getSanitizedParam("contenido_caja");
    $data['contenido_top'] = $this->_getSanitizedParam("contenido_top");
    $data['contenido_bottom'] = $this->_getSanitizedParam("contenido_bottom");
    $data['contenido_left'] = $this->_getSanitizedParam("contenido_left");
    $data['contenido_right'] = $this->_getSanitizedParam("contenido_right");
    $data['contenido_titulo'] = $this->_getSanitizedParamHtml("contenido_titulo");
    $data['contenido_subtitulo'] = $this->_getSanitizedParamHtml("contenido_subtitulo");
    if ($this->_getSanitizedParam("contenido_titulo_ver") == '') {
      $data['contenido_titulo_ver'] = '0';
    } else {
      $data['contenido_titulo_ver'] = $this->_getSanitizedParam("contenido_titulo_ver");
    }
    $data['contenido_imagen'] = "";
    $data['contenido_fondo_imagen'] = "";
    if ($this->_getSanitizedParam("contenido_fondo_imagen_tipo") == '') {
      $data['contenido_fondo_imagen_tipo'] = '0';
    } else {
      $data['contenido_fondo_imagen_tipo'] = $this->_getSanitizedParam("contenido_fondo_imagen_tipo");
    }

    if ($this->_getSanitizedParam("contenido_enlace_abrir") == '') {
      $data['contenido_enlace_abrir'] = '0';
    } else {
      $data['contenido_enlace_abrir'] = $this->_getSanitizedParam("contenido_enlace_abrir");
    }
    $data['contenido_fondo_color'] = $this->_getSanitizedParam("contenido_fondo_color");
    $data['contenido_introduccion'] = $this->_getSanitizedParamHtml("contenido_introduccion");
    $data['contenido_descripcion'] = $this->_getSanitizedParamHtml("contenido_descripcion");
    $data['contenido_enlace'] = $this->_getSanitizedParam("contenido_enlace");
    $data['contenido_vermas'] = $this->_getSanitizedParam("contenido_vermas");
    $data['contenido_video'] = $this->_getSanitizedParamHtml("contenido_video");
    $data['contenido_icono'] = $this->_getSanitizedParam("contenido_icono");
    return $data;
  }

  /**
   * Genera los valores del campo Seccion.
   *
   * @return array cadena con los valores del campo Seccion.
   */
  private function getContenidoseccion()
  {
    $array = array();
    $array['1'] = 'Home';
    $array['2'] = 'Home 2';
    return $array;
  }


  /**
   * Genera los valores del campo Tipo.
   *
   * @return array cadena con los valores del campo Tipo.
   */
  private function getContenidotipo($idpadre = 0)
  {
    $padre = $this->mainModel->getById($idpadre);
    $array = array();
    if ($padre == 0) {
      $array['3'] = 'Contenido Simple';
      $array['1'] = 'Banner';
      $array['2'] = 'Contenedor';
    } else if ($padre->contenido_tipo == 1) {
      $array['4'] = 'Banner';
    } else if ($padre->contenido_tipo == 2) {
      $array['5'] = 'Contenido';
      $array['6'] = 'Carrousel';
      $array['7'] = 'Acordion';
      $array['8'] = 'Slider';
    } else if ($padre->contenido_tipo == 8) {
      $array['10'] = 'itemSlider';
    }
    return $array;
  }


  /**
   * Genera los valores del campo Tipo de Fondo.
   *
   * @return array cadena con los valores del campo Tipo de Fondo.
   */
  private function getContenidofondoimagentipo()
  {
    $array = array();
    $array['1'] = 'Fondo Estatico';
    $array['2'] = 'Fondo Dinamico';
    return $array;
  }

  /**
   * Genera los valores del campo Tipo de Fondo.
   *
   * @return array cadena con los valores del campo Tipo de Fondo.
   */
  private function getContenidocolumnaespacios()
  {
    $array = array();
    $array['1'] = 'Sin Contenedor Con Espacios';
    $array['2'] = 'Sin Contenedor Sin Espacios';
    $array['3'] = 'Con Contenedor Con Espacios';
    $array['4'] = 'Con Contenedor Sin Espacios';
    return $array;
  }

  /**
   * Genera los valores del campo Tipo de Fondo.
   *
   * @return array cadena con los valores del campo Tipo de Fondo.
   */
  private function getContenidocolumnaalineacion()
  {
    $array = array();
    $array['1'] = 'Izquierda';
    $array['2'] = 'Centro';
    $array['3'] = 'Derecha';
    $array['4'] = 'Distribuido alrededor';
    $array['5'] = 'Distribuido entre los elementos';
    return $array;
  }

  /**
   * Genera los valores del campo Tipo de Fondo.
   *
   * @return array cadena con los valores del campo Tipo de Fondo.
   */
  private function getContenidoenlaceabrir()
  {
    $array = array();
    $array['0'] = 'Ventana Actual';
    $array['1'] = 'Ventana Nueva';
    return $array;
  }
  private function getContenidoeestado()
  {
    $array = array();
    $array['0'] = 'Inactivo';
    $array['1'] = 'Activo';
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
    $padre = $this->_getSanitizedParam('padre');
    $filtros = $filtros . " AND contenido_padre = '$padre' ";
    if (Session::getInstance()->get($this->namefilter) != "") {
      $filters = (object) Session::getInstance()->get($this->namefilter);
      if ($filters->contenido_seccion != '') {
        $filtros = $filtros . " AND contenido_seccion ='" . $filters->contenido_seccion . "'";
      }
      if ($filters->contenido_titulo != '') {
        $filtros = $filtros . " AND contenido_titulo LIKE '%" . $filters->contenido_titulo . "%'";
      }
      if ($filters->contenido_fecha != '') {
        $filtros = $filtros . " AND contenido_fecha LIKE '%" . $filters->contenido_fecha . "%'";
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
      $parramsfilter['contenido_seccion'] =  $this->_getSanitizedParam("contenido_seccion");
      $parramsfilter['contenido_titulo'] =  $this->_getSanitizedParam("contenido_titulo");
      $parramsfilter['contenido_fecha'] =  $this->_getSanitizedParam("contenido_fecha");
      Session::getInstance()->set($this->namefilter, $parramsfilter);
    }
    if ($this->_getSanitizedParam("cleanfilter") == 1) {
      Session::getInstance()->set($this->namefilter, '');
      Session::getInstance()->set($this->namepageactual, 1);
    }
  }
}
