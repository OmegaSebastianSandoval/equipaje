<?php
/**
* Controlador de Logmaletas que permite la  creacion, edicion  y eliminacion de los Log Maletas del Sistema
*/
class Administracion_logmaletasController extends Administracion_mainController
{
	/**
	 * $mainModel  instancia del modelo de  base de datos Log Maletas
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
	protected $_csrf_section = "administracion_logmaletas";

	/**
	 * $namepages nombre de la pvariable en la cual se va a guardar  el numero de seccion en la paginacion del controlador
	 * @var string
	 */
	protected $namepages;



	/**
     * Inicializa las variables principales del controlador logmaletas .
     *
     * @return void.
     */
	public function init()
	{
		$this->mainModel = new Administracion_Model_DbTable_Logmaletas();
		$this->namefilter = "parametersfilterlogmaletas";
		$this->route = "/administracion/logmaletas";
		$this->namepages ="pages_logmaletas";
		$this->namepageactual ="page_actual_logmaletas";
		$this->_view->route = $this->route;
		if(Session::getInstance()->get($this->namepages)){
			$this->pages = Session::getInstance()->get($this->namepages);
		} else {
			$this->pages = 20;
		}
		parent::init();
	}


	/**
     * Recibe la informacion y  muestra un listado de  Log Maletas con sus respectivos filtros.
     *
     * @return void.
     */
	public function indexAction()
	{
		$title = "Administración de Log Maletas";
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
		$this->_view->list_log_maleta = $this->getLogmaleta();
		$this->_view->list_log_usuario = $this->getLogusuario();
	}

	/**
     * Genera la Informacion necesaria para editar o crear un  Log Maletas  y muestra su formulario
     *
     * @return void.
     */
	public function manageAction()
	{
		$this->_view->route = $this->route;
		$this->_csrf_section = "manage_logmaletas_".date("YmdHis");
		$this->_csrf->generateCode($this->_csrf_section);
		$this->_view->csrf_section = $this->_csrf_section;
		$this->_view->csrf = Session::getInstance()->get('csrf')[$this->_csrf_section];
		$this->_view->list_log_maleta = $this->getLogmaleta();
		$this->_view->list_log_usuario = $this->getLogusuario();
		$id = $this->_getSanitizedParam("id");
		if ($id > 0) {
			$content = $this->mainModel->getById($id);
			if($content->log_id){
				$this->_view->content = $content;
				$this->_view->routeform = $this->route."/update";
				$title = "Actualizar Log Maletas";
				$this->getLayout()->setTitle($title);
				$this->_view->titlesection = $title;
			}else{
				$this->_view->routeform = $this->route."/insert";
				$title = "Crear Log Maletas";
				$this->getLayout()->setTitle($title);
				$this->_view->titlesection = $title;
			}
		} else {
			$this->_view->routeform = $this->route."/insert";
			$title = "Crear Log Maletas";
			$this->getLayout()->setTitle($title);
			$this->_view->titlesection = $title;
		}
	}

	/**
     * Inserta la informacion de un Log Maletas  y redirecciona al listado de Log Maletas.
     *
     * @return void.
     */
	public function insertAction(){
		$this->setLayout('blanco');
		$csrf = $this->_getSanitizedParam("csrf");
		if (Session::getInstance()->get('csrf')[$this->_getSanitizedParam("csrf_section")] == $csrf ) {	
			$data = $this->getData();
			$id = $this->mainModel->insert($data);
			
			$data['log_id']= $id;
			$data['log_log'] = print_r($data,true);
			$data['log_tipo'] = 'CREAR LOG MALETAS';
			$logModel = new Administracion_Model_DbTable_Log();
			$logModel->insert($data);
		}
		header('Location: '.$this->route.''.'');
	}

	/**
     * Recibe un identificador  y Actualiza la informacion de un Log Maletas  y redirecciona al listado de Log Maletas.
     *
     * @return void.
     */
	public function updateAction(){
		$this->setLayout('blanco');
		$csrf = $this->_getSanitizedParam("csrf");
		if (Session::getInstance()->get('csrf')[$this->_getSanitizedParam("csrf_section")] == $csrf ) {
			$id = $this->_getSanitizedParam("id");
			$content = $this->mainModel->getById($id);
			if ($content->log_id) {
				$data = $this->getData();
					$this->mainModel->update($data,$id);
			}
			$data['log_id']=$id;
			$data['log_log'] = print_r($data,true);
			$data['log_tipo'] = 'EDITAR LOG MALETAS';
			$logModel = new Administracion_Model_DbTable_Log();
			$logModel->insert($data);}
		header('Location: '.$this->route.''.'');
	}

	/**
     * Recibe un identificador  y elimina un Log Maletas  y redirecciona al listado de Log Maletas.
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
					$data['log_tipo'] = 'BORRAR LOG MALETAS';
					$logModel = new Administracion_Model_DbTable_Log();
					$logModel->insert($data); }
			}
		}
		header('Location: '.$this->route.''.'');
	}

	/**
     * Recibe la informacion del formulario y la retorna en forma de array para la edicion y creacion de Logmaletas.
     *
     * @return array con toda la informacion recibida del formulario.
     */
	private function getData()
	{
		$data = array();
		$data['log_maleta'] = $this->_getSanitizedParam("log_maleta");
		$data['log_tipo'] = $this->_getSanitizedParam("log_tipo");
		$data['log_fecha'] = $this->_getSanitizedParam("log_fecha");
		$data['log_usuario'] = $this->_getSanitizedParam("log_usuario");
		return $data;
	}

	/**
     * Genera los valores del campo log_maleta.
     *
     * @return array cadena con los valores del campo log_maleta.
     */
	private function getLogmaleta()
	{
		$modelData = new Administracion_Model_DbTable_Dependmaletas();
		$data = $modelData->getList();
		$array = array();
		foreach ($data as $key => $value) {
			$array[$value->maleta_id] = $value->maleta_codigo;
		}
		return $array;
	}


	/**
     * Genera los valores del campo log_usuario.
     *
     * @return array cadena con los valores del campo log_usuario.
     */
	private function getLogusuario()
	{
		$modelData = new Administracion_Model_DbTable_Dependuser();
		$data = $modelData->getList();
		$array = array();
		foreach ($data as $key => $value) {
			$array[$value->user_id] = $value->user_names;
		}
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
        if (Session::getInstance()->get($this->namefilter)!="") {
            $filters =(object)Session::getInstance()->get($this->namefilter);
            if ($filters->log_maleta != '') {
                $filtros = $filtros." AND log_maleta LIKE '%".$filters->log_maleta."%'";
            }
            if ($filters->log_usuario != '') {
                $filtros = $filtros." AND log_usuario LIKE '%".$filters->log_usuario."%'";
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
					$parramsfilter['log_maleta'] =  $this->_getSanitizedParam("log_maleta");
					$parramsfilter['log_usuario'] =  $this->_getSanitizedParam("log_usuario");Session::getInstance()->set($this->namefilter, $parramsfilter);
        }
        if ($this->_getSanitizedParam("cleanfilter") == 1) {
            Session::getInstance()->set($this->namefilter, '');
            Session::getInstance()->set($this->namepageactual,1);
        }
    }
}