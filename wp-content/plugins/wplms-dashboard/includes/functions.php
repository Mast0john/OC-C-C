<?php

function wplms_get_random_color($i=NULL){
$color_array = array(
		'#7266ba',
 		'#23b7e5',
 		'#f05050',
 		'#fad733',
 		'#27c24c',
 		'#fa7252'
	);
if(isset($i)){
	if(isset($color_array[$i]))
	return $color_array[$i];
}
$k = array_rand($color_array);
return $color_array[$k];
}

function wplms_dashboard_template() {

	if(!is_user_logged_in())
		wp_redirect(site_url());

	$template ='templates/dashboard';
	wp_enqueue_style( 'wplms-dashboard-css', plugins_url( '../css/wplms-dashboard.css' , __FILE__ ));
	wp_enqueue_script( 'wplms-dashboard-js', plugins_url( '../js/wplms-dashboard.js' , __FILE__ ),array('jquery'));

	$located_template = apply_filters( 'bp_located_template', locate_template( $template , false ), $template );	
	if ( $located_template && $located_template !='' )	{
		bp_get_template_part( apply_filters( 'bp_load_template', $located_template ) );
	}else{
	    bp_core_load_template( apply_filters( 'bp_core_template_plugin', 'members/single/dashboard' ) );
	}
}


add_action('widgets_init','wplms_dashboard_setup_sidebars');
function wplms_dashboard_setup_sidebars(){
if(function_exists('register_sidebar')){
	register_sidebar( array(
		'name' => __('Student Sidebar','wplms-dashboard'),
		'id' => 'student_sidebar',
		'before_widget' => '<div id="%1$s" class="%2$s">',
		'after_widget' => '</div>',
		'before_title' => '<h4 class="dash_widget_title">',
		'after_title' => '</h4>',
        'description'   => __('This is the dashboard sidebar for Students','wplms-dashboard')
	) );
	register_sidebar( array(
		'name' => __('Instructor Sidebar','wplms-dashboard'),
		'id' => 'instructor_sidebar',
		'before_widget' => '<div id="%1$s" class="%2$s">',
		'after_widget' => '</div>',
		'before_title' => '<h4 class="dash_widget_title">',
		'after_title' => '</h4>',
        'description'   => __('This is the dashboard sidebar for Instructors','wplms-dashboard')
	) );
    }
}


?>