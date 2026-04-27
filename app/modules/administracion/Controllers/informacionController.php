<?php

/**
 * Controlador de informacion que permite la  creacion, edicion  y eliminacion de los info pagina del Sistema
 */
class Administracion_informacionController extends Administracion_mainController
{
	public $botonpanel = 1;
	/**
	 * $mainModel  instancia del modelo de  base de datos info pagina
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
	protected $_csrf_section = "administracion_informacion";

	/**
	 * $namepages nombre de la pvariable en la cual se va a guardar  el numero de seccion en la paginacion del controlador
	 * @var string
	 */
	protected $namepages;



	/**
	 * Inicializa las variables principales del controlador informacion .
	 *
	 * @return void.
	 */
	public function init()
	{
		$this->mainModel = new Administracion_Model_DbTable_Informacion();
		$this->namefilter = "parametersfilterinformacion";
		$this->route = "/administracion/informacion";
		$this->namepages = "pages_informacion";
		$this->_view->route = $this->route;
		if (Session::getInstance()->get($this->namepages)) {
			$this->pages = Session::getInstance()->get($this->namepages);
		} else {
			$this->pages = 20;
		}
		parent::init();
	}


	/**
	 *  muestra el formulario para realizar la edicion de la informacion
	 *
	 * @return void.
	 */
	public function indexAction()
	{
		$this->_csrf_section = "manage_informacion_" . date("YmdHis");
		$this->_csrf->generateCode($this->_csrf_section);
		$this->_view->csrf_section = $this->_csrf_section;
		$this->_view->csrf = Session::getInstance()->get('csrf')[$this->_csrf_section];
		$id = 1;
		if ($id > 0) {
			$content = $this->mainModel->getById($id);
			if ($content->info_pagina_id) {
				$this->_view->content = $content;
				$this->_view->routeform = $this->route . "/update";
				$this->getLayout()->setTitle("Actualizar info pagina");
			} else {
				$this->_view->routeform = $this->route . "/insert";
				$this->getLayout()->setTitle("Crear info pagina");
			}
		} else {
			$this->_view->routeform = $this->route . "/insert";
			$this->getLayout()->setTitle("Crear info pagina");
		}
	}


	/**
	 * Inserta la informacion de un infopage  y redirecciona al listado de infopage.
	 *
	 * @return void.
	 */
	public function insertAction()
	{
		$csrf = $this->_getSanitizedParam("csrf");
		if (Session::getInstance()->get('csrf')[$this->_getSanitizedParam("csrf_section")] == $csrf) {
			$data = $this->getData();
			$uploadDocument =  new Core_Model_Upload_Document();
			$uploadImage =  new Core_Model_Upload_Image();
			if ($_FILES['info_pagina_favicon']['name'] != '') {
				$data['info_pagina_favicon'] = $uploadImage->upload("info_pagina_favicon");
			}

			if ($_FILES['info_pagina_robot']['name'] != '') {
				$data['info_pagina_robot'] = $uploadDocument->uploadpublic("info_pagina_robot", "robots.txt");
			}
			if ($_FILES['info_pagina_sitemap']['name'] != '') {
				$data['info_pagina_sitemap'] = $uploadDocument->uploadpublic("info_pagina_sitemap", "sitemap.xml");
			}
			if ($_FILES['info_pagina_favicon']['name'] != '') {
				$data['info_pagina_favicon'] = $uploadImage->upload("info_pagina_favicon");
			}
			$id = $this->mainModel->insert($data);
			$this->mainModel->changeOrder($id, $id);
		}
		header('Location: /administracion/panel');
	}

	/**
	 * Recibe un identificador  y Actualiza la informacion de un infopage  y redirecciona al listado de infopage.
	 *
	 * @return void.
	 */
	public function updateAction()
	{
		$csrf = $this->_getSanitizedParam("csrf");
		$uploadImage =  new Core_Model_Upload_Image();
		if (Session::getInstance()->get('csrf')[$this->_getSanitizedParam("csrf_section")] == $csrf) {
			$id = $this->_getSanitizedParam("id");
			$content = $this->mainModel->getById($id);
			if ($content->info_pagina_id) {
				$data = $this->getData();
				$uploadDocument =  new Core_Model_Upload_Document();
				if ($_FILES['info_pagina_robot']['name'] != '') {
					$data['info_pagina_robot'] = $uploadDocument->uploadpublic("info_pagina_robot", "robots.txt");
				} else {
					$data['info_pagina_robot'] = $content->info_pagina_robot;
				}

				if ($_FILES['info_pagina_sitemap']['name'] != '') {
					$data['info_pagina_sitemap'] = $uploadDocument->uploadpublic("info_pagina_sitemap", "sitemap.xml");
				} else {
					$data['info_pagina_sitemap'] = $content->info_pagina_sitemap;
				}
				$uploadImage =  new Core_Model_Upload_Image();
				
				if ($_FILES['info_pagina_favicon']['name'] != '') {
					if ($content->info_pagina_favicon) {
						$uploadImage->delete($content->info_pagina_favicon);
					}
					$data['info_pagina_favicon'] = $uploadImage->upload("info_pagina_favicon");
				} else {
					$data['info_pagina_favicon'] = $content->info_pagina_favicon;
				}
				$this->mainModel->update($data, $id);
			}
		}
		header('Location: /administracion/panel');
	}


