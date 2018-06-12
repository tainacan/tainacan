<?php
namespace Tainacan\Helpers;

use Tainacan\Entities;

 class  HtmlHelpers {

     /**
      * generates select metadatum for all publish collections
      *
      * @param string $selected The collection id for the selected option
      * @param string $name_metadatum (optional) default 'tnc_prop_collections_id'
      */
    public static function collections_dropdown($selected, $name_metadatum = 'tnc_prop_collection_id'){
        $Tainacan_Collections = \Tainacan\Repositories\Collections::get_instance();
        $collections = $Tainacan_Collections->fetch([], 'OBJECT');
        ?>
        <select name="<?php echo $name_metadatum ?>">
            <?php foreach ($collections as $col): ?>
                <option value="<?php echo $col->get_id(); ?>" <?php selected($col->get_id(), $selected) ?>><?php echo $col->get_name(); ?></option>
            <?php endforeach; ?>
        </select>
        <?php
    }

     /**
      * generates checboxes metadatum for all publish collections
      *
      * @param string(json) $selected json with an array with ids to be checked
      * @param string $name_metadatum (optional) default 'tnc_prop_collections_ids[]'
      */
    public static function collections_checkbox_list($selected,$name_metadatum = 'tnc_prop_collections_ids[]') {
        $Tainacan_Collections = \Tainacan\Repositories\Collections::get_instance();
        $collections = $Tainacan_Collections->fetch([], 'OBJECT');
        $selected = json_decode($selected);
        ?>
            <?php foreach ($collections as $col): ?>
                 <input type="checkbox" name="<?php echo $name_metadatum ?>" value="<?php echo $col->get_id(); ?>" <?php checked(in_array($col->get_id(), $selected)); ?> style="width: 15px;">
                 <?php echo $col->get_name(); ?>
                 <br/>
            <?php endforeach; ?>
        <?php
    }

     /**
      * generates select metadatum for all publish metadatum for a single collection
      *
      * @param Entities\Collection | integer $collection The collection id or the collection object
      * @param string $selected The collection id for the selected option
      * @param string $name_metadatum (optional) default 'tnc_prop_collections_id'
      * @param array $args (optional) filter the metadatum list
      */
     public static function metadata_dropdown( $collection  , $selected, $name_metadatum = 'tnc_prop_metadatum_id', $args = []){
         $Tainacan_Metadata = \Tainacan\Repositories\Metadata::get_instance();
         $collection = ( is_numeric( $collection ) ) ? new Entities\Collection( $collection ) : $collection;
         $metadatum = $Tainacan_Metadata->fetch_by_collection( $collection, $args, 'OBJECT');
         ?>
         <select name="<?php echo $name_metadatum ?>">
             <option value=""><?php echo __('Select an option','tainacan') ?>...</option>
             <?php foreach ($metadatum as $col): ?>
                 <option value="<?php echo $col->get_id(); ?>" <?php selected($col->get_id(), $selected) ?>><?php echo $col->get_name(); ?></option>
             <?php endforeach; ?>
         </select>
         <?php
     }


     /**
      * generates checkboxes metadata for all publish metadatum for a single collection
      *
      * @param Entities\Collection | integer $collection The collection id or the collection object
      * @param string(json) $selected string(json) | array  json with an array or array of ids to be checked
      * @param string $name_metadatum (optional) default 'tnc_prop_tnc_metadatum_ids[]' the checkbox metadatum name
      * @param array $args (optional) filter the metadatum list
      */
     public static function metadata_checkbox_list( $collection , $selected,$name_metadatum = 'tnc_prop_tnc_metadatum_ids[]', $args = []) {
         $Tainacan_Metadata = \Tainacan\Repositories\Metadata::get_instance();
         $collection = ( is_numeric( $collection ) ) ? new Entities\Collection( $collection ) : $collection;
         $metadatum = $Tainacan_Metadata->fetch_by_collection( $collection, $args, 'OBJECT');
         $selected =  ( is_array( $selected) ) ? $selected : json_decode($selected);
         $selected =  ( $selected ) ?  $selected : [];
         ?>
         <?php foreach ($metadatum as $col): ?>
             <input type="checkbox" name="<?php echo $name_metadatum ?>" value="<?php echo $col->get_id(); ?>" <?php checked(in_array($col->get_id(), $selected)); ?> style="width: 15px;">
             <?php echo $col->get_name(); ?>
             <br/>
         <?php endforeach; ?>
         <?php
     }

     /**
      * generates the radio button with options
      *
      * @param string $selected The value to be selectred
      * @param string $name_metadatum (optional) the radio metadatum name
      * @param array $options (optional) Associative array, indexes are the radio values and the values are the labels. Default yes and no
      */
     public static function radio_metadatum( $selected, $name_metadatum = 'radio_metadatum', $options = [ 'yes' => 'Yes',  'no' => 'No' ]){
         foreach ($options as $value => $option): ?>
            <input type="radio" name="<?php echo $name_metadatum ?>" value="<?php echo $value; ?>" <?php checked($value == $selected); ?> style="width: 15px;">
            <?php echo $option; ?>
            <br/>
        <?php endforeach;
     }

}