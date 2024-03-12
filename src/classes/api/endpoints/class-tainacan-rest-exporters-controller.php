<?php

namespace Tainacan\API\EndPoints;

use \Tainacan\API\REST_Controller;
use Tainacan\Repositories;
use Tainacan\Entities;
use \Tainacan\Exposers\Mappers\Value;

/**
 * Represents the Exporters REST Controller
 *
 * */
class REST_Exporters_Controller extends REST_Controller {
	private $collections_repository;

	/**
	* REST_Exporters_Controller constructor.
	* Define the namespace, rest base and instantiate your attributes.
	*/
	public function __construct() {
		$this->rest_base = 'exporters';
		parent::__construct();
		add_action('init', array(&$this, 'init_objects'), 11);
	}

	/**
	 * Initialize objects after post_type register
	 */
	public function init_objects() {
		$this->collections_repository = Repositories\Collections::get_instance();
	}

	/**
	* Register the collections route and their endpoints
	*/
	public function register_routes() {
		register_rest_route($this->namespace, '/' . $this->rest_base . '/available', array(
			array(
				'methods'             => \WP_REST_Server::READABLE,
				'callback'            => array($this, 'get_registered_exporters'),
				'permission_callback' => array($this, 'export_permissions_check'),
			),
			'schema' => [$this, 'get_schema_exporters_available']
		));

		register_rest_route($this->namespace, '/' . $this->rest_base . '/session', array(
			array(
				'methods'             => \WP_REST_Server::CREATABLE,
				'callback'            => array($this, 'create_item'),
				'permission_callback' => array($this, 'export_permissions_check'),
				'args'                => [
					'exporter_slug' => [
						'type'        => 'string',
						'description' => __( 'The slug of the exporter to be initialized', 'tainacan' ),
					]
				],
			),
			'schema' => [$this, 'get_schema']
		));

		register_rest_route($this->namespace, '/' . $this->rest_base . '/session/(?P<session_id>[0-9a-f]+)', array(
			array(
				'methods'             => \WP_REST_Server::EDITABLE,
				'callback'            => array($this, 'update_item'),
				'permission_callback' => array($this, 'export_permissions_check'),
				'args'                => [
					'session_id' => [
						'type'        => 'string',
						'description' => __( 'The ID for this exporter session', 'tainacan' ),
					],
					'send_email' => [
						'type'        => 'string',
						'description' => __( 'The e-mail to be used by the export to send a message when the process ends', 'tainacan' ),
					],
					'collection' => [
						'type'        => ['array', 'object'],
						'description' => __( 'The array describing the collection as expected by the exporter', 'tainacan' ),
					],
					'options' => [
						'type'        => ['array', 'object'],
						'description' => __( 'The exporter options', 'tainacan' ),
					]
				],
			),
			'schema' => [$this, 'get_schema']
		));

		register_rest_route($this->namespace, '/' . $this->rest_base . '/session/(?P<session_id>[0-9a-f]+)/run', array(
			array(
				'methods'             => \WP_REST_Server::CREATABLE,
				'callback'            => array($this, 'run'),
				'permission_callback' => array($this, 'export_permissions_check'),
				'args'                => [
					'session_id' => [
						'type'        => 'string',
						'description' => __( 'The ID for this exporter session', 'tainacan' ),
					],
				]
			),
			'schema' => [$this, 'get_schema_run']
		));

	}

	/**
	 *
	 * @param \WP_REST_Request $request
	 *
	 * @return bool|\WP_Error
	 * @throws \Exception
	 */
	public function  export_permissions_check($request) {
		return current_user_can('manage_tainacan');
	}

	public function get_registered_exporters() {
		global $Tainacan_Exporter_Handler;
		$exporters = $Tainacan_Exporter_Handler->get_registered_exporters();
		return new \WP_REST_Response( $exporters, 200 );
	}

