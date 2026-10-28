<?php
/**
 * clase que genera la insercion y edicion  de Maletas en la base de datos
 */
class Administracion_Model_DbTable_Maletas extends Db_Table
{
  /**
   * [ nombre de la tabla actual]
   * @var string
   */
  protected $_name = 'maletas';

  /**
   * [ identificador de la tabla actual en la base de datos]
   * @var string
   */
  protected $_id = 'maleta_id';

  /**
   * insert recibe la informacion de un Maletas y la inserta en la base de datos
   * @param  array Array array con la informacion con la cual se va a realizar la insercion en la base de datos
   * @return integer      identificador del  registro que se inserto
   */
  public function insert($data)
  {
    $maleta_codigo = $data['maleta_codigo'];
    $maleta_aerolinea = $data['maleta_aerolinea'];
    $maleta_prop = $data['maleta_prop'];
    $maleta_fl = $data['maleta_fl'];
    $maleta_estado = $data['maleta_estado'];
    $maleta_fingreso = $data['maleta_fingreso'];
    $maleta_fsalida = $data['maleta_fsalida'];
    $maleta_hingreso = $data['maleta_hingreso'];
    $maleta_hsalida = $data['maleta_hsalida'];
    $maleta_conexion = $data['maleta_conexion'];
    $maleta_aerolinea_conexion = $data['maleta_aerolinea_conexion'];
    $maleta_over_tipo = Session::getInstance()->get('over') ? Session::getInstance()->get('over') : '';
    $maleta_over_lugar = Session::getInstance()->get('over') ? Session::getInstance()->get('step') : '';
    $vuelo = $this->vueloSql($data);
    $query = "INSERT INTO maletas( maleta_codigo, maleta_aerolinea, maleta_prop, maleta_fl, maleta_estado, maleta_fingreso, maleta_fsalida, maleta_hingreso, maleta_hsalida, maleta_conexion, maleta_aerolinea_conexion, maleta_over_tipo, maleta_over_lugar, maleta_vuelo_id, maleta_vuelo_numero, maleta_vuelo_salida, maleta_vuelo_destino, maleta_vuelo_manual, maleta_minutos_disponibles) VALUES ( '$maleta_codigo', '$maleta_aerolinea', '$maleta_prop', '$maleta_fl', '$maleta_estado', '$maleta_fingreso', '$maleta_fsalida', '$maleta_hingreso', '$maleta_hsalida', '$maleta_conexion', '$maleta_aerolinea_conexion', '$maleta_over_tipo', '$maleta_over_lugar', {$vuelo['maleta_vuelo_id']}, {$vuelo['maleta_vuelo_numero']}, {$vuelo['maleta_vuelo_salida']}, {$vuelo['maleta_vuelo_destino']}, {$vuelo['maleta_vuelo_manual']}, {$vuelo['maleta_minutos_disponibles']})";
    $res = $this->_conn->query($query);
    return mysqli_insert_id($this->_conn->getConnection());
  }

  /**
   * update Recibe la informacion de un Maletas  y actualiza la informacion en la base de datos
   * @param  array Array Array con la informacion con la cual se va a realizar la actualizacion en la base de datos
   * @param  integer    identificador al cual se le va a realizar la actualizacion
   * @return void
   */
  public function update($data, $id)
  {

    $maleta_codigo = $data['maleta_codigo'];
    $maleta_aerolinea = $data['maleta_aerolinea'];
    $maleta_prop = $data['maleta_prop'];
    $maleta_fl = $data['maleta_fl'];
    $maleta_estado = $data['maleta_estado'];
    $maleta_fingreso = $data['maleta_fingreso'];
    $maleta_fsalida = $data['maleta_fsalida'];
    $maleta_hingreso = $data['maleta_hingreso'];
    $maleta_hsalida = $data['maleta_hsalida'];
    $maleta_conexion = $data['maleta_conexion'];
    $maleta_aerolinea_conexion = $data['maleta_aerolinea_conexion'];
    $maleta_over_tipo = Session::getInstance()->get('over') ? Session::getInstance()->get('over') : '';
    $maleta_over_lugar = Session::getInstance()->get('over') ? Session::getInstance()->get('step') : '';
    $query = "UPDATE maletas SET  maleta_codigo = '$maleta_codigo', maleta_aerolinea = '$maleta_aerolinea', maleta_prop = '$maleta_prop', maleta_fl = '$maleta_fl', maleta_estado = '$maleta_estado', maleta_fingreso = '$maleta_fingreso', maleta_fsalida = '$maleta_fsalida', maleta_hingreso = '$maleta_hingreso', maleta_hsalida = '$maleta_hsalida', maleta_conexion = '$maleta_conexion', maleta_aerolinea_conexion = '$maleta_aerolinea_conexion', maleta_over_tipo = '$maleta_over_tipo', maleta_over_lugar = '$maleta_over_lugar'";
    // Los datos del vuelo solo se cambian si vienen (el formulario de edicion los muestra en solo lectura)
    if (array_key_exists('maleta_vuelo_numero', $data)) {
      foreach ($this->vueloSql($data) as $campo => $valor) {
        $query .= ", $campo = $valor";
      }
    }
    $query .= " WHERE maleta_id = '" . $id . "'";
    $res = $this->_conn->query($query);
  }

