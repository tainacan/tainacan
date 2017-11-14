<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Tainacan_Filter extends Tainacan_Entity  {

    use Tainacan_Entity_Collection_Relation;

    function __construct( $which = 0 ) {

        $this->repository = 'Tainacan_Filters';

        if ( is_numeric( $which ) && $which > 0) {
            $post = get_post( $which );
            if ( $post instanceof WP_Post) {
                $this->WP_Post = get_post( $which );
            }

        } elseif ( $which instanceof WP_Post ) {
            $this->WP_Post = $which;
        } else {
            $this->WP_Post = new StdClass();
        }

    }

    // Getters
    function get_id() {
        return $this->get_mapped_property('ID');
    }

    function get_name() {
        return $this->get_mapped_property('name');
    }

    function get_order() {
        return $this->get_mapped_property('order');
    }


    function get_color() {
        return $this->get_mapped_property('color');
    }

    function get_metadata() {
        $id = $this->get_mapped_property('metadata');
        return new Tainacan_Metadata( $id );
    }

    function get_filter_type_object(){
        return unserialize( $this->get_mapped_property('filter_type_object') );
    }

    function get_filter_type(){
        return $this->get_mapped_property('filter_type');
    }

    // Setters
    function set_name($value) {
        return $this->set_mapped_property('name', $value);
    }

    function set_order($value) {
        return $this->set_mapped_property('order', $value);
    }

    function set_description($value) {
        return $this->set_mapped_property('description', $value);
    }

    function set_color( $value ) {
        return $this->set_mapped_property('parent', $value);
    }

    /**
     * @param Tainacan_Metadata / int $value
     */
    function set_metadata( $value ){
        $id = ( $value instanceof Tainacan_Metadata ) ? $value->get_id() : $value;

        return $this->set_mapped_property('metadata', $id);
    }

    function set_filter_type_object( Tainacan_Filter_Type $value ){
        // TODO: validate primitive type with filter
        //if filter matches the metadata type
        //if( in_array( $type->get_primitive_type(), $value->get_supported_types() ) ){
        $this->set_filter_type( get_class( $value ) );
        return $this->set_mapped_property('filter_type_object',serialize($value)  ) ;
        //}
    }

    /**
     * este metodo eh privado pois eh setado automaticamente pelo metodo set_filter_type_object
     *
     * @param $value
     *
     */
    private function set_filter_type($value){
        return $this->set_mapped_property('filter_type', $value );
    }
}