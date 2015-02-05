<?php

ini_set("display_errors", 1);
error_reporting(E_ALL);

/**
 * CURL function for PHP
 *
 * Use CURL-PHP to scrape websites, post data etc.
 * 
 * @author Rochelle <rochelle@xineoh.com>
 * @version v1.0.0
 * @todo Email notification when proxy is not working
 */

class Curl {
    
    /**
     * @var string $response_content Content after CURL execution
     */
    private $response_content = "";
    /**
     * @var int $response_http_code HTTP response code 
     */
    private $response_http_code = 0;
    
    /**
     * @var string $final_url Final URL after redirects 
     */
    private $final_url = "";
    /**
     *
     * @var string $error CURL error message 
     */
    private $error = 0;
    
    /**
     * URL to execute
     * @var string $request_url 
     */
    public $request_url = "GET";
    /**
     * Type of method : GET|POST|PUT|DELETE
     * @var string $request_method 
     */
    public $request_method;
    /**
     * JSON encoded string
     * @var json $request_data 
     */
    public $request_data;
    
    public $curl_error_codes = array(
        1 => "CURLE_UNSUPPORTED_PROTOCOL", 
        2 => "CURLE_FAILED_INIT", 
        3 => "CURLE_URL_MALFORMAT", 
        4 => "CURLE_URL_MALFORMAT_USER", 
        5 => "CURLE_COULDNT_RESOLVE_PROXY", 
        6 => "CURLE_COULDNT_RESOLVE_HOST", 
        7 => "CURLE_COULDNT_CONNECT", 
        8 => "CURLE_FTP_WEIRD_SERVER_REPLY",
        9 => "CURLE_REMOTE_ACCESS_DENIED",
        11 => "CURLE_FTP_WEIRD_PASS_REPLY",
        13 => "CURLE_FTP_WEIRD_PASV_REPLY",
        14 => "CURLE_FTP_WEIRD_227_FORMAT",
        15 => "CURLE_FTP_CANT_GET_HOST",
        17 => "CURLE_FTP_COULDNT_SET_TYPE",
        18 => "CURLE_PARTIAL_FILE",
        19 => "CURLE_FTP_COULDNT_RETR_FILE",
        21 => "CURLE_QUOTE_ERROR",
        22 => "CURLE_HTTP_RETURNED_ERROR",
        23 => "CURLE_WRITE_ERROR",
        25 => "CURLE_UPLOAD_FAILED",
        26 => "CURLE_READ_ERROR",
        27 => "CURLE_OUT_OF_MEMORY",
        28 => "CURLE_OPERATION_TIMEDOUT",
        30 => "CURLE_FTP_PORT_FAILED",
        31 => "CURLE_FTP_COULDNT_USE_REST",
        33 => "CURLE_RANGE_ERROR",
        34 => "CURLE_HTTP_POST_ERROR",
        35 => "CURLE_SSL_CONNECT_ERROR",
        36 => "CURLE_BAD_DOWNLOAD_RESUME",
        37 => "CURLE_FILE_COULDNT_READ_FILE",
        38 => "CURLE_LDAP_CANNOT_BIND",
        39 => "CURLE_LDAP_SEARCH_FAILED",
        41 => "CURLE_FUNCTION_NOT_FOUND",
        42 => "CURLE_ABORTED_BY_CALLBACK",
        43 => "CURLE_BAD_FUNCTION_ARGUMENT",
        45 => "CURLE_INTERFACE_FAILED",
        47 => "CURLE_TOO_MANY_REDIRECTS",
        48 => "CURLE_UNKNOWN_TELNET_OPTION",
        49 => "CURLE_TELNET_OPTION_SYNTAX",
        51 => "CURLE_PEER_FAILED_VERIFICATION",
        52 => "CURLE_GOT_NOTHING",
        53 => "CURLE_SSL_ENGINE_NOTFOUND",
        54 => "CURLE_SSL_ENGINE_SETFAILED",
        55 => "CURLE_SEND_ERROR",
        56 => "CURLE_RECV_ERROR",
        58 => "CURLE_SSL_CERTPROBLEM",
        59 => "CURLE_SSL_CIPHER",
        60 => "CURLE_SSL_CACERT",
        61 => "CURLE_BAD_CONTENT_ENCODING",
        62 => "CURLE_LDAP_INVALID_URL",
        63 => "CURLE_FILESIZE_EXCEEDED",
        64 => "CURLE_USE_SSL_FAILED",
        65 => "CURLE_SEND_FAIL_REWIND",
        66 => "CURLE_SSL_ENGINE_INITFAILED",
        67 => "CURLE_LOGIN_DENIED",
        68 => "CURLE_TFTP_NOTFOUND",
        69 => "CURLE_TFTP_PERM",
        70 => "CURLE_REMOTE_DISK_FULL",
        71 => "CURLE_TFTP_ILLEGAL",
        72 => "CURLE_TFTP_UNKNOWNID",
        73 => "CURLE_REMOTE_FILE_EXISTS",
        74 => "CURLE_TFTP_NOSUCHUSER",
        75 => "CURLE_CONV_FAILED",
        76 => "CURLE_CONV_REQD",
        77 => "CURLE_SSL_CACERT_BADFILE",
        78 => "CURLE_REMOTE_FILE_NOT_FOUND",
        79 => "CURLE_SSH",
        80 => "CURLE_SSL_SHUTDOWN_FAILED",
        81 => "CURLE_AGAIN",
        82 => "CURLE_SSL_CRL_BADFILE",
        83 => "CURLE_SSL_ISSUER_ERROR",
        84 => "CURLE_FTP_PRET_FAILED",
        84 => "CURLE_FTP_PRET_FAILED",
        85 => "CURLE_RTSP_CSEQ_ERROR",
        86 => "CURLE_RTSP_SESSION_ERROR",
        87 => "CURLE_FTP_BAD_FILE_LIST",
        88 => "CURLE_CHUNK_FAILED"
    );
    
