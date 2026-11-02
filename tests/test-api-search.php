<?php

namespace Tainacan\Tests;

/**
 * @group api
 * **/
class TAINACAN_REST_Search extends TAINACAN_UnitApiTestCase {
	function setUp(): void {
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
		$search_query = ['search' => '"Vinícius de Moraes"', 'sentence' => false];
		$search_collection_poemas->set_query_params($search_query);
		$search_response = $this->server->dispatch($search_collection_poemas);
		$items = $search_response->get_data()['items'];

		$this->assertCount(2, $items);

		$search_collection_poemas = new \WP_REST_Request('GET', $this->namespace . '/collection/' . $this->collection_poemas->get_id() . '/items');
		$search_query = ['search' => 'Vinícius de Moraes', 'sentence' => true];
		$search_collection_poemas->set_query_params($search_query);
		$search_response = $this->server->dispatch($search_collection_poemas);
		$items = $search_response->get_data()['items'];

		$this->assertCount(2, $items);

		$search_collection_frase = new \WP_REST_Request('GET', $this->namespace . '/collection/' . $this->collection_frases->get_id() . '/items');
		$search_query = ['search' => '"Guimarães Rosa"', 'sentence' => false];
		$search_collection_frase->set_query_params($search_query);
		$search_response = $this->server->dispatch($search_collection_frase);
		$items = $search_response->get_data()['items'];

		$this->assertCount(2, $items);

		$search_collection_frase = new \WP_REST_Request('GET', $this->namespace . '/collection/' . $this->collection_frases->get_id() . '/items');
		$search_query = ['search' => 'Guimarães Rosa', 'sentence' => true];
		$search_collection_frase->set_query_params($search_query);
		$search_response = $this->server->dispatch($search_collection_frase);
		$items = $search_response->get_data()['items'];

		$this->assertCount(2, $items);


		$search_items = new \WP_REST_Request('GET', $this->namespace . '/items');
		$search_query = ['search' => 'texto', 'sentence' => false];
		$search_items->set_query_params($search_query);
		$search_response = $this->server->dispatch($search_items);
		$items = $search_response->get_data()['items'];

		$this->assertCount(4, $items);

		$search_items = new \WP_REST_Request('GET', $this->namespace . '/items');
		$search_query = ['search' => 'texto poesia', 'sentence' => false];
		$search_items->set_query_params($search_query);
		$search_response = $this->server->dispatch($search_items);
		$items = $search_response->get_data()['items'];

		$this->assertCount(4, $items);

		$search_items = new \WP_REST_Request('GET', $this->namespace . '/items');
		$search_query = ['search' => '"texto poesia"', 'sentence' => false];
		$search_items->set_query_params($search_query);
		$search_response = $this->server->dispatch($search_items);
		$items = $search_response->get_data()['items'];

		$this->assertCount(1, $items);

		$search_items = new \WP_REST_Request('GET', $this->namespace . '/items');
		$search_query = ['search' => '"texto poesia" sagarana', 'sentence' => false];
		$search_items->set_query_params($search_query);
		$search_response = $this->server->dispatch($search_items);
		$items = $search_response->get_data()['items'];
		$this->assertCount(2, $items);
		

		$search_items = new \WP_REST_Request('GET', $this->namespace . '/items');
		$search_query = ['search' => 'infinito dure', 'sentence' => false];
		$search_items->set_query_params($search_query);
		$search_response = $this->server->dispatch($search_items);
		$items = $search_response->get_data()['items'];

		$this->assertCount(1, $items);

		
	}

	public function test_search_sentence_default() {
		$search_collection_poemas = new \WP_REST_Request( 'GET', $this->namespace . '/collection/' . $this->collection_poemas->get_id() . '/items' );
		$search_items = new \WP_REST_Request( 'GET', $this->namespace . '/items' );

		// Phrase default: omitted sentence behaves like sentence=true
		delete_option( 'tainacan_option_search_each_word_by_default' );
		$search_collection_poemas->set_query_params( [ 'search' => 'Vinícius de Moraes' ] );
		$search_response = $this->server->dispatch( $search_collection_poemas );
		$items = $search_response->get_data()['items'];
		$this->assertCount( 2, $items );

		// Per-word default: omitted sentence behaves like sentence=false
		update_option( 'tainacan_option_search_each_word_by_default', true );
		$search_items->set_query_params( [ 'search' => 'texto poesia' ] );
		$search_response = $this->server->dispatch( $search_items );
		$items = $search_response->get_data()['items'];
		$this->assertCount( 4, $items );

		// Explicit sentence=true overrides per-word default (phrase match: 1 item, not 4)
		$search_items->set_query_params( [ 'search' => 'texto poesia', 'sentence' => true ] );
		$search_response = $this->server->dispatch( $search_items );
		$items = $search_response->get_data()['items'];
		$this->assertCount( 1, $items );

		$search_items->set_query_params( [ 'search' => 'texto poesia', 'sentence' => false ] );
		$search_response = $this->server->dispatch( $search_items );
		$items = $search_response->get_data()['items'];
		$this->assertCount( 4, $items );

		delete_option( 'tainacan_option_search_each_word_by_default' );
	}

