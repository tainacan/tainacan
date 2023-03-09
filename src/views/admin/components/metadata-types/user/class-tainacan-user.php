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
		$this->set_default_options([
			'default_author' => 'no'
		]);
		$this->set_sortable( false );
		$this->set_preview_template('
			<div class="taginput control is-expanded has-selected">
				<div class="taginput-container is-small is-focusable">
					<div class="tags has-addons">
						<span class="tag is-small">
							<span>' . __('User', 'tainacan') .' 1</span>
						</span>
						<a class="tag is-delete is-small"></a>
					</div>
					<div class="tags has-addons">
						<span class="tag is-small">
							<span>' . __('User', 'tainacan') .' 2</span>
						</span>
						<a class="tag is-delete is-small"></a>
					</div>
					<div class="autocomplete control">
						<div class="control has-icons-left has-icons-right is-small">
							<input type="text" autocomplete="off" id="tainacan-user-usuario" placeholder="Type to search users..." class="input is-small">
							<span class="icon is-left is-small">
								<i class="mdi mdi-account"></i>
							</span>
							<span class="icon is-right is-small is-clickable">
								<i class="mdi mdi-alert-circle"></i>
							</span>
						</div>
						<div class="dropdown-menu">
							<div class="dropdown-content">
								<a class="dropdown-item">
									<div class="media">
										<div class="media-left">
											<img src="http://1.gravatar.com/avatar/194c59734d14b92b3b37825616fa1876?s=24&amp;d=mm&amp;r=g" width="16">
										</div>
										<div class="media-content"><span>' . __('User', 'tainacan') .' 3</span></div>
									</div>
								</a>
								<a class="dropdown-item is-hovered">
									<div class="media">
										<div class="media-left">
											<img src="http://1.gravatar.com/avatar/194c59734d14b92b3b37825616fa1876?s=24&amp;d=mm&amp;r=g" width="16">
										</div>
										<div class="media-content"><span>' . __('User', 'tainacan') .' 4</span></div>
									</div>
								</a>
								<a class="dropdown-item">
									<div class="media">
										<div class="media-left">
											<img src="http://1.gravatar.com/avatar/194c59734d14b92b3b37825616fa1876?s=24&amp;d=mm&amp;r=g" width="16">
										</div>
										<div class="media-content"><span>' . __('User', 'tainacan') .' 5</span></div>
									</div>
								</a>
							</div>
						</div>
					</div>
				</div>
			</div>
		');
	}

	function user_exists($user) {
		if( !is_int($user) ) {
			return false;
		}

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
				if ( !empty($user) && !$this->user_exists((int)$user) ) {
					$this->add_error( sprintf(__('User does not exist (ID: %s).', 'tainacan'), $user ) );
					return false;
				}
			}
		} else {
			if ( !empty($value) && !$this->user_exists((int)$value) ) {
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
		$values = $item_metadata->get_value();
		if (empty($values))
			return "";
		$values = is_array($values) ? $values: [$values];
		$response = [];
		$multivalue_separator = $item_metadata->get_multivalue_separator();
		foreach($values as $value) {
			$name = get_the_author_meta( 'display_name', $value );
			$response[] = apply_filters("tainacan-item-get-author-name", $name, $this);
		}
		return implode($multivalue_separator, $response);
	}
	
}