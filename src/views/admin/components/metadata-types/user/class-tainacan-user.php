<?php

namespace Tainacan\Metadata_Types;

use Tainacan\Entities\Metadatum;


defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

/**
 * Class TainacanMetadatumType
 */
class User extends Metadata_Type {

	function __construct(){
		// call metadatum type constructor
		parent::__construct();
		$this->set_primitive_type('user');
		$this->set_component('tainacan-user');
		$this->set_form_component('tainacan-form-user');
		$this->set_name( __('User', 'tainacan') );
		$this->set_description( __('A registered user on WordPress', 'tainacan') );
		$this->set_preview_template('
			<div>
				<div class="control is-clearfix">
					user
				</div>
			</div>
		');
	}

	function user_exists($user) {
		if ( empty($user) ) 
			return true;
		// if( !is_int($user) )
		// 	return username_exists($user) !== false;

		global $wpdb;
		// Check cache:
		if (wp_cache_get($user, 'users')) return true;
		// Check database:
			if ($wpdb->get_var($wpdb->prepare("SELECT EXISTS (SELECT 1 FROM $wpdb->users WHERE ID = %d)", $user)))
				return true;
			return false;
	}
	
	/**
	 * User metadatum type is stored as id of WP user 
	 *
	 * @param  TainacanEntitiesItem_Metadata_Entity $item_metadata
	 * @return bool Valid or not
	 * 
	 */
	public function validate(\Tainacan\Entities\Item_Metadata_Entity $item_metadata) {
		$value = $item_metadata->get_value();
		if ( is_array($value) ) {
			foreach($value as $user) {
				if ( !$this->user_exists($user) ) {
					$this->add_error( sprintf(__('User does not exist (ID: %s).', 'tainacan'), $user ) );
					return false;
				}
			}
		} else {
			if ( !$this->user_exists($value) ) {
				$this->add_error( sprintf( __('User does not exist (ID: %s).', 'tainacan'), $value ) );
				return false;
			}
		}
		return true;
	}

	/**
     * @inheritdoc
     */
    public function get_form_labels(){
        return [
            'default_author' => [
                'title' => __( 'Defaults to author user', 'tainacan' ),
                'description' => __( 'This sets the default value of this metadata as the current item author.', 'tainacan' ),
            ]
        ];
    }

	public function validate_options( Metadatum $metadatum ) {
		if ( !in_array($metadatum->get_status(), apply_filters('tainacan-status-require-validation', ['publish','future','private'])) ) {
			return true;
		}

		if ( empty( $this->get_option('default_author') ) || !in_array ( $this->get_option('default_author'), ['yes', 'no'] ) ) {
			return ['default_author' => __('Invalid config default author','tainacan')];
		}
		return true;
	}

	/**
	 * Get the value as a HTML string
	 * @return string
	 */
	public function get_value_as_html(\Tainacan\Entities\Item_Metadata_Entity $item_metadata) {
		$value = $item_metadata->get_value();
		if (empty($value)) 
			return "";
		$name = get_the_author_meta( 'display_name', $value );
		return apply_filters("tainacan-item-get-author-name", $name, $this);
	}
	
}