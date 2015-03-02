<?php 
			// 		'Name'	'Display name'
$result = add_role( 'Tutor', 'Tutor', array(
	//Capabilities
	'read'								=> true,
	'view_Tutor_Menus'					=> true,
	'can_see_Tutoring_System_Menu'		=> true
	) );
$result = add_role( 'Tutor_Coordinator', 'Tutor Coordinator', array(
	'read'								=> true,
	'view_Tutor_Coordinator_Menus'		=> true,
	'can_see_Tutoring_System_Menu'		=> true
	) );

// Give admin privelages to see Tutoring System Homepage
$role = get_role( 'administrator' );
if ( isset($role) ) {
	$role->add_cap('can_see_Tutoring_System_Menu');
}
?>