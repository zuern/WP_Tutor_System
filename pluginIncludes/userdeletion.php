<?php 

// This function checks if the user being deleted is a tutor.
// If a tutor, removes his/her claimed applications from the db.
// Also removes his/her qualifications from the db.
function removeStudentApplications($user_ID) {
	include_once(ctc_plugin_dir.'models/ctc_Data_Model.php');
	removeAllTutorData($user_ID);
}

add_action( 'delete_user', 'removeStudentApplications');

?>