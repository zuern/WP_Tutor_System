<?php
defined("ABSPATH") or die("No Script Kiddies Please!");	// Prevents direct access to PHP file

include_once(ctc_plugin_dir.'models/ctc_Data_Model.php');

$message = NULL;
$messageStatus = NULL;

if ($_SERVER["REQUEST_METHOD"] == "POST") {

	$ID = $_POST["applicationID"];

	if (isset($ID)) {
		
		$application = getApplication($ID);
		$tutor_ID = getCurrentUserID();

		// Double check current tutor is the tutor for the specified application and that the application claim date wasn't earlier than today.
		if ($tutor_ID == $application["Tutor_ID"]) {
			if (canUnclaimApplication($ID)) {
				unclaimApplication($tutor_ID,$ID);
				$message = "Student Application Successfully Unclaimed.";
				$messageStatus = "updated";
			}
			else {
				$message = "Student Application could not be unclaimed because it was not claimed today.";
				$messageStatus = "error";
			}	
		}
			}
	else{
		$message = "Database Error";
		$messageStatus = "error";
	}

}

$applications = getCompletedApplications(getCurrentUserID());

?>

<h2>My Students</h2>
<?php if (isset($message)): ?>
<div id="message" class="<?php echo $messageStatus; ?>">
	<p><strong><?php echo $message; ?></strong></p>
</div>
<?php endif; ?>

<?php if (sizeof($applications) > 0): ?>
	<p>Here you can see the student applications that you have 'claimed'. If you claimed an application by accident, you can unclaim it here (if it was claimed today), and it will be re-entered into the system.</p>
	<table class="ctc">
		<tr>
			<th>Name</th>
			<th>Email</th>
			<th>Course</th>
			<th>Comments</th>
			<th>Application Date</th>
			<th>Age (Days)</th>
			<th>Unclaim</th>
		</tr>
		<?php foreach ($applications as $a): ?>
		<tr>
			<td><?php echo $a["Name"]; ?></td>
			<td><?php echo $a["Email"]; ?></td>
			<td><?php echo $a["Course"]; ?></td>
			<td><?php echo $a["Comments"]; ?></td>
			<td><?php echo $a["SubmitDate"]; ?></td>
			<td><?php echo $a["Application_Age"]; ?></td>
			<td>
				<form action="<?php echo $_SERVER['REQUEST_URI']; ?>" method="POST">
					<input name="applicationID" type="hidden" value='<?php echo $a["ID"]; ?>'>
					<input type="submit" value="Unclaim" <?php if (!canUnclaimApplication($a["ID"])) { echo 'disabled'; } ?>>
				</form>
			</td>
		</tr>
		<?php endforeach; ?>
	</table>

<?php else: ?>
<p>You don't currently have any students that you have claimed. Go out there and find some students!</p>
<?php endif; ?>