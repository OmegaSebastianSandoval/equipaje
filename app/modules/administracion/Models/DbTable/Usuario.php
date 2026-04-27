<?php
/**
 * clase que genera la insercion y edicion  de Usuarios en la base de datos
 */
class Administracion_Model_DbTable_Usuario extends Db_Table
{
	/**
	 * [ nombre de la tabla actual]
	 * @var string
	 */
	protected $_name = 'user';

	/**
	 * [ identificador de la tabla actual en la base de datos]
	 * @var string
	 */
	protected $_id = 'user_uid';

	/**
	 * insert recibe la informacion de un Usuario y la inserta en la base de datos
	 * @param  array Array array con la informacion con la cual se va a realizar la insercion en la base de datos
	 * @return integer      identificador del  registro que se inserto
	 */
	public function insert($data)
	{
		$user_state = $data['user_state'];
		$user_date = $data['user_date'];
		$user_names = $data['user_names'];
		$user_cedula = $data['user_cedula'];
		$user_email = $data['user_email'];
		$user_telefono = $data['user_telefono'];
		$user_level = $data['user_level'];
		$user_user = $data['user_user'];
		$user_password = password_hash($data['user_password'], PASSWORD_DEFAULT);
		$user_delete = $data['user_delete'];
		$user_current_user = $data['user_current_user'];
		$user_code = $data['user_code'];
		$user_empresa = $data['user_empresa'];
		$user_aerolinea = $data['user_aerolinea'];
		$uid = strtoupper(dechex(mt_rand(1000, 9999)) . str_replace('.', '', uniqid('', true)));

		$user_uid = $uid;
		$user_puedeover = $data['user_puedeover'];
		$query = "INSERT INTO user(user_state, user_date, user_names, user_cedula, user_email, user_telefono, user_level, user_user, user_password, user_delete, user_current_user, user_code, user_empresa, user_aerolinea, user_uid, user_puedeover) VALUES ( '$user_state', '$user_date', '$user_names', '$user_cedula', '$user_email', '$user_telefono', '$user_level', '$user_user', '$user_password', '$user_delete', '$user_current_user', '$user_code', '$user_empresa', '$user_aerolinea', '$user_uid', '$user_puedeover')";
		$res = $this->_conn->query($query);
		return mysqli_insert_id($this->_conn->getConnection());
	}

	/**
	 * update Recibe la informacion de un Usuario  y actualiza la informacion en la base de datos
	 * @param  array Array Array con la informacion con la cual se va a realizar la actualizacion en la base de datos
	 * @param  integer    identificador al cual se le va a realizar la actualizacion
	 * @return void
	 */
	public function update($data, $id)
	{

		$user_state = $data['user_state'];
		$user_date = $data['user_date'];
		$user_names = $data['user_names'];
		$user_cedula = $data['user_cedula'];
		$user_email = $data['user_email'];
		$user_telefono = $data['user_telefono'];
		$user_level = $data['user_level'];
		$user_user = $data['user_user'];
		$changepasword = '';
		if ($data['user_password'] != '') {
			$user_password = password_hash($data['user_password'], PASSWORD_DEFAULT);
			$changepasword = " , user_password = '$user_password'";
		}
		$user_delete = $data['user_delete'];
		$user_current_user = $data['user_current_user'];
		$user_code = $data['user_code'];
		$user_empresa = $data['user_empresa'];
		$user_aerolinea = $data['user_aerolinea'];
		$user_puedeover = $data['user_puedeover'];
		$query = "UPDATE user SET  user_state = '$user_state', user_names = '$user_names', user_cedula = '$user_cedula', user_email = '$user_email', user_telefono = '$user_telefono', user_level = '$user_level', user_user = '$user_user', user_delete = '$user_delete', user_current_user = '$user_current_user', user_code = '$user_code', user_empresa='$user_empresa' $changepasword , user_aerolinea = '$user_aerolinea', user_puedeover = '$user_puedeover' WHERE user_uid = '" . $id . "'";
		$res = $this->_conn->query($query);
	}
		public function editFieldById($id, $field, $value)
	{
		$query = ' UPDATE ' . $this->_name . ' SET ' . $field . ' = "' . $value . '" WHERE user_id = "' . $id . '"';
		$res = $this->_conn->query($query);
	}
}