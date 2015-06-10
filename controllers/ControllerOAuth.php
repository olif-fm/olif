<?php
/**
 * ControllerOauth
 * @version V 0.7
 * @copyright Alberto Vara (C) Copyright 2014
 * @package OLIF.ControllerOauth
 */
namespace Olif;

require_once CORE_ROOT.LIBS.DIRECTORY_SEPARATOR."Ioauth.php";

require_once CORE_ROOT.CONTROLLERS.DIRECTORY_SEPARATOR."ControllerApp.php";

class ControllerOAuth extends ControllerApp {
    /**
     * scopes
     * array de todos los módulos que hay que llamar
     */
    public $scopes=array();
    /**
     * tokenSessionName
     * nombre de la variable de sesión donde se guarda el token para la conexión OpenId
     */
    public $tokenSessionName = "OPENID_TOKEN";
    /**
     *  init
     *  Inicializa el cliente de Google Básico para conectar con todas las APIS
     *   */
    public function init($application, $URI = "") {
        $this->getControllerSession();
        $this->resetScopes();
        //$this->session->set($this->tokenSessionName, "");
        $this->setApplication($application, $URI);
    }
    public function setApplication($application, $URI) {
        $this->getControllerAccess();

        switch ($application) {
            case 'google':
                if (@$_SESSION['SESSION_OAUTH_APP']!='google') $this->logout();
                $this->oauth = new ControllerOauthGoogle();
                if (!defined("GOOGLE_IDCLIENT")) $this->sendError("[ERROR ".get_class($this)."::".__FUNCTION__."::".__LINE__."] No se ha definido <b>GOOGLE_IDCLIENT</b>.");
                $this->oauth->setClientId(GOOGLE_IDCLIENT);
                if (!defined("GOOGLE_SECRET")) $this->sendError("[ERROR ".get_class($this)."::".__FUNCTION__."::".__LINE__."] No se ha definido <b>GOOGLE_SECRET</b>.");
                $this->oauth->setClientSecret(GOOGLE_SECRET);
                $this->access->setFieldUserOpenId('GOOGLE_ACCOUNT');
                $this->session->set('SESSION_OAUTH_APP', $application);
                $this->setRedirectUri($URI, true);
                break;
            case 'facebook':
                if (!defined("FACEBOOK_IDCLIENT")) $this->sendError("[ERROR ".get_class($this)."::".__FUNCTION__."::".__LINE__."] No se ha definido <b>FACEBOOK_IDCLIENT</b>.");
                if (!defined("FACEBOOK_SECRET")) $this->sendError("[ERROR ".get_class($this)."::".__FUNCTION__."::".__LINE__."] No se ha definido <b>FACEBOOK_SECRET</b>.");
                $this->oauth = new ControllerOauthFacebook(FACEBOOK_IDCLIENT, FACEBOOK_SECRET);
                $this->oauth->setClientId(FACEBOOK_IDCLIENT);
                $this->oauth->setClientSecret(FACEBOOK_SECRET);
                $this->access->setFieldUserOpenId('FACEBOOK_ACCOUNT');
                $this->setRedirectUri($URI, false);
                break;
            case 'twitter':
                if (!defined("TWITTER_IDCLIENT")) $this->sendError("[ERROR ".get_class($this)."::".__FUNCTION__."::".__LINE__."] No se ha definido <b>TWITTER_IDCLIENT</b>.");
                if (!defined("TWITTER_SECRET")) $this->sendError("[ERROR ".get_class($this)."::".__FUNCTION__."::".__LINE__."] No se ha definido <b>TWITTER_SECRET</b>.");
                $this->oauth = new ControllerOauthTwitter(TWITTER_IDCLIENT, TWITTER_SECRET);
                $this->access->setFieldUserOpenId('TWITTER_ACCOUNT');
                $this->setRedirectUri($URI, false);
                break;
            case 'github':
                if (!defined("GITHUB_IDCLIENT")) $this->sendError("[ERROR ".get_class($this)."::".__FUNCTION__."::".__LINE__."] No se ha definido <b>GITHUB_IDCLIENT</b>.");
                if (!defined("GITHUB_SECRET")) $this->sendError("[ERROR ".get_class($this)."::".__FUNCTION__."::".__LINE__."] No se ha definido <b>GITHUB_SECRET</b>.");
                $this->oauth = new ControllerOauthGithub(GITHUB_IDCLIENT, GITHUB_SECRET);
                $this->oauth->setClientId(GITHUB_IDCLIENT);
                $this->oauth->setClientSecret(GITHUB_SECRET);
                $this->access->setFieldUserOpenId('GITHUB_ACCOUNT');
                $this->setRedirectUri($URI, false);
                break;
            case 'linkedin':
                echo "i es igual a 2";
                break;
            default:
                $this->sendError("[ERROR ".get_class($this)."::".__FUNCTION__."::".__LINE__."] No se ha definido una aplicación para OppenID.");
                break;
        }
        $this->application = $application;
    }
    /**
     *  init
     *  Inicializa el cliente de Google Básico para conectar con todas las APIS
     *   */
    public function addScope($scope) {
        if (!in_array($scope, $this->scopes))$this->scopes[]=$scope;
    }
    public function resetScopes() {
        $this->scopes=array();
    }
    public function checkScopes() {
        return (!is_array($this->scopes) || count($this->scopes)==0) ? false : true;
    }
    public function setRedirectUri($URI = "", $full_path = true) {
        if (strlen($URI)==0) {
            if ($full_path===true) {
                $url = PROTOCOL.DOMAIN.$_SERVER['REQUEST_URI'];
            } else {
                $url = $_SERVER['REQUEST_URI'];
            }
            $this->oauth->setRedirectUri();
        } else {
            if ($full_path===true) {
                $url = WEB_URL.$URI;
            } else {
                $url = $URI;
            }
            $this->oauth->setRedirectUri($url);
        }
    }
    /**
     * setConnectionURL
     * Genera una variable la variable GOOGLE_CONNECTION_URL si la conexión no se estableció
     */
    public function setConnectionURL($assignTPL = true) {
        $this->getControllerPage();
        if ($assignTPL===true) $this->page->assignVar(strtoupper($this->application).'_CONNECTION_URL', $this->oauth->setConnectionURL($this->scopes));
        else return $this->oauth->setConnectionURL($this->scopes);
    }
    /**
     * checkConnection
     *
     * Verifica que existe conexión con las APIS de Google. Si el Token se ha perdido vuelve a tratar de establecer conexión
     * @Return si FALSE: NULL
     * @Return si TRUE:
     * }
     */
    public function checkConnection() {
        if (strlen($this->getSessionToken())>0) {
            return $this->oauth->checkConnection($this->getSessionToken());
        } else {
            if ($this->setConnection()) {
                $this->oauth->checkConnection($this->getSessionToken());
                return true;
            } else {
                return false;
            }
        }
    }
    /**
     * setConnection
     * Si se recoge el código para autentificar, genera un nuevo token. Si no, trata de verificar la sesión guardada anteriormente y la vuelve a asignar
     * a la API de cliente de Google
     */
    public function setConnection() {
        $this->getControllerRequest();
        $this->tokenSession = $this->getSessionToken();
        $code=$this->req->getVar('code');
        $codeTT=$this->req->getVar('oauth_verifier');
        if (strlen($code)>0) {
            $token = $this->oauth->setConnection($code);
            $this->setSessionToken($token);
            return true;
        }if (strlen($codeTT)>0) {
            $token = $this->oauth->setConnection($codeTT);
            $this->setSessionToken($token);
            return true;
        } else {
            return false;
        }

    }
    public function sendConnection($application) {
        //$this->setApplication($application);
        $this->session->set('SESSION_OAUTH_APP', $application);
        header("Location: ".$this->setConnectionURL(false));

    }
    public function setSessionToken($token) {
        $this->session->set($this->tokenSessionName, $token);
    }
    public function getSessionToken() {
        //$this->getControllerSession();
        if (isset($_SESSION['SESSION_OAUTH_APP']) && $_SESSION['SESSION_OAUTH_APP']!='') {
            return $this->session->get($this->tokenSessionName);
        } else {
            return false;
        }
    }
    public function logout() {
        try {
            if (!isset($this->session))
                $this->getControllerSession();
            if ($_SESSION['SESSION_OAUTH_APP']!='') {
                $this->application = "";
                $this->session->set('SESSION_OAUTH_APP', "");

                $this->session->set($this->tokenSessionName, "");
                if (isset($this->oauth))
                    $this->oauth->logout();
            }
        } catch (Exception $e) {

        }

    }
    public function getUserInfo() {
        return $this->oauth->getUserInfo();
    }
    public function __call($method, $args) {
        return $this->oauth->$method($args);
    }
}


