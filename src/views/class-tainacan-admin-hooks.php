<?php

namespace Tainacan;


class Admin_Hooks {


    private $registered_hooks = [];

    private static $instance = null;

	public static function get_instance() {
		if ( ! isset( self::$instance ) ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	private function __construct() {
        add_action('init', [$this, 'init']);
    }

    function init() {
        do_action('tainacan-register-admin-hooks');
    }

    public function get_available_positions() {
        return apply_filters('tainacan-admin-hooks-positions', ['begin-left', 'begin-right', 'end-left', 'end-right']);
    }

    public function get_available_contexts() {
        return apply_filters('tainacan-admin-hooks-contexts', ['collection', 'metadatum', 'item', 'taxonomy', 'term', 'filter']);
    }
    
    public function get_registered_hooks() {
        return $this->registered_hooks;
    }

    /**
     * 
     * @param string $context The context to add the hook to (collection, metadatum, item, taxonomy, term or filter)
     * @param string $position The position inside the page to hook. (begin, end, begin-left, begin-right, end-left, end-right)
     * @param callable $callback The callback that will output the form HTML
     */
    public function register( $context, $callback, $position = 'end-left' ) {
        
        $contexts = $this->get_available_contexts();
        $positions = $this->get_available_positions();
        
        if ( !in_array($context, $contexts) || !in_array($position, $positions) ) {
            return false;
        }
                
        $result = call_user_func($callback);
        if (is_string($result)){
            $this->registered_hooks[$context][$position][] = $result;
            return true;
        }
        return false;
    }
   

}

