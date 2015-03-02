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

	// Assign tutor to student
	if (isset($AID) && isset($TID) && isset($actionType) && $TID > 0 && $actionType == "add" && $AID > 0) {

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
		<ul>
			<li><span class="okay">&nbsp;</span> - Good</li>
			<li><span class="priority">&nbsp;</span> - Needs Attention (7+ days old)</li>
			<li><span class="tutored">&nbsp;</span> - Has Tutor</li>
		</ul>
		<table class="ctc">
			<tr>
				<th></th>
				<th>ID</th>
				<th>Course</th>
				<th>Age (Days)</th>
				<th>Application Date</th>
				<th>Name</th>
				<th>Comments</th>
				<th>Tutor</th>
				<th>Claim Date</th>
			</tr>
			<?php foreach ($courseCodes as $application): ?>
				<tr>
				 	<td>
						<?php 
							$status = 'okay';
							if ($application["Tutored"]) {
								$status = 'tutored';
							}
							else if ($application["Application_Age"] >= 7) {
								$status = 'priority';
							}
						 ?>
						 <span class="<?php echo $status; ?>">&nbsp;</span>
				 	</td>
					<td><?php echo $application["ID"]; ?></td>
					<td><?php echo $application["Course"]; ?></td>
					<td><?php echo $application["Application_Age"]; ?></td>
					<td><?php echo $application["SubmitDate"]; ?></td>
					<td><a href='mailto:<?php echo esc_html($application["Email"]);?>'><?php echo esc_html($application["Name"]); ?></a></td>
					<td><?php echo esc_html($application["Comments"]); ?></td>
					<td>
						<?php if ($application["Tutored"]): $tutor = getTutor($application["Tutor_ID"]); ?>
						<form action="<?php echo $_SERVER["REQUEST_URI"]; ?>" method="post" style="display:inline;">
							<input type="hidden" name="ApplicationID" value="<?php echo $application["ID"]; ?>">
							<input type="hidden" name="CourseID" value="<?php echo $application["Course_ID"]; ?>">
							<input type="hidden" name="tutorID" value="<?php echo $application["Tutor_ID"]; ?>">
							<input type="hidden" name="actionType" value="remove">
							<input type="submit" value="Unclaim">
						</form>
						<a href='mailto:<?php echo $tutor["Email"]; ?>'><?php echo $tutor["Name"]; ?></a>
						<?php else: ?>
						<form method="post" action="<?php echo $_SERVER["REQUEST_URI"]; ?>">
							<input type="hidden" name="ApplicationID" value="<?php echo $application["ID"]; ?>">
							<input type="hidden" name="CourseID" value="<?php echo $application["Course"]; ?>">
							<input type="hidden" name="actionType" value="add">
							<select name="tutorID" style="width:150px;">
								<option value="-1">Assign a Tutor</option>
								<?php foreach (getQualifiedTutors($application["Course_ID"]) as $t): ?>
								<option value="<?php echo $t["ID"]; ?>"><?php echo $t["Name"]; ?></option>
								<?php endforeach; ?>
							</select>
							<input type="Submit" value="Assign">
						</form>
						<?php endif; ?>
					</td>
					<td>
						<?php echo $application["ClaimDate"]; ?>
					</td>
				</tr>
			<?php endforeach; ?>
		</table>
	</div>
</div>