<?php
/*
 * ****************
 * sendMailError
 * Función auxiliar para que, en el caso de que el sistema falle, esta función pueda seguir mandando avisos de fallo
 * ***************
 */
function sendMailError($texto_error, $web, $optional_title = '')
{
    $aviso_text = "<html><body>
    <div style='padding:25px 25px 35px 35px;color:#000;background:#FFF;font-size:12px'>
    <p>Se ha detectado un error en <b>" . $web . "</b> a las " . date('h:i:s') . " del " . date('jS \of F Y') . "<br>
    <b style='font-weight:boldcolor:#DC378D'>ERROR</b>:</p><font color='#000' style='font-weight:bold'>" . $texto_error . "</font><p>
    <h2>INFORMACIÓN SERVER:</h2>";
    if (isset($_SERVER)) {
        foreach ($_SERVER as $key => $value) {
            $aviso_text .= "[" . $key . "] =>  <code>" . $value . "</code><br>";
        }
    } else {
        $aviso_text .= "NO EXISTE SERVER<br>";
    }
    $aviso_text .= "--------------------------------------------------------<br>" . "<h2>POST_VARS:</h2> ";
    if (isset($_POST)) {
        foreach ($_POST as $key => $value) {
            // echo "KEY: ".$key." VALUE".$value."<br>";
            if (! is_array($value) && $value != '' && strlen($value) < 1000)
                $aviso_text .= "[" . $key . "] =>  <code>" . ($value) . "</code><br>";
        }
    } else {
        $aviso_text .= "NO EXISTE POST_VARS<br>";
    }
    $aviso_text .= "--------------------------------------------------------<br>" . "<h2>GET_VARS:</h2> ";
    if (isset($_GET)) {
        foreach ($_GET as $key => $value) {
            $aviso_text .= "[" . $key . "] =>  <code>" . ($value) . "</code><br>";
        }
    } else {
        $aviso_text .= "NO EXISTE GET_VARS<br>";
    }
    $aviso_text .= "--------------------------------------------------------<br>" . "<h2>COOKIE:</h2> ";
    if (isset($_COOKIE)) {
        foreach ($_COOKIE as $key => $value) {
            $aviso_text .= "[" . $key . "] =>  <code>" . ($value) . "</code><br>";
        }
    } else {
        $aviso_text .= "NO EXISTE COOKIE<br>";
    }
    $aviso_text .= "--------------------------------------------------------<br>" . "<h2>ENV:</h2> ";
    if (isset($_ENV)) {
        foreach ($_ENV as $key => $value) {
            $aviso_text .= "[" . $key . "] =>   <code>" . ($value) . "</code><br>";
        }
    } else {
        $aviso_text .= "NO EXISTE ENV<br>";
    }
    $aviso_text .= "--------------------------------------------------------<br>" . "<h2>SESSION:</h2> ";
    if (isset($_SESSION)) {
        foreach ($_SESSION as $key => $value) {
            $aviso_text .= "[" . $key . "] =>   <code>" . ($value) . "</code><br>";
        }
    } else {
        $aviso_text .= "NO EXISTE SESSION<br>";
    }
    $aviso_text .= "--------------------------------------------------------<br>";
    $aviso_text .= "<h2>DEBUG:</h2> ";
    $var=debug_backtrace();
    //$debug=print_r($var,true);
    ob_start();
    json_encode($var);
    $debug= ob_get_clean();
    /**/
    $aviso_text .= "<pre>" . $debug . "</pre><br>";
    $aviso_text .= "</div>
    </body></html>";
    if ($optional_title == '') {
        $optional_title = "Error en código detectado";
    }
    $result = false;
    $title = "[AVISO] " . $optional_title . " en " . @$_SERVER["HTTP_HOST"];

    if (class_exists('ControllerMail')) {
        $h_mail = ControllerMail::getInstance();
        if (! defined("MAILER_USER_EMAIL")) {
            $h_mail->constructMail(MAILER_USER, MAILER_USER, $title, $aviso_text, "", MAILER_USER);
        } else {
            $h_mail->constructMail(MAILER_USER_EMAIL, MAILER_USER_EMAIL, $title, $aviso_text, "", MAILER_USER_EMAIL);
        }
        $h_mail->addAddress(MAIL_ALERTS);
        // $h_mail->setMailerMail();
        $result = $h_mail->sendMail();
        // echo "ENTRA1!";
    }
    if ($result === false) {
        $cabeceras = 'MIME-Version: 1.0' . "\r\n" . 'Content-type: text/html; charset=utf-8' . "\r\n" . 'From: ' . MAILER_USER . "\r\n" . 'Reply-To: ' . MAILER_USER . "\r\n" . 'X-Mailer: PHP/' . phpversion();
        $result = mail(MAIL_ALERTS, $title, $aviso_text, $cabeceras);
        // echo "ENTRA2!";
    }
    return $result;
}
