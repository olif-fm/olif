<?php
namespace Olif;

interface Ioauth {
    public function __construct($clientId, $clientSecret);
    public function setClientId($clientId);
    public function setClientSecret($clientSecret);
    public function setRedirectUri($URI);
    public function setConnectionURL($scopes);
    public function checkConnection($token);
    public function setConnection($code);
    public function getUserInfo();
}
