<?php 

// Redirects all tutor related roles to the tutoring system homepage.

function ctc_login_redirect($redirect_to,$request,$user) {
	// is there a user to check?
	global $user;

	if (isset($user-> roles) && is_array($user->roles)) {
		// check for tutors
		if (in_array('Tutor', $user->roles) || in_array('Tutor_Coordinator',$user->roles)) {
			return get_admin_url().'?page=CodeTheChange-Tutor-Management-System';
		}
		else {
			return get_admin_url();
		}
	}
	else {
		return get_admin_url();
	}
}

add_filter('login_redirect','ctc_login_redirect',10,3);


 ?>