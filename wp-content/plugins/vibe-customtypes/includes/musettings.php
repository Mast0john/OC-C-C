<?php

include_once('commissions/wplms_commissions_class.php');
include_once('class.export.php');
include_once('class.import.php');

function vibe_lms_settings() {
    $tab = isset( $_GET['tab'] ) ? $_GET['tab'] : 'general';
	lms_settings_tabs($tab); 
	get_lms_settings($tab);

}


function lms_settings_tabs( $current = 'general' ) {
    $tabs = array( 
    		'general' => __('General','vibe-customtypes'), 
    		'set_commission' => __('Set Commissions','vibe-customtypes'), 
    		'pay_commission' => __('Pay Commissions','vibe-customtypes'), 
    		'functions' => __('Admin Functions','vibe-customtypes'),
    		'import-export' => __('Import/Export','vibe-customtypes'),
    		);
    echo '<div id="icon-themes" class="icon32"><br></div>';
    echo '<h2 class="nav-tab-wrapper">';
    foreach( $tabs as $tab => $name ){
        $class = ( $tab == $current ) ? ' nav-tab-active' : '';
        echo "<a class='nav-tab$class' href='?page=lms-settings&tab=$tab'>$name</a>";

    }
    echo '</h2>';
}

function get_lms_settings($tab){
	if(isset($_POST['save']))
				lms_save_settings($tab);

	switch($tab){
		case 'pay_commission': 
			lms_commission_payments();
		break;
		case 'set_commission': 
			lms_commission_settings();
		break;
		case 'commission_history':
			lms_commission_history();
		break;
		case 'instructor':
			lms_instructor_settings();
		break;
		case 'functions':
			lms_resolve_adhoc_function();
			lms_functions();
		break;
		case 'import-export':
			lms_import_export();
		break;
		default:
			lms_general_settings();
		break;
	}
}

function lms_save_settings($tab){
	if ( !empty($_POST) && check_admin_referer('vibe_lms_settings','_wpnonce') ){
		$lms_settings=array();
		$lms_settings = get_option('lms_settings');

		unset($_POST['_wpnonce']);
		unset($_POST['_wp_http_referer']);
		unset($_POST['save']);
		switch($tab){
			case 'instructor':
				$lms_settings['instructor'] = $_POST;
			break;
			case 'student':
				$lms_settings['student'] = $_POST;
			break;
			case 'functions':
				lms_functions();
			break;
			default:
				$lms_settings['general'] = $_POST;
			break;
		}
		update_option('lms_settings',$lms_settings);
	}
}

