<?php

namespace Tainacan\API\EndPoints;

use \Tainacan\API\REST_Controller;
use Tainacan\Entities;
use Tainacan\Repositories;
use Tainacan\Entities\Entity;
use Tainacan\Tests\TAINACAN_REST_Collections_Controller;

class REST_Export_Controller extends REST_Controller {
	private $item_metadata_repository;
	private $items_repository;
	private $collection_repository;
	private $metadatum_repository;

	public function __construct() {
		$this->rest_base = 'export';
		parent::__construct();
		add_action('init', array(&$this, 'init_objects'), 11);
	}

	/**
	 * Initialize objects after post_type register
	 *
	 * @throws \Exception
	 */
	public function init_objects() {
		$this->metadatum_repository = Repositories\Metadata::get_instance();
		$this->item_metadata_repository = Repositories\Item_Metadata::get_instance();
		$this->items_repository = Repositories\Items::get_instance();
		$this->collection_repository = Repositories\Collections::get_instance();
	}

	/**
	 * If POST on metadatum/collection/<collection_id>, then
	 * a metadatum will be created in matched collection and all your item will receive this metadatum
	 *
	 * If POST on metadatum/item/<item_id>, then a value will be added in a metadatum and metadatum passed
	 * id body of requisition
	 *
	 * Both of GETs return the metadatum of matched objects
	 *
	 * @throws \Exception
	 */
	public function register_routes() {
		register_rest_route($this->namespace, '/' . $this->rest_base. '/collection/(?P<collection_id>[\d]+)',
			array(
				array(
					'methods'             => \WP_REST_Server::READABLE,
					'callback'            => array($this, 'get_items'),
					'permission_callback' => array($this, 'get_items_permissions_check'),
					'args'                => $this->get_endpoint_args_for_item_schema(\WP_REST_Server::READABLE),
				),
			)
		);
		register_rest_route($this->namespace, '/' . $this->rest_base. '/item/(?P<item_id>[\d]+)',
			array(
				array(
					'methods'             => \WP_REST_Server::READABLE,
					'callback'            => array($this, 'get_item'),
					'permission_callback' => array($this, 'get_item_permissions_check'),
					'args'                => $this->get_endpoint_args_for_item_schema(\WP_REST_Server::READABLE),
				),
			)
		);
		register_rest_route($this->namespace, '/' . $this->rest_base,
			array(
				array(
					'methods'             => \WP_REST_Server::READABLE,
					'callback'            => array($this, 'get_items'),
					'permission_callback' => array($this, 'get_items_permissions_check'),
					'args'                => $this->get_collection_params(),
				)
			)
		);
	}

	/**
	 * @param \WP_REST_Request $request
	 *
	 * @return \WP_Error|\WP_REST_Response
	 */
	public function get_item( $request ) { }

	/**
	 * @param \WP_REST_Request $request
	 *
	 * @return bool|\WP_Error
	 * @throws \Exception
	 */
	public function get_item_permissions_check( $request ) {
		if(isset($request['collection_id'])) {
			$collection = $this->collection_repository->fetch($request['collection_id']);
			if($collection instanceof Entities\Collection) {
				if (! $collection->can_read()) {
					return false;
				}
				return true;
			}
		} elseif(isset($request['item_id'])) {
			$item = $this->items_repository->fetch($request['item_id']);
			if($item instanceof Entities\Item) {
				if (! $item->can_read()) {
					return false;
				}
				return true;
			}
		} else { // Exporting all
			$dummy = new Entities\Collection();
			return current_user_can($dummy->get_capabilities()->read); // Need to check Colletion by collection
		}
		return false;
	}

	/**
	 * @param \Tainacan\Entities\Item $item
	 * @param \WP_REST_Request $request
	 *
	 * @return array|\WP_Error|\WP_REST_Response
	 */
	public function prepare_item_for_response( $item, $request ) {
		$items_metadata = $item->get_metadata();
		
		$prepared_item = [];
		
		foreach ($items_metadata as $item_metadata){
			$prepared_item[] =  $item_metadata->_toArray();
		}

		return $prepared_item;
	}
	
