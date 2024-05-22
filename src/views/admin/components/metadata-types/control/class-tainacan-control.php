<?php

namespace Tainacan\Metadata_Types;

use Tainacan\Entities\Metadatum;


defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

/**
 * Class TainacanMetadatumType
 */
class MetadataTypeControlHelper {
	private static $instance = null;
	private function __construct() { }

	public static function get_instance() {
		if ( ! isset( self::$instance ) ) {
			self::$instance = new self();
			add_action( 'tainacan-insert-tainacan-item', array(self::$instance, 'update_control_metadatum'), 10, 1 );
		}
		return self::$instance;
	}

	static function update_control_metadatum( $item ) {
		if ( $item instanceof \Tainacan\Entities\Item ) {
			$item_metadata_repositories = \Tainacan\Repositories\Item_Metadata::get_instance();
			$enabled_logs = $item_metadata_repositories->get_enabled_logs();
			$item_metadata_repositories->disable_logs();
			$collection = $item->get_collection();
			$args = [
				'include_control_metadata_types' => true,
				'add_only_repository' => true,
				'meta_query' => [
					[
						'key'     => 'metadata_type',
						'value'   => 'Tainacan\Metadata_Types\Control',
						'compare' => 'IN'
					]
				]
			];
			$metadatum_repository = \Tainacan\Repositories\Metadata::get_instance();
			$metadata = $metadatum_repository->fetch_by_collection( $collection, $args );
			foreach ($metadata as $item_metadatum) {
				if ( $item_metadatum->get_metadata_type_object() instanceof \Tainacan\Metadata_Types\Control) {
					$update_item_metadatum = new \Tainacan\Entities\Item_Metadata_Entity( $item, $item_metadatum );
					switch ( $item_metadatum->get_metadata_type_object()->get_option('control_metadatum') ) {
						case 'document_type':
							$document_type = $item->get_document_type() == 'attachment' ? get_post_mime_type($item->get_document()) : $item->get_document_type();
							$update_item_metadatum->set_value( $document_type !== false ? $document_type : 'attachment' );
						break;

						case 'collection_id':
							$update_item_metadatum->set_value( $item->get_collection_id() );
						break;

						case 'has_thumbnail':
							$update_item_metadatum->set_value( !empty($item->get__thumbnail_id()) ? 'yes':'no' );
						break;
						
						default:
							$update_item_metadatum->set_value( $update_item_metadatum->get_value() );
						break;
					}

					if ( $update_item_metadatum->validate() )
						$item_metadata_repositories->insert( $update_item_metadatum );
					else
						$errors[] = $update_item_metadatum->get_errors();
				}
			}
			if($enabled_logs) $item_metadata_repositories->enable_logs();
		}
	}
}

/**
 * Class TainacanMetadatumType
 */
class Control extends Metadata_Type {

	private $metadataTypeControlHelper;

	function __construct() {
		// call metadatum type constructor
		parent::__construct();
		$this->set_primitive_type('control');
		$this->set_component('tainacan-text');
		$this->set_name( __('Control Type', 'tainacan') );
		$this->set_description( __('A special metadata type, used to map certain item properties such as collection ID and document type to metadata in order to easily create filters.', 'tainacan') );
		$this->set_default_options([
			'control_metadatum_options' => ['document_type', 'collection_id', 'has_thumbnail'],
			'control_metadatum' => 'document_type',
			'only_repository' => 'no'
		]);
		$metadataTypeControlHelper = MetadataTypeControlHelper::get_instance();
	}

	public static function get_helper() {
		return MetadataTypeControlHelper::get_instance();
	}

	public function validate_options( Metadatum $metadatum ) {
		if ( !in_array($metadatum->get_status(), apply_filters('tainacan-status-require-validation', ['publish','future','private'])) )
			return true;

		if ( empty($this->get_option('control_metadatum')) ) {
			return [
				'control_metadatum' => __('Required control metadatum.','tainacan')
			];
		}
		return true;
	}

	public function get_control_metadatum_value($value, $control_metadatum, $format) {
		$return = '';
		
		switch ( $control_metadatum ) {
			case 'document_type':
				$return = ($format == 'html' ? $this->get_document_as_html( $value ) : $this->get_document_as_string( $value ));
			break;

			case 'collection_id':
				$return = ($format == 'html' ? $this->get_collection_as_html( $value ) : $this->get_collection_as_string( $value ));
			break;

			case 'has_thumbnail':
				$return = ( $value == 'yes' ? __( 'yes', 'tainacan' ) : __( 'no', 'tainacan' ) );
			break;
			
			default:
				$return = $value;// What the hell am I doing here?
			break;
		}
		return $return;
	}

