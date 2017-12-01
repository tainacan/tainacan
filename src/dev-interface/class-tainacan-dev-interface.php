<?php 

namespace Tainacan\DevInterface;

class DevInterface {
    
    
    public function __construct() {
        
        add_action('add_meta_boxes', array(&$this, 'register_metaboxes'));
        
    }
    
    /**
     * Run through all post types attributes and add metaboxes for them.
     *
     * Also run through all collections metadata and add metaboxes for its items post type
     * 
     * @return void
     */
    function register_metaboxes() {
        
        global $Tainacan_Collections, $Tainacan_Filters, $Tainacan_Logs, $Tainacan_Metadatas, $Tainacan_Taxonomies;
        
        $repositories = [$Tainacan_Collections, $Tainacan_Filters, $Tainacan_Logs, $Tainacan_Metadatas, $Tainacan_Taxonomies];
        
        foreach ($repositories as $repo) {
            
            // get repository post type
            $cpt = $repo->entities_type::get_post_type();
            
            $slug = str_replace('Tainacan\Repositories\\', '', get_class($repo));
            
            add_meta_box(
                $slug . '_properties', 
                __('Properties', 'tainacan'), 
                array(&$this, 'properties_metabox_' . $slug),
                $cpt, 
                'normal' 
                
            );
            
        }
        
    }
    
    function properties_metabox_Collections() {
        global $Tainacan_Collections;
        $this->properties_metabox($Tainacan_Collections);
    }
    function properties_metabox_Filters() {
        global $Tainacan_Filters;
        $this->properties_metabox($Tainacan_Filters);
    }
    function properties_metabox_Logs() {
        global $Tainacan_Logs;
        $this->properties_metabox($Tainacan_Logs);
    }
    function properties_metabox_Metadatas() {
        global $Tainacan_Metadatas;
        $this->properties_metabox($Tainacan_Metadatas);
    }
    function properties_metabox_Taxonomies() {
        global $Tainacan_Taxonomies;
        $this->properties_metabox($Tainacan_Taxonomies);
    }
    
    function properties_metabox($repo) {
        global $pagenow, $typenow, $post;
        
        $map = $repo->get_map();
        
        $entity = new $repo->entities_type($post);
        
        ?>
        <div id="postcustomstuff">
            <table>
                
                <thead>
                    <tr>
                        <th class="left"><?php _e('Property', 'tainacan'); ?></th>
                        <th><?php _e('Value', 'tainacan'); ?></th>
                    </tr>
                </thead>
                
                <tbody>
                    
                    <?php foreach ($map as $prop => $mapped): ?>
                        
                        <tr>
                            <td>
                                <label><?php echo $mapped['title']; ?></label><br/>
                                <small><?php echo $mapped['description']; ?></small>
                            </td>
                            <td>
                                <textarea name="<?php echo $prop; ?>"><?php echo htmlspecialchars($entity->get_mapped_property($prop)); ?></textarea>
                            </td>
                        </tr>
                        
                    <?php endforeach; ?>
                    
                </tbody>
                
            </table>
        </div>
        <?php

        
    }
    
    
    
    
}



 ?>