function lms_general_settings(){

	echo '<h3>'.__('LMS General Settings','vibe-customtypes').'</h3>';
	
	$settings=array(
	array(
			'label' => __('Student Login redirect','vibe-customtypes'),
			'name' =>'student_login_redirect',
			'type' => 'select',
			'options'=>array(
				'home' => __('Home page','vibe-customtypes'),
				'profile' => __('Profile page','vibe-customtypes'),
				'mycourses'=> __('My Courses page','vibe-customtypes'),
				'dashboard'=> __('Dashboard page','vibe-customtypes'),
				),
			'desc' => __('Default is home page','vibe-customtypes')
		),
	array(
			'label' => __('Instructor Login redirect','vibe-customtypes'),
			'name' =>'instructor_login_redirect',
			'type' => 'select',
			'options'=>array(
				'home' => __('Home page','vibe-customtypes'),
				'profile' => __('Profile page','vibe-customtypes'),
				'mycourses'=> __('My Courses page','vibe-customtypes'),
				'instructing_courses'=> __('Instructing Courses page','vibe-customtypes'),
				'dashboard'=> __('Dashboard page','vibe-customtypes'),
				),
			'desc' => __('Default is home page','vibe-customtypes')
		),
	array(
			'label' => __('Hide Members section in Single Course page','vibe-customtypes'),
			'name' =>'hide_course_members',
			'type' => 'checkbox',
			'desc' => __(' Hides member section in course pages','vibe-customtypes')
		),
	array(
			'label' => __('Show curriculum below Course description','vibe-customtypes'),
			'name' =>'course_curriculum_below_description',
			'type' => 'checkbox',
			'desc' => __('Show curriculum below course description','vibe-customtypes')
		),
	array(
			'label' => __('Course Timeline Accordion style','vibe-customtypes'),
			'name' =>'curriculum_accordion',
			'type' => 'checkbox',
			'desc' => __('Show curriculum accordion style','vibe-customtypes')
		),
	array(
			'label' => __('Enable Unit/Quiz Start Date time','vibe-customtypes'),
			'name' =>'unit_quiz_start_datetime',
			'type' => 'checkbox',
			'desc' => __('Units and Quizzes start at a particular date and time','vibe-customtypes')
		),
	array(
			'label' => __('Enable One session per user','vibe-customtypes'),
			'name' => 'one_session_per_user',
			'type' => 'checkbox',
			'desc' => __('A User can login from one unique user id (excludes administrators)','vibe-customtypes')
		),
	array(
			'label' => __('Hide Administrators in Instructors','vibe-customtypes'),
			'name' =>'admin_instructor',
			'type' => 'checkbox',
			'desc' => __('Hide Administrator in all instructors page & elsewhere','vibe-customtypes')
		),
	array(
            'label' => __('Enable Course Codes', 'vibe'),
            'name' => 'course_codes',
            'desc' => __('Student can purchase/access courses by using custom defined codes for courses in course pricing section', 'vibe'),
            'type' => 'checkbox',
		),
	array(
            'label' => __('Show WooCommerce/Pmpro account in profile', 'vibe'),
            'name' => 'woocommerce_account',
            'desc' => __('Display WooCommerce account in profile', 'vibe'),
            'type' => 'checkbox',
		),
	array(
            'label' => __('Enable Front end course deletion', 'vibe'),
            'name' => 'wplms_course_delete',
            'desc' => __('Instructors will be able to delete course and related content from front end', 'vibe'),
            'type' => 'checkbox',
		),
	array(
            'label' => __('Disable Auto allocation of Free courses', 'vibe'),
            'name' => 'disable_autofree',
            'desc' => __('Disables auto allocation of free courses', 'vibe'),
            'type' => 'checkbox',
		),
	array(
			'label' => __('Default order in course directory','vibe-customtypes'),
			'name' =>'default_order',
			'type' => 'select',
			'options'=>array(
				'date' => __('Recent','vibe-customtypes'),
				'title' => __('Alphabetical','vibe-customtypes'),
				'popular' => __('Number of Students','vibe-customtypes'),
				'rated' => __('Rating','vibe-customtypes'),
				),
			'desc' => __('Default is menu order','vibe-customtypes')
		),
	array(
			'label' => __('Limit Number of Courses per Instructor','vibe-customtypes'),
			'name' =>'course_limit',
			'type' => 'number',
			'desc' => __('( 0 for unlimited course per instructor )','vibe-customtypes')
		),
	array(
		'label' => __('Limit Number of Units Created per Instructor','vibe-customtypes'),
		'name' =>'unit_limit',
		'type' => 'number',
		'desc'=>__(' ( 0 for unlimited )','vibe-customtypes')
		),
	array(
		'label' => __('Limit Number of Quiz Created per Instructor ','vibe-customtypes'),
		'name' =>'quiz_limit',
		'type' => 'number',
		'desc' =>__('(0 for unlimited course per instructor )','vibe-customtypes'),
		),
	);

	lms_settings_generate_form('general',$settings);

}


function limit_courses_per_month($monthly_limit){
	if(!$monthly_limit)
		return;
	//Limit posts per month
    $time_in_days = 30; // 1 means in last day
    $count = $wpdb->get_var(
        $wpdb->prepare("
            SELECT COUNT(*) 
            FROM $wpdb->posts 
            WHERE post_status = 'publish' 
            AND post_type = %s 
            AND post_author = %s
            AND post_date >= DATE_SUB(CURDATE(),INTERVAL %s DAY)",
            'course',
            get_current_user_id(),
            $time_in_days
        )
    );
    if ( 0 < $count ) 
    $count = number_format( $count );

    if ( $monthly_limit <=$count ) {
         $errors[] = __('You have reached your monthly post limit','vibe-customtypes');
    }
}



