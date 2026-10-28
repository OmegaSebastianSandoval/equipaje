<?php
/**
 * clase que genera la insercion y edicion  de infopage en la base de datos
 */
class Administracion_Model_DbTable_Informacion extends Db_Table
{
	/**
	 * [ nombre de la tabla actual]
	 * @var string
	 */
	protected $_name = 'info_pagina';

	/**
	 * [ identificador de la tabla actual en la base de datos]
	 * @var string
	 */
	protected $_id = 'info_pagina_id';

	/**
	 * insert recibe la informacion de un infopage y la inserta en la base de datos
	 * @param  array Array array con la informacion con la cual se va a realizar la insercion en la base de datos
	 * @return integer      identificador del  registro que se inserto
	 */
	public function insert($data)
	{
		$info_pagina_facebook = $data['info_pagina_facebook'];
		$info_pagina_instagram = $data['info_pagina_instagram'];
		$info_pagina_twitter = $data['info_pagina_twitter'];
		$info_pagina_pinterest = $data['info_pagina_pinterest'];
		$info_pagina_youtube = $data['info_pagina_youtube'];
		$info_pagina_flickr = $data['info_pagina_flickr'];
		$info_pagina_linkedin = $data['info_pagina_linkedin'];
		$info_pagina_google = $data['info_pagina_google'];
		$info_pagina_telefono = $data['info_pagina_telefono'];
		$info_pagina_whatsapp = $data['info_pagina_whatsapp'];
		$info_pagina_correos_contacto = $data['info_pagina_correos_contacto'];
		$info_pagina_direccion_contacto = $data['info_pagina_direccion_contacto'];
		$info_pagina_informacion_contacto = $data['info_pagina_informacion_contacto'];
		$info_pagina_informacion_contacto_footer = $data['info_pagina_informacion_contacto_footer'];
		$info_pagina_latitud = $data['info_pagina_latitud'];
		$info_pagina_longitud = $data['info_pagina_longitud'];
		$info_pagina_zoom = $data['info_pagina_zoom'];
		$info_pagina_descripcion = $data['info_pagina_descripcion'];
		$info_pagina_tags = $data['info_pagina_tags'];
		$info_pagina_robot = $data['info_pagina_robot'];
		$info_pagina_sitemap = $data['info_pagina_sitemap'];
		$info_pagina_scripts = $data['info_pagina_scripts'];
		$info_pagina_metricas = $data['info_pagina_metricas'];

		$info_pagina_host = $data['info_pagina_host'];
		$info_pagina_port = $data['info_pagina_port'];
		$info_pagina_username = $data['info_pagina_username'];
		$info_pagina_password = $this->encryptPassword($data['info_pagina_password']); // Encriptar contraseña en insert
		$info_pagina_correo_remitente = $data['info_pagina_correo_remitente'];
		$info_pagina_nombre_remitente = $data['info_pagina_nombre_remitente'];
		$info_pagina_correo_oculto = $data['info_pagina_correo_oculto'];
		$info_pagina_titulo_legal = $data['info_pagina_titulo_legal'];
		$info_pagina_descripcion_legal = $data['info_pagina_descripcion_legal'];
		$info_pagina_favicon = $data['info_pagina_favicon'];


		$query = "INSERT INTO info_pagina( info_pagina_facebook, info_pagina_instagram, info_pagina_twitter, info_pagina_pinterest, info_pagina_youtube, info_pagina_flickr, info_pagina_linkedin, info_pagina_google, info_pagina_telefono, info_pagina_whatsapp, info_pagina_correos_contacto, info_pagina_direccion_contacto, info_pagina_informacion_contacto, info_pagina_informacion_contacto_footer, info_pagina_latitud, info_pagina_longitud, info_pagina_zoom, info_pagina_descripcion, info_pagina_tags, info_pagina_robot, info_pagina_sitemap, info_pagina_scripts, info_pagina_metricas,info_pagina_host,info_pagina_port,info_pagina_username,info_pagina_password,info_pagina_correo_remitente,info_pagina_nombre_remitente,info_pagina_correo_oculto,info_pagina_titulo_legal,info_pagina_descripcion_legal,info_pagina_favicon) VALUES ( '$info_pagina_facebook', '$info_pagina_instagram', '$info_pagina_twitter', '$info_pagina_pinterest', '$info_pagina_youtube', '$info_pagina_flickr', '$info_pagina_linkedin', '$info_pagina_google', '$info_pagina_telefono', '$info_pagina_whatsapp', '$info_pagina_correos_contacto', '$info_pagina_direccion_contacto', '$info_pagina_informacion_contacto', '$info_pagina_informacion_contacto_footer', '$info_pagina_latitud', '$info_pagina_longitud', '$info_pagina_zoom', '$info_pagina_descripcion', '$info_pagina_tags', '$info_pagina_robot', '$info_pagina_sitemap', '$info_pagina_scripts', 'info_pagina_metricas','$info_pagina_host','$info_pagina_port','$info_pagina_username','$info_pagina_password','$info_pagina_correo_remitente','$info_pagina_nombre_remitente','$info_pagina_correo_oculto','$info_pagina_titulo_legal','$info_pagina_descripcion_legal','$info_pagina_favicon')";
		$res = $this->_conn->query($query);
		return mysqli_insert_id($this->_conn->getConnection());
	}

