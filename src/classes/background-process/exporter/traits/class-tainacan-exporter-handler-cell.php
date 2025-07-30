<?php

namespace Tainacan\Traits;

defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

trait Exporter_Handler_Cell {

	function str_putcsv($input, $delimiter = ',', $enclosure = '"') {
		// Open a memory "file" for read/write...
		$fp = fopen('php://temp', 'r+');
		
		fputcsv($fp, $input, $delimiter, $enclosure);
		rewind($fp);
		//Getting detailed stats to check filesize:
		$fstats = fstat($fp);
		$data = fread($fp, $fstats['size']);
		fclose($fp);
		return rtrim($data, "\n");
	}

	function get_thumbnail_cell($item) {
		$thumbnail = $item->get__thumbnail_id();
		
		$url = wp_get_attachment_image_url($thumbnail, 'full');
		if ($url) $thumbnail = $url;
		
		return $thumbnail;
	}

	function get_document_cell($item) {
		$type = $item->get_document_type();
		if ($type == 'attachment') {
			$type = 'file';
		}

		$document = $item->get_document();
		if ($type == 'file') {
			$url = wp_get_attachment_url($document);
			if ($url) {
				$document = $url;
			}
		}
		return $type . ':' . $document;
	}

	function get_attachments_cell($item) {
		$attachments = $item->get_attachments();
		$attachments_urls = array_map(function($a) {
			if (isset($a->guid)) return $a->guid;
		}, $attachments);
		return implode( $this->get_option('multivalued_delimiter'), $attachments_urls );
	}

	function get_compound_metadata_cell($meta) {
		$enclosure = $this->get_option('enclosure');
		$delimiter = $this->get_option('delimiter');
		$multivalued_delimiter = $this->get_option('multivalued_delimiter');

		$metadata_type_options = $meta->get_metadatum()->get_metadata_type_options();
		$initial_values = [];
		foreach($metadata_type_options['children_order'] as $order) {
			$initial_values[$order['id']] = "";
		}
		$values = ($meta->get_metadatum()->is_multiple() ? $meta->get_value(): [$meta->get_value()]);
		$array_meta = [];
		foreach($values as $value) {
			$assoc_arr = array_reduce( $value, function ($result, $item) {
				$metadatum_id = $item->get_metadatum()->get_id();
				if ($item->get_metadatum()->get_metadata_type() == 'Tainacan\Metadata_Types\Relationship') {
					$result[$metadatum_id] = $item->get_value();
				} else {
					$result[$metadatum_id] = $item->get_value_as_string();
				}
				return $result;
			}, $initial_values);
			
			$array_meta[] = $this->str_putcsv($assoc_arr, $delimiter, $enclosure);
		}
		return implode($multivalued_delimiter, $array_meta);
	}

	function get_author_name_last_modification($item_id) {
		$last_id = get_post_meta( $item_id, '_user_edit_lastr', true );
		if ( $last_id ) {
			$last_user = get_userdata( $last_id );
 			return apply_filters( 'tainacan-the-modified-author', $last_user->display_name );
		}
		return "";
	}

	function get_description_title_meta($meta) {
		$meta_type =  explode('\\', $meta->get_metadata_type()) ;
		$meta_type = strtolower($meta_type[sizeof($meta_type)-1]);

		$meta_section_name = '';
		if ($this->get_option('add_section_name') == 'yes' && $current_collection = $this->get_current_collection_object()) {
			$meta_section_id = $meta->get_metadata_section_id();
			$collection_id = $current_collection->get_id();
			
			if($meta->is_repository_level()) {
				foreach($meta_section_id as $section_id ) {
					if($collection_id == get_post_meta($section_id, 'collection_id', true)) {
						$meta_section_name = get_the_title($section_id) . ': ';
						continue;
					}
				}
			} else {
				if($meta_section_id != \Tainacan\Entities\Metadata_Section::$default_section_slug) {
					$meta_section_name = get_the_title($meta_section_id) . ': ';
				}
			}
		}
		

		if($meta_type == 'compound') {
			$delimiter = $this->get_option('delimiter');
			$metadata_type_options = $meta->get_metadata_type_options();
			$desc_childrens = [];
			foreach($metadata_type_options['children_objects'] as $children) {
				$children_meta_type = explode('\\', $children['metadata_type']);
				$children_meta_type = strtolower($children_meta_type[sizeof($children_meta_type)-1]);
				$children_meta_type .=  ($children['collection_key'] === 'yes' ? '|collection_key_yes' : '');
				$desc_childrens[] = $children['name'] . '|' . $children_meta_type;
			}
			$meta_type .= "(" .  implode($delimiter, $desc_childrens)  . ")";
			$desc_title_meta = 
				$meta->get_name() .
				$meta_section_name .
				('|' . $meta_type) .
				($meta->is_multiple() ? '|multiple': '') .
				('|display_' . $meta->get_display());
		} else {
			$desc_title_meta = 
				$meta->get_name() .
				$meta_section_name .
				('|' . $meta_type) .
				($meta->is_multiple() ? '|multiple': '') .
				($meta->is_required() ? '|required': '') .
				('|display_' . $meta->get_display()) .
				($meta->is_collection_key() ? '|collection_key_yes' : '');
		}
		return $desc_title_meta;
	}
	
}