class ControllerOauthGoogle implements Ioauth{
    protected $userinfo;
    private $client;
    private $drive;
    public function __construct($clientId = "", $clientSecret = "") {
        set_include_path(get_include_path() . PATH_SEPARATOR . CORE_ROOT.THREEPARTY.'google-api-php-client/src');
        require_once 'Google/Client.php';
        require_once 'Google/Service/Oauth2.php';

        /************************************************
         Make an API request on behalf of a user. In
        this case we need to have a valid OAuth 2.0
        token for the user, so we need to send them
        through a login flow. To do this we need some
        information from our API console project.
        ************************************************/
        $this->client = new \Google_Client();
        if (strlen($clientId)>0) $this->setClientId($clientId);
        if (strlen($clientSecret)>0) $this->setClientSecret($clientSecret);

    }
    public function setClientId($clientId) {
        $this->client->setClientId($clientId);
    }
    public function setClientSecret($clientSecret) {
        $this->client->setClientSecret($clientSecret);
    }
    public function setRedirectUri($URI) {
        $this->client->setRedirectUri($URI);
    }
    public function setConnectionURL($scopes) {
        $this->client->setScopes($scopes);
        return $this->client->createAuthUrl();
    }
    public function checkConnection($token) {
        $this->client->setAccessToken($token);
        $this->service = new \Google_Service_Oauth2($this->client);
        $this->userinfo = $this->service->userinfo->get();
        return (is_object($this->userinfo))? true: false;
    }
    /**
     * setConnection
     * Si se recoge el código para autentificar, genera un nuevo token. Si no, trata de verificar la sesi�n guardada anteriormente y la vuelve a asignar
     * a la API de cliente de Google
     */
    public function setConnection($code) {
        $this->client->authenticate($code);
        return $this->client->getAccessToken();
    }
    public function getUserInfo() {
        return $this->userinfo;
    }
    public function logout() {
        $this->client->revokeToken($this->client->getAccessToken());
    }
    /* FUNCIONES ESPECÍFICAS DE GOOGLE DRIVE API*/
    public function initDriveService() {
        require_once 'Google/Service/Drive.php';
        $this->drive = new \Google_Service_Drive($this->client);
    }
    protected function checkDrive() {
        if (is_object($this->drive)) {
            return true;
        } else {
            $this->initDriveService();
            return true;
        }
    }
    public function insertFile($args) {
        $title = $args[0];
        $description = $args[1];
        $parentId = $args[2];
        //$mimeType = $args[3];
        $filename = $args[4];
        $this->checkDrive();
        $this->driveFile = new \Google_Service_Drive_DriveFile();
        $this->driveFile->setTitle($title);
        $this->driveFile->setDescription($description);
        // Set the parent folder.
        if ($parentId != null) {
            $parent = new \Google_ParentReference();
            $parent->setId($parentId);
            $this->driveFile->setParents(array($parent));
        }

        try {
            $data = file_get_contents($filename);
            $createdFile = $this->drive->files->insert($this->driveFile, array(
                    'data' => $data,
                    'uploadType' => 'multipart',
                    'mimeType' => 'application/vnd.ms-excel',
                    'pinned'    =>  true,
                    'convert' => true,
            ));
            if ($createdFile['id']!='') {
                return $createdFile['id'];
            } else {
                return false;
            }
        } catch (Exception $e) {
            print "An error occurred: " . $e->getMessage();
        }

    }
    public function checkFileExist($args) {
        $fileID = $args[0];

        if (strlen($fileID)>0) {
            if (function_exists('xdebug_disable'))xdebug_disable();
            try {

                return true;
            } catch (Google_ServiceException $e) {
                if ($e->getCode() == 404) {
                }
                return false;
            } catch (Exception $e) {
                if ($e->getCode() == 404) {
                    //print "The event doesn't exist";
                }
                return false;
            }
            if (function_exists('xdebug_enable'))xdebug_enable();
        } else {
            return false;
        }

    }
    public function getFile($fileID) {
        $this->checkDrive();
        return $this->drive->files->get($fileID);
    }
    public function updateFile($args) {
        $fileID = $args[0];
        $title = $args[1];
        $description = $args[2];
        $mimeType = $args[3];
        $newFileName = $args[4];
        $newRevision = $args[5];
        try {
            // File's new content.
            $this->checkDrive();
            $this->driveFile = new \Google_Service_Drive_DriveFile();
            $this->driveFile->setTitle($title);
            $this->driveFile->setDescription($description);
            $this->driveFile->setMimeType('application/vnd.google-apps.spreadsheet');

            $data = file_get_contents($newFileName);
            $additionalParams = array(
                    'newRevision' => $newRevision,
                    'data' => $data,
                    'mimeType' => $mimeType,
                    'uploadType' => 'multipart',
                    'pinned'    =>  true,
                    'convert' => true
            );

            // Send the request to the API.
            $updatedFile = $this->drive->files->update($fileID, $this->driveFile, $additionalParams);
            if ($updatedFile['id']!='') {
                return $updatedFile['id'];
            } else {
                return false;
            }
        } catch (Exception $e) {
            print "An error occurred: " . $e->getMessage();
        }
    }
}


