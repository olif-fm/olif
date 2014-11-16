<?php
/**
 * ControllerApp
 * @version V 1.7
 * @author Alberto Vara (C) Copyright 2013
 * @package OLIF.ControllerApp
 *
 *
 */
namespace Olif;

require_once CORE_ROOT . LIBS . DIRECTORY_SEPARATOR . 'Asingleton.php';

class ControllerTest extends Asingleton
{

    public function itsWorks()
    {
        echo "<h1>ITS WORKS!</h1>";
    }
}
