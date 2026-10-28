<?php

/**
 *
 */

class Administracion_indexController extends Controllers_Abstract
{

    protected $_csrf_section = "login_admin";

    public function indexAction()
    {
        $this->setLayout('administracion_login');
        $this->getLayout()->setTitle("ACCESO A USUARIO EQUIPAJE");
        $id = Session::getInstance()->get("kt_login_id");
        $level = Session::getInstance()->get("kt_login_level");
        if (isset($id) && $id > 0 && $level != 2) {
            header('Location: ' . Administracion_Model_Acceso::home());
        }
        $csrf = Session::getInstance()->get('csrf')[$this->_csrf_section];
        $this->_view->csrf = $csrf;
        $this->_view->error_login = Session::getInstance()->get("error_login");
        Session::getInstance()->set("error_login", "");
    }
    public function otpAction()
    {
        $this->setLayout('administracion_login');
        $this->getLayout()->setTitle("ACCESO A USUARIO");
        $id = Session::getInstance()->get("kt_login_id");
        $level = Session::getInstance()->get("kt_login_level");
        if (isset($id) && $id > 0 && $level != 2) {
            header('Location: ' . Administracion_Model_Acceso::home());
        }
        if (!Session::getInstance()->get("user_uid")) {
            header('Location: /administracion/');
            exit;
        }
        $csrf = Session::getInstance()->get('csrf')[$this->_csrf_section];
        $this->_view->csrf = $csrf;
        $this->_view->error_login = Session::getInstance()->get("error_login");
        Session::getInstance()->set("error_login", "");
        $this->_view->mensaje_otp = Session::getInstance()->get("mensaje_otp");
        Session::getInstance()->set("mensaje_otp", "");
    }

    public function olvidoAction()
    {
        $this->setLayout('administracion_login');
        $this->getLayout()->setTitle("¿Haz olvidado tu contraseña?");
        $id = Session::getInstance()->get("kt_login_id");
        $level = Session::getInstance()->get("kt_login_level");
        if (isset($id) && $id > 0 && $level != 2) {
            header('Location: ' . Administracion_Model_Acceso::home());
        }
        $csrf = Session::getInstance()->get('csrf')[$this->_csrf_section];
        $this->_view->csrf = $csrf;
        $this->_view->error_olvido = Session::getInstance()->get("error_olvido");
        Session::getInstance()->set("error_olvido", "");
        $this->_view->mensaje_olvido = Session::getInstance()->get("mensaje_olvido");
        Session::getInstance()->set("mensaje_olvido", "");
    }

    public function changepasswordAction()
    {
        $this->setLayout('administracion_login');
        $this->getLayout()->setTitle("Cambiar Contraseña");
        $user = $this->validarCodigo();
        if (isset($user['error'])) {
            if ($user['error'] == 1) {
                $this->_view->error = "Lo sentimos este codigo ya fue utilizado.";
            } else {
                $this->_view->error = "La información Suministrada es invalida.";
            }
        } else {
            $this->_view->usuario = $user['user']->user_user;
            new Core_Model_Csrf('nueva_contrasena');
            $csrf = Session::getInstance()->get('csrf')['nueva_contrasena'];
            $password = $this->_getSanitizedParam("password");
            $re_password = $this->_getSanitizedParam("re_password");
            if ($this->getRequest()->isPost() == true && $password == $re_password) {
                $id_user = $user['user']->user_id;
                $modelUser = new Core_Model_DbTable_User();
                $modelUser->changePassword($id_user, $password);
                $modelUser->editCode($id_user, $csrf);
                // Al cambiar la clave se deja de confiar en los dispositivos recordados
                $dispositivosModel = new Administracion_Model_DbTable_Dispositivos();
                $dispositivosModel->revocarUsuario($id_user);
                $this->_view->message = "Sea cambiado su contraseña satisfactoriamente.";
            } else {
                $this->_view->code = $this->_getSanitizedParam("code");
                $this->_view->usuario = $user['user']->user_user;
                $this->_view->csrf = $this->_getSanitizedParam("csrf");
            }
        }
    }


    protected function validarCodigo()
    {
        $res = [];
        $code = base64_decode($this->_getSanitizedParam("code"));
        if (isset($code) && $this->isJson($code) == true) {
            $code = json_decode($code, true);
            $modelUser = new Core_Model_DbTable_User();
            if (isset($code['user'])) {
                $user = $modelUser->getById($code['user']);
                if (isset($user->user_id)) {
                    if ($user->user_code == $code['code']) {
                        $res['user'] = $user;
                    } else {
                        $res['error'] = 1;
                        $res['user'] = $user;
                    }
                } else {
                    $res['error'] = 2;
                }
            } else {
                $res['error'] = 3;
            }
        } else {
            $res['error'] = 4;
        }
        return $res;
    }

    /**
     * verifica si una cadena es de tipo json
     * @param  string  $string cadena a evaluar
     * @return boolean    resultado de la evaluacion
     */
    private function isJson($string)
    {
        return ((is_string($string) && (is_object(json_decode($string)) || is_array(json_decode($string))))) ? true : false;
    }

    public function filluseridAction()
    {
        $this->setLayout('blanco');
        $usuarioModel = new Administracion_Model_DbTable_Usuario();
        $existe = $usuarioModel->getList(" user_uid='' OR user_uid IS NULL ", "");
        foreach ($existe as $usuario) {
            $id = $usuario->user_id;
            $user_uid = strtoupper(dechex(mt_rand(1000, 9999)) . str_replace('.', '', uniqid('', true)));
            echo $user_uid . "\n";
            echo $id . "\n";
            $usuarioModel->editFieldById($id, "user_uid", $user_uid);
        }
    }

    public function seleccionaroverAction()
    {
        $change = $this->_getSanitizedParam("change");
        if ($change == 1) {
            Session::getInstance()->set("over", null);
            Session::getInstance()->set("step", null);
        }
        if (
            Session::getInstance()->get("over") != null
        ) {
            header('Location: ' . Administracion_Model_Acceso::home());
            exit;
        }



    }
    public function seleccionAction()
    {
        $over = $this->_getSanitizedParam("over");
        if (!$over) {
            header('Location: /administracion/index/seleccionarover?error=1');
            exit;
        }
        $step = $this->_getSanitizedParam("step");
        if ($over == 1 && !$step) {
            header('Location: /administracion/index/seleccionarover?error=1');
            exit;
        }
        Session::getInstance()->set("over", $over);

        if ($over == 1 && ($step == "N" || $step == "I")) {
            Session::getInstance()->set("step", $step);
        }


        header('Location: ' . Administracion_Model_Acceso::home());
    }

    public function saliroverAction()
    {
        Session::getInstance()->set("over", null);
        Session::getInstance()->set("step", null);
        header('Location: ' . Administracion_Model_Acceso::home());
    }   
}