	/**
	 * update Recibe la informacion de un infopage  y actualiza la informacion en la base de datos
	 * @param  array Array Array con la informacion con la cual se va a realizar la actualizacion en la base de datos
	 * @param  integer    identificador al cual se le va a realizar la actualizacion
	 * @return void
	 */
	public function update($data, $id)
	{

		$info_pagina_facebook = $data['info_pagina_facebook'];
		$info_pagina_instagram = $data['info_pagina_instagram'];
		$info_pagina_twitter = $data['info_pagina_twitter'];
		$info_pagina_pinterest = $data['info_pagina_pinterest'];
		$info_pagina_youtube = $data['info_pagina_youtube'];
		$info_pagina_flickr = $data['info_pagina_flickr'];
		$info_pagina_linkedin = $data['info_pagina_linkedin'];
		$info_pagina_google = $data['info_pagina_google'];
		$info_pagina_telefono = $data['info_pagina_telefono'];
		$info_pagina_whatsapp = $data['info_pagina_whatsapp'];
		$info_pagina_correos_contacto = $data['info_pagina_correos_contacto'];
		$info_pagina_direccion_contacto = $data['info_pagina_direccion_contacto'];
		$info_pagina_informacion_contacto = $data['info_pagina_informacion_contacto'];
		$info_pagina_informacion_contacto_footer = $data['info_pagina_informacion_contacto_footer'];
		$info_pagina_latitud = $data['info_pagina_latitud'];
		$info_pagina_longitud = $data['info_pagina_longitud'];
		$info_pagina_zoom = $data['info_pagina_zoom'];
		$info_pagina_descripcion = $data['info_pagina_descripcion'];
		$info_pagina_tags = $data['info_pagina_tags'];
		$info_pagina_robot = $data['info_pagina_robot'];
		$info_pagina_sitemap = $data['info_pagina_sitemap'];
		$info_pagina_scripts = $data['info_pagina_scripts'];
		$info_pagina_metricas = $data['info_pagina_metricas'];
		$info_pagina_host = $data['info_pagina_host'];
		$info_pagina_port = $data['info_pagina_port'];
		$info_pagina_username = $data['info_pagina_username'];
		$info_pagina_password = $data['info_pagina_password'];
		$changepasword = '';
		if ($data['info_pagina_password'] != '') {
			// Encriptar la contraseña SMTP de forma reversible
			$info_pagina_password = $this->encryptPassword($data['info_pagina_password']);
			$changepasword = " , info_pagina_password = '$info_pagina_password'";
		}
		$info_pagina_correo_remitente = $data['info_pagina_correo_remitente'];
		$info_pagina_nombre_remitente = $data['info_pagina_nombre_remitente'];
		$info_pagina_correo_oculto = $data['info_pagina_correo_oculto'];
		$info_pagina_titulo_legal = $data['info_pagina_titulo_legal'];
		$info_pagina_descripcion_legal = $data['info_pagina_descripcion_legal'];
		$info_pagina_favicon = $data['info_pagina_favicon'];
		$alertaMinutos = "";
		if (isset($data['info_pagina_alerta_minutos']) && (int) $data['info_pagina_alerta_minutos'] > 0) {
			$alertaMinutos = " , info_pagina_alerta_minutos = '" . (int) $data['info_pagina_alerta_minutos'] . "'";
		}


		$query = "UPDATE info_pagina SET  info_pagina_facebook = '$info_pagina_facebook', info_pagina_instagram = '$info_pagina_instagram', info_pagina_twitter = '$info_pagina_twitter', info_pagina_pinterest = '$info_pagina_pinterest', info_pagina_youtube = '$info_pagina_youtube', info_pagina_flickr = '$info_pagina_flickr', info_pagina_linkedin = '$info_pagina_linkedin', info_pagina_google = '$info_pagina_google', info_pagina_telefono = '$info_pagina_telefono', info_pagina_whatsapp = '$info_pagina_whatsapp', info_pagina_correos_contacto = '$info_pagina_correos_contacto', info_pagina_direccion_contacto = '$info_pagina_direccion_contacto', info_pagina_informacion_contacto = '$info_pagina_informacion_contacto', info_pagina_informacion_contacto_footer = '$info_pagina_informacion_contacto_footer', info_pagina_latitud = '$info_pagina_latitud', info_pagina_longitud = '$info_pagina_longitud', info_pagina_zoom = '$info_pagina_zoom', info_pagina_descripcion = '$info_pagina_descripcion', info_pagina_tags = '$info_pagina_tags', info_pagina_robot = '$info_pagina_robot', info_pagina_sitemap = '$info_pagina_sitemap', info_pagina_scripts = '$info_pagina_scripts', info_pagina_metricas='$info_pagina_metricas',info_pagina_host='$info_pagina_host',info_pagina_port='$info_pagina_port',info_pagina_username='$info_pagina_username',info_pagina_password='$info_pagina_password',info_pagina_correo_remitente='$info_pagina_correo_remitente',info_pagina_nombre_remitente='$info_pagina_nombre_remitente',info_pagina_correo_oculto='$info_pagina_correo_oculto',info_pagina_titulo_legal='$info_pagina_titulo_legal',info_pagina_descripcion_legal='$info_pagina_descripcion_legal',info_pagina_favicon='$info_pagina_favicon' $changepasword $alertaMinutos WHERE info_pagina_id = '" . $id . "'";
		$res = $this->_conn->query($query);
	}

	/**
	 * Encripta una contraseña de forma reversible usando AES-256-CBC
	 * @param string $password Contraseña a encriptar
	 * @return string Contraseña encriptada en base64
	 */
	private function encryptPassword($password)
	{
		$encryption_key = 'SecretPistaDeOportunidadesOmega2025';
		$iv = openssl_random_pseudo_bytes(openssl_cipher_iv_length('aes-256-cbc'));
		$encrypted = openssl_encrypt($password, 'aes-256-cbc', $encryption_key, 0, $iv);
		return base64_encode($encrypted . '::' . $iv);
	}

	/**
	 * Desencripta una contraseña encriptada con encryptPassword
	 * @param string $encrypted_password Contraseña encriptada en base64
	 * @return string Contraseña desencriptada
	 */
	public function decryptPassword($encrypted_password)
	{
		$encryption_key = 'SecretPistaDeOportunidadesOmega2025';
		list($encrypted_data, $iv) = explode('::', base64_decode($encrypted_password), 2);
		return openssl_decrypt($encrypted_data, 'aes-256-cbc', $encryption_key, 0, $iv);
	}
}