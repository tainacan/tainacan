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


    public $avoid = [
        'socialdb_property_fixed_title',
        'socialdb_property_fixed_description',
        'socialdb_property_fixed_content',
        'socialdb_property_fixed_thumbnail',
        'socialdb_property_fixed_attachments'
    ],
    $steps = [
        'Creating all categories' => 'create_categories',
        'Create empty collections' => 'create_collections',
        'Creating relationships metadata' => 'create_relationships_meta',
        'Create repository metadata' => 'treat_repo_meta',
        'Create collections metadata' => 'treat_collection_metas',
        'Create collections items' => 'create_collection_items',
        "Finishing" => 'clear'
    ], $tainacan_api_address, $wordpress_api_address;


    public function create_categories()
    {
        $categories_link = $this->get_url() . $this->tainacan_api_address . "/categories";

        $categories = wp_remote_get($categories_link);

        $categories_array = $this->verify_process_result($categories);
        if($categories_array)
        {
            $Tainacan_Taxonomies = \Tainacan\Repositories\Taxonomies::get_instance();

            $categories_array = $this->remove_same_name($categories_array);

            list($inside_step_pointer, $end) = $this->get_begin_end($categories_array);
            if($inside_step_pointer === false) return false;

            $created_categories = [];
            while($inside_step_pointer < $end)
            {
                $category = $categories_array[$inside_step_pointer];

                $taxonomy = new \Tainacan\Entities\Taxonomy();

                $taxonomy->set_name($category->name);
                $taxonomy->set_description($category->description);
                $taxonomy->set_allow_insert(true);

                $Tainacan_Taxonomies->insert($taxonomy);

                $inserted_taxonomy = $Tainacan_Taxonomies->fetch($taxonomy->get_id());

                /*Insert old tainacan id*/
                $created_categories[] = $category->term_id.",".$inserted_taxonomy->get_id().",".$category->name;

                if(isset($category->children) && $inserted_taxonomy)
                {
                    $this->add_all_terms($inserted_taxonomy, $category->children);
                }

                $inside_step_pointer++;
            }

            $this->save_in_file("categories", $created_categories);
        }
        return $inside_step_pointer;
    }

    public function create_collections()
    {
        $collections_link = $this->get_url() . $this->tainacan_api_address . "/collections";
        $collections = wp_remote_get($collections_link);

        $collections_array = $this->verify_process_result($collections);
        $created_collections = [];
        if($collections_array)
        {
            list($inside_step_pointer, $end) = $this->get_begin_end($collections_array);
            if($inside_step_pointer === false) return false;

            while($inside_step_pointer < $end)
            {
                $collection = $collections_array[$inside_step_pointer];

                $new_collection = new \Tainacan\Entities\Collection();
                $new_collection->set_name($collection->post_title);
                $new_collection->set_status('publish');
                $new_collection->validate();
                $new_collection = \Tainacan\Repositories\Collections::get_instance()->insert($new_collection);

                /*Add old id*/
                $created_collections[] = $collection->ID.",".$new_collection->get_id().",".$collection->post_title;

                $inside_step_pointer++;
            }
            $this->save_in_file("collections", $created_collections);
        }

        return $inside_step_pointer;
    }

    public function create_relationships_meta()
    {
        $relationships = [];
        $created_collection = $this->read_from_file("collections");
        $created_categories = $this->read_from_file("categories");
        foreach ($created_categories as $category_id => $category)
        {
            $category_name = $category['name'];
            foreach ($created_collection as $collection_id => $collection)
            {
                $collection_name = $collection['name'];
                if(strcmp($category_name, $collection_name) === 0)
                {
                    $relationships[] = $category_id.",".$collection['new_id'].",".$category['name'];
                }
            }
        }

        $this->save_in_file('relationships', $relationships);
        return false;
    }

    public function treat_repo_meta()
    {
        $repository_meta_link = $this->get_url() . $this->tainacan_api_address . "/repository/metadata?includeMetadata=1";
        $repo_meta = wp_remote_get($repository_meta_link);

        $repo_meta_array = $this->verify_process_result($repo_meta);
        $Fields_Repository = \Tainacan\Repositories\Fields::get_instance();
        $created_categories = $this->read_from_file("categories");
        $relationships = $this->read_from_file("relationships");

        $this->create_meta_repo($repo_meta_array, $Fields_Repository, $created_categories, $relationships);

        return false;
    }

    private function create_meta_repo($repo_meta_array, $Fields_Repository, $created_categories, $relationships, $compound_id = null)
    {
        if($repo_meta_array)
        {
            $repository_fields = [];
            foreach ($repo_meta_array as $meta)
            {
                $special = [
                    'socialdb_property_fixed_type',
                    'stars'
                ];

                if(!in_array($meta->slug, $this->avoid) && !in_array($meta->type, $special))
                {
                    $newField = $this->set_fields_properties($meta, $created_categories, $relationships, $Fields_Repository, $compound_id);

                    if ($newField)
                    {
                        $newField = $Fields_Repository->insert($newField);
                        $repository_fields[] = $meta->id . "," . $newField->get_id() . "," . $meta->name;
                    }
                }
            }

            $this->save_in_file('repository_fields', $repository_fields);
        }
    }

    public function treat_collection_metas()
    {
        $created_collections = $this->read_from_file("collections");
        $created_repository_fields = $this->read_from_file("repository_fields");
        $created_categories = $this->read_from_file("categories");
        $relationships = $this->read_from_file("relationships");

        list($inside_step_pointer, $end) = $this->get_begin_end($created_collections);
        if($inside_step_pointer === false) return false;

        $Tainacan_Fields = \Tainacan\Repositories\Fields::get_instance();
        $Fields_Repository = \Tainacan\Repositories\Fields::get_instance();
        $Repository_Collections = \Tainacan\Repositories\Collections::get_instance();

        for($i = 0; $i < $inside_step_pointer; $i++)
        {
            next($created_collections);
        }

        while($inside_step_pointer < $end)
        {
            $collection_info = current($created_collections);
            $new_collection_id = $collection_info['new_id'];
            $old_collection_id = key($created_collections);
            $collection = $Repository_Collections->fetch($new_collection_id);
            $this->set_collection($collection);

            $file_fields = $this->get_collection_fields($old_collection_id);

            $mapping = $this->create_collection_meta($file_fields, $Fields_Repository, $Tainacan_Fields, $created_repository_fields, $created_categories, $relationships);

            $this->set_repository_mapping($mapping, $old_collection_id);
            next($created_collections);
            $inside_step_pointer++;
        }

        return $inside_step_pointer;
    }

    private function create_collection_meta(
        $file_fields,
        $Fields_Repository,
        $Tainacan_Fields,
        $created_repository_fields,
        $created_categories,
        $relationships,
        $compound_id = null)
    {
        if($file_fields)
        {
            foreach($file_fields as $index => $meta)
            {
                $meta_slug = $meta->slug;
                $old_field_id = $meta->id;

                if(!in_array($meta_slug, $this->avoid) && !isset($created_repository_fields[$old_field_id]))
                {
                    $newField = $this->set_fields_properties(
                        $meta,
                        $created_categories,
                        $relationships,
                        $Fields_Repository,
                        $compound_id,
                        $created_repository_fields,
                        false,
                        $Tainacan_Fields);

                    if($newField->validate())
                    {
                        $newField = $Fields_Repository->insert($newField);

                        $mapping[$newField->get_id()] = $file_fields[$index]->name;
                    }
                }else /*Map to respository fields*/
                {
                    if(isset($created_repository_fields[$old_field_id]))
                    {
                        $new_id = $created_repository_fields[$old_field_id]['new_id'];
                        $mapping[$new_id] = $created_repository_fields[$old_field_id]['name'];
                    }else
                    {
                        $fields = $Tainacan_Fields->fetch_by_collection( $this->collection, [], 'OBJECT' );

                        foreach ($fields as $field)
                        {
                            if(($field->WP_Post->post_name === 'title' || $field->WP_Post->post_name === 'description') &&
                                ($meta_slug === 'socialdb_property_fixed_title' || $meta_slug === 'socialdb_property_fixed_description'))
                            {
                                $mapping[$field->get_id()] = $field->WP_Post->post_name;
                            }
                        }
                    }

                }
            }
        }

        return $mapping;
    }

    private function set_fields_properties(
        $meta,
        $created_categories,
        $relationships,
        $Fields_Repository,
        $compound_id,
        $created_repository_fields = null,
        $is_repository = true,
        $Tainacan_Fields = null)
    {
        $newField = new \Tainacan\Entities\Field();


        $name = $meta->name;
        $type = $meta->type;

        $type = $this->define_type($type);
        $newField->set_name($name);

        $newField->set_field_type('Tainacan\Field_Types\\'.$type);
        if(strcmp($type, "Category") === 0)
        {
            $taxonomy_id = $meta->metadata->taxonomy;
            if(isset($created_categories[$taxonomy_id]))
            {
                $new_category_id = $created_categories[$taxonomy_id]['new_id'];
                $newField->set_field_type_options(['taxonomy_id' => $new_category_id]);
            }
        }else if(strcmp($type, "Relationship") === 0)
        {
            $taxonomy_id = $meta->metadata->object_category_id;

            if(isset($relationships[$taxonomy_id]))
            {
                $new_collection_id = $relationships[$taxonomy_id]['new_id'];
                $newField->set_field_type_options(['collection_id' => $new_collection_id]);
            }
        }else if(strcmp($type, "Compound") === 0)
        {
            if($is_repository)
            {
                $this->create_meta_repo(
                    $meta->metadata->children,
                    $Fields_Repository,
                    $created_categories,
                    $relationships,
                    $newField->get_id());
            }else
            {
                $this->create_collection_meta(
                    $meta->metadata->children,
                    $Fields_Repository,
                    $Tainacan_Fields,
                    $created_repository_fields,
                    $created_categories,
                    $relationships,
                    $newField->get_id());
            }
        }

        /*Compound treatement*/
        if($compound_id === null)
        {
            if($is_repository)
            {
                $newField->set_collection_id('default');
            }else
            {
                $newField->set_collection($this->collection);
            }
        }else //Set compound as field parent
        {
            $newField->set_parent($compound_id);
        }

        /*Properties of field*/
        if(isset($meta->metadata))
        {
            if($meta->metadata->required == 1)
            {
                $newField->set_required(true);
            }
            if(!empty($meta->metadata->default_value))
            {
                $newField->set_default_value($meta->metadata->default_value);
            }
            /*if(!empty($meta->metadata->text_help))
            {

            }*/
            if(!empty($meta->metadata->cardinality))
            {
                if($meta->metadata->cardinality > 1)
                {
                    $newField->set_multiple('yes');
                }
            }
        }

        if($newField->validate()){
            return $newField;
        }else return false;
    }

    public function create_collection_items()
    {
        $created_collections = $this->read_from_file("collections");
        if(!empty($created_collections))
        {
            $Repository_Collections = \Tainacan\Repositories\Collections::get_instance();
            $collection_info = current($created_collections);
            $new_collection_id = $collection_info['new_id'];
            $old_collection_id = key($created_collections);
            $collection = $Repository_Collections->fetch($new_collection_id);
            $this->set_collection($collection);

            $mapping = $this->get_repository_mapping($old_collection_id);
            $this->set_mapping($mapping);

            $this->process($this->get_start());
        }

        return false;
    }

    public function clear()
    {
        unlink($this->get_id()."_categories.txt");
        unlink($this->get_id()."_collections.txt");
        unlink($this->get_id()."_relationships.txt");
        unlink($this->get_id()."_repository_fields.txt");
        return false;
    }

    /*Aux functions*/
    private function get_collection_fields($collections_id)
    {
        $fields_link = $this->get_url() . $this->tainacan_api_address . "/collections/".$collections_id."/metadata";
        $collection = wp_remote_get($fields_link);

        $collection_metadata = $this->verify_process_result($collection);
        if($collection_metadata)
        {
            $fields = [];
            foreach ($collection_metadata[0]->{'tab-properties'} as $metadata)
            {
                $field_details_link = $fields_link . "/". $metadata->id;
                $field_details = wp_remote_get($field_details_link);
                $field_details = $this->verify_process_result($field_details);

                if($field_details)
                {
                    $fields[] = $field_details[0];
                }
            }

            return $fields;
        }

        return false;
    }

    private function read_from_file($name)
    {
        $file_name = $this->get_id()."_".$name.".txt";
        $fp = fopen($file_name, "r");
        $content = explode("|", fread($fp, filesize($file_name)));
        $result = [];
        foreach ($content as $ids)
        {
            $old_new_name = explode(",", $ids);
            $result[$old_new_name[0]] = ['new_id' => $old_new_name[1], 'name' => $old_new_name[2]];
        }

        return $result;
    }

    private function save_in_file($name, $ids)
    {
        /*Old ID, new ID*/
        $file_name = $this->get_id()."_".$name.".txt";
        $content = implode("|", $ids);
        if(file_exists($file_name))
        {
            $content = "|".$content;
        }

        $fp = fopen($file_name, "a+");

        fwrite($fp, $content);

        fclose($fp);
    }

    private function get_begin_end($items)
    {
        $inside_step_pointer = $this->get_inside_step_pointer();
        $total_items = count($items);

        if($inside_step_pointer >= $total_items)
        {
            return [false, false];
        }

        $end = $this->get_inside_step_pointer() + $this->get_items_per_step();
        if($end > $total_items)
        {
            $end = $total_items;
        }

        return [$inside_step_pointer, $end];
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

    private function remove_same_name($terms)
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

    private function verify_process_result($result)
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
        }else if(strcmp($type, 'item') === 0)
        {
            $type = "Relationship";
        }else if(strcmp($type, 'tree') === 0)
        {
            $type = "Category";
        }else if(strcmp($type, 'compound') === 0)
        {
            $type = "Compound";
        }
        else $type = 'Text';

        return $type;
    }

    /*END aux functions*/

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

    public function get_fields()
    {

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