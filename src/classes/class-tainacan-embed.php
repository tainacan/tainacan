<?php
namespace Tainacan;

class Embed {
	
	private static $instance = null;

    public static function get_instance() {
        if(!isset(self::$instance)) {
            self::$instance = new self();
        }

        return self::$instance;
    }

	private static $aspect_ratios = array(
		// Common video resolutions.
		array("ratio" => '2.33', "className" => 'tainacan-embed-aspect-21-9'),
		array("ratio" => '2.00', "className" => 'tainacan-embed-aspect-18-9'),
		array("ratio" => '1.78', "className" => 'tainacan-embed-aspect-16-9'),
		array("ratio" => '1.33', "className" => 'tainacan-embed-aspect-4-3'), 
		// Vertical video and instagram square video support.
		array("ratio" => '1.00', "className" => 'tainacan-embed-aspect-1-1' ),
		array("ratio" => '0.75', "className" => 'tainacan-embed-aspect-3-4'),
		array("ratio" => '0.56', "className" => 'tainacan-embed-aspect-9-16'),
		array("ratio" => '0.50', "className" => 'tainacan-embed-aspect-1-2' )
	);
	
	protected function __construct() {
		
		/**
		 * Replace default WordPress embedders with HTML 5 tags instead of shortcodes
		 */
		add_filter('wp_embed_handler_video', [$this, 'filter_video_embed'], 10, 4);
		add_filter('wp_embed_handler_audio', [$this, 'filter_audio_embed'], 10, 4);
		
		/**
		 * Add responsiveness to embeds
		 */
		add_action( 'admin_enqueue_scripts', array( &$this, 'add_css' ) );
		add_action( 'wp_enqueue_scripts', array( &$this, 'add_css' ) );

		/**
		 * ADD PDF Embed handler using PDF.js
		 * @var [type]
		 */
		wp_embed_register_handler( 'pdf', '#^https?://.+?\.(pdf)$#i', [$this, 'pdf_embed_handler'] );

	}
	
	public function filter_video_embed($video, $attr, $url, $rawattr) {
		
		
		$dimensions = '';
		if ( ! empty( $attr['width'] ) && ! empty( $attr['height'] ) ) {
			$dimensions .= sprintf( 'width="%d" ', (int) $attr['width'] );
			//$dimensions .= sprintf( 'height="%d" ', (int) $attr['height'] );
		}
		$video = sprintf( '<video controls="" %s src="%s"></video>', $dimensions, esc_url( $url ) );
		
		return $video;
		
	}
	
	public function filter_audio_embed($audio, $attr, $url, $rawattr) {
		
		
		if ( ! empty( $attr['width'] ) ) {
			$dimensions = sprintf( 'width="%d" ', (int) $attr['width'] );
		}
		
		$audio = sprintf('<audio controls="" src="%s" %s></audio>', esc_url( $url ), $dimensions);
		
		return $audio;
		
	}
	
	public function pdf_embed_handler($matches, $attr, $url, $rawattr) {
		global $TAINACAN_BASE_URL;
		
		$defaults = array(
			'width' => '100%',
			'height' => '640px'
		);
		
		$args = array_merge($attr, $defaults);
		
		$dimensions = '';
		if ( ! empty( $args['width'] ) && ! empty( $args['height'] ) ) {
			$dimensions .= sprintf( "width='%s' ", $args['width'] );
			$dimensions .= sprintf( "height='%s' ", $args['height'] );
		}

		$pdf = sprintf('<iframe id="iframePDF" name="iframePDF" src="%s" %s allowfullscreen webkitallowfullscreen></iframe>', esc_url( $url ), $dimensions );
		return $pdf;
	}
	
	/**
	 * Retrieves the thumbnail URL, if provided, for a given URL
	 * 
	 * @param  $string $url the URL for the content
	 * @return string|null  The thumbnail URL or null on failure
	 */
	public function oembed_get_thumbnail($url) {
		
		add_filter( 'oembed_dataparse', [$this, 'oembed_get_thumbnail_filter'], 10, 3);
		$return = wp_oembed_get($url);
		remove_filter( 'oembed_dataparse', [$this, 'oembed_get_thumbnail_filter']);
		return $return;
		
	}
	public function oembed_get_thumbnail_filter($return, $data, $url) {
		
		if ( isset($data->thumbnail_url) ) {
			return $data->thumbnail_url;
		}
		
		return null;
		
	}

	/**
	 * Responsiveness
	 */
	public function add_css() {
		global $TAINACAN_BASE_URL;
		wp_enqueue_style( 'tainacan-embeds', $TAINACAN_BASE_URL . '/assets/css/tainacan-embeds.css', [], TAINACAN_VERSION );
	}

	/**
	 * Get responsive class based on aspect ratio
	 * This code is heavily inspired by Gutenberg plugin's "getClassNames" function.
	 * Check their source code for more details: /packages/block-library/src/embed/util.js
	 * 
	 * @param {string}  html               The preview HTML that possibly contains an iframe with width and height set.
 	 * @return {string} Deduped class names.
	 */
	public function add_responsive_wrapper( $html ) {
	
		$height = false;
		$width = false;
		
		$dom = new \DOMDocument();
		libxml_use_internal_errors(true);
		$dom->loadHTML($html);
		libxml_clear_errors();

		// If we have a fixed aspect iframe, and it's a responsive embed content.
		if ($dom) {
			$externalContentElement = $dom->getElementsByTagName('iframe');
			
			if (!$externalContentElement)
				$externalContentElement = $dom->getElementsByTagName('embed');
				
			if (!$externalContentElement)
				$externalContentElement = $dom->getElementsByTagName('object');
			
			if ($externalContentElement) {
				foreach($externalContentElement as $element) {
					foreach($element->attributes as $attribute) {
						if ($attribute->nodeName == 'width')
							$width = $attribute->nodeValue;
						if ($attribute->nodeName == 'height')
							$height = $attribute->nodeValue;
						
						if ($attribute->nodeName == 'class' && $attribute->nodeValue == 'wp-embedded-content') {
							$height = false;
							$width = false;
							break;
						} 
					}
				}
			}

			if ( $height && $width ) {

				// Removes 'px' from the end if it was passed
				$height = preg_split('/px$/', $height)[0];
				$width = preg_split('/px$/', $width)[0];

				// If even then we are still not using a numeric value, it is probably the case of a 100%
				$height = is_numeric($height) ? $height : 567;
				$width = is_numeric($width) ? $width : 1024;

				$aspect_ratio = number_format(( $width / $height ), 2, '.', "");
	
				// Given the actual aspect ratio, find the widest ratio to support it.
				for ($ratioIndex = 0; $ratioIndex < count(self::$aspect_ratios); $ratioIndex++) {

					$potentialRatio = self::$aspect_ratios[ $ratioIndex ];
					if ( $aspect_ratio >= $potentialRatio['ratio'] ) {
						$class = $potentialRatio['className'] . ' tainacan-content-embed tainacan-has-aspect-ratio';
						return '<figure class="' . $class . '"><div class="tainacan-content-embed__wrapper">' . $html . '</div></figure>';
					}
				}
			}
		}
	
		return $html;
	}
	 
}