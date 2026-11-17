<?php

namespace Tainacan\Abilities;

defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

/**
 * Composite abilities that compose other abilities.
 *
 * Every method here calls wp_get_ability( ... )->execute( ... ) rather than
 * touching repositories or controllers directly. That is the whole point: the
 * permission floor, the payload shape and the validation of each step stay
 * defined in exactly one row, and a composite cannot drift away from the
 * primitives it is built on.
 *
 * The other consequence is error handling. A composite is several writes, so it
 * can fail halfway. Each of these returns enough context to recover — the
 * collection ID that was already created, the item indexes that failed — instead
 * of a bare error that leaves an agent guessing what exists.
 *
 * @since 1.5.0
 */
class Ability_Workflows {

	use \Tainacan\Traits\Singleton_Instance;

	/**
	 * Default cap on items per batch call.
	 *
	 * @var int
	 */
	const DEFAULT_BATCH_MAX = 100;

	/**
	 * Nothing to hook.
	 *
	 * @return void
	 */
	protected function init() {
	}

	/**
	 * Create a collection together with its metadata definitions.
	 *
	 * @param mixed $input Ability input.
	 * @return array|\WP_Error
	 */
	public function scaffold( $input = [] ) {
		$input = is_array( $input ) ? $input : [];
		$name  = isset( $input['name'] ) ? trim( (string) $input['name'] ) : '';

		if ( '' === $name ) {
			return new \WP_Error(
				'tainacan_workflow_missing_name',
				__( 'A collection name is required.', 'tainacan' )
			);
		}

		$collection_payload = [ 'name' => $name ];

		foreach ( [ 'description', 'status' ] as $optional ) {
			if ( isset( $input[ $optional ] ) && '' !== $input[ $optional ] ) {
				$collection_payload[ $optional ] = $input[ $optional ];
			}
		}

		$collection = $this->run( 'tainacan/collection-create', $collection_payload );

		if ( is_wp_error( $collection ) ) {
			return $collection;
		}

		$collection_id = isset( $collection['id'] ) ? (int) $collection['id'] : 0;
		$created       = [];

		$metadata = isset( $input['metadata'] ) && is_array( $input['metadata'] ) ? $input['metadata'] : [];

		foreach ( $metadata as $index => $definition ) {
			if ( ! is_array( $definition ) ) {
				return $this->partial( $collection_id, $created, $index, __( 'Each metadata entry must be an object.', 'tainacan' ) );
			}

			$payload = [
				'collection_id' => $collection_id,
				'name'          => isset( $definition['name'] ) ? (string) $definition['name'] : '',
				'metadata_type' => isset( $definition['metadata_type'] ) ? (string) $definition['metadata_type'] : '',
			];

			foreach ( [ 'description', 'status', 'multiple', 'required' ] as $optional ) {
				if ( isset( $definition[ $optional ] ) ) {
					$payload[ $optional ] = $definition[ $optional ];
				}
			}

			$metadatum = $this->run( 'tainacan/metadata-create', $payload );

			if ( is_wp_error( $metadatum ) ) {
				return $this->partial( $collection_id, $created, $index, $metadatum->get_error_message() );
			}

			$created[] = [
				'id'            => isset( $metadatum['id'] ) ? (int) $metadatum['id'] : 0,
				'name'          => $payload['name'],
				'metadata_type' => $payload['metadata_type'],
			];
		}

		return [
			'collection_id' => $collection_id,
			'collection'    => $collection,
			'metadata'      => $created,
		];
	}

