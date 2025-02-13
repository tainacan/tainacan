<?php

namespace Tainacan;

use phpDocumentor\Reflection\DocBlock\Tags\Var_;

class Settings extends Pages {
	use \Tainacan\Traits\Singleton_Instance;

	public function init() {
		parent::init();
		add_action( 'admin_init', array( &$this, 'settings_init' ) );
	}

	public function add_admin_menu() {
		
		add_submenu_page(
			$this->tainacan_root_menu_slug,
			__('Other', 'tainacan'),
			'<span class="icon">' . $this->get_svg_icon( 'viewminiature' ) . '</span><span class="menu-text">' .__( 'Other', 'tainacan' ) . '</span>',
			'read',
			$this->tainacan_other_links_slug,
			'#'
		);

		$tainacan_page_suffix = add_submenu_page(
			$this->tainacan_other_links_slug,
			__('Settings', 'tainacan'),
			'<span class="icon">' . $this->get_svg_icon( 'settings' ) . '</span><span class="menu-text">' .__( 'Settings', 'tainacan' ) . '</span>',
			'manage_options',
			'tainacan_settings',
			array( &$this, 'render_page' )
		);
		add_action( 'load-' . $tainacan_page_suffix, array( &$this, 'load_page' ) );
	}

	function admin_enqueue_css() {
		global $TAINACAN_BASE_URL;
		wp_enqueue_style( 'tainacan-settings-page', $TAINACAN_BASE_URL . '/assets/css/tainacan-settings.css', [], TAINACAN_VERSION );
	}

	public function render_page_content() {
		require_once('page.php');
	}

