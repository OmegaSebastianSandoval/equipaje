<?php 
/**
* clase que genera la insercion y edicion  de Log Maletas en la base de datos
*/
class Administracion_Model_DbTable_Logmaletas extends Db_Table
{
	/**
	 * [ nombre de la tabla actual]
	 * @var string
	 */
	protected $_name = 'log_maletas';

	/**
	 * [ identificador de la tabla actual en la base de datos]
	 * @var string
	 */
	protected $_id = 'log_id';

	/**
	 * insert recibe la informacion de un Log Maletas y la inserta en la base de datos
	 * @param  array Array array con la informacion con la cual se va a realizar la insercion en la base de datos
	 * @return integer      identificador del  registro que se inserto
	 */
	public function insert($data){
		$log_maleta = $data['log_maleta'];
		$log_tipo = $data['log_tipo'];
		$log_fecha = $data['log_fecha'];
		$log_usuario = $data['log_usuario'];
		$query = "INSERT INTO log_maletas( log_maleta, log_tipo, log_fecha, log_usuario) VALUES ( '$log_maleta', '$log_tipo', '$log_fecha', '$log_usuario')";
		$res = $this->_conn->query($query);
        return mysqli_insert_id($this->_conn->getConnection());
	}

	/**
	 * update Recibe la informacion de un Log Maletas  y actualiza la informacion en la base de datos
	 * @param  array Array Array con la informacion con la cual se va a realizar la actualizacion en la base de datos
	 * @param  integer    identificador al cual se le va a realizar la actualizacion
	 * @return void
	 */
	public function update($data,$id){
		
		$log_maleta = $data['log_maleta'];
		$log_tipo = $data['log_tipo'];
		$log_fecha = $data['log_fecha'];
		$log_usuario = $data['log_usuario'];
		$query = "UPDATE log_maletas SET  log_maleta = '$log_maleta', log_tipo = '$log_tipo', log_fecha = '$log_fecha', log_usuario = '$log_usuario' WHERE log_id = '".$id."'";
		$res = $this->_conn->query($query);
	}
}