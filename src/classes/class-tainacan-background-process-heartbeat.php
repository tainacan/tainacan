<?php
namespace Tainacan;

class Background_Importer_Heartbeat {
	
	public function __construct() {
		add_filter( 'heartbeat_send', array( &$this, 'bg_process_feedback' ), 10, 2 );
	}
	
	/**
	* Receive Heartbeat data and response.
	*
	* Processes data received via a Heartbeat request, and returns additional data to pass back to the front end.
	*
	* @param array $response Heartbeat response data to pass back to front end.
	* @param array $data Data received from the front end (unslashed).
	*/
	public function bg_process_feedback( $response, $data ){
		
		
		$request = new \WP_REST_Request( 'GET', '/tainacan/v2/bg-processes' );
		$request->set_query_params( [ 'recent' => 1 ] );
		$api_response = rest_do_request( $request );
		$server = rest_get_server();
		$data = $server->response_to_data( $api_response, false );
		
		
		$response['bg_process_feedback'] = $data;
		
		return $response;
		
		
	}
}
