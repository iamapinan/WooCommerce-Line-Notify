<?php
include '../lib/Requests/library/Requests.php';

class LineNotify {

  private $token = null;
  private $apiUrl = null;

  public function __construct( $token, $url ) {
    $this->apiUrl = $url;
    $this->token = $token;
  }

  public function setToken($token) {
    $this->token = $token;
  }

  public function getToken() {
    return $this->token;
  }

  public function request($param) {
    // $curl = curl_init();
    
    // curl_setopt_array( $curl, 
    //   array(
    //     CURLOPT_URL => $this->apiUrl,
    //     CURLOPT_RETURNTRANSFER => true,
    //     CURLOPT_ENCODING => "application/x-www-form-urlencoded",
    //     CURLOPT_MAXREDIRS => 10,
    //     CURLOPT_TIMEOUT => 30,
    //     CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
    //     CURLOPT_CUSTOMREQUEST => "POST",
    //     CURLOPT_POSTFIELDS => http_build_query( $param ),
    //     CURLOPT_HTTPHEADER => array( 'Authorization' => 'Bearer ' . $this->token )
    //   )
    // );

    // Next, make sure Requests can load internal classes
    Requests::register_autoloader();
    
    // $response = curl_exec($curl);
    // $err = curl_error($curl);

    // curl_close($curl);
    
    $header = array( 'Authorization' => 'Bearer '.$this->token );

    $request = Requests::post( $this->apiUrl, $header, $param );
    // Check what we received
    echo json_encode($request);
  }

  public function send( $text, $imagePath = null ) {

    if (empty($text)) {
      return false;
    }

    
    $request_params = array('message' => $text);
    

    $response = $this->request( $request_params );

    if (!$response) {
      return false;
    }

    //echo json_encode( $response );
  }

}
