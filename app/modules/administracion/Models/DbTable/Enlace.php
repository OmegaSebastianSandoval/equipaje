<?php 
/**
* clase que genera la insercion y edicion  de enlace en la base de datos
*/
class Administracion_Model_DbTable_Enlace extends Db_Table
{
	/**
	 * [ nombre de la tabla actual]
	 * @var string
	 */
	protected $_name = 'enlaces';

	/**
	 * [ identificador de la tabla actual en la base de datos]
	 * @var string
	 */
	protected $_id = 'enlaces_id';

	/**
	 * insert recibe la informacion de un enlace y la inserta en la base de datos
	 * @param  array Array array con la informacion con la cual se va a realizar la insercion en la base de datos
	 * @return integer      identificador del  registro que se inserto
	 */
	public function insert($data){
		$enlaces_titulo = $data['enlaces_titulo'];
		$enlaces_link = $data['enlaces_link'];
		$query = "INSERT INTO enlaces( enlaces_titulo, enlaces_link) VALUES ( '$enlaces_titulo', '$enlaces_link')";
		$res = $this->_conn->query($query);
        return mysqli_insert_id($this->_conn->getConnection());
	}

	/**
	 * update Recibe la informacion de un enlace  y actualiza la informacion en la base de datos
	 * @param  array Array Array con la informacion con la cual se va a realizar la actualizacion en la base de datos
	 * @param  integer    identificador al cual se le va a realizar la actualizacion
	 * @return void
	 */
	public function update($data,$id){
		
		$enlaces_titulo = $data['enlaces_titulo'];
		$enlaces_link = $data['enlaces_link'];
		$query = "UPDATE enlaces SET  enlaces_titulo = '$enlaces_titulo', enlaces_link = '$enlaces_link' WHERE enlaces_id = '".$id."'";
		$res = $this->_conn->query($query);
	}
}