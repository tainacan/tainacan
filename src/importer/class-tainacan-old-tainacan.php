<?php
/**
 * Created by PhpStorm.
 * User: andre
 * Date: 03/04/18
 * Time: 08:39
 */

namespace Tainacan\Importer;

class Old_Tainacan extends Importer
{

    public $avoid = [
        'ID',
        'post_author',
        'post_date',
        'post_date_gmt',
        /*'post_content',
        'post_title',*/
        'post_excerpt',
        'post_status',
        'comment_status',
        'ping_status',
        'post_name',
        'post_modified',
        'post_modified_gmt',
        'post_content_filtered',
        'post_parent',
        'guid',
        'comment_count',
        'filter',
        'link',
        'thumbnail'
    ],
    $steps = [
        'Creating all categories' => 'create_categories',
        'Create empty collections' => 'create_collections',
        'Create repository metadata' => 'create_repo_meta',
        'Create collections metadata' => 'create_collection_metas',
        'Create collections items' => 'create_collection_items',
        'Setting relationships' => 'set_relationships'
    ], $tainacan_api_address, $wordpress_api_address;

    public function __construct()
    {
        parent::__construct();
        $this->set_repository();
        $this->set_steps($this->steps);
        $this->remove_import_method('file');
        $this->add_import_method('url');
        $this->tainacan_api_address = "/wp-json/tainacan/v1";
        $this->wordpress_api_address = "/wp-json/wp/v2";
    }

    public function verify_process_result($result)
    {
        if(is_wp_error($result))
        {
            $this->add_log('error', $result->get_error_message());
            return false;
        }else if(isset($result['body']))
        {
            return json_decode($result['body']);
        }

        return false;
    }


    public function create_categories()
    {
        $categories_link = $this->get_url() . $this->tainacan_api_address . "/categories";

        $categories = wp_remote_get($categories_link);

        $categories_array = $this->verify_process_result($categories);
        if($categories_array)
        {
            $Tainacan_Taxonomies = \Tainacan\Repositories\Taxonomies::get_instance();

            $categories_array = $this->remove_same_name($categories_array);
            foreach ($categories_array as $category)
            {

                $taxonomy = new \Tainacan\Entities\Taxonomy();

                $taxonomy->set_name($category->name);
                $taxonomy->set_description($category->description);
                $taxonomy->set_allow_insert(true);

                $Tainacan_Taxonomies->insert($taxonomy);

                $inserted_taxonomy = $Tainacan_Taxonomies->fetch($taxonomy->get_id());

                /*Insert old tainacan id*/
                add_post_meta($inserted_taxonomy->get_id(), 'old_tainacan_category_id', $category->term_id);

                if(isset($category->children) && $inserted_taxonomy)
                {
                    $this->add_all_terms($inserted_taxonomy, $category->children);
                }
            }
        }

        return false;
    }

    private function add_all_terms($taxonomy_father, $children, $term_father = null)
    {
        $Tainacan_Terms = \Tainacan\Repositories\Terms::get_instance();

        $children = $this->remove_same_name($children);
        foreach ($children as $term)
        {
            $new_term = new \Tainacan\Entities\Term();

            $new_term->set_taxonomy($taxonomy_father->get_db_identifier());
            if($term_father)
            {
                $new_term->set_parent($term_father->get_id());
            }

            $new_term->set_name($term->name);
            $new_term->set_description($term->description);

            $inserted_term = $Tainacan_Terms->insert($new_term);

            /*Insert old tainacan id*/
            add_term_meta($inserted_term->get_id(), 'old_tainacan_category_id', $term->term_id );

            if(isset($term->children))
            {
                $this->add_all_terms($taxonomy_father, $term->children, $inserted_term);
            }
        }
    }

    public function remove_same_name($terms)
    {
        $unique = [];
        $unique_terms = [];
        foreach($terms as $term)
        {
            $unique[$term->name] = $term->term_id;
        }

        foreach($terms as $index => $term)
        {
            if(in_array($term->term_id, $unique))
            {
                array_push($unique_terms, $term);
            }
        }

        return $unique_terms;
    }

