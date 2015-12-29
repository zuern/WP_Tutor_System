<?php
/*
Plugin Name: Englinks Tutor Matcher
Description: This plugin provides an interface for automatically matching students with tutors.
Version: 1.1.5
Author: Code the Change, Queen's Chapter
Author URI: http://www.queenscodethechange.com/
License: GPL2
*/
/*
Copyright 2012  Queen's Code the Change  (email : queensu@codethechange.org)

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License, version 2, as 
published by the Free Software Foundation.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/
defined("ABSPATH") or die("No Script Kiddies Please!");	// Prevents direct access to PHP file
define('ctc_plugin_dir', plugin_dir_path(__FILE__)); 	// Fetch the path of the plugin directory.

class ctc_tutor_form {

	public function __construct()
	{
		// Register plugin CSS file to make accessible to all views.
		add_action('admin_enqueue_scripts',array($this,'register_plugin_styles'));

		// Register the shortcode [ctc_tutor_form]
		include_once('pluginIncludes/setupShortcode.php');

		// Create the Menu Items in the Administrative Interface in the back end.
		include_once('pluginIncludes/buildMenus.php');

		// Register login redirects
		include_once('pluginIncludes/loginRedirect.php');

		// Register User Deletion hooks (make sure we remove any student applications a tutor claimed when the tutor is deleted)
		include_once('pluginIncludes/userdeletion.php');

	} // END public function __construct()

	public function activate()
	{
		// Create the database tables.
		include_once('pluginIncludes/installDatabase.php');

		// Register the Custom User Roles and Capabilities used to authenticate tutors and managers.
		include_once('pluginIncludes/do_roles.php');

	} // END public function activate()

	public function deactivate()
	{
		// Deactivate the plugin

	} // END public function deactivate()


	// Loads our stylesheet on all admin pages.
	public function register_plugin_styles() {
		// Register Plugin Stylesheet
		wp_register_style( 'ctc-pluginStyle', plugins_url( 'resources/style.css', __FILE__ ));
		
		//Enqueue Plugin Stylesheet
		wp_enqueue_style( 'ctc-pluginStyle' );
	}
}

// Installation and Uninstallation Hooks
register_activation_hook(__FILE__,array('ctc_tutor_form','activate'));
register_deactivation_hook(__FILE__,array('ctc_tutor_form','deactivate'));

// Create the plugin object
$the_plugin = new ctc_tutor_form();