<?php
/**
* Controlador de Aerolineas que permite la  creacion, edicion  y eliminacion de los Aerolineas del Sistema
*/
class Administracion_aerolineasController extends Administracion_mainController
{
  public $botonpanel = 6;
	/**
	 * $mainModel  instancia del modelo de  base de datos Aerolineas
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
	protected $pages ;

	/**
	 * $namefilter nombre de la variable a la fual se le van a guardar los filtros
	 * @var string
	 */
	protected $namefilter;

	/**
	 * $_csrf_section  nombre de la variable general csrf  que se va a almacenar en la session
	 * @var string
	 */
	protected $_csrf_section = "administracion_aerolineas";

	/**
	 * $namepages nombre de la pvariable en la cual se va a guardar  el numero de seccion en la paginacion del controlador
	 * @var string
	 */
	protected $namepages;



	/**
     * Inicializa las variables principales del controlador aerolineas .
     *
     * @return void.
     */
	public function init()
	{
		$this->mainModel = new Administracion_Model_DbTable_Aerolineas();
		$this->namefilter = "parametersfilteraerolineas";
		$this->route = "/administracion/aerolineas";
		$this->namepages ="pages_aerolineas";
		$this->namepageactual ="page_actual_aerolineas";
		$this->_view->route = $this->route;
		if(Session::getInstance()->get($this->namepages)){
			$this->pages = Session::getInstance()->get($this->namepages);
		} else {
			$this->pages = 20;
		}
		parent::init();
	}


	/**
     * Recibe la informacion y  muestra un listado de  Aerolineas con sus respectivos filtros.
     *
     * @return void.
     */
	public function indexAction()
	{
		$title = "Administración de Aerolineas";
		$this->getLayout()->setTitle($title);
		$this->_view->titlesection = $title;
		$this->filters();
		$this->_view->csrf = Session::getInstance()->get('csrf')[$this->_csrf_section];
		$filters =(object)Session::getInstance()->get($this->namefilter);
        $this->_view->filters = $filters;
		$filters = $this->getFilter();
		$order = "";
		$list = $this->mainModel->getList($filters,$order);
		$amount = $this->pages;
		$page = $this->_getSanitizedParam("page");
		if (!$page && Session::getInstance()->get($this->namepageactual)) {
		   	$page = Session::getInstance()->get($this->namepageactual);
		   	$start = ($page - 1) * $amount;
		} else if(!$page){
			$start = 0;
		   	$page=1;
			Session::getInstance()->set($this->namepageactual,$page);
		} else {
			Session::getInstance()->set($this->namepageactual,$page);
		   	$start = ($page - 1) * $amount;
		}
		$this->_view->register_number = count($list);
		$this->_view->pages = $this->pages;
		$this->_view->totalpages = ceil(count($list)/$amount);
		$this->_view->page = $page;
		$this->_view->lists = $this->mainModel->getListPages($filters,$order,$start,$amount);
		$this->_view->csrf_section = $this->_csrf_section;
	}

	/**
     * Genera la Informacion necesaria para editar o crear un  Aerolineas  y muestra su formulario
     *
     * @return void.
     */
	public function manageAction()
	{
		$this->_view->route = $this->route;
		$this->_csrf_section = "manage_aerolineas_".date("YmdHis");
		$this->_csrf->generateCode($this->_csrf_section);
		$this->_view->csrf_section = $this->_csrf_section;
		$this->_view->csrf = Session::getInstance()->get('csrf')[$this->_csrf_section];
		$id = $this->_getSanitizedParam("id");
		if ($id > 0) {
			$content = $this->mainModel->getById($id);
			if($content->aerolinea_id){
				$this->_view->content = $content;
				$this->_view->routeform = $this->route."/update";
				$title = "Actualizar Aerolineas";
				$this->getLayout()->setTitle($title);
				$this->_view->titlesection = $title;
			}else{
				$this->_view->routeform = $this->route."/insert";
				$title = "Crear Aerolineas";
				$this->getLayout()->setTitle($title);
				$this->_view->titlesection = $title;
			}
		} else {
			$this->_view->routeform = $this->route."/insert";
			$title = "Crear Aerolineas";
			$this->getLayout()->setTitle($title);
			$this->_view->titlesection = $title;
		}
	}

	/**
     * Inserta la informacion de un Aerolineas  y redirecciona al listado de Aerolineas.
     *
     * @return void.
     */
	public function insertAction(){
		$this->setLayout('blanco');
		$csrf = $this->_getSanitizedParam("csrf");
		if (Session::getInstance()->get('csrf')[$this->_getSanitizedParam("csrf_section")] == $csrf ) {	
			$data = $this->getData();
			$uploadImage =  new Core_Model_Upload_Image();
			if($_FILES['aerolinea_imagen']['name'] != ''){
				$data['aerolinea_imagen'] = $uploadImage->upload("aerolinea_imagen");
			}
			$id = $this->mainModel->insert($data);
			
			$data['aerolinea_id']= $id;
			$data['log_log'] = print_r($data,true);
			$data['log_tipo'] = 'CREAR AEROLINEAS';
			$logModel = new Administracion_Model_DbTable_Log();
			$logModel->insert($data);
		}
		header('Location: '.$this->route.''.'');
	}

