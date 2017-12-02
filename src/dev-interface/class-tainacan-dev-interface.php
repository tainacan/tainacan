<?php 

namespace Tainacan\DevInterface;

class DevInterface {
    
    var $repositories = [];
    var $has_errors = false;
    
    public function __construct() {
        
        add_action('add_meta_boxes', array(&$this, 'register_metaboxes'));
        add_action('save_post', array(&$this, 'save_post'));
        add_action('admin_notices', array(&$this, 'display_error'));
        
        global $Tainacan_Collections, $Tainacan_Filters, $Tainacan_Logs, $Tainacan_Metadatas, $Tainacan_Taxonomies;
        
        $repositories = [$Tainacan_Collections, $Tainacan_Filters, $Tainacan_Logs, $Tainacan_Metadatas, $Tainacan_Taxonomies];
        
        foreach ($repositories as $repo) {
            $cpt = $repo->entities_type::get_post_type();
            $this->repositories[$cpt] = $repo;
        }
        
    }
    
    /**
     * Run through all post types attributes and add metaboxes for them.
     *
     * Also run through all collections metadata and add metaboxes for its items post type
     * 
     * @return void
     */
    function register_metaboxes() {
        
        
        foreach ($this->repositories as $cpt => $repo) {
            
            add_meta_box(
                $slug . '_properties', 
                __('Properties', 'tainacan'), 
                array(&$this, 'properties_metabox_' . $repo->get_name()),
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
        
        wp_nonce_field( 'save_'.$repo->get_name(), $repo->get_name().'_noncename' );
        
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
                        
                        <?php if ($mapped['map'] != 'meta' && $mapped['map'] != 'meta_multi') continue; ?>
                        <?php 
                            $value = $entity->get_mapped_property($prop); 
                            if (is_array($value)) $value = json_encode($value);
                        ?>
                        <tr>
                            <td>
                                <label><?php echo $mapped['title']; ?></label><br/>
                                <small><?php echo $mapped['description']; ?></small>
                            </td>
                            <td>
                                <textarea name="tnc_prop_<?php echo $prop; ?>"><?php echo htmlspecialchars($value); ?></textarea>
                            </td>
                        </tr>
                        
                    <?php endforeach; ?>
                    
                </tbody>
                
            </table>
        </div>
        <?php

        
    }
    
    function save_post($post_id) {
        
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE)
            return;
        
        $post = get_post($post_id);
        $post_type = $post->post_type;
        
        if (array_key_exists($post_type, $this->repositories)) {
            $repo = $this->repositories[$post_type];
            
            if (!isset($_POST[$repo->get_name().'_noncename']) || !wp_verify_nonce($_POST[$repo->get_name().'_noncename'], 'save_'.$repo->get_name()))
                return;
            
            $map = $repo->get_map();
            $entity = new $repo->entities_type($post);
            
            foreach ($map as $prop => $mapped) {
                
                if ($mapped['map'] != 'meta' && $mapped['map'] != 'meta_multi') 
                    continue; 
                    
                $value = $_POST["tnc_prop_" . $prop];
                if ($mapped['map'] == 'meta_multi')
                    $value = json_decode($value);
                
                $entity->set_mapped_property($prop, $value);
                if ($entity->validate_prop($prop)) {
                    // we cannot user repository->insert here, it would create an infinite loop
                    if ($mapped['map'] == 'meta') {
        				update_post_meta($post_id, $prop, $value);
        			} elseif ($mapped['map'] == 'meta_multi') {
        				
        				delete_post_meta($post_id, $prop);
        				
        				if (is_array($value)){
        					foreach ($value as $v){
        						add_post_meta($post_id, $prop, $v);
        					}
        				}
        			}
                }
                // TODO: display validation errors somehow
                // TODO: Actually we will replace it saving via ajax using API
            }
        }
        
    }
    
    function display_error() {
        if ($this->has_errors === false)
            return;
        ?>
        <div class="notice notice-success is-dismissible">
            <p><?php _e('Congratulations, you did it!', 'shapeSpace'); ?></p>
        </div>
        <?php
    }
    
}



 ?>