	/**
	 * Creates a new instance of the desired exporter and returns its ID
	 *
	 * @param \WP_REST_Request $request
	 *
	 * @return array|\WP_Error|\WP_REST_Response
	 */
	public function create_item( $request ) {
		$body = json_decode($request->get_body(), true);

		if(empty($body)) {
			return new \WP_REST_Response([
				'error_message' => __('Body cannot be empty.', 'tainacan'),
			], 400);
		}
		$slug = $body['exporter_slug'];
		global $Tainacan_Exporter_Handler;

		if ($object = $Tainacan_Exporter_Handler->initialize_exporter($slug)) {
			$response = $object->_to_Array();
			$Tainacan_Exporter_Handler->save_exporter_instance($object);
			return new \WP_REST_Response($response, 201);
		} else {
			return new \WP_REST_Response([
				'error_message' => __('Exporter not found', 'tainacan'),
			], 400);
		}
	}

	/**
	 * Update a collection
	 *
	 * @param \WP_REST_Request $request
	 *
	 * @return string|\WP_Error|\WP_REST_Response
	 */
	public function update_item( $request ) {

		$session_id = $request['session_id'];
		$body = json_decode($request->get_body(), true);

		if(!empty($body)) {
			global $Tainacan_Exporter_Handler;
			$exporter = $Tainacan_Exporter_Handler->get_exporter_instance_by_session_id($session_id);
			
			if($exporter) {
				foreach ($body as $att => $value) {
					if ($att == 'collection') {
						if (is_array($value) && isset($value['id'])) {
							$collection = $this->collections_repository->fetch($value['id']);
							$total_items = wp_count_posts( $collection->get_db_identifier(), 'readable' );
							$value['total_items'] = ($total_items->publish + $total_items->private + $total_items->draft);
							$exporter->add_collection($value);
							continue;
						} else {
							return new \WP_REST_Response([
								'error_message' => __('Invalid collection', 'tainacan' ),
										'session_id' => $session_id
							], 400);
						}
					}
					$method = 'set_' . $att;
					if (method_exists($exporter, $method)) {
						$exporter->$method($value);
					}
				}

				$response = $exporter->_to_Array();
				$Tainacan_Exporter_Handler->save_exporter_instance($exporter);
				return new \WP_REST_Response( $response, 200 );
			}

			return new \WP_REST_Response([
				'error_message' => __('Exporter Session not found', 'tainacan' ),
				'session_id' => $session_id
			], 400);
		}

		return new \WP_REST_Response([
			'error_message' => __('The body cannot be empty', 'tainacan'),
			'body'          => $body
		], 400);
	}
	
	/**
	 * Run a exporter
	 *
	 * @param \WP_REST_Request $request
	 *
	 * @return string|\WP_Error|\WP_REST_Response
	 */
	public function run($request) {
		$session_id = $request['session_id'];
		global $Tainacan_Exporter_Handler;
		$exporter = $Tainacan_Exporter_Handler->get_exporter_instance_by_session_id($session_id);

		if(!$exporter) {
			return new \WP_REST_Response([
				'error_message' => __('Exporter Session not found', 'tainacan' ),
				'session_id' => $session_id
			], 400);
		}

		global $Tainacan_Exporter_Handler;
		$process = $Tainacan_Exporter_Handler->add_to_queue($exporter);
		if (false === $process) {
			return new \WP_REST_Response([
				'error_message' => __('Error starting exporter', 'tainacan' ),
				'session_id' => $session_id
			], 400);
		}
		$response = [
			'bg_process_id' => $process->ID
		];
		$Tainacan_Exporter_Handler->delete_exporter_instance($exporter);
		return new \WP_REST_Response( $response, 200 );
	}
	
	protected function map($item_arr, $mapper) {
		$ret = $item_arr;
		if(array_key_exists('metadatum', $item_arr)) { // getting a unique metadatum
			$ret = $this->map_metadatum($item_arr, $mapper);
		} else { // array of elements
			$ret = [];
			foreach ($item_arr as $item) {
				if(array_key_exists('metadatum', $item)) {
					$ret = array_merge($ret, $this->map($item, $mapper) );
				} else {
					$ret[] = $this->map($item, $mapper);
				}
			}
		}
		return $ret;
	}

