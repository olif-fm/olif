<?php
/**
 * ControllerFileExcel
 * @version V 1.7
 * @author Alberto Vara (C) Copyright 2013
 * @package OLIF.ControllerFileExcel
 *
 * DEPENDS/REQUIRED:
 * + controllers/ControllerFile
 */
namespace Olif;

class ControllerFileExcel extends ControllerFile
{
    /*
     * pExcel
     * Objeto donde se instanciará la clase PHPExcel
     */
    private $pExcel;

    private $activeSheet = 0;

    private $matrixXLSLetters = array(
        'A',
        'B',
        'C',
        'D',
        'E',
        'F',
        'G',
        'H',
        'I',
        'J',
        'K',
        'L',
        'M',
        'N',
        'O',
        'P',
        'Q',
        'R',
        'S',
        'T',
        'U',
        'V',
        'W',
        'X',
        'Y',
        'Z',
        'AA',
        'AB',
        'AC',
        'AD',
        'AE',
        'AF',
        'AG',
        'AH',
        'AI',
        'AJ',
        'AK',
        'AL',
        'AM',
        'AN',
        'AO',
        'AP',
        'AQ',
        'AR'
    );

    private $typeExcel = array(
        'xlsx' => 'Excel2007',
        'xls' => 'Excel2007'
    );// 'xls' => 'Excel5' Quitado porque Google Drive no reconoce Excel5

    public function __construct()
    {
        require_once (CORE_ROOT . THREEPARTY . '/PHPExcel/Classes/PHPExcel.php');
        $this->initPHPExcel();
    }

    private function initPHPExcel()
    {
        if (! class_exists('PHPExcel')) {
            $texto_error = "[ERROR " . get_class($this) . "::" . __FUNCTION__ . "::" . __LINE__ . "] Se produjo un error: " . $this->h_db->connect_error . "\n" . $this->h_db->error;
            $this->sendError($texto_error);
        }
        if (! is_object($this->pExcel)) {
            $this->pExcel = new \PHPExcel();
        }
    }

    public function setActiveSheet($pos)
    {
        $this->activeSheet = $pos;
        if ($pos > 0) {
            $this->pExcel->createSheet();
        }
        $this->pExcel->setActiveSheetIndex($pos);
    }

    public function setActiveSheetName($name)
    {
        $name = preg_replace("/(á|à|ä|Á|À|Ä)+/i", "a", ($name));
        $name = preg_replace("/(é|è|ë|É|È|Ë)+/i", "e", $name);
        $name = preg_replace("/(í|ì|ï|Í|Ì|Ï)+/i", "i", $name);
        $name = preg_replace("/(ó|ò|ö|Í|Ì|Ï)+/i", "o", $name);
        $name = preg_replace("/(ú|ù|ü|Ú|Ù|Ü)+/i", "u", $name);
        $this->pExcel->getActiveSheet()->setTitle(($name));
    }

    public function writeSheetEmptyField()
    {
        $this->pExcel->getActiveSheet()->setCellValue("A1", "");
    }

    public function writeSheet($params)
    {
        $this->initPHPExcel();
        $num_results = count($params);
        for ($i = 0; $i < $num_results; $i ++) {
            $k = 0;
            foreach ($params[$i] as $key => $value) {
                $j = ($i + 1);
                $this->pExcel->getActiveSheet()->setCellValue($this->matrixXLSLetters[$k] . $j, $value);
                $k ++;
            }
        }
        return true;
    }

    public function saveFile($filename, $path)
    {
        $this->setWorkingFile($filename);
        $this->setWorkingPath($path);
        $this->checkAbsolutePath();
        $this->pExcel->setActiveSheetIndex(0);
        $objWriter = \PHPExcel_IOFactory::createWriter($this->pExcel, $this->getTypeExcel($this->getAbsolutePathFile()));
        $objWriter->save($this->getAbsolutePathFile());
        return $this->insertLog();
    }

    public function getTypeExcel($file)
    {
        return $this->typeExcel[$this->getExtension($file)];
    }

    public function writeExpress($filename, $path, $params)
    {
        $this->setWorkingFile($filename);
        $this->setWorkingPath($path);
        $this->checkAbsolutePath();

        $this->initPHPExcel();
        $this->pExcel->getProperties()
            ->setCreator("OLIF FW")
            ->setLastModifiedBy("OLIF FW")
            ->setTitle("OLIF FW Excel Document")
            ->setSubject("OLIF FW Excel Document")
            ->setDescription("OLIF FW Excel Document")
            ->setKeywords("OLIF FW Excel Document")
            ->setCategory("OLIF FW Excel Document");

        $num_results = count($params);
        for ($i = 0; $i < $num_results; $i ++) {
            $k = 0;
            foreach ($params[$i] as $key => $value) {
                $j = ($i + 1);
                // $this->pExcel->setActiveSheetIndex($this->activeSheet)->setCellValue($this->matrixXLSLetters[$k].$j, mb_convert_encoding($value, 'UTF-16LE', 'UTF-8'));
                $this->pExcel->setActiveSheetIndex($this->activeSheet)->setCellValue($this->matrixXLSLetters[$k] . $j, $value);
                $k ++;
            }
        }

        // $this->pExcel->getActiveSheet()->setTitle('Simple');
        $this->pExcel->setActiveSheetIndex($this->activeSheet);

        $objWriter = \PHPExcel_IOFactory::createWriter($this->pExcel, $this->getTypeExcel($this->getWorkingFile()));
        // echo $this->getTypeExcel($this->getAbsolutePathFile())."<br>";
        $objWriter->save($this->getAbsolutePathFile());
        // $result = $this->insertLog();
        return $this->insertLog();
    }
}
