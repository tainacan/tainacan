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
	
	protected function __construct() {
		
		/**
		 * Replace default WordPress embedders with HTML 5 tags instead of shortcodes
		 */
		add_filter('wp_embed_handler_video', [$this, 'filter_video_embed'], 10, 4);
		add_filter('wp_embed_handler_audio', [$this, 'filter_audio_embed'], 10, 4);
		
		/**
		 * Add responsiveness to embeds
		 */
		add_filter('embed_oembed_html', [$this, 'responsive_embed'], 10, 3);
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
		
		$audio = sprintf('<audio controls="" src="%s" %s></audio>', $url, $dimensions);
		
		return $audio;
		
	}
	
	public function pdf_embed_handler($matches, $attr, $url, $rawattr) {
		global $TAINACAN_BASE_URL;
		$viewer_url = $TAINACAN_BASE_URL . '/pdf-viewer/pdf-viewer.html?file=' . $url;
		//$viewer_url = $TAINACAN_BASE_URL . '/assets/pdfjs-dist/web/viewer.html?file=' . $url;
		
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

		$pdf = "<iframe id='iframePDF' name='iframePDF' src='$viewer_url' $dimensions allowfullscreen webkitallowfullscreen></iframe>";
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
	 * Adds a responsive embed wrapper around oEmbed content
	 * @param  string $html The oEmbed markup
	 * @param  string $url  The URL being embedded
	 * @param  array  $attr An array of attributes
	 * @return string       Updated embed markup
	 */
	function responsive_embed($html, $url, $attr) {
		return $html !== '' ? '<div class="tainacan-embed-container">'.$html.'</div>' : '';
	}
	 
}