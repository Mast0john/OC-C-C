<?php

/**
 * Check to see if a high five is being given, and if so, save it.
 *
 * Hooked to bp_actions, this function will fire before the screen function. We use our function
 * bp_is_course_component(), along with the bp_is_current_action() and bp_is_action_variable()
 * functions, to detect (based on the requested URL) whether the user has clicked on "send high
 * five". If so, we do a bit of simple logic to see what should happen next.
 *
 * @package BuddyPress_Course_Component
 * @since 1.6
 */


add_action('bp_activity_register_activity_actions','bp_course_register_actions');
function bp_course_register_actions(){
	global $bp;
	$bp_course_action_desc=array(
		'remove_from_course' => __( 'Removed a student from Course', 'vibe' ),
		'submit_course' => __( 'Student submitted a Course', 'vibe' ),
		'start_course' => __( 'Student started a Course', 'vibe' ),
		'submit_quiz' => __( 'Student submitted a Quiz', 'vibe' ),
		'start_quiz' => __( 'Student started a Course', 'vibe' ),
		'unit_complete' => __( 'Student submitted a Course', 'vibe' ),
		'reset_course' => __( 'Course reset for Student', 'vibe' ),
		'bulk_action' => __( 'Bulk action by instructor', 'vibe' ),
		'course_evaluated' => __( 'Course Evaluated for student', 'vibe' ),
		'student_badge'=> __( 'Student got a Badge', 'vibe' ),
		'student_certificate' => __( 'Student got a certificate', 'vibe' ),
		'quiz_evaluated' => __( 'Quiz Evaluated for student', 'vibe' ),
		'subscribe_course' => __( 'Student subscribed for course', 'vibe' ),
		);
	foreach($bp_course_action_desc as $key => $value){
		bp_activity_set_action($bp->activity->id,$key,$value);	
	}
}

add_filter( 'woocommerce_get_price_html', 'course_subscription_filter',100,2 );
function course_subscription_filter($price,$product){

	$subscription=get_post_meta($product->id,'vibe_subscription',true);

		if(vibe_validate($subscription)){
			$x=get_post_meta($product->id,'vibe_duration',true);
			$product_duration_parameter = apply_filters('vibe_product_duration_parameter',86400);
			$t=$x*$product_duration_parameter;

			if($x == 1){
				$price = $price .'<span class="subs"> '.__('per','vibe').' '.tofriendlytime($t).'</span>';
			}else{
				$price = $price .'<span class="subs"> '.__('per','vibe').' '.tofriendlytime($t).'</span>';
			}
		}
		return apply_filters( 'woocommerce_get_price', $price );
}




