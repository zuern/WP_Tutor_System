<?php 
defined("ABSPATH") or die("No Script Kiddies Please!"); // Prevents direct access to PHP file.

wp_enqueue_script( 'jquery-ui-accordion' );

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


$courseList = getCourseList();

?>
<div class="wrap container-fluid">
	<div id="masterCourseList" class="col-md-4">
		<h2>Master Course List</h2>
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
			<?php foreach ($courseList as $c): ?>
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
	<div class="col-md-6">
		<h2>View Tutors by Qualification</h2>
		<div id="tutors_block">
		<?php foreach ($courseList as $course): ?>

			<h3><?php echo esc_html($course["code"]); ?></h3>
			<div>
			<?php  $Qualifiedtutors = getQualifiedTutors($course["id"]); ?>
			<?php foreach ($Qualifiedtutors as $tutor): ?>
				<?php
					$name = esc_html($tutor["Name"]);
					$email = esc_html($tutor["Email"]);
				?>
				<li><a href="mailto:<?php echo $email; ?>"><?php echo $name; ?></a></li>
			<?php endforeach; ?>
			</div>		

		<?php endforeach; ?>
		</div>
	</div>
</div>
<script type="text/javascript">

jQuery(document).ready(function(){
	jQuery('#tutors_block').accordion({
		collapsible: true,
		heightStyle: "content",
	});
});

</script>