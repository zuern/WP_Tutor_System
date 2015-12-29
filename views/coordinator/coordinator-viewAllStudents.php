<?php

defined("ABSPATH") or die("No Script Kiddies Please!");	// Prevents direct access to PHP file
include_once(ctc_plugin_dir.'models/ctc_Data_Model.php');

$success = NULL;
$message = NULL;
$messageStatus = NULL;

if ($_SERVER["REQUEST_METHOD"] == "POST") {

	$AID 			= intval($_POST["ApplicationID"]);
	$application 	= getApplication($AID);
	$TID 			= intval($_POST["tutorID"]);
	$CID 			= $application["Course_ID"];
	$actionType 	= $_POST["actionType"];

	// Delete student application record
	if (isset($AID) && isset($actionType) && $AID > 0 && $actionType == "DeleteApplication") {
		// Delete the record
		$success = removeSingleApplication($AID);

		if ($success) {
			$message = "Successfully delete application for tutoring from the database.";
			$messageStatus = "updated";
		}
		else {
			$message = "Could not delete application from the database.";
			$messageStatus = "error";
		}
	}

	// Assign tutor to student
	else if (isset($AID) && isset($TID) && isset($actionType) && $TID > 0 && $actionType == "add" && $AID > 0) {

		// Check if selected tutor is qualified to teach this student.
		if (!canTeachCourse($TID,$CID)){
			$message = "Tutor is not qualified to teach this student";
			$messageStatus = "error";
		}
		else if ($TID == $application["Tutor_ID"])
		{
			$message = "Tutor is already teaching this student.";
			$messageStatus = "error";
		}
		else {

			$success = claimApplication($TID,$AID);

			if ($success) {
				$message = "Successfully Paired";
				$messageStatus = "updated";
			}
			else if (!$success) {
				$message = "Database Error.";
				$messageStatus = "error";
			}
		}
	}

	// Unassign tutor from student
	else if (isset($AID) && isset($TID) && isset($actionType) && $TID > 0 && $actionType == "remove" && $AID>0) {
		$success = unclaimApplication($TID,$AID);
		if ($success) {
			$message = "Successfully Removed Pairing";
			$messageStatus = "updated";
		}
		else if (!$success) {
			$message = "Database Error. Did you make sure you specified the correct tutor?";
			$messageStatus = "error";
		}
	}
	else {
		$message = "Error. Did you select a tutor?";
		$messageStatus = "error";
	}
}

$courseCodes = getAllApplications();

