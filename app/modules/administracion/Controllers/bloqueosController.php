<?php
/**
* Controlador de Bloqueos que permite la  creacion, edicion  y eliminacion de los bloqueos del Sistema
*/
class Administracion_bloqueosController extends Administracion_mainController
{
	/**
	 * $mainModel  instancia del modelo de  base de datos bloqueos
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
	protected $_csrf_section = "administracion_bloqueos";

	/**
	 * $namepages nombre de la pvariable en la cual se va a guardar  el numero de seccion en la paginacion del controlador
	 * @var string
	 */
	protected $namepages;



	/**
     * Inicializa las variables principales del controlador bloqueos .
     *
     * @return void.
     */
	public function init()
	{
		$this->mainModel = new Administracion_Model_DbTable_Bloqueos();
		$this->namefilter = "parametersfilterbloqueos";
		$this->route = "/administracion/bloqueos";
		$this->namepages ="pages_bloqueos";
		$this->namepageactual ="page_actual_bloqueos";
		$this->_view->route = $this->route;
		if(Session::getInstance()->get($this->namepages)){
			$this->pages = Session::getInstance()->get($this->namepages);
		} else {
			$this->pages = 20;
		}
		parent::init();
	}


	/**
     * Recibe la informacion y  muestra un listado de  bloqueos con sus respectivos filtros.
     *
     * @return void.
     */
	public function indexAction()
	{
		$title = "Administración de bloqueos";
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
     * Genera la Informacion necesaria para editar o crear un  bloqueos  y muestra su formulario
     *
     * @return void.
     */
	public function manageAction()
	{
		$this->_view->route = $this->route;
		$this->_csrf_section = "manage_bloqueos_".date("YmdHis");
		$this->_csrf->generateCode($this->_csrf_section);
		$this->_view->csrf_section = $this->_csrf_section;
		$this->_view->csrf = Session::getInstance()->get('csrf')[$this->_csrf_section];
		$id = $this->_getSanitizedParam("id");
		if ($id > 0) {
			$content = $this->mainModel->getById($id);
			if($content->bloqueo_id){
				$this->_view->content = $content;
				$this->_view->routeform = $this->route."/update";
				$title = "Actualizar bloqueos";
				$this->getLayout()->setTitle($title);
				$this->_view->titlesection = $title;
			}else{
				$this->_view->routeform = $this->route."/insert";
				$title = "Crear bloqueos";
				$this->getLayout()->setTitle($title);
				$this->_view->titlesection = $title;
			}
		} else {
			$this->_view->routeform = $this->route."/insert";
			$title = "Crear bloqueos";
			$this->getLayout()->setTitle($title);
			$this->_view->titlesection = $title;
		}
	}

	/**
     * Inserta la informacion de un bloqueos  y redirecciona al listado de bloqueos.
     *
     * @return void.
     */
	public function insertAction(){
		$this->setLayout('blanco');
		$csrf = $this->_getSanitizedParam("csrf");
		if (Session::getInstance()->get('csrf')[$this->_getSanitizedParam("csrf_section")] == $csrf ) {	
			$data = $this->getData();
			$id = $this->mainModel->insert($data);
			
			$data['bloqueo_id']= $id;
			$data['log_log'] = print_r($data,true);
			$data['log_tipo'] = 'CREAR BLOQUEOS';
			$logModel = new Administracion_Model_DbTable_Log();
			$logModel->insert($data);
		}
		header('Location: '.$this->route.''.'');
	}

	/**
     * Recibe un identificador  y Actualiza la informacion de un bloqueos  y redirecciona al listado de bloqueos.
     *
     * @return void.
     */
	public function updateAction(){
		$this->setLayout('blanco');
		$csrf = $this->_getSanitizedParam("csrf");
		if (Session::getInstance()->get('csrf')[$this->_getSanitizedParam("csrf_section")] == $csrf ) {
			$id = $this->_getSanitizedParam("id");
			$content = $this->mainModel->getById($id);
			if ($content->bloqueo_id) {
				$data = $this->getData();
					$this->mainModel->update($data,$id);
			}
			$data['bloqueo_id']=$id;
			$data['log_log'] = print_r($data,true);
			$data['log_tipo'] = 'EDITAR BLOQUEOS';
			$logModel = new Administracion_Model_DbTable_Log();
			$logModel->insert($data);}
		header('Location: '.$this->route.''.'');
	}

	/**
     * Recibe un identificador  y elimina un bloqueos  y redirecciona al listado de bloqueos.
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
					$this->mainModel->deleteRegister($id);$data = (array)$content;
					$data['log_log'] = print_r($data,true);
					$data['log_tipo'] = 'BORRAR BLOQUEOS';
					$logModel = new Administracion_Model_DbTable_Log();
					$logModel->insert($data); }
			}
		}
		header('Location: '.$this->route.''.'');
	}

	/**
     * Recibe la informacion del formulario y la retorna en forma de array para la edicion y creacion de Bloqueos.
     *
     * @return array con toda la informacion recibida del formulario.
     */
	private function getData()
	{
		$data = array();
		$data['bloqueo_usuario'] = $this->_getSanitizedParam("bloqueo_usuario");
		$data['bloqueo_fechaintento'] = $this->_getSanitizedParam("bloqueo_fechaintento");
		if($this->_getSanitizedParam("bloqueo_intentosfallidos") == '' ) {
			$data['bloqueo_intentosfallidos'] = '0';
		} else {
			$data['bloqueo_intentosfallidos'] = $this->_getSanitizedParam("bloqueo_intentosfallidos");
		}
		$data['bloqueo_ip'] = $this->_getSanitizedParam("bloqueo_ip");
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
            if ($filters->bloqueo_usuario != '') {
                $filtros = $filtros." AND bloqueo_usuario LIKE '%".$filters->bloqueo_usuario."%'";
            }
            if ($filters->bloqueo_fechaintento != '') {
                $filtros = $filtros." AND bloqueo_fechaintento LIKE '%".$filters->bloqueo_fechaintento."%'";
            }
            if ($filters->bloqueo_intentosfallidos != '') {
                $filtros = $filtros." AND bloqueo_intentosfallidos LIKE '%".$filters->bloqueo_intentosfallidos."%'";
            }
            if ($filters->bloqueo_ip != '') {
                $filtros = $filtros." AND bloqueo_ip LIKE '%".$filters->bloqueo_ip."%'";
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
					$parramsfilter['bloqueo_usuario'] =  $this->_getSanitizedParam("bloqueo_usuario");
					$parramsfilter['bloqueo_fechaintento'] =  $this->_getSanitizedParam("bloqueo_fechaintento");
					$parramsfilter['bloqueo_intentosfallidos'] =  $this->_getSanitizedParam("bloqueo_intentosfallidos");
					$parramsfilter['bloqueo_ip'] =  $this->_getSanitizedParam("bloqueo_ip");Session::getInstance()->set($this->namefilter, $parramsfilter);
        }
        if ($this->_getSanitizedParam("cleanfilter") == 1) {
            Session::getInstance()->set($this->namefilter, '');
            Session::getInstance()->set($this->namepageactual,1);
        }
    }
}