	/**
	 * Terms of a private taxonomy metadatum must not be matched by the search of users
	 * who cannot read private metadata, while terms of public taxonomy metadata still are.
	 *
	 * @see https://github.com/tainacan/tainacan/issues/1125
	 */
	public function test_private_taxonomy_metadata_is_not_searched_without_permission() {
		$soneto    = $this->item_titled( 'Soneto de fidelidade' );
		$dialetica = $this->item_titled( 'Dialética' );

		$this->set_taxonomy_value( $soneto, $this->create_taxonomy_metadatum( 'Acervo interno', 'private' ), 'My taxonomy 001' );
		$this->set_taxonomy_value( $dialetica, $this->create_taxonomy_metadatum( 'Acervo aberto', 'publish' ), 'Publico 001' );

		wp_set_current_user( 0 );
		$this->assertCount( 0, $this->search_poemas( 'My taxonomy 001' ), 'Term of a private taxonomy metadatum leaked to an anonymous user' );
		$this->assertCount( 1, $this->search_poemas( 'Publico 001' ), 'Term of a public taxonomy metadatum must still be searchable' );
		$this->assertCount( 1, $this->search_poemas( 'Soneto de fidelidade' ) );

		wp_set_current_user( $this->user_id );
		$this->assertCount( 1, $this->search_poemas( 'My taxonomy 001' ), 'Users who can read private metadata still find the term' );
	}

	/**
	 * Creates a taxonomy plus a taxonomy metadatum (accepting new terms) in the poems collection.
	 *
	 * @param string $name   Taxonomy and metadatum name.
	 * @param string $status Metadatum status.
	 * @return \Tainacan\Entities\Metadatum
	 */
	private function create_taxonomy_metadatum( $name, $status ) {
		$taxonomy = $this->tainacan_entity_factory->create_entity(
			'taxonomy',
			array(
				'name'            => $name,
				'allow_insert'    => 'yes',
				'status'          => 'publish',
				'collections_ids' => [ $this->collection_poemas->get_id() ],
			),
			true
		);

		return $this->tainacan_entity_factory->create_entity(
			'metadatum',
			array(
				'name'                  => $name,
				'collection'            => $this->collection_poemas,
				'metadata_type'         => 'Tainacan\Metadata_Types\Taxonomy',
				'status'                => $status,
				'metadata_type_options' => [
					'allow_new_terms' => 'yes',
					'taxonomy_id'     => $taxonomy->get_id(),
				],
			),
			true
		);
	}

	/**
	 * Assigns a (new) term to an item through a taxonomy metadatum.
	 *
	 * @param \Tainacan\Entities\Item      $item      Item.
	 * @param \Tainacan\Entities\Metadatum $metadatum Taxonomy metadatum.
	 * @param string                       $term_name Term to create and assign.
	 */
	private function set_taxonomy_value( $item, $metadatum, $term_name ) {
		$item_metadata = new \Tainacan\Entities\Item_Metadata_Entity( $item, $metadatum );
		$item_metadata->set_value( $term_name );
		$this->assertTrue( $item_metadata->validate(), 'Could not assign term ' . $term_name . ': ' . print_r( $item_metadata->get_errors(), true ) );
		\Tainacan\Repositories\Item_Metadata::get_instance()->insert( $item_metadata );
	}

	/**
	 * Finds an item of the poems collection by its title.
	 *
	 * @param string $title Item title.
	 * @return \Tainacan\Entities\Item
	 */
	private function item_titled( $title ) {
		$items = \Tainacan\Repositories\Items::get_instance()->fetch( [ 'posts_per_page' => -1 ], $this->collection_poemas, 'OBJECT' );
		foreach ( $items as $item ) {
			if ( $item->get_title() === $title ) {
				return $item;
			}
		}
		$this->fail( 'Item not found: ' . $title );
	}

	/**
	 * Searches the poems collection through the REST API with an exact sentence.
	 *
	 * @param string $search Search sentence.
	 * @return array Items of the response.
	 */
	private function search_poemas( $search ) {
		$request = new \WP_REST_Request( 'GET', $this->namespace . '/collection/' . $this->collection_poemas->get_id() . '/items' );
		$request->set_query_params( [ 'search' => $search, 'sentence' => true ] );
		return $this->server->dispatch( $request )->get_data()['items'];
	}
}