	protected function map_metadatum($item_arr, $mapper) {
		$ret = $item_arr;
		$metadatum_mapping = $item_arr['metadatum']['exposer_mapping'];
		if(array_key_exists($mapper->slug, $metadatum_mapping)) {
			if(
					is_string($metadatum_mapping[$mapper->slug]) && is_array($mapper->metadata) && !array_key_exists( $metadatum_mapping[$mapper->slug], $mapper->metadata) ||
					is_array($metadatum_mapping[$mapper->slug]) && $mapper->allow_extra_metadata != true
			) {
				throw new \Exception('Invalid Mapper Option');
			}
			$slug = '';
			if(is_string($metadatum_mapping[$mapper->slug])) {
				$slug = $metadatum_mapping[$mapper->slug];
			} else {
				$slug = $metadatum_mapping[$mapper->slug]['slug'];
			}
			$ret = [$mapper->prefix.$slug.$mapper->sufix => $item_arr['value']]; //TODO Validate option
		} elseif($mapper->slug == 'value') {
			$ret = [$item_arr['metadatum']['name'] => $item_arr['value']];
		} else {
			$ret = [];
		}
		return $ret;
	}

	public function get_schema() {
		
		$schema = [
			'$schema'  => 'http://json-schema.org/draft-04/schema#',
			'title' => 'exporters',
			'type' => 'object',
			'tags' => [ 'background-process' ],
		];

		$schema['properties'] = array_merge(
			[
				'id' => [
					'description'  => esc_html__( 'The ID for this exporter session', 'tainacan' ),
					'type'         => 'string',
					'context'      => array( 'view' ),
					'readonly'     => true
				],
				'in_step_count' => [
					'description'  => esc_html__( 'Number of items found to process in step.', 'tainacan' ),
					'type'         => 'integer',
					'context'      => array( 'view' ),
					'readonly'     => true
				],
				'current_step' => [
					'description'  => esc_html__( 'Current step.', 'tainacan' ),
					'type'         => 'integer',
					'context'      => array( 'view' ),
					'readonly'     => true
				],
				'transients' => [
					'description'  => esc_html__( 'Properties of exporter class, the values will be kept during all the time the process is running.', 'tainacan' ),
					'type'         => 'object',
					'context'      => array( 'view' ),
					'readonly'     => true
				],
				'options' => [
					'description'  => esc_html__( 'the set set of options exporter may have its own, that will be used during the export process.', 'tainacan' ),
					'type'         => 'object',
					'context'      => array( 'view' ),
					'readonly'     => true
				],
				'options_form' => [
					'description'  => esc_html__( 'The HTML form with a set of options that the exporter may have.', 'tainacan' ),
					'type'         => 'string',
					'context'      => array( 'view' ),
					'readonly'     => true
				],
				'collections' => [
					'description'  => esc_html__( 'Array of the target collections, with their IDs, an identifier from the source, the total number of items to be exporter, the mapping array from the source structure to the ID of the metadata in tainacan', 'tainacan' ),
					'type'        => 'array',
					'items'       => [
						'type' => 'object',
						'properties' => [
							'id' => [
								'type' => 'integer',
								'description' => __( 'Collection ID an identifier from the source', 'tainacan' ),
							],
							'total_items' => [
								'type' => 'integer',
								'description' => __( 'The total number of items to be exporter', 'tainacan' ),
							]
						]
					],
					'context'      => array( 'view' ),
					'readonly'     => true
				],
				'class_name' => [
					'description'  => esc_html__( 'The Exporter Class. e.g. "\Tainacan\Exporter\Test_Exporter"', 'tainacan' ),
					'type'         => 'string',
					'context'      => array( 'view' ),
					'readonly'     => true
				],
				'manual_collection' => [
					'description'  => esc_html__( 'Define whether manually selecting collections is accepted', 'tainacan' ),
					'type'         => 'boolean',
					'context'      => array( 'view' ),
					'readonly'     => true
				],
				'mapping_accept' => [
					'description'  => esc_html__( 'If is set to “any”, all mappers will be available. If set to “list”, only the list of mappers indicated by "mapping_list" can be used.', 'tainacan' ),
					'type'         => 'string',
					'context'      => array( 'view' ),
					'readonly'     => true
				],
				'mapping_list' => [
					'description'  => esc_html__( 'The list of mappers accepted by the exported.', 'tainacan' ),
					'type'         => 'string',
					'context'      => array( 'view' ),
					'readonly'     => true
				],
				'accept_no_mapping' => [
					'description'  => esc_html__( 'Informs that is also allow export items in their original form, without any mapping.', 'tainacan' ),
					'type'         => 'string',
					'context'      => array( 'view' ),
					'readonly'     => true
				],
				'mapping_selected' => [
					'description'  => esc_html__( 'Mapper selected for process.', 'tainacan' ),
					'type'         => 'string',
					'context'      => array( 'view' ),
					'readonly'     => true
				],
				'current_collection_item' => [
					'description'  => esc_html__( 'Id of the current item in the process is running on.', 'tainacan' ),
					'type'         => 'integer',
					'context'      => array( 'view' ),
					'readonly'     => true
				],
				'current_collection' => [
					'description'  => esc_html__( 'Id of the current collection in the process is running on.', 'tainacan' ),
					'type'         => 'integer',
					'context'      => array( 'view' ),
					'readonly'     => true
				],
				'output_files' => [
					'description'  => esc_html__( 'Get a list of the generated files to display to users.', 'tainacan' ),
					'type'         => 'array',
					'items'		   => 'string',
					'context'      => array( 'view' ),
					'readonly'     => true
				],
				'send_email' => [
					'description'  => esc_html__( 'if notify the user after completion of the process.', 'tainacan' ),
					'type'         => 'string',
					'context'      => array( 'view' ),
					'readonly'     => true
				],
				
			]
		);

		return $schema;
	}

