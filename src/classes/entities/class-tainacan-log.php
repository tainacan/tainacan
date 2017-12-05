<?php

namespace Tainacan\Entities;

defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

/**
 * Representa a entidade Log
 */
class Log extends Entity {
	protected static $post_type = 'tainacan-logs';
    
    function __construct($which = 0) {
        
        $this->repository = 'Tainacan_Logs';
        
        if (is_numeric($which) && $which > 0) {
            $post = get_post($which);
            if ($post instanceof \WP_Post) {
                $this->WP_Post = get_post($which);
            }
            
        } elseif ($which instanceof \WP_Post) {
            $this->WP_Post = $which;
        } else {
            $this->WP_Post = new \StdClass();
        }
        
        if( is_int($which) && $which == 0) {
        	$this->set_user_id();
        	$this->set_blog_id();
        }
    }

	public function  __toString(){
		return 'Hello, I\'m the Log Entity';
	}

    /**
     * Retorna ID do Log
     *
     * @return integer
     */
    function get_id() {
        return $this->get_mapped_property('id');
    }

    /**
     * Retorna titulo do Log
     *
     * @return string
     */
    function get_title() {
        return $this->get_mapped_property('title');
    }

    /**
     * Retorna tipo de ordenação do Log
     *
     * @return string
     */
    function get_order() {
        return $this->get_mapped_property('order');
    }

    /**
     * Retorna ID do parent do Log
     *
     * @return integer
     */
    function get_parent() {
        return $this->get_mapped_property('parent');
    }

    /**
     * Retorna descrição do Log
     *
     * @return string
     */
    function get_description() {
        return $this->get_mapped_property('description');
    }
    
    /**
     * Retorn the ID of blog
     *
     * @return integer
     */
    function get_blog_id() {
    	return $this->get_mapped_property('blog_id');
    }
    
    /**
     * Return User Id of who make the action
     * 
     * @return int User Id of logged action
     */
    function get_user_id() {
    	return $this->get_mapped_property('user_id');
    }
    
    /**
     * get value of log entry
     *
     * @param mixed $value
     * @return void
     */
    public function get_value($value = null) {
    	return maybe_unserialize( base64_decode($this->get_mapped_property('value')));
    }
    
    /**
     * get old value of log entry object
     *
     * @param mixed $value
     * @return void
     */
    public function get_old_value($value = null) {
    	return maybe_unserialize( base64_decode($this->get_mapped_property('old_value')));
    }
        
    /**
     * Set log tittle
     *
     * @param string $value
     * @return void
     */
    function set_title($value) {
        $this->set_mapped_property('title', $value);
    }

    /**
     * Atribui tipo de ordenação
     *
     * @param [string] $value
     * @return void
     */
    function set_order($value) {
        $this->set_mapped_property('order', $value);
    }

    /**
     * Atribui ID do parent ao log
     *
     * @param [integer] $value
     * @return void
     */
    function set_parent($value) {
        $this->set_mapped_property('parent', $value);
    }

    /**
     * Atribui descrição ao log
     *
     * @param [string] $value
     * @return void
     */
    function set_description($value) {
        $this->set_mapped_property('description', $value);
    }
    
    /**
     * user_id of log entry
     *
     * @param integer $value
     * @return void
     */
    protected function set_user_id($value = 0) {
    	if(0 == $value) $value = get_current_user_id();
    	$this->set_mapped_property('user_id', $value);
    }
    
    /**
     * blog_id of log entry
     *
     * @param integer $value
     * @return void
     */
    protected function set_blog_id($value = 0) {
    	if(0 == $value) $value = get_current_blog_id();
    	$this->set_mapped_property('blog_id', $value);
    }
    
    /**
     * value of log entry
     *
     * @param mixed $value
     * @return void
     */
    protected function set_value($value = null) {
    	$this->set_mapped_property('value', base64_encode( maybe_serialize($value)));
    }
    
    /**
     * set old value of log entry
     *
     * @param mixed $value
     * @return void
     */
    protected function set_old_value($value = null) {
    	$this->set_mapped_property('old_value', base64_encode( maybe_serialize($value)));
    }
    
    /**
     * 
     * @param boolean|string $msn
     * @param string $desc
     * @param mixed $new_value
     * @param mixed $old_value
     * @throws \Exception
     * @return \Tainacan\Entities\Log
     */
    public static function create($msn = false, $desc = '', $new_value = null, $old_value = null) {
    	$log = new Log();
    	$log->set_title($msn);
    	$log->set_description($desc);
    	$log->set_status('publish'); //TODO may be private
    	
    	if(!is_null($new_value)) {
	    	$type = gettype($new_value);
	    	
	    	$log->set_value($new_value);
	    	if(!is_null($old_value)) $log->set_old_value($old_value);
    	}
		elseif($msn === false) {
			throw new \Exception('msn or new_value is need to log');
		}

		global $Tainacan_Logs;

		if ($log->validate()) {
            return $Tainacan_Logs->insert($log);
        } else {
            throw new \Exception('Invalid log');
        }
		
    }
}