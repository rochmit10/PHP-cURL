<?php
/**
 * CURL function for PHP
 *
 * Use CURL-PHP to do REST requests, post data etc.
 * 
 * @author Rochelle <rochmit10@gmail.com>
 * @version v1.0.0
 * @todo use cookie else redirecting wont work in some cases
 * @todo login and save sessions
 */

class CurlRequest {
    
    private $handle = null;
    /**
     * @var string $responseContent Content after CURL execution
     * @access private
     */
    private $responseContent;
    /**
     * @var int $responseHttpCode HTTP response code 
     * @access private
     */
    private $responseHttpCode;
    /**
     * @var string $finalUrl Final URL after redirects 
     * @access private
     */
    private $finalUrl;
    /**
     * @var string $error CURL error message 
     * @access private
     */
    private $error;
    /**
     * URL to execute
     * @var string $requestUrl 
     * @access public
     */
    public $requestUrl;
    /**
     * Type of method to use 
     * @var string $requestMethod GET|POST|PUT|DELETE
     * @access public
     */
    public $requestMethod;
    /**
     * array of data to post
     * @var array $requestData 
     * @access public
     */
    public $requestData;
    /**
     * @var array $userAgents List of user agents to spoof your user agent, if you want
     * @todo Can be turned of
     * @access public
     */
    public $userAgents = array(
        "Mozilla/5.0 (Windows NT 6.1; WOW64; rv:13.0) Gecko/20100101 Firefox/13.0.1",
        "Mozilla/5.0 (Windows NT 6.3; rv:36.0) Gecko/20100101 Firefox/36.0",
        "Mozilla/5.0 (compatible, MSIE 11, Windows NT 6.3; Trident/7.0; rv:11.0) like Gecko",
        "Mozilla/5.0 (compatible; MSIE 10.0; Windows NT 6.1; WOW64; Trident/6.0)",
        "Mozilla/5.0 (X11; Linux x86_64; rv:17.0) Gecko/20121202 Firefox/17.0 Iceweasel/17.0.1",
        "Mozilla/5.0 (Macintosh; Intel Mac OS X 10_10_1) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/37.0.2062.124 Safari/537.36",
        "Mozilla/5.0 (Linux; U; Android 4.0.3; ko-kr; LG-L160L Build/IML74K) AppleWebkit/534.30 (KHTML, like Gecko) Version/4.0 Mobile Safari/534.30"        
    );
    
    /**
     * @var type $requestHeaders Default request headers
     * @access public
     */
    public $requestHeaders = array(
        "Content-Type" => "text/html; charset=utf-8",
        "Accept" => "*/*"
    );
    
    /**
     *
     * @var type cURL default options
     * @access public
     */
    public $defaultOptions = array(
        CURLOPT_USERAGENT => null,
        CURLOPT_URL => null,
        CURLOPT_HTTPHEADER => null,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_ENCODING => "",
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_AUTOREFERER => true,
        CURLOPT_MAXREDIRS => 10,
    );
    
    /**
     * Return HTTP code 200/404/500 etc.
     * @access public
     * @return int $responseHttpCode
     */
    public function getHttpCode(){
        return $this->responseHttpCode;
    }
    
    /**
     * Return content from URL
     * @access public
     * @return string $responseContent
     */
    public function getContent(){
        return $this->responseContent;
    }
    
    /**
     * Return final URL when pages redirect
     * @access public
     * @return string $finalUrl
     */
    public function getFinalURL(){
        return $this->finalUrl;
    }
    
    /**
     * Return error Message
     * @link http://curl.haxx.se/libcurl/c/libcurl-errors.html List containing all error codes & description
     * @access public
     * @return string $error
     * @todo return errors as an object
     */
    public function getError(){
        return (object) $this->error;
    }
    
    /**
     * Return the cURL info type
     * @param constant $name
     * @access public
     * @return string curl_info
     */
    public function getInfo($name){
        return curl_getinfo($this->handle, $name);
    }
    
