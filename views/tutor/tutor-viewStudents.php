<?php
defined("ABSPATH") or die("No Script Kiddies Please!");	// Prevents direct access to PHP file

include_once(ctc_plugin_dir.'models/ctc_Data_Model.php');
wp_enqueue_script( 'jquery-ui-accordion' );

$message = NULL;
$messageStatus = NULL;

if ($_SERVER["REQUEST_METHOD"] == "POST") {

	$applicationID 	= intval($_POST["applicationID"]);

	$application 	= getApplication($applicationID);

	$CourseID 		= NULL;

	if (isset($application)) {

		$CourseID = $application["Course_ID"];

	}


	// Make sure tutor is qualified to teach this student.

	if (isset($CourseID) && canTeachCourse(getCurrentUserID(),$CourseID)) {
		
		$succeeded = claimApplication(getCurrentUserID(),$applicationID);
		$name = $application["Name"];
		$message = "You are now tutoring $name!";
		$messageStatus = "updated";
	}
	else {
		echo "<script>alert('You are not qualified to teach this student.');</script>";
	}

}

$hasQualifications = tutorHasQualifications(getCurrentUserID());

// Grab all the applications this user is qualified to teach.
$applications = getApplications(getCurrentUserID());

// Sort the applications by course code.
$studInEachCourse = sortApplicationsByCourse($applications);

?>

<div class="wrap">
	<div class="container-fluid">
		<h2>Students In Need Of A Tutor</h2>
		<?php if (isset($message)): ?>
		<div id="message" class="<?php echo $messageStatus; ?>">
			<p><strong><?php echo $message; ?></strong></p>
		</div>
		<?php endif; ?>
		
		<?php if ($hasQualifications && sizeof($studInEachCourse) > 0): ?>
			<p>
				Below are all the different students who need your help. Follow these steps please:
			</p>
			<ol>
				<li>Contact a potential student via their email address, and coordinate tutoring.</li>
				<li>Confirm they are taking you on as a tutor.</li>
				<li>Come back to this page and click the "Claim" button.</li>
			</ol>
			<ul>
				<li><span class="okay">&nbsp;</span> - Normal Priority</li>
				<li><span class="priority">&nbsp;</span> - High Priority (5+ days old)</li>
			</ul>
			<table class="ctc">
				<tr>
					<th></th>
					<th>Course</th>
					
					<th>Name</th>
					<th>Email</th>
					<th>Comments</th>
					<th>Application Date</th>
					<th>Tutor</th>
				</tr>
				<?php foreach ($applications as $application): ?>
					<tr>
					 	<td>
							<?php 
								$status = 'okay';
								if ($application["Application_Age"] >= 5) {
									$status = 'priority';
								}
							 ?>
							 <span class="<?php echo $status; ?>">&nbsp;</span>
				 		</td>
						<td><?php echo $application["Course"]; ?></td>
						<td><?php echo esc_html($application["Name"]); ?></td>
						<td><a href='mailto:<?php echo esc_html($application["Email"]);?>'><?php echo esc_html($application["Email"]); ?></a></td>
						<td><?php echo esc_html($application["Comments"]); ?></td>
						<td><?php echo $application["SubmitDate"]; ?></td>
						<td>
							<form action="<?php echo $_SERVER['REQUEST_URI']; ?>" method="POST">
								<input type="hidden" name="applicationID" value="<?php echo $application['ID']; ?>">
								<input type="submit" value="Claim">
							</form>
						</td>
					</tr>
				<?php endforeach; ?>
			</table>
		<?php elseif (!$hasQualifications): ?>
			<p style="color:red;">You do not yet have any qualifications. Contact your coordinator/admin, and have them list the courses you are qualified to teach.</p>
		<?php else: // If there are no students needing tutoring ?>
			<h3>It looks lonely in here!</h3>
			<p>It seems like there are no current applications that you could look at. Please check back in later!</p>
			
		<?php endif; // No applications ?>
	</div>
</div>

 <script type="text/javascript">

 	jQuery(document).ready(function(){
 		jQuery('.accordion').accordion({heightStyle: "content"});
 	});

 </script>