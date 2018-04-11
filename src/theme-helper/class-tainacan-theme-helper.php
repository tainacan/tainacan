<?php

namespace Tainacan;
use Tainacan\Entities;


class Theme_Helper {

	private static $instance = null;

	public static function getInstance() {
		if ( ! isset( self::$instance ) ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	private function __construct() {

		add_filter( 'the_content', [&$this, 'the_content_filter'] );
		
		

	}
	
	public function is_post_an_item(\WP_Post $post) {
		$post_type = $post->post_type;
		$prefix = substr( $post_type, 0, strlen( Entities\Collection::$db_identifier_prefix ) );
		return $prefix == Entities\Collection::$db_identifier_prefix;
	}
	
	public function the_content_filter($content) {
		global $post;
		
		if (!is_single())
			return $content;
		
		// Is it a collection Item?
		if ( !$this->is_post_an_item($post) ) {
			return $content;
		}
		
		$item = new Entities\Item($post);
		$content = '';
		
		// document
		
		// metadata
		$content .= $item->get_metadata_as_html();
		
		// attachments
		
		return $content;
		
	}
	
	

}

