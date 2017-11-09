<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Tainacan_Taxonomy extends Entity {

    function __construct( $which = 0 ) {

        $this->repository = 'Tainacan_Taxonomies';

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
}