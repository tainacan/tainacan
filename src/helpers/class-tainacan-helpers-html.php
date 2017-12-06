<?php
namespace Tainacan\Helpers;


 class  HtmlHelpers {

     /**
      * generates select field for all publish collections
      *
      * @param string $selected The collection id for the selected option
      */
    public static function collections_dropdown($selected){
        global $Tainacan_Collections;
        $collections = $Tainacan_Collections->fetch([], 'OBJECT');
        ?>
        <select name="tnc_prop_collection_id">
            <?php foreach ($collections as $col): ?>
                <option value="<?php echo $col->get_id(); ?>" <?php selected($col->get_id(), $selected) ?>><?php echo $col->get_name(); ?></option>
            <?php endforeach; ?>
        </select>
        <?php
    }

     /**
      * generates select field for all publish collections
      *
      * @param string(json) $selected json with an array with ids to be checked
      */
    public static function collections_checkbox_list($selected) {
        global $Tainacan_Collections;
        $collections = $Tainacan_Collections->fetch([], 'OBJECT');
        $selected = json_decode($selected);
        ?>
            <?php foreach ($collections as $col): ?>
                 <input type="checkbox" name="tnc_prop_collections_ids[]" value="<?php echo $col->get_id(); ?>" <?php checked(in_array($col->get_id(), $selected)); ?> style="width: 15px;">
                 <?php echo $col->get_name(); ?>
                 <br/>
            <?php endforeach; ?>
        <?php
    }


}