function lms_functions(){
	do_action('wplms_admin_custom_admin_panel');
	echo '<h3>'.__('LMS Admin Functions [ For Ad-Hoc Management]','vibe-customtypes').'</h3>';
	echo '<form method="post"><ul class="lms-settings">';
	echo '<li><label>'.__('Custom Field Value','vibe-customtypes').'</label><input type="text" name="id" placeholder="ID"><input type="text" name="field_name" placeholder="Field Name"><input type="text" name="field_value" placeholder="Field Value"><input type="submit" name="set_field" class="button button-primary" value="Set Field" />';
	echo '<li><label>'.__('Custom Field for Student Value','vibe-customtypes').'</label><input type="text" name="student_id" placeholder="Student ID"><input type="text" name="field_name_student" placeholder="Field Name"><input type="text" name="field_value_student" placeholder="Field Value"><input type="submit" name="set_field_for_student" class="button button-primary" value="Set Field" />';
	echo '<li><label>'.__('Current Time Stamp ','vibe-customtypes').'</label><span>'.time().'</span></li>';
	wp_nonce_field('vibe_admin_adhoc','_vibe_admin_adhoc');
	echo '</ul></form>';
	
}

function lms_settings_generate_form($tab,$settings=array()){
	echo '<form method="post">';
	wp_nonce_field('vibe_lms_settings','_wpnonce');   
	echo '<ul class="lms-settings">';
	$lms_settings=get_option('lms_settings');

	foreach($settings as $setting ){
		echo '<li>';
		switch($setting['type']){
			case 'textarea':
				echo '<label>'.$setting['label'].'</label>';
				echo '<textarea name="'.$setting['name'].'">'.(isset($lms_settings[$tab][$setting['name']])?$lms_settings[$tab][$setting['name']]:'').'</textarea>';
				echo '<span>'.$setting['desc'].'</span>';
			break;
			case 'select':
				echo '<label>'.$setting['label'].'</label>';
				echo '<select name="'.$setting['name'].'" class="chzn-select">';
				foreach($setting['options'] as $key=>$option){
					echo '<option value="'.$key.'" '.(isset($lms_settings[$tab][$setting['name']])?selected($key,$lms_settings[$tab][$setting['name']]):'').'>'.$option.'</option>';
				}
				echo '</select>';
				echo '<span>'.$setting['desc'].'</span>';
			break;
			case 'checkbox':
				echo '<label>'.$setting['label'].'</label>';
				echo '<input type="checkbox" name="'.$setting['name'].'" '.(isset($lms_settings[$tab][$setting['name']])?'CHECKED':'').' />';
				echo '<span>'.$setting['desc'].'</span>';
			break;
			case 'number':
				echo '<label>'.$setting['label'].'</label>';
				echo '<input type="number" name="'.$setting['name'].'" value="'.(isset($lms_settings[$tab][$setting['name']])?$lms_settings[$tab][$setting['name']]:'').'" />';
				echo '<span>'.$setting['desc'].'</span>';
			break;
			case 'hidden':
				echo '<input type="hidden" name="'.$setting['name'].'" value="1"/>';
			break;
			default:
				echo '<label>'.$setting['label'].'</label>';
				echo '<input type="text" name="'.$setting['name'].'" value="'.(isset($lms_settings[$tab][$setting['name']])?$lms_settings[$tab][$setting['name']]:'').'" />';
				echo '<span>'.$setting['desc'].'</span>';
			break;
		}
		
		echo '</li>';
	}
	echo '</ul>';
	echo '<input type="submit" name="save" value="'.__('Save Settings','vibe-customtypes').'" class="button button-primary" /></form>';
}


