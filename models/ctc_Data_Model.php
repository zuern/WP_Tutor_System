<?php
defined("ABSPATH") or die("No Script Kiddies Please!");	// Prevents direct access to PHP file
require_once(ABSPATH.'wp-admin/includes/user.php' );

// Adds a new application to the database. Returns an array of errors or NULL if successful
function addApplication($fullName, $email, $courseID, $tutoringFrequency, $comments){
	$errors = array(); 	// Holds any error messages to return

	// Sanitize Data
	$cleanName 			= sanitize_text_field( $fullName );
	$cleanEmail 		= sanitize_email( $email );
	$cleanCourseID 		= intval($courseID);
	$cleanFrequency		= intval($tutoringFrequency);
	$cleanComments 		= sanitize_text_field( $comments );
	$cleanDate 			= date('Y-m-d');

	// Insert into Database.
	global $wpdb;

	// Verify no existing applications for this student for that course (If tutor_ID has a value, we assume the application has yielded a tutor. We're looking for ones without a tutor).

	$duplicates = $wpdb->get_results("
		SELECT * FROM ctc_applications WHERE email = '$cleanEmail' AND course_ID = $cleanCourseID AND tutor_ID IS NULL
	");

	if (!sizeof($duplicates) > 0) {
		if ($cleanFrequency > 0) {
			$successfulInsert = $wpdb->insert(
				'ctc_applications',
				array(
					'email'			=>	$cleanEmail,
					'course_ID'		=>	$cleanCourseID,
					'frequency'		=>  $cleanFrequency,
					'comments'		=>	$cleanComments,
					'submitdate'	=>	$cleanDate,
					'name'			=>	$cleanName
					),
				array(
					'%s',
					'%d',
					'%d',
					'%s',
					'%s',
					'%s',
					)
				);
		}
		else {
			$errors[] = "Frequency must be set.";
		}
	}
	else {	// We found duplicate records in the db before inserting.
		$errors[] = "You have an existing application for this course. If it's been over a week since you have applied, please contact EngLinks directly via email, and they will manually match you to a tutor.";
	}

	if (isset($successfulInsert) && !$successfulInsert) {
		$errors[] = "Database Error. Please try again later, or if the problem persists, contact the site administrator.";
	}
	return $errors;
}

// Adds a new course to the database. Courses are disabled by default. Returns true if successful, Returns error message if unsuccessful.    
function addCourse($courseCode) {
	global $wpdb;

	// Sanitize away any nastiness.
	// Convert any lowercase characters to uppercase.
	$code = strtoupper(sanitize_text_field($courseCode));

	// If course code does not match correct format return false
	if (preg_match("/[A-Z]{4}[-]{1}[0-9]{3}/", $code) != 1)
		return "Course could not be added. '$code' does not match format 'XXXX-XXX'.";

	// Make sure course code isn't already in database
	$potentialDuplicates = $wpdb->get_results("SELECT course_ID FROM ctc_courses WHERE code = '$code'");

	if (sizeof($potentialDuplicates) > 0)
		return "Course could not be added. '$code' is already in the database.";

	// Make sure course code has at most 8 characters (XXXX-XXX)
	if (strlen($code) == 8) {
		$value =  $wpdb->query("INSERT INTO ctc_courses (code,isEnabled) VALUES ('$code',0)");
		if ($value == 1) {
			return true;
		}
		else {
			return "An error occured. Please try again later.";
		}
	}
	else
		return "'$code' could not be added. Course code too short/long. Must be 8 characters total";
}

// Returns true if the specified user ID is qualified to teach the specified course ID
function canTeachCourse($tutor_ID,$course_ID) {
	global $wpdb;

	$course = $wpdb->get_row("SELECT * FROM ctc_tutor_qualifications WHERE user_ID = $tutor_ID AND course_ID = $course_ID");

	return (isset($course));
}

// Returns true if the specified application can be unclaimed.
function canUnclaimApplication($application_ID) {
	$app = getApplication($application_ID);
	if (isset($app)) {
		// If application was claimed today (i.e. less than a day ago), it can be unclaimed.
		return ($app["ClaimDate"] == date('Y-m-d'));
	}
}

// Lists the specified tutor as the tutor in the specified application. Returns true if successful.
function claimApplication($tutor_ID, $application_ID) {
	$application = getApplication($application_ID);

	// Verify the specified tutor is qualified to teach this student
	if (canTeachCourse($tutor_ID,$application["Course_ID"])) {

		global $wpdb;

		// Update the database
		$result = $wpdb->update(
				"ctc_applications",
				array(
						"tutor_ID"			=> $tutor_ID,
						"claimDate"			=> date('Y-m-d')
					),
				array(
						"application_ID"	=> $application_ID
					)
			);

		// Returns true if update was successful.
		return (!$result === false);
	}
}

// Creates the student array used throughout the application
function createStudent($application) {

	$Name 			= 	$application->name;
	$Email			=	$application->email;
	$Course			=	getCourseName((int)$application->course_ID);
	$Course_ID		=	intval($application->course_ID);
	$Comments		=	$application->comments;
	$SubmitDate		=	$application->submitdate;
	$ID				=	intval($application->application_ID);
	$Tutored		=	isset($application->tutor_ID);
	$Tutor_ID		=	intval($application->tutor_ID);
	$ClaimDate 		=	$application->claimDate;

	$dateNow  = new DateTime();
	$dateThen = new DateTime($application->submitdate);
	$age 	  = $dateNow->diff($dateThen)->days;

	// Set the tutoring frequency. The values 1-5 and their meaning is defined in installDatabase.php
	$Tutoring_Frequency = intval($application->frequency);
	switch ($Tutoring_Frequency) {
		case 1:
			$Tutoring_Frequency = "Once";
			break;
		case 2:
			$Tutoring_Frequency = "Weekly";
			break;
		case 3:
			$Tutoring_Frequency = "Bi-Weekly";
			break;
		case 4:
			$Tutoring_Frequency = "Monthly";
			break;
		case 5:
			$Tutoring_Frequency = "Bi-Monthly";
			break;
	}
	
	return array(
		"Name"				=>	$Name,
		"Email"				=>	$Email,
		"Course"			=>	$Course,
		"Course_ID"			=>	$Course_ID,
		"Frequency"			=>  $Tutoring_Frequency,
		"Comments"			=>	$Comments,
		"SubmitDate"		=>	$SubmitDate,
		"ID"				=>	$ID,
		"Tutored"			=>	$Tutored,
		"Tutor_ID"			=>	$Tutor_ID,
		"ClaimDate"			=>	$ClaimDate,
		"Application_Age" 	=>	intval($age)
		);
}

// Deletes all users of a certain role from the db. Returns true if successful, else returns false.
function deleteUsersByRole($role, $areYouSure) {
	if ($areYouSure) {
		$usersList = get_users(array(
			"role"	=> "$role"
			));

		foreach ($usersList as $user) {
			$id = $user->ID;
			wp_delete_user($id);
		}
		return true;
	}
	else
	{
		return false;
	}
}

// Disables all courses
function disableAllCourses() {
	global $wpdb;
	$wpdb->query("UPDATE ctc_courses SET isEnabled = 0");
}

// Drops all code the change tables in the db. returns true if successful.
function dropAllCTCTables($areYouSure) {
	if ($areYouSure) {
		global $wpdb;
		$wpdb->query("DROP TABLE IF EXISTS `ctc_tutor_qualifications`");
		$wpdb->query("DROP TABLE IF EXISTS `ctc_applications`");
		$wpdb->query("DROP TABLE IF EXISTS `ctc_courses`");
		return true;
	}
	else
	{
		return false;
	}
}

// Enables all courses
function enableAllCourses() {
	global $wpdb;
	$wpdb->query("UPDATE ctc_courses SET isEnabled = 1");
}

// Returns the list of all students' applications sorted by those without a tutor first, then by course code. Returns most recent 300.
function getAllApplications($sortbyApplicationID=false) {
	global $wpdb;
	$byID = "";
	if ($sortbyApplicationID)
		$byID = "application_ID ASC, ";

	$unclaimedStudents = $wpdb->get_results("SELECT * FROM ctc_applications WHERE tutor_ID IS NULL ORDER BY $byID submitdate ASC, course_ID ASC LIMIT 300");

	$studentsList = $wpdb->get_results("SELECT * FROM ctc_applications WHERE tutor_ID IS NOT NULL ORDER BY $byID submitdate DESC, tutor_ID ASC, course_ID ASC LIMIT 50");

	// Convert raw data into formatted array of students.
	$students = array();
	foreach ($unclaimedStudents as $student) {
		$s = createStudent($student);
		$students[] = $s;
	}
	foreach ($studentsList as $student) {
		$s = createStudent($student);
		$students[] = $s;
	}
	return $students;
}

// Returns the list of tutors, along with the courses they are qualified to teach.
function getAllTutors() {
	$tutors = get_users( array(
		'role'		=>	'tutor',
		'orderby'	=>	'display_name'
		) );

	$AllTutors = array();

	foreach ($tutors as $t) {
		$AllTutors[] = array(
			"Name"			=>	$t->data->display_name,
			"ID"			=>	$t->data->ID,
			"Email"			=>	$t->data->user_email,
			"Courses"		=>	getCourseList($t->data->ID)
		);
	}
	return $AllTutors;
}

// Returns NULL if no match found. Else returns a single application matching the applicationID
function getApplication($application_ID) {
	global $wpdb;

	$application = $wpdb->get_row("SELECT * FROM ctc_applications WHERE application_ID = $application_ID");

	return createStudent($application);
}

// Returns the list of students' applications sorted by date application submitted. Only returns applications needing tutors that the specified userID is qualified to teach.
function getApplications($userID) {
	global $wpdb;

	// If tutor has no qualifications, return null
	if (!tutorHasQualifications($userID)) {
		return NULL;
	}

	// Get courses tutor can teach
	$courses = getCourseList($userID);

	// Get all applications that this user could teach.
	$query = "SELECT * FROM ctc_applications WHERE ( course_ID = ";
	for ($i = 0; $i<count($courses); $i++){
		$query .= $courses[$i]["id"];

		if ($i < count($courses)-1) {
			$query.= ' OR course_ID = ';
		}
	}
	$query .= " ) AND tutor_ID IS NULL ORDER BY submitdate";

	// Run the query
	$studentsList = $wpdb->get_results($query);


	// Convert raw data into formatted array of students.
	$students = array();
	foreach ($studentsList as $student) {
		$s = createStudent($student);
		$students[] = $s;
	}

	return $students;
}

// Returns all applications that a specified tutor has taken on, sorted by date, then course code
function getCompletedApplications($tutor_ID) {
	global $wpdb;
	if (!isset($tutor_ID)) {
		return NULL;
	}
	$applications = $wpdb->get_results("SELECT * FROM ctc_applications WHERE tutor_ID = $tutor_ID ORDER BY submitdate DESC, course_ID");

	$students = array();
	if (sizeof($applications) > 0) {
		
		foreach ($applications as $a) {
			$s = createStudent($a);
			$students[] = $s;
		}
		return $students;
	}
}

// Returns the int ID number of the specified course. If course code not in db, returns false
function getCourseIDByCode($course_Code) {
	global $wpdb;
	$result =  $wpdb->get_var("SELECT course_id FROM  ctc_courses WHERE code = '$course_Code'");
	return $result;
}

// Returns the list of courses that can be taught. If tutorID is supplied, returns list of courses that that tutor is qualified to teach.
function getCourseList($tutorID = NULL) {
	global $wpdb;

	$tutorFilter = "";  			// Used for additional query parameters.
	if (isset($tutorID)) {			// If tutorID was supplied, only return courses that he/she can teach.
		// Get the list of course codes the tutor can teach.
		$rawqualifications = $wpdb->get_results("SELECT * FROM ctc_tutor_qualifications WHERE user_ID = $tutorID");

		// If no qualifications, return Null
		if (sizeof($rawqualifications) < 1) {
			return NULL;
		}

		// Adjust tutorFilter to select only courses where the course id matches ones the tutor can teach.
		$tutorFilter = "WHERE";
		foreach ($rawqualifications as $q) {
			$id = $q->course_ID;
			$tutorFilter .= ' course_ID = '. $id . ' OR ';
		}

		// Remove trailing 'OR '
		$tutorFilter = substr($tutorFilter, 0, -4);
	}

	$raw = $wpdb->get_results("SELECT * FROM ctc_courses $tutorFilter ORDER BY code");

	$courses = array();

	foreach ($raw as $course) {
		$courses[] = array(
			"id"		=> intval($course->course_ID),
			"code"		=> $course->code,
			"isEnabled"	=> $course->isEnabled
		);
	}

	return $courses;
}

// Returns the course code given a course id
function getCourseName($course_ID) {
	global $wpdb;
	$result =  $wpdb->get_var("SELECT code FROM  ctc_courses WHERE course_ID = $course_ID");
	return $result;
}

// Get the current user's ID
function getCurrentUserID() {
	// Get current user ID
	$user = wp_get_current_user();
	return $user->ID;
}

// Returns all courses a tutor is NOT qualified to teach
function getNonQualifiedCourseList($tutorID) {
	global $wpdb;

	// Get the list of course codes the tutor can teach.
	$rawqualifications = $wpdb->get_results("SELECT * FROM ctc_tutor_qualifications WHERE user_ID = $tutorID");

	// If no qualifications, return all courses
	if (sizeof($rawqualifications) < 1) {
		return getCourseList();
	}

	// Adjust tutorFilter to select only courses where the course id doesn't match ones the tutor can teach.
	// IE select only courses the tutor CAN'T teach.
	$tutorFilter = "WHERE";
	foreach ($rawqualifications as $q) {
		$id = $q->course_ID;
		$tutorFilter .= ' course_ID != '. $id . ' AND ';
	}

	// Remove trailing 'AND '
	$tutorFilter = substr($tutorFilter, 0, -5);

	$raw = $wpdb->get_results("SELECT * FROM ctc_courses $tutorFilter ORDER BY code");

	$courses = array();

	foreach ($raw as $course) {
		$courses[] = array( 
			"id"		=> intval($course->course_ID),
			"code"		=> $course->code,
			"isEnabled"	=> $course->isEnabled
		);
	}

	return $courses;
}

// Returns list of tutors qualified to teach a course
function getQualifiedTutors($course_ID) {
	global $wpdb;
	
	$qualifiedTutors = $wpdb->get_results("SELECT * FROM ctc_tutor_qualifications WHERE course_ID = $course_ID");

	$tutors = array();	// Stores our data

	if (sizeof($qualifiedTutors) > 0) {
		foreach ($qualifiedTutors as $q) {
			$tutor =  getTutor($q->user_ID);
			$tutors[] = $tutor;
		}
	}
	return $tutors;
}

// Returns the data about the specified tutor.
function getTutor($tutor_ID) {
	if (!isset($tutor_ID)) {
		return NULL;
	}
	global $wpdb;
	$userstable = $wpdb->prefix.'users';

	$tutor 	= $wpdb->get_row("SELECT * FROM $userstable WHERE ID = $tutor_ID");
	return array(
	"Name"	=> $tutor->display_name,
	"Email"	=> $tutor->user_email,
	"ID"	=> $tutor_ID
	);
}

// Returns array of statistics about the tutoring system.
function getStatistics() {
	global $wpdb;

	$totalApplications 		= 0;
	$pendingApplications	= 0;
	$totalTutors			= 0;
	$totalCourses			= 0;

	$applicationscount		= $wpdb->get_results("SELECT COUNT(1) AS 'value' FROM ctc_applications");
	$pendingcount			= $wpdb->get_results("SELECT COUNT(1) AS 'value' FROM ctc_applications WHERE tutor_ID IS NULL");
	$totaltutorscount		= count_users( "Tutor" );
	$totalcoursescount		= $wpdb->get_results("SELECT COUNT(1) AS 'value' FROM ctc_courses");

	$totalApplications		= intval($applicationscount[0]->value);
	$pendingApplications	= intval($pendingcount[0]->value);
	$totalTutors 			= intval($totaltutorscount["avail_roles"]["Tutor"]);
	$totalCourses 			= intval($totalcoursescount[0]->value);

	return array(
		"Total Applications"		=>	$totalApplications,
		"Pending Applications"		=>	$pendingApplications,
		"Total Tutors"				=>	$totalTutors,
		"Total Courses"				=>	$totalCourses
	);
}

// Removes the specified qualification from the specified tutor. Returns true if successful.
function removeTutorQualification($tutor_ID,$course_ID) {
	global $wpdb;

	if (!isset($tutor_ID) || !isset($course_ID)) {
		return false;
	}
	$result = $wpdb->delete(
		"ctc_tutor_qualifications",
		array(
				"user_ID" 	=>	$tutor_ID,
				"course_ID"	=>	$course_ID
			)
		);
	return $result;
}

// Removes all qualifications and claimed applications from the database. Returns false if an error occured
function removeAllTutorData($tutor_ID) {
	global $wpdb;

	if (!isset($tutor_ID)) {
		return false;
	}
	// Delete all qualifications
	$result1 = $wpdb->delete(
		"ctc_tutor_qualifications",
		array(
				"user_ID"	=>	$tutor_ID
			)
		);
	// Delete all claimed applications
	$result2 = $wpdb->delete(
		"ctc_applications",
		array(
				"tutor_ID"	=>	$tutor_ID
			)
		);

	return ($result1 && $result2);
}

// Removes a single application from the database.
// $application_ID: The ID of the application to remove from the database.
// Returns true if succeeded. Else returns false.
function removeSingleApplication($application_ID) {
	global $wpdb;

	$result = $wpdb->query("DELETE FROM ctc_applications WHERE application_ID = $application_ID");

	return $result;
}

// Removes all applications that were submitted before the specified date.
// $date: the date string (YYYY-MM-DD).
// Returns number of rows affected or false if an error occured.
function removeApplicationsBeforeDate($date) {
	global $wpdb;

	$date = date($date); // Convert to date object

	$rowsaffected = $wpdb->query(
		"DELETE FROM ctc_applications WHERE submitdate < '$date'"
		);

	if ($rowsaffected === false) {
		return false;
	}
	return 0;
}

// Sets values for the specified course
function setCourseProperties($course_ID,$code = NULL, $isEnabled = NULL) {
	if (isset($course_ID)) {
		global $wpdb;

		$data = array();
		if (isset($code))
			$data["code"] = intval($code);
		if (isset($isEnabled))
			$data["isEnabled"] = $isEnabled;

		$result = $wpdb->update(
			"ctc_courses",
			$data,
			array (
				"course_ID"	=> $course_ID
				)
			);

		// Returns true if records updated. Else returns false
		return (!$result === false);
	}
	return false;
}

// Assigns the specified tutor the specified qualification. Returns true if successful.
function setTutorQualification($tutor_ID,$course_ID) {
	global $wpdb;

	// Make sure qualification isn't already in database
	$results = $wpdb->get_row("SELECT * FROM ctc_tutor_qualifications WHERE user_ID = $tutor_ID AND course_ID = $course_ID");
	if (isset($results)){
		return false;
	}

	if (!isset($tutor_ID) || !isset($course_ID)) {
		return false;
	}

	$result = $wpdb->insert(
		"ctc_tutor_qualifications",
		array(
			"user_ID"	=> $tutor_ID,
			"course_ID"	=> $course_ID
			)
		);

	return $result;
}

// Returns an array of course codes, each of which has a list of applications.
function sortApplicationsbyCourse($applications) {

	if (!isset($applications)) {
		return NULL;
	}

	$courseCodes = array();

	// Add course codes of each course
	foreach ($applications as $app) {
		$courseCodes[] = $app["Course"];
	}

	// Remove any duplicate values
	$courseCodes = array_unique($courseCodes);
	
	// Create variable to hold sorted applications
	$sortedApplications = array();

	$counter = 0;

	foreach ($courseCodes as $code) {

		$sortedApplications[] = array( $code, array() );
		foreach ($applications as $app) {
			if ($app["Course"] == $code) {
				$sortedApplications[$counter][1][] = $app;
			}
		}
		$counter++;
	}

	return $sortedApplications;
}

// Returns true if the tutor has at least one course he/she is qualified to teach.
function tutorHasQualifications($tutor_ID) {
	global $wpdb;

	$qualifications = $wpdb->get_results("SELECT * FROM ctc_tutor_qualifications WHERE user_ID = $tutor_ID");

	return (sizeof($qualifications) > 0);
}

// Unlists the specified tutor from the specified application. Returns true if unclaim was successful.
function unclaimApplication($tutor_ID, $application_ID) {

	$AID = intval($application_ID);

	$application = getApplication($AID);

	// Verify the specified tutor is the tutor for this application
	if ($application["Tutor_ID"] == $tutor_ID) {

		global $wpdb;

		// Update the database
		$result = $wpdb->query("UPDATE ctc_applications SET tutor_ID = NULL, claimDate = NULL WHERE application_ID = $AID");

		// Returns true if update was successful.
		return (!$result === false);
	}
}

?>