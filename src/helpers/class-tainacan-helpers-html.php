<?php
namespace Tainacan\Helpers;

use Tainacan\Entities;

 class  HtmlHelpers {

     /**
      * generates select field for all publish collections
      *
      * @param string $selected The collection id for the selected option
      * @param string $name_field (optional) default 'tnc_prop_collections_id'
      */
    public static function collections_dropdown($selected, $name_field = 'tnc_prop_collection_id'){
        global $Tainacan_Collections;
        $collections = $Tainacan_Collections->fetch([], 'OBJECT');
        ?>
        <select name="<?php echo $name_field ?>">
            <?php foreach ($collections as $col): ?>
                <option value="<?php echo $col->get_id(); ?>" <?php selected($col->get_id(), $selected) ?>><?php echo $col->get_name(); ?></option>
            <?php endforeach; ?>
        </select>
        <?php
    }

     /**
      * generates checboxes field for all publish collections
      *
      * @param string(json) $selected json with an array with ids to be checked
      * @param string $name_field (optional) default 'tnc_prop_collections_ids[]'
      */
    public static function collections_checkbox_list($selected,$name_field = 'tnc_prop_collections_ids[]') {
        global $Tainacan_Collections;
        $collections = $Tainacan_Collections->fetch([], 'OBJECT');
        $selected = json_decode($selected);
        ?>
            <?php foreach ($collections as $col): ?>
                 <input type="checkbox" name="<?php echo $name_field ?>" value="<?php echo $col->get_id(); ?>" <?php checked(in_array($col->get_id(), $selected)); ?> style="width: 15px;">
                 <?php echo $col->get_name(); ?>
                 <br/>
            <?php endforeach; ?>
        <?php
    }

     /**
      * generates select field for all publish metadata for a single collection
      *
      * @param Entities\Collection | integer $collection The collection id or the collection object
      * @param string $selected The collection id for the selected option
      * @param string $name_field (optional) default 'tnc_prop_collections_id'
      * @param array $args (optional) filter the metadata list
      */
     public static function metadata_dropdown( $collection  , $selected, $name_field = 'tnc_prop_metadata_id', $args = []){
         global $Tainacan_Metadatas;
         $collection = ( is_numeric( $collection ) ) ? new Entities\Collection( $collection ) : $collection;
         $metadata = $Tainacan_Metadatas->fetch_by_collection( $collection, $args, 'OBJECT');
         ?>
         <select name="<?php echo $name_field ?>">
             <option value=""><?php echo __('Select an option','tainacan') ?>...</option>
             <?php foreach ($metadata as $col): ?>
                 <option value="<?php echo $col->get_id(); ?>" <?php selected($col->get_id(), $selected) ?>><?php echo $col->get_name(); ?></option>
             <?php endforeach; ?>
         </select>
         <?php
     }


     /**
      * generates checkboxes fields for all publish metadata for a single collection
      *
      * @param Entities\Collection | integer $collection The collection id or the collection object
      * @param string(json) $selected string(json) | array  json with an array or array of ids to be checked
      * @param string $name_field (optional) default 'tnc_prop_tnc_metadata_ids[]' the checkbox field name
      * @param array $args (optional) filter the metadata list
      */
     public static function metadata_checkbox_list( $collection , $selected,$name_field = 'tnc_prop_tnc_metadata_ids[]', $args = []) {
         global $Tainacan_Metadatas;
         $collection = ( is_numeric( $collection ) ) ? new Entities\Collection( $collection ) : $collection;
         $metadata = $Tainacan_Metadatas->fetch_by_collection( $collection, $args, 'OBJECT');
         $selected =  ( is_array( $selected) ) ? $selected : json_decode($selected);
         $selected =  ( $selected ) ?  $selected : [];
         ?>
         <?php foreach ($metadata as $col): ?>
             <input type="checkbox" name="<?php echo $name_field ?>" value="<?php echo $col->get_id(); ?>" <?php checked(in_array($col->get_id(), $selected)); ?> style="width: 15px;">
             <?php echo $col->get_name(); ?>
             <br/>
         <?php endforeach; ?>
         <?php
     }

     /**
      * generates the radio button with options
      *
      * @param string $selected The value to be selectred
      * @param string $name_field (optional) the radio field name
      * @param array $options (optional) Associative array, indexes are the radio values and the values are the labels. Default yes and no
      */
     public static function radio_field( $selected, $name_field = 'radio_field', $options = [ 'yes' => 'Yes',  'no' => 'No' ]){
         foreach ($options as $value => $option): ?>
            <input type="radio" name="<?php echo $name_field ?>" value="<?php echo $value; ?>" <?php checked($value == $selected); ?> style="width: 15px;">
            <?php echo $option; ?>
            <br/>
        <?php endforeach;
     }

}