    public function create_collections()
    {
        $collections_link = $this->get_url() . $this->tainacan_api_address . "/collections";
        $collections = wp_remote_get($collections_link);

        $collections_array = $this->verify_process_result($collections);
        if($collections_array)
        {
            foreach ($collections_array as $collection)
            {
                $new_collection = new \Tainacan\Entities\Collection();
                $new_collection->set_name($collection->post_title);
                $new_collection->set_status('publish');
                $new_collection->validate();
                $new_collection = \Tainacan\Repositories\Collections::get_instance()->insert($new_collection);

                /*Add old id*/
                add_post_meta($new_collection->get_id(), "old_tainacan_collection_id", $collection->ID);
            }
        }

        return false;
    }

    public function create_repo_meta()
    {
        $repository_meta_link = $this->get_url() . $this->tainacan_api_address . "/repository/metadata";
        $repo_meta = wp_remote_get($repository_meta_link);

        $repo_meta_array = $this->verify_process_result($repo_meta);
        if($repo_meta_array)
        {
            $Fields_Repository = \Tainacan\Repositories\Fields::get_instance();
            foreach ($repo_meta_array as $meta)
            {
                $avoid = [
                    'socialdb_property_fixed_title',
                    'socialdb_property_fixed_description',
                    'socialdb_property_fixed_content',
                    'socialdb_property_fixed_thumbnail',
                    'socialdb_property_fixed_attachments',
                    'stars',
                    'item',
                    'tree',
                    'compound'
                ];

                $special = [
                    'socialdb_property_fixed_type'
                ];
                if(!in_array($meta->slug, $avoid) && !in_array($meta->type, $avoid))
                {
                    $newField = new \Tainacan\Entities\Field();

                    $type = $this->define_type($meta->type);
                    $newField->set_name($meta->name);

                    $newField->set_field_type('Tainacan\Field_Types\\'.$type);

                    $newField->set_collection_id('default');
                    $newField->validate(); // there is no user input here, so we can be sure it will validate.

                    $newField = $Fields_Repository->insert($newField);
                    print $meta->type;
                }
            }
        }
        return false;
    }

    public function create_collection_metas()
    {
        return false;
    }

    public function create_collection_items()
    {
        /*Use standard method*/
        return false;
    }

    public function set_relationships()
    {
        return false;
    }

    public function fetch_from_remote( $url ){
        $url_json = explode('/colecao/', $url)[0] . "/wp-json/tainacan/v1/collections";

        $all_collections_info = wp_remote_get($url_json);

        if(isset($all_collections_info['body']))
        {
            $all_collections_array = json_decode($all_collections_info['body']);

            $collection_name = explode('/', $url);
            $collection_name = array_filter($collection_name, function($item){
                if(empty($item)) return false;

                return true;
            });
            $collection_name = end($collection_name);

            foreach($all_collections_array as $collection)
            {
                if(strcmp($collection->post_name, $collection_name) === 0)
                {
                    $link = $collection->link[0]->href;
                    break;
                }
            }

            if(!empty($link))
            {
                $info = wp_remote_get( $link."/items/?includeMetadata=1" );
                $info = json_decode($info['body']);
                $count_total_pages = ceil($info->found_items / $info->items_per_page);

                $items_json = wp_remote_get( $link."/items/?includeMetadata=1&filter[page]=1" );
                if(isset($items_json['body']))
                {
                    $items = json_decode($items_json['body']);
                    for ($i = 2; $i <= $count_total_pages; $i++)
                    {
                        $part = wp_remote_get($link . "/items/?includeMetadata=1&filter[page]=".$i);
                        if(isset($part['body']))
                        {
                            $part_array = json_decode($part['body'])->items;
                            foreach ($part_array as $item)
                            {
                                $items->items[] =  $item;
                            }
                        }
                    }
                }

                $file = fopen( $this->get_id().'.txt', 'w' );
                fwrite( $file, serialize($items));
                fclose( $file );

                return $this->set_file( $this->get_id().'.txt' );
            }
        }
    }

