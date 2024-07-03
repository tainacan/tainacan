<?php

namespace Tainacan\Metadata_Types;

use Tainacan\Entities\Metadatum;
use Tainacan\Entities\Item_Metadata_Entity;
use Tainacan\Repositories\Metadata;

defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

/**
 * Class TainacanMetadatumType
 */
class Taxonomy extends Metadata_Type {

	function __construct(){
		// call metadatum type constructor
		parent::__construct();
		$this->set_primitive_type('term');
		$this->set_repository( \Tainacan\Repositories\Terms::get_instance() );
		
		$this->set_default_options([
			'allow_new_terms' => 'no',
			'link_filtered_by_current_collection' => 'no',
			'link_filtered_by_collections' => [],
			'input_type' => 'tainacan-taxonomy-radio',
			'hide_hierarchy_path' => 'no',
			'do_not_dispaly_term_as_link' => 'no',
		]);

		$this->set_form_component('tainacan-form-taxonomy');
		$this->set_component('tainacan-taxonomy');
		$this->set_name( __('Taxonomy', 'tainacan') );
		$this->set_description( __('A metadatum to use a taxonomy in this collection', 'tainacan') );
		$this->set_sortable( false );
		$this->set_preview_template('
			<div>
				<div>
					<p class="has-text-gray" style="font-size: 0.75em;">'. __('Selected terms') . ': </p>
					<div class="field selected-tags is-grouped-multiline is-grouped">
						<div>
							<div class="tags has-addons">
								<span class="tag is-small"><span>'. __('Term') . ' 2</span></span>
								<a class="tag is-delete is-small"></a>
							</div>
						</div>
						<div>
							<div class="tags has-addons">
								<span class="tag is-small"><span>'. __('Term') . ' 3</span></span>
								<a class="tag is-delete is-small"></a>
							</div>
						</div>
					</div>
					<div>
						<label class="b-checkbox checkbox" border="" style="padding-left: 8px;">
							<input type="checkbox" value="option1">
							<span class="check"></span>
							<span class="control-label">'. __('Term') . ' 1</span>
						</label>
						<br>
					</div>
					<div>
						<label class="b-checkbox checkbox" border="" style="padding-left: 8px;">
							<input type="checkbox" checked value="option2">
							<span class="check"></span>
							<span class="control-label">'. __('Term') . ' 2</span>
						</label>
					</div>
					<div>
						<label class="b-checkbox checkbox" border="" style="padding-left: 8px;">
							<input type="checkbox" checked value="option3">
							<span class="check"></span>
							<span class="control-label">'. __('Term') . ' 3</span>
						</label>
					</div>
				</div>
				<a class="add-new-term">'. __('View all') . '</a>
			</div>
		');

	}

	/**
	 * @inheritdoc
		*/
	public function get_form_labels(){
		return [
			'taxonomy_id' => [
				'title' => __( 'Related Taxonomy', 'tainacan' ),
				'description' => __( 'Select the taxonomy to fetch terms', 'tainacan' ),
			],
			'input_type' => [
				'title' => __( 'Input type', 'tainacan' ),
				'description' => __( 'The html type of the terms list ', 'tainacan' ),
			],
			'visible_options_list' => [
				'title' => __( 'Always visible options list', 'tainacan' ),
				'description' => __( 'Check this option if you are displaying a checkbox or radio input type and wish the options list to always be visible.', 'tainacan' ),
			],
			'allow_new_terms' => [
				'title' => __( 'Allow new terms', 'tainacan' ),
				'description' => __( 'Allows to create new terms directly on the item form.', 'tainacan' ),
			],
			'link_filtered_by_current_collection' => [
				'title' => __( 'Link filtered by current collection', 'tainacan' ),
				'description' => __( 'Links to term items list filtered by the collection of the current item instead of a repository level term items page.', 'tainacan' ),
			],
			'link_filtered_by_collections' => [
				'title' => __( 'Link filtered by collections', 'tainacan' ),
				'description' => __( 'Links to term items list filtered by certain collections instead of repository level term items page.', 'tainacan' ),
			],
			'hide_hierarchy_path' => [
				'title' => __( 'Hide hierarchy path', 'tainacan' ),
				'description' => __( 'Display only the current child term when showing values that belong to a term hierarchy.', 'tainacan' ),
			],
			'do_not_dispaly_term_as_link' => [
				'title' => __( 'Do not display term as link', 'tainacan' ),
				'description' => __( 'Do not show terms page link in the term name.', 'tainacan' ),
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

			// Remove this option as it doesn't matter if using a taginput
			if ( isset($options['visible_options_list']) && isset($options['input_type']) && $options['input_type'] == 'tainacan-taxonomy-tag-input' )
				unset($options['visible_options_list']);

			$form_labels = $this->get_form_labels();
			
			foreach($options as $option_label => $option_value) {

				if ( $option_value != '' && $option_label != 'taxonomy' ) {
					$options_as_html .= '<div class="field"><div class="label">' . ( isset($form_labels[$option_label]) && isset($form_labels[$option_label]['title']) ? $form_labels[$option_label]['title'] : $option_label ) .'</div>';
					
					$readable_option_value = '';

					switch($option_label) {
						
						case 'taxonomy_id':
							$taxonomy = \tainacan_taxonomies()->fetch( (int) $option_value );
							if ( $taxonomy instanceof \Tainacan\Entities\Taxonomy )
								$readable_option_value = $taxonomy->get_name();
							else
								$readable_option_value = $option_value;
						break;

						case 'input_type':
								if ($option_value == 'tainacan-taxonomy-radio')
									$readable_option_value = __('Radio', 'tainacan');
								else if ($option_value == 'tainacan-taxonomy-checkbox')
									$readable_option_value = __('Checkbox', 'tainacan');
								else if ($option_value == 'tainacan-taxonomy-tag-input')
									$readable_option_value = __('Taginput', 'tainacan');
								else
									$readable_option_value = $option_value;
						break;

						case 'visible_options_list':
							if ($option_value == 1)
								$readable_option_value = __('Yes', 'tainacan');
							else if ($option_value == 0)
								$readable_option_value = __('No', 'tainacan');
							else
								$readable_option_value = $option_value;
						break;

						case 'allow_new_terms':
						case 'do_not_dispaly_term_as_link':
						case 'link_filtered_by_current_collection':
							if ($option_value == 'yes')
								$readable_option_value = __('Yes', 'tainacan');
							else if ($option_value == 'no')
								$readable_option_value = __('No', 'tainacan');
							else
								$readable_option_value = $option_value;
						break;

						case 'link_filtered_by_collections':
							if (count($option_value) > 0) {
								$collections = \tainacan_collections()->fetch( [ 'post__in' => $option_value ], 'OBJECT' );
								
								if ( is_array($collections) ) {
									
									$collection_names = '';
									for ($i = 0; $i < count($collections); $i++) {
										$collection_names .= $collections[$i]->get_name();
										if ($i < count($collections) - 1)
											$collection_names .= ', ';
									}

									$readable_option_value = $collection_names;
								}
								else
									$readable_option_value = $option_value;
							} else
								$readable_option_value = __( 'None', 'tainacan' );
						break;

						case 'hide_hierarchy_path':
							if ($option_value == 'yes')
								$readable_option_value = __('Yes', 'tainacan');
							else if ($option_value == 'no')
								$readable_option_value = __('No', 'tainacan');
							else
								$readable_option_value = $option_value;
						break;

						default:
							$readable_option_value = $option_value;
					}
					$options_as_html .= '<div class="value">' . $readable_option_value . '</div></div>';
				}
            }
        }
        return $options_as_html;
    }

	public function validate_options( Metadatum $metadatum) {

		if ( !in_array($metadatum->get_status(), apply_filters('tainacan-status-require-validation', ['publish','future','private'])) )
			return true;

		if (empty($this->get_option('taxonomy_id')))
			return ['taxonomy_id' => __('Please select a taxonomy', 'tainacan')];

		$options = $metadatum->get_metadata_type_options();
		if ( !$metadatum->is_multiple() && $this->get_option('input_type') !== 'tainacan-taxonomy-radio' ) {
			return ['input_type' => __('A taxonomy metadata that does not accept multiple values should use a radio type input', 'tainacan')];
		}

		$Tainacan_Metadata = Metadata::get_instance();

		// Check taxonomy visibility
		$status = get_post_status( $this->get_option('taxonomy_id') );
		$post_status_obj = get_post_status_object($status);
		if ( ! $post_status_obj->public ) {
			$meta_status_obj = get_post_status_object($metadatum->get_status());
			if ( $meta_status_obj->public ) {
				return ['taxonomy_id' => __('This metadatum cannot be public because chosen taxonomy is not.', 'tainacan')];
			}
		}

		if ( $this->get_option('allow_new_terms') == 'yes' ) {
			$taxonomy = \tainacan_taxonomies()->fetch( (int) $this->get_option('taxonomy_id') );
			if ( $taxonomy instanceof \Tainacan\Entities\Taxonomy ) {
				if ( $taxonomy->get_allow_insert() != 'yes' ) {
					return ['allow_new_terms' => __('This metadatum cannot allow new terms to be created because the chosen taxonomy does not allow it.', 'tainacan')];
				}
			}
		}

		$collections = $metadatum->get_collection_id() == 'default' ? [] : [$metadatum->get_collection_id(), 'default'];
		$taxonomy_metadata = $Tainacan_Metadata->fetch([
			'collection_id' => $collections,
			'metadata_type' => 'Tainacan\\Metadata_Types\\Taxonomy'
		], 'OBJECT');

		$taxonomy_metadata = array_map(function ($metadatum_map) {
			$fto = $metadatum_map->get_metadata_type_object();
			return [ $metadatum_map->get_id() => $fto->get_option('taxonomy_id') ];
		}, $taxonomy_metadata);

		if( is_array( $taxonomy_metadata ) ){
			foreach ($taxonomy_metadata as $metadatum_id => $taxonomy_metadatum) {
				if ( is_array( $taxonomy_metadatum ) && key($taxonomy_metadatum) != $metadatum->get_id()
					&& in_array($this->get_option('taxonomy_id'), $taxonomy_metadatum)) {
					return ['taxonomy_id' => __('You cannot have 2 taxonomy metadata using the same taxonomy in a collection or repository level.', 'tainacan')];
				}
			}
		}
		
		$collection_ancestors = get_post_ancestors( $metadatum->get_collection_id() );
		$descendants = $this->get_collection_children( $metadatum->get_collection_id() );
		$collections_id = array_merge($collection_ancestors, $descendants);
		if (!empty($collections_id)) {
			$taxonomy_metadata = $Tainacan_Metadata->fetch([
				'collection_id' =>  $collections_id,
				'metadata_type' => 'Tainacan\\Metadata_Types\\Taxonomy'
			], 'OBJECT');
	
			$taxonomy_metadata = array_map(function ($metadatum_map) {
				$fto = $metadatum_map->get_metadata_type_object();
				return [ $metadatum_map->get_id() => $fto->get_option('taxonomy_id') ];
			}, $taxonomy_metadata);
	
			if( is_array( $taxonomy_metadata ) ){
				foreach ($taxonomy_metadata as $metadatum_id => $taxonomy_metadatum) {
					if ( is_array( $taxonomy_metadatum ) && key($taxonomy_metadatum) != $metadatum->get_id()
						&& in_array($this->get_option('taxonomy_id'), $taxonomy_metadatum)) {
						return ['taxonomy_id' => __('You cannot have 2 taxonomy metadata using the same taxonomy in a ancestors or descendants collection.', 'tainacan')];
					}
				}
			}
		}

		return true;

	}

	/**
	 * Validate item based on metadatum type taxonomies options
	 *
	 * @param Item_Metadata_Entity $item_metadata
	 *
	 * @return bool Valid or not
	 */
	public function validate( Item_Metadata_Entity $item_metadata) {

		$item = $item_metadata->get_item();

		// if ( !in_array($item->get_status(), apply_filters('tainacan-status-require-validation', ['publish','future','private'])) )
		// 	return true;

		$valid = true;

		if ('no' === $this->get_option('allow_new_terms') || false === $this->get_option('allow_new_terms')) { //support legacy bug when it was saved as false
			$terms = $item_metadata->get_value();

			if (false === $terms)
				return true;

			if (!is_array($terms))
				$terms = array($terms);

			foreach ($terms as $term) {
				if (is_object($term) && $term instanceof \Tainacan\Entities\Term) {
					$term = $term->get_id();
				}

				// TODO term_exists is not fully reliable. Use $terms_repository->term_exists. see issue #159
				if (!term_exists($term)) {
					$valid = false;
					$this->add_error(__('term not found.', 'tainacan'));
					break;
				}
			}

		}

		return $valid;
	}
	
	/**
	 * Return the value of an Item_Metadata_Entity using a metadatum of this metadatum type as an html string
	 * @param  Item_Metadata_Entity $item_metadata
	 * @return string The HTML representation of the value, containing one or multiple terms, separated by comma, linked to term page
	 */
	public function get_value_as_html(Item_Metadata_Entity $item_metadata) {
		$value = $item_metadata->get_value();
		$return = '';

		if ( !isset($value) )
			return $return;
		
		if ( $item_metadata->is_multiple() ) {
			$count = 1;
			$total = sizeof($value);
			$prefix = $item_metadata->get_multivalue_prefix();
			$suffix = $item_metadata->get_multivalue_suffix();
			$separator = $item_metadata->get_multivalue_separator();

			foreach ( $value as $term ) {
				$count++;

				if ( is_integer($term) ) {
					$term = \Tainacan\Repositories\Terms::get_instance()->fetch($term, $this->get_option('taxonomy_id'));
				}

				if ( $term instanceof \Tainacan\Entities\Term ) {
					$return .= $prefix;
					$return .= $this->get_term_hierarchy_html($term, $item_metadata->get_item());
					$return .= $suffix;

					if ( $count <= $total ) {
						$return .= $separator;
					}
				}
			}
		} else {
			if ( $value instanceof \Tainacan\Entities\Term ) {
				$return .= $this->get_term_hierarchy_html($value, $item_metadata->get_item());
			}
		}

		return $return;
	}

	private function get_term_hierarchy_html( \Tainacan\Entities\Term $term, \Tainacan\Entities\Item $item = null) {

		if ( $this->get_option('hide_hierarchy_path') == 'yes' )
			return $this->term_to_html($term, $item);

		$terms = [];
		$terms[] = $this->term_to_html($term, $item);

		while ($term->get_parent() > 0) {
			$term = \Tainacan\Repositories\Terms::get_instance()->fetch( (int) $term->get_parent(), $term->get_taxonomy() );
			$terms[] = $this->term_to_html($term, $item);
		}

		$terms = \array_reverse($terms);
		$glue = apply_filters('tainacan-terms-hierarchy-html-separator', '<span class="hierarchy-separator"> > </span>');

		return \implode($glue, $terms);
	}

	private function term_to_html($term, \Tainacan\Entities\Item $item = null) {
		$collections = ( isset($item) && $this->get_option( 'link_filtered_by_current_collection' ) === 'yes' ) ? [ $item->get_collection_id() ] : $this->get_option( 'link_filtered_by_collections' );
		$do_not_display_term_as_link = $this->get_option('do_not_dispaly_term_as_link') == 'yes';

		if ( !empty( $collections ) ) {
			$return = '';
			$id = $term->get_id();

			if ( $id ) {
				if ( $do_not_display_term_as_link )
					$return = $term->get_name();
				else {
					$link = get_term_link( (int) $id );
					if (is_string($link)) {
						$meta_query = [
							'metaquery' => [
								[
									'key' => 'collection_id',
									'compare' => 'IN',
									'value' => $collections
								]
							]
						];
						$link = $link . '?' . http_build_query( $meta_query );
						$return = "<a data-linkto='term' data-id='$id' href='$link'>";
						$return.= $term->get_name();
						$return .= "</a>";
					}
				}
			}
			return $return;
		}
		return $do_not_display_term_as_link ? $term->get_name() : $term->_toHtml();
	}

	public function _toArray() {

		$array = parent::_toArray();

		if ( isset($array['options']['taxonomy_id']) ) {
			$term_taxonomy = $this->get_taxonomy();

			$array['options']['taxonomy'] = \Tainacan\Repositories\Taxonomies::get_instance()->get_db_identifier_by_id( $array['options']['taxonomy_id'] );
			$array['options']['hierarchical'] = $term_taxonomy == false ? true : $term_taxonomy->get_hierarchical();
		}
		return $array;

	}

	function get_collection_children($parent_id){
		$children = array();
		// grab the posts children
		$posts = get_posts(
			[
				'numberposts' => -1, 
				'post_status' => 'any',
				'post_type' => \Tainacan\Entities\Collection::get_post_type(),
				'post_parent' => $parent_id
			]
		);
		foreach( $posts as $child ){
			$gchildren = $this->get_collection_children($child->ID);
			if( !empty($gchildren) ) {
				$children = array_merge($children, $gchildren);
			}
		}
		$children = array_merge($children, array_map( function($p) { return $p->ID; }, $posts) );
		return $children;
	}

	/**
	 * Get related taxonomy object
	 * @return \Tainacan\Entities\Taxonomy|false The Taxonomy object or false
	 */
	public function get_taxonomy() {

		$taxonomy_id = $this->get_option('taxonomy_id');

		if ( is_numeric($taxonomy_id) ) {
			$taxonomy = \Tainacan\Repositories\Taxonomies::get_instance()->fetch( (int) $taxonomy_id );
			if ( $taxonomy instanceof \Tainacan\Entities\Taxonomy ) {
				return $taxonomy;
			}
		}

		return false;

	}

}
