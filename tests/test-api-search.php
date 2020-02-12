<?php

namespace Tainacan\Tests;

/**
 * @group api
 * **/
class TAINACAN_REST_Search extends TAINACAN_UnitApiTestCase {
	function setUp() {
		parent::setUp();
		$Tainacan_Item_Metadata = \Tainacan\Repositories\Item_Metadata::get_instance();
		$Tainacan_Metadata = \Tainacan\Repositories\Metadata::get_instance();

		// Populate the database
		$author = $this->tainacan_entity_factory->create_entity(
			'metadatum',
			array(
				'name'              => 'Autor',
				'collection_id'     => $Tainacan_Metadata->get_default_metadata_attribute(),
				'metadata_type'  => 'Tainacan\Metadata_Types\Text',
				'status'            => 'publish'
			),
			true
		);

		$keywords = $this->tainacan_entity_factory->create_entity(
			'metadatum',
			array(
				'name'              => 'keywords',
				'collection_id'     => $Tainacan_Metadata->get_default_metadata_attribute(),
				'metadata_type'  => 'Tainacan\Metadata_Types\Text',
				'status'            => 'publish'
			),
			true
		);


		// collections:
		$this->collection_poemas = $this->tainacan_entity_factory->create_entity(
			'collection',
			[
				'name'        => 'Poemas',
				'description' => 'Poemas',
				'status'      => 'publish'
			],
			true
		);

		$taxonomy_tipo = $this->tainacan_entity_factory->create_entity(
			'taxonomy',
			array(
				'name'            => 'Tipo',
				'description'     => 'Classificação poesia',
				'allow_insert'    => 'no',
				'status'          => 'publish',
				'collections_ids' => [$this->collection_poemas->get_id()]
			),
			true
		);

		// Create Term
		$term_poesia = $this->tainacan_entity_factory->create_entity(
			'term',
			array(
				'taxonomy' => $taxonomy_tipo->get_db_identifier(),
				'name'     => 'Poesia',
				'user'     => get_current_user_id(),
			),
			true
		);

		$term_soneto = $this->tainacan_entity_factory->create_entity(
			'term',
			array(
				'taxonomy' => $taxonomy_tipo->get_db_identifier(),
				'name'     => 'Soneto',
				'user'     => get_current_user_id(),
				'parent'   => $term_poesia->get_id()
			),
			true
		);

		$metadatum_tipo = $this->tainacan_entity_factory->create_entity(
			'metadatum',
			array(
				'name'              => 'tipo',
				'description'       => 'tipo',
				'collection'        => $this->collection_poemas,
				'metadata_type'  => 'Tainacan\Metadata_Types\Taxonomy',
				'metadata_type_options' => [
					'allow_new_terms' => 'yes',
					'taxonomy_id' => $taxonomy_tipo->get_id()
				],
			),
			true
		);

		//items:
		$soneto = $this->tainacan_entity_factory->create_entity(
			'item',
			[
				'status'      => 'publish',
				'collection'  => $this->collection_poemas,
				'title'       => 'Soneto de fidelidade',
				'description' => "
					De tudo ao meu amor serei atento
					Antes, e com tal zelo, e sempre, e tanto
					Que mesmo em face do maior encanto
					Dele se encante mais meu pensamento.
					
					Quero vivê-lo em cada vão momento
					E em seu louvor hei de espalhar meu canto
					E rir meu riso e derramar meu pranto
					Ao seu pesar ou seu contentamento
					
					E assim, quando mais tarde me procure
					Quem sabe a morte, angústia de quem vive
					Quem sabe a solidão, fim de quem ama
					
					Eu possa me dizer do amor (que tive):
					Que não seja imortal, posto que é chama
					Mas que seja infinito enquanto dure."
			],
			true
		);

		$dialetica = $this->tainacan_entity_factory->create_entity(
			'item',
			[
				'status'      => 'publish',
				'collection'  => $this->collection_poemas,
				'title'       => 'Dialética',
				'description' => "
					É claro que a vida é boa
					E a alegria, a única indizível emoção
					É claro que te acho linda
					Em ti bendigo o amor das coisas simples
					É claro que te amo
					E tenho tudo para ser feliz
					Mas acontece que eu sou triste"
			],
			true
		);

		$soneto_meta = new \Tainacan\Entities\Item_Metadata_Entity($soneto, $metadatum_tipo);
		$soneto_meta->set_value('Soneto');
		$soneto_meta->validate();
		$Tainacan_Item_Metadata->insert($soneto_meta);
		$this->tainacan_item_metadata_factory->create_item_metadata($soneto, $author, 'Vinícius de Moraes');

		$poesia_meta = new \Tainacan\Entities\Item_Metadata_Entity($dialetica, $metadatum_tipo);
		$poesia_meta->set_value('Poesia');
		$poesia_meta->validate();
		$Tainacan_Item_Metadata->insert($poesia_meta);
		$this->tainacan_item_metadata_factory->create_item_metadata($dialetica, $author, 'Vinícius de Moraes');


		$this->collection_frases = $this->tainacan_entity_factory->create_entity(
			'collection',
			[
				'name'        => 'Frases',
				'description' => 'Frases',
				'status'      => 'publish'
			],
			true
		);

		//items:
		$frase_1 = $this->tainacan_entity_factory->create_entity(
			'item',
			[
				'status'      => 'publish',
				'collection'  => $this->collection_frases,
				'title'       => 'Frase 1',
				'description' => "Eu estou triste como sapo na água suja"
			],
			true
		);

		$frase_2 = $this->tainacan_entity_factory->create_entity(
			'item',
			[
				'status'      => 'publish',
				'collection'  => $this->collection_frases,
				'title'       => 'Frase 2',
				'description' => "O mais importante e bonito, do mundo, é isto: que as pessoas não estão sempre iguais,
													ainda não foram terminadas - mas que elas vão sempre mudando. Afinam ou desafinam,
													verdade maior. É o que a vida me ensinou. Isso que me alegra montão."
			],
			true
		);

		$origem = $this->tainacan_entity_factory->create_entity(
			'metadatum',
			array(
				'name'           => 'Origem',
				'collection'     => $this->collection_frases,
				'metadata_type'  => 'Tainacan\Metadata_Types\Text',
				'status'         => 'publish'
			),
			true
		);

		$this->tainacan_item_metadata_factory->create_item_metadata($frase_1, $author, 'Guimarães Rosa');
		$this->tainacan_item_metadata_factory->create_item_metadata($frase_1, $origem, 'Sagarana');

		$this->tainacan_item_metadata_factory->create_item_metadata($frase_2, $author, 'Guimarães Rosa');
		$this->tainacan_item_metadata_factory->create_item_metadata($frase_2, $origem, 'Grande sertão: Veredas');

		$this->tainacan_item_metadata_factory->create_item_metadata($soneto,    $keywords, 'poema poesia texto vinicius moraes soneto');
		$this->tainacan_item_metadata_factory->create_item_metadata($dialetica, $keywords, 'poema texto poesia vinicius moraes');
		$this->tainacan_item_metadata_factory->create_item_metadata($frase_2,   $keywords, 'texto frase veredas guimaraes rosa sertao grande');
		$this->tainacan_item_metadata_factory->create_item_metadata($frase_1,   $keywords, 'frase texto guimaraes rosa sagarana');

	}

