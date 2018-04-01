<?php

namespace Tainacan\Repositories;
use Tainacan\Entities;

defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

use \Respect\Validation\Validator as v;
/**
* Class Tainacan_Terms
*/
class Terms extends Repository {
	
	public $entities_type = '\Tainacan\Entities\Term';

    private static $instance = null;

    public static function getInstance()
    {
        if(!isset(self::$instance))
        {
            self::$instance = new self();
        }

        return self::$instance;
    }

    protected function __construct()
    {
        parent::__construct();
    }

    public function get_map() {
    	return apply_filters('tainacan-get-map-'.$this->get_name(), [
            'term_id'     => [
                'map'        => 'term_id',
                'title'      => __('ID', 'tainacan'),
                'type'       => 'integer',
                'description'=> __('Unique identifier', 'tainacan'),
                //'validation' => ''
            ],
            'name'        => [
                'map'        => 'name',
                'title'      => __('Name', 'tainacan'),
                'type'       => 'string',
                'description'=> __('Name of the term', 'tainacan'),
                'on_error'   => __('The name is empty', 'tainacan'),
                'validation' => v::stringType()->notEmpty(),
            ],
            'parent'      => [
                'map'        => 'parent',
                'title'      => __('Parent', 'tainacan'),
                'type'       => 'integer',
                'description'=> __('The parent of the term', 'tainacan'),
				'default'	 => 0,
                'validation' => ''
            ],
            'description' => [
                'map'        => 'description',
                'title'      => __('Description', 'tainacan'),
                'type'       => 'string',
                'description'=> __('The term description', 'tainacan'),
            	'default'	 => '',
                'validation' => ''
            ],
            'taxonomy'    => [
                'map'        => 'taxonomy',
                'title'      => __('Taxonomy', 'tainacan'),
                'type'       => 'string',
                'description'=> __('The term taxonomy', 'tainacan'),
                'on_error'   => __('The taxonomy is empty', 'tainacan'),
                'validation' => v::stringType()->notEmpty(),
            ],
            'user'        => [
                'map'        => 'termmeta',
                'title'      => __('User', 'tainacan'),
                'type'       => 'integer',
                'description'=> __('The term creator', 'tainacan'),
                'on_error'   => __('The user is empty or invalid', 'tainacan'),
				'default'    => get_current_user_id(),
                'validation' => v::numeric(),
            ],
		    'hide_empty'  => [
		    	'map'        => 'hide_empty',
			    'type'       => 'bool'
		    ]
        ]);
    }
    
    public function get_default_properties($map) {
    	$defaults = parent::get_default_properties($map);
    	//its uses the term_id and not id
    	unset($defaults['id']);
    	return $defaults;
    }

	/**
	 * @param Entities\Entity $term
	 *
	 * @return Entities\Entity|Entities\Term
	 */
	public function insert($term){
        // First iterate through the native post properties
        $map = $this->get_map();
        foreach ($map as $prop => $mapped) {
            if ($mapped['map'] != 'termmeta') {
                $term->WP_Term->{$mapped['map']} = $term->get_mapped_property($prop);
            }
        }

        // save post and get its ID
	    if(isset($term->WP_Term->term_id)){

        	$args = [];
		    foreach ($map as $prop => $mapped) {
			    if ($mapped['map'] != 'termmeta') {
			    	$get_ = 'get_'. $prop;

				    if( !empty($term->WP_Term->{$mapped['map']}) ){
				    	$args[$mapped['map']] =  $term->$get_();
					}

			    }
		    }

        	$term_saved = wp_update_term( $term->get_id(), $term->get_taxonomy(), $args);
	    } else {
		    $term_saved = wp_insert_term( $term->get_name(), $term->get_taxonomy(), [
			    'parent'      => $term->get_parent(),
			    'description' => $term->get_description(),
		    ] );
	    }

        // Now run through properties stored as postmeta
        foreach ($map as $prop => $mapped) {
            if ($mapped['map'] == 'termmeta') {
                update_term_meta($term_saved['term_id'], $prop, wp_slash( $term->get_mapped_property($prop) ));
            }
        }
        
        do_action('tainacan-insert', $term);
        do_action('tainacan-insert-Term', $term);

		return new Entities\Term($term_saved['term_id'], $term->get_taxonomy());
    }

    /**
     * fetch terms based on ID or get terms args
     *
     * Terms are stored as WordPress regular terms. Check (@see https://developer.wordpress.org/reference/functions/get_terms/) get_terms() docs
     * to learn all args accepted in the $args parameter
     *
     * The second paramater specifies from which taxonomies terms should be fetched.
     * You can pass the Taxonomy ID or object, or an Array of IDs or taxonomies objects
     *
     * @param array $args WP_Query args || int $args the term id
     * @param array $taxonomies Array Entities\Taxonomy || Array int terms IDs || int collection id || Entities\Taxonomy taxonomy object
     * @return array of Entities\Term objects || Entities\Term
     */
    public function fetch( $args = [], $taxonomies = []){

        $Tainacan_Taxonomies = \Tainacan\Repositories\Taxonomies::getInstance();

        if ( $taxonomies instanceof Entities\Taxonomy ) {
            $cpt = $taxonomies->get_db_identifier();
        } elseif (is_array( $taxonomies )) {
            $cpt = [];

            foreach ($taxonomies as $taxonomy) {
                if (is_numeric($taxonomy)){
                    $taxonomy = $Tainacan_Taxonomies->fetch( $taxonomy );
                }
                if ($taxonomy instanceof Entities\Taxonomy){
                    $cpt[] = $taxonomy->get_db_identifier();
                }
            }

        } else {
            return [];
        }

        if(is_array( $args ) && !empty( $cpt ) ){ // if an array of arguments is
            $terms = get_terms( $cpt, $args );
            $return = [];

            foreach ($terms as $term) {
                $tainacan_term = new Entities\Term( $term );
                $tainacan_term->set_user( get_term_meta($tainacan_term->get_id() , 'user', true ) );
                $return[] = $tainacan_term;
            }
            return $return;
        } elseif( is_numeric($args) && !empty($cpt) && !is_array( $cpt ) ){ // if an id is passed taxonomy cannot be an array
            $wp_term = get_term_by('id', $args, $cpt);
            $tainacan_term = new Entities\Term( $wp_term );
            $tainacan_term->set_user( get_term_meta($tainacan_term->get_id() , 'user', true ) );

            return $tainacan_term;
        } else{
            return [];
        }
    }

    public function update($object, $args = null){
    	return $this->insert($object);
    }

    public function delete($args){
    	return wp_delete_term($args[0], $args[1]);
    }
    
    public function register_post_type() { }
}