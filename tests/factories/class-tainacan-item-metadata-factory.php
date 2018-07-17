<?php

namespace Tainacan\Tests\Factories;

class Item_Metadata_Factory {
	private $item_metadata;

	public function create_item_metadata(\Tainacan\Entities\Item $item, \Tainacan\Entities\Metadatum $metadatum, $value = ''){
		$Tainacan_Item_Metadata = \Tainacan\Repositories\Item_Metadata::get_instance();
        $this->item_metadata = new \Tainacan\Entities\Item_Metadata_Entity($item, $metadatum);
        
        if (!empty($value))
            $this->item_metadata->set_value($value);
        
        if ($this->item_metadata->validate()) {
            $this->item_metadata = $Tainacan_Item_Metadata->insert($this->item_metadata);
        } 
        
        return $this->item_metadata; // If not validated, get_error() method should return the errors. Its up to the tests to use it or not
        
	}
}

?>