// Functioning ===== of SETTINGS
function lms_resolve_adhoc_function(){
	if ( !isset($_POST['_vibe_admin_adhoc']) || !wp_verify_nonce($_POST['_vibe_admin_adhoc'],'vibe_admin_adhoc') )
	 return;
	else{
		do_action('wplms_admin_custom_admin_process');
		if(isset($_POST['set_field'])){
			$id=$_POST['id'];
			$field_name=$_POST['field_name'];
			$field_value=$_POST['field_value'];
			if(isset($id)){
				if(update_post_meta($id,$field_name,$field_value))
					echo '<div id="moderated" class="updated below-h2"><p>'.__('Field Value Changed','vibe-customtypes').'</p></div>';
				else
					echo '<div id="moderated" class="error below-h2"><p>'.__('Error Field value not changed','vibe-customtypes').'</p></div>';
			}else{
				echo '<div id="moderated" class="error below-h2"><p>'.__('Error Field value not entered','vibe-customtypes').'</p></div>';
			}
		}
		if(isset($_POST['set_field_for_student'])){
			$student_id=$_POST['student_id'];
			$field_name=$_POST['field_name_student'];
			$field_value=$_POST['field_value_student'];
			if(strpos($field_value,'|')){
				$field_value=explode('|',$field_value);
			}

			if(isset($student_id)){
				if(update_user_meta($student_id,$field_name,$field_value))
					echo '<div id="moderated" class="updated below-h2"><p>'.__('Student Value Changed','vibe-customtypes').'</p></div>';
				else
					echo '<div id="moderated" class="error below-h2"><p>'.__('Student value not changed','vibe-customtypes').'</p></div>';
			}else{
				echo '<div id="moderated" class="error below-h2"><p>'.__('Student value not entered','vibe-customtypes').'</p></div>';
			}
		}
	}
}


add_action( 'admin_head-post-new.php', 'check_course_limit' );
function check_course_limit() {

	$lms_settings=get_option('lms_settings');

	if(!isset($lms_settings) || !is_array($lms_settings))
		return;

    global $userdata;
    global $post_type;
    

    global $wpdb;

    
    if(in_array('instructor',$userdata->roles)){
		if( $post_type === 'course' && isset($lms_settings['general']['course_limit']) && $lms_settings['general']['course_limit']) {
			$course_count = $wpdb->get_var( "SELECT count(*) FROM $wpdb->posts WHERE post_status = 'publish' AND post_type = 'course' AND post_author = $userdata->ID" );
			if( $course_count >= $lms_settings['general']['course_limit'] ) { wp_die( "Course Limit Exceeded" ); }
		} elseif( $post_type === 'unit' && isset($lms_settings['general']['unit_limit']) && $lms_settings['general']['unit_limit']) {
			$unit_count = $wpdb->get_var( "SELECT count(*) FROM $wpdb->posts WHERE post_status = 'publish' AND post_type = 'unit' AND post_author = $userdata->ID" );
			if( $unit_count >= $lms_settings['general']['unit_limit'] ) { wp_die( "Unit Limit Exceeded" ); }
		} elseif( $post_type === 'quiz' && isset($lms_settings['general']['quiz_limit']) && $lms_settings['general']['quiz_limit']) {
			$quiz_count = $wpdb->get_var( "SELECT count(*) FROM $wpdb->posts WHERE post_status = 'publish' AND post_type = 'quiz' AND post_author = $userdata->ID" );
			if( $quiz_count >= $lms_settings['general']['quiz_limit'] ) { wp_die( "Quiz Limit Exceeded" ); }
		}
	}
	return;
}

