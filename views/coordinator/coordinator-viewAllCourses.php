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

	}
}


$courseList = getCourseList(NULL,False) // Get ALL courses regardless of whether they are active or not;

?>
<div class="wrap container-fluid">
	<div id="masterCourseList" class="col-md-4">
		<h2>Master Course List</h2>
		<h4>Add new course</h4>
		<form action="<?php echo $_SERVER["REQUEST_URI"]; ?>" method="post">
			<input type="hidden" name="actionType" value="addMasterCourse">
			<input type="text" name="courseCode" placeholder="eg: APSC-101" maxlength="8">
			<input type="submit" value="Add">
		</form>
		<p>Remember, these are visible to students when requesting help.</p>
		<p>Only courses listed as "active" will be displayed to students when they are making applications for tutoring.</p>
		<p>If a course is listed as "inactive" it means there are currently no tutors available to teach this course.</p>
		<table class="ctc">
			<tr>
				<th>Course Code</th>
				<th>Active/Inactive</th>
			</tr>
			<?php foreach ($courseList as $c): ?>
			<tr>
				<td><?php echo $c["code"]; ?></td>
				<?php if (isCourseActive($c["id"])): ?>
					<td style="background-color:green;">(Active)</td>
				<?php else: ?>
					<td style="background-color:red;">(Inactive)</td>
				<?php endif; ?>
	</tr>
<?php endforeach; ?>
</table>
</div>
<div class="col-md-6">
	<h2>View Tutors by Qualification</h2>
	<table class="ctc">
		<tr>
			<th>Course</th>
			<th>Tutors</th>
		</tr>

		<tr>
			<?php foreach ($courseList as $course): ?>
			<td>
				<?php echo esc_html($course["code"]); ?>
			</td>
			<td><ol>
				<?php  $Qualifiedtutors = getQualifiedTutors($course["id"]); ?>
				<?php foreach ($Qualifiedtutors as $tutor): ?>
				<?php
				$name = esc_html($tutor["Name"]);
				$email = esc_html($tutor["Email"]);
				?>
				<li><a href="mailto:<?php echo $email; ?>"><?php echo $name; ?></a></li>
			<?php endforeach; ?>
		</ol></td>

	</tr>

<?php endforeach; ?>
</table>
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