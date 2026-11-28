<?php

namespace Tainacan\Tests;

/**
 * Item attachments get distinct menu_order values, so every list shows them in the same order.
 *
 * @see https://github.com/tainacan/tainacan/issues/922
 *
 * @group attachments
 */
class Item_Attachments_Order extends TAINACAN_UnitTestCase {

	/**
	 * Creates a published item.
	 *
	 * @return \Tainacan\Entities\Item
	 */
	private function create_item() {
		$collection = $this->tainacan_entity_factory->create_entity(
			'collection',
			array(
				'name'   => 'Collection with attachments',
				'status' => 'publish',
			),
			true
		);

		return $this->tainacan_entity_factory->create_entity(
			'item',
			array(
				'title'      => 'Item with attachments',
				'collection' => $collection,
				'status'     => 'publish',
			),
			true
		);
	}

	/**
	 * Adds an attachment post to a parent.
	 *
	 * @param int    $parent_id  The parent post.
	 * @param string $title      The attachment title.
	 * @param int    $menu_order A menu_order given by whoever adds it.
	 *
	 * @return int The attachment ID.
	 */
	private function add_attachment( $parent_id, $title, $menu_order = 0 ) {
		return wp_insert_attachment(
			array(
				'post_title'     => $title,
				'post_mime_type' => 'image/jpeg',
				'menu_order'     => $menu_order,
			),
			$title . '.jpg',
			$parent_id
		);
	}

	private function menu_order( $post_id ) {
		return (int) get_post( $post_id )->menu_order;
	}

	private function attachment_ids( $item ) {
		return array_map( 'intval', wp_list_pluck( $item->get_attachments(), 'ID' ) );
	}

	public function test_new_attachments_keep_the_order_they_were_added_in() {
		$item = $this->create_item();

		// Titles out of alphabetical order: the upload order must win, not the title.
		$first  = $this->add_attachment( $item->get_id(), 'Zebra' );
		$second = $this->add_attachment( $item->get_id(), 'Apple' );
		$third  = $this->add_attachment( $item->get_id(), 'Mango' );

		$this->assertSame( array( 1, 2, 3 ), array( $this->menu_order( $first ), $this->menu_order( $second ), $this->menu_order( $third ) ) );
		$this->assertSame( array( $first, $second, $third ), $this->attachment_ids( $item ) );
	}

	public function test_attachments_sharing_a_menu_order_are_renumbered_in_the_order_already_shown() {
		global $wpdb;
		$item = $this->create_item();

		$apple = $this->add_attachment( $item->get_id(), 'Apple' );
		$mango = $this->add_attachment( $item->get_id(), 'Mango' );
		$zebra = $this->add_attachment( $item->get_id(), 'Zebra' );

		// As left by older versions: every attachment with menu_order 0.
		$wpdb->query( $wpdb->prepare( "UPDATE $wpdb->posts SET menu_order = 0 WHERE post_parent = %d", $item->get_id() ) );
		foreach ( array( $apple, $mango, $zebra ) as $id ) {
			clean_post_cache( $id );
		}
		$shown_before = $this->attachment_ids( $item );

		$new = $this->add_attachment( $item->get_id(), 'Banana' );

		$this->assertSame( array_merge( $shown_before, array( $new ) ), $this->attachment_ids( $item ) );
		$this->assertSame( array( 1, 2, 3, 4 ), array_map( array( $this, 'menu_order' ), $this->attachment_ids( $item ) ) );
	}

	public function test_a_chosen_menu_order_is_kept() {
		$item = $this->create_item();

		$this->add_attachment( $item->get_id(), 'First' );
		$chosen = $this->add_attachment( $item->get_id(), 'Chosen', 10 );

		$this->assertSame( 10, $this->menu_order( $chosen ) );
	}

	public function test_attachments_of_other_posts_are_not_changed() {
		$page = self::factory()->post->create( array( 'post_type' => 'page' ) );

		$attachment = $this->add_attachment( $page, 'Page image' );

		$this->assertSame( 0, $this->menu_order( $attachment ) );
	}

	public function test_paginated_attachments_endpoint_has_no_repeated_attachment() {
		$item = $this->create_item();
		$ids  = array();
		for ( $i = 0; $i < 25; $i++ ) {
			// The same title for all, as in the report: only menu_order and ID tell them apart.
			$ids[] = $this->add_attachment( $item->get_id(), 'Scan' );
		}

		$listed = array();
		for ( $page = 1; $page <= 3; $page++ ) {
			$request = new \WP_REST_Request( 'GET', '/tainacan/v2/items/' . $item->get_id() . '/attachments' );
			// The same params the item edition page sends.
			$request->set_query_params( array( 'paged' => $page, 'perpage' => 10, 'order' => 'ASC', 'orderby' => 'menu_order' ) );
			$response = rest_get_server()->dispatch( $request );
			$data     = $response->get_data();
			$listed   = array_merge( $listed, array_map( 'intval', array_column( isset( $data['items'] ) ? $data['items'] : $data, 'id' ) ) );
		}

		$this->assertSame( $ids, $listed );
	}
}