	public function get_schema_run() {
		
		$schema = [
			'$schema'  => 'http://json-schema.org/draft-04/schema#',
			'title' => 'exporters-run',
			'type' => 'object',
			'tags' => [ 'background-process' ],
		];

		$schema['properties'] = array_merge(
			[
				'bg_process_id' => [
					'description'  => esc_html__( 'The ID for this exporter session', 'tainacan' ),
					'type'         => 'string',
					'context'      => array( 'view' ),
					'readonly'     => true
				],
			]
		);

		return $schema;
	}

	public function get_schema_exporters_available() {
		$schema = [
			'$schema'  => 'http://json-schema.org/draft-04/schema#',
			'title' => 'exporters-available',
			'type' => 'object',
			'tags' => [ 'background-process' ],
		];

		$schema['properties'] = array_merge(
			[
				'slug' => [
					'description'  => esc_html__( 'A unique slug for the exporter. e.g. "example-exporter"', 'tainacan' ),
					'type'         => 'string',
					'context'      => array( 'view' ),
					'readonly'     => true
				],
				'name' => [
					'description'  => esc_html__( 'The name of the exporter. e.g. "Example exporter".', 'tainacan' ),
					'type'         => 'string',
					'context'      => array( 'view' ),
					'readonly'     => true
				],
				'description' => [
					'description'  => esc_html__( 'The exporter description', 'tainacan' ),
					'type'         => 'string',
					'context'      => array( 'view' ),
					'readonly'     => true
				],
				'class_name' => [
					'description'  => esc_html__( 'The Exporter Class. e.g. "\Tainacan\Exporter\Test_Exporter"', 'tainacan' ),
					'type'         => 'string',
					'context'      => array( 'view' ),
					'readonly'     => true
				],
				'manual_mapping' => [
					'description'  => esc_html__( 'Whether Tainacan must present the user with an interface to manually choose a mapping standard. This will allow them to export the items mapped to a chosen standard instead of in its original form.', 'tainacan' ),
					'type'         => 'boolean',
					'context'      => array( 'view' ),
					'readonly'     => true
				],
				'manual_collection' => [
					'description'  => esc_html__( 'Whether Tainacan will let the user choose the source collection. If set to true, Tainacan give the user a select box from where he/she will Choose one (and only one) Collection to export items from. Otherwise, the child exporter class must choose the collections somehow.', 'tainacan' ),
					'type'         => 'boolean',
					'context'      => array( 'view' ),
					'readonly'     => true
				]
			]
		);

		return $schema;
	}
	
}

?>