	public function settings_init() {

		/**
		 * Search and Performance -----------------------------------------------------
		 */
		add_settings_section(
			'tainacan_settings_search_and_performance', // ID
			__( 'Search and performance', 'tainacan' ), // Title
			array( $this, 'search_and_performance_section_description' ), // Callback
			'tainacan_settings'               		    // Page
		);
		
		$this->create_tainacan_setting( array(
			'id' => 'search_results_per_page',
			'title' => __( 'Search results per page', 'tainacan' ),
			'section' => 'tainacan_settings_search_and_performance',
			'type' => 'number',
			'input_type' => 'number',
			'input_attrs' => 'min=0',
			'input_disabled' => defined('TAINACAN_API_MAX_ITEMS_PER_PAGE'),
			'description' => sprintf( __( 'Number of items to show in search results. The default is %s and larger numbers should be avoided as it impacts in your server load time.', 'tainacan' ), ( defined('TAINACAN_API_MAX_ITEMS_PER_PAGE') ? TAINACAN_API_MAX_ITEMS_PER_PAGE : 96 ) ),
			'sanitize_callback' => 'absint',
			'default' => defined('TAINACAN_API_MAX_ITEMS_PER_PAGE') ? TAINACAN_API_MAX_ITEMS_PER_PAGE : 96,
			'forced_value' => defined('TAINACAN_API_MAX_ITEMS_PER_PAGE') ? TAINACAN_API_MAX_ITEMS_PER_PAGE : null
		) );

		$this->create_tainacan_setting( array(
			'id' => 'index_pdf_content',
			'section' => 'tainacan_settings_search_and_performance',
			'title' => __( 'PDF content', 'tainacan' ),
			'label' => __( 'Index textual content from PDF files in search results', 'tainacan' ),
			'description' => __( 'Enable this option to index the content of PDF files. This will increase the search results accuracy but also the server load.', 'tainacan' ),
			'type' => 'boolean',
			'input_type' => 'checkbox',
			'input_disabled' => defined('TAINACAN_INDEX_PDF_CONTENT'),
			'sanitize_callback' => 'rest_sanitize_boolean',
			'default' => defined('TAINACAN_INDEX_PDF_CONTENT') ? TAINACAN_INDEX_PDF_CONTENT : false,
			'forced_value' => defined('TAINACAN_INDEX_PDF_CONTENT') ? TAINACAN_INDEX_PDF_CONTENT : null
		) );

		$this->create_tainacan_setting( array(
			'id' => 'enable_default_search_engine',
			'section' => 'tainacan_settings_search_and_performance',
			'title' => __( 'Fields for textual search', 'tainacan' ),
			'label' => __( 'Enable the search on every metadata.', 'tainacan' ),
			'description' => __( 'Check this option to enable Tainacan\'s textual search in every metadata of the collection. If disabled, only title and description will be considered, which may improve the search perfomance.', 'tainacan' ),	
			'type' => 'boolean',
			'input_type' => 'checkbox',
			'input_disabled' => defined('TAINACAN_DISABLE_DEFAULT_SEARCH_ENGINE'),
			'sanitize_callback' => 'rest_sanitize_boolean',
			'default' => defined('TAINACAN_DISABLE_DEFAULT_SEARCH_ENGINE') ? !TAINACAN_DISABLE_DEFAULT_SEARCH_ENGINE : true,
			'forced_value' => defined('TAINACAN_DISABLE_DEFAULT_SEARCH_ENGINE') ? !TAINACAN_DISABLE_DEFAULT_SEARCH_ENGINE : null
		) );

		$this->create_tainacan_setting( array(
			'id' => 'enable_relationship_metaquery',
			'section' => 'tainacan_settings_search_and_performance',
			'title' => __( 'Metadados relacionados', 'tainacan' ),
			'label' => __( 'Search in related metadata of related items', 'tainacan' ),
			'description' => __( 'Check this option to enable Tainacan\'s textual search in metadata other than title of itens related items. If disabled it may improve the search performance.', 'tainacan' ),
			'type' => 'boolean',
			'input_type' => 'checkbox',
			'input_disabled' => defined('TAINACAN_ENABLE_RELATIONSHIP_METAQUERY'),
			'sanitize_callback' => 'rest_sanitize_boolean',
			'default' => defined('TAINACAN_ENABLE_RELATIONSHIP_METAQUERY') ? TAINACAN_ENABLE_RELATIONSHIP_METAQUERY : true,
			'forced_value' => defined('TAINACAN_ENABLE_RELATIONSHIP_METAQUERY') ? TAINACAN_ENABLE_RELATIONSHIP_METAQUERY : null
		) );

		$this->create_tainacan_setting( array(
			'id' => 'facets_enable_filter_items',
			'section' => 'tainacan_settings_search_and_performance',
			'title' => __( 'Filters dynamic values', 'tainacan' ),
			'label' => __( 'Narrows down filters options based on current search', 'tainacan' ),
			'description' => __( 'Check this option to have filter values being reloaded every time a new filter is applied for displaing only options that will result in some item count. If disabled, this can increase the search results speed well.', 'tainacan' ),
			'type' => 'boolean',
			'input_type' => 'checkbox',
			'input_disabled' => defined('TAINACAN_FACETS_ENABLE_FILTER_ITEMS'),
			'sanitize_callback' => 'rest_sanitize_boolean',
			'default' => defined('TAINACAN_FACETS_DISABLE_FILTER_ITEMS') ? !TAINACAN_FACETS_DISABLE_FILTER_ITEMS : true,
			'forced_value' => defined('TAINACAN_FACETS_DISABLE_FILTER_ITEMS') ? !TAINACAN_FACETS_DISABLE_FILTER_ITEMS : null
		) );

		$this->create_tainacan_setting( array(
			'id' => 'facets_enable_count_items',
			'section' => 'tainacan_settings_search_and_performance',
			'title' => __( 'Facets count', 'tainacan' ),
			'label' => __( 'Calculate total items for each filter option', 'tainacan' ),
			'description' => __( 'Check this option to enable the numbers that appear alongside filter values. If disabled, this can increase the search results speed, as facets count are heavy to proccess.', 'tainacan' ),
			'type' => 'boolean',
			'input_type' => 'checkbox',
			'input_disabled' => defined('TAINACAN_FACETS_DISABLE_COUNT_ITEMS'),
			'sanitize_callback' => 'rest_sanitize_boolean',
			'default' => defined('TAINACAN_FACETS_DISABLE_COUNT_ITEMS') ? !TAINACAN_FACETS_DISABLE_COUNT_ITEMS : true,
			'forced_value' => defined('TAINACAN_FACETS_DISABLE_COUNT_ITEMS') ? !TAINACAN_FACETS_DISABLE_COUNT_ITEMS : null
		) );

		/**
		 * Theme default templates -----------------------------------------------------
		 */
		add_settings_section(
			'tainacan_settings_theme_templates', // ID
			__( 'Theme default templates', 'tainacan' ), // Title
			array( $this, 'theme_templates_section_description' ), // Callback
			'tainacan_settings'               		    // Page
		);
		
		$this->create_tainacan_setting( array(
			'id' => 'override_item_single_template',
			'section' => 'tainacan_settings_theme_templates',
			'title' => __( 'Item page', 'tainacan' ),
			'label' => __( 'Replace WordPress post-like template with basic item information', 'tainacan' ),
			'description' => __( 'Enable this option to override the WordPress default post-like template and insert some basic item information at it, such as the item document within the media gallery, the custom metadata and the attachments.', 'tainacan' ),
			'type' => 'boolean',
			'input_type' => 'checkbox',
			'input_disabled' => defined( 'TAINACAN_DISABLE_ITEM_THE_CONTENT_FILTER' ),
			'sanitize_callback' => 'rest_sanitize_boolean',
			'default' => defined( 'TAINACAN_DISABLE_ITEM_THE_CONTENT_FILTER' ) ? !TAINACAN_DISABLE_ITEM_THE_CONTENT_FILTER : true,
			'forced_value' => defined( 'TAINACAN_DISABLE_ITEM_THE_CONTENT_FILTER' ) ? !TAINACAN_DISABLE_ITEM_THE_CONTENT_FILTER : null
		) );

		$this->create_tainacan_setting( array(
			'id' => 'override_collection_items_archive_template',
			'section' => 'tainacan_settings_theme_templates',
			'title' => __( 'Collection items page', 'tainacan' ),
			'label' => __( 'Replace WordPress blog-like template with a faceted search', 'tainacan' ),
			'description' => __( 'Enable this option to override the WordPress default blog-list-like template and display the faceted search in the collection items page, incluiding filters and custom view modes.', 'tainacan' ),
			'type' => 'boolean',
			'input_type' => 'checkbox',
			'sanitize_callback' => 'rest_sanitize_boolean',
			'default' => false
		) );

		$this->create_tainacan_setting( array(
			'id' => 'override_repository_items_archive_template',
			'section' => 'tainacan_settings_theme_templates',
			'title' => __( 'Repository items page', 'tainacan' ),
			'label' => __( 'Replace WordPress blog-like template with a faceted search', 'tainacan' ),
			'description' => __( 'Enable this option to override the WordPress default blog-list-like template and display the faceted search in the repository items page, incluiding filters and custom view modes.', 'tainacan' ),
			'type' => 'boolean',
			'input_type' => 'checkbox',
			'sanitize_callback' => 'rest_sanitize_boolean',
			'default' => false
		) );

		$this->create_tainacan_setting( array(
			'id' => 'override_taxonomy_archive_template',
			'section' => 'tainacan_settings_theme_templates',
			'title' => __( 'Taxonomy terms page', 'tainacan' ),
			'label' => __( 'Replace WordPress blog-like template with a basic terms list', 'tainacan' ),
			'description' => __( 'Enable this option to override the WordPress default blog-list-like template and display taxonomy terms list with links to child terms and its items, besides having basic sorting an search options.', 'tainacan' ),
			'type' => 'boolean',
			'input_type' => 'checkbox',
			'input_disabled' => defined( 'TAINACAN_DISABLE_TAXONOMY_THE_CONTENT_FILTER' ),
			'sanitize_callback' => 'rest_sanitize_boolean',
			'default' => defined( 'TAINACAN_DISABLE_TAXONOMY_THE_CONTENT_FILTER' ) ? !TAINACAN_DISABLE_TAXONOMY_THE_CONTENT_FILTER : true,
			'forced_value' => defined( 'TAINACAN_DISABLE_TAXONOMY_THE_CONTENT_FILTER' ) ? !TAINACAN_DISABLE_TAXONOMY_THE_CONTENT_FILTER : null
		) );

		/**
		 * Items list default options -----------------------------------------------------
		 */
		add_settings_section(
			'tainacan_settings_items_list_defaults', // ID
			__( 'Default theme items list options', 'tainacan' ), // Title
			array( $this, 'items_list_defaults_section_description' ), // Callback
			'tainacan_settings'               		    // Page
		);

		$view_modes = tainacan_get_the_view_modes();
		$enabled_view_modes = isset($view_modes['enabled_view_modes']) ? $view_modes['enabled_view_modes'] : [];

		if ( count($enabled_view_modes) > 1 ) {

			$enabled_view_modes_options = '';

			foreach( $enabled_view_modes as $view_mode_key ) {
				if ( isset($view_modes['registered_view_modes'][$view_mode_key]) &&  !$view_modes['registered_view_modes'][$view_mode_key]['full_screen'] )
					$enabled_view_modes_options .= '<option value="' . esc_attr( $view_mode_key ) . '">' . esc_html( $view_modes['registered_view_modes'][$view_mode_key]['label'] ) . '</option>';
			}

			$this->create_tainacan_setting( array(
				'id' => 'default_view_mode',
				'section' => 'tainacan_settings_items_list_defaults',
				'title' => __( 'View mode', 'tainacan' ),
				'type' => 'string',
				'input_type' => 'select',
				'input_inner_html' => $enabled_view_modes_options,
				'sanitize_callback' => 'sanitize_text_field',
				'default' => isset($view_modes['default_view_mode']) ? $view_modes['default_view_mode'] : 'masonry'
			) );

			$enabled_view_modes_labels = [];
			foreach( $enabled_view_modes as $view_mode_key ) {
				if ( isset($view_modes['registered_view_modes'][$view_mode_key]) )
					$enabled_view_modes_labels[$view_mode_key] = $view_modes['registered_view_modes'][$view_mode_key]['label'];
			}

			$this->create_tainacan_setting( array(
				'id' => 'enabled_view_modes',
				'section' => 'tainacan_settings_items_list_defaults',
				'title' => __( 'Enabled view modes', 'tainacan' ),
				'label' => $enabled_view_modes_labels,
				'type' => 'array',
				'input_type' => 'checkbox',
				'sanitize_callback' => function( $input ) {
					return is_array( $input ) ? array_map( 'sanitize_text_field', $input ) : [];
				},
				'default' => false
			) );
		}

		/**
		 * Google reCAPTCHA -----------------------------------------------------
		 */
		add_settings_section(
			'tainacan_item_submission_recaptcha_id', 					// ID
			__( 'Item Submission Forms with reCAPTCHA', 'tainacan'), 	// Title
			array( $this, 'print_section_info' ),    					// Callback
			'tainacan_settings'               		 					// Page
		);

		add_settings_field(
			'tnc_option_recaptch_site_key',                  // ID
			__( 'Google reCAPTCHA Site Key', 'tainacan' ),   // Title
			array( $this, 'tnc_option_recaptch_site_key' ),  // Callback
			'tainacan_settings',                     		 // Page
			'tainacan_item_submission_recaptcha_id',         // Section
			array( 'label_for' => 'tnc_option_recaptch_site_key' ) 
		);
		register_setting(
			'tainacan_settings',
			'tnc_option_recaptch_site_key',
			'sanitize_text_field'
		);

		add_settings_field(
			'tnc_option_recaptch_secret_key',                  // ID
			__( 'Google reCAPTCHA Secret Key', 'tainacan' ),   // Title
			array( $this, 'tnc_option_recaptch_secret_key' ),  // Callback
			'tainacan_settings',                       		   // Page
			'tainacan_item_submission_recaptcha_id',           // Section,
			array( 'label_for' => 'tnc_option_recaptch_secret_key' ) 
		);
		register_setting(
			'tainacan_settings',
			'tnc_option_recaptch_secret_key',
			'sanitize_text_field'
		);

	}

