<?php
/**
* Controlador de Usuario que permite la  creacion, edicion  y eliminacion de los Usuarios del Sistema
*/
class Core_userController extends Controllers_Abstract
{
	public function validationAction()
    {
        $modelUser = new Core_Model_DbTable_User();
        $user= $this->_getSanitizedParam("user_user");
        $user2= $this->_getSanitizedParam("user");
        $res_user = $modelUser->searchUserByUser($user);
        if(  $user2 !='' &&  $user2 ==  $user  ){
            http_response_code(200);
        } else {
	        if ( $res_user != false ) {
	            header("HTTP/1.0 400 Usuario no Disponible");
	        } else {
	            http_response_code(200);
	        }
    	}
    }

    public function validationemailAction()
    {
        $modelUser = new Core_Model_DbTable_User();
        $correo= $this->_getSanitizedParam("user_email");
        $correo2= $this->_getSanitizedParam("email");
        $res_user = $modelUser->getList("user_email = '$correo'" ,"");
        if( $correo2 !='' && $correo2 == $correo  ){
            http_response_code(200);
        } else {
            if ( isset($res_user[0])) {
                header("HTTP/1.0 400 Correo ya existe");
            } else {
                http_response_code(200);
            }
        }
    }

    public function validarclaveAction (){
        $clave = $this->_getSanitizedParam("user_password");
        $error_clave = '';
       if(strlen($clave) < 8){
          $error_clave = "La clave debe tener al menos 8 caracteres";
       } else if(strlen($clave) > 16){
          $error_clave = "La clave no puede tener mas de 16 caracteres";
       } else if (!preg_match('`[a-z]`',$clave)){
          $error_clave = "La clave debe tener al menos una  minuscula";
       } else if (!preg_match('`[A-Z]`',$clave)){
          $error_clave = "La clave debe tener al menos una mayuscula";
       } else if (!preg_match('`[0-9]`',$clave)){
          $error_clave = "La clave debe tener al menos un numero";
       }
       if ( $error_clave != '' ) {
            header("HTTP/1.0 400 ".$error_clave);
        } else {
            http_response_code(200);
        }
}

}