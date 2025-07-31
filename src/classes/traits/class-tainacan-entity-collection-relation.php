<?php

namespace Tainacan\Traits;
use Tainacan\Entities;
use Tainacan\Entities\Collection;

defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

// used by Item, Event, Metadatum
/**
 * Defines Collection and Items relation
 * @author medialab
 *
 */
trait Entity_Collection_Relation {

    protected $collection;
	/**
	 *
	 * @return int collection item ID
	 */
    public function get_collection_id() {
        return $this->get_mapped_property('collection_id');
    }

    /**
     * Return Collection from relation
     * @return Entities\Collection|NULL Return Collection or null on errors
     */
    public function get_collection() {

        if (is_numeric($this->get_collection_id())) {
            $Tainacan_Collections = \Tainacan\Repositories\Collections::get_instance();

            $this->collection = $Tainacan_Collections->fetch($this->get_collection_id());

            if($this->collection instanceof Entities\Collection){
                return $this->collection;
            }
        } else {
            $post = get_post($this->get_id());
            if (!$post || !$post->post_type)
                return null;
            $post_type = $post->post_type;
            $matches = array();
            $regex = '/' . Collection::$db_identifier_prefix . '([a-zA-Z0-9_]*)' . Collection::$db_identifier_sufix . '/';
            preg_match($regex, $post_type, $matches);
            $collection_id = $matches[1];
            if (is_numeric($collection_id)) {
                $this->set_collection_id($collection_id);
                return $this->get_collection();
            }
        }

        return null;
    }

    /**
     * Set collection ID
     * @param int $value
     */
    public function set_collection_id($value) {
        $this->collection = null;
        $this->set_mapped_property('collection_id', $value);
    }

    /**
     * set collection object and id
     * @param Entities\Collection $collection
     */
    public function set_collection(Entities\Collection $collection) {
        $this->collection = $collection;
        $this->set_collection_id($collection->get_id());
    }

}
