<?php 
defined("ABSPATH") or die("No Script Kiddies Please!");	// Prevents direct access to PHP file
include_once(ctc_plugin_dir.'models/ctc_Data_Model.php');

$success = NULL;
$message = NULL;
$messageStatus = NULL;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
	if (isset($_POST['data'])) {
		$data = explode("\n", $_POST["data"]);
		
		foreach ($data as $user) {

			$user = explode(",", $user); // Break into columns.
			$username 	= $user[0];
			$courses	= explode(';',$user[1]);

			// Fetch the user's userID
			$userID = get_user_by('login',$username);

			if ($userID != False) {
				$userID = $userID->ID;
				foreach ($courses as $code){
					if (strlen($code) == 8) {
						// Fetch the course code's ID
						$courseID = getCourseIDByCode($code);

						if ($courseID != false){	// Its in the db.

							// Set the qualification
							$result = setTutorQualification($userID,$courseID);

							if (!$result) {	// Failed to add.
								$success = False;
								$message = "Error: $username already has this qualification: $code. Continuing anyways...";
								$messageStatus = "error";
							
							}
						}
					}
					else {
						$success = False;
						$message = "Course Code Error: '$code' is not of format XXXX-XXX.";
						$messageStatus = "error";
						
					}
				}
			}
			else {
				$success = False;
				$message = "Username Error: '$username' is not in the database.";
				$messageStatus = "error";
				
			}
		}
		if ($success) {
			$message = "All data successfully saved to the db.";
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
	<h2>Bulk Add Qualifications</h2>
	<p>Use this form to add qualifications for tutors in bulk as comma seperated values.</p>
	<h3>Important!</h3>
	<p>Before using this form, first ensure that
		<ol>
			<li>all tutors in the form below are already registered in the database (i.e. can log in)</li>
			<li>all course codes in the form below are already registered in the database (i.e. listed in master course list)</li>
			<li><b>IMPORTANT: NO SPACES BETWEEN COMMAS WHEN WRITING CSV DATA IN FORM.</b></li>
		</ol>
	</p>
	<h3>Format</h3>
	<p>
		Use the following format for your csv file. Use one line per user:<br>
		<code>
		username,course1; course2; course3; course4; ...etc
		</code>
	</p>
	<form action='<?php echo $_SERVER["REQUEST_URI"]; ?>' method="POST">
		<label for="data">User Data</label>
		<br>
		<textarea name="data" id="data" cols="75" rows="20" placeholder="One user per line!"></textarea>
		<br>
		<input type="Submit">
	</form>
</div>