add_action('woocommerce_after_add_to_cart_button','bp_course_subscription_product');
function bp_course_subscription_product(){
	global $product;
	$check_susbscription=get_post_meta($product->id,'vibe_subscription',true);
	if(vibe_validate($check_susbscription)){
		$duration=get_post_meta($product->id,'vibe_duration',true);	
		$product_duration_parameter = apply_filters('vibe_product_duration_parameter',86400);
		$t=tofriendlytime($duration*$product_duration_parameter);
		echo '<div id="duration"><strong>'.__('SUBSCRIPTION FOR','vibe').' '.$t.'</strong></div>';
	}
}
//woocommerce_order_status_completed
add_action('woocommerce_order_status_completed','bp_course_enable_access');
function bp_course_enable_access($order_id){

	$order = new WC_Order( $order_id );

	$items = $order->get_items();
	$user_id=$order->user_id;
	$order_total = $order->get_total();
	$total_discount = $order->get_total_discount();
	$commission_array=array();

	foreach($items as $item_id=>$item){

	$instructors=array();
	$product_id = $item['product_id'];
	$subscribed=get_post_meta($product_id,'vibe_subscription',true);

	$courses=vibe_sanitize(get_post_meta($product_id,'vibe_courses',false));

	if($total_discount){
		$multiplier = round($order_total/($order_total+$total_discount),4);		
	}

	if(isset($courses) && is_array($courses)){

		if(vibe_validate($subscribed) ){

			$duration=get_post_meta($product_id,'vibe_duration',true);
			$product_duration_parameter = apply_filters('vibe_product_duration_parameter',86400); // Product duration for subscription based
			$start_date = get_post_meta($course,'vibe_start_date',true);
			$time=0;
			if(isset($start_date) && $start_date){
				$time=strtotime($start_date);
			}
			if($time<time())
				$time=time();
			
			$t=$time+$duration*$product_duration_parameter;

			foreach($courses as $course){
				update_post_meta($course,$user_id,0);
				update_user_meta($user_id,$course,$t);
				update_user_meta($user_id,'course_status'.$course,1);

				$group_id=get_post_meta($course,'vibe_group',true);
				if(isset($group_id) && $group_id !='' && is_numeric($group_id))
				groups_join_group($group_id, $user_id );  

				$durationtime = $duration.' '.calculate_duration_time($product_duration_parameter);
				if($duration == '9999')
					$durationtime = __('Unlimited Duration','vibe');
				
				bp_course_record_activity(array(
				      'action' => __('Student subscribed for course ','vibe').get_the_title($course),
				      'content' => __('Student ','vibe').bp_core_get_userlink( $user_id ).__(' subscribed for course ','vibe').get_the_title($course).__(' for ','vibe').$durationtime,
				      'type' => 'subscribe_course',
				      'item_id' => $course,
				      'primary_link'=>get_permalink($course),
				      'secondary_item_id'=>$user_id
		        ));   
		        $instructors[$course]=apply_filters('wplms_course_instructors',get_post_field('post_author',$course),$course);
		        do_action('wplms_course_product_puchased',$course,$user_id,$t,1);
			}
		}else{	
			if(isset($courses) && is_array($courses)){
			foreach($courses as $course){
				$duration=get_post_meta($course,'vibe_duration',true);
				$course_duration_parameter = apply_filters('vibe_course_duration_parameter',86400); // Course duration for subscription based
				$start_date = get_post_meta($course,'vibe_start_date',true);
				$time=0;
				if(isset($start_date) && $start_date){
					$time=strtotime($start_date);
				}
				if($time<time())
					$time=time();

				$t=$time+$duration*$course_duration_parameter;
				update_post_meta($course,$user_id,0);
				update_user_meta($user_id,$course,$t);
				update_user_meta($user_id,'course_status'.$course,1);
				$group_id=get_post_meta($course,'vibe_group',true);
				if(isset($group_id) && $group_id !='' && is_numeric($group_id))
					groups_join_group($group_id, $user_id );

				$durationtime = $duration.' '.calculate_duration_time($product_duration_parameter);
				if($duration == '9999')
					$durationtime = __('Unlimited Duration','vibe');

				bp_course_record_activity(array(
				      'action' => __('Student subscribed for course ','vibe').get_the_title($course),
				      'content' => __('Student ','vibe').bp_core_get_userlink( $user_id ).__(' subscribed for course ','vibe').get_the_title($course).__(' for ','vibe').$durationtime,
				      'type' => 'subscribe_course',
				      'item_id' => $course,
				      'primary_link'=>get_permalink($course),
				      'secondary_item_id'=>$user_id
		        )); 
					
		        	$instructors[$course]=apply_filters('wplms_course_instructors',get_post_field('post_author',$course,'raw'),$course);
		        	do_action('wplms_course_product_puchased',$course,$user_id,$t,0);
				}
			}
		}//End Else

		if($total_discount){ // Product discounted
			$line_total=round(($item['line_total']*$multiplier),2);
		}else
			$line_total=$item['line_total'];

		//Commission Calculation
		$commission_array[$item_id]=array(
			'instructor'=>$instructors,
			'course'=>$courses,
			'total'=>$line_total,
		);

	  }//End If courses
	}// End Item for loop
	
	if(function_exists('vibe_get_option'))
      $instructor_commission = vibe_get_option('instructor_commission');
    
    if($instructor_commission == 0)
      		return;
      	
    if(!isset($instructor_commission) || !$instructor_commission)
      $instructor_commission = 70;

    $commissions = get_option('instructor_commissions');

	foreach($commission_array as $item_id=>$commission_item){

			foreach($commission_item['course'] as $course_id){ 
			
			if(count($commission_item['instructor'][$course_id]) > 1){     // Multiple instructors
				
				$calculated_commission_base=round(($commission_item['total']*($instructor_commission/100)/count($commission_item['instructor'][$course_id])),0); // Default Slit equal propertion

				foreach($commission_item['instructor'][$course_id] as $instructor){
					if(isset($commissions[$course_id][$instructor])){
						$calculated_commission_base = round(($commission_item['total']*$commissions[$course_id][$instructor]/100),2);
					}
					$calculated_commission_base = apply_filters('wplms_calculated_commission_base',$calculated_commission_base,$instructor);
					woocommerce_update_order_item_meta( $item_id, 'commission'.$instructor,$calculated_commission_base);
				}
			}else{
				if(is_array($instructors[$course_id]))                                    // Single Instructor
					$instructor=$instructors[$course_id][0];
				else
					$instructor=$instructors[$course_id]; 
				
				if(isset($commissions[$course_id][$instructor]) && is_numeric($commissions[$course_id][$instructor]))
					$calculated_commission_base = round(($commission_item['total']*$commissions[$course_id][$instructor]/100),2);
				else
					$calculated_commission_base = round(($commission_item['total']*$instructor_commission/100),2);

				$calculated_commission_base = apply_filters('wplms_calculated_commission_base',$calculated_commission_base,$instructor);
				woocommerce_update_order_item_meta( $item_id, 'commission'.$instructor,$calculated_commission_base);
			}   
		}

	} // End Commissions_array  
}

