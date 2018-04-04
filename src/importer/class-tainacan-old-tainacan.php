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
    public function __construct() {
        parent::__construct();

        $this->remove_import_method('file');
        $this->add_import_method('url');
    }

    /**
     * get the fields of file/url to allow mapping
     * should return an array
     *
     * @return array $fields_source the fields from the source
     */
    public function get_fields()
    {
        // TODO: Implement get_fields() method.
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