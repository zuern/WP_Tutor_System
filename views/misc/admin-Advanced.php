<?php
defined("ABSPATH") or die("No Script Kiddies Please!");	// Prevents direct access to PHP file

include_once(ctc_plugin_dir.'models/ctc_Data_Model.php');

$message = NULL;
$messageStatus = NULL;

if ($_SERVER["REQUEST_METHOD"] == "POST") {

	if (isset($_POST["actionType"])) {
		switch ($_POST["actionType"]) {
			
			case 'deleteEverything':
				if (isset($_POST["iamsure"]) && isset($_POST["recreateTables"])) {
					if ($_POST["iamsure"] == "yes" && current_user_can( 'create_users' )) {
						// Drop our tables
						// Delete all Tutors
						// Delete all Coordinators
						if (deleteUsersByRole("Tutor",true) && deleteUsersByRole("Tutor_Coordinator",true) && dropAllCTCTables(true)) {
							if ($_POST["recreateTables"]) {
								// Install our DB.
								include_once(ctc_plugin_dir.'pluginIncludes/installDatabase.php');
							}
							// Output Message
							$message = "All Done. All data has been removed.";
							$messageStatus = 'updated';
						}
						else
						{
							$messageStatus = 'error';
							$message = "An Error has occured.";
						}
					}
				}
				break;
			
			default:
				$message = "An unknown error has occured.";
				$messageStatus = "error";
				break;
		}
	}

}
?>

<div class="wrap">
	<div class="container-fluid">
		<?php if (current_user_can("create_users")): ?>
			<?php if (isset($message)): ?>
			<div id="message" class="<?php echo $messageStatus; ?>">
				<p><strong><?php echo $message; ?></strong></p>
			</div>
			<?php endif; ?>
			<h2>Advanced</h2>
			<h3>Delete Everything:</h3>
			<p>Please don't ever click this unless you are 1000% sure you want to.</p>
			<form action="<?php echo $_SERVER["REQUEST_URI"]; ?>" method="post" style="border:1px grey solid; padding:10px;">
				<label>Drop all Code the Change Tables and Delete All Tutors and Coordinators</label><br><br>
				<input type="hidden" name="actionType" value="deleteEverything">
				<input type="radio" name="recreateTables" value="1" checked>Re-create tables after deletion.<br>
				<input type="radio" name="recreateTables" value="0"> Don't create any tables after deletion.<br><br>
				<input type="checkbox" name="iamsure" value="yes">I'm Sure I want to do this.<br><br>
				<input type="submit" value="Do It.">
			</form>
			<hr>
		<?php endif; ?>
	</div>
</div>