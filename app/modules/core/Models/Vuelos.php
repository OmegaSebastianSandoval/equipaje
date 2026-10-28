<?php

/**
 * Modelo del modulo Core que sincroniza los vuelos de salida desde el XML de FIDS
 * y los consulta para asociarlos a las maletas.
 */
class Core_Model_Vuelos
{
  const LOCK = 'opain_cron';

  /**
   * Status del XML que ya no permiten asociar maletas: despego o cancelado.
   */
  const STATUS_CERRADOS = "'DEP','CAN'";

  protected $_conn;

  public function __construct()
  {
    $this->_conn = App::getDbConnection();
  }

  /**
   * Toma el lock de MySQL para que no corran dos sincronizaciones a la vez.
   * @return bool true si se obtuvo el lock
   */
  public function lock()
  {
    $res = $this->_conn->query("SELECT GET_LOCK('" . self::LOCK . "', 0) AS l")->fetchAsObject();
    return isset($res[0]) && $res[0]->l == 1;
  }

  public function unlock()
  {
    $this->_conn->query("SELECT RELEASE_LOCK('" . self::LOCK . "')");
  }

  /**
   * Descarga el XML, guarda los vuelos de salida (D/TU/S) con upsert y limpia los viejos.
   * Si algo falla no se borra nada y el error queda en vuelos_sync.
   * @return array ['status' => 'ok'|'error', 'filas' => int, 'error' => string]
   */
  public function sincronizar()
  {
    $syncModel = new Administracion_Model_DbTable_Vuelossync();
    try {
      $xml = $this->descargar();
      $filas = $this->guardar($xml);
      $syncModel->registrar(true, $filas);
      return ['status' => 'ok', 'filas' => $filas, 'error' => ''];
    } catch (Exception $e) {
      $syncModel->registrar(false, 0, $e->getMessage());
      return ['status' => 'error', 'filas' => 0, 'error' => $e->getMessage()];
    }
  }

  /**
   * Sincroniza solo si los datos tienen mas de $minutos (alternativa si el cron no esta corriendo).
   * @return void
   */
  public function sincronizarSiViejo($minutos = 3)
  {
    $sync = (new Administracion_Model_DbTable_Vuelossync())->getById(1);
    if ($sync && $sync->sync_ultimo_ok && strtotime($sync->sync_ultimo_ok) > time() - $minutos * 60) {
      return;
    }
    // Evita reintentar en cada request mientras el XML esta caido
    if ($sync && $sync->sync_ultimo_intento && strtotime($sync->sync_ultimo_intento) > time() - 60) {
      return;
    }
    if ($this->lock()) {
      try {
        $this->sincronizar();
      } finally {
        $this->unlock();
      }
    }
  }

