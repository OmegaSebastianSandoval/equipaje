<?php
/**
* Controlador de Log que permite la  creacion, edicion  y eliminacion de los logs del Sistema
*/
class Administracion_logController extends Administracion_mainController
{
	/**
	 * $mainModel  instancia del modelo de  base de datos logs
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
	protected $_csrf_section = "administracion_log";

	/**
	 * $namepages nombre de la pvariable en la cual se va a guardar  el numero de seccion en la paginacion del controlador
	 * @var string
	 */
	protected $namepages;



	/**
     * Inicializa las variables principales del controlador log .
     *
     * @return void.
     */
	public function init()
	{
		$this->mainModel = new Administracion_Model_DbTable_Log();
		$this->namefilter = "parametersfilterlog";
		$this->route = "/administracion/log";
		$this->namepages ="pages_log";
		$this->namepageactual ="page_actual_log";
		$this->_view->route = $this->route;
		if(Session::getInstance()->get($this->namepages)){
			$this->pages = Session::getInstance()->get($this->namepages);
		} else {
			$this->pages = 20;
		}
		parent::init();
	}


	/**
     * Recibe la informacion y  muestra un listado de  logs con sus respectivos filtros.
     *
     * @return void.
     */
	public function indexAction()
	{
		$title = "Logs de usuario";
		$this->getLayout()->setTitle($title);
		$this->_view->titlesection = $title;
		$this->filters();
		$this->_view->csrf = Session::getInstance()->get('csrf')[$this->_csrf_section];
		$filters =(object)Session::getInstance()->get($this->namefilter);
        $this->_view->filters = $filters;
		$filters = $this->getFilter();
		$order = " log_fecha DESC ";
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
		$this->_view->list_log_tipo = $this->getLogtipo();
	}

	/**
     * Genera la Informacion necesaria para editar o crear un  log  y muestra su formulario
     *
     * @return void.
     */
	public function manageAction()
	{
		$this->_view->route = $this->route;
		$this->_csrf_section = "manage_log_".date("YmdHis");
		$this->_csrf->generateCode($this->_csrf_section);
		$this->_view->csrf_section = $this->_csrf_section;
		$this->_view->csrf = Session::getInstance()->get('csrf')[$this->_csrf_section];
		$this->_view->list_log_tipo = $this->getLogtipo();
		$id = $this->_getSanitizedParam("id");
		if ($id > 0) {
			$content = $this->mainModel->getById($id);
			if($content->log_id){
				$this->_view->content = $content;
				$this->_view->routeform = $this->route."/update";
				$title = "Actualizar log";
				$this->getLayout()->setTitle($title);
				$this->_view->titlesection = $title;
			}else{
				$this->_view->routeform = $this->route."/insert";
				$title = "Crear log";
				$this->getLayout()->setTitle($title);
				$this->_view->titlesection = $title;
			}
		} else {
			$this->_view->routeform = $this->route."/insert";
			$title = "Crear log";
			$this->getLayout()->setTitle($title);
			$this->_view->titlesection = $title;
		}
	}

	/**
     * Inserta la informacion de un log  y redirecciona al listado de logs.
     *
     * @return void.
     */
	public function insertAction(){
		$this->setLayout('blanco');
		$csrf = $this->_getSanitizedParam("csrf");
		if (Session::getInstance()->get('csrf')[$this->_getSanitizedParam("csrf_section")] == $csrf ) {
			$data = $this->getData();
			print_r($data);
			$id = $this->mainModel->insert($data);
		}
		header('Location: '.$this->route.''.'');
	}

	/**
     * Recibe un identificador  y Actualiza la informacion de un log  y redirecciona al listado de logs.
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
		}
		header('Location: '.$this->route.''.'');
	}

	/**
     * Recibe un identificador  y elimina un log  y redirecciona al listado de logs.
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
					$this->mainModel->deleteRegister($id);
				}
			}
		}
		header('Location: '.$this->route.''.'');
	}

	/**
     * Recibe la informacion del formulario y la retorna en forma de array para la edicion y creacion de Log.
     *
     * @return array con toda la informacion recibida del formulario.
     */
	private function getData()
	{
		$data = array();
		if($this->_getSanitizedParam("log_usuario") == '' ) {
			$data['log_usuario'] = $_SESSION['kt_login_user'];
		} else {
			$data['log_usuario'] = $this->_getSanitizedParam("log_usuario");
		}
		$data['log_tipo'] = $this->_getSanitizedParam("log_tipo");
		$data['log_fecha'] = $this->_getSanitizedParam("log_fecha");
		$data['log_log'] = $this->_getSanitizedParam("log_log");
		$data['log_fecha'] = date("Y-m-d H:i:s");
		return $data;
	}

	/**
     * Genera los valores del campo tipo.
     *
     * @return array cadena con los valores del campo tipo.
     */
	private function getLogtipo()
	{
		$array = array();
		$tipos = $this->mainModel->getTipos();
		foreach ($tipos as $tipo) {
			$array[$tipo->log_tipo]=$tipo->log_tipo;
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
            if ($filters->log_usuario != '') {
                $filtros = $filtros." AND log_usuario LIKE '%".$filters->log_usuario."%'";
            }
            if ($filters->log_tipo != '') {
                $filtros = $filtros." AND log_tipo ='".$filters->log_tipo."'";
            }
            if ($filters->log_fecha != '') {
                $filtros = $filtros." AND log_fecha LIKE '%".$filters->log_fecha."%'";
            }
            if ($filters->log_log != '') {
                $filtros = $filtros." AND log_log LIKE '%".$filters->log_log."%'";
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
        if ($this->getRequest()->isPost()== true or 1==1) {
        	Session::getInstance()->set($this->namepageactual,1);
            $parramsfilter = array();
					$parramsfilter['log_usuario'] =  $this->_getSanitizedParam("log_usuario");
					$parramsfilter['log_tipo'] =  $this->_getSanitizedParam("log_tipo");
					$parramsfilter['log_fecha'] =  $this->_getSanitizedParam("log_fecha");
					$parramsfilter['log_log'] =  $this->_getSanitizedParam("log_log");Session::getInstance()->set($this->namefilter, $parramsfilter);
        }
        if ($this->_getSanitizedParam("cleanfilter") == 1) {
            Session::getInstance()->set($this->namefilter, '');
            Session::getInstance()->set($this->namepageactual,1);
        }
    }
}