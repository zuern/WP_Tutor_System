<?php
defined("ABSPATH") or die("No Script Kiddies Please!");	// Prevents direct access to PHP file

include_once(ctc_plugin_dir.'models/ctc_Data_Model.php');

$message = NULL;
$messageStatus = NULL;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
	// Post Logic
	$wantsTutees = false;
	if (isset($_POST["wantsTutees"]))
		$wantsTutees = $_POST["wantsTutees"];

	// If tutor wants tutees
	if ($wantsTutees == "on") {
		setTutorStatus(getCurrentUserID(), true);
	}
	else // Doesn't want tutees
		setTutorStatus(getCurrentUserID(), false);

	// Update output message
	$message = "Your preferences have been saved";
	$messageStatus = "updated";
}

// Is the current tutor actively looking for new students?
$isActive = tutorIsActive(getCurrentUserID());

// Represents the current state of the checkbox
$isChecked = "";

// If tutor is active, change the checkbox state
if ($isActive)
	$isChecked = "checked";

?>


<div id="wrap">
<?php if (isset($message)): ?>
	<div id="message" class="<?php echo $messageStatus; ?>">
		<p><strong><?php echo $message; ?></strong></p>
	</div>
<?php endif; ?>

<h2>Tutoring Preferences</h2>
<form method="post" style="border: 1px solid black;">
	<p>Checking this box indicates you wish to have more tutees assigned to you.</p>
	<input type="checkbox" name="wantsTutees" <?php echo $isChecked; ?>> Have the Coordinator assign me new students
	<br>
	<input type="submit" value ="Save">
</form>
</div>