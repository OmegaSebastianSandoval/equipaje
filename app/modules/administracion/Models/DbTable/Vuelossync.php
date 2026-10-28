<?php
/**
 * clase que guarda el estado de la sincronizacion del XML de vuelos (una sola fila)
 */
class Administracion_Model_DbTable_Vuelossync extends Db_Table
{
	/**
	 * [ nombre de la tabla actual]
	 * @var string
	 */
	protected $_name = 'vuelos_sync';

	/**
	 * [ identificador de la tabla actual en la base de datos]
	 * @var string
	 */
	protected $_id = 'sync_id';

	/**
	 * registrar guarda el resultado de un intento de sincronizacion
	 * @param  bool $ok si la sincronizacion termino bien
	 * @param  integer $filas filas guardadas
	 * @param  string $error mensaje de error si fallo
	 * @return void
	 */
	public function registrar($ok, $filas = 0, $error = '')
	{
		$db = $this->_conn->getConnection();
		$now = date('Y-m-d H:i:s');
		$error = mysqli_real_escape_string($db, substr($error, 0, 2000));
		$filas = (int) $filas;
		if ($ok) {
			$query = "UPDATE vuelos_sync SET sync_ultimo_intento = '$now', sync_ultimo_ok = '$now', sync_filas = '$filas', sync_error = NULL WHERE sync_id = 1";
		} else {
			$query = "UPDATE vuelos_sync SET sync_ultimo_intento = '$now', sync_error = '$error' WHERE sync_id = 1";
		}
		$this->_conn->query($query);
	}
}