	/**
	 * 
	 * @param \WP_REST_Request $request
	 * @param \WP_Query|Entities\Item $query
	 * @param array $args
	 * @return \WP_Error|number
	 */
	public function export($request, $query, $args) {
		
		$type = \Tainacan\Exposers_Handler::request_has_type($request);
		$path = wp_upload_dir();
		$path = $path['path'];
		$filename = $path.date('YmdHis').'-tainacan-export.'.$type->get_extension();
		$pid = -1;

		$log = \Tainacan\Entities\Log::create(
			__('Export Process', 'tainacan'),
			__('Exporting Data', 'tainacan').'\nArgs: '.print_r($args, true),
			['file' => $filename],
			[],
			'processing'
		);
		
		$body = json_decode( $request->get_body(), true );
		$background = ! (isset($body['export-background']) && $body['export-background'] == false);
		if( $background ) {
			$pid = pcntl_fork();
		} else {
			$pid = true;
		}
		if ($pid === -1) {
			$error = new \WP_Error('could not fork');
			$log = \Tainacan\Entities\Log::create(
				__('Export Process Error', 'tainacan'),
				__('Exporting Error', 'tainacan').'\\nArgs: '.print_r($args, true).'\\nError: could not fork',
				$error,
				[],
				'error'
			);
			remove_filter( 'rest_request_after_callbacks', [\Tainacan\Exposers_Handler::get_instance(), 'rest_request_after_callbacks'], 10, 3 ); //exposer mapping
			remove_filter( 'tainacan-rest-response', [\Tainacan\Exposers_Handler::get_instance(), 'rest_response'], 10, 2 ); // exposer types
			return $log;
		} elseif ($pid) { // we are the parent or run at foreground
			try {
				ignore_user_abort(true);
				set_time_limit(0);
				ini_set("memory_limit", "256M");
				
				if($background) { // wait for child to respond and exit and reconnect database if is forked
					$status = null;
					pcntl_wait($status);
					global $wpdb;
					$wpdb->db_connect();
				}
				
				$response = [];
				if(isset($request['collection_id'])) { // One Colletion
					$collection_id = $request['collection_id'];
					$items = $query;
					if ($items->have_posts()) {
						while ( $items->have_posts() ) { //TODO write line by line
							$items->the_post();
							
							$item = new Entities\Item($items->post);
							
							$prepared_item = $this->prepare_item_for_response($item, $request);
							
							array_push($response, $prepared_item);
						}
						wp_reset_postdata();
					}
				} elseif (isset($request['item_id'])) { // One Item
					
					$item = new Entities\Item($request['item_id']);
					if($item->get_id() > 0) {
						$prepared_item = $this->prepare_item_for_response($item, $request);
						
						$response = [$prepared_item];
					}
				} else { // Export All
				    $collections = $query;
				    $collection_controller = new REST_Collections_Controller();
				    if ($collections->have_posts()) {
				        while ($collections->have_posts()) {
				            $collections->the_post();
				            $collection_id = $collections->post->ID;
				            $collection = \Tainacan\Repositories\Repository::get_entity_by_post($collections->post);
				            
				            $prepared_collection = $collection_controller->prepare_item_for_response($collection, $request);
				            
				            $prepared_items = [];
				            
				            $items = $this->items_repository->fetch($args, $collection_id, 'WP_Query');
        				    if ($items->have_posts()) {
        				        while ( $items->have_posts() ) { //TODO write line by line
        				            $items->the_post();
        				            
        				            $item = new Entities\Item($items->post);
        				            
        				            $prepared_item = $this->prepare_item_for_response($item, $request);
        				            
        				            array_push($prepared_items, $prepared_item);
        				        }
        				        wp_reset_postdata();
        				    }
        				    
        				    $prepared_collection['items'] = $prepared_items;
        				    array_push($prepared_collection, $response);
				        }
				        wp_reset_postdata();
				    }
				}
				
				$rest_response = new \WP_REST_Response(apply_filters('tainacan-rest-response', $response, $request));
				$data = $rest_response->get_data();
				file_put_contents($filename, is_string($data) ? $data : print_r($data, true));
				
				if($background) {
					$log->set_status('publish');
					$logs = \Tainacan\Repositories\Logs::get_instance();
					$logs->update($log);
					exit(1);
				} else {
					return $rest_response->get_data();
				}
			} catch (\Exception $e) {
				if($background) {
					exit(1);
				} else {
					throw $e;
				}
			}
		} else { // we are the child

			remove_filter( 'rest_request_after_callbacks', [\Tainacan\Exposers_Handler::get_instance(), 'rest_request_after_callbacks'], 10, 3 ); //exposer mapping
			remove_filter( 'tainacan-rest-response', [\Tainacan\Exposers_Handler::get_instance(), 'rest_response'], 10, 2 ); // exposer types
			return $log;
		}
		
	}

	/**
	 * @param \WP_REST_Request $request
	 *
	 * @return \WP_Error|\WP_REST_Response
	 */
	public function get_items( $request ) {
		$args = $this->prepare_filters($request); // TODO default args
		$rest_response = new \WP_REST_Response([], 200); // TODO error, empty response
		
		if(isset($request['collection_id'])) { // One Colletion
			$collection_id = $request['collection_id'];
			$items = $this->items_repository->fetch($args, $collection_id, 'WP_Query');
			
			$response = $this->export($request, $items, $args);
			
			$total_items  = $items->found_posts;
			$ret = $response instanceof Entity ? $response->__toArray() : $response;
			$rest_response = new \WP_REST_Response($ret, 200);
			
			$rest_response->header('X-WP-Total', (int) $total_items);
		} elseif (isset($request['item_id'])) { // One Item
					
			$item = new Entities\Item($request['item_id']);
			if($item->get_id() > 0) {
				$response = $this->export($request, $item, $args);
				
				$total_items  = 1;
				$max_pages = 1;
				
				$rest_response = new \WP_REST_Response($response->__toArray(), 200);
				
				$rest_response->header('X-WP-Total', 1);
				$rest_response->header('X-WP-TotalPages', 1);
			}
		} else { // Export All
		    $Tainacan_Collection = \Tainacan\Repositories\Collections::get_instance();
		    $collections = $Tainacan_Collection->fetch(['post_status' => 'publish'], 'WP_Query');
    		    
		    $response = $this->export($request, $collections, $args);
   		    $total_items = $collections->found_posts;
   		    $ret = $response instanceof Entity ? $response->__toArray() : $response;
   		    $rest_response = new \WP_REST_Response($ret, 200);

   		    $rest_response->header('X-WP-Total', (int) $total_items);
		}
		
		return $rest_response;
	}

	/**
	 * @param \WP_REST_Request $request
	 *
	 * @return bool|\WP_Error
	 * @throws \Exception
	 */
	public function get_items_permissions_check( $request ) {
		return $this->get_item_permissions_check($request);
	}

}

?>
