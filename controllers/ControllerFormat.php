<?php
/**
 * ControllerFormat
 * @version V 1.7
 * @author Alberto Vara (C) Copyright 2013
 * @package OLIF.ControllerFormat
 *
 * DEPENDS/REQUIRED:
 * + controllers/ControllerApp
 */
namespace Olif;

require_once CORE_ROOT . CONTROLLERS . DIRECTORY_SEPARATOR . "ControllerApp.php";

class ControllerFormat extends ControllerApp
{

    /**
     * formatDateToSQL
     * Da formato a una fecha para insertarla en SQL
     *
     * @param
     *            date
     */
    public function dateToSQL($date)
    {
        $f_dia = substr($date, 0, 2);
        $f_mes = substr($date, 3, 2);
        $f_anio = substr($date, 6, 4);
        $fecha_formated = $f_anio . "-" . $f_mes . "-" . $f_dia;
        return $fecha_formated;
    }

    public function dateToTPL($date, $sep = "/")
    {
        $f_dia = substr($date, 8, 2);
        $f_mes = substr($date, 5, 2);
        $f_anio = substr($date, 0, 4);
        $fecha_formated = $f_dia . $sep . $f_mes . $sep . $f_anio;
        return $fecha_formated;
    }

    public function dateToTPL_beauty($date, $s_dayWeek = true, $s_monthText = true, $sep = " de ")
    {
        $dia = date('j', strtotime($date));
        // $date_beautifull = date('d', strtotime($date));
        $dia_semana_num = date('N', strtotime($date));
        $dia_mes_num = date('n', strtotime($date));
        if ($s_dayWeek === true) {
            switch ($dia_semana_num) {
                case 1:
                    $dia_semana_text = "lunes";
                    break;
                case 2:
                    $dia_semana_text = "martes";
                    break;
                case 3:
                    $dia_semana_text = "miércoles";
                    break;
                case 4:
                    $dia_semana_text = "jueves";
                    break;
                case 5:
                    $dia_semana_text = "viernes";
                    break;
                case 6:
                    $dia_semana_text = "sábado";
                    break;
                case 7:
                    $dia_semana_text = "domingo";
                    break;
            }
        } else {
            $dia_semana_text = "";
        }
        if ($s_monthText === true) {
            switch ($dia_mes_num) {
                case 1:
                    $dia_mes_text = "Enero";
                    break;
                case 2:
                    $dia_mes_text = "Febrero";
                    break;
                case 3:
                    $dia_mes_text = "Marzo";
                    break;
                case 4:
                    $dia_mes_text = "Abril";
                    break;
                case 5:
                    $dia_mes_text = "Mayo";
                    break;
                case 6:
                    $dia_mes_text = "Junio";
                    break;
                case 7:
                    $dia_mes_text = "Julio";
                    break;
                case 8:
                    $dia_mes_text = "Agosto";
                    break;
                case 9:
                    $dia_mes_text = "Septiembre";
                    break;
                case 10:
                    $dia_mes_text = "Octubre";
                    break;
                case 11:
                    $dia_mes_text = "Noviembre";
                    break;
                case 12:
                    $dia_mes_text = "Diciembre";
                    break;
            }
        } else {
            $dia_mes_text = "";
        }
        return $dia_semana_text . " " . $dia . $sep . $dia_mes_text;
    }

    public function clearOnlyChars($string)
    {
        $string = strtolower($string);
        $string = str_replace(" ", "_", $string);
        $string = preg_replace("([^0-9A-Za-z_]+)", "", $string);
        return $string;
    }

    public function textAreaToTPL($text)
    {
        $text = str_replace("\n", "<br>", $text);
        return $text;
    }

    public function textAreaToInput($text)
    {
        $text = str_replace("<br>", "\n", $text);
        return $text;
    }

    public function sumDate($fecha, $ndias)
    {
        if (preg_match("/[0-9]{1,2}\/[0-9]{1,2}\/([0-9][0-9]){1,2}/", $fecha))
            list ($dia, $mes, $año) = preg_split('/[-\.\/ ]/', $fecha);
        if (preg_match("/([0-9][0-9]){1,2}-[0-9]{1,2}-[0-9]{1,2}/", $fecha))
            list ($año, $mes, $dia) = preg_split('/[-\.\/ ]/', $fecha);
        $nueva = mktime(0, 0, 0, $mes, $dia, $año) + $ndias * 24 * 60 * 60;
        $nuevafecha = date("d/m/Y", $nueva);
        return ($nuevafecha);
    }

