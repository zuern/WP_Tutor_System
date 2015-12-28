<?php 
defined("ABSPATH") or die("No Script Kiddies Please!"); // Prevents direct access to PHP file.
wp_enqueue_script( 'jquery-ui-accordion' );

include_once(ctc_plugin_dir.'models/ctc_Data_Model.php');

$message = NULL;
$messageStatus = NULL;
$blockID = 0;	// Dictates which block of the accordion we are going to open first

if ($_SERVER["REQUEST_METHOD"] == "POST") {

	if (isset($_POST["blockID"])) {
		$blockID = $_POST["blockID"];
	}

	$actionType = $_POST["actionType"];

	// Determine which action we are taking.
	switch ($actionType) {
		// Add a course to a tutor's qualifications
		case 'addQualification':
		if (isset($_POST["tutorID"]) && isset($_POST["qualificationID"]) && current_user_can("view_Tutor_Coordinator_Menus")) {
			$tutorID 			= intval($_POST["tutorID"]);
			$qualificationID 	= intval($_POST["qualificationID"]);

				// Make sure tutor doesn't already have this qualification
			if (canTeachCourse($tutorID,$qualificationID)){
				$message = "This tutor already has this qualification";
				$messageStatus = "error";
				break;
			}


			$success = setTutorQualification($tutorID,$qualificationID);

			if ($success) {
				$message = "Successfully added qualification.";
				$messageStatus = "updated";
			}
			else {
				$message = "Database Error";
				$messageStatus = "error";
			}
		}
		break;
		// Remove a course from a tutor's qualifications
		case 'deleteQualification':
		if (isset($_POST["tutorID"]) && isset($_POST["qualificationID"]) && current_user_can("view_Tutor_Coordinator_Menus")) {
			$tutorID 			= intval($_POST["tutorID"]);
			$qualificationID 	= intval($_POST["qualificationID"]);

				// If tutor doesn't have this qualification (for whatever reason), simply return a success message.
			if (!canTeachCourse($tutorID,$qualificationID)){
				$message = "Successfully removed qualification";
				$messageStatus = "updated";
				break;
			}

			$success = removeTutorQualification($tutorID,$qualificationID);

			if ($success) {
				$message = "Successfully removed qualification.";
				$messageStatus = "updated";
			}
			else {
				$message = "Database Error";
				$messageStatus = "error";
			}
		}
		break;
	}

}

// Get all tutors
$tutorData 		= getAllTutors();
$courseData 	= getCourseList();

