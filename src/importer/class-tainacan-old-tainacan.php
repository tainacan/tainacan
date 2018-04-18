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
    public function __construct($import_structure_and_mapping = false) {
        parent::__construct();

        $this->remove_import_method('file');
        $this->add_import_method('url');

        $this->import_structure_and_mapping = $import_structure_and_mapping;
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
                $items = wp_remote_get( $link."/items/?includeMetadata=1" );

                if(isset($items['body']))
                {
                    $items_array = json_decode($items['body']);

                    //Get Metatype
                    $meta_type = wp_remote_get($link."/metadata");
                    if(isset($meta_type['body']))
                    {
                        $meta_type_array = json_decode($meta_type['body']);
                        $file_info['items'] = $items_array;
                        $file_info['meta'] = $meta_type_array;

                        $file = fopen( $this->get_id().'.txt', 'w' );
                        fwrite( $file, serialize($file_info) );
                        fclose( $file );
                        return $this->set_file( $this->get_id().'.txt' );
                    }
                }
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


        foreach($file_content['meta'] as $tab)
        {
            foreach($tab->{"tab-properties"} as $meta)
            {
                $fields[] = ['name' => $meta->name, 'type' => $meta->type];
            }
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

        /*to fix this*/
        $values = $file_content['items']->items[$index]->item;

        if( count( $headers ) !== count( $values ) ){
            return false;
        }

        foreach ($headers as $header) {
            $processedItem[ $header['name'] ] = $values[ $header['name'] ];
        }

        return $processedItem;
    }

    function create_fields_and_mapping() {

        $fields_repository = \Tainacan\Repositories\Fields::get_instance();

        $file_fields = $this->get_fields();
        /*$avoid = [
            'ID',
            'post_author',
            'post_date',
            'post_date_gmt',
            'post_content',
            'post_title',
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
        ];*/

        foreach($file_fields as $index => $meta_info)
        {
            $newField = new \Tainacan\Entities\Field();

            $newField->set_name($meta_info['name']);

            $type = 'Text';

            $newField->set_field_type('Tainacan\Field_Types\\'.$type);

            $newField->set_collection($this->collection);
            $newField->validate(); // there is no user input here, so we can be sure it will validate.

            $newField = $fields_repository->insert($newField);


            $this->set_mapping([
                $newField->get_id() => $file_fields[$index]
            ]);
        }
    }


    /**
    * Method implemented by the child importer class to return the number of items to be imported
    * @return int
    */
    public function get_total_items_from_source()
    {
        $file = new \SplFileObject( $this->tmp_file, 'r' );
        $file_content = unserialize($file->fread($file->getSize()));

        return $this->total_items = $file_content['items']->found_items;
    }
}