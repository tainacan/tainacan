<?php

namespace Tainacan\Entities;

defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

/**
 * Represents the Entity Taxonomy
 */
class Taxonomy extends Entity {
    use \Tainacan\Traits\Entity_Collections_Relation;

    protected
        $name,
        $description,
        $allow_insert,
		$hierarchical,
        $slug;

    /**
	 * {@inheritDoc}
     * @see \Tainacan\Entities\Entity::post_type
	 * @var string
	 */
    static $post_type = 'tainacan-taxonomy';

    /**
	 * {@inheritDoc}
     * @see \Tainacan\Entities\Entity::capability_type
	 * @var string
	 */
    protected static $capability_type = ['tainacan-taxonomy', 'tainacan-taxonomies'];

    /**
     * {@inheritDoc}
     * @see \Tainacan\Entities\Entity::repository
     * @var string
     */
    protected $repository = 'Taxonomies';

	/**
	 * Prefix used to create the db_identifier
	 *
	 * @var string
	 */
	static $db_identifier_prefix = 'tnc_tax_';

	public function __toString() {
		return (string) apply_filters("tainacan-taxonomy-to-string", $this->get_name(), $this);
	}

	/**
	 * Register the taxonomy
	 *
	 * @return bool
	 */
	function tainacan_register_taxonomy() {
	    $taxonomy_name = $this->get_name();
	    $labels = array(
            'name'              => $taxonomy_name,
            'singular_name'     => $taxonomy_name,
            'search_items'      => __( sprintf('Search terms in %s', $taxonomy_name), 'tainacan' ),
            'all_items'         => __( sprintf('All terms in %s', $taxonomy_name), 'tainacan' ),
            'parent_item'       => __( 'Parent term', 'tainacan' ),
            'parent_item_colon' => __( 'Parent term:', 'tainacan' ),
            'edit_item'         => __( 'Edit term', 'tainacan' ),
            'update_item'       => __( 'Update term', 'tainacan' ),
            'add_new_item'      => __( 'Add New term', 'tainacan' ),
            'new_item_name'     => __( 'New Genre term', 'tainacan' ),
            'menu_name'         => $taxonomy_name,
        );

		$enabled_post_types = $this->get_enabled_post_types();
        $enabled_post_types = sizeof($enabled_post_types) ? $enabled_post_types : null;
		$show_ui = is_array($enabled_post_types) ? true : false;

        $args = array(
            'hierarchical'      => $this->get_hierarchical() !== 'no',
            'labels'            => $labels,
            'show_ui'           => $show_ui,
            'show_in_rest'      => $show_ui,
            'show_admin_column' => false,
            'rewrite'           => [
                'slug' => $this->get_slug()
			],
			'update_count_callback' => '_update_generic_term_count'
        );

        if (taxonomy_exists($this->get_db_identifier())){
            unregister_taxonomy($this->get_db_identifier());
        }

        register_taxonomy(
            $this->get_db_identifier(),
            $enabled_post_types,
            $args
        );

        return true;
    }

    public function get_capabilities() {

		return (object) [
			// meta
			'edit_post' => "tnc_rep_edit_taxonomy",
			'read_post' => "tnc_rep_read_taxonomy",
			'delete_post' => "tnc_rep_delete_taxonomy",

			// primitive
			'edit_posts' => "tnc_rep_edit_taxonomies",
			'edit_others_posts' => "tnc_rep_edit_others_taxonomies",
			'publish_posts' => "tnc_rep_edit_taxonomies",
			'read_private_posts' => "tnc_rep_read_private_taxonomies",
			'read' => "read",
			'delete_posts' => "tnc_rep_delete_taxonomies",
			'delete_private_posts' => "tnc_rep_delete_taxonomies",
			'delete_published_posts' => "tnc_rep_delete_taxonomies",
			'delete_others_posts' => "tnc_rep_delete_others_taxonomies",
			'edit_private_posts' => "tnc_rep_edit_taxonomies",
			'edit_published_posts' => "tnc_rep_edit_taxonomies",
			'create_posts' => "tnc_rep_edit_taxonomies"
		];

	}

    // Getters

	/**
	 * Return the name
	 *
	 * @return string
	 */
	function get_name() {
        return $this->get_mapped_property('name');
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
	 * Return 'yes' if terms hierarchy is allowd and 'no' otherwise
	 *
	 * @return boolean
	 */
	function get_hierarchical() {
        return $this->get_mapped_property('hierarchical');
    }

	/**
	 * Return the slug
	 *
	 * @return string
	 */
	function get_slug() {
        return $this->get_mapped_property('slug');
    }

    /**
	 * Return the enabled post types
	 *
	 * @return array
	 */
	function get_enabled_post_types() {
        return $this->get_mapped_property('enabled_post_types');
    }

    // special Getters

	/**
	 * Return the DB ID
	 *
	 * @return bool|string
	 */
	function get_db_identifier() {
        return $this->get_id() ? self::$db_identifier_prefix . $this->get_id() : false;
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
	 * @param [String] $value
	 */
	function set_allow_insert($value) {
        $this->set_mapped_property('allow_insert', $value);
    }

	/**
	 * Define if hierarchical is 'yes' or 'no'
	 *
	 * @param [String] $value
	 */
	function set_hierarchical($value) {
        $this->set_mapped_property('hierarchical', $value);
    }

    /**
	 * Sets enabled post types
	 *
	 * @param array $value array of post types slugs
	 */
	function set_enabled_post_types($value) {
		$this->set_mapped_property('enabled_post_types', $value);
	}

	/**
	 * Validate Taxonomy
	 *
	 * @return bool
	 */
	function validate() {
		if ( ! in_array( $this->get_status(), apply_filters( 'tainacan-status-require-validation', [
			'publish',
			'future',
			'private',
			'pending'
		] ) ) ) {
			return true;
		}

		return parent::validate();

	}

	/**
	* Check if a term already exists
	*
	* @param string $term_name The term name
	* @param int|null $parent The ID of the parent term to look for children or null to look for terms in any hierarchical position. Default is null
	* @param bool $return_term whether to return the term object if it exists. default is to false
	*
	* @return bool|WP_Term return boolean indicating if term exists. If $return_term is true and term exists, return WP_Term object
	*/
	function term_exists($term_name, $parent = null, $return_term = false) {
		$repo = $this->get_repository();
		return $repo->term_exists($this, $term_name, $parent, $return_term);
	}

}