	/**
     * Recibe un identificador  y Actualiza la informacion de un Aerolineas  y redirecciona al listado de Aerolineas.
     *
     * @return void.
     */
	public function updateAction(){
		$this->setLayout('blanco');
		$csrf = $this->_getSanitizedParam("csrf");
		if (Session::getInstance()->get('csrf')[$this->_getSanitizedParam("csrf_section")] == $csrf ) {
			$id = $this->_getSanitizedParam("id");
			$content = $this->mainModel->getById($id);
			if ($content->aerolinea_id) {
				$data = $this->getData();
				$uploadImage =  new Core_Model_Upload_Image();
				if($_FILES['aerolinea_imagen']['name'] != ''){
					if($content->aerolinea_imagen){
						$uploadImage->delete($content->aerolinea_imagen);
					}
					$data['aerolinea_imagen'] = $uploadImage->upload("aerolinea_imagen");
				} else {
					$data['aerolinea_imagen'] = $content->aerolinea_imagen;
				}
				$this->mainModel->update($data,$id);
			}
			$data['aerolinea_id']=$id;
			$data['log_log'] = print_r($data,true);
			$data['log_tipo'] = 'EDITAR AEROLINEAS';
			$logModel = new Administracion_Model_DbTable_Log();
			$logModel->insert($data);}
		header('Location: '.$this->route.''.'');
	}

	/**
     * Recibe un identificador  y elimina un Aerolineas  y redirecciona al listado de Aerolineas.
     *
     * @return void.
     */
	public function deleteAction()
	{
		$this->setLayout('blanco');
		$csrf = $this->_getSanitizedParam("csrf");
		if (Session::getInstance()->get('csrf')[$this->_csrf_section] == $csrf ) {
			$id =  $this->_getSanitizedParam("id");
			if (isset($id) && $id > 0) {
				$content = $this->mainModel->getById($id);
				if (isset($content)) {
					$uploadImage =  new Core_Model_Upload_Image();
					if (isset($content->aerolinea_imagen) && $content->aerolinea_imagen != '') {
						$uploadImage->delete($content->aerolinea_imagen);
					}
					$this->mainModel->deleteRegister($id);$data = (array)$content;
					$data['log_log'] = print_r($data,true);
					$data['log_tipo'] = 'BORRAR AEROLINEAS';
					$logModel = new Administracion_Model_DbTable_Log();
					$logModel->insert($data); }
			}
		}
		header('Location: '.$this->route.''.'');
	}

	/**
     * Recibe la informacion del formulario y la retorna en forma de array para la edicion y creacion de Aerolineas.
     *
     * @return array con toda la informacion recibida del formulario.
     */
	private function getData()
	{
		$data = array();
		$data['aerolinea_estado'] = $this->_getSanitizedParam("aerolinea_estado");
		$data['aerolinea_nombre'] = $this->_getSanitizedParam("aerolinea_nombre");
		$data['aerolinea_correo'] = $this->_getSanitizedParam("aerolinea_correo");
		$data['aerolinea_telefono'] = $this->_getSanitizedParam("aerolinea_telefono");
		$data['aerolinea_contrasena'] = $this->_getSanitizedParam("aerolinea_contrasena");
		$data['aerolinea_codigo'] = $this->_getSanitizedParam("aerolinea_codigo");
		$data['aerolinea_iata'] = strtoupper(preg_replace('/[^A-Za-z0-9]/', '', $this->_getSanitizedParam("aerolinea_iata")));
		$data['aerolinea_imagen'] = "";
		$data['aerolinea_correo_over'] = $this->_getSanitizedParam("aerolinea_correo_over");
    $data['aerolinea_telefono_over'] = $this->_getSanitizedParam("aerolinea_telefono_over");
		return $data;
	}
	/**
     * Genera la consulta con los filtros de este controlador.
     *
     * @return array cadena con los filtros que se van a asignar a la base de datos
     */
    protected function getFilter()
    {
    	$filtros = " 1 = 1 ";
        if (Session::getInstance()->get($this->namefilter)!="") {
            $filters =(object)Session::getInstance()->get($this->namefilter);
            if ($filters->aerolinea_nombre != '') {
                $filtros = $filtros." AND aerolinea_nombre LIKE '%".$filters->aerolinea_nombre."%'";
            }
            if ($filters->aerolinea_correo != '') {
                $filtros = $filtros." AND aerolinea_correo LIKE '%".$filters->aerolinea_correo."%'";
            }
            if ($filters->aerolinea_codigo != '') {
                $filtros = $filtros." AND aerolinea_codigo LIKE '%".$filters->aerolinea_codigo."%'";
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
        if ($this->getRequest()->isPost()== true) {
        	Session::getInstance()->set($this->namepageactual,1);
            $parramsfilter = array();
					$parramsfilter['aerolinea_nombre'] =  $this->_getSanitizedParam("aerolinea_nombre");
					$parramsfilter['aerolinea_correo'] =  $this->_getSanitizedParam("aerolinea_correo");
					$parramsfilter['aerolinea_codigo'] =  $this->_getSanitizedParam("aerolinea_codigo");Session::getInstance()->set($this->namefilter, $parramsfilter);
        }
        if ($this->_getSanitizedParam("cleanfilter") == 1) {
            Session::getInstance()->set($this->namefilter, '');
            Session::getInstance()->set($this->namepageactual,1);
        }
    }
}
