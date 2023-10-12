<?php 

require_once 'AmazonSpApi.php';
class Products extends AmazonSpApi{

    public function getItemOffersBatch($asins,$marketplace_id,$item_condition){
        $api_endpoint = $this->marketplace_url . '/batches/products/pricing/v0/itemOffers';
        $requests = [];
        foreach ($asins as $key => $asin) {
            $request_uri = "products/pricing/v0/items/$asin/offers";
            $listing_offers_request = $this->createItemOffersRequest($request_uri,"GET",$marketplace_id,$item_condition="New");
            # code...
            $requests[] = $listing_offers_request;
        }
        $body = [
            "requests" => $requests
        ];
        $access_token_response = $this->getAccessToken();
        if($access_token_response['success']){
            $access_token = $access_token_response['output']['access_token'];

            $headers = array(
                'x-amz-access-token: ' . $access_token,
                'content-type: application/json',
            );
            $response = $this->send_curl_request($api_endpoint , "POST" , $headers ,json_encode($body) );
            if($response){
                $responseData = json_decode($response, true);
                return $this->response(true,$responseData);
            }
        }
        return $this->response(false,null,$this->lastCurlError);
    }

    public function getItemOffers($asin,$marketplace_id,$item_condition="New",$customer_type=null){
        $api_endpoint = $this->marketplace_url . "/products/pricing/v0/items/$asin/offers";
        $queryParams['MarketplaceId'] = $marketplace_id;
        $queryParams['ItemCondition'] = $item_condition;

        if(!empty($customer_type)){
            $queryParams['CustomerType'] = $customer_type;
        }
        $urlWithQuery = $api_endpoint . '?' . http_build_query($queryParams);
        $access_token_response = $this->getAccessToken();
        if($access_token_response['success']){
            $access_token = $access_token_response['output']['access_token'];

            $headers = array(
                'x-amz-access-token: ' . $access_token,
                'content-type: application/json',
            );
            $response = $this->send_curl_request($urlWithQuery , "GET" , $headers  );
            if($response){
                $responseData = json_decode($response, true);
                return $this->response(true,$responseData);
            }  
        }
        return $this->response(false,null,$this->lastCurlError);

    }

    public function getListingOffersBatch($skus,$marketplace_id,$item_condition){
        $api_endpoint = $this->marketplace_url . '/batches/products/pricing/v0/listingOffers';
        $requests = [];
        foreach ($skus as $key => $sku) {
            $request_uri = "products/pricing/v0/listings/$sku/offers";
            $listing_offers_request = $this->createItemOffersRequest($request_uri,"GET",$marketplace_id,$item_condition="New");
            $requests[] = $listing_offers_request;
        }
        $body = [
            "requests" => $requests
        ];
        $access_token_response = $this->getAccessToken();
        if($access_token_response['success']){
            $access_token = $access_token_response['output']['access_token'];

            $headers = array(
                'x-amz-access-token: ' . $access_token,
                'content-type: application/json',
            );

            $response = $this->send_curl_request($api_endpoint , "POST" , $headers  , json_encode($body));
            if($response){
                $responseData = json_decode($response, true);
                return $this->response(true,$responseData);
            }
        }
        return $this->response(false,null,$this->lastCurlError);
    }

    public function getListingOffers($sku,$marketplace_id,$item_condition="New",$customer_type=null){
        $api_endpoint = $this->marketplace_url . "/products/pricing/v0/listings/$sku/offers";
        $queryParams['MarketplaceId'] = $marketplace_id;
        $queryParams['ItemCondition'] = $item_condition;

        if(!empty($customer_type)){
            $queryParams['CustomerType'] = $customer_type;
        }
        $urlWithQuery = $api_endpoint . '?' . http_build_query($queryParams);
        $access_token_response = $this->getAccessToken();
        if($access_token_response['success']){
            $access_token = $access_token_response['output']['access_token'];

            $headers = array(
                'x-amz-access-token: ' . $access_token,
                'content-type: application/json',
            );

            $response = $this->send_curl_request($urlWithQuery , "GET" , $headers  );
            if($response){
                $responseData = json_decode($response, true);
                return $this->response(true,$responseData);
            }
       
        }
        return $this->response(false,null,$this->lastCurlError);
    }

    public function createItemOffersRequest($uri,$method,$marketplace_id,$item_condition="New",$headers=[],$customer_type=false){
        $request = [
            "uri" => $uri,
            "method" => $method,
            "MarketplaceId"=> $marketplace_id,
            "ItemCondition"=> $item_condition,
        ];
        if(!empty($customer_type)){
            $request['CustomerType'] = $customer_type;
        }
        if(!empty($headers)){
            $request['headers'] = $headers;
        }

        return $request;
    }
    
}