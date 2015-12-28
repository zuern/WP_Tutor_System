<?php 
defined("ABSPATH") or die("No Script Kiddies Please!");	// Prevents direct access to PHP file
 ?>
<div class="wrap">
	<h2>Code the Change Tutoring Management Plugin</h2>
	<p>This plugin makes finding a tutor much easier for students. It also reduces the workload involved in manually coordinating a student and tutor.</p>

	<?php if (current_user_can("view_Tutor_Coordinator_Menus")): include_once(ctc_plugin_dir.'models/ctc_Data_Model.php'); ?>

	<h3>Statistics:</h3>

	<table style="text-align:right;" class="ctc">
		<tr>
			<th>Metric</th>
			<th>Value</th>
		</tr>
		<?php foreach (getStatistics() as $key => $value): ?>
		<tr>
			<td style="font-weight:bold; font-size:15px;"><?php echo $key; ?></td>
			<td><?php echo $value; ?></td>
		</tr>
		<?php endforeach; ?>
	</table>

	<?php endif; ?>

	<?php if (current_user_can("can_see_Tutoring_System_Menu")): ?>
		<h3>Using this Plugin</h3>
		<p>
			<ol>
				<?php if (current_user_can( "create_users" )): ?>
				
				<li>Create a user account for each coordinator. They should have their 'Role' set to 'Tutor Coordinator'.</li>
				<li>Create a user account for each Tutor, and set their 'Role' to 'Tutor'. This will enable them to log into the website and view our interface for finding new students to tutor.</li>
				<li>Have a coordinator log in and continue the set up.</li>
				<li>Once the coordinator is happy with his/her set up, create a page to hold the submission form, and place this shortcode: <code>[ctc_tutor_form]</code> where ever you want a submission form to be shown.</li>
				<?php endif; ?>
				<?php if (current_user_can( "view_Tutor_Coordinator_Menus" )): ?>
				<li>Fill in the master list of courses with course codes that you want students to apply for tutoring in.</li>
				<li>Update each tutor's qualifications to set which courses they are qualified to teach.</li>
				<li>Once this is done, tutors will only see applications for tutoring from students that they are qualified to teach.</li>
				<li>Update the master list, and qualifications as needed afterwards.</li>
				<?php endif; ?>
				<?php if (current_user_can( "view_Tutor_Menus" )): ?>
				<li>Click on "Find Students" to view any pending applications made by students.</li>
				<li>In the "Find Students" page, you will be shown applications sorted by the courses you teach.</li>
				<li>Click "Contact Student" to email the student and arrange tutoring.</li>
				<li><b>IMPORTANT:</b>&nbsp; Once you have arranged tutoring with a student, ensure to mark that you are tutoring this student on their application.</li>
				<?php endif; ?>
			</ol>
		</p>
		<hr>
	<?php endif; ?>
	<h4>Credits</h4>
	<p><i>This plugin was made with &#9829; by <b>Code the Change</b> for EngLinks.</i></p>
	<p>
		<b>The Developers:</b>
		<ol>
			<li><a href="http://kevinzuern.com">Kevin Zuern (Project Manager)</a></li>
			<li>Anna Ilina (Project Manager)</li>
			<li>Matthew Pollack</li>
			<li>Daniel Lucia</li>
			<li>Jerry Mak</li>
			<li>Prajjwol Mondal</li>
			<li>Austin Attah</li>
			<li>Lucas Bullen</li>
		</ol>
	</p>
	<a href="http://queenscodethechange.com">
		<img src="<?php echo plugins_url( '../images/logo.png', dirname(__FILE__) ); ?>" alt="Code the Change, Queen's Chapter" style="max-height:150px;">
	</a>
</div>