    /**
     * Set curl options
     * @param constant $name
     * @param mixed $value
     * @access public
     * @link http://php.net/manual/en/function.curl-setopt.php
     */
    public function setOption($option, $value){
        curl_setopt($this->handle, $option, $value);
    }
    /**
     * 
     * @param string $option
     * @param string $value
     * @access public
     */
    public function setHeader($option, $value){
        $this->requestHeaders[$option] = $value;
    }

    /**
     * Optional : use cookie else redirecting wont work in some cases
     * @access public
     */
    public function setCookies(){
        $this->setOption(CURLOPT_COOKIEFILE, '/tmp/curl-session');
        $this->setOption(CURLOPT_COOKIEJAR, '/tmp/curl-session');
    }
    
    /**
     * Inititiate cURL
     */
    public function __construct(){
        $this->handle = curl_init();
    }
    
    /**
     * Execute URL and set all request parameters
     * @access public
     * @return void
     */
    public function execute(){

        // Set default option values
        $this->defaultOptions[CURLOPT_USERAGENT] = $this->userAgents[array_rand($this->userAgents)];
        $this->defaultOptions[CURLOPT_URL] = $this->requestUrl;
        // generate header arrays
        $this->defaultOptions[CURLOPT_HTTPHEADER] = 
                explode(', ', 
                        implode(', ', array_map(function ($v, $k) { 
                                            return $k . ': ' . $v; 
                                          }, 
                                          $this->requestHeaders, 
                                          array_keys($this->requestHeaders)
                                      ) 
                        ) 
                );
        
        /*
         *  set curl options
         */
        foreach($this->defaultOptions as $option => $value){
            $this->setOption($option, $value);
        }
        
        /*
         * Set different properties for different methods
         */
        if($this->requestMethod == "POST"){
            
            $postData = "";
            
            foreach( $this->requestData as $key => $val ) {
               $postData .=$key."=".$val."&";
            }
            
            $postData = rtrim($postData, "&");
            /*
             *  set data
             */
            $this->setOption(CURLOPT_POST, 1);
            $this->setOption(CURLOPT_POSTFIELDS, $postData);
            
        } else if($this->requestMethod == "PUT"){
            /*
             *  send json string to request
             */
            $this->setOption(CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
            $this->setOption(CURLOPT_CUSTOMREQUEST, "PUT");
            $this->setOption(CURLOPT_POSTFIELDS, json_encode($this->requestData));
            
        } else if($this->requestMethod == "DELETE"){
            /**
             * @todo DELETE method
             */
        } else {
            /* Do nothing */
            
        }
        
        /*
         * Set responseContent
         */
		$this->responseContent = curl_exec($this->handle);
        
        /*
         * Set URL response http code
         */
        $this->responseHttpCode = $this->getInfo(CURLINFO_HTTP_CODE);
        
        /*
         * Get final request url ( when urls redirect )
         */
        $this->finalUrl =  $this->getInfo(CURLINFO_EFFECTIVE_URL);
        
        /* 
         * check if response code is not 200
         */
        if($this->responseHttpCode != 200){
            /*
             * Set error message
             */
            $this->error[] = "cURL response code ({$this->responseHttpCode})";
            
        }
        
        /*
         * Check for errors and display the error message
         */
        if($errno = curl_errno($this->handle)) {
            /*
             * Set error message
             */
            $errorString = curl_error($this->handle);
            $this->error[] = "cURL error ({$errno}) : {$errorString}";
            
        }
        
        /*
         * Close CURL connection
         */
        curl_close($this->handle);
        
        /*
         * Return true/false depending on whether or not $error is empty
         */
        return empty($this->error) ? true : false;
        
    }
    
}

$curl = new CurlRequest();
$curl->requestUrl = "http://headers.jsontest.com/";
$curl->setHeader("Content-Type" , "application/json");

if($curl->execute()){
    echo $curl->getContent();
} else {
    echo $curl->getError();
}

?>