class ControllerOauthFacebook implements Ioauth{
    protected $userinfo;
    private $client;
    protected $clientId;
    protected $clientSecret;
    protected $scopes;
    protected $likes = array();
    public function __construct($clientId, $clientSecret) {
        require_once(CORE_ROOT.THREEPARTY.DIRECTORY_SEPARATOR."facebook-php-sdk".DIRECTORY_SEPARATOR."src".DIRECTORY_SEPARATOR."facebook.php");
        $this->client = new \Facebook(array(
            'appId'  => $clientId,
            'secret' => $clientSecret,
        ));
    }
    public function setClientId($clientId) {
        $this->client->setAppId($clientId);
    }
    public function setClientSecret($clientSecret) {
        $this->client->setAppSecret($clientSecret);
    }
    public function setRedirectUri($URI) {
        $_SERVER['REQUEST_URI'] = $URI;
        //$this->client->setRedirectUri($URI);
    }
    /*REVISAR*/
    public function setConnectionURL($scopes) {
        $this->scopes = "";
        foreach ($scopes as $scope){
            if(strlen($this->scopes)>0){
                $this->scopes.=",";
            }
            $this->scopes.=$scope;
        }
        return $this->client->getLoginUrl(array(
            'scope' => $this->scopes,
            ));
    }
    public function checkConnection($token) {
        $user = $this->client->getUser();
        // We may or may not have this data based on whether the user is logged in.
        //
        // If we have a $user id here, it means we know the user is logged into
        // Facebook, but we don't know if the access token is valid. An access
        // token is invalid if the user logged out of Facebook.
        if ($user) {
            try {
                // Proceed knowing you have a logged in user who's authenticated.
                $this->userinfo = $this->client->api('/me');
            } catch (FacebookApiException $e) {
                error_log($e);
                $user = null;
            }
        }
        return (is_object($this->userinfo) || is_array($this->userinfo))? true: false;
    }
    public function getLikes($after) {
        $user = $this->client->getUser();
        if ($user) {
            try {
                // Proceed knowing you have a logged in user who's authenticated.
               if(is_array($after))$after = "";
               if (strlen($after)>0) $after= "&after=".$after;
               $URI = $this->userinfo['id']."/likes?fields=id&access_token=".$this->client->getAccessToken()."&limit=150".$after;
               $likes = $this->client->api($URI);
               $this->likes = array_merge($this->likes, $likes['data']);
               if(isset($likes['paging']['next'])){
                   $this->getLikes($likes['paging']['cursors']['after']);
               }
            } catch (FacebookApiException $e) {
                error_log($e);
                $user = null;
            }
         }
        return $this->likes;
    }
    /**
     * setConnection
     * Si se recoge el código para autentificar, genera un nuevo token. Si no, trata de verificar la sesión guardada anteriormente y la vuelve a asignar
     * a la API de cliente de Google
     */
    public function setConnection($code) {

        $user = $this->client->getUser();
        // We may or may not have this data based on whether the user is logged in.
        //
        // If we have a $user id here, it means we know the user is logged into
        // Facebook, but we don't know if the access token is valid. An access
        // token is invalid if the user logged out of Facebook.
        if ($user) {
            try {
                // Proceed knowing you have a logged in user who's authenticated.
                $this->userinfo = $this->client->api('/me');
            } catch (FacebookApiException $e) {
                error_log($e);
                $user = null;
            }
        }
        return $this->client->getAccessToken();
    }
    public function getUserInfo() {
        return $this->userinfo;
    }
    public function logout() {
        $this->client->destroySession();
    }
}


