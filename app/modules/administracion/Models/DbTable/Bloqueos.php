<?php 
/**
* clase que genera la insercion y edicion  de bloqueos en la base de datos
*/
class Administracion_Model_DbTable_Bloqueos extends Db_Table
{
	/**
	 * [ nombre de la tabla actual]
	 * @var string
	 */
	protected $_name = 'bloqueos';

	/**
	 * [ identificador de la tabla actual en la base de datos]
	 * @var string
	 */
	protected $_id = 'bloqueo_id';

	/**
	 * insert recibe la informacion de un bloqueos y la inserta en la base de datos
	 * @param  array Array array con la informacion con la cual se va a realizar la insercion en la base de datos
	 * @return integer      identificador del  registro que se inserto
	 */
	public function insert($data){
		$bloqueo_usuario = $data['bloqueo_usuario'];
		$bloqueo_fechaintento = date('Y-m-d H:i:s'); 

		$bloqueo_intentosfallidos = $data['bloqueo_intentosfallidos'];
		$bloqueo_ip = $data['bloqueo_ip'];
		$query = "INSERT INTO bloqueos( bloqueo_usuario, bloqueo_fechaintento, bloqueo_intentosfallidos, bloqueo_ip) VALUES ( '$bloqueo_usuario', '$bloqueo_fechaintento', '$bloqueo_intentosfallidos', '$bloqueo_ip')";
		$res = $this->_conn->query($query);
        return mysqli_insert_id($this->_conn->getConnection());
	}

	/**
	 * update Recibe la informacion de un bloqueos  y actualiza la informacion en la base de datos
	 * @param  array Array Array con la informacion con la cual se va a realizar la actualizacion en la base de datos
	 * @param  integer    identificador al cual se le va a realizar la actualizacion
	 * @return void
	 */
	public function update($data,$id){
		
		$bloqueo_usuario = $data['bloqueo_usuario'];
		$bloqueo_fechaintento = $data['bloqueo_fechaintento'];
		$bloqueo_intentosfallidos = $data['bloqueo_intentosfallidos'];
		$bloqueo_ip = $data['bloqueo_ip'];
		$query = "UPDATE bloqueos SET  bloqueo_usuario = '$bloqueo_usuario', bloqueo_fechaintento = '$bloqueo_fechaintento', bloqueo_intentosfallidos = '$bloqueo_intentosfallidos', bloqueo_ip = '$bloqueo_ip' WHERE bloqueo_id = '".$id."'";
		$res = $this->_conn->query($query);
	}
}