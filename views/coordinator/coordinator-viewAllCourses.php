<?php 
defined("ABSPATH") or die("No Script Kiddies Please!"); // Prevents direct access to PHP file.

include_once(ctc_plugin_dir.'models/ctc_Data_Model.php');

$message = NULL;
$messageStatus = NULL;

if ($_SERVER["REQUEST_METHOD"] == "POST") {

	$actionType = $_POST["actionType"];

	// Determine which action we are taking.
	switch ($actionType) {

		// Enable a master course
		case 'enableMasterCourse':
			if (isset($_POST["courseID"]) && current_user_can( "view_Tutor_Coordinator_Menus" ) ) {
				$courseID = intval($_POST["courseID"]);
				$coursename = getCourseName($courseID);

				$success = setCourseProperties($courseID,NULL,true);

				if ($success) {
					$message = "Successfully enabled '$coursename'. Students are now able to see this course when applying for help.";
					$messageStatus = "updated";
				}
				else {
					$message = "Database Error";
					$messageStatus = "error";
				}
			}
		break;

		// Disable a master course
		case 'disableMasterCourse':
			if (isset($_POST["courseID"]) && current_user_can( "view_Tutor_Coordinator_Menus" ) ) {
				$courseID = intval($_POST["courseID"]);
				$coursename = getCourseName($courseID);	

				$success = setCourseProperties($courseID,NULL,false);

				if ($success) {
					$message = "Successfully disabled '$coursename'. Students are no longer able to see this course when applying for help.";
					$messageStatus = "updated";
				}
				else {
					$message = "Database Error";
					$messageStatus = "error";
				}
			}
		break;

		// Create a new master course
		case 'addMasterCourse':
			if (isset($_POST["courseCode"]) && current_user_can( "view_Tutor_Coordinator_Menus" ) ) {
				$code = strtoupper(sanitize_text_field($_POST["courseCode"]));
				$message = addCourse($code);

				if (gettype($message) == "boolean" && $message == true) {
					$message = "Successfully added course '$code'. Be sure to enable it once you have updated qualifications for tutors.";
					$messageStatus = "updated";
				}
				else {
					$messageStatus = "error";
				}
			}
		break;

		// Enable or Disable all Courses
		case 'bulkEnableDisableCourses':
			if (isset($_POST["action"]) && current_user_can( "view_Tutor_Coordinator_Menus" )) {
				$action = $_POST["action"];
				$descriptor = NULL;
				switch ($action) {
					case "enable":
						$descriptor = "enabled";
					enableAllCourses();
					break;
					case "disable":
					$descriptor = "disabled";
					disableAllCourses();
					break;
				}
			$message = "All Courses are now $descriptor";
			$messageStatus = "updated";
			}
		break;
	}
}
?>
<div class="wrap">
	<h2>Master Course List</h2>
	<div id="masterCourseList">
		<form action="<?php echo $_SERVER["REQUEST_URI"]; ?> " method="post">
			<h4>Enable/Disable all courses</h4>
			<input type="hidden" name="actionType" value="bulkEnableDisableCourses">
			<input type="radio" name="action" value="enable">Enable All Courses<br>
			<input type="radio" name="action" value="disable">Disable All Courses<br>
			<input type="submit" value="Update">
		</form>
		<h4>Add new course</h4>
		<form action="<?php echo $_SERVER["REQUEST_URI"]; ?>" method="post">
			<input type="hidden" name="actionType" value="addMasterCourse">
			<input type="text" name="courseCode" placeholder="eg: APSC-101" maxlength="8">
			<input type="submit" value="Add">
		</form>
		<p>Remember, these are visible to students when requesting help.</p>
		<p>Only courses in this list will be displayed to students when they are making applications for tutoring.</p>
		<table class="ctc">
			<tr>
				<th>Course Code</th>
				<th>Toggle</th>
			</tr>
			<?php foreach (getCourseList() as $c): ?>
			<tr>
				<td><?php echo $c["code"]; ?></td>
				<td>
					<?php if ($c["isEnabled"]): ?>
					<form action="<?php echo $_SERVER["REQUEST_URI"]; ?>" method="post">
						<input type="hidden" name="actionType" value="disableMasterCourse">
						<input type="hidden" name="courseID" value="<?php echo $c["id"]; ?>">
						<input type="submit" value="Disable">
					</form>
					<?php else: ?>
					<form action="<?php echo $_SERVER["REQUEST_URI"]; ?>" method="post">
						<input type="hidden" name="actionType" value="enableMasterCourse">
						<input type="hidden" name="courseID" value="<?php echo $c["id"]; ?>">
						<input type="submit" value="Enable">
					</form>
					<?php endif; ?>
				</td>
			</tr>
			<?php endforeach; ?>
		</table>
	</div>
</div>