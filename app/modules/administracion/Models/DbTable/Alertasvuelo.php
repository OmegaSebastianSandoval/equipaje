<?php
/**
 * clase que registra las alertas de salida proxima enviadas (una por vuelo y destino)
 */
class Administracion_Model_DbTable_Alertasvuelo extends Db_Table
{
	/**
	 * [ nombre de la tabla actual]
	 * @var string
	 */
	protected $_name = 'alertas_vuelo';

	/**
	 * [ identificador de la tabla actual en la base de datos]
	 * @var string
	 */
	protected $_id = 'alerta_id';

	/**
	 * reservar inserta la alerta antes de enviarla. Si ya existe (clave unica) devuelve 0 y no se debe enviar.
	 * @param  integer $vueloId identificador del vuelo
	 * @param  string $destino 'normal' u 'over'
	 * @param  string $maletas codigos de las maletas separados por coma
	 * @return integer identificador de la alerta o 0 si ya existia
	 */
	public function reservar($vueloId, $destino, $maletas)
	{
		$db = $this->_conn->getConnection();
		$vueloId = (int) $vueloId;
		$destino = $destino == 'over' ? 'over' : 'normal';
		$maletas = mysqli_real_escape_string($db, $maletas);
		$now = date('Y-m-d H:i:s');
		$query = "INSERT IGNORE INTO alertas_vuelo( alerta_vuelo_id, alerta_destino, alerta_fecha, alerta_maletas) VALUES ( '$vueloId', '$destino', '$now', '$maletas')";
		$this->_conn->query($query);
		return $db->affected_rows > 0 ? mysqli_insert_id($db) : 0;
	}
}
