<?php
namespace Tainacan\Importer;
use Tainacan;

class Background_Importer_Heartbeat {

    public function __construct() {
        add_filter( 'heartbeat_received', array( &$this, 'bg_process_feedback' ), 10, 2 );
    }

    /**
     * Receive Heartbeat data and respond.
     *
     * Processes data received via a Heartbeat request, and returns additional data to pass back to the front end.
     *
     * @param array $response Heartbeat response data to pass back to front end.
     * @param array $data Data received from the front end (unslashed).
     */
    public function bg_process_feedback( $response, $data ){
        $response['bg_process_feedback'] = true;

        //TODO: list all bg process

        return $response;
    }
}