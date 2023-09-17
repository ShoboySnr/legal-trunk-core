<?php

namespace LegalTrunkCore\Core;


use LegalTrunkCore\Core\PostTypes\Init;

class Core {
	
	public function __construct() {
		Init::get_instance();
	}
	
	
	/**
	 * @return Core
	 */
	public static function get_instance() {
		static $instance = null;
		
		if (is_null($instance)) {
			$instance = new self();
		}
		
		return $instance;
	}
}