  private function descargar()
  {
    $config = Config_Config::getInstance();
    $ch = curl_init($config->getValue('fids/url'));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 20);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
    curl_setopt($ch, CURLOPT_FAILONERROR, true);
    curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
    curl_setopt($ch, CURLOPT_USERPWD, $config->getValue('fids/user') . ':' . $config->getValue('fids/password'));
    // Opcional: archivo de certificados de confianza (fids/cainfo en config.php) si el servidor tiene la lista desactualizada
    $cainfo = @$config->getValue('fids/cainfo');
    if ($cainfo && is_readable($cainfo)) {
      curl_setopt($ch, CURLOPT_CAINFO, $cainfo);
    }
    $body = curl_exec($ch);
    $errno = curl_errno($ch);
    $error = $errno ? curl_error($ch) : '';
    // Si el certificado no es valido, reintenta sin verificarlo (el endpoint es el configurado en fids/url)
    if (in_array($errno, array(35, 51, 58, 60, 77, 83))) {
      curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
      curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
      $body = curl_exec($ch);
      $errno = curl_errno($ch);
      $error = $errno ? curl_error($ch) : '';
    }
    curl_close($ch);
    if ($error != '' || $body === false || $body === '') {
      throw new Exception('Error descargando el XML: ' . ($error ?: 'respuesta vacia'));
    }
    return $body;
  }

  /**
   * Parsea el XML y guarda las filas filtradas en una transaccion.
   * @param string $body contenido del XML
   * @return integer filas guardadas
   */
  public function guardar($body)
  {
    libxml_use_internal_errors(true);
    $xml = simplexml_load_string($body, 'SimpleXMLElement', LIBXML_NOCDATA);
    if ($xml === false || !isset($xml->row)) {
      throw new Exception('XML invalido');
    }

    $vuelos = [];
    $now = date('Y-m-d H:i:s');
    foreach ($xml->row as $row) {
      if ((string) $row['adi'] != 'D' || (string) $row->terminal['code'] != 'TU' || (string) $row->qualifier['code'] != 'S') {
        continue;
      }
      $schedule = $this->fecha((string) $row['schedule']);
      if (!$schedule) {
        continue;
      }
      $ciudades = [];
      $codigos = [];
      foreach ($row->{'city-es'} as $city) {
        $orden = (int) $city['order'];
        $ciudades[$orden] = trim((string) $city);
        $codigos[$orden] = (string) $city['code'];
      }
      ksort($ciudades);
      ksort($codigos);
      $vuelos[] = [
        'vuelo_xml_id' => (string) $row['id'],
        'vuelo_linecode' => strtoupper(trim((string) $row->linecode)),
        'vuelo_numero' => trim((string) $row->number),
        'vuelo_aerolinea_nombre' => trim((string) $row->line),
        'vuelo_schedule' => $schedule,
        'vuelo_actual' => $this->fecha((string) $row['actual']),
        'vuelo_tipo' => (string) $row->flighttype['code'] == 'I' ? 'I' : 'D',
        'vuelo_destino' => implode(' / ', $ciudades),
        'vuelo_destino_codigo' => implode('/', $codigos),
        'vuelo_puerta' => trim((string) $row->gate),
        'vuelo_status' => (string) $row->{'status-es'}['code'],
        'vuelo_status_texto' => trim((string) $row->{'status-es'}),
        'vuelo_actualizado' => $now,
      ];
    }
    if (count($vuelos) == 0) {
      throw new Exception('El XML no trajo vuelos de salida');
    }

    $vuelosModel = new Administracion_Model_DbTable_Vuelos();
    $db = $this->_conn->getConnection();
    $db->begin_transaction();
    try {
      foreach ($vuelos as $vuelo) {
        $vuelosModel->upsert($vuelo);
      }
      $vuelosModel->limpiarViejos(date('Y-m-d H:i:s', strtotime('-3 days')));
      $db->commit();
    } catch (Exception $e) {
      $db->rollback();
      throw $e;
    }
    return count($vuelos);
  }

  private function fecha($valor)
  {
    if ($valor == '') {
      return null;
    }
    $time = strtotime($valor);
    return $time ? date('Y-m-d H:i:s', $time) : null;
  }

  /**
   * Tipo de vuelo (D/I) por defecto segun el tipo de maleta de la sesion.
   * normal -> D, over 1 N -> D, over 1 I -> I, over 2 -> I, over 3 -> D
   * @return string
   */
  public static function tipoPorSesion()
  {
    $over = Session::getInstance()->get('over');
    $step = Session::getInstance()->get('step');
    if ($over == '2' || ($over == '1' && $step == 'I')) {
      return 'I';
    }
    return 'D';
  }

  /**
   * Vuelos disponibles para asociar a una maleta.
   * @param string $iata linecode de la aerolinea efectiva
   * @param string $rango 'hoy' o '24h'
   * @param string|null $tipo 'D', 'I' o null para todos
   * @param string $q texto para filtrar por numero de vuelo (acepta "AV8484" o "AV 8484")
   * @return array
   */
  public function disponibles($iata, $rango = 'hoy', $tipo = null, $q = '')
  {
    $db = $this->_conn->getConnection();
    $iata = strtoupper(preg_replace('/[^A-Za-z0-9]/', '', $iata));
    if ($iata == '') {
      return [];
    }
    $now = date('Y-m-d H:i:s');
    $hasta = $rango == '24h' ? date('Y-m-d H:i:s', strtotime('+24 hours')) : date('Y-m-d 23:59:59');
    $where = "vuelo_linecode = '$iata'
      AND (vuelo_status IS NULL OR vuelo_status NOT IN (" . self::STATUS_CERRADOS . "))
      AND (vuelo_schedule >= '$now' OR vuelo_actual >= '$now')
      AND COALESCE(vuelo_actual, vuelo_schedule) <= '$hasta'";
    if ($tipo == 'D' || $tipo == 'I') {
      $where .= " AND vuelo_tipo = '$tipo'";
    }
    $q = strtoupper(preg_replace('/[^A-Za-z0-9]/', '', $q));
    if ($q != '' && strpos($q, $iata) === 0) {
      $q = substr($q, strlen($iata));
    }
    if ($q != '') {
      $where .= " AND vuelo_numero LIKE '" . mysqli_real_escape_string($db, $q) . "%'";
    }
    return $this->_conn->query("SELECT * FROM vuelos WHERE $where ORDER BY COALESCE(vuelo_actual, vuelo_schedule) ASC")->fetchAsObject();
  }

  /**
   * Valida en el servidor que un vuelo se pueda asociar a una maleta de la aerolinea indicada.
   * No valida el filtro nacional/internacional porque existe "Ver todos".
   * @return object|false el vuelo si es valido
   */
  public function validar($vueloId, $iata)
  {
    $vueloId = (int) $vueloId;
    $iata = strtoupper(preg_replace('/[^A-Za-z0-9]/', '', $iata));
    if ($vueloId <= 0 || $iata == '') {
      return false;
    }
    $res = $this->_conn->query("SELECT * FROM vuelos WHERE vuelo_id = '$vueloId' AND vuelo_linecode = '$iata'
      AND (vuelo_status IS NULL OR vuelo_status NOT IN (" . self::STATUS_CERRADOS . "))")->fetchAsObject();
    return $res[0] ?? false;
  }

  /**
   * Vuelos por id (para pintar listados sin una consulta por fila).
   * @param array $ids
   * @return array vuelo_id => vuelo
   */
  public function porIds($ids)
  {
    $ids = array_filter(array_map('intval', $ids));
    if (count($ids) == 0) {
      return [];
    }
    $res = $this->_conn->query("SELECT * FROM vuelos WHERE vuelo_id IN (" . implode(',', array_unique($ids)) . ")")->fetchAsObject();
    $vuelos = [];
    foreach ($res as $vuelo) {
      $vuelos[$vuelo->vuelo_id] = $vuelo;
    }
    return $vuelos;
  }

  public static function numero($vuelo)
  {
    return $vuelo->vuelo_linecode . ' ' . $vuelo->vuelo_numero;
  }

  public static function salida($vuelo)
  {
    return $vuelo->vuelo_actual ?: $vuelo->vuelo_schedule;
  }

  /**
   * Datos de salida y urgencia de una maleta para las pantallas y el listado.
   * @param object $maleta fila de maletas
   * @param object|null $vuelo fila de vuelos (si la maleta tiene vuelo del XML)
   * @param integer $alertaMinutos info_pagina_alerta_minutos
   * @return array
   */
  public static function estadoSalida($maleta, $vuelo, $alertaMinutos)
  {
    $res = [
      'vuelo' => '—',
      'salida' => '',
      'salida_programada' => '',
      'tiempo_restante' => '—',
      'urgencia' => 'ninguna',
    ];
    if ($maleta->maleta_vuelo_manual == 1) {
      $res['vuelo'] = 'Manual: ' . $maleta->maleta_vuelo_numero;
      return $res;
    }
    if (!$maleta->maleta_vuelo_numero) {
      return $res;
    }
    $res['vuelo'] = $maleta->maleta_vuelo_numero;
    $salida = $vuelo ? self::salida($vuelo) : $maleta->maleta_vuelo_salida;
    if (!$salida) {
      return $res;
    }
    $res['salida'] = date('H:i', strtotime($salida));
    if ($vuelo && $vuelo->vuelo_actual && $vuelo->vuelo_actual != $vuelo->vuelo_schedule) {
      $res['salida_programada'] = date('H:i', strtotime($vuelo->vuelo_schedule));
    }
    if (date('Y-m-d', strtotime($salida)) != date('Y-m-d')) {
      $res['salida'] = date('d/m H:i', strtotime($salida));
    }
    if ($vuelo && $vuelo->vuelo_status == 'CAN') {
      $res['tiempo_restante'] = 'Cancelado';
      return $res;
    }
    $minutos = (int) floor((strtotime($salida) - time()) / 60);
    $despego = $vuelo && $vuelo->vuelo_status == 'DEP';
    if ($minutos < 0 || $despego) {
      $res['tiempo_restante'] = 'Salió';
      $res['urgencia'] = 'salio';
    } else {
      $res['tiempo_restante'] = self::formatoMinutos($minutos);
      if ($minutos < $alertaMinutos) {
        $res['urgencia'] = 'alta';
      } elseif ($minutos < $alertaMinutos * 2) {
        $res['urgencia'] = 'media';
      }
    }
    return $res;
  }

  public static function formatoMinutos($minutos)
  {
    if ($minutos === null || $minutos === '') {
      return '—';
    }
    $minutos = (int) $minutos;
    $signo = $minutos < 0 ? '-' : '';
    $minutos = abs($minutos);
    if ($minutos < 60) {
      return $signo . $minutos . ' min';
    }
    $h = floor($minutos / 60);
    $m = $minutos % 60;
    return $signo . $h . ' h' . ($m > 0 ? ' ' . $m . ' min' : '');
  }
}
