<?php
/**
 * ControllerFile
 * @version V 1.7
 * @author Alberto Vara (C) Copyright 2013
 * @package OLIF.ControllerFile
 *
 * DEPENDS/REQUIRED:
 * + controllers/ControllerApp
 */
namespace Olif;

require_once CORE_ROOT.CONTROLLERS.DIRECTORY_SEPARATOR."ControllerApp.php";

class ControllerFile extends ControllerApp{
    /**
     * workingFile
     * Fichero sobre el que estamos trabajando.
     * @var string
     */
    public $workingFile;
    /**
     * workingFileID
     * ID del fichero sobre el que estamos trabajando.
     * @var string
     */
    public $workingFileID;
    /**
     * workingPath
     * Path/Ruta sobre el que estamos trabajando.
     * @var string
     */
    public $workingPath;

    public $ExtensionWhiteList = array();

    public $uploadError="";
    /**
     * insertLog
     * Inserta en Base de datos la información del fichero que hemos subido
     * @return boolean
     */
    public function insertLog() {
        $this->getMimeType($this->getAbsolutePathFile());
        $this->getControllerAccess();
        $this->getModel();
        $this->model->getModelFile();
        $userID=$this->access->getSessionID();
        $checkFile = $this->model->file->getFileInfoByUserAndFileName($userID, $this->getWorkingFile());
        if ($checkFile === false) {
            $result = $this->model->file->insertLog($this->getWorkingFile(), $this->getWorkingPath(), $this->getMimeType($this->getAbsolutePathFile()), $_SERVER['REMOTE_ADDR'], $userID, NODE_ROOT);
            if ($result!==false && strlen($result)>0) {
                $this->setWorkingFileID($result);
                return true;
            } else {
                return false;
            }
        } else {
            $result = $this->model->file->updateLog($checkFile['ID'], $_SERVER['REMOTE_ADDR'], $userID);

            if ($result!==false && strlen($result)>0) {
                $this->setWorkingFileID($result);
                return true;
            } else {
                return false;
            }
        }

    }
    public function getFileInfo($fileID) {
        $this->getControllerAccess();
        $this->getModel();
        $this->model->getModelFile();
        $result = $this->model->file->getFileInfo($fileID);
        if ($result!==false) {
            $this->setWorkingFileID($result['ID']);
            $this->setWorkingFile($result['FILENAME']);
            $this->setWorkingPath($result['PATH']);
            return $result;
        } else {
            return false;
        }
    }
    /**
     * updateFileGoogleDriveID
     * Ver si quizás mejor moverlo a googleAPI... aquí no pega demasiado
     * @param string $IdGoogleDrive
     * @param string $fileID
     * @return boolean
     */
    public function updateFileGoogleDriveID($IdGoogleDrive, $fileID = "") {
        if (strlen($fileID)==0)$fileID = $this->getWorkingFileID();
        $result = $this->model->file->getFileInfo($fileID);
        if ($result!==false) {
            return $this->model->file->updateIDGoogleDrive($this->getWorkingFileID(), $IdGoogleDrive);
        } else {
            return false;
        }
    }
    /**
     * updateFileGoogleDriveID
     * Ver si quizás mejor moverlo a googleAPI... aquí no pega demasiado
     * @param string $IdGoogleDrive
     * @param string $fileID
     * @return boolean
     */
    public function updateFileGoogleDriveURL($URLGoogleDrive, $fileID = "") {
        if (strlen($fileID)==0)$fileID = $this->getWorkingFileID();
        $result = $this->model->file->getFileInfo($fileID);
        if ($result!==false) {
            return $this->model->file->updateURLGoogleDrive($this->getWorkingFileID(), $URLGoogleDrive);
        } else {
            return false;
        }
    }
    /**
     * uploadFile
     * Inserta en Base de datos la información del fichero que hemos subido
     * @param $filename
     * @param $filename
     * @param $FileTemp = $_FILE['NOMBRE_POST']
     * @return boolean
     */
    public function uploadFile($filename, $path, $FileTemp) {
        $upload = true;
        $this->getControllerFormat();
        $this->getControllerAccess();

        if (strlen($FileTemp['name'])>0) {
            $ext=$this->getExtension($FileTemp['name']);
        } else {
            $this->setUploadError("NO_FILE");
            $upload=false;
        }
        $finalFilename=$this->access->getSessionID()."_".($filename).".".$ext;
        $this->setWorkingFile($finalFilename);
        $this->setWorkingPath($path);
        $this->checkAbsolutePath();
        //$this->getMimeType($this->getAbsolutePathFile());

        if (count($this->ExtensionWhiteList)>0) {
            if (!in_array($ext, $this->ExtensionWhiteList)) {
                $upload=false;
                $this->setUploadError("ERROR_EXENSION");
            }
        }
        if ($FileTemp['size'] < 1) {
            $upload=false;
            $this->setUploadError("ERROR_SIZE");
        }
        if (!$this->checkAbsolutePath()) {
            $this->setUploadError("ERROR_DIR_DEST");
            $upload=false;
        }
        if (!is_file($FileTemp["tmp_name"])) {
            $this->setUploadError("ERROR_TMP_FILE");
            $upload=false;
        }
        if ($upload===true) {

            if (move_uploaded_file($FileTemp["tmp_name"], $this->getAbsolutePathFile())) {
                return $this->insertLog();
            } elseif (copy($FileTemp["tmp_name"], $this->getAbsolutePathFile())) {
                return $this->insertLog();
            } else {
                $this->setUploadError("PERMISSION_DENIED");
            }
        }
        return false;
    }
    /**
     * uploadFile
     * Inserta en Base de datos la información del fichero que hemos subido
     * @param $filename
     * @param $filename
     * @param $FileTemp = $_FILE['NOMBRE_POST']
     * @return boolean
     */
    public function uploadFileImg($filename, $path, $FileTemp, $maxW, $maxH = null) {
        $upload = true;
        $this->getControllerFormat();
        $this->getControllerAccess();
        $finalFilename=$this->access->getSessionID()."_".($filename);
        //$finalFilename=($filename);
        $this->setWorkingFile($finalFilename);
        $this->setWorkingPath($path);
        $this->checkAbsolutePath();
        //$this->getMimeType($this->getAbsolutePathFile());
        if (strlen($FileTemp['name'])>0) {
            $ext=$this->getExtension($FileTemp['name']);
        } else {
            $this->setUploadError("NO_FILE");
            $upload=false;
        }
        if (count($this->ExtensionWhiteList)>0) {
            if (!in_array($ext, $this->ExtensionWhiteList)) {
                $upload=false;
                $this->setUploadError("ERROR_EXENSION");
            }
        }
        if ($FileTemp['size'] < 1) {
            $upload=false;
            $this->setUploadError("ERROR_SIZE");
        }
        if (!$this->checkAbsolutePath()) {
            $this->setUploadError("ERROR_DIR_DEST");
            $upload=false;
        }
        if (!is_file($FileTemp["tmp_name"])) {
            $this->setUploadError("ERROR_TMP_FILE");
            $upload=false;
        }
        if ($upload===true) {
            $top_offset=0;
            list($width_orig, $height_orig) = getimagesize($FileTemp['tmp_name']);
            if ($maxH == null || $maxH=="") {
                if ($width_orig < $maxW) {
                    $fwidth = $width_orig;
                } else {
                    $fwidth = $maxW;
                }
                $ratio_orig = $width_orig/$height_orig;
                $fheight = $fwidth/$ratio_orig;

                $blank_height = $fheight;
                $top_offset = 0;
            } else {
                if ($width_orig <= $maxW && $height_orig <= $maxH) {
                    $fheight = $height_orig;
                    $fwidth = $width_orig;
                } else {
                    if ($width_orig > $maxW) {
                        $ratio = ($width_orig / $maxW);
                        $fwidth = $maxW;
                        $fheight = ($height_orig / $ratio);
                        if ($fheight > $maxH) {
                            $ratio = ($fheight / $maxH);
                            $fheight = $maxH;
                            $fwidth = ($fwidth / $ratio);
                        }
                    }
                    if ($height_orig > $maxH) {
                        $ratio = ($height_orig / $maxH);
                        $fheight = $maxH;
                        $fwidth = ($width_orig / $ratio);
                        if ($fwidth > $maxW) {
                            $ratio = ($fwidth / $maxW);
                            $fwidth = $maxW;
                            $fheight = ($fheight / $ratio);
                        }
                    }
                }
                if ($fheight == 0 || $fwidth == 0 || $height_orig == 0 || $width_orig == 0) {
                    die("FATAL ERROR REPORT ERROR CODE [add-pic-line-67-orig] ");
                }
                if ($fheight < 45) {
                    $blank_height = 45;
                    $top_offset = round(($blank_height - $fheight)/2);
                } else {
                    $blank_height = $fheight;
                }
            }
            $filetype=$this->getExtension($FileTemp["name"]);
            $image_p = imagecreatetruecolor($fwidth, $blank_height);
            $white = imagecolorallocate($image_p, 255, 255, 255);
            imagefill($image_p, 0, 0, $white);
            switch($filetype) {
                case "gif":
                    $image = @imagecreatefromgif($FileTemp['tmp_name']);
                    break;
                case "jpg":
                    $image = @imagecreatefromjpeg($FileTemp['tmp_name']);
                    break;
                case "jpeg":
                    $image = @imagecreatefromjpeg($FileTemp['tmp_name']);
                    break;
                case "png":
                    $image = @imagecreatefrompng($FileTemp['tmp_name']);
                    break;
            }
            imagecopyresampled($image_p, $image, 0, $top_offset, 0, 0, $fwidth, $fheight, $width_orig, $height_orig);
            switch($filetype) {
                case "gif":
                    if (!imagegif($image_p, $this->getAbsolutePathFile())) {
                        $this->setUploadError("PERMISSION DENIED [GIF] ".$this->getAbsolutePathFile());
                    }
                    break;
                case "jpg":
                    if (!imagejpeg($image_p, $this->getAbsolutePathFile(), 100)) {
                        $this->setUploadError("PERMISSION DENIED [JPG]: ".$this->getAbsolutePathFile());
                    }
                    break;
                case "jpeg":
                    if (!imagejpeg($image_p, $this->getAbsolutePathFile(), 100)) {
                        $this->setUploadError("PERMISSION DENIED [JPEG]: ".$this->getAbsolutePathFile());
                    }
                    break;
                case "png":
                    if (!imagepng($image_p, $this->getAbsolutePathFile(), 0)) {
                        $this->setUploadError("PERMISSION DENIED [PNG]: ".$this->getAbsolutePathFile());
                    }
                    break;
            }
            imagedestroy($image_p);
            return $this->insertLog();
        }
        return false;
    }
    public function setUploadError($error) {
        $this->uploadError = $error;
    }
    public function getUploadError() {
        return $this->uploadError;
    }
    public function setExtensionWhiteList($ext) {
        $this->ExtensionWhiteList[]=$ext;
    }
    public function downloadFile($fileID, $checkSec = true, $sendError = true) {
        $this->getControllerAccess();
        $this->getModel();
        $this->model->getModelFile();

        $download=false;
        $result = $this->getFileInfo($fileID);
        if ($result !== false) {
            $download=true;

        } else {
            $download=false;
            if ($sendError===true)$this->sendError("[".get_class($this)."::".__FUNCTION__."::".__LINE__."] No existen datos del fichero ID ".$fileID);
        }
        if ($checkSec===true) {
            /* Si el usuario tiene permisos de administrador puede descargar todos los ficheros */
            if ($this->access->checkLevelRolFact($this->access->getSessionSec(), ROL_ADMIN_N)) {
                $download=true;
                $this->setWorkingFile($result['FILENAME']);
                $this->setWorkingPath($result['PATH']);
            /* Si no los tiene, sólo puede descargarse los ficheros con su misma ID en FK_USER, es decir, los que ha subido el mismo */
            } elseif ($this->access->getSessionID()==$result['FK_USER']) {
                $download=true;
            } else {
                $download=false;
                if ($sendError===true)$this->sendError("[".get_class($this)."::".__FUNCTION__."::".__LINE__."] No tiene permisos para el fichero ".$fileID);
            }
        }
        if ($download) {
            if (ini_get('zlib.output_compression'))
                ini_set('zlib.output_compression', 'Off');
            $reader = fopen($this->getAbsolutePathFile(), "r");
            $contents = fread($reader, filesize($this->getAbsolutePathFile()));
            header('Content-Description: File Transfer');
            header('Content-Type: '.$result['MIME_TYPE']);
            header('Content-Disposition: attachment; filename="' . ($this->getWorkingFile()) . '"');
            header('Content-Transfer-Encoding: binary');
            header('Expires: 0');
            header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
            header('Pragma: public');
            header('Content-Length: ' . filesize($this->getAbsolutePathFile()));
            ob_end_clean();
            echo $contents;
            die();
        }

    }
    public function getMimeType($file) {
        //echo "FILE!!!! ".$file." MIME!!!!! ".$this->mime_content_type($file);
        return $this->mime_content_type($file);
    }
    public function mime_content_type($filename) {
        $mime_types = array(
            'txt' => 'text/plain',
            'htm' => 'text/html',
            'html' => 'text/html',
            'php' => 'text/html',
            'css' => 'text/css',
            'js' => 'application/javascript',
            'json' => 'application/json',
            'xml' => 'application/xml',
            'swf' => 'application/x-shockwave-flash',
            'flv' => 'video/x-flv',
            'epub'=> 'application/epub+zip',
            // images
            'png' => 'image/png',
            'jpe' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'jpg' => 'image/jpeg',
            'gif' => 'image/gif',
            'bmp' => 'image/bmp',
            'ico' => 'image/vnd.microsoft.icon',
            'tiff' => 'image/tiff',
            'tif' => 'image/tiff',
            'svg' => 'image/svg+xml',
            'svgz' => 'image/svg+xml',
            // archives
            'zip' => 'application/zip',
            'rar' => 'application/x-rar-compressed',
            'exe' => 'application/x-msdownload',
            'msi' => 'application/x-msdownload',
            'cab' => 'application/vnd.ms-cab-compressed',
            // audio/video
            'mp3' => 'audio/mpeg',
            'qt' => 'video/quicktime',
            'mov' => 'video/quicktime',
            // adobe
            'pdf' => 'application/pdf',
            'psd' => 'image/vnd.adobe.photoshop',
            'ai' => 'application/postscript',
            'eps' => 'application/postscript',
            'ps' => 'application/postscript',
            // ms office
            'doc' => 'application/msword',
            'rtf' => 'application/rtf',
            'xls' => 'application/vnd.ms-excel',
            'ppt' => 'application/vnd.ms-powerpoint',
            // open office
            'odt' => 'application/vnd.oasis.opendocument.text',
            'ods' => 'application/vnd.oasis.opendocument.spreadsheet',
        );
        $ext = $this->getExtension($filename);
        //echo "<br> EXT!!! ".$ext."<br>";
        if (array_key_exists($ext, $mime_types)) {
            return $mime_types[$ext];
        } elseif (function_exists('finfo_open')) {
            $finfo = finfo_open(FILEINFO_MIME);
            $mimetype = finfo_file($finfo, $filename);
            finfo_close($finfo);
            return $mimetype;
        } else {
            return 'application/octet-stream';
        }
    }
    public function getExtension($file) {
        return pathinfo($file, PATHINFO_EXTENSION);
    }
    public function setWorkingFile($filename) {
        $this->getControllerSession();
        $this->session->set('workingFile', $filename);
        $this->workingFile=$filename;
    }
    public function getWorkingFile() {
        $this->getControllerSession();
        if (strlen($this->workingFile)==0)$this->workingFile = $this->session->get('workingFile');
        return $this->workingFile;
    }
    public function setWorkingFileID($fileID) {
        $this->getControllerSession();
        $this->session->set('workingFileID', $fileID);
        $this->workingFileID=$fileID;
    }
    public function getWorkingFileID() {
        $this->getControllerSession();
        if (strlen($this->workingFileID)==0)$this->workingFileID = $this->session->get('workingFileID');
        return $this->workingFileID;
    }
    public function setWorkingPath($relativePath) {
        $this->workingPath=$relativePath;
    }
    public function getWorkingPath() {
        return $this->workingPath;
    }
    public function checkRelativePath($sendError = false) {
        if (is_dir($this->workingPath)) {
            return true;
        } else {
            if ($sendError===true)$this->sendError("[".get_class($this)."::".__FUNCTION__."::".__LINE__."] No existe el directorio ".$this->workingPath);
            return false;
        }
    }
    /**
     * checkAbsolutePath
     * Devuelve la ruta absoluta y el nombre del fichero sobre el que estamos trabajando
     * @return string / error
     */
    public function checkAbsolutePath($sendError = false) {
        if (is_dir(OLIF_ROOT.$this->workingPath)) {
            return true;
        } else {
            if ($sendError===true)$this->sendError("[".get_class($this)."::".__FUNCTION__."::".__LINE__."] No existe el directorio ".OLIF_ROOT.$this->workingPath);
            return false;
        }
    }
    public function getAbsolutePathFile() {
        return ($this->checkAbsolutePath(true))? OLIF_ROOT.$this->workingPath.$this->workingFile:false;
    }

    /**
     * readCsv
     *
     */
    public function readCsv($filename = "", $delimiter = ';', $enclosure = '"') {
        $list_array=array();
        $row=0;
        ini_set('auto_detect_line_endings', true);
        if (is_file(OLIF_ROOT.$filename)) {
            if (($handle = fopen(OLIF_ROOT.$filename, "r")) !== false) {
                while (($data = fgetcsv($handle, 0, $delimiter, $enclosure)) !== false) {
                    $num = count($data);
                    $row++;
                    for ($c=0; $c < $num; $c++) {
                        $data[$c];
                    }
                    $list_array[]=$data;
                }
                fclose($handle);
            }
            return $list_array;
        } else {
            //echo "NO FILE";
            return false;
        }

    }
}
