<?php

namespace Tainacan\Entities;

defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

/**
 * Represents the Entity Taxonomy
 */
class Taxonomy extends Entity {
    use \Tainacan\Traits\Entity_Collections_Relation;

    protected static $post_type = 'tainacan-taxonomies';
    /**
     * {@inheritDoc}
     * @see \Tainacan\Entities\Entity::repository
     * @var string
     */
    protected $repository = 'Tainacan_Taxonomies';

	/**
	 * Taxonomy constructor.
	 *
	 * @param int $which
	 */
	function __construct( $which = 0 ) {

        $this->repository = 'Tainacan_Taxonomies';

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
		return 'Hello, my name is '. $this->get_name();
	}

	/**
	 * Register the taxonomy
	 *
	 * @return bool
	 */
	function register_taxonomy() {
        $labels = array(
            'name'              => $this->get_name(),
            'singular_name'     => $this->get_name(),
            'search_items'      => __( sprintf('Search terms in %s', $this->get_name()), 'tainacan' ),
            'all_items'         => __( sprintf('All terms in %s', $this->get_name()), 'tainacan' ),
            'parent_item'       => __( 'Parent term', 'tainacan' ),
            'parent_item_colon' => __( 'Parent term:', 'tainacan' ),
            'edit_item'         => __( 'Edit term', 'tainacan' ),
            'update_item'       => __( 'Update term', 'tainacan' ),
            'add_new_item'      => __( 'Add New term', 'tainacan' ),
            'new_item_name'     => __( 'New Genre term', 'tainacan' ),
            'menu_name'         => $this->get_name(),
        );

        $args = array(
            'hierarchical'      => true,
            'labels'            => $labels,
            'show_ui'           => tnc_enable_dev_wp_interface(),
            'show_admin_column' => tnc_enable_dev_wp_interface(),
            'rewrite'           => [
                'slug' => $this->get_slug()
            ],
        );
        
        
        $tax_cpts = [];
        if (is_array($this->fetch())){
            foreach ($this->fetch() as $tax_col){
                $tax_cpts[] = $tax_col->get_db_identifier();
            }
        }
        
        if (taxonomy_exists($this->get_db_identifier())){
            unregister_taxonomy($this->get_db_identifier());
        }
        
        register_taxonomy( 
            $this->get_db_identifier(), 
            $tax_cpts, 
            $args 
        );
        
        return true;
    }

    // Getters

	/**
	 * Return the unique identifier
	 *
	 * @return integer
	 */
	function get_id() {
        return $this->get_mapped_property('id');
    }

	/**
	 * Return the name
	 *
	 * @return string
	 */
	function get_name() {
        return $this->get_mapped_property('name');
    }

	/**
	 * Return the parent id
	 *
	 * @return integer
	 */
	function get_parent() {
        return $this->get_mapped_property('parent');
    }

	/**
	 * Return the description
	 *
	 * @return string
	 */
	function get_description() {
        return $this->get_mapped_property('description');
    }

	/**
	 * Return true if allow insert or false if not allow insert
	 *
	 * @return boolean
	 */
	function get_allow_insert() {
        return $this->get_mapped_property('allow_insert');
    }

	/**
	 * Return the slug
	 *
	 * @return string
	 */
	function get_slug() {
        return $this->get_mapped_property('slug');
    }
    
    // special Getters

	/**
	 * Return the DB ID
	 *
	 * @return bool|string
	 */
	function get_db_identifier() {
        return $this->get_id() ? 'tnc_tax_' . $this->get_id() : false;
    }

    // Setters

	/**
	 * Define the name of taxonomy
	 *
	 * @param [string] $value
	 */
	function set_name($value) {
        $this->set_mapped_property('name', $value);
    }

	/**
	 * Define the parent ID
	 *
	 * @param [integer] $value
	 */
	function set_parent($value) {
        $this->set_mapped_property('parent', $value);
    }

	/**
	 * Define the slug
	 *
	 * @param [string] $value
	 */
	function set_slug($value) {
        $this->set_mapped_property('slug', $value);
    }

	/**
	 * Define the description
	 *
	 * @param [string] $value
	 */
	function set_description($value) {
        $this->set_mapped_property('description', $value);
    }

	/**
	 * Define if allow insert or not
	 *
	 * @param [boolean] $value
	 */
	function set_allow_insert($value) {
        $this->set_mapped_property('allow_insert', $value);
    }
}