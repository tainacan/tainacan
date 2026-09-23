<?php

namespace Tainacan\Tests;

/**
 * @group api
 */
class TAINACAN_REST_Reports_Controller extends TAINACAN_UnitApiTestCase {

	public function test_subscriber_cannot_read_reports() {
		$subscriber = $this->factory()->user->create( array( 'role' => 'subscriber' ) );
		wp_set_current_user( $subscriber );

		$routes = array(
			'/reports/activities',
			'/reports/repository/summary',
			'/reports/collection/1/activities',
			'/reports/collection/1/summary',
		);

		foreach ( $routes as $route ) {
			$request  = new \WP_REST_Request( 'GET', $this->namespace . $route );
			$response = $this->server->dispatch( $request );

			$this->assertEquals( 403, $response->get_status(), $route );
		}
	}

	public function test_log_reader_cannot_read_repository_reports() {
		$reader = $this->factory()->user->create( array( 'role' => 'subscriber' ) );
		$user   = get_userdata( $reader );
		$user->add_cap( 'tnc_rep_read_logs' );
		clean_user_cache( $reader );
		wp_set_current_user( $reader );

		$request  = new \WP_REST_Request( 'GET', $this->namespace . '/reports/activities' );
		$response = $this->server->dispatch( $request );

		$this->assertEquals( 403, $response->get_status() );
	}

	public function test_collection_manager_can_read_only_that_collection() {
		$manager = $this->factory()->user->create( array( 'role' => 'subscriber' ) );
		$user    = get_userdata( $manager );
		$user->add_cap( 'manage_tainacan_collection_12' );
		clean_user_cache( $manager );
		wp_set_current_user( $manager );

		$allowed  = new \WP_REST_Request( 'GET', $this->namespace . '/reports/collection/12/activities' );
		$other    = new \WP_REST_Request( 'GET', $this->namespace . '/reports/collection/13/activities' );
		$repo     = new \WP_REST_Request( 'GET', $this->namespace . '/reports/repository/summary' );

		$this->assertEquals( 200, $this->server->dispatch( $allowed )->get_status() );
		$this->assertEquals( 403, $this->server->dispatch( $other )->get_status() );
		$this->assertEquals( 403, $this->server->dispatch( $repo )->get_status() );
	}

	public function test_private_item_reader_cannot_read_collection_reports() {
		$reader = $this->factory()->user->create( array( 'role' => 'subscriber' ) );
		$user   = get_userdata( $reader );
		$user->add_cap( 'tnc_col_12_read_private_items' );
		clean_user_cache( $reader );
		wp_set_current_user( $reader );

		$request  = new \WP_REST_Request( 'GET', $this->namespace . '/reports/collection/12/summary' );
		$response = $this->server->dispatch( $request );

		$this->assertEquals( 403, $response->get_status() );
	}

	public function test_legacy_report_cache_is_not_served() {
		set_transient(
			'reports_tnc_activities_',
			array(
				'totals' => array(
					'by_user' => array(
						array(
							'user' => array(
								'name'  => 'Legacy Cache Marker',
								'email' => 'legacy@example.com',
							),
						),
					),
				),
			),
			HOUR_IN_SECONDS
		);

		$request  = new \WP_REST_Request( 'GET', $this->namespace . '/reports/activities' );
		$response = $this->server->dispatch( $request );
		$data     = $response->get_data();

		$this->assertEquals( 200, $response->get_status() );
		$this->assertStringNotContainsString( 'Legacy Cache Marker', wp_json_encode( $data ) );
		$this->assertStringNotContainsString( 'legacy@example.com', wp_json_encode( $data ) );
	}
}
