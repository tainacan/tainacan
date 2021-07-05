<?php

namespace Tainacan\Tests;

/**
 * Class TestCollections
 *
 * @package Test_Tainacan
 */

use Tainacan\Entities;

/**
 * Sample test case.
 */
class RelationshipMetadatumTypes extends TAINACAN_UnitTestCase {

	private $collection_author = null;
	private $collection_book = null;
	private $collection_article = null;

	public function setUp() {
		parent::setUp();
		$this->collection_book =    $this->tainacan_entity_factory->create_entity('collection', ['name' => 'Book',   'status' => 'publish'], true);
		$this->collection_author =  $this->tainacan_entity_factory->create_entity('collection', ['name' => 'Author', 'status' => 'publish'], true);
		$this->collection_article = $this->tainacan_entity_factory->create_entity('collection', ['name' => 'Article','status' => 'publish'], true);

		$this->metadatum_author_book = $this->tainacan_entity_factory->create_entity(
			'metadatum',
			array(
				'name' => 'author',
				'description' => 'author',
				'collection' => $this->collection_book,
				'metadata_type'  => 'Tainacan\Metadata_Types\Relationship',
				'status'	 => 'publish',
				'metadata_type_options' => [
					'display_in_related_items' => 'yes',
					'collection_id' => $this->collection_author->get_id(),
					'search' => ''
				],
				'multiple' => 'yes'
			),
			true
		);

		$this->metadatum_author_article = $this->tainacan_entity_factory->create_entity(
			'metadatum',
			array(
				'name' => 'author',
				'description' => 'author',
				'collection' => $this->collection_article,
				'metadata_type'  => 'Tainacan\Metadata_Types\Relationship',
				'status'	 => 'publish',
				'metadata_type_options' => [
					'display_in_related_items' => 'yes',
					'collection_id' => $this->collection_author->get_id(),
					'search' => ''
				],
			),
			true
		);

		$this->metadatum_second_author_article = $this->tainacan_entity_factory->create_entity(
			'metadatum',
			array(
				'name' => 'secound author',
				'description' => 'secound author',
				'collection' => $this->collection_article,
				'metadata_type'  => 'Tainacan\Metadata_Types\Relationship',
				'status'	 => 'publish',
				'metadata_type_options' => [
					'display_in_related_items' => 'no',
					'collection_id' => $this->collection_author->get_id(),
					'search' => ''
				]
			),
			true
		);

	}

	function test_related_items() {
		
		$a1 = $this->tainacan_entity_factory->create_entity(
			'item',
			array(
				'title'       => 'Lopes da silva',
				'description' => 'autor fisico',
				'collection'  => $this->collection_author,
				'status'      => 'publish'
			),
			true
		);

		$a2 = $this->tainacan_entity_factory->create_entity(
			'item',
			array(
				'title'       => 'Siqueira da martins',
				'description' => 'autor simuante',
				'collection'  => $this->collection_author,
				'status'      => 'publish'
			),
			true
		);

		$b1 = $this->tainacan_entity_factory->create_entity(
			'item',
			array(
				'title'       => 'O livro dos livros',
				'description' => 'livro broxura',
				'collection'  => $this->collection_book,
				'status'      => 'publish'
			),
			true
		);

		$j1 = $this->tainacan_entity_factory->create_entity(
			'item',
			array(
				'title'       => 'O artigo da semana',
				'description' => 'artigo mais que cientifico',
				'collection'  => $this->collection_article,
				'status'      => 'publish'
			),
			true
		);

		$j2 = $this->tainacan_entity_factory->create_entity(
			'item',
			array(
				'title'       => 'Não é magia é tecnologias',
				'description' => 'So digo que não digo nada',
				'collection'  => $this->collection_article,
				'status'      => 'publish'
			),
			true
		);

		$this->tainacan_item_metadata_factory->create_item_metadata($j1, $this->metadatum_author_article, $a1->get_id());
		$this->tainacan_item_metadata_factory->create_item_metadata($j1, $this->metadatum_second_author_article, $a2->get_id());

		$this->tainacan_item_metadata_factory->create_item_metadata($j2, $this->metadatum_author_article, $a1->get_id());
		$this->tainacan_item_metadata_factory->create_item_metadata($j2, $this->metadatum_second_author_article, $a2->get_id());
		
		$this->tainacan_item_metadata_factory->create_item_metadata($b1, $this->metadatum_author_book, [$a1->get_id(), $a2->get_id()]);

		
		$a1_related_items = $a1->get_related_items();
		$a2_related_items = $a1->get_related_items();
		
		$this->assertTrue(isset($a1_related_items[$this->metadatum_author_article->get_id()]));
		$this->assertEquals(2, count($a1_related_items[$this->metadatum_author_article->get_id()]['items']));
		$this->assertTrue(!isset($a2_related_items[$this->metadatum_second_author_article->get_id()])); //não existe esse 
		
		$this->assertTrue(isset($a1_related_items[$this->metadatum_author_book->get_id()]));
		$this->assertTrue(isset($a2_related_items[$this->metadatum_author_book->get_id()]));

		$order = array([
			'id' => $this->metadatum_author_article->get_id(),
			'enabled' => false,
		]);
		$this->collection_article->set_metadata_order($order);
		$this->collection_article->validate();
		\tainacan_collections()->insert($this->collection_article);
		\tainacan_items()->fetch($a1->get_id());
		$a1_related_items = $a1->get_related_items();
		$this->assertTrue(!isset($a1_related_items[$this->metadatum_author_article->get_id()]));

	}
	
}