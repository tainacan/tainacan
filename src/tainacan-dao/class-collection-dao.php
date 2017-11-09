<?php

require_once(realpath(dirname(__FILE__)) .'/../tainacan-dao/class-tainacan-dao.php');

class CollectionDAO implements TainacanDAO{

    public function tainacan_insert($collection){
        $mapped_atributes = $collection->get_mapped_atributes();
        $db_identifier = Collection::DB_IDENTIFIER_META;
        
        try {
            $wp_post = new WP_Post($collection);

            $wp_post->post_title = $collection->get_name();
            $wp_post->post_content = $collection->get_description();

            $id = wp_insert_post($wp_post);

            foreach($mapped_atributes as $atribute => $value){
                if($atribute == 'meta'){
                    update_post_meta($id, $atribute, $value);
                }
            }

            if (!get_post_meta($id, $db_identifier, true)) {
                $post = get_post($id);

                add_post_meta($id, $db_identifier, $post->post_title);
            }
        }catch(Error $error){
            var_dump($error);
            return false;
        }

        return $id;
    }

    public function tainacan_register_post_type() {
        $labels = array(
            'name'               => 'Collections',
            'singular_name'      => 'Collections',
            'add_new'            => 'Adicionar Novo',
            'add_new_item'       =>'Adicionar Collections',
            'edit_item'          => 'Editar',
            'new_item'           => 'Novo Collections',
            'view_item'          => 'Visualizar',
            'search_items'       => 'Pesquisar',
            'not_found'          => 'Nenhum ticket encontrado',
            'not_found_in_trash' => 'Nenhum Collections encontrado na lixeira',
            'parent_item_colon'  => 'Collections acima:',
            'menu_name'          => 'Collections'
        );
        $args = array(
            'labels'              => $labels,
            'hierarchical'        => true,
            //'supports'            => array('title'),
            //'taxonomies'          => array(self::TAXONOMY),
            'public'              => true,
            'show_ui'             => tnc_enable_dev_wp_interface(),
            'show_in_menu'        => tnc_enable_dev_wp_interface(),
            //'menu_position'       => 5,
            //'show_in_nav_menus'   => false,
            'publicly_queryable'  => true,
            'exclude_from_search' => true,
            'has_archive'         => true,
            'query_var'           => true,
            'can_export'          => true,
            'rewrite'             => true,
            'capability_type'     => 'post',
        );
        register_post_type(Collection::POSTTYPE, $args);
    }
    
    public function tainacan_select($args){
        if(is_numeric($args)){
            $wp_post = get_post($args);
            
            return Collection::__construct_with_wp_post($wp_post);
        }
        else{
            $wp_posts = get_posts($args);

            $posts_as_objects = [];
            foreach ($wp_posts as $wp_post) {
                $posts_as_objects[] = Collection::__construct_with_wp_post($wp_post);
            }

            return $posts_as_objects;
        }
    }

    public function tainacan_delete($object){
        #TODO: Implement
    }
}

?>