class ControllerOauthTwitter implements Ioauth{
    protected $userinfo;
    private $client;
    protected $reply;
    public function __construct($clientId = "", $clientSecret = "") {
        require_once(CORE_ROOT.THREEPARTY.DIRECTORY_SEPARATOR."codebird-php".DIRECTORY_SEPARATOR."src".DIRECTORY_SEPARATOR."codebird.php");
        \Codebird\Codebird::setConsumerKey($clientId, $clientSecret);

        /************************************************
         Make an API request on behalf of a user. In
        this case we need to have a valid OAuth 2.0
        token for the user, so we need to send them
        through a login flow. To do this we need some
        information from our API console project.
        ************************************************/
        $this->client = \Codebird\Codebird::getInstance();

    }
    public function setClientId($clientId) {
        //$this->client->setClientId($clientId);
    }
    public function setClientSecret($clientSecret) {
        //$this->client->setClientSecret($clientSecret);
    }
    public function setRedirectUri($URI) {
        $this->reply = $this->client->oauth_requestToken(array(
            'oauth_callback' => 'http://' . $_SERVER['HTTP_HOST'] . $URI
        ));
    }
    public function setConnectionURL($scopes = "") {
        // store the token
        $this->setToken(array($this->reply->oauth_token, $this->reply->oauth_token_secret));
        $_SESSION['oauth_token'] = $this->reply->oauth_token;
        $_SESSION['oauth_token_secret'] = $this->reply->oauth_token_secret;
        $_SESSION['oauth_verify'] = true;

        return $this->client->oauth_authorize();
    }
    public function checkConnection($token) {
        $this->setToken(array($token, $_SESSION['oauth_token_secret']));
        $this->userinfo = $this->client->account_verifyCredentials();
        return (is_object($this->userinfo))? true: false;
    }
    public function setToken($token) {
        //Livevar_dump($token);
        if(strlen($token[0]) > 0 && strlen($token[1]) > 0){
            $this->client->setToken($token[0], $token[1]);
        }
    }
    public function getHastag($params) {
        //https://dev.twitter.com/docs/api/1.1/get/statuses/user_timeline
        //https://dev.twitter.com/docs/api/1.1/get/search/tweets
        $q = $params[0];
        $count = $params[1];
        $params = array(
            'screen_name' => $q,
            'q' => $q,
            'count' => $count,
            'include_entities' => true,
            'result_type' => 'mixed'
        );
        var_dump($params);
        //Make the REST call
        $data = (array) $this->client->search_tweets($params);
        return $data;
    }
    public function getUsetTweets($params) {
        //https://dev.twitter.com/docs/api/1.1/get/statuses/user_timeline
        //https://dev.twitter.com/docs/api/1.1/get/search/tweets
        $q = $params[0];
        $count = $params[1];
        $params = array(
                'screen_name' => $q,
                'q' => $q,
                'count' => $count,
                'include_entities' => true,
                'result_type' => 'mixed'
        );
        var_dump($params);
        //Make the REST call
        $data = (array) $this->client->statuses_userTimeline($params);
        return $data;
    }
    /**
     * setConnection
     * Si se recoge el código para autentificar, genera un nuevo token. Si no, trata de verificar la sesión guardada anteriormente y la vuelve a asignar
     * a la API de cliente de Google
     */
    public function setConnection($code) {
        $this->setToken(array($_SESSION['oauth_token'], $_SESSION['oauth_token_secret']));
        unset($_SESSION['oauth_verify']);

        // get the access token
        $this->reply = $this->client->oauth_accessToken(array(
            'oauth_verifier' => $code
        ));

        // store the token (which is different from the request token!)
        $_SESSION['oauth_token'] = $this->reply->oauth_token;
        $_SESSION['oauth_token_secret'] = $this->reply->oauth_token_secret;
        return $this->reply->oauth_token;
    }
    public function getUserInfo() {
        return $this->userinfo;
    }
    public function logout() {
        unset($_SESSION['oauth_token']);
        unset($_SESSION['oauth_token_secret']);
        unset($_SESSION['oauth_verify']);
    }
}

