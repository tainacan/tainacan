<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Tainacan_Metadata extends Entity {

    function __construct( $which = 0 ) {

        $this->repository = 'Tainacan_Metadatas';

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

    /**
     * @param $attribute
     * @return
     */
    function getter( $attribute ){
        return $this->get_mapped_property( $attribute );
    }

    /**
     *
     * @param $attribute
     * @param $value
     */
    function setter( $attribute, $value ){
        return $this->set_mapped_property( $attribute, $value );
    }

}