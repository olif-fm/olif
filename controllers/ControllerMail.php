<?php
/**
 * ControllerMail
 * @version V 1.7
 * @author Alberto Vara (C) Copyright 2013
 * @package OLIF.ControllerMail
 *
 * DEPENDS/REQUIRED:
 * + controllers/ControllerApp
 * + threeparty/PHPMailer
 */
namespace Olif;

require_once CORE_ROOT.CONTROLLERS.DIRECTORY_SEPARATOR."ControllerApp.php";

class ControllerMail extends ControllerApp {
    /** *
    * h_mail
    * Objeto donde se instancia la clase phpMailer
    */
    public $h_mail;
    /**
    * __construct
    * Constructor de la clase
    */
    public function __construct() {
        parent::__construct();
        require_once (CORE_ROOT.THREEPARTY.'PHPMailer/class.phpmailer.php');
        $this->h_mail = new phpmailer();
        $this->h_mail->PluginDir = CORE_ROOT.THREEPARTY."PHPMailer/";
        $this->h_mail->Host     = MAILER_HOST;
        $this->h_mail->Port     = MAILER_PORT;
        $this->h_mail->Username = MAILER_USER;
        $this->h_mail->Password = MAILER_PASS;
        $this->h_mail->SMTPAuth = true;  // authentication enabled
        if (MAILER_USE_SLL)$this->h_mail->SMTPSecure = 'ssl'; // secure transfer enabled REQUIRED for Gmail
        $this->h_mail->Timeout = 50;
        $this->h_mail->Mailer = "smtp";
        $this->h_mail->IsSMTP();
        $this->h_mail->IsHTML(true);
        $this->h_mail->CharSet = "UTF-8";
        $this->h_mail->SMTPDebug = 0;
    }
    public function setMailerMail() {
        $this->h_mail->Mailer = "mail";
    }
    public function setMailerSMTP() {
        $this->h_mail->Mailer = "smtp";
    }
    /**
     * constructMail
     * @param from: email del remitente
     * @param fromname: nombre del remitente
     * @param subject: titulo del mensaje
     * @param html: contenido del mail
     * @param plain_text: texto plano: suele ser el aviso legal
     * @param reply: contestar a:
        *
        **/
    public function constructMail($from, $fromname, $subject, $html, $plain_text, $reply, $default = false) {
        //Si no se ha enviado ningún cuerpo, se incluye uno por defecto
        $body="";
        $html=$this->stringToMail($html);

        if ($default==true) {
            $body = '<html>
            <head>
            <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
            </head>
            <table width="676" border="0" align="center" cellpadding="0" cellspacing="0">
              <tr>
                <td>&nbsp;<img src="http://www.gobalo.es/phpmailer/mail.jpg" alt="Gobalo using inspiration"/></td>
              </tr>
              </table>';
        }
        $body.=$html;
        if ($default==true) {
            $body.='<table width="676" border="0" align="center" cellpadding="0" cellspacing="0">
              <tr>
                <td>&nbsp;
                <p style="font-family:Tahoma, Geneva, sans-serif; color:#444444; font-size:9px; line-height:17px;">AVISO LEGAL<br />
            Este mensaje está dirigido únicamente a su destinatario y es confidencial. Si lo ha recibido o tenido conocimiento del mismo por error, GOBALO le informa que su contenido es reservado y su lectura, copia y uso no está autorizado, por lo que en tal caso le rogamos nos lo comunique por la misma vía y proceda a borrarlo de manera inmediata.</p>
            <p style="font-family:Tahoma, Geneva, sans-serif; color:#444444; font-size:9px; line-height:17px;">GOBALO no garantiza la confidencialidad de los mensajes transmitidos vía Internet, por lo que si el destinatario de este mensaje no consiente en la utilización de este medio, deberá ponerlo inmediatamente en nuestro conocimiento. GOBALO se reserva el derecho a ejercer las acciones legales que le correspondan contra todo tercero que acceda de forma ilegítima al contenido de este mensaje y al de los ficheros contenidos en el mismo.</p></td>
              </tr>
            </table>';
        }
        //Footer plain text
        $plain_text.='';

        $this->h_mail->From     = $from;
        $this->h_mail->FromName = $fromname;
        $this->h_mail->Subject  = $subject;
        $this->h_mail->AddReplyTo($reply, $fromname);
        $this->h_mail->AltBody = $plain_text;
        $this->h_mail->Body = $body;


    }
    /**
     * Adjuntar destinatarios
     */
    public function addAddress($address) {
        return $this->h_mail->AddAddress($address);
    }
    /**
     * Adjuntar destinatarios
     */
    public function AddBCC($address) {
        return $this->h_mail->AddBCC($address);
    }
    /**
     * Adjuntar ficheros
     */
    public function addAttachment($atach = "", $atach_name = "") {
        if ( is_file($atach) ) {
            $this->h_mail->AddAttachment($atach, $atach_name);
        }
    }
    /**
     * Enviar mail
     */
    public function sendMail() {
        //se envia el mensaje, si no ha habido problemas
        //la variable $exito tendra el valor true
        //if (IS_DEV===false)$this->setMailerMail();
        $result=$this->h_mail->Send();
        /*
        $intentos=1;
        while ((!$result) && ($intentos < 5)) {
            sleep(5);
            //echo $mail->ErrorInfo;
            $result = $this->h_mail->Send();
            if (!$result) {
                echo 'Error: ' . $this->h_mail->ErrorInfo;
            }
            $intentos++;
        }*/
        $this->h_mail->ClearAddresses();
        return $result;
    }
    public function ErrorInfo() {
        return $this->h_mail->ErrorInfo;
    }
    public function IsError() {
        return $this->h_mail->IsError();
    }
    /**
    *
    */
    public function stringToMail($texto) {
        $texto=html_entity_decode($texto, ENT_NOQUOTES);
        $texto=htmlspecialchars_decode($texto);
        $texto=str_replace("\\\"", '"', $texto);
        $texto=str_replace("\\n", '', $texto);
        $texto=str_replace("\\r", '', $texto);
        return $texto;
    }
}