add_action('woocommerce_order_status_cancelled','bp_course_disable_access');
add_action('woocommerce_order_status_refunded','bp_course_disable_access');

function bp_course_disable_access($order_id){
	$order = new WC_Order( $order_id );

	$items = $order->get_items();
	$user_id=$order->user_id;
	foreach($items as $item){
		$product_id = $item['product_id'];
		$subscribed=get_post_meta($product_id,'vibe_subscription',true);
		$courses=vibe_sanitize(get_post_meta($product_id,'vibe_courses',false));

			if(isset($courses) && is_array($courses)){
			foreach($courses as $course){
				delete_post_meta($course,$user_id);
				delete_user_meta($user_id,$course);
				$group_id=get_post_meta($course,'vibe_group',true);

				if(isset($group_id))
					groups_remove_member($user_id,$group_id);

				$instructors = apply_filters('wplms_course_instructors',get_post_field('post_author',$course,'raw'),$course);
				if(is_array($instructors)){
					foreach($instructors as $instructor){
						woocommerce_update_order_item_meta( $item_id, 'commission'.$instructor);
					}
				}
				bp_course_record_activity(array(
			      'action' => __('Student ','vibe').bp_core_get_userlink($user_id).__(' removed from course ','vibe').get_the_title($course_id),
			      'content' => __('Student ','vibe').bp_core_get_userlink($user_id).__(' removed from the course ','vibe').get_the_title($course_id),
			      'type' => 'remove_from_course',
			      'primary_link' => get_permalink($course_id),
			      'item_id' => $course_id,
			      'secondary_item_id' => $user_id
			    ));
				}
			}
		} 
}

add_action('bp_members_directory_member_types','bp_course_instructor_member_types');

function bp_course_instructor_member_types(){
	?>
		<li id="members-instructors"><a href="#"><?php printf( __( 'All Instructors <span>%s</span>', 'vibe' ), bp_get_total_instructor_count() ); ?></a></li>
	<?php
}


add_filter('bp_course_admin_before_course_students_list','bp_course_admin_search_course_students',10,2);
function bp_course_admin_search_course_students($students,$course_id){

	echo '<form method="post">
			<input type="text" name="search" value="'.$_POST['search'].'" placeholder="'.__('Enter student name/email','vibe').'" class="input" />
			<input type="submit" value="'.__('Search','vibe').'" />
		  </form>';
    if(isset($_POST['search'])){

    	$args = array(
			'search'         => $_POST['search'],
			'search_columns' => array( 'login', 'email','nicename'),
			'fields' => array('ID'),
			'meta_query' => array(
				array(
					'key' => $course_id,
					'compare' => 'EXISTS'
					)
				),
		);
    	$user_query = new WP_User_Query( $args );
    	$users = $user_query->get_results();

		if(count($users)){
			$students=array();
			foreach($users as $user){
				if(is_object($user) && isset($user->ID))
					$students[]=$user->ID;
			}
		}
    }
	return $students;
}

if(function_exists('wplms_show_course_student_status'))
	remove_filter('wplms_course_credits','wplms_show_course_student_status'); // Remove this line in 1.8.5
