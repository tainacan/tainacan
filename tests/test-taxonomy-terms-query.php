<?php

namespace Tainacan\Tests;

/**
 * Integers handed to the taxonomy terms list and its pagination.
 */
class Test_Taxonomy_Terms_Query extends TAINACAN_UnitTestCase {

	/**
	 * @var array<string, mixed>
	 */
	private $original_query_vars = array();

	public function setUp(): void {
		parent::setUp();
		global $wp_query;
		foreach ( array( 'termspaged', 'perpage', 'termsparent' ) as $key ) {
			$this->original_query_vars[ $key ] = $wp_query->get( $key, null );
		}
	}

	public function tearDown(): void {
		global $wp_query;
		foreach ( $this->original_query_vars as $key => $value ) {
			if ( null === $value ) {
				unset( $wp_query->query_vars[ $key ] );
			} else {
				$wp_query->set( $key, $value );
			}
		}
		remove_filter( 'tainacan-default-taxonomy-terms-perpage', array( $this, 'return_zero' ) );
		parent::tearDown();
	}

	/**
	 * ?termspaged=abc used to throw while building the terms offset.
	 */
	public function test_non_numeric_page_is_the_first_page() {
		$this->set_terms_query_vars( array(
			'termspaged'  => 'abc',
			'termsparent' => 'nope',
			'perpage'     => 'abc',
		) );

		$args   = \Tainacan\Theme_Helper::get_instance()->get_taxonomies_query_args();
		$offset = ( $args['termspaged'] - 1 ) * $args['perpage'];

		$this->assertSame( 1, $args['termspaged'] );
		$this->assertSame( 12, $args['perpage'] );
		$this->assertSame( 0, $args['termsparent'] );
		$this->assertSame( 0, $offset );

		$html = tainacan_get_taxonomies_pagination( 30 );
		$this->assertIsString( $html );
		$this->assertNotSame( '', $html );
	}

	/**
	 * A real page, page size and parent pass through as integers.
	 */
	public function test_numeric_terms_query_is_preserved() {
		$this->set_terms_query_vars( array(
			'termspaged'  => '2',
			'perpage'     => '5',
			'termsparent' => '4',
		) );

		$args = \Tainacan\Theme_Helper::get_instance()->get_taxonomies_query_args();

		$this->assertSame( 2, $args['termspaged'] );
		$this->assertSame( 5, $args['perpage'] );
		$this->assertSame( 4, $args['termsparent'] );
		$this->assertSame( 5, ( $args['termspaged'] - 1 ) * $args['perpage'] );
	}

	/**
	 * A default page size of 0 would divide the pagination total by zero.
	 */
	public function test_non_positive_default_page_size_falls_back_to_twelve() {
		add_filter( 'tainacan-default-taxonomy-terms-perpage', array( $this, 'return_zero' ) );
		$this->set_terms_query_vars( array( 'perpage' => '0' ) );

		$args = \Tainacan\Theme_Helper::get_instance()->get_taxonomies_query_args();

		$this->assertSame( 12, $args['perpage'] );
		$this->assertSame( 3, (int) ceil( 30 / $args['perpage'] ) );
	}

	/**
	 * @return int
	 */
	public function return_zero() {
		return 0;
	}

	/**
	 * @param array<string, mixed> $vars Query vars for this request.
	 */
	private function set_terms_query_vars( array $vars ) {
		global $wp_query;
		foreach ( $vars as $key => $value ) {
			$wp_query->set( $key, $value );
		}
	}
}
