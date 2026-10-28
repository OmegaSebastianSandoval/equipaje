<?php

/**
 * Cron externo (cron-job.org, cada minuto): sincroniza los vuelos del XML de FIDS y envia las alertas de salida.
 * URL: /page/cron/run?token=XXX (el token esta en app/config/config.php -> fids/cron_token)
 * Va en el modulo page para no exigir sesion de administrador.
 */
class Page_cronController extends Controllers_Abstract
{
  public function runAction()
  {
    header('Content-Type: application/json');
    $token = (string) $this->getRequest()->_getParam('token', '');
    $esperado = (string) Config_Config::getInstance()->getValue('fids/cron_token');
    if ($esperado == '' || !hash_equals($esperado, $token)) {
      http_response_code(403);
      die(json_encode(['error' => 'forbidden']));
    }

    set_time_limit(300);
    $vuelosModel = new Core_Model_Vuelos();
    if (!$vuelosModel->lock()) {
      die(json_encode(['sync' => 'locked']));
    }
    try {
      $sync = $vuelosModel->sincronizar();
      $alertas = (new Core_Model_Alertas($this->_view))->procesar();
    } finally {
      $vuelosModel->unlock();
    }
    $res = ['sync' => $sync['status'], 'filas' => $sync['filas'], 'alertas' => $alertas];
    if ($sync['status'] != 'ok') {
      $res['error'] = $sync['error'];
    }
    die(json_encode($res));
  }
}