	/**
	 * Create an item with its metadata values and an optional document, in one call.
	 *
	 * @param mixed $input Ability input.
	 * @return array|\WP_Error
	 */
	public function create_item_with_metadata( $input = [] ) {
		$input = is_array( $input ) ? $input : [];

		$collection_id = isset( $input['collection_id'] ) ? (int) $input['collection_id'] : 0;
		$title         = isset( $input['title'] ) ? trim( (string) $input['title'] ) : '';

		if ( $collection_id <= 0 || '' === $title ) {
			return new \WP_Error(
				'tainacan_workflow_missing_input',
				__( 'collection_id and title are required.', 'tainacan' )
			);
		}

		$item_payload = [
			'collection_id' => $collection_id,
			'title'         => $title,
		];

		foreach ( [ 'description', 'status' ] as $optional ) {
			if ( isset( $input[ $optional ] ) && '' !== $input[ $optional ] ) {
				$item_payload[ $optional ] = $input[ $optional ];
			}
		}

		$item = $this->run( 'tainacan/item-create', $item_payload );

		if ( is_wp_error( $item ) ) {
			return $item;
		}

		$item_id  = isset( $item['id'] ) ? (int) $item['id'] : 0;
		$failures = [];

		if ( ! empty( $input['document_attachment_id'] ) ) {
			$document = $this->run(
				'tainacan/item-set-document',
				[
					'item_id'       => $item_id,
					'document'      => (string) (int) $input['document_attachment_id'],
					'document_type' => 'attachment',
				]
			);

			if ( is_wp_error( $document ) ) {
				$failures[] = [
					'step'    => 'document',
					'message' => $document->get_error_message(),
				];
			}
		}

		$set = 0;

		$metadata = isset( $input['metadata'] ) && is_array( $input['metadata'] ) ? $input['metadata'] : [];

		foreach ( $metadata as $index => $value ) {
			if ( ! is_array( $value ) || empty( $value['metadatum_id'] ) ) {
				$failures[] = [
					'step'    => 'metadata',
					'index'   => $index,
					'message' => __( 'Each metadata entry needs a metadatum_id.', 'tainacan' ),
				];
				continue;
			}

			$stored = $this->run(
				'tainacan/item-metadata-set',
				[
					'item_id'      => $item_id,
					'metadatum_id' => (int) $value['metadatum_id'],
					'values'       => isset( $value['values'] ) ? $value['values'] : '',
				]
			);

			if ( is_wp_error( $stored ) ) {
				$failures[] = [
					'step'         => 'metadata',
					'index'        => $index,
					'metadatum_id' => (int) $value['metadatum_id'],
					'message'      => $stored->get_error_message(),
				];
				continue;
			}

			$set++;
		}

		return [
			'item_id'        => $item_id,
			'item'           => $item,
			'metadata_set'   => $set,
			'failures'       => $failures,
		];
	}

	/**
	 * Create several items in one call.
	 *
	 * @param mixed $input Ability input.
	 * @return array|\WP_Error
	 */
	public function batch_items( $input = [] ) {
		$input = is_array( $input ) ? $input : [];

		$collection_id = isset( $input['collection_id'] ) ? (int) $input['collection_id'] : 0;
		$items         = isset( $input['items'] ) && is_array( $input['items'] ) ? $input['items'] : [];

		if ( $collection_id <= 0 ) {
			return new \WP_Error(
				'tainacan_workflow_missing_collection',
				__( 'A collection_id is required.', 'tainacan' )
			);
		}

		if ( empty( $items ) ) {
			return new \WP_Error(
				'tainacan_workflow_missing_items',
				__( 'An items array is required.', 'tainacan' )
			);
		}

		$max = (int) apply_filters( 'tainacan_ability_batch_max', self::DEFAULT_BATCH_MAX );

		if ( count( $items ) > $max ) {
			return new \WP_Error(
				'tainacan_workflow_batch_too_large',
				sprintf(
					/* translators: %d: maximum items per batch */
					__( 'The batch exceeds the maximum of %d items. Split it into smaller calls.', 'tainacan' ),
					$max
				)
			);
		}

		$created  = [];
		$failures = [];

		foreach ( $items as $index => $spec ) {
			if ( ! is_array( $spec ) ) {
				$failures[] = [
					'index'   => $index,
					'message' => __( 'Each item must be an object.', 'tainacan' ),
				];
				continue;
			}

			$spec['collection_id'] = $collection_id;
			$result                = $this->create_item_with_metadata( $spec );

			if ( is_wp_error( $result ) ) {
				$failures[] = [
					'index'   => $index,
					'message' => $result->get_error_message(),
				];
				continue;
			}

			$created[] = $result['item_id'];
		}

		return [
			'collection_id' => $collection_id,
			'created'       => count( $created ),
			'item_ids'      => $created,
			'failures'      => $failures,
		];
	}

