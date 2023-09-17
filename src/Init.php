<?php

namespace LegalTrunkCore;

class Init {
	
	public static function init() {
		\LegalTrunkCore\Core\Core::get_instance();
		\LegalTrunkCore\UsersWP\Connect::get_instance();
		\LegalTrunkCore\GravityForms\Connect::get_instance();
	}
}