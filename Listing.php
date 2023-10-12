<?php 

require_once 'AmazonSpApi.php';
class Listing extends AmazonSpApi{
    public $listing_section = "/listings/2021-08-01/";

    
    public function getListingsItem($seller_id,$sku){
        $api_endpoint = $this->marketplace_url . $this->listing_section . 'items/'.$seller_id."/".$sku;
        $access_token_response = $this->getAccessToken();
        $queryParams['marketplaceIds'] = implode(',', $this->marketplace_ids);
        $queryParams['issueLocale'] = "en_US";

        $urlWithQuery = $api_endpoint . '?' . http_build_query($queryParams);
        var_dump($urlWithQuery);
        if($access_token_response['success']){
            $access_token = $access_token_response['output']['access_token'];

            $headers = array(
                'x-amz-access-token: ' . $access_token,
                'content-type: application/json',
            );
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $urlWithQuery);
            // curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

            if(!$this->ssl_verification){
                curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
            }
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

            $response = curl_exec($ch);

            if (curl_errno($ch)) {
                $error = curl_error($ch);
                $this->log($error);
                return $this->response(false,null,$error);
            }
        
            curl_close($ch);
        
            $responseData = json_decode($response, true);
            return $this->response(true,$responseData);
        }
        return $this->response(false,null,$access_token_response['error_msg']);
    }
    
}