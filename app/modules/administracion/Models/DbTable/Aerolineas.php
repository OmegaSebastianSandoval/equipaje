<?php 
/**
* clase que genera la insercion y edicion  de Aerolineas en la base de datos
*/
class Administracion_Model_DbTable_Aerolineas extends Db_Table
{
	/**
	 * [ nombre de la tabla actual]
	 * @var string
	 */
	protected $_name = 'aerolineas';

	/**
	 * [ identificador de la tabla actual en la base de datos]
	 * @var string
	 */
	protected $_id = 'aerolinea_id';

	/**
	 * insert recibe la informacion de un Aerolineas y la inserta en la base de datos
	 * @param  array Array array con la informacion con la cual se va a realizar la insercion en la base de datos
	 * @return integer      identificador del  registro que se inserto
	 */
	public function insert($data){
		$aerolinea_estado = $data['aerolinea_estado'];
		$aerolinea_nombre = $data['aerolinea_nombre'];
		$aerolinea_correo = $data['aerolinea_correo'];
		$aerolinea_telefono = $data['aerolinea_telefono'];
		$aerolinea_contrasena = $data['aerolinea_contrasena'];
		$aerolinea_codigo = $data['aerolinea_codigo'];
		$aerolinea_iata = $data['aerolinea_iata'] != '' ? "'" . substr($data['aerolinea_iata'], 0, 3) . "'" : 'NULL';
		$aerolinea_imagen = $data['aerolinea_imagen'];
		$aerolinea_correo_over = $data['aerolinea_correo_over'];
		$aerolinea_telefono_over = $data['aerolinea_telefono_over'];

		$query = "INSERT INTO aerolineas( aerolinea_estado, aerolinea_nombre, aerolinea_correo, aerolinea_contrasena, aerolinea_codigo, aerolinea_iata, aerolinea_imagen, aerolinea_telefono, aerolinea_correo_over, aerolinea_telefono_over) VALUES ( '$aerolinea_estado', '$aerolinea_nombre', '$aerolinea_correo', '$aerolinea_contrasena', '$aerolinea_codigo', $aerolinea_iata, '$aerolinea_imagen', '$aerolinea_telefono', '$aerolinea_correo_over', '$aerolinea_telefono_over')";
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
		
		$aerolinea_estado = $data['aerolinea_estado'];
		$aerolinea_nombre = $data['aerolinea_nombre'];
		$aerolinea_correo = $data['aerolinea_correo'];
		$aerolinea_telefono = $data['aerolinea_telefono'];
		$aerolinea_contrasena = $data['aerolinea_contrasena'];
		$aerolinea_codigo = $data['aerolinea_codigo'];
		$aerolinea_iata = $data['aerolinea_iata'] != '' ? "'" . substr($data['aerolinea_iata'], 0, 3) . "'" : 'NULL';
		$aerolinea_imagen = $data['aerolinea_imagen'];
		$aerolinea_correo_over = $data['aerolinea_correo_over'];
		$aerolinea_telefono_over = $data['aerolinea_telefono_over'];
		$query = "UPDATE aerolineas SET  aerolinea_estado = '$aerolinea_estado', aerolinea_nombre = '$aerolinea_nombre', aerolinea_correo = '$aerolinea_correo', aerolinea_contrasena = '$aerolinea_contrasena', aerolinea_codigo = '$aerolinea_codigo', aerolinea_iata = $aerolinea_iata, aerolinea_imagen = '$aerolinea_imagen', aerolinea_telefono = '$aerolinea_telefono', aerolinea_correo_over = '$aerolinea_correo_over', aerolinea_telefono_over = '$aerolinea_telefono_over' WHERE aerolinea_id = '".$id."'";
		$res = $this->_conn->query($query);
	}
}