	/**
	 * Creates a Tainacan setting.
	 *
	 * This function registers a new setting field and its associated options in the Tainacan settings page.
	 * It acts as a wrapper for WordPress functions add_settings_field and register_setting.
	 *
	 * @param array $args {
	 *     An array of arguments for creating the setting.
	 *
	 *     @type string   $id                The ID or 'slug' of the setting. Will be concatenated with 'tainacan_option_'.
	 *     @type string   $title             The title of the setting, that will appear on the left side of the form.
	 *     @type callable $callback          The callback function for rendering the setting field. Default is 'default_field_callback'.
	 *     @type string   $page              The settings page where the field will be displayed. Default is 'tainacan_settings'.
	 *     @type string   $section           The section of the settings page where the field will be displayed.
	 *     @type string   $class             The CSS class for the setting field. Will be added to the <tr> tag tha contains the field.
	 *     @type string   $type              The type of the setting. Valid values are 'string', 'boolean', 'integer', 'number', 'array', and 'object'. Default is 'string'.
	 *     @type string   $input_type        The input type for the setting field. Valid values are 'text', 'checkbox', 'radio', 'select', 'textarea', 'email', 'url', 'number', 'password', 'hidden', 'color', 'date', 'datetime-local', 'month', 'range', 'search', 'tel', 'time', 'week'. Default is 'text'.
	 *     @type string   $input_attrs       Additional attributes for the input field markup. May be used for passsing, for example, min and max values.
	 *     @type bool     $input_disabled    Whether the input field is disabled. Default is false.
	 *     @type string   $input_inner_html  Inner HTML content for the input field. May be used for setting select <option> elements.
	 *     @type string   $description       Description of the setting, used for help.
	 *     @type callable $sanitize_callback The callback function for sanitizing the setting value. Default is 'sanitize_text_field'.
	 *     @type mixed    $default           The default value of the setting. Will be passed to get_option() if the setting is not set.
	 *     @type string   $label             The label for the setting field.
	 *     @type mixed    $forced_value      A value that overrides the setting value.
	 * }
	 */
	public function create_tainacan_setting( $args ) {
		$defaults = array(
			'id' => '',
			'title' => '',
			'callback' => array( $this, 'default_field_callback' ),
			'page' => 'tainacan_settings',
			'section' => '',
			'class' => '',
			'type' => 'string',
			'input_type' => 'text',
			'input_attrs' => '',
			'input_disabled' => false,
			'input_inner_html' => '',
			'description' => '',
			'sanitize_callback' => 'sanitize_text_field',
			'default' => '',
			'label' => '',
			'forced_value' => null
		);

		$args = wp_parse_args( $args, $defaults );

		add_settings_field(
			'tainacan_option_' . $args['id'],
			$args['title'],
			array( $this, 'tainacan_settings_field_callback' ),
			$args['page'],
			$args['section'],
			array(
				'label_for' => isset($args['label']) && ( !empty($args['label']) || is_array($args['label']) ) ? null : 'tainacan_option_' . $args['id'],
				'label' => $args['label'],
				'class' => $args['class'],
				'callback' => $args['callback'],
				'id' => 'tainacan_option_' . $args['id'],
				'input_type' => $args['input_type'],
				'input_attrs' => $args['input_attrs'],
				'input_disabled' => $args['input_disabled'],
				'input_inner_html' => $args['input_inner_html'],
				'description' => $args['description'],
				'default' => $args['default'],
				'forced_value' => $args['forced_value'],
			) 
		);

		register_setting(
			'tainacan_settings',
			'tainacan_option_' . $args['id'],
			array(
				'type' => $args['type'],
				'label' => is_array($args['label']) ? implode(', ', $args['label']) : $args['label'],
				'description' => $args['description'],
				'sanitize_callback' => $args['sanitize_callback'],
				'show_in_rest' => true,
				'default' => $args['default']
			)
		);
	}

