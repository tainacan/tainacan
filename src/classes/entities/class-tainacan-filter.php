<?php

namespace Tainacan\Entities;
use Tainacan\Entities;

defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

/**
 * Representa a entidade Filter
 * 
 */
class Filter extends \Tainacan\Entity {
    const POST_TYPE = 'tainacan-filters';    

    use \Tainacan\Traits\Entity_Collection_Relation;

    function __construct( $which = 0 ) {

        $this->repository = 'Tainacan_Filters';

        if ( is_numeric( $which ) && $which > 0) {
            $post = get_post( $which );
            if ( $post instanceof \WP_Post) {
                $this->WP_Post = get_post( $which );
            }

        } elseif ( $which instanceof \WP_Post ) {
            $this->WP_Post = $which;
        } else {
            $this->WP_Post = new \StdClass();
        }

    }

    /**
     * Retorna o ID do filtro
     *
     * @return integer
     */
    function get_id() {
        return $this->get_mapped_property('id');
    }

    /**
     * Retorna o nome do filtro
     *
     * @return string
     */
    function get_name() {
        return $this->get_mapped_property('name');
    }

    /**
     * Retorna a forma de ordeção do filtro
     *
     * @return string
     */
    function get_order() {
        return $this->get_mapped_property('order');
    }

    /**
     * Retorna a cor do fitro
     *
     * @return string
     */
    function get_color() {
        return $this->get_mapped_property('color');
    }

    /**
     * Retorna o metadado
     *
     * @return Entities\Metadata
     */
    function get_metadata() {
        $id = $this->get_mapped_property('metadata');
        return new Entities\Metadata( $id );
    }

    /**
     * Retorna o objeto filtro
     *
     * @return Filter
     */
    function get_filter_type_object(){
    	return unserialize( base64_decode( $this->get_mapped_property('filter_type_object') ) );
    }

    /**
     * Retorna o filtro
     *
     * @return string
     */
    function get_filter_type(){
        return $this->get_mapped_property('filter_type');
    }

    /**
     * Atribui nome ao filtro
     *
     * @param [string] $value
     * @return void
     */
    function set_name($value) {
        $this->set_mapped_property('name', $value);
    }

    /**
     * Atribui tipo de ordenação do filtro
     *
     * @param [string] $value
     * @return void
     */
    function set_order($value) {
        $this->set_mapped_property('order', $value);
    }

    /**
     * Atribui descrição ao filtro
     *
     * @param [string] $value
     * @return void
     */
    function set_description($value) {
        $this->set_mapped_property('description', $value);
    }

    /**
     * Atribui cor ao filtro
     *
     * @param [string] $value
     * @return void
     */
    function set_color( $value ) {
        $this->set_mapped_property('parent', $value);
    }

    /**
     * Atribui metadado ao filtro
     * 
     * @param \Entities\Metadata
     * @return void
     */
    function set_metadata( $value ){
    	$id = ( $value instanceof Entities\Metadata ) ? $value->get_id() : $value;

        $this->set_mapped_property('metadata', $id);
    }

    /**
     * Atribui o próprio objeto do filter type de forma serializada
     *
     * @param \Tainacan\Filter_Types\Filter_Type $value
     * @return void
     */
    function set_filter_type_object( \Tainacan\Filter_Types\Filter_Type $value ){
        // TODO: validate primitive type with filter
        //if filter matches the metadata type
        //if( in_array( $type->get_primitive_type(), $value->get_supported_types() ) ){
        $this->set_filter_type( get_class( $value ) );
        $this->set_mapped_property('filter_type_object', base64_encode( serialize($value) ) );
        //}
    }

    /**
     * Atribui o filter type.
     * Este metodo é privado, porque é é utilizado apenas neste contexto pelo metodo set_filter_type_object
     *
     * @param string
     *
     */
    private function set_filter_type($value){
        $this->set_mapped_property('filter_type', $value );
    }
}