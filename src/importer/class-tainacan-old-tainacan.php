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

    /**
     * get the fields of file/url to allow mapping
     * should return an array
     *
     * @return array $fields_source the fields from the source
     */
    public function get_fields()
    {
        $file = new \SplFileObject( $this->tmp_file, 'r' );
        $json = json_decode($file->fread($file->getSize()), true);

        $item = $json['items'][0]['item'];
        return array_keys($item);
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
        $json = json_decode($file->fread($file->getSize()), true);

        $values = $json['items'][$index]['item'];

        if( count( $headers ) !== count( $values ) ){
            return false;
        }

        foreach ($headers as $header) {
            $processedItem[ $header ] = $values[ $header ];
        }

        return $processedItem;
    }

    function create_fields_and_mapping() {

        $file = new \SplFileObject( $this->tmp_file, 'r' );
        $json = json_decode($file->fread($file->getSize()), true);
        $item = $json['items'][0]['item'];
        $fields_repository = \Tainacan\Repositories\Fields::get_instance();

        $avoid = [
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
        ];

        foreach($item as $field_name => $value)
        {
            if(!in_array($field_name, $avoid))
            {
                $newField = new \Tainacan\Entities\Field();

                $newField->set_name($field_name);
                $newField->set_field_type('Tainacan\Field_Types\Text');

                $newField->set_collection($this->collection);
                $newField->validate(); // there is no user input here, so we can be sure it will validate.

                $newField = $fields_repository->insert($newField);

                $source_fields = $this->get_fields();

                $source_id = array_search($field_name, $source_fields);
                $this->set_mapping([
                    $newField->get_id() => $source_fields[$source_id]
                ]);
            }
        }
    }


    /**
    * Method implemented by the child importer class to return the number of items to be imported
    * @return int
    */
    public function get_total_items_from_source()
    {
        $file = new \SplFileObject( $this->tmp_file, 'r' );
        $json = json_decode($file->fread($file->getSize()), true);

        return $this->total_items = $json['found_items'];
    }
}