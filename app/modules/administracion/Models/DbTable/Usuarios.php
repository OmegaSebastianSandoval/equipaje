<?php 
/**
* clase que genera la insercion y edicion  de Usuarios en la base de datos
*/
class Administracion_Model_DbTable_Usuarios extends Db_Table
{
	/**
	 * [ nombre de la tabla actual]
	 * @var string
	 */
	protected $_name = 'usuarios';

	/**
	 * [ identificador de la tabla actual en la base de datos]
	 * @var string
	 */
	protected $_id = 'id';

	/**
	 * insert recibe la informacion de un Usuarios y la inserta en la base de datos
	 * @param  array Array array con la informacion con la cual se va a realizar la insercion en la base de datos
	 * @return integer      identificador del  registro que se inserto
	 */
	public function insert($data){
		$razon_social = $data['razon_social'];
		$identificacion = $data['identificacion'];
		$dv = $data['dv'];
		$email = $data['email'];
		$contacto_nombre = $data['contacto_nombre'];
		$contacto_email = $data['contacto_email'];
		$contacto_cargo = $data['contacto_cargo'];
		$contacto_nombre_2 = $data['contacto_nombre_2'];
		$contacto_email_2 = $data['contacto_email_2'];
		$contacto_cargo_2 = $data['contacto_cargo_2'];
		$query = "INSERT INTO usuarios( razon_social, identificacion, dv, email, contacto_nombre, contacto_email, contacto_cargo, contacto_nombre_2, contacto_email_2, contacto_cargo_2) VALUES ( '$razon_social', '$identificacion', '$dv', '$email', '$contacto_nombre', '$contacto_email', '$contacto_cargo', '$contacto_nombre_2', '$contacto_email_2', '$contacto_cargo_2')";
		$res = $this->_conn->query($query);
        return mysqli_insert_id($this->_conn->getConnection());
	}

	/**
	 * update Recibe la informacion de un Usuarios  y actualiza la informacion en la base de datos
	 * @param  array Array Array con la informacion con la cual se va a realizar la actualizacion en la base de datos
	 * @param  integer    identificador al cual se le va a realizar la actualizacion
	 * @return void
	 */
	public function update($data,$id){
		
		$razon_social = $data['razon_social'];
		$identificacion = $data['identificacion'];
		$dv = $data['dv'];
		$email = $data['email'];
		$contacto_nombre = $data['contacto_nombre'];
		$contacto_email = $data['contacto_email'];
		$contacto_cargo = $data['contacto_cargo'];
		$contacto_nombre_2 = $data['contacto_nombre_2'];
		$contacto_email_2 = $data['contacto_email_2'];
		$contacto_cargo_2 = $data['contacto_cargo_2'];
		$query = "UPDATE usuarios SET  razon_social = '$razon_social', identificacion = '$identificacion', dv = '$dv', email = '$email', contacto_nombre = '$contacto_nombre', contacto_email = '$contacto_email', contacto_cargo = '$contacto_cargo', contacto_nombre_2 = '$contacto_nombre_2', contacto_email_2 = '$contacto_email_2', contacto_cargo_2 = '$contacto_cargo_2' WHERE id = '".$id."'";
		$res = $this->_conn->query($query);
	}
}