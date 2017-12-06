<?php

namespace Tainacan\Entities;

defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

/**
 * Represents the Entity Term
*/
class Term extends Entity {
	protected static $post_type = false;
	/**
	 * {@inheritDoc}
	 * @see \Tainacan\Entities\Entity::repository
	 * @var string
	 */
	protected $repository = 'Tainacan_Terms';

	/**
	 * Term constructor.
	 *
	 * @param int $which
	 * @param string $taxonomy
	 */
	function __construct($which = 0, $taxonomy = '' ) {

        $this->set_taxonomy( $taxonomy );

        if ( is_numeric( $which ) && $which > 0) {
            $post = get_term_by('id', $which, $taxonomy);
            
            if ( $post instanceof \WP_Term) {
                $this->WP_Term = get_term_by('id', $which, $taxonomy);
            }

        } elseif ( $which instanceof \WP_Term ) {
            $this->WP_Term = $which;
        } else {
            $this->WP_Term = new \StdClass();
        }
    }

	public function  __toString(){
		return 'Hello, my name is '. $this->get_name();
	}

    // Getters

	/**
	 * Return the unique identifier
	 *
	 * @return integer
	 */
	function get_id() {
        return $this->get_mapped_property('term_id');
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
	 * Return the parent ID
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
	 * Return the user ID
	 *
	 * @return integer
	 */
	function get_user() {
        return $this->get_mapped_property('user');
    }

	/**
	 * Return the taxonomy
	 *
	 * @return integer
	 */
	function get_taxonomy() {
        return $this->get_mapped_property('taxonomy');
    }

    // Setters

	/**
	 * Define the name
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
	 * Define the description
	 *
	 * @param [string] $value
	 */
	function set_description($value) {
        $this->set_mapped_property('description', $value);
    }

	/**
	 * Define the user associated
	 *
	 * @param [integer] $value
	 */
	function set_user($value) {
        $this->set_mapped_property('user', $value);
    }

	/**
	 * Define the taxonomy associated
	 *
	 * @param [integer] $value
	 */
	function set_taxonomy($value) {
        $this->set_mapped_property('taxonomy', $value);
    }
}
