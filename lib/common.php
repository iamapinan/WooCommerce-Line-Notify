<?php
include 'Requests.php';

class LineNotify {

  private $token = null;
  private $apiUrl = null;

  public function __construct( $token, $url ) {
    $this->apiUrl = $url;
    $this->token = $token;
  }

  public function request($param) {
    Requests::register_autoloader();
    $header = array( 'Authorization' => 'Bearer '.$this->token );
    $request = Requests::post( $this->apiUrl, $header, $param );

    return $request->status_code;
  }

  public function send( $text, $imagePath = null ) {

    if (empty($text)) {
      return false;
    }
    $request_params = array('message' => $text);
    
    $response = $this->request( $request_params );

    if ( $response == '200' ) {
      return true;
    } else {
      return false;
    }
  }

}
