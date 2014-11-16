Installation
============

* In your conf/constantProjects:

.. sourcecode:: php

    /* *****************
     * SQL connection
    * ****************/
    define("DDBBTYPE"               ,   "MYSQL"); //OPTIONS: APPENGINE, MYSQL   
    define("DBHOST"                 ,   "localhost");
    define("DBUSER"                 ,   "olif_user");
    define("DBPASS"                 ,   "test1234");
    define("DBNAME"                 ,   "olif_example");
    define("DBSOCKET"               ,   "");
    
* In your conf/pathProject.php:

.. sourcecode:: php
    
    if(!defined("WEB_PATH_RELATIVE")) define("WEB_PATH_RELATIVE","/OLIF_SYSTEM_GIT/");
