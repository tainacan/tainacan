<?php

namespace Tainacan\Entities;

defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

/**
 * Represents the Metadata Entity
 */
class Metadata extends Entity {
	
    // Collection getter and setter declared here
    use \Tainacan\Traits\Entity_Collection_Relation;

	protected static $post_type = 'tainacan-metadata';
	
    function __construct( $which = 0 ) {

        $this->repository = 'Tainacan_Metadatas';

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

	public function  __toString(){
		return 'Hello, I\'m the Metadata Entity';
	}

    /**
     * Retorna o ID do metadado
     *
     * @return integer
     */
    function get_id() {
        return $this->get_mapped_property('id');
    }

    /**
     * Retorna o nome do metadado
     *
     * @return string
     */
    function get_name() {
        return $this->get_mapped_property('name');
    }

    /**
     * Retorna a forma de ordenação do metadado
     *
     * @return string
     */
    function get_order() {
        return $this->get_mapped_property('order');
    }

    /**
     * Retorna o parent do metadado
     *
     * @return string
     */
    function get_parent() {
        return $this->get_mapped_property('parent');
    }

    /**
     * Retorna a descrição do metado
     *
     * @return string
     */
    function get_description() {
        return $this->get_mapped_property('description');
    }

    /**
     * Retorna se é metadado obrigatório
     *
     * @return boolean
     */
    function get_required(){
        return $this->get_mapped_property('required');
    }
    
    /**
     * Retorna se é metado multiplo
     *
     * @return boolean
     */
    function get_multiple(){
        return $this->get_mapped_property('multiple');
    }
    
    /**
     * Retorna a cardinalidade
     *
     * @return string
     */
    function get_cardinality(){
        return $this->get_mapped_property('cardinality');
    }
    
    /**
     * Retorna se é metadado chave
     *
     * @return boolean
     */
    function get_collection_key(){
        return $this->get_mapped_property('collection_key');
    }

    /**
     * Retorna a máscara
     *
     * @return string
     */
    function get_mask(){
        return $this->get_mapped_property('mask');
    }

    /**
     * Retorna o nível de privacidade
     *
     * @return string
     */
    function get_privacy(){
        return $this->get_mapped_property('privacy');
    }

    /**
     * Retorna valor padrão do metadado
     *
     * @return string || integer
     */
    function get_default_value(){
        return $this->get_mapped_property('default_value');
    }

    /**
     * Retorna o objeto Metadado
     *
     * @return array || object
     */
    function get_field_type_object(){
    	return unserialize(base64_decode( $this->get_mapped_property('field_type_object') ) );
    }

    /**
     * Retorna o objeto field type
     *
     * @return array || object
     */
    function get_field_type(){
    	return base64_decode($this->get_mapped_property('field_type'));
    }

    /**
     * Atribui nome
     *
     * @param [string] $value
     * @return void
     */
    function set_name($value) {
        $this->set_mapped_property('name', $value);
    }

    /**
     * Atribui o tipo de ordenação
     *
     * @param [string] $value
     * @return void
     */
    function set_order($value) {
        $this->set_mapped_property('order', $value);
    }

    /**
     * Atribui ID do parent
     *
     * @param [integer] $value
     * @return void
     */
    function set_parent($value) {
        $this->set_mapped_property('parent', $value);
    }

    /**
     * Atribui descrição
     *
     * @param [string] $value
     * @return void
     */
    function set_description($value) {
        $this->set_mapped_property('description', $value);
    }

    /**
     * Define se é obrigatório
     *
     * @param [boolean] $value
     * @return void
     */
    function set_required( $value ){
        $this->set_mapped_property('required', $value);
    }
    
    /**
     * Define se é multiplo
     *
     * @param [boolean] $value
     * @return void
     */
    function set_multiple( $value ){
        $this->set_mapped_property('multiple', $value);
    }
    
    /**
     * Define a cardinalidade
     *
     * @param [string] $value
     * @return void
     */
    function set_cardinality( $value ){
        $this->set_mapped_property('cardinality', $value);
    }
    
    /**
     * Define se é chave
     *
     * @param [string] $value
     * @return void
     */
    function set_collection_key( $value ){
        $this->set_mapped_property('collection_key', $value);
    }

    /**
     * Atribui máscara
     *
     * @param [string] $value
     * @return void
     */
    function set_mask( $value ){
        $this->set_mapped_property('mask', $value);
    }

    /**
     * Define o nível de privacidade
     *
     * @param [string] $value
     * @return void
     */
    function set_privacy( $value ){
        $this->set_mapped_property('privacy', $value);
    }

    /**
     * Define o valor padrão
     *
     * @param [string || integer] $value
     * @return void
     */
    function set_default_value( $value ){
        $this->set_mapped_property('default_property', $value);
    }

    function set_field_type_object(\Tainacan\Field_Types\Field_Type $value){
        $this->set_field_type( get_class( $value )  );
        $this->set_mapped_property('field_type_object', base64_encode( serialize($value) ) ); // Encode to avoid backslaches removal
    }

    /**
     * Este metodo é privado, porque é utilizado apenas neste contexto pelo @method set_field_type_object()
     *
     * @param $value
     */
    private function set_field_type($value){
    	$this->set_mapped_property('field_type',   base64_encode($value) ) ; // Encode to avoid backslaches removal
    }

    // helpers
    function is_multiple() {
        return $this->get_multiple() === 'yes';
    }
    
    function is_collection_key() {
        return $this->get_collection_key() === 'yes';
    }
    
    function is_required() {
        return $this->get_required() === 'yes';
    }
}