add_filter('wplms_course_credits','wplms_show_new_course_student_status',20,2);
function wplms_show_new_course_student_status($credits,$course_id){
  if(is_user_logged_in() && !is_singular('course')){
    $user_id=get_current_user_id();
    $check=get_user_meta($user_id,$course_id,true);
    if(isset($check) && $check){
      if($check < time()){
        return '<strong>'.__('EXPIRED','vibe').'<span class="subs">'.__('COURSE','vibe').'</span></strong>';
      }

      $check_course= bp_course_get_user_course_status($user_id,$course_id);
      $new_check_course = get_user_meta($user_id,'course_status'.$course_id,true);
      if(isset($new_check_course) && is_numeric($new_check_course) && $new_check_course){
  	      switch($check_course){
	        case 1:
	        $credits ='<a href="'.get_permalink($course_id).'"><strong>'.__('START','vibe').'<span class="subs">'.__('COURSE','vibe').'</span></strong></a>';
	        break;
	        case 2:
	        $credits ='<a href="'.get_permalink($course_id).'"><strong>'.__('CONTINUE','vibe').'<span class="subs">'.__('COURSE','vibe').'</span></strong></a>';
	        break;
	        case 3:
	        $credits ='<a href="'.get_permalink($course_id).'"><strong>'.__('UNDER','vibe').'<span class="subs">'.__('EVALUATION','vibe').'</span></strong></a>';
	        break;
	        case 4:
	        $credits ='<a href="'.get_permalink($course_id).'"><strong>'.__('FINISHED','vibe').'<span class="subs">'.__('COURSE','vibe').'</span></strong></a>';
	        break;
	        default:
	        $credits =apply_filters('wplms_course_status_display','<a href="'.get_permalink($course_id).'"><strong>'.__('COURSE','vibe').'<span class="subs">'.__('ENABLED','vibe').'</span></strong></a>',$course_id);
	        break;
	      }
      }else{
      		switch($check_course){
	        case 0:
	        $credits ='<a href="'.get_permalink($course_id).'"><strong>'.__('START','vibe').'<span class="subs">'.__('COURSE','vibe').'</span></strong></a>';
	        break;
	        case 1:
	        $credits ='<a href="'.get_permalink($course_id).'"><strong>'.__('CONTINUE','vibe').'<span class="subs">'.__('COURSE','vibe').'</span></strong></a>';
	        break;
	        case 2:
	        $credits ='<a href="'.get_permalink($course_id).'"><strong>'.__('UNDER','vibe').'<span class="subs">'.__('EVALUATION','vibe').'</span></strong></a>';
	        break;
	        default:
	        $credits ='<a href="'.get_permalink($course_id).'"><strong>'.__('FINISHED','vibe').'<span class="subs">'.__('COURSE','vibe').'</span></strong></a>';
	        break;
	      }	
      }
    }
  }
  return $credits;
}

if(function_exists('wplms_before_start_course_check'))
	remove_action('wplms_before_start_course','wplms_before_start_course_check');
add_action('wplms_before_start_course','wplms_before_start_course_status');
function wplms_before_start_course_status(){
  $user_id = get_current_user_id();  
  
  if ( isset($_POST['start_course']) && wp_verify_nonce($_POST['start_course'],'start_course'.$user_id) ){
      $course_id=$_POST['course_id'];
      $coursetaken=1;
      $cflag=0;
      $precourse=get_post_meta($course_id,'vibe_pre_course',true);

      if(isset($precourse) && $precourse !=''){
          
          $preid=bp_course_get_user_course_status($user_id,$precourse);
          //get_post_meta($precourse,$user_id,true);

          if(isset($preid) && $preid !='' && $preid > 2){ 
            // OLD COURSE STATUS : 
            // 0 : NOT STARTED 
            // 1: STARTED 
            // 2 : SUBMITTED
            // NEW COURSE STATUSES : Since version 1.8.4
            // 1 : START COURSE
            // 2 : CONTINUE COURSE
            // 3 : FINISH COURSE : COURSE UNDER EVALUATION
            // 4 : COURSE EVALUATED
              $cflag=1;
          }
      }else{
          $cflag=1;
      }

      if($cflag){
          $course_duration_parameter = apply_filters('vibe_course_duration_parameter',86400);
          $expire=time()+$course_duration_parameter; // One Unit logged in Limit for the course
          setcookie('course',$course_id,$expire,'/');
          $students=get_post_meta($course_id,'vibe_students',true);
          $students++;
          update_post_meta($course_id,'vibe_students',$students);
          bp_course_update_user_course_status($user_id,$course_id,1);//Since version 1.8.4
          
          $activity_id=bp_course_record_activity(array(
            'action' => __('Student started course ','vibe').get_the_title($course_id),
            'content' => __('Student ','vibe').bp_core_get_userlink( $user_id ).__(' started the course ','vibe').get_the_title($course_id),
            'type' => 'start_course',
            'item_id' => $course_id,
            'primary_link'=>get_permalink($course_id),
            'secondary_item_id'=>$user_id
          ));

          bp_course_record_activity_meta(array(
              'id' => $activity_id,
              'meta_key' => 'instructor',
              'meta_value' => get_post_field( 'post_author', $course_id )
              ));

          do_action('badgeos_wplms_start_course',$course_id);
      }else{
          
          header('Location: ' . get_permalink($course_id) . '?error=precourse');
          
      }

    

  }else if ( isset($_POST['continue_course']) && wp_verify_nonce($_POST['continue_course'],'continue_course'.$user_id) ){
    $course_id=$_POST['course_id'];
    $coursetaken=get_user_meta($user_id,$course_id,true);
      setcookie('course',$course_id,$expire,'/');
  }else{
    if(isset($_COOKIE['course'])){
      $course_id=$_COOKIE['course'];
      $coursetaken=1;
    }else
      wp_die( __('This Course can not be taken. Contact Administrator.','vibe'), 'Contact Admin', array(500,true) );
  }

}
?>