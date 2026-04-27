<?php

/**
 *
 */
class Administracion_panelController extends Administracion_mainController
{
	public $botonpanel = 1;
	public function init()
	{
		$this->mainModel = new Administracion_Model_DbTable_Enlace();
		$this->namefilter = "parametersfilterenlace";
		$this->route = "/administracion/enlace";
		$this->namepages = "pages_enlace";
		$this->namepageactual = "page_actual_enlace";
		$this->_view->route = $this->route;
		if (Session::getInstance()->get($this->namepages)) {
			$this->pages = Session::getInstance()->get($this->namepages);
		} else {
			$this->pages = 20;
		}
		parent::init();
	}
	public function indexAction()
	{
		$this->getLayout()->setTitle("Panel Administrativo");
		$infoModel = new Administracion_Model_DbTable_Informacion();
		$this->_view->info = $infoModel->getList("", "")[0];
		$logModel = new Administracion_Model_DbTable_Log();
		$this->_view->log = $logModel->getList(" log_tipo = 'LOGIN' ", " log_fecha DESC LIMIT 10 ");
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
	}
	public function insertAction()
	{
		$this->setLayout('blanco');
		$csrf = $this->_getSanitizedParam("csrf");
		if (Session::getInstance()->get('csrf')[$this->_getSanitizedParam("csrf_section")] == $csrf) {
			$data = $this->getData();
			$id = $this->mainModel->insert($data);
			$this->mainModel->changeOrder($id, $id);
			$data['enlaces_id'] = $id;
			$data['log_log'] = print_r($data, true);
			$data['log_tipo'] = 'CREAR ENLACE';
			$logModel = new Administracion_Model_DbTable_Log();
			$logModel->insert($data);
		}
		header('Location: ' . $this->route . '' . '');
	}
	public function updateAction()
	{
		$this->setLayout('blanco');
		$csrf = $this->_getSanitizedParam("csrf");
		if (Session::getInstance()->get('csrf')[$this->_getSanitizedParam("csrf_section")] == $csrf) {
			$id = $this->_getSanitizedParam("id");
			$content = $this->mainModel->getById($id);
			if ($content->enlaces_id) {
				$data = $this->getData();
				$this->mainModel->update($data, $id);
			}
			$data['enlaces_id'] = $id;
			$data['log_log'] = print_r($data, true);
			$data['log_tipo'] = 'EDITAR ENLACE';
			$logModel = new Administracion_Model_DbTable_Log();
			$logModel->insert($data);
		}
		header('Location: ' . $this->route . '' . '');
	}
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
					$data['log_tipo'] = 'BORRAR ENLACE';
					$logModel = new Administracion_Model_DbTable_Log();
					$logModel->insert($data); }
			}
		}
		header('Location: /administracion/panel');

		//header('Location: '.$this->route.''.'');
	}
	private function getData()
	{
		$data = array();
		$data['enlaces_titulo'] = $this->_getSanitizedParam("enlaces_titulo");
		$data['enlaces_link'] = $this->_getSanitizedParam("enlaces_link");
		return $data;
	}
	protected function filters()
	{
		if ($this->getRequest()->isPost() == true) {
			Session::getInstance()->set($this->namepageactual, 1);
			$parramsfilter = array();
			$parramsfilter['enlaces_titulo'] =  $this->_getSanitizedParam("enlaces_titulo");
			$parramsfilter['enlaces_link'] =  $this->_getSanitizedParam("enlaces_link");
			Session::getInstance()->set($this->namefilter, $parramsfilter);
		}
		if ($this->_getSanitizedParam("cleanfilter") == 1) {
			Session::getInstance()->set($this->namefilter, '');
			Session::getInstance()->set($this->namepageactual, 1);
		}
	}
	protected function getFilter()
	{
		$filtros = " 1 = 1 ";
		if (Session::getInstance()->get($this->namefilter) != "") {
			$filters = (object) Session::getInstance()->get($this->namefilter);
			if ($filters->enlaces_titulo != '') {
				$filtros = $filtros . " AND enlaces_titulo LIKE '%" . $filters->enlaces_titulo . "%'";
			}
			if ($filters->enlaces_link != '') {
				$filtros = $filtros . " AND enlaces_link LIKE '%" . $filters->enlaces_link . "%'";
			}
		}
		return $filtros;
	}
}
