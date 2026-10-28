<?php

/**
 * Modelo del modulo Core que se encarga de inicializar  la clase de envio de correos
 */
class Core_Model_Mail
{
    /**
     * classe de  phpmailer
     * @var class
     */
    private $mail;

    /**
     * asigna los valores a la clase e instancia el phpMailer
     */
    public function __construct()
    {

        $informacionModel = new Page_Model_DbTable_Informacion();
        $informacion = $informacionModel->getList("", "orden ASC")[0];

        $informacionAdminModel = new Administracion_Model_DbTable_Informacion();
        $decrypted_password = $informacionAdminModel->decryptPassword($informacion->info_pagina_password);

        $this->mail = new PHPMailer;
        $this->mail->CharSet = 'UTF-8';
        $this->mail->isSMTP();
        $this->mail->SMTPDebug = 0;
        //$this->mail->SMTPSecure = 'ssl';
        //$this->mail->SMTPSecure = false;
        $this->mail->Host = $informacion->info_pagina_host;
        $this->mail->Port = $informacion->info_pagina_port;
        $this->mail->SMTPAuth = true;
        // PHPMailer espera hasta 300s por defecto si el SMTP no responde; se limita para no colgar el registro de maletas
        $this->mail->Timeout = 30;
        $this->mail->Username = $informacion->info_pagina_username;
        $this->mail->Password = $decrypted_password;
        $this->mail->setFrom($informacion->info_pagina_correo_remitente, $informacion->info_pagina_nombre_remitente);


    }
    /**
     * retorna la  instancia de email
     * @return class email
     */
    public function getMail()
    {
        return $this->mail;
    }

    /**
     * envia el correo
     * @return bool envia el estado del correo
     */
    public function sed()
    {

        if (Config_Config::getInstance()->getValue('notificaciones/redirigir')) {
            $this->mail->clearAddresses();
            $this->mail->clearCCs();
            $this->mail->clearBCCs();
            $this->mail->addAddress('desarrollo8@omegawebsystems.com', 'Desarrollo 8');
        }

        if ($this->mail->send()) {
            return true;
        } else {
            return false;
        }
    }
}