	/**
	 * Callback wrapper to allow receiving args in the callback function.
	 */
	public function tainacan_settings_field_callback( $args ) {
		if ( is_callable( $args['callback'] ) ) {
			call_user_func( $args['callback'], $args );
		}
	}

	/**
	 * Generic field callback to be used in the create_tainacan_setting function.
	 * Renders a basic input field with a description.
	 */
	public function default_field_callback( $args ) {

		$option_name = $args['id'];
		$description = isset( $args['description'] ) ? $args['description'] : '';
		$default = isset( $args['default'] ) ? $args['default'] : '';
		$value = ( isset($args['forced_value']) && $args['forced_value'] !== null ) ? $args['forced_value'] : get_option( $option_name, $default );
		$input_type = $args['input_type'] ? $args['input_type'] : 'text';
		$label = $args['label'] ? $args['label'] : '';
		$disabled = isset( $args['input_disabled'] ) && $args['input_disabled'] ? 'disabled' : '';

		if ( $label && !is_array($label) ) : ?>
			<label for="<?php echo esc_attr( $option_name ); ?>">
		<?php endif; ?>

		<?php if ( $args['input_type'] === 'select' ) : ?>
			<select 
				id="<?php echo esc_attr( $option_name ); ?>" 
				name="<?php echo esc_attr( $option_name ); ?>"
				<?php echo ' ' . esc_attr( $args['input_attrs'] ) . ' '; ?>
				<?php echo $disabled; ?>>
				<?php echo str_replace( 'value="' . $value . '"', 'value="' . $value . '" selected'  , $args['input_inner_html'] ); ?>
			</select>
		<?php elseif ( $args['input_type'] === 'checkbox' && is_array($args['label']) ) : ?>
			<div class="multiple-options">
				<?php
					foreach ( $label as $key => $option_label ) {
						$checked = is_array($value) && in_array( $key, $value ) ? 'checked' : '';
						echo '<label>';
						echo '<input type="checkbox" name="' . esc_attr( $option_name ) . '[]" value="' . esc_attr( $key ) . '" ' . $checked . ' />' . esc_html($option_label);
						echo '</label><br>';
					}
				?>
			<div>
		<?php else : ?>
			<input 
				id="<?php echo esc_attr( $option_name ); ?>" 
				name="<?php echo esc_attr( $option_name ); ?>"
				type="<?php echo $input_type; ?>" 
				<?php echo ($input_type === 'checkbox' ? ( $value == true || $value == "1" ? ' checked value="1"' : ' value="1"' ) : ' value="' . $value . '"'); ?> 
				<?php echo ' ' . esc_attr( $args['input_attrs'] ) . ' '; ?>
				<?php echo $disabled; ?> />
		<?php endif; ?>

		<?php if ( $label && !is_array($label) ) : ?>
				<?php echo esc_html( $label ); ?>
			</label>
		<?php endif;

		if ( ! empty( $description ) ) : ?>
			<p class="description"><?php echo esc_html( $description ); ?></p>
		<?php endif;
	}	


