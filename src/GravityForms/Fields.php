<?php

namespace LegalTrunkCore\GravityForms;

use GF_Fields;

define( 'LOCAL_TRUNK_CORE_GRAVITY_FORMS_ASSETS_DIRECTORY',  LOCAL_TRUNK_CORE_SYSTEM_SRC_DIRECTORY. '/GravityForms/assets');

class Fields {
	
	public function __construct() {
		if(class_exists('GF_Field')) {
			$this->init_fields();
		}
	}
	
	public function init_fields() {
		GF_Fields::register( new GF_Field_CountrySelect() );
//		GF_Fields::register( new GF_Field_Founders() );
		GF_Fields::register( new GF_Field_Founders() );
	}
	
	
	public static function get_founders() {
		return array(
			'' => __('Select one', 'legal-trunk-core'),
			'chief-executive-officer' => __('Chief Executive Officer', 'legal-trunk-core'),
			'chief-operating-officer' => __('Chief Operating Officer', 'legal-trunk-core'),
			'chief-financial-officer' => __('Chief Financial Officer', 'legal-trunk-core'),
			'chief-information-officer' => __('Chief Information Officer', 'legal-trunk-core'),
			'human-resources' => __('Human Resources', 'legal-trunk-core'),
			'sales-manager' => __('Sales Manager', 'legal-trunk-core'),
			'general-manager' => __('General Manager', 'legal-trunk-core'),
			'product-manager' => __('Product Manager', 'legal-trunk-core'),
			'software-engineer' => __('Software Engineer', 'legal-trunk-core'),
		);
	}
	
	/**
	 * @return Fields
	 */
	public static function get_instance() {
		static $instance = null;
		
		if (is_null($instance)) {
			$instance = new self();
		}
		
		return $instance;
	}
}