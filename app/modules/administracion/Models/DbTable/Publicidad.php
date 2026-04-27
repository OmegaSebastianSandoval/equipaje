<?php 
/**
* clase que genera la insercion y edicion  de Administrar Banners en la base de datos
*/
class Administracion_Model_DbTable_Publicidad extends Db_Table
{
	/**
	 * [ nombre de la tabla actual]
	 * @var string
	 */
	protected $_name = 'publicidad';

	/**
	 * [ identificador de la tabla actual en la base de datos]
	 * @var string
	 */
	protected $_id = 'publicidad_id';

	/**
	 * insert recibe la informacion de un Banner y la inserta en la base de datos
	 * @param  array Array array con la informacion con la cual se va a realizar la insercion en la base de datos
	 * @return integer      identificador del  registro que se inserto
	 */
	public function insert($data){
		$publicidad_seccion = $data['publicidad_seccion'];
		$publicidad_nombre = $data['publicidad_nombre'];
		$publicidad_fecha = $data['publicidad_fecha'];
		$publicidad_imagen = $data['publicidad_imagen'];
		$publicidad_imagenresponsive = $data['publicidad_imagenresponsive'];
		$publicidad_video = $data['publicidad_video'];
		$publicidad_color_fondo = $data['publicidad_color_fondo'];
		$publicidad_posicion = $data['publicidad_posicion'];
		$publicidad_descripcion = $data['publicidad_descripcion'];
		$publicidad_estado = $data['publicidad_estado'];
		$publicidad_click = $data['publicidad_click'];
		$publicidad_enlace = $data['publicidad_enlace'];
		$publicidad_tipo_enlace = $data['publicidad_tipo_enlace'];
		$publicidad_texto_enlace = $data['publicidad_texto_enlace'];
		$query = "INSERT INTO publicidad( publicidad_seccion, publicidad_nombre, publicidad_fecha, publicidad_imagen, publicidad_imagenresponsive, publicidad_video, publicidad_color_fondo, publicidad_posicion, publicidad_descripcion, publicidad_estado, publicidad_click, publicidad_enlace, publicidad_tipo_enlace, publicidad_texto_enlace) VALUES ( '$publicidad_seccion', '$publicidad_nombre', '$publicidad_fecha', '$publicidad_imagen', '$publicidad_imagenresponsive', '$publicidad_video', '$publicidad_color_fondo', '$publicidad_posicion', '$publicidad_descripcion', '$publicidad_estado', '$publicidad_click', '$publicidad_enlace', '$publicidad_tipo_enlace', '$publicidad_texto_enlace')";
		$res = $this->_conn->query($query);
        return mysqli_insert_id($this->_conn->getConnection());
	}

	/**
	 * update Recibe la informacion de un Banner  y actualiza la informacion en la base de datos
	 * @param  array Array Array con la informacion con la cual se va a realizar la actualizacion en la base de datos
	 * @param  integer    identificador al cual se le va a realizar la actualizacion
	 * @return void
	 */
	public function update($data,$id){
		
		$publicidad_seccion = $data['publicidad_seccion'];
		$publicidad_nombre = $data['publicidad_nombre'];
		$publicidad_fecha = $data['publicidad_fecha'];
		$publicidad_imagen = $data['publicidad_imagen'];
		$publicidad_imagenresponsive = $data['publicidad_imagenresponsive'];
		$publicidad_video = $data['publicidad_video'];
		$publicidad_color_fondo = $data['publicidad_color_fondo'];
		$publicidad_posicion = $data['publicidad_posicion'];
		$publicidad_descripcion = $data['publicidad_descripcion'];
		$publicidad_estado = $data['publicidad_estado'];
		$publicidad_click = $data['publicidad_click'];
		$publicidad_enlace = $data['publicidad_enlace'];
		$publicidad_tipo_enlace = $data['publicidad_tipo_enlace'];
		$publicidad_texto_enlace = $data['publicidad_texto_enlace'];
		echo $query = "UPDATE publicidad SET  publicidad_seccion = '$publicidad_seccion', publicidad_nombre = '$publicidad_nombre', publicidad_fecha = '$publicidad_fecha', publicidad_imagen = '$publicidad_imagen', publicidad_imagenresponsive = '$publicidad_imagenresponsive', publicidad_video = '$publicidad_video', publicidad_color_fondo = '$publicidad_color_fondo', publicidad_posicion = '$publicidad_posicion', publicidad_descripcion = '$publicidad_descripcion', publicidad_estado = '$publicidad_estado', publicidad_click = '$publicidad_click', publicidad_enlace = '$publicidad_enlace', publicidad_tipo_enlace = '$publicidad_tipo_enlace', publicidad_texto_enlace = '$publicidad_texto_enlace' WHERE publicidad_id = '".$id."'";
		$res = $this->_conn->query($query);
	}
}