<?php

namespace Tainacan\Tests;

/**
 * Class TestUtilities
 *
 * @package Test_Tainacan
 */

/**
 * Sample test case.
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

		$text_no_links = "Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed pharetra sapien quis nunc vulputate dictum. Pellentesque id euismod mauris.";
		$text_no_links_expected = "Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed pharetra sapien quis nunc vulputate dictum. Pellentesque id euismod mauris.";
		$text_no_links_response = $text->make_clickable_links($text_no_links);
		$this->assertEquals($text_no_links_expected, $text_no_links_response);

		$text_simple_link = "Lorem https://www.tainacan.org/ ipsum dolor sit amet, consectetur adipiscing elit. Sed pharetra sapien quis nunc vulputate dictum. Pellentesque id euismod mauris.";
		$text_simple_link_expected = 'Lorem <a href="https://www.tainacan.org/" target="_blank" title="https://www.tainacan.org/">https://www.tainacan.org/</a> ipsum dolor sit amet, consectetur adipiscing elit. Sed pharetra sapien quis nunc vulputate dictum. Pellentesque id euismod mauris.';
		$text_simple_link_response = $text->make_clickable_links($text_simple_link);
		$this->assertEquals($text_simple_link_expected, $text_simple_link_response);

		$text_multiple_links = 'Lorem https://www.tainacan.org ipsum dolor sit amet http://www.tainacan.org' .
													' ftp://www.teste.com.br consectetur adipiscing elit. ftps://www.teste.com.br Sed pharetra sapien quis nunc vulputate dictum.' .
													' www.simple.com.br ' .
													' www.simple.com ' .
													' www.simple.org ' .
													' Pellentesque id //ww.lair.com.br of a http://wwwliar.com.br euismod mauris. //pegadinha.com.br ';
		
		$text_multiple_links_expected =  'Lorem <a href="https://www.tainacan.org" target="_blank" title="https://www.tainacan.org">https://www.tainacan.org</a> ipsum dolor sit amet <a href="http://www.tainacan.org" target="_blank" title="http://www.tainacan.org">http://www.tainacan.org</a>' .
													' <a href="ftp://www.teste.com.br" target="_blank" title="ftp://www.teste.com.br">ftp://www.teste.com.br</a> consectetur adipiscing elit. <a href="ftps://www.teste.com.br" target="_blank" title="ftps://www.teste.com.br">ftps://www.teste.com.br</a> Sed pharetra sapien quis nunc vulputate dictum.' .
													' <a href="http://www.simple.com.br" target="_blank" title="www.simple.com.br">www.simple.com.br</a> ' . 
													' <a href="http://www.simple.com" target="_blank" title="www.simple.com">www.simple.com</a> ' .
													' <a href="http://www.simple.org" target="_blank" title="www.simple.org">www.simple.org</a> ' .
													' Pellentesque id //ww.lair.com.br of a <a href="http://wwwliar.com.br" target="_blank" title="http://wwwliar.com.br">http://wwwliar.com.br</a> euismod mauris. //pegadinha.com.br ';

		$text_multiple_links_response = $text->make_clickable_links($text_multiple_links);
		$this->assertEquals($text_multiple_links_expected, $text_multiple_links_response);

		$text_multiple_links = 'Lorem <a href="https://www.tainacan.org" target="_blank" title="https://www.tainacan.org">https://www.tainacan.org</a> Lorem https://tainacan.org hahahahahahhttps://tainacan.org hahaha ';
		$text_multiple_links_expected = 'Lorem <a href="https://www.tainacan.org" target="_blank" title="https://www.tainacan.org">https://www.tainacan.org</a> Lorem <a href="https://tainacan.org" target="_blank" title="https://tainacan.org">https://tainacan.org</a> hahahahahah<a href="https://tainacan.org" target="_blank" title="https://tainacan.org">https://tainacan.org</a> hahaha ';
		$text_multiple_links_response = $text->make_clickable_links($text_multiple_links);
		$this->assertEquals($text_multiple_links_expected, $text_multiple_links_response);

		$text_input = 'If you type only www.abc.com without the http the link.';
		$text_input_expected = 'If you type only <a href="http://www.abc.com" target="_blank" title="www.abc.com">www.abc.com</a> without the http the link.';
		$text_input_response = $text->make_clickable_links($text_input);
		$this->assertEquals($text_input_expected, $text_input_response);
	}
	
}