	/**
	 * Recibe la informacion del formulario y la retorna en forma de array para la edicion y creacion de Infopage.
	 *
	 * @return array con toda la informacion recibida del formulario.
	 */
	private function getData()
	{
		$data = array();
		$data['info_pagina_facebook'] = $this->_getSanitizedParam("info_pagina_facebook");
		$data['info_pagina_instagram'] = $this->_getSanitizedParam("info_pagina_instagram");
		$data['info_pagina_twitter'] = $this->_getSanitizedParam("info_pagina_twitter");
		$data['info_pagina_pinterest'] = $this->_getSanitizedParam("info_pagina_pinterest");
		$data['info_pagina_youtube'] = $this->_getSanitizedParam("info_pagina_youtube");
		$data['info_pagina_flickr'] = $this->_getSanitizedParam("info_pagina_flickr");
		$data['info_pagina_linkedin'] = $this->_getSanitizedParam("info_pagina_linkedin");
		$data['info_pagina_google'] = $this->_getSanitizedParam("info_pagina_google");
		$data['info_pagina_telefono'] = $this->_getSanitizedParam("info_pagina_telefono");
		$data['info_pagina_whatsapp'] = $this->_getSanitizedParam("info_pagina_whatsapp");
		$data['info_pagina_correos_contacto'] = $this->_getSanitizedParam("info_pagina_correos_contacto");
		$data['info_pagina_direccion_contacto'] = $this->_getSanitizedParam("info_pagina_direccion_contacto");
		$data['info_pagina_informacion_contacto'] = $this->_getSanitizedParamHtml("info_pagina_informacion_contacto");
		$data['info_pagina_informacion_contacto_footer'] = $this->_getSanitizedParamHtml("info_pagina_informacion_contacto_footer");
		$data['info_pagina_latitud'] = $this->_getSanitizedParam("info_pagina_latitud");
		$data['info_pagina_longitud'] = $this->_getSanitizedParam("info_pagina_longitud");
		$data['info_pagina_zoom'] = $this->_getSanitizedParam("info_pagina_zoom");
		$data['info_pagina_descripcion'] = $this->_getSanitizedParam("info_pagina_descripcion");
		$data['info_pagina_tags'] = $this->_getSanitizedParam("info_pagina_tags");
		$data['info_pagina_robot'] = "";
		$data['info_pagina_sitemap'] = "";
		$data['info_pagina_scripts'] = $this->_getSanitizedParamHtml("info_pagina_scripts");
		$data['info_pagina_metricas'] = $this->_getSanitizedParamHtml("info_pagina_metricas");

		$data['info_pagina_host'] = $this->_getSanitizedParamHtml("info_pagina_host");
		$data['info_pagina_port'] = $this->_getSanitizedParamHtml("info_pagina_port");
		$data['info_pagina_username'] = $this->_getSanitizedParamHtml("info_pagina_username");
		$data['info_pagina_password'] = $this->_getSanitizedParamHtml("info_pagina_password");
		$data['info_pagina_correo_remitente'] = $this->_getSanitizedParamHtml("info_pagina_correo_remitente");
		$data['info_pagina_nombre_remitente'] = $this->_getSanitizedParamHtml("info_pagina_nombre_remitente");
		$data['info_pagina_correo_oculto'] = $this->_getSanitizedParamHtml("info_pagina_correo_oculto");

		$data['info_pagina_titulo_legal'] = $this->_getSanitizedParamHtml("info_pagina_titulo_legal");
		$data['info_pagina_descripcion_legal'] = $this->_getSanitizedParamHtml("info_pagina_descripcion_legal");
		$data['info_pagina_favicon'] = "";

		return $data;
	}
}