	public function test_search() {

		$search_collection_poemas = new \WP_REST_Request('GET', $this->namespace . '/collection/' . $this->collection_poemas->get_id() . '/items');
		$search_query = ['search' => '"Vinícius de Moraes"'];
		$search_collection_poemas->set_query_params($search_query);
		$search_response = $this->server->dispatch($search_collection_poemas);
		$items = $search_response->get_data()['items'];

		$this->assertCount(2, $items);

		$search_collection_frase = new \WP_REST_Request('GET', $this->namespace . '/collection/' . $this->collection_frases->get_id() . '/items');
		$search_query = ['search' => '"Guimarães Rosa"'];
		$search_collection_frase->set_query_params($search_query);
		$search_response = $this->server->dispatch($search_collection_frase);
		$items = $search_response->get_data()['items'];

		$this->assertCount(2, $items);


		$search_items = new \WP_REST_Request('GET', $this->namespace . '/items');
		$search_query = ['search' => 'texto'];
		$search_items->set_query_params($search_query);
		$search_response = $this->server->dispatch($search_items);
		$items = $search_response->get_data()['items'];

		$this->assertCount(4, $items);

		$search_items = new \WP_REST_Request('GET', $this->namespace . '/items');
		$search_query = ['search' => 'texto poesia'];
		$search_items->set_query_params($search_query);
		$search_response = $this->server->dispatch($search_items);
		$items = $search_response->get_data()['items'];

		$this->assertCount(4, $items);

		$search_items = new \WP_REST_Request('GET', $this->namespace . '/items');
		$search_query = ['search' => '"texto poesia"'];
		$search_items->set_query_params($search_query);
		$search_response = $this->server->dispatch($search_items);
		$items = $search_response->get_data()['items'];

		$this->assertCount(1, $items);

		$search_items = new \WP_REST_Request('GET', $this->namespace . '/items');
		$search_query = ['search' => '"texto poesia" sagarana'];
		$search_items->set_query_params($search_query);
		$search_response = $this->server->dispatch($search_items);
		$items = $search_response->get_data()['items'];

		$this->assertCount(2, $items);

		$search_items = new \WP_REST_Request('GET', $this->namespace . '/items');
		$search_query = ['search' => 'infinito dure'];
		$search_items->set_query_params($search_query);
		$search_response = $this->server->dispatch($search_items);
		$items = $search_response->get_data()['items'];

		$this->assertCount(1, $items);

		
	}
}

?>