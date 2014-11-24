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

require_once CORE_ROOT . CONTROLLERS . DIRECTORY_SEPARATOR . "ControllerApp.php";

class ModelFile extends ModelApp
{

    /**
     * __construct
     * Instanciamos el objeto de base de datos ($db) para atacar a la tabla
     */
    public function __construct()
    {
        parent::__construct();
        $this->getModelDB();
    }

    /**
     * insertFile
     * Instanciamos el objeto de base de datos ($db) para atacar a la tabla
     */
    public function insertLog($file, $path, $mime_type, $ip, $userID, $node_root)
    {
        $this->db->setAction('I');
        $this->db->setTable(TABLE_FILES);
        $this->db->setIUField('FK_USER', $userID);
        $this->db->setIUField('IP', $ip);
        $this->db->setIUField('PATH', $path);
        $this->db->setIUField('FILENAME', $file);
        $this->db->setIUField('MIME_TYPE', $mime_type);
        $this->db->setIUField('NODE_ROOT', $node_root);
        $result = $this->db->executeCommand();
        $this->db->setLastID($result['ID']);
        return ($result !== false) ? $this->db->getLastID() : false;
    }

    public function updateLog($fileID, $ip, $userID)
    {
        $this->db->setAction('U');
        $this->db->setTable(TABLE_FILES);
        $this->db->setCond('ID', $fileID);
        $this->db->setIUField('FK_USER', $userID);
        $this->db->setIUField('IP', $ip);
        $this->db->setIUField('LAST_UPDATE', date("Y-m-d H:i:s"));
        $result_op = $this->db->executeCommand();
        return ($result_op !== false) ? $fileID : false;
    }

    public function updateURLGoogleDrive($fileID, $URLGoogleDrive)
    {
        $this->db->setAction('U');
        $this->db->setTable(TABLE_FILES);
        $this->db->setCond('ID', $fileID);
        $this->db->setIUField('GOOGLE_DRIVE_URL', $URLGoogleDrive);
        $this->db->setIUField('LAST_UPDATE', date("Y-m-d H:i:s"));
        $result_op = $this->db->executeCommand();
        return ($result_op !== false) ? true : false;
    }

    public function updateIDGoogleDrive($fileID, $IdGoogleDrive)
    {
        $this->db->setAction('U');
        $this->db->setTable(TABLE_FILES);
        $this->db->setCond('ID', $fileID);
        $this->db->setIUField('FK_GOOGLE_DRIVE', $IdGoogleDrive);
        $this->db->setIUField('LAST_UPDATE', date("Y-m-d H:i:s"));
        $result_op = $this->db->executeCommand();
        return ($result_op !== false) ? true : false;
    }

    /**
     * getFileInfo
     * Instanciamos el objeto de base de datos ($db) para atacar a la tabla
     */
    public function getFileInfo($fileID)
    {
        $result = $this->db->query(TABLE_FILES_FIELDS, TABLE_FILES . " F", "", "F.ID = ?", "", array(
            $fileID
        ));
        return (count($result) == 1) ? $result[0] : false;
    }

    /**
     * getFileInfo
     * Instanciamos el objeto de base de datos ($db) para atacar a la tabla
     */
    public function getFileInfoByUserAndFileName($userID, $fileName)
    {
        $result = $this->db->query(TABLE_FILES_FIELDS, TABLE_FILES . " F", "", "F.FK_USER = ? AND F.FILENAME = ?", "", array(
            $userID,
            $fileName
        ));
        return (count($result) == 1) ? $result[0] : false;
    }
}
