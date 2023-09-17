<?php

namespace LegalTrunkCore\UsersWP;


class Connect {
	
	public function __construct()
	{
		// check if the UserWP class exist
		if ( class_exists( 'UsersWP' ) ) {
			$this->users_wp_init();
		}
	}
	
	/**
	 * Initialize all hooks for
	 */
	public function users_wp_init() {
		Authentication::get_instance();
	}
	
	
	public function header_element($hook) {
	
	}
	
	/**
	 * @return Connect
	 */
	public static function get_instance()
	{
		static $instance = null;
		
		if (is_null($instance)) {
			$instance = new self();
		}
		
		return $instance;
	}
}