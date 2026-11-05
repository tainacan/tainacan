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
	 * Values of a public metadatum that a visitor cannot read, because its section or its
	 * compound parent is private, must not be matched by that visitor's search.
	 *
	 * Metadata::fetch_by_collection() already leaves these metadata out, which is why the
	 * item page does not show the values and why taxonomy terms in the same situation are
	 * not matched. Only the text values were still being found.
	 */
	public function test_metadata_hidden_by_section_or_parent_is_not_searched_anonymously() {
		$soneto    = $this->item_titled( 'Soneto de fidelidade' );
		$dialetica = $this->item_titled( 'Dialética' );

		$private_section = $this->tainacan_entity_factory->create_entity(
			'Metadata_Section',
			[
				'name'       => 'Internal',
				'collection' => $this->collection_poemas,
				'status'     => 'private',
			],
			true
		);
		$in_section      = $this->create_text_metadatum( 'In private section', [ 'metadata_section_id' => $private_section->get_id() ] );
		$this->set_text_value( $soneto, $in_section, 'Section value 001' );

		$private_compound = $this->tainacan_entity_factory->create_entity(
			'metadatum',
			[
				'name'          => 'Private compound',
				'collection'    => $this->collection_poemas,
				'metadata_type' => 'Tainacan\Metadata_Types\Compound',
				'status'        => 'private',
			],
			true
		);
		$child            = $this->create_text_metadatum( 'Child of private compound', [ 'parent' => $private_compound->get_id() ] );
		// The value is written directly: only its presence in the postmeta matters to the search.
		add_post_meta( $soneto->get_id(), (string) $child->get_id(), 'Compound value 001' );

		$public_compound = $this->tainacan_entity_factory->create_entity(
			'metadatum',
			[
				'name'          => 'Public compound',
				'collection'    => $this->collection_poemas,
				'metadata_type' => 'Tainacan\Metadata_Types\Compound',
				'status'        => 'publish',
			],
			true
		);
		$public_child    = $this->create_text_metadatum( 'Child of public compound', [ 'parent' => $public_compound->get_id() ] );
		add_post_meta( $dialetica->get_id(), (string) $public_child->get_id(), 'Public child value 001' );

		$public = $this->create_text_metadatum( 'Public', [] );
		$this->set_text_value( $dialetica, $public, 'Public value 001' );

		// A relationship in a private section must not match through the title of the related item.
		$related = $this->tainacan_entity_factory->create_entity(
			'item',
			[
				'title'      => 'Related title 001',
				'status'     => 'publish',
				'collection' => $this->collection_poemas,
			],
			true
		);
		$relationship = $this->create_text_metadatum(
			'Relationship in private section',
			[
				'metadata_type'         => 'Tainacan\Metadata_Types\Relationship',
				'metadata_section_id'   => $private_section->get_id(),
				'metadata_type_options' => [ 'collection_id' => $this->collection_poemas->get_id() ],
			]
		);
		add_post_meta( $soneto->get_id(), (string) $relationship->get_id(), (string) $related->get_id() );

		// Metadata that do not belong to the collection itself must keep being searched.
		$repository_level = $this->tainacan_entity_factory->create_entity(
			'metadatum',
			[
				'name'          => 'Repository level',
				'collection_id' => 'default',
				'metadata_type' => 'Tainacan\Metadata_Types\Text',
				'status'        => 'publish',
			],
			true
		);
		add_post_meta( $dialetica->get_id(), (string) $repository_level->get_id(), 'Repository value 001' );

		$child_collection = $this->tainacan_entity_factory->create_entity(
			'collection',
			[
				'name'   => 'Child of poems',
				'status' => 'publish',
				'parent' => $this->collection_poemas->get_id(),
			],
			true
		);
		$child_item       = $this->tainacan_entity_factory->create_entity(
			'item',
			[
				'title'      => 'Item of the child collection',
				'status'     => 'publish',
				'collection' => $child_collection,
			],
			true
		);
		add_post_meta( $child_item->get_id(), (string) $public->get_id(), 'Inherited value 001' );

		wp_set_current_user( 0 );
		$this->assertCount( 0, $this->search_poemas( 'Section value 001' ), 'Value in a private section leaked to an anonymous search' );
		$this->assertCount( 0, $this->search_poemas( 'Compound value 001' ), 'Value under a private compound leaked to an anonymous search' );
		$this->assertCount( 1, $this->search_poemas( 'Public value 001' ), 'Value of a public metadatum must still be searchable' );
		$this->assertCount( 1, $this->search_poemas( 'Public child value 001' ), 'Value under a public compound must still be searchable' );
		$this->assertCount( 1, $this->search_poemas( 'Soneto de fidelidade' ) );
		$this->assertSame( [ 'Related title 001' ], wp_list_pluck( $this->search_poemas( 'Related title 001' ), 'title' ), 'Relationship in a private section leaked to an anonymous search' );
		$this->assertCount( 1, $this->search_poemas( 'Repository value 001' ), 'Value of a repository-level metadatum must still be searchable' );

		$request = new \WP_REST_Request( 'GET', $this->namespace . '/collection/' . $child_collection->get_id() . '/items' );
		$request->set_query_params( [ 'search' => 'Inherited value 001', 'sentence' => true ] );
		$this->assertCount( 1, $this->server->dispatch( $request )->get_data()['items'], 'Value of a metadatum inherited from the parent collection must still be searchable' );

		wp_set_current_user( $this->user_id );
		$this->assertCount( 2, $this->search_poemas( 'Related title 001' ), 'Users who can read private sections still find the item through the relationship' );
		$this->assertCount( 1, $this->search_poemas( 'Section value 001' ), 'Users who can read private sections still find the value' );
		$this->assertCount( 1, $this->search_poemas( 'Compound value 001' ), 'Users who can read private metadata still find the value' );
	}

	/**
	 * Creates a text metadatum in the poems collection.
	 *
	 * @param string $name Metadatum name.
	 * @param array  $args Other attributes.
	 * @return \Tainacan\Entities\Metadatum
	 */
	private function create_text_metadatum( $name, $args ) {
		return $this->tainacan_entity_factory->create_entity(
			'metadatum',
			array_merge(
				[
					'name'          => $name,
					'collection'    => $this->collection_poemas,
					'metadata_type' => 'Tainacan\Metadata_Types\Text',
					'status'        => 'publish',
				],
				$args
			),
			true
		);
	}

	/**
	 * Sets the value of a text metadatum.
	 *
	 * @param \Tainacan\Entities\Item      $item      Item.
	 * @param \Tainacan\Entities\Metadatum $metadatum Text metadatum.
	 * @param string                       $value     Value.
	 */
	private function set_text_value( $item, $metadatum, $value ) {
		$item_metadata = new \Tainacan\Entities\Item_Metadata_Entity( $item, $metadatum );
		$item_metadata->set_value( $value );
		$this->assertTrue( $item_metadata->validate(), 'Could not set value: ' . print_r( $item_metadata->get_errors(), true ) );
		\Tainacan\Repositories\Item_Metadata::get_instance()->insert( $item_metadata );
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