<?php

class Administracion_dashboardController extends Administracion_mainController
{
  public $botonpanel;
  public function init()
  {
    //si url tiene over escrito
    if ($_SERVER['REQUEST_URI'] == '/administracion/dashboard/over') {
      $this->botonpanel = 8;
    } else {
      $this->botonpanel = 7;
    }
    parent::init();
  }

  public function indexAction()
  {
    if (!Administracion_Model_Acceso::canDashboard()) {
      Administracion_Model_Acceso::redirectHome();
    }
    $maletasModel = new Administracion_Model_DbTable_Maletas();
    $aerolineasModel = new Administracion_Model_DbTable_Aerolineas();
    $this->_view->grafica1 = $maletasModel->getList_maletas_aerolineas_grafica();
    $this->_view->grafica2 = $maletasModel->getList_maletas_estado_grafica();
    $this->_view->grafica3 = $maletasModel->getList_maletas_hora_grafica();
    $aerolineas = $aerolineasModel->getList();
  }

  public function overAction()
  {
    if (!Administracion_Model_Acceso::canDashboardOver()) {
      Administracion_Model_Acceso::redirectHome();
    }
    $this->botonpanel = 8;
    $maletasModel = new Administracion_Model_DbTable_Maletas();

    $this->_view->graficaOverTipo = $maletasModel->getListOverTipo();
    $this->_view->graficaOverLugar = $maletasModel->getListOverLugar();
    $this->_view->graficaOverHora = $maletasModel->getListOverHora();

    $this->_view->grafica1 = $maletasModel->getList_maletas_aerolineas_grafica_over();
    $this->_view->grafica2 = $maletasModel->getList_maletas_estado_grafica_over();
    $this->_view->grafica3 = $maletasModel->getListOverHora();
  }
}