?>
<?php if (current_user_can("view_Tutor_Coordinator_Menus")): ?>
	<div class="wrap">
		<h2>Tutor Management</h2>
		<?php if (isset($message)): ?>
		<div id="message" class="<?php echo $messageStatus; ?>">
			<p><strong><?php echo $message; ?></strong></p>
		</div>
		<?php endif; ?>
	<div class="container-fluid">
		<div class="col-md-4">
			<h2>Quick Links:</h2>
			<p>Click a name below to jump to that tutor's data.</p>
			<ol>
				<?php foreach ($tutorData as $tutor): ?>
					
					<li>
						<a href="#<?php echo $tutor['Name']; ?>" onclick="jQuery('#<?php echo $tutor['Name']; ?>').addClass('ui-accordion-header-active');"><?php echo $tutor['Name']; ?></a>
					</li>

				<?php endforeach; ?>
			</ol>
		</div>
		<div id="tutors_block" class="col-md-8">
			<?php if (sizeof($tutorData) > 0): for ($i = 0; $i < count($tutorData); $i++): ?>
			<?php $t = $tutorData[$i]; ?>
			<h3 class="tutor-head" id="<?php echo $t["Name"]; ?>"><?php echo $t["Name"]; ?></h3>
			<div>
				<h4>Email</h4>
				<p id="email"><a href='mailto:<?php echo $t["Email"]; ?>'><?php echo $t["Email"]; ?></a></p>

				<h4>Wants New Tutees</h4>
				<?php 
					// The message to display
					$tutorStatusMessage = "<span style='border:3px dashed green;'>This tutor wants to tutor more students.</span>";

					// If the tutor is NOT active, change the message.
					if ($t["isActive"] == 0) {
						$tutorStatusMessage = "<span style='border:3px dashed grey;'>This tutor is <b>not</b> looking for new students to teach.</span>";
					}

				 ?>
				 <p><?php echo $tutorStatusMessage; ?></p>

				<h4>Qualifications</h4>
				<?php if (isset($t["Courses"])): ?>
				<table class="ctc">
					<tr>
						<th>Course Code</th>
						<th>Delete</th>
					</tr>
					<?php $courses = $t["Courses"]; foreach ($courses as $c): ?>
					<tr>
						<td><?php echo $c["code"]; ?></td>
						<td>
							<form action='<?php echo $_SERVER['REQUEST_URI']; ?>#<?php echo $t["Name"]; ?>' method="post">
								<input type="hidden" name="actionType" value="deleteQualification">
								<input type="hidden" name="blockID" value="<?php echo $i; // Store which accordion block we are in to reopen as active on post ?>">
								<input type="hidden" name="qualificationID" value="<?php echo $c["id"]; ?>">
								<input type="hidden" name="tutorID" value="<?php echo $t["ID"]; ?>">
								<input type="submit" value="Delete">
							</form>
						</td>
					</tr>
					<?php endforeach; ?>
				</table>
		<?php else: // Has No Qualifications ?>
		<p>This tutor has no qualifications yet.</p>
	<?php endif; ?>
	<h4>Add new qualification</h4>
	<form action='<?php echo $_SERVER['REQUEST_URI']; ?>#<?php echo $t["Name"]; ?>' method="post">
		<input type="hidden" name="actionType" value="addQualification">
		<input type="hidden" name="tutorID" value="<?php echo $t["ID"]; ?>">
		<input type="hidden" name="blockID" value="<?php echo $i; // Store which accordion block we are in to reopen as active on post ?>">
		<select name="qualificationID" id="qualificationID">
			<option value="-1">Select a Course</option>
			<?php foreach (getNonQualifiedCourseList($t["ID"]) as $c): ?>
			<option value="<?php echo $c["id"]; ?>"><?php echo $c["code"]; ?></option>
		<?php endforeach; ?>
	</select>
	<input type="submit" value="Add">
</form>
<?php 
$tutorApps = getCompletedApplications($t["ID"]);
$hasApps   = isset($tutorApps);
?>
<h4>Claimed Applications</h4>
<?php if ($hasApps): ?>
	<table class="ctc">
		<tr>
			<th>Course</th>
			<th>Name</th>
			<th>Email</th>
			<th>App Date</th>
			<th>Claim Date</th>
			<th>ID</th>
		</tr>
		<?php foreach ($tutorApps as $a): ?>
		<tr>
			<td>
				<?php echo $a["Course"]; ?>
			</td>
			<td>
				<?php echo esc_html($a["Name"]); ?>
			</td>
			<td>
				<a href='mailto:<?php echo $a["Email"]; ?>'><?php echo esc_html($a["Email"]); ?></a>
			</td>
			<td>
				<?php echo $a["SubmitDate"]; ?>
			</td>
			<td>
				<?php echo $a["ClaimDate"]; ?>
			</td>
			<td>
				<?php echo $a["ID"]; ?>
			</td>
		</tr>
	<?php endforeach; ?>
</table>
<?php else: //else if student doesn't have past applications ?>
	<p>This tutor has not taken on any student applications yet.</p>
<?php endif; ?>
</div>


<?php endfor; endif; // For each item in tutordata ?>
</div>
</div>

</div>
<?php else: // if user can't view coordinator menus ?>
	<h2>You are not authorized to view this page.</h2>
<?php endif; ?>
<script type="text/javascript">

jQuery(document).ready(function(){
	jQuery('#tutors_block').accordion({
		collapsible: true,
		heightStyle: "content",
		active: <?php echo $blockID; ?>
	});
});

</script>