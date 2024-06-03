<?php



// Essentials

include_once 'includes/config.php';

include_once 'includes/init.php';



// Register & Functions

include_once 'includes/register.php';

include_once 'includes/func.php';

include_once 'includes/ratings.php';

// Customizer

include_once 'includes/customizer/customizer.php';

include_once 'includes/customizer/css.php';

include_once 'includes/vibe-menu.php';

include_once 'includes/notes-discussions.php';

if ( function_exists('bp_get_signup_allowed')) {

    include_once 'includes/bp-custom.php';

}



include_once '_inc/ajax.php';



//Widgets

include_once('includes/widgets/custom_widgets.php');

if ( function_exists('bp_get_signup_allowed')) {

 include_once('includes/widgets/custom_bp_widgets.php');

}

include_once('includes/widgets/advanced_woocommerce_widgets.php');

include_once('includes/widgets/twitter.php');

include_once('includes/widgets/flickr.php');



//Misc

include_once 'includes/extras.php';

include_once 'includes/tincan.php';

include_once 'setup/wplms-install.php';



// Options Panel

get_template_part('vibe','options');



//add password field in gravity forms 

function msk_enable_password_field($is_enabled){

    return true;

}

add_action("gform_enable_password_field", "msk_enable_password_field");

function msk_gform_prepopulate_email($value){
    if (get_current_user_id()) :
        $user_data = get_userdata(get_current_user_id());
        return $user_data->user_email;
    endif;
    return '';
}
add_filter('gform_field_value_adc_user_email', 'msk_gform_prepopulate_email');


function msk_gform_prepopulate_firstname($value){
    if (get_current_user_id()) :
        $user_data = get_userdata(get_current_user_id());
        return $user_data->user_firstname;
    endif;
    return '';
}
add_filter('gform_field_value_adc_user_firstname', 'msk_gform_prepopulate_firstname');

function msk_gform_prepopulate_lastname($value){
    if (get_current_user_id()) :
        $user_data = get_userdata(get_current_user_id());
        return $user_data->user_lastname;
    endif;
    return '';
}
add_filter('gform_field_value_adc_user_lastname', 'msk_gform_prepopulate_lastname');



?>

