<?php 
defined("ABSPATH") or die("No Script Kiddies Please!");	// Prevents direct access to PHP file
include_once(ctc_plugin_dir.'models/ctc_Data_Model.php');

$success = NULL;
$message = NULL;
$messageStatus = NULL;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
	if (isset($_POST['codes'])) {
		$codes = explode("\n", $_POST["codes"]);
		
		foreach ($codes as $c) {
			global $message;
			global $success;
			global $messageStatus;
			
			//Try and add course code to db.
			if (addCourse($c) == false) {
				$message = "An error has occured trying to add at least 1 course to the db.";
				$success = false;
				$messageStatus = "error";
			}
		}
		if ($success) {
			$message = "All courses successfully added to the db.";
			$messageStatus = "updated";
		}

	}
}

?>
<div class="wrap">
	<?php if (isset($message)): ?>
		<div id="message" class="<?php echo $messageStatus; ?>">
			<p><strong><?php echo $message; ?></strong></p>
		</div>
	<?php endif; ?>
	<h2>Bulk Add Courses to Master Course List</h2>
	<p>Use this to add courses in bulk to the master course list. Add courses one per line in this format: <code>XXXX-YYY</code> where X is an uppercase letter and Y is a number (0-9).</p>
	<form action='<?php echo $_SERVER["REQUEST_URI"]; ?>' method="POST">
		<label for="codes">Course Codes</label>
		<br>
		<textarea name="codes" id="codes" cols="10" rows="30" placeholder="Paste codes here 1 per line."></textarea>
		<br>
		<input type="Submit">
	</form>
</div>