function lms_commission_settings(){
	echo '<h3>'.__('Set Instructor Commisions','vibe-customtypes').'</h3>';

	if(isset($_POST['set_commission'])){
		if(update_option('instructor_commissions',$_POST['commission']))
			echo '<div id="moderated" class="updated below-h2"><p>'.__('Instructor Commissions Saved','vibe-customtypes').'</p></div>';
		else
			echo '<div id="moderated" class="error below-h2"><p>'.__('Instructor Commissions not saved, contact Site-Admin !','vibe-customtypes').'</p></div>';
		$commission = $_POST['commission'];
	}else{
		$commission = get_option('instructor_commissions');
	}

	$courses = get_posts('post_type=course&posts_per_page=-1');
	
	echo '<form method="POST"><div class="postbox instructor_info">
					<h3><label>'.__('Course Name','vibe-customtypes').'</label><span>'.__('Instructor','vibe-customtypes').'</span><span>'.__('PERCENTAGE','vibe-customtypes').'</span></h3>
					<div class="inside">
						<ul>';
	foreach($courses as $course){
			$instructors=apply_filters('wplms_course_instructors',$course->post_author,$course->ID);
			$cval=array();
			if(isset($commission) && is_array($commission)){
				
				if(isset($instructors) && is_array($instructors)){
					foreach($instructors as $k=>$instructor){
						$cval[$k] = $commission[$course->ID][$instructor];	
					}
				}else{
						$val = $commission[$course->ID][$course->post_author];	
				}
			}else
		 		$val=0;

		 	if(isset($instructors) && is_array($instructors)){
				foreach($instructors as $k=>$instructor){
					echo '<li><label>'.$course->post_title.'</label><span>'.get_the_author_meta('display_name',$instructor).'</span><span><input type="number" name="commission['.$course->ID.']['.$instructor.']" class="small-text" value="'.$cval[$k].'" /></span></li>';
				}	
			}else	
				echo '<li><label>'.$course->post_title.'</label><span>'.get_the_author_meta('display_name',$course->post_author).'</span><span><input type="number" name="commission['.$course->ID.']['.$course->post_author.']" class="small-text" value="'.$val.'" /></span></li>';
	}

	echo '</ul>
					</div>
				</div>
				<input type="submit" class="button-primary" name="set_commission" value="'.__('Set Commisions','vibe-customtypes').'">
		   </form>';
}

