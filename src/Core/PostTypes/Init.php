<?php

namespace LegalTrunkCore\Core\PostTypes;


//use LegalTrunkCore\Core\PostTypes\Company;

class Init {
	
	/** Constructor
	 *
	 */
	public function __construct() {
		Company::get_instance();
	}
	
	
	/**
	 * @return Init
	 */
	public static function get_instance() {
		static $instance = null;
		
		if (is_null($instance)) {
			$instance = new self();
		}
		
		return $instance;
	}
}