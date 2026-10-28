<?php
/**
 * clase que maneja los dispositivos confiables (omiten el OTP) en la base de datos
 */
class Administracion_Model_DbTable_Dispositivos extends Db_Table
{
	/**
	 * [ nombre de la tabla actual]
	 * @var string
	 */
	protected $_name = 'dispositivos_confiables';

	/**
	 * [ identificador de la tabla actual en la base de datos]
	 * @var string
	 */
	protected $_id = 'dispositivo_id';

	/**
	 * insert recibe la informacion de un dispositivo y la inserta en la base de datos
	 * @param  array Array array con la informacion con la cual se va a realizar la insercion en la base de datos
	 * @return integer      identificador del  registro que se inserto
	 */
	public function insert($data)
	{
		$db = $this->_conn->getConnection();
		$dispositivo_usuario = (int) $data['dispositivo_usuario'];
		$dispositivo_token = mysqli_real_escape_string($db, $data['dispositivo_token']);
		$dispositivo_navegador = mysqli_real_escape_string($db, substr($data['dispositivo_navegador'] ?? '', 0, 255));
		$dispositivo_ip = mysqli_real_escape_string($db, $data['dispositivo_ip']);
		$dispositivo_fecha = date('Y-m-d H:i:s');
		$dispositivo_vence = mysqli_real_escape_string($db, $data['dispositivo_vence']);
		$query = "INSERT INTO dispositivos_confiables( dispositivo_usuario, dispositivo_token, dispositivo_navegador, dispositivo_ip, dispositivo_fecha, dispositivo_vence) VALUES ( '$dispositivo_usuario', '$dispositivo_token', '$dispositivo_navegador', '$dispositivo_ip', '$dispositivo_fecha', '$dispositivo_vence')";
		$this->_conn->query($query);
		return mysqli_insert_id($db);
	}

	/**
	 * getValido busca un dispositivo vigente del usuario con el hash del token
	 * @param  integer $userId identificador del usuario
	 * @param  string  $tokenHash hash sha256 (hex) del token de la cookie
	 * @return object|false
	 */
	public function getValido($userId, $tokenHash)
	{
		if (!ctype_xdigit($tokenHash) || strlen($tokenHash) != 64) {
			return false;
		}
		$userId = (int) $userId;
		$now = date('Y-m-d H:i:s');
		$res = $this->getList(
			"dispositivo_usuario = '$userId' AND dispositivo_token = '$tokenHash' AND dispositivo_vence > '$now'",
		);
		return $res[0] ?? false;
	}

	/**
	 * revocarUsuario elimina todos los dispositivos confiables de un usuario
	 * @param  integer $userId identificador del usuario
	 * @return void
	 */
	public function revocarUsuario($userId)
	{
		$userId = (int) $userId;
		$this->_conn->query("DELETE FROM dispositivos_confiables WHERE dispositivo_usuario = '$userId'");
	}

	/**
	 * limpiarVencidos elimina los dispositivos cuyo periodo de confianza ya termino
	 * @return void
	 */
	public function limpiarVencidos()
	{
		$now = date('Y-m-d H:i:s');
		$this->_conn->query("DELETE FROM dispositivos_confiables WHERE dispositivo_vence <= '$now'");
	}
}
