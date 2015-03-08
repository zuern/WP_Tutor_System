<?php
	add_action( 'admin_menu','create_menu_pages' );

	function load_tutor_mainPage() {
		include_once(ctc_plugin_dir.'views/misc/TutorManagerHomePage.php');
	}
	function load_tutor_viewStudents() {
		include_once(ctc_plugin_dir.'views/tutor/tutor-viewStudents.php');
	}
	function load_tutor_myStudents() {
		include_once(ctc_plugin_dir.'views/tutor/tutor-myStudents.php');
	}
	function load_tutorCoordinator_viewTutors() {
		include_once(ctc_plugin_dir.'views/coordinator/coordinator-viewAllTutors.php');
	}
	function load_tutorCoordinator_viewAllCourses() {
		include_once(ctc_plugin_dir.'views/coordinator/coordinator-viewAllCourses.php');
	}
	function load_tutorCoordinator_viewStudents() {
		include_once(ctc_plugin_dir.'views/coordinator/coordinator-viewAllStudents.php');
	}
	function load_tutorCoordinator_bulkAddCourseCodes() {
		include_once(ctc_plugin_dir.'views/coordinator/coordinator-addcodes.php');
	}
	function load_tutorCoordinator_bulkAddQualifications() {
		include_once(ctc_plugin_dir.'views/coordinator/coordinator-addqualifications.php');
	}
	function load_tutorCoordinator_bulkDeleteApplications() {
		include_once(ctc_plugin_dir.'views/coordinator/coordinator-deleteApplications.php');
	}
	function load_Admin_Advanced() {
		include_once(ctc_plugin_dir.'views/misc/admin-Advanced.php');
	}


	function create_menu_pages() {
		$topLevelMenuSlug = "CodeTheChange-Tutor-Management-System";
		
		//
		// Create top level menu
		//
		add_menu_page( 
			// Title attribute for link.
			"Code the Change Tutor Management System", 
			// Text to display on menu.
			"Tutoring System", 
			// Capability needed for menu to display.
			"can_see_Tutoring_System_Menu",
			// Unique slug (url) for the page
			$topLevelMenuSlug, 
			// Function to generate the page.
			'load_tutor_mainPage', 
			// Icon to display on menu. (dashicons-edit is a pencil)
			'dashicons-edit', 
			3
		);

		//
		// Create Tutors-only sub page for viewing students
		//
		add_submenu_page(
			// The slug of the top level menu we are attaching to
			$topLevelMenuSlug,
			// Title attribute for link.
			'View and Contact Students Needing Help',
			// Text to display on menu.
			'Find Students',
			// Capability needed for menu to display.
			'view_Tutor_Menus',
			// Unique slug (url) for the page.
			'viewStudents',
			// Function to generate the page.
			'load_tutor_viewStudents'
		);

		//
		// Create Tutors-only sub page for viewing students tutor has claimed
		//
		add_submenu_page(
			// The slug of the top level menu we are attaching to
			$topLevelMenuSlug,
			// Title attribute for link.
			'View My Students',
			// Text to display on menu.
			'My Students',
			// Capability needed for menu to display.
			'view_Tutor_Menus',
			// Unique slug (url) for the page.
			'myStudents',
			// Function to generate the page.
			'load_tutor_myStudents'
		);

		//
		// Create Tutor Coordinators-only sub page for managing privilages that tutors have.
		//
		add_submenu_page(
			// The slug of the top level menu we are attaching to
			$topLevelMenuSlug,
			// Title attribute for link.
			'View and Manage Tutors',
			// Text to display on menu.
			'Manage Tutors',
			// Capability needed for menu to display.
			'view_Tutor_Coordinator_Menus',
			// Unique slug (url) for the page.
			'manageTutors',
			// Function to generate the page.
			'load_tutorCoordinator_viewTutors'
		);

		//
		// Create Tutor Coordinators-only sub page for managing available courses
		//
		add_submenu_page(
			// The slug of the top level menu we are attaching to
			$topLevelMenuSlug,
			// Title attribute for link.
			'View and Manage Courses',
			// Text to display on menu.
			'Manage Courses',
			// Capability needed for menu to display.
			'view_Tutor_Coordinator_Menus',
			// Unique slug (url) for the page.
			'manageCourses',
			// Function to generate the page.
			'load_tutorCoordinator_viewAllCourses'
		);

		//
		// Create Tutor Coordinators-only sub page for managing all student applications.
		//
		add_submenu_page(
			// The slug of the top level menu we are attaching to
			$topLevelMenuSlug,
			// Title attribute for link.
			'View and Manage Students',
			// Text to display on menu.
			'Manage Students',
			// Capability needed for menu to display.
			'view_Tutor_Coordinator_Menus',
			// Unique slug (url) for the page.
			'manageStudents',
			// Function to generate the page.
			'load_tutorCoordinator_viewStudents'
		);

		//
		// Create Tutor Coordinators-only sub page for bulk adding course codes
		//
		add_submenu_page(
			// The slug of the top level menu we are attaching to
			$topLevelMenuSlug,
			// Title attribute for link.
			'Bulk Add Course Codes',
			// Text to display on menu.
			'Bulk Add Courses',
			// Capability needed for menu to display.
			'view_Tutor_Coordinator_Menus',
			// Unique slug (url) for the page.
			'bulkAddCourseCodes',
			// Function to generate the page.
			'load_tutorCoordinator_bulkAddCourseCodes'
		);

		//
		// Create Tutor Coordinators-only sub page for bulk adding qualifications
		//
		add_submenu_page(
			// The slug of the top level menu we are attaching to
			$topLevelMenuSlug,
			// Title attribute for link.
			'Bulk Add Qualifications',
			// Text to display on menu.
			'Bulk Add Qualifications',
			// Capability needed for menu to display.
			'view_Tutor_Coordinator_Menus',
			// Unique slug (url) for the page.
			'bulkAddQualifications',
			// Function to generate the page.
			'load_tutorCoordinator_bulkAddQualifications'
		);

		//
		// Create Tutor Coordinators-only sub page for bulk deleting courses
		//
		add_submenu_page(
			// The slug of the top level menu we are attaching to
			$topLevelMenuSlug,
			// Title attribute for link.
			'Bulk Delete Applications',
			// Text to display on menu.
			'Bulk Delete Applications',
			// Capability needed for menu to display.
			'view_Tutor_Coordinator_Menus',
			// Unique slug (url) for the page.
			'bulkDeleteApplications',
			// Function to generate the page.
			'load_tutorCoordinator_bulkDeleteApplications'
		);

		//
		// Create Admin-only sub page for deleting all ctc data.
		//
		add_submenu_page(
			// The slug of the top level menu we are attaching to
			$topLevelMenuSlug,
			// Title attribute for link.
			'Advanced',
			// Text to display on menu.
			'Advanced',
			// Capability needed for menu to display.
			'create_users',
			// Unique slug (url) for the page.
			'ctcAdvanced',
			// Function to generate the page.
			'load_Admin_Advanced'
		);


	}
?>