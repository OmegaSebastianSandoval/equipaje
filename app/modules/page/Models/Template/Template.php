<?php 

/**
* 
*/
class Page_Model_Template_Template
{

    protected $_view;

    function __construct($view)
    {
        $this->_view = $view;
    }


	public function getContentseccion($seccion){
		$contenidoModel = new Page_Model_DbTable_Contenido();
		$contenidos = [];
		$rescontenidos = $contenidoModel->getList("contenido_estado='1' AND contenido_seccion = '$seccion' AND contenido_padre = '0' ","orden ASC");
		foreach ($rescontenidos as $key => $contenido) {
			$contenidos[$key] = [];
			$contenidos[$key]['detalle'] = $contenido;
			$padre = $contenido->contenido_id;
			$hijos = $contenidoModel->getList("contenido_estado='1' AND contenido_padre = '$padre' ","orden ASC");
			foreach ($hijos as $key2 => $hijo) {
				$padre = $hijo->contenido_id;
				$contenidos[$key]['hijos'][$key2] = [];
				$contenidos[$key]['hijos'][$key2]['detalle'] = $hijo;
				$nietos = $contenidoModel->getList("contenido_padre = '$padre' ","orden ASC");
				if($nietos){
					$contenidos[$key]['hijos'][$key2]['hijos'] = $nietos;
				}
			}
		}
		$this->_view->contenidos = $contenidos;
		return $this->_view->getRoutPHP("modules/page/Views/template/contenedor.php");
	}

	public function banner($seccion){
		$this->_view->seccionbanner = $seccion;
		$publicidadModel = new Page_Model_DbTable_Publicidad();
		$this->_view->banners = $publicidadModel->getList("publicidad_seccion = '$seccion' AND publicidad_estado = '1'","orden ASC");

		return $this->_view->getRoutPHP("modules/page/Views/template/bannerprincipal.php");
	}
}