	/**
	 * Return the value of an Item_Metadata_Entity using a metadatum of this metadatum type as an html string
	 * @param  Item_Metadata_Entity $item_metadata 
	 * @return string The HTML representation of the value, containing one or multiple items names, linked to the item page
	 */
	public function get_value_as_html(\Tainacan\Entities\Item_Metadata_Entity $item_metadata) {
		
		$value = $item_metadata->get_value();
		$control_metadatum = $this->get_option('control_metadatum');

		if (in_array($control_metadatum, ['document_type', 'collection_id', 'has_thumbnail']))
			return $this->get_control_metadatum_value($value, $control_metadatum, 'html');
		
		$return = '';
		if ( $item_metadata->is_multiple() ) {
			$total = sizeof($value);
			$count = 0;
			$prefix = $item_metadata->get_multivalue_prefix();
			$suffix = $item_metadata->get_multivalue_suffix();
			$separator = $item_metadata->get_multivalue_separator();
			foreach ($value as $v) {
				$return .= $prefix;
				$return .= (string) $v;
				$return .= $suffix;
				$count ++;
				if ($count < $total)
					$return .= $separator;
			}
		} else {
			$return = (string) $value;
		}
		return $return;
	}

	/**
	 * Return the value of an Item_Metadata_Entity using a metadatum of this metadatum type as a string
	 * @param  Item_Metadata_Entity $item_metadata 
	 * @return string The String representation of the value, containing one or multiple items names, linked to the item page
	 */
	public function get_value_as_string(\Tainacan\Entities\Item_Metadata_Entity $item_metadata) {
		
		$value = $item_metadata->get_value();
		$control_metadatum = $this->get_option('control_metadatum');

		if (in_array($control_metadatum, ['document_type', 'collection_id', 'has_thumbnail']))
			return $this->get_control_metadatum_value($value, $control_metadatum, 'string');
		
		$return = '';
		if ( $item_metadata->is_multiple() ) {
			$total = sizeof($value);
			$count = 0;
			$prefix = $item_metadata->get_multivalue_prefix();
			$suffix = $item_metadata->get_multivalue_suffix();
			$separator = $item_metadata->get_multivalue_separator();
			foreach ($value as $v) {
				$return .= $prefix;
				$return .= (string) $v;
				$return .= $suffix;
				$count ++;
				if ($count < $total)
					$return .= $separator;
			}
		} else {
			$return = (string) $value;
		}
		
		return $return;
	}

	private function get_document_as_html( $value ) {
		return $this->get_document_as_string( $value );
	}

	public function get_document_as_string( $value ) {
		switch ($value) {
			case 'attachment':
				return __( 'File', 'tainacan' );
			break;
			
			case 'text':
				return __( 'Text', 'tainacan' );
			break;
			
			case 'url':
				return __( 'URL', 'tainacan' );
			break;

			case 'empty':
				return __( 'Empty', 'tainacan' );
			break;

			case 'application/pdf':
				return "PDF";
			break;
		}
		$type = explode( '/', $value );
		if (count($type) == 2 ) {
			switch ($type[0]) {
				case 'image':
					$value = __( 'Image', 'tainacan' ) . '/' . $type[1];
				break;
				case 'video':
					$value = __( 'Video', 'tainacan' ) . '/' . $type[1];
				break;
				case 'audio':
					$value = __( 'Audio', 'tainacan' ) . '/' . $type[1];
				break;
				case 'text':
					$value = __( 'Text', 'tainacan' ) . '/' . $type[1];
				break;
				case 'application':
					$value = __( 'Others', 'tainacan' ) . '/' . $type[1];
				break;
			}
		}
		return $value;
	}

	private function get_collection_as_html( $value ) {
		$collection = \Tainacan\Repositories\Collections::get_instance()->fetch( (int) $value );
		if ( $collection instanceof \Tainacan\Entities\Collection ) {
			$label = $collection->get_name();
			$link = $collection->get_url();
			$return = "<a data-linkto='collection' data-id='$value' href='$link'>";
			$return.= $label;
			$return .= "</a>";
			
			return $return;
		}
		return null;
	}

	private function get_collection_as_string( $value ) { 	
		$collection = \Tainacan\Repositories\Collections::get_instance()->fetch( (int) $value );
		if ( $collection instanceof \Tainacan\Entities\Collection ) {
			$label = $collection->get_name();
			return $label;
		}
		return null;
	}
	
}