	/**
	 * Apply one status change to many items.
	 *
	 * @param mixed $input Ability input.
	 * @return array|\WP_Error
	 */
	public function bulk_set_status( $input = [] ) {
		$input     = is_array( $input ) ? $input : [];
		$operation = isset( $input['operation'] ) ? (string) $input['operation'] : '';
		$item_ids  = isset( $input['item_ids'] ) && is_array( $input['item_ids'] ) ? $input['item_ids'] : [];

		$map = [
			'publish' => 'tainacan/item-publish',
			'draft'   => 'tainacan/item-unpublish',
			'trash'   => 'tainacan/item-delete',
		];

		if ( ! isset( $map[ $operation ] ) ) {
			return new \WP_Error(
				'tainacan_workflow_invalid_operation',
				__( 'operation must be one of publish, draft or trash.', 'tainacan' )
			);
		}

		if ( empty( $item_ids ) ) {
			return new \WP_Error(
				'tainacan_workflow_missing_items',
				__( 'An item_ids array is required.', 'tainacan' )
			);
		}

		$max = (int) apply_filters( 'tainacan_ability_batch_max', self::DEFAULT_BATCH_MAX * 2 );

		if ( count( $item_ids ) > $max ) {
			return new \WP_Error(
				'tainacan_workflow_batch_too_large',
				sprintf(
					/* translators: %d: maximum items per call */
					__( 'The call exceeds the maximum of %d items. Split it into smaller calls.', 'tainacan' ),
					$max
				)
			);
		}

		$ability_name = $map[ $operation ];
		$succeeded    = [];
		$failures     = [];

		foreach ( $item_ids as $index => $item_id ) {
			$result = $this->run( $ability_name, [ 'item_id' => (int) $item_id ] );

			if ( is_wp_error( $result ) ) {
				$failures[] = [
					'index'   => $index,
					'item_id' => (int) $item_id,
					'message' => $result->get_error_message(),
				];
				continue;
			}

			$succeeded[] = (int) $item_id;
		}

		return [
			'operation' => $operation,
			'succeeded' => $succeeded,
			'failures'  => $failures,
		];
	}

	/**
	 * Execute another ability.
	 *
	 * @param string $name  Ability name.
	 * @param array  $input Ability input.
	 * @return mixed
	 */
	private function run( $name, array $input ) {
		$ability = wp_get_ability( $name );

		if ( ! $ability ) {
			return new \WP_Error(
				'tainacan_workflow_missing_ability',
				sprintf(
					/* translators: %s: ability name */
					__( 'This workflow needs the %s ability, which is not registered.', 'tainacan' ),
					$name
				)
			);
		}

		return $ability->execute( $input );
	}

	/**
	 * Error that still reports what was created before the failure.
	 *
	 * A composite that fails halfway without saying what exists forces the caller
	 * to guess, and guessing produces duplicates.
	 *
	 * @param int    $collection_id Collection already created.
	 * @param array  $created       Metadata already created.
	 * @param int    $index         Index of the metadata entry that failed.
	 * @param string $message       Failure message.
	 * @return \WP_Error
	 */
	private function partial( $collection_id, array $created, $index, $message ) {
		return new \WP_Error(
			'tainacan_workflow_partial',
			sprintf(
				/* translators: 1: failure message, 2: index of the failing entry */
				__( 'The collection was created but metadata entry %2$d failed: %1$s', 'tainacan' ),
				$message,
				(int) $index
			),
			[
				'collection_id'        => (int) $collection_id,
				'metadata_created'     => $created,
				'failed_metadata_index'=> (int) $index,
			]
		);
	}
}
