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
	 * Lazyload can be disabled while retaining the scoped video output.
	 */
	public function test_video_lazyload_can_be_disabled() {
		update_option( 'tainacan_option_enable_video_lazyload', '0' );

		try {
			$this->assertSame( '0', get_option( 'tainacan_option_enable_video_lazyload', true ) );
			$output = $this->run_tainacan_embed('https://example.com/video.mp4');

			$this->assertStringContainsString('<video', $output);
			$this->assertStringContainsString('class="tainacan-video-embed"', $output);
			$this->assertStringContainsString('preload="none"', $output);
			$this->assertStringNotContainsString('tainacan-video-lazyload', $output);
		} finally {
			delete_option( 'tainacan_option_enable_video_lazyload' );
		}
	}

	/**
	 * Without a companion image, non-document videos fall back to the item
	 * thumbnail instead of the generic placeholder.
	 */
	public function test_attached_video_without_companion_uses_item_thumbnail() {
		$item = new class extends \Tainacan\Entities\Item {
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
		$attachment_id = wp_insert_attachment(
			array(
				'post_title'     => 'Attached video',
				'post_status'    => 'inherit',
				'post_mime_type' => 'video/mp4',
				'guid'           => 'https://example.com/attached-video.mp4',
			)
		);

		try {
			$attachment_output = $item->get_attachment_as_html($attachment_id);
			$this->assertStringContainsString('class="tainacan-video-lazyload"', $attachment_output);
			$this->assertStringContainsString('item-thumbnail.jpg', $attachment_output);
			$this->assertStringNotContainsString('placeholder_video', $attachment_output);

			$item->set_document_type('attachment');
			$item->set_document($attachment_id);
			$document_output = $item->get_document_as_html();
			$this->assertStringContainsString('item-thumbnail.jpg', $document_output);
		} finally {
			wp_delete_attachment($attachment_id, true);
		}
	}

	/**
	 * A non-document video attachment must use a same-named image attachment
	 * as its lazyload miniature.
	 */
	public function test_attached_video_uses_companion_image_as_miniature() {
		$item = new class extends \Tainacan\Entities\Item {
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
		$video_id = wp_insert_attachment(
			array(
				'post_title'     => 'Interview video',
				'post_status'    => 'inherit',
				'post_mime_type' => 'video/mp4',
				'guid'           => 'https://example.com/interview.mp4',
			),
			'/tmp/interview.mp4'
		);
		$image_id = wp_insert_attachment(
			array(
				'post_title'     => 'Interview miniature',
				'post_status'    => 'inherit',
				'post_mime_type' => 'image/jpeg',
				'guid'           => 'https://example.com/interview.jpg',
			),
			'/tmp/interview.jpg',
			$item->get_id()
		);

		try {
			$output = $item->get_attachment_as_html($video_id);

			$this->assertStringContainsString('class="tainacan-video-lazyload"', $output);
			$this->assertStringContainsString('interview.jpg', $output);
			$this->assertStringNotContainsString('placeholder_video', $output);
			$this->assertStringNotContainsString('item-thumbnail.jpg', $output);
		} finally {
			wp_delete_attachment($video_id, true);
			wp_delete_attachment($image_id, true);
		}
	}

	/**
	 * Images paired with non-document videos must not be listed as gallery
	 * attachments, while pairs of the document video stay visible.
	 */
	public function test_companion_images_are_hidden_from_attachments_list() {
		$item = new class extends \Tainacan\Entities\Item {
			private $document;

			public function set_document( $document ) {
				$this->document = $document;
			}

			public function get_id() {
				return 424242;
			}

			public function get_document_type() {
				return $this->document ? 'attachment' : 'url';
			}

			public function get_document() {
				return $this->document;
			}
		};
		$make_attachment = function ( $title, $mime, $guid, $file ) use ( $item ) {
			return wp_insert_attachment(
				array(
					'post_title'     => $title,
					'post_status'    => 'inherit',
					'post_mime_type' => $mime,
					'guid'           => $guid,
					'post_parent'    => $item->get_id(),
				),
				$file
			);
		};
		$video_id = $make_attachment('Second video', 'video/mp4', 'https://example.com/second.mp4', '/tmp/second.mp4');
		$companion_id = $make_attachment('Second miniature', 'image/jpeg', 'https://example.com/second.jpg', '/tmp/second.jpg');
		$unrelated_id = $make_attachment('Poster', 'image/png', 'https://example.com/poster.png', '/tmp/poster.png');
		$attachments = array( get_post($companion_id), get_post($unrelated_id) );

		$embed = \Tainacan\Embed::get_instance();

		try {
			// Without a document video, the paired image is hidden.
			$filtered = $embed->filter_video_companion_images($attachments, $item);
			$this->assertSame(array($unrelated_id), wp_list_pluck($filtered, 'ID'));

			// The document video keeps using the item thumbnail: its pair stays.
			$item->set_document($video_id);
			$filtered = $embed->filter_video_companion_images($attachments, $item);
			$this->assertSame(array($companion_id, $unrelated_id), wp_list_pluck($filtered, 'ID'));
		} finally {
			wp_delete_attachment($video_id, true);
			wp_delete_attachment($companion_id, true);
			wp_delete_attachment($unrelated_id, true);
		}
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