class ControllerOauthGithub implements Ioauth{
    protected $userinfo;
    //private $client;
    protected $clientId;
    protected $clientSecret;
    protected $redirectUri;
    public function __construct($clientId = "", $clientSecret = "") {
        if (strlen($clientId)>0) $this->setClientId($clientId);
        if (strlen($clientSecret)>0) $this->setClientSecret($clientSecret);

    }
    public function setClientId($clientId) {
        $this->clientId = $clientId;
    }
    public function setClientSecret($clientSecret) {
        $this->clientSecret = $clientSecret;
    }
    public function setRedirectUri($URI) {
        $this->redirectUri = 'http://' . $_SERVER['HTTP_HOST'] . $URI;
    }
    public function setConnectionURL($scopes) {
        $listScopes = "";
        $sep = "";
        foreach ($scopes as $scope) {
            $listScopes = $sep.$scope;
            if (strlen($listScopes)>0) {
                $sep = ", ";
            }
        }
        return "https://github.com/login/oauth/authorize?client_id=".$this->clientId."&redirect_uri=".$this->redirectUri."&scope=".$listScopes."&";
    }
    public function checkConnection($token) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

        curl_setopt($ch, CURLOPT_URL, "https://api.github.com/user?access_token=".$token);
        curl_setopt($ch, CURLOPT_USERAGENT, "OLIFtest");

        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        $data = curl_exec($ch);
        if (curl_errno($ch)) {
            echo 'error:' . curl_error($ch);
        }
        $result = json_decode($data);
        $this->userinfo = $result;
        return (is_object($this->userinfo))? true: false;
    }
    /**
     * setConnection
     * Si se recoge el código para autentificar, genera un nuevo token. Si no, trata de verificar la sesión guardada anteriormente y la vuelve a asignar
     * a la API de cliente de Google
     */
    public function setConnection($code) {
        $ch = curl_init();
        $datapost = array(
            'client_id' =>  $this->clientId,
            'client_secret' =>  $this->clientSecret,
            'redirect_uri'  =>  $this->redirectUri,
            'code'  => $code,
        );
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_URL, "https://github.com/login/oauth/access_token");
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($datapost));
        $data = curl_exec($ch);
        if (curl_errno($ch)) {
            echo 'error:' . curl_error($ch);
        }
        parse_str($data, $result);
        return $result['access_token'];
    }
    public function getUserInfo() {
        return $this->userinfo;
    }
    public function logout() {
        //TODO
    }
}
