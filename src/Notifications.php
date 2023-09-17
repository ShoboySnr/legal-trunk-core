<?php

use BracketSpace\Notification\Abstracts\Trigger as AbstractTrigger;

class Notifications extends AbstractTrigger {
	
	
	/**
	 * Constructor
	 */
	public function __construct() {
		// 1. Slug, can be prefixed with your plugin name.
		// 2. Title, should be translatable.
		parent::__construct(
			'legal-core-wp/member-notification',
			__( 'Member Notification', 'legal-core-wp' )
		);
		
		$this->add_action( 'trigger_company_approved', 10, 2 );
		
		$this->set_description(
			__( 'Fires when a company is approved', 'legal-core-wp' )
		);
	}
	
	
	public function context( $user_id, $post ) {
	
	}
	
	public function merge_tags() {
		// TODO: Implement merge_tags() method.
	}
}