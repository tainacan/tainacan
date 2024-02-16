<?php

namespace Tainacan\Metadata_Types;

class URL extends Metadata_Type {
	use \Tainacan\Traits\Formatter_Text;

	function __construct() {

		parent::__construct();

        // Basic options
        $this->set_name( __('URL', 'tainacan') );
        $this->set_description( __('An URL link, possibly with embedded content.', 'tainacan') );
        $this->set_primitive_type('string');
        $this->set_component('tainacan-url');
        $this->set_form_component('tainacan-form-url');
        $this->set_default_options([
			'link-as-button' => 'no',
			'force-iframe' => 'no',
			'iframe-min-height' => '',
			'iframe-allowfullscreen' => 'no',
			'is-image' => 'no'
		]);
		$this->set_preview_template('
			<div>
				<div class="control is-clearfix">
					<input type="url" placeholder="https://youtube.com/?v=abc123456" class="input">
				</div>
			</div>
		');
	}
	
	/**
     * @inheritdoc
     */
	public function get_form_labels() {
		return [
			'link-as-button' => [
				'title' 	  => __( 'Display link as a button', 'tainacan' ),
				'description' => __( 'Style the link to be displayed as a button instead of a simple textual link.', 'tainacan' ),
			],
			'force-iframe' => [
				'title' 	  => __( 'Force iframe', 'tainacan' ),
				'description' => __( 'Force the URL to be displayed in an iframe in case the content is not embeddable by WordPress.', 'tainacan' ),
			],
			'iframe-min-height' => [
				'title' 	  => __( 'Forced iframe minimum height', 'tainacan' ),
				'description' => __( 'If forcing the use of an iframe, sets the height attribute, in pixels. Leave it empty to be 100% of the container.', 'tainacan' ),
			],
			'iframe-allowfullscreen' => [
				'title' 	  => __( 'Allow fullscreen on forced iframe', 'tainacan' ),
				'description' => __( 'If forcing the use of an iframe, allows it to request fullscreen to the browser.', 'tainacan' ),
			],
			'is-image' => [
				'title' 	  => __( 'Is link to external image', 'tainacan' ),
				'description' => __( 'If you are linking directly to an external image, use this option so it can be properly embedded.', 'tainacan' ),
			]
		];
	}

	/**
	 * Get the value as a HTML string with links
	 * @return string
	 */
	public function get_value_as_html(\Tainacan\Entities\Item_Metadata_Entity $item_metadata) {
		
		$value = $item_metadata->get_value();
		$link_as_button = $this->get_option('link-as-button') == 'yes';
		$return = '';

		$return .= $link_as_button ? '<div class="wp-block-buttons">' : '';
		
		if ( is_array($value) && $item_metadata->is_multiple() ) {
			$total = sizeof($value);
			$count = 0;
			$prefix = $item_metadata->get_multivalue_prefix();
			$suffix = $item_metadata->get_multivalue_suffix();
			$separator = $item_metadata->get_multivalue_separator();

			foreach ( $value as $el ) {
				if ( !empty($el) ) {
					$return .= $prefix;
					
					$return .= $this->get_single_value_as_html($el);

					$return .= $suffix;
					
					$count ++;

					if ($count < $total && !$link_as_button)
						$return .= $separator;
				}
			}
			
		} else {			
			$return .= $this->get_single_value_as_html($value);	
		}
		$return .= $link_as_button ? '</div>' : '';

		return $return;
	}

	/**
	 * Get the a single value as a HTML string with links
	 * @return string
	 */
	public function get_single_value_as_html($value) {
		global $wp_embed;

		$link_as_button = $this->get_option('link-as-button') == 'yes';
		$return = '';

		if ($link_as_button) {
			$mkstr = preg_replace(
				'/\[([^\]]+)\]\(([^\)]+)\)/',
				'<div class="wp-block-button"><a class="wp-block-button__link wp-element-button" href="\2" target="_blank" title="\1">\1</a></div>',
				$value
			);
			$return = $this->make_clickable_links($mkstr);
		} else {

			// First, we try WordPress autoembed
			$embed = $wp_embed->autoembed($value);
			
			// If it didn't work, it will still ba a URL
 			if ( esc_url($embed) == esc_url($value) ) {

				// Than we can force the usage of an iframe
				if ( $this->get_option('force-iframe') == 'yes' ) {

					// URL points to an image file
					if ( $this->get_option('is-image') == 'yes' ) {
						$return = sprintf('<a href="%s" target="blank"><img src="%s" /></a>', $value, $value);

					// URL points to a content that is not an image
					} else {
						$iframeMininumHeight = '100%';

						if (!empty($this->get_option('iframe-min-height')))
							$iframeMininumHeight = $this->get_option('iframe-min-height');

						// Creates an embed with responsive wrapper
						$tainacan_embed = \Tainacan\Embed::get_instance();
						$return = $tainacan_embed->add_responsive_wrapper( '<iframe src="' . $value . '" width="100%" height="' . $iframeMininumHeight  . '" style="border:none;" allowfullscreen="' . ($this->get_option('iframe-allowfullscreen') == 'yes' ? 'true' : 'false') . '"></iframe>' );
					}

				// Or we can leave it as a link
				} else {
					$mkstr = preg_replace(
						'/\[([^\]]+)\]\(([^\)]+)\)/',
						'<a href="\2" target="_blank" title="\1">\1</a>',
						$value
					);
					$return = $this->make_clickable_links($mkstr);
				}

			// If the autoembed did work, we pass the responsive wrapper to it
			} else {
				$tainacan_embed = \Tainacan\Embed::get_instance();
				$return = $tainacan_embed->add_responsive_wrapper($embed);
			}
		}

		return $return;
	}

	/**
	 * Checks if the value passed is a valid URL or markdown link
	 * @return boolean
	 */
	public function validate(\Tainacan\Entities\Item_Metadata_Entity $item_metadata) {
		$value = $item_metadata->get_value();
		//$reg_mrkd = '~\[(.+)\]\(([^ ]+)?\)~i';
		$reg_url = '~^((www\.|http:\/\/www\.|http:\/\/|https:\/\/www\.|https:\/\/|ftp:\/\/www\.|ftp:\/\/|ftps:\/\/www\.|ftps:\/\/)[^"<\s]+)(?![^<>]*>|[^"]*?<\/a)$~i';
		$reg_full = '~\[(.+)\]\((((www\.|http:\/\/www\.|http:\/\/|https:\/\/www\.|https:\/\/|ftp:\/\/www\.|ftp:\/\/|ftps:\/\/www\.|ftps:\/\/)[^"<\s]+)(?![^<>]*>|[^"]*?<\/a))?\)~i';

		// Multivalued metadata --------------
		if ( is_array($value) ) {
			foreach ($value as $url_value) {

				// Empty strings are valid
				if ( !empty($url_value) ) {

					$url_value = trim($url_value);

					// If this seems to be a markdown link, we check if the url inside it is ok as well
					if ( !preg_match($reg_url, $url_value) && !preg_match($reg_full, $url_value) ) {
						$this->add_error( sprintf( __('"%s" is invalid. Please provide a valid, full URL or a Markdown link in the form of [label](url).', 'tainacan'), $url_value ) );
						return false;
					}
				}
			}
			return true;
		}

		// Single valued metadata --------------
		// Empty strings are valid
		if ( !empty($value) ) {

			$value = trim($value);

			// If this seems to be a markdown link, we check if the url inside it is ok as well
			if ( !preg_match($reg_url, $value) && !preg_match($reg_full, $value) ) {
				$this->add_error( sprintf( __('"%s" is invalid. Please provide a valid, full URL or a Markdown link in the form of [label](url).', 'tainacan'), $value ) );
				return false;
			}
		}
		return true;
	}

}
?>
