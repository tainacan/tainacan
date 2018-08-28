<?php

namespace Tainacan;


class AdminHooks {


    public $registered_plugins = [];

    private static $instance = null;

	public static function get_instance() {
		if ( ! isset( self::$instance ) ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	private function __construct() {

        do_action('tainacan-register-admin-hooks');
        do_action('tainacan-register-meta');

    }
    
    /**
     * 
     * @param string $context The context to add the hook to (collection, metadatum, item, taxonomy, term or filter)
     * @param string $position The position inside the page to hook. (begin, end, begin-left, begin-right, end-left, end-right)
     * @param callable $callback The callback that will output the form HTML
     */
    public function register( $context, $position, $callback ) {
        $result = call_user_func($callback);
        if (is_string($result)){
            $this->registered_hooks[$context][$position][] = $result;
            return true;
        }
        return false;
    }

    /**
     * 
     * @param string $context The context to add the metahook to (collection, metadatum, item, taxonomy, term or filter)
     * @param string $meta_key The unique name of a meta where information about the entity will be saved using add_post_meta()
     */
    public function register_meta( $context, $meta_key ) {
        if (is_string($meta_key)){
            $this->registered_meta[$context][] = $meta_key;
            return true;
        }
        return false;
    }


}