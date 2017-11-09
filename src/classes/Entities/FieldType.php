<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Class TainacanFieldType
 */
abstract class Tainacan_Field_Type extends Entity {

    abstract function render( $metadata );
}