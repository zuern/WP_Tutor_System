<?php 
defined("ABSPATH") or die("No Script Kiddies Please!");	// Prevents direct access to PHP file
include_once(ctc_plugin_dir.'models/ctc_Data_Model.php');

$success = NULL;
$message = NULL;
$messageStatus = NULL;

if ($_SERVER["REQUEST_METHOD"] == "POST") {

	if (current_user_can( "view_Tutor_Coordinator_Menus" )) {
		if (isset($_POST["date"])){ 
			$date = date($_POST["date"]);
			$result = removeApplicationsBeforeDate($date);
			if ($result > 1) { // Success
				$success = true;
				$message = "Successfully Deleted $result Records from the the DB";
				$messageStatus = "updated";
			}
			else if ($result == 0) {
				$success = false;
				$message = "No records were found that were submitted up to that date.";
				$messageStatus = "error";
			}
			else {
				$success = false;
				$message = "An error occurred trying to delete the records.";
				$messageStatus = "error";
			}			
		}
		else {
			$success = false;
			$message = "An error has occurred.";
			$messageStatus = "error";
		}
	}

}

?>

<div class="wrap">
	<div class="container-fluid">
		<h2>Delete Student Applications</h2>
		<?php if (isset($message)): ?>
		<div id="message" class="<?php echo $messageStatus; ?>">
			<p><strong><?php echo $message; ?></strong></p>
		</div>
		<?php endif; ?>
		<p>Delete student applications that were submitted before a certain date.</p>
		<p><b>Warning:</b> This action cannot be undone.</p>
		<form action="<?php echo $_SERVER["REQUEST_URI"]; ?>" method="post">
			<label for="date">
				Delete all student applications up to this date (YYYY-MM-DD):<br>
				<input type="date" name="date">
			</label>
			<input type="submit" value="Delete">
		</form>
	</div>
</div>