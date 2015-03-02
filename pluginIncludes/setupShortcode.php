<?php 

//[ctc_tutor_form]
function ctc_tutor_form_func(){
	 include_once(ctc_plugin_dir.'views/misc/submitTutoringRequestForm.php');
}

add_shortcode("ctc_tutor_form", "ctc_tutor_form_func" );

 ?>