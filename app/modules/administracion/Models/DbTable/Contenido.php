<?php 
/**
* clase que genera la insercion y edicion  de Contenidos en la base de datos
*/
class Administracion_Model_DbTable_Contenido extends Db_Table
{
	/**
	 * [ nombre de la tabla actual]
	 * @var string
	 */
	protected $_name = 'contenido';

	/**
	 * [ identificador de la tabla actual en la base de datos]
	 * @var string
	 */
	protected $_id = 'contenido_id';

	/**
	 * insert recibe la informacion de un Contenido y la inserta en la base de datos
	 * @param  array Array array con la informacion con la cual se va a realizar la insercion en la base de datos
	 * @return integer      identificador del  registro que se inserto
	 */
	public function insert($data){
		$contenido_seccion = $data['contenido_seccion'];
		$contenido_tipo = $data['contenido_tipo'];
		$contenido_padre = $data['contenido_padre'];
		$contenido_columna = $data['contenido_columna'];
		$contenido_columna_espacios = $data['contenido_columna_espacios'];
		$contenido_columna_alineacion = $data['contenido_columna_alineacion'];
		$contenido_disenio = $data['contenido_disenio'];
		$contenido_borde = $data['contenido_borde'];
		$contenido_estado = $data['contenido_estado'];
		$contenido_fecha = $data['contenido_fecha'];
		$contenido_titulo = $data['contenido_titulo'];
		$contenido_titulo_ver = $data['contenido_titulo_ver'];
		$contenido_imagen = $data['contenido_imagen'];
		$contenido_archivo = $data['contenido_archivo'];
		$contenido_fondo_imagen = $data['contenido_fondo_imagen'];
		$contenido_fondo_imagen_tipo = $data['contenido_fondo_imagen_tipo'];
		$contenido_fondo_color = $data['contenido_fondo_color'];
		$contenido_introduccion = $data['contenido_introduccion'];
		$contenido_descripcion = $data['contenido_descripcion'];
		$contenido_enlace = $data['contenido_enlace'];
		$contenido_enlace_abrir = $data['contenido_enlace_abrir'];
		$contenido_vermas = $data['contenido_vermas'];
		$query = "INSERT INTO contenido( contenido_seccion, contenido_tipo, contenido_padre, contenido_columna, contenido_columna_espacios, contenido_disenio, contenido_borde, contenido_estado, contenido_fecha, contenido_titulo, contenido_titulo_ver, contenido_imagen, contenido_archivo, contenido_fondo_imagen, contenido_fondo_imagen_tipo, contenido_fondo_color, contenido_introduccion, contenido_descripcion, contenido_enlace, contenido_vermas,contenido_columna_alineacion,contenido_enlace_abrir) VALUES ( '$contenido_seccion', '$contenido_tipo', '$contenido_padre', '$contenido_columna', '$contenido_columna_espacios', '$contenido_disenio', '$contenido_borde', '$contenido_estado', '$contenido_fecha', '$contenido_titulo', '$contenido_titulo_ver', '$contenido_imagen', '$contenido_archivo', '$contenido_fondo_imagen', '$contenido_fondo_imagen_tipo', '$contenido_fondo_color', '$contenido_introduccion', '$contenido_descripcion', '$contenido_enlace', '$contenido_vermas','$contenido_columna_alineacion','$contenido_enlace_abrir')";
		$res = $this->_conn->query($query);
        return mysqli_insert_id($this->_conn->getConnection());
	}

	/**
	 * update Recibe la informacion de un Contenido  y actualiza la informacion en la base de datos
	 * @param  array Array Array con la informacion con la cual se va a realizar la actualizacion en la base de datos
	 * @param  integer    identificador al cual se le va a realizar la actualizacion
	 * @return void
	 */
	public function update($data,$id){
		
		$contenido_seccion = $data['contenido_seccion'];
		$contenido_tipo = $data['contenido_tipo'];
		$contenido_padre = $data['contenido_padre'];
		$contenido_columna = $data['contenido_columna'];
		$contenido_columna_espacios = $data['contenido_columna_espacios'];
		$contenido_columna_alineacion = $data['contenido_columna_alineacion'];
		$contenido_disenio = $data['contenido_disenio'];
		$contenido_borde = $data['contenido_borde'];
		$contenido_estado = $data['contenido_estado'];
		$contenido_fecha = $data['contenido_fecha'];
		$contenido_titulo = $data['contenido_titulo'];
		$contenido_titulo_ver = $data['contenido_titulo_ver'];
		$contenido_imagen = $data['contenido_imagen'];
		$contenido_archivo = $data['contenido_archivo'];
		$contenido_fondo_imagen = $data['contenido_fondo_imagen'];
		$contenido_fondo_imagen_tipo = $data['contenido_fondo_imagen_tipo'];
		$contenido_fondo_color = $data['contenido_fondo_color'];
		$contenido_introduccion = $data['contenido_introduccion'];
		$contenido_descripcion = $data['contenido_descripcion'];
		$contenido_enlace = $data['contenido_enlace'];
		$contenido_enlace_abrir = $data['contenido_enlace_abrir'];
		$contenido_vermas = $data['contenido_vermas'];
		$query = "UPDATE contenido SET  contenido_seccion = '$contenido_seccion', contenido_tipo = '$contenido_tipo', contenido_padre = '$contenido_padre', contenido_columna = '$contenido_columna', contenido_columna_espacios = '$contenido_columna_espacios', contenido_disenio = '$contenido_disenio', contenido_borde = '$contenido_borde', contenido_estado = '$contenido_estado', contenido_fecha = '$contenido_fecha', contenido_titulo = '$contenido_titulo', contenido_titulo_ver = '$contenido_titulo_ver', contenido_imagen = '$contenido_imagen', contenido_archivo = '$contenido_archivo', contenido_fondo_imagen = '$contenido_fondo_imagen', contenido_fondo_imagen_tipo = '$contenido_fondo_imagen_tipo', contenido_fondo_color = '$contenido_fondo_color', contenido_introduccion = '$contenido_introduccion', contenido_descripcion = '$contenido_descripcion', contenido_enlace = '$contenido_enlace', contenido_vermas = '$contenido_vermas', contenido_columna_alineacion = '$contenido_columna_alineacion',contenido_enlace_abrir = '$contenido_enlace_abrir' WHERE contenido_id = '".$id."'";
		$res = $this->_conn->query($query);
	}
}