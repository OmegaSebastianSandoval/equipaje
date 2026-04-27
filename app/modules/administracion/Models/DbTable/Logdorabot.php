<?php 
/**
* clase que genera la insercion y edicion  de Aerolineas en la base de datos
*/
class Administracion_Model_DbTable_Logdorabot extends Db_Table
{
	/**
	 * [ nombre de la tabla actual]
	 * @var string
	 */
	protected $_name = 'log_dorabot';

	/**
	 * [ identificador de la tabla actual en la base de datos]
	 * @var string
	 */
	protected $_id = 'id';

	/**
	 * insert recibe la informacion de un Aerolineas y la inserta en la base de datos
	 * @param  array Array array con la informacion con la cual se va a realizar la insercion en la base de datos
	 * @return integer      identificador del  registro que se inserto
	 */
	public function insert($data){
		$date = $data['date'];
		$log = $data['log'];
		$code = $data['code'];
		$query = "INSERT INTO log_dorabot( date, log, code ) VALUES ( '$date', '$log', '$code' )";
		$res = $this->_conn->query($query);
        return mysqli_insert_id($this->_conn->getConnection());
	}

	/**
	 * update Recibe la informacion de un Aerolineas  y actualiza la informacion en la base de datos
	 * @param  array Array Array con la informacion con la cual se va a realizar la actualizacion en la base de datos
	 * @param  integer    identificador al cual se le va a realizar la actualizacion
	 * @return void
	 */
	public function update($data,$id){
		
		$$date = $data['date'];
		$log = $data['log'];
		$code = $data['code'];
		$query = "UPDATE log_dorabot SET date = '$date', log = '$log', code = '$code' WHERE aerolinea_id = '".$id."'";
		$res = $this->_conn->query($query);
	}
}