  /**
   * Valores SQL (ya escapados, con NULL) de las columnas del vuelo asociado a la maleta.
   * @param  array $data informacion de la maleta
   * @return array columna => valor SQL
   */
  private function vueloSql($data)
  {
    $db = $this->_conn->getConnection();
    $texto = function ($valor) use ($db) {
      return ($valor === null || $valor === '') ? 'NULL' : "'" . mysqli_real_escape_string($db, $valor) . "'";
    };
    $entero = function ($valor) {
      return ($valor === null || $valor === '') ? 'NULL' : "'" . (int) $valor . "'";
    };
    return [
      'maleta_vuelo_id' => $entero($data['maleta_vuelo_id'] ?? null),
      'maleta_vuelo_numero' => $texto($data['maleta_vuelo_numero'] ?? null),
      'maleta_vuelo_salida' => $texto($data['maleta_vuelo_salida'] ?? null),
      'maleta_vuelo_destino' => $texto($data['maleta_vuelo_destino'] ?? null),
      'maleta_vuelo_manual' => "'" . (int) ($data['maleta_vuelo_manual'] ?? 0) . "'",
      'maleta_minutos_disponibles' => $entero($data['maleta_minutos_disponibles'] ?? null),
    ];
  }

  public function getList_maletas_aerolineas($filters = '', $order = '')
  {
    $filter = '';
    if ($filters != '') {
      $filter = ' WHERE ' . $filters;
    }
    $orders = "";
    if ($order != '') {
      $orders = ' ORDER BY ' . $order;
    }
    $select = 'SELECT * FROM maletas LEFT JOIN aerolineas ON maletas.maleta_aerolinea = aerolineas.aerolinea_codigo ' . $filter . ' ' . $orders;
    $res = $this->_conn->query($select)->fetchAsObject();
    return $res;
  }
  public function getList_maletas_aerolineas_grafica($filters = '', $order = '')
  {
    $filter = " AND (maleta_over_tipo = '' OR maleta_over_tipo IS NULL)";
    if ($filters != '') {
      $filter .= ' AND ' . $filters;
    }
    $orders = "";
    if ($order != '') {
      $orders = ' ORDER BY ' . $order;
    }
    $select = "SELECT a.aerolinea_nombre, COUNT(*) as cantidad FROM  maletas m JOIN aerolineas a ON m.maleta_aerolinea = a.aerolinea_codigo WHERE 1 $filter GROUP BY a.aerolinea_nombre";
    $res = $this->_conn->query($select)->fetchAsObject();
    return $res;
  }
  public function getList_maletas_estado_grafica($filters = '', $order = '')
  {
    $filter = " WHERE (maleta_over_tipo = '' OR maleta_over_tipo IS NULL)";
    if ($filters != '') {
      $filter .= " AND $filters";
    }
    $select = "SELECT maleta_estado, COUNT(*) as cantidad FROM maletas $filter GROUP BY maleta_estado";
    $res = $this->_conn->query($select)->fetchAsObject();
    return $res;
  }
  public function getList_maletas_hora_grafica($filters = '', $order = '')
  {
    $filter = " WHERE (maleta_over_tipo = '' OR maleta_over_tipo IS NULL)";
    if ($filters != '') {
      $filter .= ' AND ' . $filters;
    }
    $orders = "";
    if ($order != '') {
      $orders = ' ORDER BY ' . $order;
    }
    $select = "SELECT SUBSTRING_INDEX(maleta_hingreso, ':', 1) AS hora, COUNT(*) AS cantidad FROM maletas $filter GROUP BY SUBSTRING_INDEX(maleta_hingreso, ':', 1)";
    $res = $this->_conn->query($select)->fetchAsObject();
    return $res;
  }

  public function getList_maletas_aerolineas_grafica_over($filters = '', $order = '')
  {
    $filter = " AND (maleta_over_tipo != '' AND maleta_over_tipo IS NOT NULL)";
    if ($filters != '') {
      $filter .= " AND " . $filters;
    }
    $select = "SELECT a.aerolinea_nombre, COUNT(*) as cantidad FROM  maletas m JOIN aerolineas a ON m.maleta_aerolinea = a.aerolinea_codigo WHERE 1 $filter GROUP BY a.aerolinea_nombre";
    $res = $this->_conn->query($select)->fetchAsObject();
    return $res;
  }

  public function getList_maletas_estado_grafica_over($filters = '', $order = '')
  {
    $filter = " WHERE (maleta_over_tipo != '' AND maleta_over_tipo IS NOT NULL)";
    if ($filters != '') {
      $filter .= " AND $filters";
    }
    $select = "SELECT maleta_estado, COUNT(*) as cantidad FROM maletas $filter GROUP BY maleta_estado";
    $res = $this->_conn->query($select)->fetchAsObject();
    return $res;
  }

  public function getListOverTipo($filters = '')
  {
    $filter = " WHERE (maleta_over_tipo != '' AND maleta_over_tipo IS NOT NULL)";
    if ($filters != '') {
      $filter .= " AND ($filters)";
    }
    $select = "SELECT maleta_over_tipo, COUNT(*) as cantidad FROM maletas $filter GROUP BY maleta_over_tipo";
    return $this->_conn->query($select)->fetchAsObject();
  }

  public function getListOverLugar($filters = '')
  {
    $filter = " WHERE (maleta_over_tipo = '1')";
    if ($filters != '') {
      $filter .= " AND ($filters)";
    }
    $select = "SELECT maleta_over_lugar, COUNT(*) as cantidad FROM maletas $filter GROUP BY maleta_over_lugar";
    return $this->_conn->query($select)->fetchAsObject();
  }

  public function getListOverHora($filters = '')
  {
    $filter = " WHERE (maleta_over_tipo != '' AND maleta_over_tipo IS NOT NULL)";
    if ($filters != '') {
      $filter .= " AND ($filters)";
    }
    $select = "SELECT SUBSTRING_INDEX(maleta_hingreso, ':', 1) AS hora, COUNT(*) AS cantidad FROM maletas $filter GROUP BY SUBSTRING_INDEX(maleta_hingreso, ':', 1) ORDER BY hora ASC";
    return $this->_conn->query($select)->fetchAsObject();
  }
}
