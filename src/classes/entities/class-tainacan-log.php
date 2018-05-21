<?php

namespace Tainacan\Entities;

defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

/**
 * Represents entity Log
 */
class Log extends Entity {

	protected
		$title,
		$order,
		$parent,
		$description,
		$blog_id,
		$user_id,
		$log_date,
		$user_name,
		$collection_id;

	static $post_type = 'tainacan-log';
	/**
	 * {@inheritDoc}
	 * @see \Tainacan\Entities\Entity::repository
	 * @var string
	 */
	protected $repository = 'Logs';

	public function __construct( $which = 0 ) {
		parent::__construct( $which );

		if ( is_int( $which ) && $which == 0 ) {
			$this->set_user_id();
			$this->set_blog_id();
		}
	}

	public function __toString() {
		return 'Hello, my title is ' . $this->get_title();
	}

	/**
	 * @return array
	 */
	public function _toArray() {
		$array_log = parent::_toArray();

		$array_log['user_name']     = $this->get_user_name();

		return $array_log;
	}


	/**
	 * @param $collection_id
	 */
	function set_collection_id($collection_id){
		$this->set_mapped_property('collection_id', $collection_id);
	}

	/**
	 * @return mixed|null
	 */
	function get_collection_id(){
		return $this->get_mapped_property('collection_id');
	}

	/**
	 * Return the Log title
	 *
	 * @return string
	 */
	function get_title() {
		return $this->get_mapped_property( 'title' );
	}

	/**
	 * @return string
	 */
	function get_user_name() {
		return get_the_author_meta( 'display_name', $this->get_user_id() );
	}

	/**
	 * Return the log date
	 *
	 * @return mixed|null
	 */
	function get_log_date() {
		return $this->get_mapped_property( 'log_date' );
	}

	/**
	 * Return the log order type
	 *
	 * @return string
	 */
	function get_order() {
		return $this->get_mapped_property( 'order' );
	}

	/**
	 * Retun the parent ID
	 *
	 * @return integer
	 */
	function get_parent() {
		return $this->get_mapped_property( 'parent' );
	}

	/**
	 * Return the Log description
	 *
	 * @return string
	 */
	function get_description() {
		return $this->get_mapped_property( 'description' );
	}

	/**
	 * Return the ID of blog
	 *
	 * @return integer
	 */
	function get_blog_id() {
		return $this->get_mapped_property( 'blog_id' );
	}

	/**
	 * Return User Id of who make the action
	 *
	 * @return int User Id of logged action
	 */
	function get_user_id() {
		return $this->get_mapped_property( 'user_id' );
	}

	/**
	 * Get value of log entry
	 *
	 * @param mixed $value
	 *
	 * @return void
	 */
	public function get_value() {
		return maybe_unserialize( base64_decode( $this->get_mapped_property( 'value' ) ) );
	}

//	/**
//	 * Get old value of log entry object
//	 *
//	 * @param mixed $value
//	 *
//	 * @return void
//	 */
//	public function get_old_value() {
//		return maybe_unserialize( base64_decode( $this->get_mapped_property( 'old_value' ) ) );
//	}

	/**
	 * Set log tittle
	 *
	 * @param string $value
	 *
	 * @return void
	 */
	function set_title( $value ) {
		$this->set_mapped_property( 'title', $value );
	}

	/**
	 * Define the order type
	 *
	 * @param [string] $value
	 *
	 * @return void
	 */
	function set_order( $value ) {
		$this->set_mapped_property( 'order', $value );
	}

	/**
	 * Define the parent ID
	 *
	 * @param [integer] $value
	 *
	 * @return void
	 */
	function set_parent( $value ) {
		$this->set_mapped_property( 'parent', $value );
	}

	/**
	 * Define the Log description
	 *
	 * @param [string] $value
	 *
	 * @return void
	 */
	function set_description( $value ) {
		$this->set_mapped_property( 'description', $value );
	}

	/**
	 * Define the user ID of log entry
	 *
	 * @param [integer] $value
	 *
	 * @return void
	 */
	protected function set_user_id( $value = 0 ) {
		if ( 0 == $value ) {
			$value = get_current_user_id();
		}
		$this->set_mapped_property( 'user_id', $value );
	}

	/**
	 * Define the blog ID of log entry
	 *
	 * @param [integer] $value
	 *
	 * @return void
	 */
	protected function set_blog_id( $value = 0 ) {
		if ( 0 == $value ) {
			$value = get_current_blog_id();
		}
		$this->set_mapped_property( 'blog_id', $value );
	}

	/**
	 * Define the value of log entry
	 *
	 * @param [mixed] $value
	 *
	 * @return void
	 */
	protected function set_value( $value = null ) {
		$this->set_mapped_property( 'value', base64_encode( maybe_serialize( $value ) ) );
	}

//	/**
//	 * Set old value of log entry
//	 *
//	 * @param [mixed] $value
//	 *
//	 * @return void
//	 */
//	protected function set_old_value( $value = null ) {
//		$this->set_mapped_property( 'old_value', base64_encode( maybe_serialize( $value ) ) );
//	}

	/**
	 * @param $diffs
	 */
	public function set_log_diffs($diffs){
		$this->set_mapped_property( 'log_diffs', $diffs );
	}

	/**
	 * @return mixed|null
	 */
	public function get_log_diffs(){
		return $this->get_mapped_property('log_diffs');
	}

	/**
	 *
	 * @param boolean|string $msn
	 * @param string $desc
	 * @param mixed $new_value
	 * @param array $diffs
	 * @param string $status 'publish', 'private' or 'pending'
	 *
	 * @return \Tainacan\Entities\Log
	 * @throws \Exception
	 */
	public static function create( $msn = false, $desc = '', $new_value = null, $diffs = [], $status = 'publish' ) {

		$log = new Log();
		$log->set_title( $msn );
		$log->set_description( $desc );
		$log->set_status( $status );
		$log->set_log_diffs( $diffs );

		if(array_search( 'Tainacan\Traits\Entity_Collection_Relation', class_uses($new_value))) {
			$log->set_collection_id( $new_value->get_collection_id() );
		} elseif($new_value instanceof Collection){
			$log->set_collection_id( $new_value->get_id());
		}

		if ( ! is_null( $new_value ) ) {
			$log->set_value( $new_value );
		} elseif ( $msn === false ) {
			throw new \Exception( 'msn or new_value is need to log' );
		}

		$Tainacan_Logs = \Tainacan\Repositories\Logs::get_instance();

		if ( $log->validate() ) {
			return $Tainacan_Logs->insert( $log );
		} else {
			throw new \Exception( 'Invalid log' );
		}

	}

	/**
	 * {@inheritDoc}
	 * @see \Tainacan\Repositories\Logs::approve
	 */
	public function approve() {
		$repository = $this->get_repository();

		return $repository->approve( $this );
	}
}