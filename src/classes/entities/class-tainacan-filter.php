<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Tainacan_Filter extends Entity  {

    use EntityCollectionRelation;

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

    function get_widget( $output = 'object' ){
        if( $output === 'object'){
            return unserialize( $this->get_mapped_property('option') );
        } else{
            return $this->get_mapped_property('widget');
        }
    }

    //Setters
    function set_widget($value){
        if( is_object( $value ) && is_subclass_of( $value, 'Tainacan_Filter_Type' ) ){
            $this->set_option( $value );
           
            return $this->set_mapped_property('widget', get_class( $value ) ) ;
        }
        return null;
    }

    function set_option($value){
        return $this->set_mapped_property('option',  serialize($value) ) ;
    }
}