    public function numberToSQL($number)
    {
        $number = str_replace(",", ".", $number);
        if (! is_numeric($number))
            $number = 0;
        else {
            // $isNegative=($number<0)?-1:1;
            // echo "|".$isNegative."<br>";
            $number = (number_format((float) $number, 2, '.', ''));
        }
        // echo $number;
        return $number;
    }

    public function numberToTPL($number, $num_dec = 2)
    {
        if (! is_numeric($number))
            $number = '0';
        else {
            $number = number_format((float) $number, $num_dec, ',', '.');
        }
        return $number;
    }

    public function noAcents($string)
    {
        $string = preg_replace("/(á|à|ä|Á|À|Ä)+/i", "a", $string);
        $string = preg_replace("/(é|è|ë|É|È|Ë)+/i", "e", $string);
        $string = preg_replace("/(í|ì|ï|Í|Ì|Ï)+/i", "i", $string);
        $string = preg_replace("/(ó|ò|ö|Í|Ì|Ï)+/i", "o", $string);
        $string = preg_replace("/(ú|ù|ü|Ú|Ù|Ü)+/i", "u", $string);
        return $string;
    }

    public function clearTitleToUrl($url)
    {
        $url = mb_strtolower($url, 'UTF-8');
        $url = str_replace(" ", "_", $url);
        $url = str_replace("á", "a", $url);
        $url = str_replace("&aacute;", "a", $url);
        $url = str_replace("é", "e", $url);
        $url = str_replace("&eacute;", "e", $url);
        $url = str_replace("í", "i", $url);
        $url = str_replace("&iacute;", "i", $url);
        $url = str_replace("ó", "o", $url);
        $url = str_replace("&oacute;", "o", $url);
        $url = str_replace("ú", "u", $url);
        $url = str_replace("&uacute;", "e", $url);
        $url = str_replace("ñ", "n", $url);
        $url = str_replace("&ntilde;", "n", $url);
        $url = str_replace("º", "o", $url);
        $url = str_replace("ª", "a", $url);
        $url = str_replace("?", "", $url);
        $url = str_replace("¿", "", $url);
        $url = str_replace("@", "", $url);
        $url = str_replace("?", "", $url);
        $url = str_replace("$", "", $url);
        $url = str_replace("/", "", $url);
        $url = str_replace(".", "_", $url);
        $url = str_replace(",", "_", $url);
        $url = str_replace("-", "_", $url);
        $url = str_replace("\\", "", $url);
        $url = preg_replace("([^0-9A-Za-z_]+)", "", $url);
        return $url;
    }

    public function cutText($texto, $longitud = 180, $force = false)
    {
        if ((mb_strlen($texto) > $longitud)) {
            ini_set("display_errors", 'off');
            if ($force === false) {
                $pos_espacios = mb_strpos($texto, ' ', $longitud) - 1;
                if ($pos_espacios > 0) {
                    $caracteres = count_chars(mb_substr($texto, 0, ($pos_espacios + 1)), 1);
                    if (@$caracteres[ord('<')] > @$caracteres[ord('>')]) {
                        $pos_espacios = mb_strpos($texto, ">", $pos_espacios) - 1;
                    }
                    $texto = mb_substr($texto, 0, ($pos_espacios + 1)) . '...';
                }
                if (preg_match_all("|(<([\w]+)[^>]*>)|", $texto, $buffer)) {
                    if (! empty($buffer[1])) {
                        preg_match_all("|</([a-zA-Z]+)>|", $texto, $buffer2);
                        if (count($buffer[2]) != count($buffer2[1])) {
                            $cierrotags = array_diff($buffer[2], $buffer2[1]);
                            $cierrotags = array_reverse($cierrotags);
                            foreach ($cierrotags as $tag) {
                                $texto .= '</' . $tag . '>';
                            }
                        }
                    }
                }
            } elseif ($force === true) {
                $texto = mb_substr($texto, 0, $longitud) . '...';
            }
            ini_set("display_errors", 'on');
        }
        return $texto;
    }
}
