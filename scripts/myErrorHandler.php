<?php
/*
 * ****************
 * myErrorHandler
 * Para modificar los avisos de error que devuelve PHP
 * Para activar y desactivar esta función revisar construct_site_basic
 * ***************
 */
function myErrorHandler($errno, $errstr, $errfile, $errline)
{
    if (ini_get("display_errors") != 1 && ini_get("display_errors") != 'on') {
        return true;
    }
    $texto_error = "";
    $texto_error .= "";
    $exit = false;
    switch ($errno) {
        /**
         * ******* E_USER_WARNING *****
         */
        case E_USER_ERROR:
            $texto_error = "<b>ERROR</b> [$errno] $errstr<br />\n";
            $texto_error .= "  Fatal error on line $errline in file $errfile";
            $texto_error .= ", PHP " . PHP_VERSION . " (" . PHP_OS . ")<br />\n";
            $texto_error .= "Aborting...<br />\n";
            $exit = true;
            break;
        case 256:
            $texto_error = "<b>ERROR</b> [$errno] $errstr<br />\n";
            $texto_error .= "  Fatal error on line $errline in file $errfile";
            $texto_error .= ", PHP " . PHP_VERSION . " (" . PHP_OS . ")<br />\n";
            $texto_error .= "Aborting...<br />\n";
            $exit = true;
            break;
        /**
         * ******* E_USER_WARNING *****
         */
        case E_USER_WARNING:
            $texto_error .= "<b>WARNING</b> [$errno] $errstr<br />\n";
            $texto_error .= "on line " . $errline . " in file " . $errfile . "<br />\n";

            break;
        case 2:
            $texto_error = "<b>ERROR</b> [$errno] $errstr<br />\n";
            $texto_error .= "  Fatal error on line $errline in file $errfile";
            $texto_error .= ", PHP " . PHP_VERSION . " (" . PHP_OS . ")<br />\n";
            $texto_error .= "Aborting...<br />\n";
            $exit = true;
            break;
        /**
         * ******* E_USER_WARNING *****
         */
        case E_NOTICE:
            $texto_error .= "<b>NOTICE</b> [$errno] $errstr<br />\n";
            $texto_error .= "on line " . $errline . " in file " . $errfile . "<br />\n";
            break;
        case E_USER_NOTICE:
            $texto_error .= "<b>NOTICE</b> [$errno] $errstr<br />\n";
            $texto_error .= "on line " . $errline . " in file " . $errfile . "<br />\n";
            break;
        case 1024:
            $texto_error .= "<b>NOTICE</b> [$errno] $errstr<br />\n";
            $texto_error .= "on line " . $errline . " in file " . $errfile . "<br />\n";

            break;
        /**
         * ******* DEFAULT *****
         */
        default:
            $texto_error .= "Unknown error type: [$errno] $errstr<br />\n";
            $texto_error .= "on line " . $errline . " in file " . $errfile . "<br />\n";
            $exit = true;
            break;
    }
    if (IS_DEV) {
        if (! ((SHOW_SYSTEM_ERRORS == 30711 || SHOW_SYSTEM_ERRORS == 32759 || SHOW_SYSTEM_ERRORS == - 9) && $errno == 8)) {
            echo $texto_error;
        }
    } else {
        if (! ((SHOW_SYSTEM_ERRORS == 30711 || SHOW_SYSTEM_ERRORS == 32759 || SHOW_SYSTEM_ERRORS == - 9) && $errno == 8)) {
            sendMailError($texto_error, DOMAIN);
        }
        if ($exit === true)
            exit(1);
    }

    return true;
}
