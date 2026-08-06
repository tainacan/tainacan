<?php

namespace Tainacan\Tests;

/**
 * Tests Tainacan's scoped video embed output.
 */
class VideoEmbed extends TAINACAN_UnitTestCase {

	/**
	 * WordPress output must pass through when Tainacan is not embedding it.
	 */
	public function test_video_embed_is_unchanged_outside_tainacan_context() {
		$default_video = '<video class="wp-default" src="https://example.com/video.mp4"></video>';
		$embed = \Tainacan\Embed::get_instance();

		$this->assertSame(
			$default_video,
			$embed->filter_video_embed(
				$default_video,
				array(),
				'https://example.com/video.mp4',
				array()
			)
		);
	}

	/**
	 * Tainacan embeds must return a placeholder rather than a video element.
	 */
	public function test_tainacan_video_embed_returns_lazy_placeholder() {
		$item = new class {
			public function get_thumbnail() {
				return array(
					'tainacan-medium' => array(
						'https://example.com/item-thumbnail.jpg',
						275,
						275,
						false,
						''
					),
				);
			}
		};

		$output = $this->run_tainacan_embed('https://example.com/video.mp4', $item);

		$this->assertStringNotContainsString('<video', $output);
		$this->assertStringContainsString('class="tainacan-video-lazyload"', $output);
		$this->assertStringContainsString('data-video-src="https://example.com/video.mp4"', $output);
		$this->assertStringContainsString('https://example.com/item-thumbnail.jpg', $output);

		$default_video = '<video class="wp-default"></video>';
		$this->assertSame(
			$default_video,
			\Tainacan\Embed::get_instance()->filter_video_embed(
				$default_video,
				array(),
				'https://example.com/video.mp4',
				array()
			)
		);
	}

	/**
	 * A missing item thumbnail must use Tainacan's existing video placeholder.
	 */
	public function test_tainacan_video_embed_uses_video_placeholder_without_thumbnail() {
		$item = new class {
			public function get_thumbnail() {
				return array(
					'tainacan-medium' => false,
					'medium_large' => false,
				);
			}
		};

		$output = $this->run_tainacan_embed('https://example.com/video.mp4', $item);

		$this->assertStringNotContainsString('<video', $output);
		$this->assertStringContainsString('placeholder_video', $output);
		$this->assertStringContainsString('tainacan-video-lazyload__play', $output);
	}

	/**
	 * Runs the Embed wrapper with a small stand-in for WP_Embed.
	 *
	 * @param string $url  Video URL.
	 * @param object|null $item Owning item context.
	 * @return string
	 */
	private function run_tainacan_embed($url, $item = null) {
		global $wp_embed;

		$previous_wp_embed = $wp_embed;
		$wp_embed = new class {
			public function autoembed($url) {
				return \Tainacan\Embed::get_instance()->filter_video_embed(
					'<video class="wp-default"></video>',
					array('width' => 640),
					$url,
					array()
				);
			}
		};

		try {
			return \Tainacan\Embed::get_instance()->embed($url, $item);
		} finally {
			$wp_embed = $previous_wp_embed;
		}
	}
}
