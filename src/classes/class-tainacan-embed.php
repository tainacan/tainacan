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
		add_filter('wp_embed_handler_video', [&$this, 'filter_video_embed'], 10, 4);
		add_filter('wp_embed_handler_audio', [&$this, 'filter_audio_embed'], 10, 4);
		
		/**
		 * ADD PDF Embed handler using PDF.js
		 * @var [type]
		 */
		wp_embed_register_handler( 'pdf', '#^https?://.+?\.(pdf)$#i', [&$this, 'pdf_embed_handler'] );
		
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
			'width' => 800,
			'height' => 1000
		);
		
		$args = array_merge($defaults, $attr);
		
		$dimensions = '';
		if ( ! empty( $args['width'] ) && ! empty( $args['height'] ) ) {
			$dimensions .= sprintf( "width='%d' ", (int) $args['width'] );
			$dimensions .= sprintf( "height='%d' ", (int) $args['height'] );
		}
		
		$pdf = "<iframe id='iframePDF' name='iframePDF' src='$viewer_url' $dimensions allowfullscreen webkitallowfullscreen></iframe>";
		return $pdf;
	}
	
}