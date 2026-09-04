<?php

namespace Tainacan\Tests;

/**
 * Tests the scoped video embed output.
 */
class VideoEmbed extends TAINACAN_UnitTestCase {

	public function test_video_preload_setting_only_affects_tainacan_embeds() {
		global $wp_embed;

		$embed = \Tainacan\Embed::get_instance();
		$default_video = '<video class="wp-default"></video>';
		$previous_wp_embed = $wp_embed;
		$wp_embed = new class {
			public function autoembed( $url ) {
				return \Tainacan\Embed::get_instance()->filter_video_embed(
					'<video class="wp-default"></video>',
					array( 'width' => 640, 'height' => 360 ),
					$url,
					array()
				);
			}
		};

		try {
			delete_option( 'tainacan_option_enable_video_preload_none' );
			$this->assertSame(
				$default_video,
				$embed->filter_video_embed( $default_video, array(), 'https://example.com/video.mp4', array() )
			);

			$this->assertStringNotContainsString( 'preload=', $embed->embed( 'https://example.com/video.mp4' ) );

			update_option( 'tainacan_option_enable_video_preload_none', '1' );
			$this->assertStringContainsString( 'preload="none"', $embed->embed( 'https://example.com/video.mp4' ) );
		} finally {
			delete_option( 'tainacan_option_enable_video_preload_none' );
			$wp_embed = $previous_wp_embed;
		}
	}
}
