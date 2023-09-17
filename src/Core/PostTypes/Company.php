<?php

namespace LegalTrunkCore\Core\PostTypes;


class Company {
	
	private $post_type = 'company';
	
	/**
	 * Constructor
	 *
	 */
	public function __construct() {
		add_action('save_post', array($this, 'company_change_callback'), 10, 3);
	}
	
	
	public function company_change_callback($post_id, $post, $update) {
		if(!is_admin() || !$update) return; // end if it is not in admin or if it is not an update
		
		if($post->post_type == $this->post_type) {
			//save the member the notification
			$author_id = get_field('user_id', $post_id);
			
			if(!empty($author_id)) {
				
				// check if the company is approved
				$is_approved = get_field('approve_company', $post_id);
				if(!empty($is_approved) && $is_approved == 'yes') $this->handle_approved_notifications($author_id);
			}
		}
		
		exit;
	
	}
	
	public function handle_approved_notifications($user_id = '') {
		if(empty($user_id)) $user_id = get_current_user_id();
		
		
		
	}
	
	
	/**
	 * @return Company
	 */
	public static function get_instance() {
		static $instance = null;
		
		if (is_null($instance)) {
			$instance = new self();
		}
		
		return $instance;
	}
}