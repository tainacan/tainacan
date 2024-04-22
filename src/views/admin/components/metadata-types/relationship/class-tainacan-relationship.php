<?php

namespace Tainacan\Metadata_Types;

defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

/**
 * Class TainacanMetadatumType
 */
class Relationship extends Metadata_Type {

	function __construct(){
		// call metadatum type constructor
		parent::__construct();
		$this->set_primitive_type('item');
		$this->set_repository( \Tainacan\Repositories\Items::get_instance() );
		$this->set_component('tainacan-relationship');
		$this->set_form_component('tainacan-form-relationship');
		$this->set_name( __('Relationship', 'tainacan') );
		$this->set_description( __('A relationship with another item', 'tainacan') );
		$this->set_sortable( false );
		$this->set_preview_template('
			<div>
				<div class="taginput control is-expanded has-selected">
					<div class="taginput-container is-focusable"> 
						<div class="autocomplete control">
							<div class="control has-icon-right is-loading is-clearfix">
								<input type="text" class="input" value="'. __('Item') . ' 9" > 
							</div> 
							<div class="dropdown-menu" style="">
								<div class="dropdown-content">
									<a class="dropdown-item is-hovered">
										<span>'. __('Collection') . ' 3 <strong>'._('item') . ' 2</strong>9</span>
									</a>
									<a class="dropdown-item">
										<span>'. __('Collection') . ' 3 <strong>'._('item') . ' 4</strong>9</span>
									</a>
									<a class="dropdown-item">
										<span>'. __('Collection') . ' 3 <strong>'._('item') . ' 9</strong>8</span>
									</a>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		');
	}

	/**
	 * @inheritdoc
	 */
	public function get_form_labels(){
		return [
			'collection_id' => [
				'title' => __( 'Related Collection', 'tainacan' ),
				'description' => __( 'Select the collection to fetch items', 'tainacan' ),
			],
			'search' => [
				'title' => __( 'Related Metadatum', 'tainacan' ),
				'description' => __( 'Select the metadata to use as search criteria in the target collection and as a label when representing the relationship', 'tainacan' ),
			],
			'display_related_item_metadata' => [
				'title' => __( 'Displayed related item metadata', 'tainacan' ),
				'description' => __( 'Select the metadata that will be displayed from the related item.', 'tainacan' ),
			],
			'display_in_related_items' => [
				'title' => __( 'Display in "Items related to this"', 'tainacan' ),
				'description' => __( 'Include items linked by this metadata on a list of related items.', 'tainacan' ),
			],
			'accept_draft_items' => [
				'title' => __( 'List and accept draft items on the relation', 'tainacan' ),
				'description' => __( 'Include draft items as possible options to the relationship metadata.', 'tainacan' ),
			],
			'accept_only_items_authored_by_current_user' => [
				'title' => __( 'Bind items only by current author', 'tainacan' ),
				'description' => __( 'Accept stabelishing the replationship only with items authored by the current user editing the item.', 'tainacan' ),
			]
		];
	}

	/**
	 * Gets print-ready version of the options list in html
	 *
	 * Checks if at least one option exists, otherwise return an empty string
	 * 
	 * @return string An html content with labels and values for the options or an empty string
	 */
	public function get_options_as_html() {
		$options_as_html = '';
		$options = $this->get_options();
		
		if ( count($options) > 0 ) {

			// Remove this option that is not relevant for the user
			if ( isset($options['related_primitive_type']) )
				unset($options['related_primitive_type']);

			$form_labels = $this->get_form_labels();
				
			foreach($options as $option_label => $option_value) {

				if ( $option_value != '' ) {
					$options_as_html .= '<div class="field"><div class="label">' . ( isset($form_labels[$option_label]) && isset($form_labels[$option_label]['title']) ? $form_labels[$option_label]['title'] : $option_label ) .'</div>';
					
					$readable_option_value = '';

					switch($option_label) {
						
						case 'collection_id':
							$collection = \tainacan_collections()->fetch( (int) $option_value );
							if ( $collection instanceof \Tainacan\Entities\Collection )
								$readable_option_value = $collection->get_name();
							else
								$readable_option_value = $option_value;
						break;

						case 'search':
							$metadata = \tainacan_metadata()->fetch( (int) $option_value );
							if ( $metadata ) {
								$readable_option_value = $metadata;
							} else
								$readable_option_value = $option_value;
						break;

						case 'display_in_related_items':
						case 'accept_draft_items':
						case 'accept_only_items_authored_by_current_user':
							if ($option_value == 'yes')
								$readable_option_value = __('Yes', 'tainacan');
							else if ($option_value == 'no')
								$readable_option_value = __('No', 'tainacan');
							else
								$readable_option_value = $option_value;
						break;

						case 'display_related_item_metadata':
							if (  is_array($option_value) ) {
								$metadata_list = [];
								foreach($option_value as $metadata_id) {
									if ($metadata_id == 'thumbnail') {
										$metadata_list[] = __('Thumbnail', 'tainacan');
									} else {
										$metadata = \tainacan_metadata()->fetch( (int) $metadata_id );
										if ( $metadata ) $metadata_list[] = $metadata;
									}
									
								}
								$readable_option_value = implode(", ", $metadata_list);
							}
						break;

						default:
							$readable_option_value = is_string($option_value) ? $option_value : json_encode($option_value);
					}
					$options_as_html .= '<div class="value">' . $readable_option_value . '</div></div>';
				}
			}
		}
		return $options_as_html;
	}

	public function validate_options(\Tainacan\Entities\Metadatum $metadatum) {
		if ( !in_array($metadatum->get_status(), apply_filters('tainacan-status-require-validation', ['publish','future','private'])) )
			return true;

		if (!empty($this->get_option('collection_id')) && !is_numeric($this->get_option('collection_id'))) {
			return [
				'collection_id' => __('Invalid collection ID','tainacan')
			];
		} else if( empty($this->get_option('collection_id'))) {
			return [
				'collection_id' => __('The related collection is required','tainacan')
			];
		}
		// empty is ok
		if  ( !empty($this->get_option('search')) && !is_numeric($this->get_option('search')) ) {
			return [
				'search' => __('Search option must be a numeric Metadatum ID','tainacan')
			];
		}
		// empty is ok
		if ( !empty($this->get_option('display_in_related_items')) && !in_array($this->get_option('display_in_related_items'), ['yes', 'no']) ) {
			return [
				'display_in_related_items' => __('Display in related items must be an option yes or no','tainacan')
			];
		}
		// empty is ok
		if ( !empty($this->get_option('accept_draft_items')) && !in_array($this->get_option('accept_draft_items'), ['yes', 'no']) ) {
			return [
				'accept_draft_items' => __('Accept draft items must be an option yes or no','tainacan')
			];
		}
		// empty is ok
		if ( !empty($this->get_option('accept_only_items_authored_by_current_user')) && !in_array($this->get_option('accept_only_items_authored_by_current_user'), ['yes', 'no']) ) {
			return [
				'accept_only_items_authored_by_current_user' => __('Bind items only by current author must be an option yes or no','tainacan')
			];
		}
		
		return true;
	}

	/**
	 * Return the value of an Item_Metadata_Entity using a metadatum of this metadatum type as an html string
	 * @param  Item_Metadata_Entity $item_metadata 
	 * @return string The HTML representation of the value, containing one or multiple items names, linked to the item page
	 */
	public function get_value_as_html(\Tainacan\Entities\Item_Metadata_Entity $item_metadata) {
		$value = $item_metadata->get_value();
		$search_meta_id = $this->get_option('search');
		$display_metas = $this->get_option('display_related_item_metadata');
		
		$return = '';
		if ( $item_metadata->is_multiple() ) {
			$prefix = $item_metadata->get_multivalue_prefix();
			$suffix = $item_metadata->get_multivalue_suffix();
			$separator = $item_metadata->get_multivalue_separator();
			
			foreach ( $value as $item_id ) {
				try {
					$Tainacan_Items = \Tainacan\Repositories\Items::get_instance();
					$item = $Tainacan_Items->fetch( (int) $item_id);
					if ( $this->can_display_item($item) ) {
						$return .= empty($return)
							? ($prefix . $this->get_item_html($item, $search_meta_id, $display_metas) . $suffix)
							: ($separator . $prefix . $this->get_item_html($item, $search_meta_id, $display_metas) . $suffix);
					}
				} catch (\Exception $e) {
					// item not found
				}
			}
		} else {
			try {
				$item = new \Tainacan\Entities\Item($value);
				if ( $this->can_display_item($item) ) {
					$return .= $this->get_item_html($item, $search_meta_id, $display_metas);
				}
			} catch (\Exception $e) {
				// item not found 
			}
		}
		if(!empty($display_metas) && is_array($display_metas) && count($display_metas) > 1 && $return !== '') {
			return "<div class='tainacan-relationship-group'>{$return}</div>";
		}
		return $return;
	}

	private function can_display_item($item) {
		return (
			$item instanceof \Tainacan\Entities\Item && (
				is_user_logged_in() ||
				(
					\is_post_status_viewable( $item->get_status() ) &&
					($item->get_collection() != null && \is_post_status_viewable( $item->get_collection()->get_status() ))
				)
			)
		);
	}

	private function get_item_html($item, $search_meta_id, $display_metas) {
		$return = '';
		$id = $item->get_id();
		
		if(!empty($display_metas) && is_array($display_metas) && count($display_metas) > 1) {
			$has_thumbnail = array_search('thumbnail', $display_metas);
			$thumbnail_id = false;
			if($has_thumbnail !== false) {
				unset($display_metas[$has_thumbnail]);
				$thumbnail_id = $item->get__thumbnail_id();
			}
			$args = ['post__in' => $display_metas];
			$metadatum = $item->get_metadata($args);

			$metadata_value = [];
			foreach ( $metadatum as $item_meta_id => $item_meta ) {
				if ( $item_meta instanceof \Tainacan\Entities\Item_Metadata_Entity && $item_meta->get_value_as_html() != '' ) {
					$meta_id = $item_meta->get_metadatum()->get_id();
					$as_header = $search_meta_id == $meta_id ? $this->get_item_link($item, $search_meta_id) : false;
					$html = $this->get_meta_html($item_meta, $item, $as_header, $thumbnail_id, $has_thumbnail);
					if($as_header === false) {
						$metadata_value[] = $html;
					} else {
						array_unshift($metadata_value, $html);
					}
				}
				$return = implode("\n", $metadata_value);
			}
			$return = "<div class='tainacan-relationship-metadatum' data-item-id='$id'>{$return}</div>";
		} else if ( $id && $search_meta_id ) {
			$as_link = $this->get_item_link($item, $search_meta_id);
			$return = "$as_link";
		}

		return $return;
	}

	private function get_item_link($item, $search_meta_id) {
		$return = '';
		$id = $item->get_id();
		$link = get_permalink( (int) $id );
		$metadatum = \Tainacan\Repositories\Metadata::get_instance()->fetch((int) $search_meta_id);
		$value = '';
		if ($metadatum instanceof \Tainacan\Entities\Metadatum) {
			$item_meta = new \Tainacan\Entities\Item_Metadata_Entity($item, $metadatum);
			$value = $item_meta->get_value_as_string();
		}
		if ( empty($value) ) {
			$value = $item->get_title();
		}
		if (is_string($link)) {
			$return = "<a data-linkto='item' data-id='$id' href='$link'>$value</a>";
		}
		return $return;
	}

	private function get_item_thumbnail($thumbnail_id, $item) {
		if($thumbnail_id !== false && !empty($thumbnail_id)){
			return \wp_get_attachment_image($thumbnail_id, 'tainacan-small');
		}
		$media_type = $item->get_document_mimetype();
		$placeholder_image = '<img src="' . \tainacan_get_the_mime_type_icon($media_type, 'tainacan-small') . '" />';
		return $placeholder_image;
	}

	private function get_meta_html(\Tainacan\Entities\Item_Metadata_Entity $meta, \Tainacan\Entities\Item $item, $value_link = false, $thumbnail_id = false, $should_display_thumbnail = true) {
		$html = '';
		if ($meta instanceof \Tainacan\Entities\Item_Metadata_Entity && !empty($meta->get_value_as_html())) {
			ob_start();
			if ($value_link) {
				?>
					<div class="tainacan-relationship-metadatum-header">
						<?php echo ($should_display_thumbnail ? $this->get_item_thumbnail($thumbnail_id, $item) : ''); ?>
						<h4 class="label">
							<?php
							/**
							 * Note to code reviewers: This lines doesn't need to be escaped.
							 * The variable $value_link is escaped.
							 */
							echo $value_link;
							?>
						</h4>
					</div>
				<?php
			} else {
				?>
					<div class="tainacan-metadatum">
						<h5 class="label">
							<?php echo esc_html($meta->get_metadatum()->get_name()); ?>
						</h5>
						<p>
							<?php echo wp_kses_tainacan(($value_link === false ? $meta->get_value_as_html() : $value_link)); ?> 
						</p>
					</div>
				<?php
			}
			$html = ob_get_contents();
			ob_end_clean();
		}

		return $html;
	}

	/**
	 * Get related Collection object 
	 * @return \Tainacan\Entities\Collection|false The Collection object or false
	 */
	public function get_collection() {
		$collection_id = $this->get_option('collection_id');
		
		if ( is_numeric($collection_id) ) {
			$collection = \Tainacan\Repositories\Collections::get_instance()->fetch( (int) $collection_id );
			if ( $collection instanceof \Tainacan\Entities\Collection ) {
				return $collection;
			}
		}
		
		return false;
	}

	/**
	 * Gets the options for this metadatum types, including default values for options
	 * that were not set yet.
	 * @return array Metadatum type options
	 */
	public function get_options() {
		$opt = parent::get_options();
		if ( isset($opt['search']) && !empty($opt['search']) ) {
			$search_id = $opt['search'];
			$metadata = \Tainacan\Repositories\Metadata::get_instance()->fetch($search_id, 'OBJECT');
			if( $metadata instanceof \Tainacan\Entities\Metadatum ) {
				$opt = array_merge(['related_primitive_type' => $metadata->get_metadata_type_object()->get_primitive_type()], $opt);

				if ($metadata->get_metadata_type() == 'Tainacan\Metadata_Types\Taxonomy') {
					$taxonomy_id = $metadata->get_metadata_type_options()['taxonomy_id'];
					return array_merge(['search_by_tax' => $taxonomy_id], $opt);
				}
			}
		}
		return $opt;
	}
}