<?php

namespace Tainacan\Exposers;

/**
 * Generate a text formated response
 *
 */
class JSON_flat extends Exposer {
	
	public $slug = 'json-flat'; // type slug for url safe
	public $name = 'Simple JSON (JavaScript Object Notation) for tainacan items';
	public $accept_no_mapper = true;
	protected $mappers = true;

	function __construct() {
		$this->set_name( __('Simple JSON', 'tainacan') );
		$this->set_description( __('Simple JSON for tainacan items', 'tainacan') );
	}
	
	
	/**
	 * 
	 * {@inheritDoc}
	 * @see \Tainacan\Exposers\Types\Type::rest_request_after_callbacks()
	 */
	public function rest_request_after_callbacks( $response, $handler, $request ) {
		$query_url_params = $request->get_query_params();
		$headers = $response->get_headers();
		$total_page = $headers['X-WP-TotalPages'];
		$current_page = isset($request['paged']) ? $request['paged'] : 1;
		if ($current_page < $total_page ) {
			$next_page = $current_page + 1;
			$next_page_link = $request->get_route() . '?' . http_build_query(array_merge($query_url_params, ['paged'=> $next_page]));
		} else {
			$next_page = false;
			$next_page_link = false;
		}

		$pagination = [
			'per_page' => $headers['X-WP-ItemsPerPage'], //items per page,
			'total_page' => $total_page, //total pages 
			'total_items' => $headers['X-WP-Total'], //total items
			'current_page' => $current_page,
			'next_page' => $next_page,
			'next_page_link' => $next_page_link
		];

		$data = $response->get_data(); 
		$items = $data['items'];
		$response_items = array_map( function($item) {
			$item_data = $item['metadata'];
			array_walk(
				$item_data,
				function(&$meta, $meta_key) {
					$meta = array(
						'label' => $meta['name'],
						'value' => $meta['value_as_string']
					); 
				}
			);
			$item_document = array(
				'type' => $item['document_type'],
				'value' => ''
			);
			if($item_document['type'] == 'url') {
				$item_document['value'] = $item['document'];
			} elseif ( $item_document['type'] == 'text' ) {
				$item_document['value'] = $item['document_as_html'];
			} elseif ( $item_document['type'] == 'attachment' ) {
				$item_document['value'] = wp_get_attachment_url($item['document']);
			}
			
			return [
				'id' => $item['id'],
				'data' => $item_data,
				'url' => $item['url'],
				'document' => $item_document,
				'thumbnail' => isset($item['_thumbnail_id']) ? wp_get_attachment_image_src($item['_thumbnail_id'], 'full') : false,
				'creation_date' => isset($item['creation_date']) ? $item['creation_date'] : '',
				'modification_date' =>  isset($item['modification_date']) ? $item['modification_date'] : ''
			];
		}, $items);

		$response_data = json_encode(
			[
				'pagination' => $pagination,
				'items' => $response_items
			]
		);
		$response->set_headers( ['Content-Type: application/json; charset=' . get_option( 'blog_charset' )] );
		$response->set_data(addslashes($response_data));
		return $response;
	}
}