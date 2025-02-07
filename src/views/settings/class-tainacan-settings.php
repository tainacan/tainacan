<?php

namespace Tainacan;

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
			'description' => sprintf( __( 'Number of items to show in search results. The default is %s and larger numbers should be avoided as it impacts in your server load time.', 'tainacan' ), ( defined('TAINACAN_API_MAX_ITEMS_PER_PAGE') ? TAINACAN_API_MAX_ITEMS_PER_PAGE : 96 ) ),
			'sanitize_callback' => 'absint',
			'default' => defined('TAINACAN_API_MAX_ITEMS_PER_PAGE') ? TAINACAN_API_MAX_ITEMS_PER_PAGE : 96
		) );

		$this->create_tainacan_setting( array(
			'id' => 'index_pdf_content',
			'section' => 'tainacan_settings_search_and_performance',
			'title' => __( 'PDF content', 'tainacan' ),
			'label' => __( 'Index textual content from PDF files in search results', 'tainacan' ),
			'description' => __( 'Enable this option to index the content of PDF files. This will increase the search results accuracy but also the server load.', 'tainacan' ),
			'type' => 'boolean',
			'input_type' => 'checkbox',
			'sanitize_callback' => 'rest_sanitize_boolean',
			'default' => defined('TAINACAN_INDEX_PDF_CONTENT') ? TAINACAN_INDEX_PDF_CONTENT : false
		) );

		$this->create_tainacan_setting( array(
			'id' => 'enable_default_search_engine',
			'section' => 'tainacan_settings_search_and_performance',
			'title' => __( 'Fields for textual search', 'tainacan' ),
			'label' => __( 'Enable the search on every metadata.', 'tainacan' ),
			'description' => __( 'Check this option to enable Tainacan\'s textual search in every metadata of the collection. If disabled, only title and description will be considered, which may improve the search perfomance.', 'tainacan' ),	
			'type' => 'boolean',
			'input_type' => 'checkbox',
			'sanitize_callback' => 'rest_sanitize_boolean',
			'default' => defined('TAINACAN_DISABLE_DEFAULT_SEARCH_ENGINE') ? TAINACAN_DISABLE_DEFAULT_SEARCH_ENGINE : true
		) );

		$this->create_tainacan_setting( array(
			'id' => 'enable_relationship_metaquery',
			'section' => 'tainacan_settings_search_and_performance',
			'title' => __( 'Metadados relacionados', 'tainacan' ),
			'label' => __( 'Search in related metadata of related items', 'tainacan' ),
			'description' => __( 'Check this option to enable Tainacan\'s textual search in metadata other than title of itens related items. If disabled it may improve the search performance.', 'tainacan' ),
			'type' => 'boolean',
			'input_type' => 'checkbox',
			'sanitize_callback' => 'rest_sanitize_boolean',
			'default' => defined('TAINACAN_ENABLE_RELATIONSHIP_METAQUERY') ? TAINACAN_ENABLE_RELATIONSHIP_METAQUERY : true
		) );

		$this->create_tainacan_setting( array(
			'id' => 'facets_enable_filter_items',
			'section' => 'tainacan_settings_search_and_performance',
			'title' => __( 'Filters dynamic values', 'tainacan' ),
			'label' => __( 'Narrows down filters options based on current search', 'tainacan' ),
			'description' => __( 'Check this option to have filter values being reloaded every time a new filter is applied for displaing only options that will result in some item count. If disabled, this can increase the search results speed well.', 'tainacan' ),
			'type' => 'boolean',
			'input_type' => 'checkbox',
			'sanitize_callback' => 'rest_sanitize_boolean',
			'default' => defined('TAINACAN_FACETS_DISABLE_FILTER_ITEMS') ? TAINACAN_FACETS_DISABLE_FILTER_ITEMS : true
		) );

		$this->create_tainacan_setting( array(
			'id' => 'facets_disable_count_items',
			'section' => 'tainacan_settings_search_and_performance',
			'title' => __( 'Facets count', 'tainacan' ),
			'label' => __( 'Calculate total items for each filter option', 'tainacan' ),
			'description' => __( 'Check this option to enable the numbers that appear alongside filter values. If disabled, this can increase the search results speed, as facets count are heavy to proccess.', 'tainacan' ),
			'type' => 'boolean',
			'input_type' => 'checkbox',
			'sanitize_callback' => 'rest_sanitize_boolean',
			'default' => defined('TAINACAN_FACETS_DISABLE_COUNT_ITEMS') ? TAINACAN_FACETS_DISABLE_COUNT_ITEMS : true
		) );

		/**
		 * Search and Performance -----------------------------------------------------
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
			'sanitize_callback' => 'rest_sanitize_boolean',
			'default' => true
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
			'sanitize_callback' => 'rest_sanitize_boolean',
			'default' => false
		) );

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
			'tainacan_item_submission_recaptcha',
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
			'tainacan_item_submission_recaptcha',
			'tnc_option_recaptch_secret_key',
			'sanitize_text_field'
		);

	}

	/**
	 * Generic function to help registernig and adding settings to the page.
	 * It acts as a wrapper for WordPress functions add_settings_field and register_setting.
	 */
	public function create_tainacan_setting( $args ) {
		$defaults = array(
			'id' => '',
			'title' => '',
			'callback' => array( $this, 'default_field_callback' ),
			'page' => 'tainacan_settings',
			'section' => '',
			'class' => '',
			'type' => 'string', // Valid values are 'string', 'boolean', 'integer', 'number', 'array', and 'object'
			'input_type' => 'text', // Valid values are 'text', 'checkbox', 'radio', 'select', 'textarea', 'email', 'url', 'number', 'password', 'hidden', 'color', 'date', 'datetime-local', 'month', 'range', 'search', 'tel', 'time', 'week'
			'input_attrs' => '',
			'description' => '',
			'sanitize_callback' => 'sanitize_text_field',
			'default' => '',
			'label' => '',
		);

		$args = wp_parse_args( $args, $defaults );

		add_settings_field(
			$args['id'],
			$args['title'],
			array( $this, 'tainacan_settings_field_callback' ),
			$args['page'],
			$args['section'],
			array(
				'label_for' => isset($args['label']) && !empty($args['label']) ? null : 'tainacan_option_' . $args['id'],
				'label' => $args['label'],
				'class' => $args['class'],
				'callback' => $args['callback'],
				'id' => $args['id'],
				'input_type' => $args['input_type'],
				'input_attrs' => $args['input_attrs'],
				'description' => $args['description'],
				'default' => $args['default']
			) 
		);

		register_setting(
			$args['section'],
			'tainacan_option_' . $args['id'],
			array(
				'type' => $args['type'],
				'label' => $args['label'],
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
		$value = get_option( $option_name, $default );
		$input_type = $args['input_type'] ? $args['input_type'] : 'text';
		$label = $args['label'] ? $args['label'] : '';

		if ( $label ) : ?>
			<label for="tainacan_option_<?php echo esc_attr( $option_name ); ?>">
		<?php endif; ?>

			<input 
				id="tainacan_option_<?php echo esc_attr( $option_name ); ?>" 
				name="tainacan_option_<?php echo esc_attr( $option_name ); ?>" 
				type="<?php echo $input_type; ?>" 
				<?php echo ($input_type === 'checkbox' ? checked( $value ) : ' value="' . $value . '"'); ?> 
				<?php echo ' ' . esc_attr( $args['input_attrs'] ) . ' '; ?> />
		<?php

		if ( $label ) : ?>
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
			<?php echo _e('Options that may impact on your servers response. Use with caution!', 'tainacan');?>
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