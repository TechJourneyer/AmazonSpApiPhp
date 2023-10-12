<?php 

require_once 'AmazonSpApi.php';
class Catalog extends AmazonSpApi{
    public $catalog_section = "/catalog/2020-12-01/";

    
    public function getCatalogItem($asin){
        $api_endpoint = $this->marketplace_url . $this->catalog_section . 'items/'.$asin;
        $access_token_response = $this->getAccessToken();
        $queryParams['marketplaceIds'] = implode(',', $this->marketplace_ids);
        $urlWithQuery = $api_endpoint . '?' . http_build_query($queryParams);

        if($access_token_response['success']){
            $access_token = $access_token_response['output']['access_token'];

            $headers = array(
                'x-amz-access-token: ' . $access_token,
                'content-type: application/json',
            );
            $response = $this->send_curl_request($urlWithQuery , "GET" , $headers );
            if($response){
                $responseData = json_decode($response, true);
                return $this->response(true,$responseData);
            }
            $this->log($this->lastCurlError);
            return $this->response(false,null,$this->lastCurlError);
        }
        return $this->response(false,null,$access_token_response['error_msg']);
    }
    
}