<?php
require_once "phing/Task.php";
class UnfuddleBaseTask extends Task {
    const URL_TEMPLATE_UPDATE = 'http://%s.unfuddle.com/api/v1/';
    // Twitter response codes
    const HTTP_RESPONSE_OK                  = 200;
    const HTTP_RESPONSE_CREATED             = 201;
    const HTTP_RESPONSE_BAD_REQUEST         = 400;
    const HTTP_RESPONSE_BAD_CREDENTIALS     = 401;
    const HTTP_RESPONSE_BAD_URL             = 404;
    const HTTP_RESPONSE_METHOD_NOT_ALLOWED  = 405;
    const HTTP_RESPONSE_SERVER_ERROR        = 500;
    const HTTP_RESPONSE_BAD_GATEWAY         = 502;
    const HTTP_RESPONSE_SERVICE_UNAVAILABLE = 503;

    protected static $responseMessages = array(
        self::HTTP_RESPONSE_BAD_REQUEST         => 'Bad request - you may have exceeded the rate limit',
        self::HTTP_RESPONSE_BAD_CREDENTIALS     => 'Your username and password did not authenticate',
        self::HTTP_RESPONSE_BAD_URL             => 'The Unfuddle URL is invalid',
        self::HTTP_RESPONSE_METHOD_NOT_ALLOWED  => 'The specified HTTP verb is not allowed',
        self::HTTP_RESPONSE_SERVER_ERROR        => 'There is a problem with the Unfuddle server',
        self::HTTP_RESPONSE_BAD_GATEWAY         => 'Unfuddle is either down or being upgraded',
        self::HTTP_RESPONSE_SERVICE_UNAVAILABLE => 'Unfuddle servers are refusing request',
    );

    protected $subdomain;
    protected $projectId;
    protected $username;
    protected $password;
    protected $title;
    protected $body;
    protected $categoryIds;
    protected $checkReturn = false;
    protected $apiURLAppend = 'projects/%d/messages';

    public function setSubdomain($subdomain) {
        $this->subdomain = $subdomain;
    }
    public function setProjectId($projectId) {
        $this->projectId = (int)$projectId;
    }
    public function setUsername($username) {
        $this->username = $username;
    }
    public function setPassword($password) {
        $this->password = $password;
    }
    public function setTitle($title) {
        $this->title = $title;
    }
    public function setBody($body) {
        $this->body = $body;
    }
    public function setCategoryId($categoryId) {
        $this->categoryIds = array((int)$categoryId);
    }
    public function setCategoryIds($categoryIdList) {
        $this->categoryIds = explode(",", $categoryIdList);
    }
    public function setCheckReturn($checkReturn) {
        $this->checkReturn = (boolean)$checkReturn;
    }

    public function init() {
        if (!extension_loaded('curl')) {
            throw new BuildException("Cannot update Unfuddle", "The cURL extension is not installed");
        }
    }

    public function main() {
        $this->validateProperties();
        $curlHandle = curl_init();
        curl_setopt($curlHandle, CURLOPT_URL, $this->getUpdateUrl());
        curl_setopt($curlHandle, CURLOPT_USERPWD, "$this->username:$this->password");
        curl_setopt($curlHandle, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curlHandle, CURLOPT_HTTPHEADER, array('Accept: application/xml', 'Content-type: application/xml'));
        curl_setopt($curlHandle, CURLOPT_POST, true);
        curl_setopt($curlHandle, CURLOPT_POSTFIELDS, $this->getRequestBodyXml());
        $responseData = curl_exec($curlHandle);
        $responseCode = curl_getinfo($curlHandle, CURLINFO_HTTP_CODE);
        $errorCode    = curl_errno($curlHandle);
        $errorMessage = curl_error($curlHandle);
        curl_close($curlHandle);

        if (0 != $errorCode) {
            throw new BuildException("cURL error ($errorCode): $errorMessage");
        }
        $this->handleResponseCode((int)$responseCode);
    }
    protected function validateProperties() {
        if (!$this->subdomain) {
            throw new BuildException("You must specify a subdomain");
        }
        if (!$this->projectId) {
            throw new BuildException("You must specify a project id");
        }
        if (!$this->username || !$this->password) {
            throw new BuildException("You must specify an Unfuddle username and password");
        }
        if (!$this->title) {
            throw new BuildException("You must specify a message title");
        }
    }
    protected function getUpdateUrl() {
        return sprintf(self::URL_TEMPLATE_UPDATE.$this->apiURLAppend, $this->subdomain, $this->projectId);
    }
    protected function handleFailedUpdate($failureMessage) {
        if (true === $this->checkReturn) {
            throw new BuildException($failureMessage);
        }
        $this->log($this->failMessageIntro.' : '.$failureMessage, Project::MSG_WARN);
    }
    protected function handleResponseCode($code) {
        if ($code == self::HTTP_RESPONSE_CREATED) {
            $this->log($this->successMessageIntro.' : '.$this->title, Project::MSG_INFO);
            return;
        }
        if (array_key_exists($code, self::$responseMessages)) {
            $this->handleFailedUpdate(self::$responseMessages[$code]);
        } else {
            $this->handleFailedUpdate("Unrecognised HTTP response code '$code' from Unfuddle");
        }
    }
}
