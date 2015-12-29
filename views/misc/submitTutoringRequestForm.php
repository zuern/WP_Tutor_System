<?php 
	defined("ABSPATH") or die("No Script Kiddies Please!"); // Prevents direct access to PHP file
	include_once(ctc_plugin_dir.'models/ctc_Data_Model.php');
?>
<style>
	div.smallText {
		font-size:12px;
	}
	span.underline {
		text-decoration: underline;
	}
	span.extrainfo {
		display:block;
		font-size:12px;
	}
	label {
		display:block;
		margin-top:10px;
	}
	input:required:after {
		content:'*';
		color:red;
		margin-left:5px;
	}

	div.errormessages {
		color:red;
	}

	.okay {
		background-color: rgb(140,250,140);
		display: inline-block;
		width: 5px;
		height: 20px;
	}

	#counter {
		display: block;
		float: right;
		background-color: white;
		padding: 0px 5px;
	}

	#submit {
		margin-top:20px;
		padding:10px 25px;
		background-color:#24890D;
		color:white;
		text-align: center;
	}
	#submit:hover {
		background-color: #41a62a;
	}
</style>
<?php 
	
	$commentsCharLimit = 1024;	// Maximum number of characters permitted in the comments field. (Limit imposed by database).
	
	/*
	*	
	*	Variables to hold form data after POST (in case they need to fix something)
	*	
	*/
	
	$fullName = "";
	$email = "";
	$courseID = NULL;
	$tutoringFrequency = NULL;
	$comments = "";
	
	/*
	*
	*	 Handle POST data if POST occured.
	*	
	*/
	$noErrors = true;
	$errors = array();	// Will hold an array of error messages to be output to the browser if needed.
	$successfulInsert = NULL;

	if ($_SERVER["REQUEST_METHOD"] == "POST") {
	
		if (empty($_POST["fullName"])) {
			$errors[] = "Name is required";
			$noErrors=false;
		} else{
			$fullName = test_input($_POST["fullName"]);
            //confirm only valid characters are present (a-z, A-Z, and ',')
			if (preg_match("/[^a-zA-Z\s,]+/",$fullName)){
				$errors[] = "Name: Only letters, commas, and white space allowed";
				$noErrors=false;
			}
		}
		if (empty($_POST["email"])) {
			$errors[]="Email is required";
			$noErrors=false;
		} else{
			$email = test_input($_POST["email"]);
            //confirm that valid queens email
			if(substr($email,-strlen("@queensu.ca"))!="@queensu.ca" || !filter_var($email, FILTER_VALIDATE_EMAIL)){
				$errors[] = "Email must be a valid Queen's Email (NetID@queensu.ca)"; 
				$noErrors=false;
			}
		}
		if (empty($_POST["course"]) || $_POST["course"]=="-1") {
			$errors[] ="Must select a course code";
			$noErrors=false;
		} else{
			$courseID = intval(test_input($_POST["course"]));
		}
		if (empty($_POST["frequency"]) == "-1") {
			$errors[] = "Must select a tutoring frequency.";
			$noErrors = false;
		}
		else {
			$tutoringFrequency = intval(test_input(($_POST["frequency"])));
		}
		if (!empty($_POST["comments"])) {
			$comments = test_input($_POST["comments"]);
			if(strlen($comments)>$commentsCharLimit){
				$errors[]="Comments must be less than $commentsCharLimit characters";
				$noErrors=false;
			}
		}

		if (isset($noErrors) && $noErrors){
			// Add our application to the database. If we have any errors, the error messages will be returned.
			$errors = addApplication($fullName,$email,$courseID,$tutoringFrequency,$comments);
			$successfulInsert = count($errors) == 0; // If we have no errors, then we successfully inserted into the db.
		}
	}
	function test_input($data) {
		$data = trim($data);
		$data = stripslashes($data);
		$data = htmlspecialchars($data);
		return $data;
	}
?>
<form action="<?php echo $_SERVER['REQUEST_URI']; ?>" method="post">

<?php if (isset($successfulInsert) && $successfulInsert == true): ?>
	<h4><span class="okay">&nbsp;</span> Success</h4>
	<p>Your application was successfully submitted!</p>
<?php endif; ?>

<?php if (sizeof($errors) > 0): ?>
	<div class="errormessages">
		<h4>Errors:</h4>
		<ul>
			<?php foreach ($errors as $error): ?>
			<li>
				<?php echo $error; ?>
			</li>
			<?php endforeach; ?>
		</ul>
	</div>
<?php endif; ?>

<label for="fullName">Full Name <span class="extrainfo">(First, Last)</span></label>
<input type="text" name="fullName" value="<?php echo $fullName; ?>" required>

<label for="email">Email <span class="extrainfo">(NetID@queensu.ca)</span></label>
<input type="email" name="email" value="<?php echo $email; ?>" required>

<label for="course">Select your course.</label>
<select name="course">
	<option value="-1" <?php if (!isset($courseID)) {echo 'selected'; } ?>>Select A Course</option>
	<?php foreach (getCourseList(NULL,True) as $course): //Get all available courses?>
	<option value="<?php echo $course['id']; ?>" <?php if (isset($courseID) && $courseID == $course["id"]) {echo 'selected'; } ?>><?php echo $course['code']; ?></option>
	<?php endforeach; ?>
</select>

<label for="frequency">How often do you require tutoring?</label>
<select name="frequency">
	<option value="-1"<?php if (!isset($tutoringFrequency)) {echo 'selected'; } ?>>Select Frequency</option>
	<option value="1">Just Once</option>
	<option value="2">Weekly</option>
	<option value="3">Bi-Weekly</option>
	<option value="4">Monthly</option>
	<option value="5">Bi-Monthly</option>
</select>

<label for="comments">Comments <span class="extrainfo">(Mention how long you need tutoring, your availabilty, etc.)</span></label>
<textarea id="comments" name="comments" rows="5" cols="40" onkeyup="textCounter(this,'counter');"><?php if (isset($comments)) {echo $comments; } ?></textarea>
<input disabled  maxlength="4" size="4" id="counter">

<input id="submit" type="submit" name="submit" value="Submit" />

</form>

<a href="<?php echo wp_login_url(); ?>">Tutors Log In</a>


<?php // Script to display remaining characters in comments box ?>

<script>
var maxlimit = <?php echo $commentsCharLimit; ?>;
// On load, set the counter to the number of characters in the comments box.
var countfield 		 =  document.getElementById('counter');
var commentsboxchars =  document.getElementById('comments').value.length;

if (commentsboxchars > 0) {
	countfield.value = maxlimit - document.getElementById('comments').value.length;
}
else {
	countfield.value = maxlimit;
}
function textCounter(field,field2)
{
	var countfield = document.getElementById(field2);
	if ( field.value.length > maxlimit ) {
		field.value = field.value.substring( 0, maxlimit );
		return false;
	} else {
		countfield.value = maxlimit - field.value.length;
	}
}
</script>