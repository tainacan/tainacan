<?php

namespace Tainacan\Tests\Factories;


class Entity_Factory {

	private   $entity;
	protected $repository;
	protected $entity_type;
	protected $repository_type;

	/**
	 * Receive a type of the collection as string, an associative array,
	 * a boolean that say if the values need to be validated and inserted in database, and then
	 * create the entity type ordered.
	 *
	 * @param $type
	 * @param array $args
	 * @param bool $is_validated_and_in_db
	 */
	public function create_entity($type, $args = [], $is_validated_and_in_db = false){
		try {
			$this->entity_type     = "\Tainacan\Entities\\$type";
			$this->repository_type = "\Tainacan\Repositories\\$type".'s';

			$this->entity     = new $this->entity_type();
			$this->repository = new $this->repository_type();

			if ( ! empty( $args ) && $is_validated_and_in_db ) {
				foreach ( $args as $attribute => $content ) {
					$this->entity->set_mapped_property( $attribute, $content );
				}

				if ( $this->entity->validate() ) {
					$this->entity = $this->repository->insert( $this->entity );
				} else {
					throw new Exception( __( 'The entity wasn\'t validated.' ) );
				}
			} elseif ( empty( $args ) && ! $is_validated_and_in_db ) {
				$this->entity->set_name( "$type" . random_int( 0, 10000 ) .  " for test" );
				$this->entity->set_description( 'It is only for test' );
			} elseif ( empty( $args ) && $is_validated_and_in_db ) {
				$this->entity->set_name( "$type" . random_int( 0, 10000 ) .  " for test" );
				$this->entity->set_description( 'It is only for test' );

				if ( $this->entity->validate() ) {
					$this->entity = $this->repository->insert( $this->entity );
				} else {
					throw new Exception( __( 'The entity wasn\'t validated.' ) );
				}
			} else {
				throw new InvalidArgumentException( __( 'One or more arguments are invalid.', 'tainacan' ) );
			}
		} catch (Error $exception){
			echo($exception->getMessage());
			echo($exception->getTraceAsString());
		}
	}

	/**
	 * Return the entity type ordered
	 *
	 * @return mixed
	 */
	public function get_entity(){
		return $this->entity;
	}
}


?>