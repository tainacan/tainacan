<?php

namespace Tainacan\Tests;

/**
 * Class XssTaxonomyTermNames
 *
 * @package Test_Tainacan
 */

/**
 * Regression tests for the stored XSS through taxonomy term names.
 *
 * A term name or facet label that contains HTML in its escaped form must not be
 * decoded back into a live tag when it is read, since those values are later
 * rendered as HTML. Only the ampersand entity may be decoded, so names such as
 * "R&D" still display with a literal "&".
 */
class XssTaxonomyTermNames extends TAINACAN_UnitTestCase {

	/**
	 * Reading a term name must not decode "<"/">" into a live tag.
	 */
	function test_term_name_html_entities_are_not_decoded_to_tags() {
		$Tainacan_Terms = \Tainacan\Repositories\Terms::get_instance();

		$taxonomy = $this->tainacan_entity_factory->create_entity(
			'taxonomy',
			array(
				'name'         => 'xss tax',
				'allow_insert' => 'yes',
				'status'       => 'publish',
			),
			true
		);

		$term = $this->tainacan_entity_factory->create_entity(
			'term',
			array(
				'taxonomy' => $taxonomy->get_db_identifier(),
				'name'     => '&lt;img src=x onerror=alert(1)&gt;',
			),
			true
		);

		$fetched = $Tainacan_Terms->fetch( $term->get_term_id(), $taxonomy );
		$name    = $fetched->get_name();

		// The security property: no live tag is ever produced on read.
		$this->assertStringNotContainsString( '<', $name );
		$this->assertStringNotContainsString( '>', $name );
		$this->assertStringNotContainsString( '<img', $name );
		// The text is preserved as stored, escaped.
		$this->assertStringContainsString( 'img src=x onerror=alert(1)', $name );
	}

	/**
	 * The ampersand entity must still be decoded so "R&D" shows a literal "&".
	 */
	function test_term_name_ampersand_entity_is_still_decoded() {
		$Tainacan_Terms = \Tainacan\Repositories\Terms::get_instance();

		$taxonomy = $this->tainacan_entity_factory->create_entity(
			'taxonomy',
			array(
				'name'         => 'amp tax',
				'allow_insert' => 'yes',
				'status'       => 'publish',
			),
			true
		);

		$term = $this->tainacan_entity_factory->create_entity(
			'term',
			array(
				'taxonomy' => $taxonomy->get_db_identifier(),
				'name'     => 'R&amp;D',
			),
			true
		);

		$fetched = $Tainacan_Terms->fetch( $term->get_term_id(), $taxonomy );

		$this->assertEquals( 'R&D', $fetched->get_name() );
	}

	/**
	 * A term description stored as escaped markup must not be decoded into a tag
	 * on read either (the same read path covers name and description).
	 */
	function test_term_description_html_entities_are_not_decoded_to_tags() {
		$Tainacan_Terms = \Tainacan\Repositories\Terms::get_instance();

		$taxonomy = $this->tainacan_entity_factory->create_entity(
			'taxonomy',
			array(
				'name'         => 'desc tax',
				'allow_insert' => 'yes',
				'status'       => 'publish',
			),
			true
		);

		$term = $this->tainacan_entity_factory->create_entity(
			'term',
			array(
				'taxonomy'    => $taxonomy->get_db_identifier(),
				'name'        => 'desc term',
				'description' => '&lt;img src=x onerror=alert(4)&gt;',
			),
			true
		);

		$fetched     = $Tainacan_Terms->fetch( $term->get_term_id(), $taxonomy );
		$description = $fetched->get_description();

		$this->assertStringNotContainsString( '<img', $description );
		$this->assertStringContainsString( 'img src=x onerror=alert(4)', $description );
	}

	/**
	 * Reading a taxonomy facet label must not decode "<"/">" into a live tag.
	 */
	function test_facet_label_html_entities_are_not_decoded_to_tags() {
		$Tainacan_Item_Metadata = \Tainacan\Repositories\Item_Metadata::get_instance();
		$Tainacan_Metadata      = \Tainacan\Repositories\Metadata::get_instance();

		$collection = $this->tainacan_entity_factory->create_entity(
			'collection',
			array(
				'name'   => 'xss col',
				'status' => 'publish',
			),
			true
		);

		$taxonomy = $this->tainacan_entity_factory->create_entity(
			'taxonomy',
			array(
				'name'         => 'xss facet tax',
				'allow_insert' => 'yes',
				'status'       => 'publish',
			),
			true
		);

		$metadatum = $this->tainacan_entity_factory->create_entity(
			'metadatum',
			array(
				'name'                  => 'xss tax metadatum',
				'collection'            => $collection,
				'metadata_type'         => 'Tainacan\Metadata_Types\Taxonomy',
				'metadata_type_options' => array(
					'allow_new_terms' => 'yes',
					'taxonomy_id'     => $taxonomy->get_id(),
				),
				'status'                => 'publish',
			),
			true
		);

		$item = $this->tainacan_entity_factory->create_entity(
			'item',
			array(
				'title'      => 'xss item',
				'collection' => $collection,
				'status'     => 'publish',
			),
			true
		);

		// Creating the value with allow_new_terms creates the malicious term.
		$item_metadata = new \Tainacan\Entities\Item_Metadata_Entity( $item, $metadatum );
		$item_metadata->set_value( '&lt;img src=x onerror=alert(2)&gt;' );
		$item_metadata->validate();
		$Tainacan_Item_Metadata->insert( $item_metadata );

		$response = $Tainacan_Metadata->fetch_all_metadatum_values( $metadatum->get_id() );
		$values   = isset( $response['values'] ) ? $response['values'] : array();

		$this->assertNotEmpty( $values );

		$payload_label = null;
		foreach ( $values as $value ) {
			$this->assertStringNotContainsString( '<', $value['label'] );
			$this->assertStringNotContainsString( '>', $value['label'] );
			$this->assertStringNotContainsString( '<img', $value['label'] );
			if ( false !== strpos( $value['label'], 'img src=x onerror=alert(2)' ) ) {
				$payload_label = $value['label'];
			}
		}

		// The label for the malicious term must exist and keep its text escaped,
		// so an empty or missing label can never make this test pass.
		$this->assertNotNull( $payload_label );
	}

	/**
	 * Numeric character references for "<"/">" must not be decoded into a tag
	 * either (the old wp_specialchars_decode() also decoded "&#060;"/"&#062;").
	 */
	function test_term_name_numeric_entities_are_not_decoded_to_tags() {
		$Tainacan_Terms = \Tainacan\Repositories\Terms::get_instance();

		$taxonomy = $this->tainacan_entity_factory->create_entity(
			'taxonomy',
			array(
				'name'         => 'num tax',
				'allow_insert' => 'yes',
				'status'       => 'publish',
			),
			true
		);

		$term = $this->tainacan_entity_factory->create_entity(
			'term',
			array(
				'taxonomy' => $taxonomy->get_db_identifier(),
				'name'     => '&#060;img src=x onerror=alert(3)&#062;',
			),
			true
		);

		$fetched = $Tainacan_Terms->fetch( $term->get_term_id(), $taxonomy );
		$name    = $fetched->get_name();

		$this->assertStringNotContainsString( '<', $name );
		$this->assertStringNotContainsString( '>', $name );
		$this->assertStringNotContainsString( '<img', $name );
		// The inner text is preserved, only the angle brackets stay encoded.
		$this->assertStringContainsString( 'img src=x onerror=alert(3)', $name );
	}
}
