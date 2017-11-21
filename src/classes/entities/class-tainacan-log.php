<?php

namespace Tainacan\Entities;

defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

/**
 * Representa a entidade Log
 */
class Log extends \Tainacan\Entity {

    const POST_TYPE = 'tainacan-logs';    
    
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
}