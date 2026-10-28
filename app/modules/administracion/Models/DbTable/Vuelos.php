<?php
/**
 * clase que maneja los vuelos de salida que se sincronizan desde el XML de FIDS
 */
class Administracion_Model_DbTable_Vuelos extends Db_Table
{
	/**
	 * [ nombre de la tabla actual]
	 * @var string
	 */
	protected $_name = 'vuelos';

	/**
	 * [ identificador de la tabla actual en la base de datos]
	 * @var string
	 */
	protected $_id = 'vuelo_id';

	/**
	 * upsert inserta o actualiza un vuelo por su clave unica (xml_id, linecode, numero) sin cambiar su vuelo_id
	 * @param  array $data informacion del vuelo
	 * @return void
	 */
	public function upsert($data)
	{
		$db = $this->_conn->getConnection();
		$v = array();
		foreach ($data as $key => $value) {
			$v[$key] = $value === null ? 'NULL' : "'" . mysqli_real_escape_string($db, $value) . "'";
		}
		$query = "INSERT INTO vuelos( vuelo_xml_id, vuelo_linecode, vuelo_numero, vuelo_aerolinea_nombre, vuelo_schedule, vuelo_actual, vuelo_tipo, vuelo_destino, vuelo_destino_codigo, vuelo_puerta, vuelo_status, vuelo_status_texto, vuelo_actualizado) VALUES ( {$v['vuelo_xml_id']}, {$v['vuelo_linecode']}, {$v['vuelo_numero']}, {$v['vuelo_aerolinea_nombre']}, {$v['vuelo_schedule']}, {$v['vuelo_actual']}, {$v['vuelo_tipo']}, {$v['vuelo_destino']}, {$v['vuelo_destino_codigo']}, {$v['vuelo_puerta']}, {$v['vuelo_status']}, {$v['vuelo_status_texto']}, {$v['vuelo_actualizado']})
			ON DUPLICATE KEY UPDATE vuelo_aerolinea_nombre = VALUES(vuelo_aerolinea_nombre), vuelo_schedule = VALUES(vuelo_schedule), vuelo_actual = VALUES(vuelo_actual), vuelo_tipo = VALUES(vuelo_tipo), vuelo_destino = VALUES(vuelo_destino), vuelo_destino_codigo = VALUES(vuelo_destino_codigo), vuelo_puerta = VALUES(vuelo_puerta), vuelo_status = VALUES(vuelo_status), vuelo_status_texto = VALUES(vuelo_status_texto), vuelo_actualizado = VALUES(vuelo_actualizado)";
		$this->_conn->query($query);
	}

	/**
	 * limpiarViejos elimina los vuelos programados antes de la fecha indicada
	 * @param  string $limite fecha Y-m-d H:i:s
	 * @return void
	 */
	public function limpiarViejos($limite)
	{
		$this->_conn->query("DELETE FROM vuelos WHERE vuelo_schedule < '$limite'");
	}
}
