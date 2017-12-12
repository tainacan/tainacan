<?php

namespace Tainacan\Tests\Factories;

class Entity_Factory {

	/**
	 * 
	 * @var \Tainacan\Entities\Entity
	 */
	private   $entity;
	/**
	 * 
	 * @var \Tainacan\Repositories\Repository
	 */
	protected $repository;
	protected $entity_type;
	protected $repository_type;

	/**
	 * Receive a type of the collection as string, an associative array,
	 * a boolean that say if the values need to be validated and inserted in database, and then
	 * create the entity type ordered and return it.
	 *
	 * @param $type
	 * @param array $args
	 * @param bool $is_validated_and_in_db
	 *
	 * @return mixed
	 * @throws \ErrorException
	 */
	public function create_entity($type, $args = [], $is_validated_and_in_db = false, $publish = false){
		ini_set('display_errors', 1);

		try {
			if(empty($type)){
				throw new \InvalidArgumentException('The type can\'t be empty');
			} elseif(!strrchr($type, '_')){
				$type = ucfirst(strtolower($type));
			} else {
				$type = ucwords(strtolower($type), '_');
			}

			$this->entity_type = "\Tainacan\Entities\\$type";

			$type_size = strlen($type);

			if($type[$type_size-1] == 'y'){
				$type[$type_size-1] = 'i';
				$this->repository_type = "\Tainacan\Repositories\\$type".'es';
			} else {
				$this->repository_type = "\Tainacan\Repositories\\$type".'s';
			}

			$this->entity     = new $this->entity_type();
			$this->repository = new $this->repository_type();
			
			if($publish) {
				$this->entity->set_status('publish');
			}

			if (!empty($args) && $is_validated_and_in_db) {
				foreach ($args as $attribute => $content) {
					if($attribute == 'add_metadata'){
						foreach ($content as $in){
							$this->entity->$attribute($in[0], $in[1]);
						}
					} else {
						$set_ = 'set_' . $attribute;
						$this->entity->$set_( $content );
					}
				}

				if ($this->entity->validate()) {
					$this->entity = $this->repository->insert($this->entity);
				} else {
					throw new \ErrorException('The entity wasn\'t validated.' . print_r( $this->entity->get_errors(), true));
				}

			} elseif (!empty($args) && !$is_validated_and_in_db){
				foreach ($args as $attribute => $content) {
					if($attribute == 'add_metadata'){
						foreach ($content as $in){
							$this->entity->$attribute($in[0], $in[1]);
						}
					} else {
						$set_ = 'set_' . $attribute;
						$this->entity->$set_( $content );
					}
				}

			} elseif (empty($args) && !$is_validated_and_in_db) {
				try {
					$this->entity->set_name( "$type " . random_int( 0, 10000 ) . " for test" );
					$this->entity->set_description( 'It is only for test' );
				} catch (\Error $exception){
					$this->entity->set_title( "$type " . random_int( 0, 10000 ) . " for test" );
					$this->entity->set_description( 'It is only for test' );
				}

			} elseif (empty($args) && $is_validated_and_in_db) {
				try {
					$this->entity->set_name( "$type " . random_int( 0, 10000 ) . " for test" );
					$this->entity->set_description( 'It is only for test' );
				} catch (\Error $exception){
					$this->entity->set_title( "$type " . random_int( 0, 10000 ) . " for test" );
					$this->entity->set_description( 'It is only for test' );
				}

				$this->entity->validate();
				$this->entity = $this->repository->insert( $this->entity );
			} else {
				throw new \InvalidArgumentException('One or more arguments are invalid.');
			}
		} catch (\Error $exception){
			echo "\n" . $exception->getMessage() . "\n";
		}
		
		return $this->entity;
	}
}

?>
