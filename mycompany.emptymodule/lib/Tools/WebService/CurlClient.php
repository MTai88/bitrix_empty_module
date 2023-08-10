<?php

namespace Mycompany\EmptyModule\Tools\WebService;

class CurlClient {
  /**
   * @var Curl curl handler for requests
   */
  private $_ch;
  
  /**
   * @var string url for the http request
   */
  private $_url;
  
  /**
   * @var string user agent for the http request
   */
  private $_userAgent;

  /**
   * @var array http response with body and header including http_code
   */  
  private $_response = array();
  
  /**
   * @var string file path for the cookie file
   */
   private $_cookieFile;

  /**
   * Getter function for the object
   * @param string name
   * @return mixed
   */
  public function __get($name)
  {
      if (array_key_exists($name, $this->_response)) {
        return $this->_response[$name];
      }
      return null;
  }

  /**
   * Constructor
   * @param string url
   * @param string userAgent
   */  
  public function __construct($url = null, $userAgent = 'RestHttpClient/curl PHP 5.x') {
    $this->_openCurl();
    $this->setUrl($url);
    $this->setUserAgent($userAgent);
  }
  
  public function __destruct() {
    $this->_closeCurl();
  }
  
  /**
   * Get request url
   * @return string
   */  
  public function getUrl() {
    return $this->_url;
  }

  /**
   * Set request url
   * @param string value
   */  
  public function setUrl($value) {
    $this->_url = $value;
  }

  /**
   * Get request user agent
   * @return string
   */
  public function getUserAgent() {
    return $this->_userAgent;
  }

  /**
   * Set request user agent
   * @param string value
   */  
  public function setUserAgent($value) {
    $this->_userAgent = $value;
    curl_setopt($this->_ch, CURLOPT_USERAGENT, $this->useragent);
  }

  /**
   * Get request cookie file
   * @return string
   */
  public function getCookieFile() {
    return $this->_cookieFile;
  }

  /**
   * Set request cookie file
   * @param string value
   */  
  public function setCookieFile($value) {
    $this->_cookieFile = $value;
    curl_setopt ( $this->_ch, CURLOPT_COOKIEFILE, $value );
    curl_setopt ( $this->_ch, CURLOPT_COOKIEJAR, $value );
  }
  
  /**
   * GET request 
   * @param string $path
   * @param array $header
   * @param array $params
   * @return array
   */
  public function get($path, $header = null, $params = null) {
    curl_setopt($this->_ch, CURLOPT_CUSTOMREQUEST, 'GET');
    curl_setopt($this->_ch, CURLOPT_HTTPGET, TRUE);
    $this->_initHttpHeader($header);
    $this->_initUrl($path, $params);
    $this->_runCurl();
    return $this->_response;
  }

  /**
   * POST request 
   * @param string $path
   * @param array $header
   * @param array|string $params
   * @return array
   */  
  public function post($path, $header = null, $params = null) {
    curl_setopt($this->_ch, CURLOPT_CUSTOMREQUEST, 'POST'); 
    curl_setopt($this->_ch, CURLOPT_POST, TRUE);
    $this->_initPostFields($params);
    $this->_initHttpHeader($header);
    $this->_initUrl($path);
    $this->_runCurl();
    return $this->_response;
  }

  /**
   * PUT request 
   * @param string $path
   * @param array $header
   * @param array|string $params
   * @return array
   */  
  public function put($path, $header = null, $params = null) {
    curl_setopt($this->_ch, CURLOPT_CUSTOMREQUEST, 'PUT');
    $this->_initPostFields($params);
    $this->_initHttpHeader($header);
    $this->_initUrl($path);
    $this->_runCurl();
    return $this->_response;
  }
  

  /**
   * PATCH request 
   * @param string $path
   * @param array $header
   * @param array|string $params
   * @return array
   */  
  public function patch($path, $header = null, $params = null) {
    curl_setopt($this->_ch, CURLOPT_CUSTOMREQUEST, 'PATCH');
    $this->_initPostFields($params);
    $this->_initHttpHeader($header);
    $this->_initUrl($path);
    $this->_runCurl();
    return $this->_response;
  }

  /**
   * DELETE request 
   * @param string $path
   * @param array $header
   * @param array $params
   * @return array
   */  
  public function delete($path, $header = null, $params = null) {
    curl_setopt($this->_ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
    $this->_initPostFields($params);
    $this->_initHttpHeader($header);
    $this->_initUrl($path);
    $this->_runCurl();
    return $this->_response;
  }
  
  
  private function _openCurl() {
    $this->_ch = curl_init();
    curl_setopt($this->_ch, CURLOPT_HEADER, TRUE);
    curl_setopt($this->_ch, CURLOPT_RETURNTRANSFER, TRUE);
  }
  
  private function _closeCurl() {
    curl_close($this->_ch);
  }
  
  private function _runCurl() {
    $response   = curl_exec($this->_ch);
    $headerSize = curl_getinfo($this->_ch, CURLINFO_HEADER_SIZE);
    $this->_response['http_code'] = curl_getinfo($this->_ch, CURLINFO_HTTP_CODE);
    $this->_response['header'] = substr($response, 0, $headerSize);
    $this->_response['body'] = substr($response, $headerSize);
  }

  private function _initUrl($path, $params = null) {
    if ($params) {
      curl_setopt($this->_ch, CURLOPT_URL, "{$this->_url}{$path}?" . http_build_query($params));
    } else {
      curl_setopt($this->_ch, CURLOPT_URL, "{$this->_url}{$path}");
    }
  }
  
  private function _initHttpHeader($header) {
    if ($header) {
      curl_setopt($this->_ch, CURLOPT_HTTPHEADER, $header);
    }
  }
  
  private function _initPostFields($params = null) {
    if (is_array($params)) {
      curl_setopt($this->_ch, CURLOPT_POSTFIELDS, $params);
    } elseif ($params!=null) {
      curl_setopt($this->_ch, CURLOPT_POSTFIELDS, '@' . $params);
    } else {
      curl_setopt($this->_ch, CURLOPT_POSTFIELDS, $params);
    }
  }
}