    /**
     * get the fields of file/url to allow mapping
     * should return an array
     *
     * @return array $fields_source the fields from the source
     */
    public function get_fields()
    {
        $file = new \SplFileObject( $this->tmp_file, 'r' );
        $file_content = unserialize($file->fread($file->getSize()));

        $item = $file_content->items[0];
        $fields = [];

        //Default meta
        foreach ($item->item as $meta_name => $value)
        {
            if(!in_array($meta_name, $this->avoid))
            {
                $fields[] = $meta_name;
            }
        }

        //Added meta
        foreach ($item->metadata as $metadata)
        {
            $fields[] = ['name' => $metadata->name, 'type' => $metadata->type];
        }

        return $fields;
    }

    /**
     * get values for a single item
     *
     * @param  $index
     * @return array with field_source's as the index and values for the
     * item
     *
     * Ex: [ 'Field1' => 'value1', 'Field2' => [ 'value2','value3' ]
     */
    public function process_item($index)
    {
        $processedItem = [];
        $headers = $this->get_fields();

        // search the index in the file and get values
        $file =  new \SplFileObject( $this->tmp_file, 'r' );
        $file_content = unserialize($file->fread($file->getSize()));
        $values = $file_content->items[$index];
        foreach ($headers as $header)
        {
            if(isset($header['name']))
            {
                $item_index = $this->search_obj_in_array($values->metadata, $header['name']);
                if(isset($values->metadata[ $item_index ]->values))
                    $processedItem[ $header['name'] ] = $values->metadata[ $item_index ]->values[0];
                else $processedItem[ $header['name'] ] = '';
            }
            else
            {
                if($header === 'link')
                {
                    $processedItem[$header] = $values->item->link[0]->href;
                }
                else
                {
                    $processedItem[$header] = $values->item->{$header};
                }
            }
        }

        return $processedItem;
    }

    public function search_obj_in_array($array, $name)
    {
        foreach ($array as $index => $obj)
        {
            if(strcmp($obj->name, $name) === 0)
            {
                return $index;
            }
        }

        return false;
    }

    public function create_fields_and_mapping()
    {
        $Tainacan_Fields = \Tainacan\Repositories\Fields::get_instance();
        $fields_repository = \Tainacan\Repositories\Fields::get_instance();

        $file_fields = $this->get_fields();

        foreach($file_fields as $index => $meta_info)
        {
            if(is_array($meta_info))
            {
                $meta_name = $meta_info['name'];
                $type = $this->define_type($meta_info['type']);
            }
            else
            {
                $meta_name = $meta_info;
                $type = 'Text';
            }

            if(!in_array($meta_name, $this->avoid))
            {
                $newField = new \Tainacan\Entities\Field();

                $newField->set_name($meta_name);

                $newField->set_field_type('Tainacan\Field_Types\\'.$type);

                $newField->set_collection($this->collection);
                $newField->validate(); // there is no user input here, so we can be sure it will validate.

                $newField = $fields_repository->insert($newField);

                $mapping[$newField->get_id()] = $file_fields[$index];
            }else
            {
                $fields = $Tainacan_Fields->fetch_by_collection( $this->collection, [], 'OBJECT' ) ;
                foreach ($fields as $field)
                {
                    if($field->WP_Post->post_name === 'title' || $field->WP_Post->post_name === 'description')
                    {
                        $mapping[$field->get_id()] = $file_fields[$meta_name];
                    }
                }
            }
        }

        $this->set_mapping($mapping);
    }

    public function define_type($type)
    {
        $type = strtolower($type);
        $tainacan_types = ['text', 'textarea', 'numeric', 'date'];

        if(in_array($type, $tainacan_types))
        {
            $type = ucfirst($type);
        }else if(strcmp($type, 'autoincrement') === 0)
        {
            $type = "Numeric";
        }else if(strcmp($type, 'item'))
        {
            $type = "Relationship";
        }else if(strcmp($type, 'tree'))
        {
            $type = "Category";
        }else if(strcmp($type, 'compound'))
        {
            $type = "Compound";
        }
        else $type = 'Text';

        return $type;
    }

    /**
    * Method implemented by the child importer class to return the number of items to be imported
    * @return int
    */
    public function get_total_items_from_source()
    {
        $file = new \SplFileObject( $this->tmp_file, 'r' );
        $file_content = unserialize($file->fread($file->getSize()));

        return $this->total_items = $file_content->found_items;
    }
}