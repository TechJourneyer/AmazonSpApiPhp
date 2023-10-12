<?php 

require_once 'AmazonSpApi.php';
class Feeds extends AmazonSpApi{
    public $feed_section = "/feeds/2021-06-30/";

    public function createFeedDocument($access_token,$content_type){
        if($access_token){
            $api_endpoint = $this->marketplace_url . $this->feed_section . 'documents';
            $headers = array(
                'x-amz-access-token: ' . $access_token,
                'content-type: application/json',
            );
            $request_data = array(
                'contentType' => $content_type,
            );
            $response = $this->send_curl_request($api_endpoint , "POST" , $headers , json_encode($request_data) );
            if($response){
                return $this->response(true,json_decode($response, true));
            }
        }
        return $this->response(false,null, $this->lastCurlError);
    }

    private function uploadFeed($url, $file_path,$content_type){
        $fileData = file_get_contents($file_path);
        $headers = [ 'Content-Type: ' . $content_type ];
        $response = $this->send_curl_request($url , "PUT" , $headers , $fileData );
        if($this->lastCurlHttpCode >=200 && $this->lastCurlHttpCode < 300){
            return true;
        }
        return false;
    }

    private function submitFeed($access_token , $feed_type,$feed_document_id,$marketplace_id){
        $api_endpoint = $this->marketplace_url . $this->feed_section . 'feeds';

        // Construct the request headers
        $headers = array(
            'x-amz-access-token: ' . $access_token,
            'content-type: application/json',
        );
    
        // Construct the request body
        $request_data = array(
            'feedType' => $feed_type,
            'inputFeedDocumentId' => $feed_document_id,
            'marketplaceIds' => $this->marketplace_ids,
        );
        
        $response = $this->send_curl_request($api_endpoint , "POST" , $headers , json_encode($request_data) );
        if($response){
            $response_data = json_decode($response, true);
            if(isset($response_data['feedId'])){
                return $this->response(true, $response_data );
            }
            $error = "Error submitting feed:  " . $response;
            return $this->response(false, $response_data ,$error);
        }
        return $this->response(false, $response_data ,$this->lastCurlError);
    }
    
    public function createAndSubmitFeed($feed_type,$feed_file_path,$content_type,$marketplace_id){

        if(!file_exists($feed_file_path)){
            return $this->response(false,null,"Failed : Check you feed file path");
        }
        
        $this->setMarketplaceId($marketplace_id);
        $access_token_response = $this->getAccessToken();
        
        if($access_token_response['success']){
            $access_token = $access_token_response['output']['access_token'];
            $createFeedDocumentResponse =  $this->createFeedDocument($access_token,$content_type);
            if(isset($createFeedDocumentResponse['success']) && $createFeedDocumentResponse['success']){
                $output = $createFeedDocumentResponse['output'];
                $feed_document_id = $output['feedDocumentId'];
                $presigned_url = $output['url'];
                $uploadFeed = $this->uploadFeed($presigned_url, $feed_file_path,$content_type);
                if($uploadFeed){
                    $response =  $this->submitFeed($access_token , $feed_type,$feed_document_id,$marketplace_id);
                    if(isset($response['output'])){
                        $response['output']['presigned_url'] = $presigned_url;
                        $response['output']['feed_document_id'] = $feed_document_id;
                    }
                    return $response;
                }
                return $this->response(false,null,"Feed upload failed");
            }
            return $this->response(false,null,"API Failed : " . $createFeedDocumentResponse['error_msg']);
        }
        return $this->response(false,null,$access_token_response['error_msg']);
    }

    public function getFeeds($params) {
        
        $feed_types         = isset($params['feed_types']) ? $params['feed_types'] : [] ;
        $marketplace_ids    = isset($params['marketplace_ids']) ? $params['marketplace_ids'] : [] ;
        $page_size          = isset($params['page_size']) ? $params['page_size'] : 10 ;
        $processing_statuses = isset($params['processing_statuses']) ? $params['processing_statuses'] : [] ;
        $created_since      = isset($params['created_since']) ? $params['created_since'] : null ;
        $created_until      = isset($params['created_until']) ? $params['created_until'] : null ;
        $next_token         = isset($params['next_token']) ? $params['next_token'] : null ;

        $api_endpoint = $this->marketplace_url . $this->feed_section . 'feeds';
        $access_token_response = $this->getAccessToken();
        
        if($access_token_response['success']){
            $access_token = $access_token_response['output']['access_token'];

            $headers = array(
                'x-amz-access-token: ' . $access_token,
                'content-type: application/json',
            );

            $queryParams = [];
        
            if (!empty($feed_types)) {
                $queryParams['feedTypes'] = implode(',', $feed_types);
            }
        
            if (!empty($marketplace_ids)) {
                $queryParams['marketplaceIds'] = implode(',', $marketplace_ids);
            }
        
            $queryParams['pageSize'] = $page_size;
        
            if (!empty($processing_statuses)) {
                $queryParams['processingStatuses'] = implode(',', $processing_statuses);
            }
        
            if ($created_since !== null) {
                $queryParams['createdSince'] = $created_since;
            }
        
            if ($created_until !== null) {
                $queryParams['createdUntil'] = $created_until;
            }
        
            if ($next_token !== null) {
                $queryParams['nextToken'] = $next_token;
            }
        
            $urlWithQuery = $api_endpoint . '?' . http_build_query($queryParams);
            
            $response = $this->send_curl_request($urlWithQuery , "GET" , $headers  );
            if($response){
                $responseData = json_decode($response, true);
                return $this->response(true,$responseData);
            }
        }
        return $this->response(false,null,$this->lastCurlError);
    }

    public function getFeed($feed_id){
        $api_endpoint = $this->marketplace_url . $this->feed_section . 'feeds/'.$feed_id;
        $access_token_response = $this->getAccessToken();
        
        if($access_token_response['success']){
            $access_token = $access_token_response['output']['access_token'];

            $headers = array(
                'x-amz-access-token: ' . $access_token,
                'content-type: application/json',
            );

            $response = $this->send_curl_request($api_endpoint , "GET" , $headers  );
            if($response){
                $responseData = json_decode($response, true);
                return $this->response(true,$responseData);
            }
        }
        return $this->response(false,null,$this->lastCurlError);
    }
    
}