    /**
     * @var array $user_agents
     */
    public $user_agents = array(
        "Mozilla/5.0 (Windows NT 6.1; WOW64; rv:13.0) Gecko/20100101 Firefox/13.0.1",
        "Mozilla/5.0 (Windows NT 6.3; rv:36.0) Gecko/20100101 Firefox/36.0",
        "Mozilla/5.0 (compatible, MSIE 11, Windows NT 6.3; Trident/7.0; rv:11.0) like Gecko",
        "Mozilla/5.0 (compatible; MSIE 10.0; Windows NT 6.1; WOW64; Trident/6.0)",
        "Mozilla/5.0 (X11; Linux x86_64; rv:17.0) Gecko/20121202 Firefox/17.0 Iceweasel/17.0.1",
        "Mozilla/5.0 (Macintosh; Intel Mac OS X 10_10_1) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/37.0.2062.124 Safari/537.36",
        "Mozilla/5.0 (Linux; U; Android 4.0.3; ko-kr; LG-L160L Build/IML74K) AppleWebkit/534.30 (KHTML, like Gecko) Version/4.0 Mobile Safari/534.30"        
    );
    
    /**
     *
     * @var array $proxies 
     */
    public $proxies = array(
        array("ip" => "198.71.85.73", "port" => "29842", "username" => "rbotha", "password" => "tcDHyW9L"),
        array("ip" => "192.80.185.184", "port" => "29842", "username" => "rbotha", "password" => "tcDHyW9L"),
        array("ip" => "104.251.85.166", "port" => "29842", "username" => "rbotha", "password" => "tcDHyW9L"),
        array("ip" => "192.80.131.102", "port" => "29842", "username" => "rbotha", "password" => "tcDHyW9L"),
        array("ip" => "198.71.85.128", "port" => "29842", "username" => "rbotha", "password" => "tcDHyW9L"),
        array("ip" => "192.80.185.106", "port" => "29842", "username" => "rbotha", "password" => "tcDHyW9L"),
        array("ip" => "104.251.85.157", "port" => "29842", "username" => "rbotha", "password" => "tcDHyW9L"),
        array("ip" => "192.80.131.8", "port" => "29842", "username" => "rbotha", "password" => "tcDHyW9L"),
        array("ip" => "198.71.85.104", "port" => "29842", "username" => "rbotha", "password" => "tcDHyW9L"),
        array("ip" => "192.80.185.46", "port" => "29842", "username" => "rbotha", "password" => "tcDHyW9L"),
        array("ip" => "104.251.85.124", "port" => "29842", "username" => "rbotha", "password" => "tcDHyW9L"),
        array("ip" => "192.80.131.163", "port" => "29842", "username" => "rbotha", "password" => "tcDHyW9L"),
        array("ip" => "198.71.85.213", "port" => "29842", "username" => "rbotha", "password" => "tcDHyW9L"),
        array("ip" => "192.80.185.142", "port" => "29842", "username" => "rbotha", "password" => "tcDHyW9L"),
        array("ip" => "104.251.85.180", "port" => "29842", "username" => "rbotha", "password" => "tcDHyW9L"),
        array("ip" => "192.80.131.28", "port" => "29842", "username" => "rbotha", "password" => "tcDHyW9L"),
        array("ip" => "198.71.85.14", "port" => "29842", "username" => "rbotha", "password" => "tcDHyW9L"),
        array("ip" => "192.80.185.11", "port" => "29842", "username" => "rbotha", "password" => "tcDHyW9L"),
        array("ip" => "104.251.85.172", "port" => "29842", "username" => "rbotha", "password" => "tcDHyW9L"),
        array("ip" => "192.80.131.219", "port" => "29842", "username" => "rbotha", "password" => "tcDHyW9L"),
        array("ip" => "198.71.85.182", "port" => "29842", "username" => "rbotha", "password" => "tcDHyW9L"),
        array("ip" => "192.80.185.128", "port" => "29842", "username" => "rbotha", "password" => "tcDHyW9L"),
    );
    
