<?php 

//[ctc_tutor_form]
function ctc_tutor_form_func(){
	ob_start();
	include_once(ctc_plugin_dir.'views/misc/submitTutoringRequestForm.php');
	return ob_get_clean();
}

add_shortcode("ctc_tutor_form", "ctc_tutor_form_func" );

 ?>