function lms_commission_payments(){
	echo '<h3>'.__('Pay Instructor Commisions','vibe-customtypes').'</h3>';

	
	if(isset($_POST['set_time'])){
		$start_date=$_POST['start_date'];
		$end_date=$_POST['end_date'];
	}
	
	if(isset($_POST['payment_complete'])){
		$post = array();
		$post['post_title'] = sprintf(__('Commission Payments on %s','vibe-customtypes'),date('Y-m-d H:i:s'));
		$post['post_status'] = 'publish';
		$post['post_type'] = 'payments';
		$post_id = wp_insert_post( $post, $wp_error );
		if(isset($post_id) && $post_id){
			update_post_meta($post_id,'vibe_instructor_commissions',$_POST['instructor']);
			update_post_meta($post_id,'vibe_date_from',$_POST['start_date']);
			update_post_meta($post_id,'vibe_date_to',$_POST['end_date']);
			echo '<div id="moderated" class="updated below-h2"><p>'.__(' Commission Payments Saved','vibe-customtypes').'</p></div>';
		}else
			echo '<div id="moderated" class="error below-h2"><p>'.__('Commission payments not saved !','vibe-customtypes').'</p></div>';
	}

	
	echo '<form method="POST" name="payment">';
	$posts = get_posts( array ('post_type'=>'payments', 'orderby' => 'date','order'=>'DESC', 'numberposts' => '1' ) );
	foreach($posts as $post){
		$date=$post->post_date;
		$id=$post->ID;
	}
	if(isset($date))
	echo '<strong>LAST PAYMENT : '.date("G:i | D , M j Y", strtotime($date)).'</strong> <a href="'.get_edit_post_link( $id ).'" class="small_link">'.__('CHECK NOW','vibe-customtypes').'</a><br /><br />';
		
	if(!isset($start_date))
		$start_date =  date('Y-m-d', strtotime( date('Ym', current_time('timestamp') ) . '01' ) );
	if(!isset($end_date))
		$end_date = date('Y-m-d', current_time( 'timestamp' ) );	

	echo '<strong>'.__('SET TIME PERIOD','vibe-customtypes').' :</strong><input type="text" name="start_date" id="from" value="'.$start_date.'" class="date-picker-field">
				 <label for="to">&nbsp;&nbsp; To:</label> 
				<input type="text" name="end_date" id="to" value="'.$end_date.'" class="date-picker-field">
				<input type="submit" class="button" name="set_time" value="Show"></p>';

	if(isset($_POST['set_time'])){	

	

	echo '<div class="postbox instructor_info">
					<h3><label>'.__('Instructor Name','vibe-customtypes').'</label><span>'.__('Commission','vibe-customtypes').' ('.get_woocommerce_currency_symbol().')</span><span>'.__('PAYPAL EMAIL','vibe-customtypes').'</span><span>'.__('Select','vibe-customtypes').'</span><span>'.__('Pay via PayPal','vibe-customtypes').'</span></h3>
					<div class="inside">
						<ul>';

				$order_data = new WPLMS_Commissions;
				$instructor_data=$order_data->instructor_data($start_date,$end_date);

				$instructors = get_users('role=instructor');		
				foreach ($instructors as $instructor) {
					        echo '<li><label>'. $instructor->user_nicename.'</label>
					        <span><input type="number" id="'.$instructor->user_login.'_amount" name="instructor['.$instructor->ID.'][commission]" class="text" value="'.(isset($instructor_data[$instructor->ID])?$instructor_data[$instructor->ID]:0).'" /></span>
					        <span><input type="text" id="'.$instructor->user_login.'_email" name="instructor['.$instructor->ID.'][email]"  value="' . $instructor->user_email . '" /></span>
					        <span><input type="checkbox" name="instructor['.$instructor->ID.'][set]" class="checkbox" value="1" /></span>
					        <span>
					        <a id="'.$instructor->username.'_payment" class="button">'.__('Pay via PayPal','vibe-customtypes').'</a>
					        
					        </span></li>';
					    }	
				   echo '</ul>
					</div>
				</div>
				<input type="submit" class="button-primary" name="payment_complete" value="'.__('Mark as Paid','vibe-customtypes').'">
				<script>
		        	jQuery(document).ready(function($){
		        		$("#'.$instructor->username.'_payment").click(function(){
		        			var amount =$("#'.$instructor->user_login.'_amount").val();
		        			var email =$("#'.$instructor->user_login.'_email").val();
		        			$(\'<form name="_xclick" action="https://www.paypal.com/in/cgi-bin/webscr" method="post" target="_blank"><input type="hidden" name="cmd" value="_xclick"><input type="hidden" name="business" value="\'+email+\'"><input type="hidden" name="currency_code" value="'.get_woocommerce_currency().'"><input type="hidden" name="item_name" value="'.__('Instructor Commission','vibe-customtypes').'"><input type="hidden" name="amount" value="\'+amount+\'"></form>\').appendTo($(this)).submit();
		        		});
		        	});
		        </script>		
		   ';	
	}	  

	echo '</form>'; 			
}	


function lms_commission_history(){

}

function lms_import_export(){
	$url='';
	$wplms_export= new wplms_export();
	$wplms_import = new wplms_import();
	if(isset($_POST['export'])){
		$url=$wplms_export->generate_report();
	}

	echo '<h3>'.__('Import/Export WPLMS Elements','vibe-customtypes').'</h3>';
	echo '<p>'.__('Download and upload in CSV format. Import/Export WPLMS elements with user statuses: Courses, Quizzes, Units, Assignments, Questions and Events.','vibe-customtypes').'</p>';
	
	echo '<hr/><h3>'.__('EXPORT SETTINGS','vibe-customtypes').'</h3>';
	

	$wplms_export->generate_form($url);

	echo '<hr/>';
	echo '<div style="background:#FFF;display:inline-block;padding:20px 30px 30px; margin:30px 0;border-radius:2px;">';
	if(isset($_POST['import'])){
		if(current_user_can('manage_options'))
			$wplms_import->process_upload();
	}
		$wplms_import->generate_form();
	echo '</div>';	
}
?>