	public function search_and_performance_section_description() {
	?>
		<p class="settings-section-descrition">
			<?php echo _e('Options that may impact on your servers response. Some may be disabled by your server settings. Use with caution!', 'tainacan');?>
		</p>
	<?php
	}

	public function theme_templates_section_description() {
	?>
		<p class="settings-section-descrition">
			<?php echo _e('Options related to theme compatibility. If your theme does not implements its own versions of Tainacan templates you can enable some options that will override WordPress default templates. Extra customization might required at least some knowledge of CSS.', 'tainacan');?>
		</p>
	<?php
	}

	public function items_list_defaults_section_description() {
	?>
		<p class="settings-section-descrition">
			<?php echo _e('Options that will be used as default for items list in the collection and repository pages. They might be override by collection settings or theme options.', 'tainacan');?>
		</p>
	<?php
	}

	public function print_section_info() {
	?>
		<p class="settings-section-descrition">
			<?php echo _e('When using the Item\'s Submission block, you can enable Google reCAPTCHA for increasing security. For that you must configure your site and key settings here.', 'tainacan');?>
		</p>
	<?php
	}

	public function tnc_option_recaptch_site_key() {
		printf(
			'<input type="text" id="tnc_option_recaptch_site_key" name="tnc_option_recaptch_site_key" value="%s" />',
			esc_attr( get_option('tnc_option_recaptch_site_key') )
		);
	}

	public function tnc_option_recaptch_secret_key() {
		printf(
			'<input type="text" id="tnc_option_recaptch_secret_key" name="tnc_option_recaptch_secret_key" value="%s" />',
			esc_attr( get_option('tnc_option_recaptch_secret_key') )
		);
	}

}