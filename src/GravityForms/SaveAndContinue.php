<?php

namespace LegalTrunkCore\GravityForms;

use GFCommon;
use GFFormSettings;

class SaveAndContinue {
	
	public function __construct() {
		add_filter( 'gform_incomplete_submissions_expiration_days', array($this, 'modify_submissions_expiration_days'), 10, 1 );
//		add_action( 'gform_incomplete_submission_post_save', array($this, 'attach_submission_to_user'), 10, 4 );
	}
	
	public function modify_submissions_expiration_days( $expiration_days ) {
		GFCommon::log_debug( 'gform_incomplete_submissions_expiration_days: running.' );
		
		// save the link for a year
		return 365;
	}
	
	public function attach_submission_to_user($submission, $resume_token, $form, $entry) {
		if(!empty($form['fields'])) {
			$submitted_values = $submission['submitted_values'];
			$saved_entry = [];
			foreach ($form['fields'] as $field ) {
				$id = $field->id;
				$label = $field->label;
				$type = $field->type;
				if($label == 'Proposed Company Name') {
					$postarr['post_title'] = $submitted_values[$id];
				}
				
//				var_dump($label, $submitted_values[$id]);
////				$saved_entry[$id] = json_encode($)
////				$postarr['post_title'] =
			
			}
			
			$postarr['post_status'] = 'draft';
			$postarr['post_author'] = get_current_user_id();
			$postarr['post_type'] = 'company';
			
			$this->insert_posts($postarr);
		}
		exit;
	}
	
	public function insert_posts($postarr, $post_type = 'company') {
		
		try {
			$postarr['post_type'] = $post_type;
			$inserted_post = wp_insert_post($postarr, true);
			
			if(is_wp_error($inserted_post)) return false;
			
			// update custom fields
			if(isset($postarr['registration_location'])) {
				update_field('registration_location', sanitize_text_field($postarr['registration_location']), $inserted_post);
			}
			
			if(isset($postarr['company_type'])) {
				update_field('company_type', sanitize_text_field($postarr['company_type']), $inserted_post);
			}
			
			if(isset($postarr['founder_name'])) {
				update_field('founder_name', sanitize_text_field($postarr['founder_name']), $inserted_post);
			}
			
			if(isset($postarr['full_address'])) {
				update_field('full_address', sanitize_text_field($postarr['full_address']), $inserted_post);
			}
			
			if(isset($postarr['state'])) {
				update_field('state', sanitize_text_field($postarr['state']), $inserted_post);
			}
			
			if(isset($postarr['zip_code'])) {
				update_field('zip_code', sanitize_text_field($postarr['zip_code']), $inserted_post);
			}
			
			if(isset($postarr['country'])) {
				update_field('country', sanitize_text_field($postarr['country']), $inserted_post);
			}
			
			if(isset($postarr['help_with_tax_identification_number'])) {
				update_field('help_with_tax_identification_number', sanitize_text_field($postarr['help_with_tax_identification_number']), $inserted_post);
			}
			
			if(isset($postarr['has_social_security_number'])) {
				update_field('has_social_security_number', sanitize_text_field($postarr['has_social_security_number']), $inserted_post);
			}
			
			if(isset($postarr['founder_position'])) {
				update_field('founder_position', sanitize_text_field($postarr['founder_position']), $inserted_post);
			}
			
			return true;
			
		} catch (\Exception $e) {
			return false;
		}
	}
	
	
	/**
	 * @return SaveAndContinue
	 */
	public static function get_instance() {
		static $instance = null;
		
		if (is_null($instance)) {
			$instance = new self();
		}
		
		return $instance;
	}
}