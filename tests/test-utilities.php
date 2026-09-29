<?php

namespace Tainacan\Tests;

/**
 * Class TestUtilities
 *
 * @package Test_Tainacan
 */

/**
 * TestUtilities
 */
class TestUtilities extends TAINACAN_UnitTestCase {

    function test_initials() {
        $string = 'Roberto Carlos';

        $this->assertEquals('RC', tainacan_get_initials($string));

        $string = 'Ronaldo';

        $this->assertEquals('RO', tainacan_get_initials($string));
        $this->assertEquals('R', tainacan_get_initials($string, true));

        $string = 'Marilia Mendonça das Neves Costa Silva Fonseca';

        $this->assertEquals('MF', tainacan_get_initials($string));
        $this->assertEquals('M', tainacan_get_initials($string, true));
        
        $string = 'Machado de Assis';

        $this->assertEquals('MA', tainacan_get_initials($string));

        $string = 'b';

        $this->assertEquals('B', tainacan_get_initials($string));

        $string = '';

        $this->assertEquals('', tainacan_get_initials($string));
	}
	
	function test_get_descendants_ids() {
		
		$collection = $this->tainacan_entity_factory->create_entity(
			'collection',
			array(
				'name'   => 'test',
				'status'	 => 'publish',
			),
			true
		);
		
		$collection2 = $this->tainacan_entity_factory->create_entity(
			'collection',
			array(
				'name'   => 'test',
				'status'	 => 'publish',
			),
			true
		);
		
		$collection2_c = $this->tainacan_entity_factory->create_entity(
			'collection',
			array(
				'name'   => 'test',
				'parent' => $collection2->get_id(),
				'status'	 => 'publish',
			),
			true
		);
		
		$collection2_gc = $this->tainacan_entity_factory->create_entity(
			'collection',
			array(
				'name'   => 'test',
				'parent' => $collection2_c->get_id(),
				'status'	 => 'publish',
			),
			true
		);
		$collection2_gc2 = $this->tainacan_entity_factory->create_entity(
			'collection',
			array(
				'name'   => 'test',
				'parent' => $collection2_c->get_id(),
				'status'	 => 'publish',
			),
			true
		);
		
		$collection2_ggc = $this->tainacan_entity_factory->create_entity(
			'collection',
			array(
				'name'   => 'test',
				'parent' => $collection2_gc->get_id(),
				'status'	 => 'publish',
			),
			true
		);
		
		$Tainacan_Collections = \Tainacan\Repositories\Collections::get_instance();
		
		$test = $Tainacan_Collections->get_descendants_ids($collection2);
		$this->assertEquals(4, sizeof($test));
		
		$test = $Tainacan_Collections->get_descendants_ids($collection2_c);
		$this->assertEquals(3, sizeof($test));
		
		$test = $Tainacan_Collections->get_descendants_ids($collection2_gc);
		$this->assertEquals(1, sizeof($test));
		
		$test = $Tainacan_Collections->get_descendants_ids($collection2_ggc);
		$this->assertEquals(0, sizeof($test));
		
		$test = $Tainacan_Collections->get_descendants_ids($collection);
		$this->assertEquals(0, sizeof($test));
		
		$test = $Tainacan_Collections->get_descendants_ids($collection2, 2);
		$this->assertEquals(3, sizeof($test));
		
		$test = $Tainacan_Collections->get_descendants_ids($collection2, 1);
		$this->assertEquals(1, sizeof($test));
		
		$test = $Tainacan_Collections->get_descendants_ids($collection2_c, 1);
		$this->assertEquals(2, sizeof($test));
		
	}

	function test_replace_links_to_clickable_tag() {
		$text = new \Tainacan\Metadata_Types\Text;

		$this->assertSame(
			'Plain text without a URL.',
			$text->make_clickable_links('Plain text without a URL.')
		);
		$this->assertSame(
			'External <a href="http://google.com" rel="nofollow">http://google.com</a> and <a href="https://www.tainacan.org/" rel="nofollow">https://www.tainacan.org/</a>',
			$text->make_clickable_links('External http://google.com and https://www.tainacan.org/')
		);
		$this->assertSame(
			'Bare <a href="http://www.abc.com" rel="nofollow">http://www.abc.com</a> and email <a href="mailto:hello@example.net">hello@example.net</a>',
			$text->make_clickable_links('Bare www.abc.com and email hello@example.net')
		);
		$this->assertSame(
			'<a href="https://example.net/path">Existing link</a> then <a href="http://google.com" rel="nofollow">http://google.com</a>',
			$text->make_clickable_links('<a href="https://example.net/path">Existing link</a> then http://google.com')
		);
		$this->assertSame(
			'See (<a href="http://google.com/path" rel="nofollow">http://google.com/path</a>).',
			$text->make_clickable_links('See (http://google.com/path).')
		);
	}

	function test_make_clickable_links_internal_url() {
		$text = new \Tainacan\Metadata_Types\Text;
		$url = home_url('/inside/');
		// WordPress 5.9 adds nofollow to internal URLs; 6.2+ does not.
		$rel = version_compare( get_bloginfo('version'), '6.2', '<' ) ? ' rel="nofollow"' : '';

		$this->assertSame(
			'Internal <a href="' . $url . '"' . $rel . '>' . $url . '</a>',
			$text->make_clickable_links('Internal ' . $url)
		);
	}

	function test_maybe_unserialize_array() {
		$this->assertSame( array( 1, 2 ), tainacan_maybe_unserialize_array( array( 1, 2 ) ) );
		$this->assertSame( array(), tainacan_maybe_unserialize_array( null ) );
		$this->assertSame( array(), tainacan_maybe_unserialize_array( '' ) );
		$this->assertSame( array(), tainacan_maybe_unserialize_array( 0 ) );
		$this->assertSame( array(), tainacan_maybe_unserialize_array( 'not-serialized' ) );

		$order = array( array( 'id' => 12, 'enabled' => true ) );
		$this->assertEquals( $order, tainacan_maybe_unserialize_array( serialize( $order ) ) );

		Tainacan_POI_Canary::reset();
		$this->assertSame( array(), tainacan_maybe_unserialize_array( serialize( new Tainacan_POI_Canary() ) ) );
		$this->assertFalse( Tainacan_POI_Canary::$woke );
	}
	
}
