<?php 

class AmazonSpApi{
    
    private $client_id;
    private $client_secret;
    private $refresh_token;
    private $token_endpoint = "https://api.amazon.com/auth/o2/token";
    public  $lastCurlHttpCode;
    public  $lastCurlHeaders;
    public  $marketplace_url;
    public  $marketplace_ids = [];    
    public  $log_filepath = false;
    public  $ssl_verification = true;

    function __construct($client_id,$client_secret,$refresh_token){
        $this->client_id = $client_id;
        $this->client_secret = $client_secret;
        $this->refresh_token = $refresh_token;
    }

    public function clearCurlResponse()
    {
        $this->lastCurlError = null;
        $this->lastCurlResult = null;
        $this->lastCurlHttpCode = null;
        $this->lastCurlHeaders = null;
    }
    public function setCurlResponse($result,$httpCode,$headers)
    {
        $this->lastCurlResponse = $result;
        $this->lastCurlHttpCode = $httpCode;
        $this->lastCurlHeaders = $headers;
    }

    public function setCurlError($error)
    {
        $this->lastCurlError = $error;
    }

    public function send_curl_request($url, $method = 'GET', $headers = [], $data = null)
    {
        $this->clearCurlResponse();

        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, strtoupper($method));

        if (!empty($headers)) {
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        }

        if ($data !== null) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        }
        if (!$this->ssl_verification) {
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        }
        curl_setopt($ch, CURLOPT_HEADER, true); // Include headers in the response

        $response = curl_exec($ch);
        // var_dump($response);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        list($op_headers, $body) = explode("\r\n\r\n", $response, 2);
        // Convert the headers into an array
        $headerLines = explode("\r\n", $op_headers);
        
        
        if (curl_errno($ch)) {
            $this->setCurlError(curl_error($ch));
            curl_close($ch);
            return false;
        }

        curl_close($ch);
        $this->setCurlResponse($body,$httpCode,$headerLines);

        return $body;
    }

    function setMarketplaceId($marketplace_id){
        $this->marketplace_ids[] =  $marketplace_id;
        $this->marketplace_ids = array_unique($this->marketplace_ids);
    }

    function set_marketplace_url($url){
        $this->marketplace_url = $url;
    }

    function disable_ssl_verification(){
        $this->ssl_verification = false;
    }

    function setMarketplaceIds($marketplace_ids){
        $this->marketplace_ids =  $marketplace_ids;
    }

    function getAccessToken(){
        $headers =  [
            "cache-control: no-cache",
            "content-type: application/x-www-form-urlencoded"
        ];
        $response = $this->send_curl_request($this->token_endpoint , "POST" , $headers , "grant_type=refresh_token&refresh_token={$this->refresh_token}&client_id={$this->client_id}&client_secret={$this->client_secret}");
        if($response){
            $data = json_decode($response, true);
            if (isset($data['access_token'])) {
                return $this->response(true,$data);
            }
            $error = $data['error'];
            $error_description = $data['error_description'];
            $error_msg = " $error : $error_description";
            $this->log($error);
            return $this->response(false,$data,$error_msg);
        }
        return $this->response(false,[],$this->lastCurlError);
    }

    function enableLogs($log_filepath){
        $this->log_filepath = $log_filepath;
    } 

    function log($message){
        $timestamp = date('Y-m-d H:i:s');
        $logEntry = "[$timestamp] $message" . PHP_EOL;
        if($this->log_filepath!==false){
            $file = @fopen($this->log_filepath, 'a');
            if ($file) {
                fwrite($file, $logEntry);
                fclose($file);
            } 
        }
    }

    function response($success,$output=null,$error_message=""){
        return [
            'success' => $success,
            'output' => $output,
            'error_msg' => $error_message,
        ];
    }
}