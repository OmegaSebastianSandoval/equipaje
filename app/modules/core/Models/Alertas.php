<?php

/**
 * Modelo del modulo Core que envia la alerta de salida proxima a la aerolinea
 * cuando un vuelo tiene maletas sin reclamar. Se envia una sola vez por vuelo y destino (normal/over).
 * Corre solo desde el cron (no depende de la sesion).
 */
class Core_Model_Alertas
{
  protected $_conn;
  protected $_view;

  public function __construct($view)
  {
    $this->_conn = App::getDbConnection();
    $this->_view = $view;
  }

  /**
   * @return integer cantidad de alertas enviadas
   */
  public function procesar()
  {
    $info = (new Administracion_Model_DbTable_Informacion())->getById(1);
    $minutos = (int) ($info->info_pagina_alerta_minutos ?? 60);
    if ($minutos <= 0) {
      $minutos = 60;
    }
    $now = date('Y-m-d H:i:s');
    $hasta = date('Y-m-d H:i:s', strtotime("+$minutos minutes"));
    $vuelos = $this->_conn->query("SELECT * FROM vuelos
      WHERE COALESCE(vuelo_actual, vuelo_schedule) BETWEEN '$now' AND '$hasta'
      AND (vuelo_status IS NULL OR vuelo_status NOT IN (" . Core_Model_Vuelos::STATUS_CERRADOS . "))")->fetchAsObject();

    $aerolineasModel = new Administracion_Model_DbTable_Aerolineas();
    $alertasModel = new Administracion_Model_DbTable_Alertasvuelo();
    $enviadas = 0;
    foreach ($vuelos as $vuelo) {
      $maletas = $this->_conn->query("SELECT * FROM maletas WHERE maleta_vuelo_id = '" . (int) $vuelo->vuelo_id . "' AND maleta_estado = '' ORDER BY maleta_fingreso, maleta_hingreso")->fetchAsObject();
      if (count($maletas) == 0) {
        continue;
      }
      $grupos = ['normal' => [], 'over' => []];
      foreach ($maletas as $maleta) {
        $grupos[$maleta->maleta_over_tipo ? 'over' : 'normal'][] = $maleta;
      }
      foreach ($grupos as $destino => $lista) {
        if (count($lista) == 0) {
          continue;
        }
        $codigos = implode(',', array_map(function ($m) {
          return $m->maleta_codigo;
        }, $lista));
        $alertaId = $alertasModel->reservar($vuelo->vuelo_id, $destino, $codigos);
        if (!$alertaId) {
          continue;
        }
        $resultado = $this->enviar($vuelo, $lista, $destino == 'over', $aerolineasModel);
        $alertasModel->editField($alertaId, 'alerta_resultado', mysqli_real_escape_string($this->_conn->getConnection(), substr($resultado, 0, 255)));
        $enviadas++;
      }
    }
    return $enviadas;
  }

  /**
   * Envia el correo (y el WhatsApp si existe plantilla) a cada aerolinea efectiva de las maletas.
   * @return string resumen del envio
   */
  private function enviar($vuelo, $maletas, $esOver, $aerolineasModel)
  {
    // Aerolinea efectiva: la de conexion si la maleta es de conexion
    $porAerolinea = [];
    foreach ($maletas as $maleta) {
      $codigo = $maleta->maleta_conexion == '1' ? $maleta->maleta_aerolinea_conexion : $maleta->maleta_aerolinea;
      $porAerolinea[$codigo][] = $maleta;
    }
    $resultados = [];
    foreach ($porAerolinea as $codigo => $lista) {
      $aerolinea = $aerolineasModel->getList("aerolinea_codigo = '" . $codigo . "'", "")[0] ?? null;
      if (!$aerolinea) {
        $resultados[] = $codigo . ': sin aerolinea';
        continue;
      }
      $mailModel = new Core_Model_Sendingemail($this->_view);
      $mail = $mailModel->notificarAlertaVuelo($aerolinea, $vuelo, $lista, $esOver);
      $resultados[] = $codigo . ': correo ' . ($mail == 1 ? 'ok' : 'error');

      $dorabot = new Core_Model_Dorabot();
      $telefonos = explode(",", $esOver ? $aerolinea->aerolinea_telefono_over : $aerolinea->aerolinea_telefono);
      $porTipo = [];
      foreach ($lista as $maleta) {
        $porTipo[$esOver ? $maleta->maleta_over_tipo : ''][] = $maleta->maleta_codigo;
      }
      foreach ($porTipo as $overTipo => $codigos) {
        foreach ($telefonos as $telefono) {
          $dorabot->notificar($telefono, 'alerta', $aerolinea->aerolinea_nombre, implode(', ', $codigos), $overTipo, [
            'vuelo' => Core_Model_Vuelos::numero($vuelo),
            'salida' => date('H:i', strtotime(Core_Model_Vuelos::salida($vuelo))),
          ]);
        }
      }
    }
    return implode('; ', $resultados);
  }
}
