<?php

namespace LegalTrunkCore\GravityForms;


class Connect {
	
	public function __construct()
	{
		// check if the GF Forms class exist
		if(class_exists('GFForms')) {
			$this->gravity_form_init();
		}
	}
	
	public function gravity_form_init() {
		Fields::get_instance();
		Form::get_instance();
		SaveAndContinue::get_instance();
	}
	
	/**
	 * @return Connect
	 */
	public static function get_instance() {
		static $instance = null;
		
		if (is_null($instance)) {
			$instance = new self();
		}
		
		return $instance;
	}
}