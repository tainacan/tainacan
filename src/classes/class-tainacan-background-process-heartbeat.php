<?php
namespace Tainacan;

class Background_Importer_Heartbeat {

    public function __construct() {
		global $wpdb;
		$this->table = $wpdb->prefix . 'tnc_bg_process';
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
        global $wpdb;

        $user_q = $wpdb->prepare("AND user_id = %d", get_current_user_id());
        $status_q = "";

        $base_query = "FROM $this->table WHERE processed_last >= NOW() - INTERVAL 10 MINUTE";

        $query = "SELECT * $base_query";
        $result = $wpdb->get_results($query);

        $response['bg_process_feedback'] = $result;

        return $response;
    }
}