?>
<div class="wrap">
	<div class="container-fluid">
		<h2>All Students</h2>
		<?php if (isset($message)): ?>
		<div id="message" class="<?php echo $messageStatus; ?>">
			<p><strong><?php echo $message; ?></strong></p>
		</div>
	<?php endif; ?>
	<table class="ctc">
		<tr>
			<th>Course</th>
			<th>Age (Days)</th>
			<th>Name</th>
			<th>Comments</th>
			<th>Frequency</th>
			<th>Tutor</th>
			<th>Application Date</th>
			<th>Claim Date</th>
			<th>Delete Record</th>
		</tr>
		<?php foreach ($courseCodes as $application): ?>
		<?php 
		$status = 'okay';
		if ($application["Tutored"]) {
			$status = 'tutored';
		}
		else if ($application["Application_Age"] >= 7) {
			$status = 'priority';
		}
		?>
		<tr class="<?php echo $status; ?>">
			<td><?php echo $application["Course"]; ?></td>
			<td style="text-align:center; font-weight:bold;"><?php echo $application["Application_Age"]; ?></td>
			<?php 
			$tutorName = "~TUTOR~";
			if ($application["Tutored"]){
				$tutorName = esc_html(getTutor($application["Tutor_ID"])["Name"]);
			}
			?>
			<td><a href='mailto:<?php echo esc_html($application["Email"]); ?>?subject=EngLinks%3A%20You%20have%20been%20matched%20with%20a%20tutor!&body=Dear%20Student%2C%0A%0A<?php echo $tutorName; ?>%20is%20a%20tutor%20for%20<?php echo esc_html($application["Course"]); ?>.%20The%20cost%20for%20EngLinks%20tutors%20is%20%2415(1st%20year%20courses)%2F%2420(upper%20year%20courses)%20an%20hour%2C%20and%20you%20are%20responsible%20for%20paying%20your%20tutor%20when%20you%20meet.%20This%20email%20was%20also%20sent%20to%20<?php echo $tutorName; ?>.%20You%20both%20can%20communicate%20to%20determine%20a%20good%20time%20to%20meet.%20Have%20a%20great%20day!%0A'><?php echo esc_html($application["Name"]); ?></a></td>
			<td><?php echo esc_html($application["Comments"]); ?></td>
			<td><?php echo esc_html($application["Frequency"]); ?></td>
			<td>
				<?php if ($application["Tutored"]): $tutor = getTutor($application["Tutor_ID"]); ?>
				<div class="container-fluid">
					<div class="col-md-6"><a href='mailto:<?php echo esc_html($tutor["Email"]); ?>?subject=EngLinks%3A%20You%20have%20been%20matched%20with%20a%20student!&body=Dear%20Tutor%2C%0A%0AWe%20are%20happy%20to%20notify%20you%20that%20you%20have%20been%20matched%20with%20a%20new%20student%3A%20%22<?php echo esc_html($application["Name"]); ?>%22.%20As%20always%2C%20the%20cost%20for%20EngLinks%20tutors%20is%20%2415(1st%20year%20courses)%20or%20%2420(upper%20year%20courses)%20an%20hour%2C%20and%20the%20student%20is%20responsible%20for%20paying%20you%20when%20you%20meet.%20This%20email%20was%20also%20sent%20to%20<?php echo esc_html($application["Name"]); ?>.%20You%20both%20can%20communicate%20to%20determine%20a%20good%20time%20to%20meet.%20You%20can%20contact%20the%20student%20here:%20<?php echo esc_html($application["Email"]); ?>.%20Have%20a%20great%20day!'><?php echo $tutor["Name"]; ?></a></div>
					<div class="col-md-6"><form action="<?php echo $_SERVER["REQUEST_URI"]; ?>" method="post" style="display:inline;">
						<input type="hidden" name="ApplicationID" value="<?php echo $application["ID"]; ?>">
						<input type="hidden" name="CourseID" value="<?php echo $application["Course_ID"]; ?>">
						<input type="hidden" name="tutorID" value="<?php echo $application["Tutor_ID"]; ?>">
						<input type="hidden" name="actionType" value="remove">
						<input type="submit" value="Unclaim">
					</form>
				</div>
			</div>
		<?php else: ?>
		<form method="post" action="<?php echo $_SERVER["REQUEST_URI"]; ?>">
			<input type="hidden" name="ApplicationID" value="<?php echo $application["ID"]; ?>">
			<input type="hidden" name="CourseID" value="<?php echo $application["Course"]; ?>">
			<input type="hidden" name="actionType" value="add">
			<?php 
			$qualifiedTutors = getQualifiedTutors($application["Course_ID"],True);
			if (sizeof($qualifiedTutors) > 0):
				?>
			<select name="tutorID" style="width:150px;">
				<option value="-1">Assign a Tutor</option>
				<?php foreach ($qualifiedTutors as $t): ?>
				<option value="<?php echo $t["ID"]; ?>"><?php echo $t["Name"]; ?></option>
			<?php endforeach; ?>
		</select>
		<input type="Submit" value="Assign">
	<?php else: ?>
	<?php 
									// Calculate how many tutors are qualified for below
	$numQualifiedTutors = sizeof(getQualifiedTutors($application["Course_ID"]));
	?>
	<p style="color:red; font-weight:bold;">No tutors available!<br>(<?php echo $numQualifiedTutors; ?> qualified)</p>
<?php endif; ?>
</form>
<?php endif; ?>
</td>
<td><?php echo $application["SubmitDate"]; ?></td>
<td>
	<?php echo $application["ClaimDate"]; ?>
</td>
<td>
	<form action="<?php echo $_SERVER["REQUEST_URI"]; ?>" method="post" onsubmit="return confirm( 'Permanently delete this record? (<?php echo esc_html($application["Name"]); ?>)' );">
		<input type="hidden" name="ApplicationID" value="<?php echo $application["ID"]; ?>" >
		<input type="hidden" name="actionType" value="DeleteApplication">
		<input type="submit" value="Delete">
	</form>
</td>
</tr>
<?php endforeach; ?>
</table>
</div>
</div>