    /**
     *
     * @var type 
     */
    public $used_proxy = array();
    
    /**
     * Get HTTP code 200/404/500 etc.
     * @return int $response_http_code
     */
    function getHttpCode(){
        return $this->response_http_code;
    }
    
    /**
     * Get content from URL
     * @return string $response_content
     */
    function getContent(){
        return $this->response_content;
    }
    
    /**
     * Get final URL
     * @return string $final_url
     */
    public function getFinalURL(){
        return $this->final_url;
    }
    
    /**
     * Get error Message
     * @link [http://curl.haxx.se/libcurl/c/libcurl-errors.html] List containing all error codes & description
     * @return string $error
     */
    function getError(){
        return $this->error;
    }
    
    /**
     * 
     */
    function randomizeProxy(){
        return $this->proxies[4];//[mt_rand(0, count($this->proxies) - 1)];
    }
    /**
     * Select a random proxy
     */
    function getProxy(){
        return implode(":", $this->used_proxy);
    }
    
    /**
     * Check if proxies are working
     */
    function checkProxies(){
        
        $url = "http://www.episodeworld.com";
        echo 'asdf';
        foreach($this->proxies as $proxy){
            
            $curl = new Curl();
            $curl->execute($url, "GET", null, $proxy);
            
            $httpcode = $curl->getHttpCode();
            
            echo date("Y-m-d H:i:s : ")." {$httpcode} {$proxy['ip']} " . strlen($curl->getContent()) . "\n";
            
        }
        
    }
    
    /**
     * Execute URL and set all request parameters
     * @return void
     */
    function execute(){
        
        $ch = curl_init();
        
        /*
         * Set proxy settings
         */
        
        //$this->used_proxy = $this->randomizeProxy();
        
        $proxy = !empty($setProxy) ? $setProxy : $this->used_proxy;
        /*
        curl_setopt($ch, CURLOPT_PROXYPORT, $proxy['port']);
        curl_setopt($ch, CURLOPT_PROXYTYPE, 'HTTP');
        curl_setopt($ch, CURLOPT_PROXY, $proxy['ip']);
        curl_setopt($ch, CURLOPT_PROXYUSERPWD, $proxy['username'].":".$proxy['password']);
        */
        
        /*
         * Set default CURL settings
         */
        curl_setopt($ch, CURLOPT_USERAGENT, array_rand($this->user_agents));
		curl_setopt($ch, CURLOPT_URL, $this->request_url);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
		curl_setopt($ch, CURLOPT_ENCODING, "");
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_AUTOREFERER, true);
		curl_setopt($ch, CURLOPT_MAXREDIRS, 10);
        
        /**
         * Set different properties for different methods
         */
        if($this->request_method == "POST"){
            
            $postData = "";
            
            foreach( $this->request_data as $key => $val ) {
               $postData .=$key."=".$val."&";
            }
            
            $postData = rtrim($postData, "&");
            curl_setopt($ch, CURLOPT_POST, 1); 
            // set data
            curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);  
            
        } else if($this->request_method == "PUT"){
            
            // send json string to request
            curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json')); 
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT"); 
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($request_data));  // set data
            
        } else if($this->request_method == "DELETE"){
            /**
             * @todo DELETE method
             */
        } else {
            /**
             *  Do nothing 
             */
        }
        
        /*
         * Set URL content
         */
		$this->response_content = curl_exec($ch);
        
        /*
         * Set URL response code
         */
        $this->response_http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $this->final_url =  curl_getinfo($ch, CURLINFO_EFFECTIVE_URL);

        /*
         * Check for errors
         */
        if($errno = curl_errno($ch)) {
            /*
             * Set error message
             */
            $this->error = isset($this->curl_error_codes[$errno]) ? $errno . " - " . $this->curl_error_codes[$errno] : 0;
            
        }
        
        /*
         * Close CURL connection
         */

        curl_close($ch);
        
    }
    
}

$linebreak = "<br/>";

$curl = new Curl();
$curl->request_url = "http://www.episodeworld.com";
$curl->execute();

printf("Response Code : %s ,{$linebreak} Error Code : %s ,{$linebreak} Content-Length : %s {$linebreak}",
        $curl->getHttpCode(), 
        $curl->getError(), 
        strlen($curl->getContent()));

?>
