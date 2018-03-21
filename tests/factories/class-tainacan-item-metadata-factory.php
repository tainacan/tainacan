<?php

namespace Tainacan\Tests\Factories;

class Item_Metadata_Factory {
	private $item_metadata;

	public function create_item_metadata(\Tainacan\Entities\Item $item, \Tainacan\Entities\Field $field, $value = ''){
		$Tainacan_Item_Metadata = \Tainacan\Repositories\Item_Metadata::getInstance();
        $item_metadata = new \Tainacan\Entities\Item_Metadata_Entity($item, $field);
        
        if (!empty($value))
            $item_metadata->set_value($value);
        
        if ($item_metadata->validate()) {
            $item_metadata = $Tainacan_Item_Metadata->insert($item_metadata);
        } 
        
        return $item_metadata; // If not validated, get_error() method should return the errors. Its up to the tests to use it or not
        
	}
}

?>