<?php

/***
 * You can hook in ajax functions in WordPress/BuddyPress by using the 'wp_ajax' action.
 * 
 * When you post your ajax call from javascript using jQuery, you can define the action
 * which will determin which function to run in your PHP component code.
 *
 * Here's an course:
 *
 * In Javascript we can post an action with some parameters via jQuery:
 * 
 * 			jQuery.post( ajaxurl, {
 *				action: 'my_course_action',
 *				'cookie': encodeURIComponent(document.cookie),
 *				'parameter_1': 'some_value'
 *			}, function(response) { ... } );
 *
 * Notice the action 'my_course_action', this is the part that will hook into the wp_ajax action.
 * 
 * You will need to add an add_action( 'wp_ajax_my_course_action', 'the_function_to_run' ); so that
 * your function will run when this action is fired.
 * 
 * You'll be able to access any of the parameters passed using the $_POST variable.
 *
 * Below is an course of the addremove_friend AJAX action in the friends component.
 */



add_action('wp_ajax_complete_unit', 'wplms_complete_unit');

function wplms_complete_unit(){
  $unit_id = $_POST['id'];
  $course_id = $_POST['course_id'];
  if ( !isset($_POST['security']) || !wp_verify_nonce($_POST['security'],'security') ){
     _e('Security check Failed. Contact Administrator.','vibe');
     die();
  }

  // Check if user has taken the course
  $user_id = get_current_user_id();
  $coursetaken=get_user_meta($user_id,$course_id,true);
  
  if(isset($coursetaken) && $coursetaken){
    $nextunit_access = vibe_get_option('nextunit_access');

    if(isset($nextunit_access) && $nextunit_access){ // Enable Next unit access
      if(add_user_meta($user_id,$unit_id,time())){
         $curriculum=bp_course_get_curriculum_units($course_id);
         $key = array_search($unit_id,$curriculum);
         if($key <=(count($curriculum)-1) ){  // Check if not the last unit
          $key++;
          echo $curriculum[$key];
         }
      }
    }else{
      $curriculum=bp_course_get_curriculum_units($course_id);
      $key = array_search($unit_id,$curriculum);
      $key++;
      add_user_meta($user_id,$unit_id,time());
    }
    $activity_id=bp_course_record_activity(array(
      'action' => __('Student finished unit ','vibe'),
      'content' => sprintf(__('Student %s finished the unit %s in course %s','vibe'),bp_core_get_user_displayname($user_id),get_the_title($unit_id),get_the_title($course_id)),
      'type' => 'unit_complete',
      'primary_link' => get_permalink($unit_id),
      'item_id' => $unit_id,
      'secondary_item_id' => $course_id
    ));
    bp_course_record_activity_meta(array(
      'id' => $activity_id,
      'meta_key' => 'instructor',
      'meta_value' => get_post_field( 'post_author', $unit_id )
      ));
    
    $c=(count($curriculum)?count($curriculum):1);
    $course_progress = $key/$c;
    do_action('badgeos_wplms_unit_complete',$unit_id,$course_progress,$course_id );
  }
  die();
}


add_action('wp_ajax_reset_question_answer', 'reset_question_answer');
function reset_question_answer(){
  global $wpdb;
  $ques_id = $_POST['ques_id'];
  if(isset($ques_id) && $_POST['security'] && wp_verify_nonce($_POST['security'],'security'.$ques_id)){
    $user_id = get_current_user_id();
    $wpdb->query($wpdb->prepare("UPDATE $wpdb->comments SET comment_approved='trash' WHERE comment_post_ID=%d AND user_id=%d",$ques_id,$user_id));
    echo '<p>'.__('Answer Reset','vibe').'</p>';
  }else
    echo '<p>'.__('Unable to Reset','vibe').'</p>';

  die();
}


add_action( 'wp_ajax_calculate_stats_course', 'calculate_stats_course' ); // RESETS QUIZ FOR USER
function calculate_stats_course(){
	$course_id=$_POST['id'];
	$flag=0;
	if ( !isset($_POST['security']) || !wp_verify_nonce($_POST['security'],'vibe_security') ){
        echo '<p>'.__('Security check failed !','vibe').'</p>';
        die();
    }

    if ( !isset($course_id) || !$course_id){
    	echo '<p>'.__('Incorrect Course selected.','vibe').'</p>';
        die();
    }
    $badge=$pass=$total_qmarks=$gross_qmarks=0;
    $users=array();
	global $wpdb;

	$badge_val=get_post_meta($course_id,'vibe_course_badge_percentage',true);
	$pass_val=get_post_meta($course_id,'vibe_course_passing_percentage',true);

	$members_course_grade = $wpdb->get_results( $wpdb->prepare("select meta_value,meta_key from {$wpdb->postmeta} where post_id = %d AND meta_key REGEXP '[0-9]+'",$course_id), ARRAY_A);


	if(count($members_course_grade)){
    $cmarks=$i=0;
		foreach($members_course_grade as $meta){
			if(is_numeric($meta['meta_key']) && $meta['meta_value'] > 2){
       
						if($meta['meta_value'] > $badge_val)
							$badge++;

						if($meta['meta_value'] > $pass_val)
							$pass++;

						$users[]=$meta['meta_key'];

            if(isset($meta['meta_value']) && is_numeric($meta['meta_value']) && $meta['meta_value'] > 2 && $meta['meta_value']<101){
              $cmarks += $meta['meta_value'];
              $i++;
            }
					}
			}  // META KEY is NUMERIC ONLY FOR USERIDS
	}

	if($pass)
		update_post_meta($course_id,'pass',$pass);


	if($badge)
		update_post_meta($course_id,'badge',$badge);

	if($i==0)$i=1;
    $avg = round(($cmarks/$i));

update_post_meta($course_id,'average',$avg);

if($flag !=1){
	$curriculum=vibe_sanitize(get_post_meta($course_id,'vibe_course_curriculum',false));
		foreach($curriculum as $c){
			if(is_numeric($c)){

				if(get_post_type($c) == 'quiz'){
          $i=$qmarks=0;

					foreach($users as $user){
						$k=get_post_meta($c,$user,true);
            if(is_numeric($k)){
  						$qmarks +=$k;
              $i++;
  						$gross_qmarks +=$k;
            }
					}
          if($i==0)$i=1;
					
          $qavg=round(($qmarks/$i),1);

					if($qavg)
						update_post_meta($c,'average',$qavg);
					else{
						$flag=1;
						break;
					}
				}
			}
	}
}

if(function_exists('assignment_comment_handle')){ // Assignment is active
  $assignments_query = $wpdb->get_results( $wpdb->prepare("select post_id from {$wpdb->postmeta} where meta_value = %d AND meta_key = 'vibe_assignment_course'",$course_id), ARRAY_A);
  foreach($assignments_query as $assignment_query){
    $assignments[]=$assignment_query['post_id'];
  }

  if(count($assignments)){ // If any connected assignments
    $assignments_string = implode(',',$assignments);
    $assignments_marks_query = $wpdb->get_results("select post_id,meta_value from {$wpdb->postmeta} where post_id IN ($assignments_string) AND meta_key REGEXP '[0-9]+' AND meta_value REGEXP '[0-9]+'", ARRAY_A);
    
    foreach($assignments_marks_query as $marks){
      $user_assignments[$marks['post_id']]['total'] += $marks['meta_value'];
      $user_assignments[$marks['post_id']]['number']++;
    }

    foreach($user_assignments as $key=>$user_assignment){
      if(isset($user_assignment['number']) && $user_assignment['number']){
        $avg = $user_assignment['total']/$user_assignment['number'];  
        update_post_meta($key,'average',$avg);
      }
    }
  }
}
	if(!$flag){
		echo '<p>'.__('Statistics successfully calculated. Reloading...','vibe').'</p>';
	}else{
		echo '<p>'.__('Unable to calculate Average.','vibe').'</p>';
	}

	die();
}

add_action( 'wp_ajax_course_stats_user', 'course_stats_user' ); // RESETS QUIZ FOR USER
function course_stats_user(){
	$course_id = $_POST['id'];
    $user_id = $_POST['user'];

    echo '<a class="show_side link right" data-side=".course_stats_user">'.__('SHOW STATS','vibe').'</a><div class="course_stats_user"><a class="hide_parent link right">'.__('HIDE','vibe').'</a>';

    if ( !isset($_POST['security']) || !wp_verify_nonce($_POST['security'],'vibe_security') ){
        echo '<div id="message" class="info notice"><p>'.__('Security check failed !','vibe').'</p></div>';
        die();
    }

    if ( !isset($user_id) || !$user_id){
    	echo '<div id="message" class="info notice"><p>'.__('Incorrect User selected.','vibe').'</p></div>';
        die();
    }


    $start=get_user_meta($user_id,$course_id,true);
	
	$being=get_post_meta($course_id,$user_id,true);

	if(isset($being) && $being !=''){
		if(!$being){
			echo '<p>'.__('This User has not started the course.','vibe').'</p>';
		}else if($being > 2 && $being < 100){
			echo '<p>'.__('This User has completed the course.','vibe').'</p>';
			echo '<h4>'.__('Student Score for Course ','vibe').' : <strong>'.$being.__(' out of 100','vibe').'</strong></h4>';

      $course_curriculum=vibe_sanitize(get_post_meta($course_id,'vibe_course_curriculum',false));
      $complete=$total=count($course_curriculum);

		}else{
			$total=0;
			$complete=0;

			echo '<h6>';
			_e('Course Started : ','vibe');
			echo '<span>'.tofriendlytime((time()-$start)).__(' ago','vibe').'</span></h6>';

			$course_curriculum=vibe_sanitize(get_post_meta($course_id,'vibe_course_curriculum',false));

			$curriculum = '<div class="curriculum_check"><h6>'.__('Curriculum :','vibe').'</h6><ul>';
			$quiz ='<h5>'.__('Quizes','vibe').'</h5>';
			foreach($course_curriculum as $c){
				if(is_numeric($c)){
					$total++;
					$check=get_user_meta($user_id,$c,true);
					if(isset($check) && $check !=''){
						$complete++;
						if(get_post_type($c) == 'quiz'){
							$marks = get_post_meta($c,$user_id,true);

							$curriculum .= '<li><span class="done"></span> '.get_the_title($c).' <strong>'.(($marks)?__('Marks Obtained : ','vibe').$marks:__('Under Evaluation','vibe')).'</strong></li>';
						}else
							$curriculum .= '<li><span class="done"></span> '.get_the_title($c).'</li>';

					}else{
						$curriculum .= '<li><span></span> '.get_the_title($c).'</li>';
					}
				}else{
					$curriculum .= '<li><h5>'.$c.'</h5></li>';
				}
			}
			$curriculum .= '</ul></div>';
		}
	}

	echo '<strong>'.__('Units Completed ','vibe').$complete.__(' out of ','vibe').$total.'</strong>';
	echo '<div class="complete_course"><input type="text" class="dial" data-max="'.$total.'" value="'.$complete.'"></div>';
	echo $curriculum;
    echo '</div>';
	die();
}


add_action( 'wp_ajax_remove_user_course', 'remove_user_course' ); // RESETS QUIZ FOR USER
function remove_user_course(){
	  $course_id = $_POST['id'];
    $user_id = $_POST['user'];

    if ( !isset($_POST['security']) || !wp_verify_nonce($_POST['security'],'vibe_security') ){
        echo '<p>'.__('Security check failed !','vibe').'</p>';
        die();
    }

    if ( !isset($user_id) || !$user_id){
        echo '<p>'.__(' Incorrect User selected.','vibe').'</p>';
        die();
    }

    if(delete_post_meta($course_id,$user_id)){

			delete_user_meta($user_id,$course_id);
      delete_user_meta($user_id,'course_status'.$course_id);

      $students=get_post_meta($course_id,'vibe_students',true);
      if($students > 1){
        $students--;
        update_post_meta($course_id,'vibe_students',$students);
      }
			echo '<p>'.__('User removed from the Course','vibe').'</p>';

      $group_id=get_post_meta($course_id,'vibe_group',true);
      if(isset($group_id) && is_numeric($group_id) && bp_is_active('groups')){
        groups_remove_member($user_id,$group_id);
      }
      
      do_action('wplms_student_course_remove',$course_id,$user_id);

      bp_course_record_activity(array(
      'action' => __('Student ','vibe').bp_core_get_userlink($user_id).__(' removed from course ','vibe').get_the_title($course_id),
      'content' => __('Student ','vibe').bp_core_get_userlink($user_id).__(' removed from the course ','vibe').get_the_title($course_id),
      'type' => 'remove_from_course',
      'primary_link' => get_permalink($course_id),
      'item_id' => $course_id,
      'secondary_item_id' => $user_id
    ));

	}else{
		echo '<p>'.__('There was issue in removing this user from the Course. Please contact admin.','vibe').'</p>';
	}
	die();
}


add_action( 'wp_ajax_reset_course_user', 'reset_course_user' ); // RESETS COURSE FOR USER
function reset_course_user(){
	  $course_id = $_POST['id'];
    $user_id = $_POST['user'];

    if ( !isset($_POST['security']) || !wp_verify_nonce($_POST['security'],'vibe_security') ){
        echo '<p>'.__('Security check failed !','vibe').'</p>';
        die();
    }

    if ( !isset($user_id) || !is_numeric($user_id) || !$user_id){
        echo '<p>'.__(' Incorrect User selected.','vibe').'</p>';
        die();
    }
      
      //delete_user_meta($user_id,$course_id) // DELETE ONLY IF USER SUBSCRIPTION EXPIRED
    $status = bp_course_get_user_course_status($user_id,$course_id);
    
    if(isset($status) && is_numeric($status)){  // Necessary for continue course
      do_action('wplms_student_course_reset',$course_id,$user_id);
      bp_course_update_user_course_status($user_id,$course_id,0); // New function
			$course_curriculum=vibe_sanitize(get_post_meta($course_id,'vibe_course_curriculum',false));

			foreach($course_curriculum as $c){
				if(is_numeric($c)){
					delete_user_meta($user_id,$c);
					delete_post_meta($c,$user_id);
					if(get_post_type($c) == 'quiz'){

            $questions = vibe_sanitize(get_post_meta($c,'quiz_questions'.$user_id,false));
            
            if(!isset($questions) || !is_array($questions)) // Fallback for Older versions
              $questions = vibe_sanitize(get_post_meta($c,'vibe_quiz_questions',false));
            else
              delete_post_meta($c,'quiz_questions'.$user_id); // Re-capture new questions in quiz begining

            if(isset($questions) && is_array($questions) && is_Array($questions['ques']))
				      	foreach($questions['ques'] as $question){
				        	global $wpdb;
                  if(isset($question) && $question !='' && is_numeric($question))
				        	$wpdb->query($wpdb->prepare("UPDATE $wpdb->comments SET comment_approved='trash' WHERE comment_post_ID=%d AND user_id=%d",$question,$user_id));
				      	}
					}

				}
			}
      /*=== Fix in 1.5 : Reset  Badges and CErtificates on Course Reset === */
      $user_badges=vibe_sanitize(get_user_meta($user_id,'badges',false));
      $user_certifications=vibe_sanitize(get_user_meta($user_id,'certificates',false));

      if(isset($user_badges) && is_Array($user_badges) && in_array($course_id,$user_badges)){
          $key=array_search($course_id,$user_badges);
          unset($user_badges[$key]);
          $user_badges = array_values($user_badges);
          update_user_meta($user_id,'badges',$user_badges);
      }
      if(isset($user_certifications) && is_Array($user_certifications) && in_array($course_id,$user_certifications)){
          $key=array_search($course_id,$user_certifications);
          unset($user_certifications[$key]);
          $user_certifications = array_values($user_certifications);
          update_user_meta($user_id,'certificates',$user_certifications);
      }
      /*==== End Fix ======*/

			echo '<p>'.__('Course Reset for User','vibe').'</p>';
      bp_course_record_activity(array(
      'action' => __('Course reset for student ','vibe'),
      'content' => __('Course ','vibe').get_the_title($course_id).__(' reset for student ','vibe').bp_core_get_userlink($user_id),
      'type' => 'reset_course',
      'primary_link' => get_permalink($course_id),
      'item_id' => $course_id,
      'secondary_item_id' => $user_id
    ));
	}else{
		echo '<p>'.__('There was issue in resetting this course for the user. Please contact admin.','vibe').'</p>';
	}
	die();
}

add_action( 'wp_ajax_reset_quiz', 'reset_quiz' ); // RESETS QUIZ FOR USER
function reset_quiz(){

    $quiz_id = $_POST['id'];
    $user_id = $_POST['user'];

     if ( !isset($_POST['security']) || !wp_verify_nonce($_POST['security'],'vibe_quiz') ){
        echo '<p>'.__('Security check failed !','vibe').'</p>';
        die();
    }

    if ( !isset($user_id) || !$user_id){
        echo '<p>'.__(' Incorrect User selected.','vibe').'</p>';
        die();
    }

    if(delete_user_meta($user_id,$quiz_id)){

      delete_post_meta($quiz_id,$user_id); // Optional validates that user can retake the quiz

      $questions = vibe_sanitize(get_post_meta($quiz_id,'quiz_questions'.$user_id,false));
      if(!isset($questions) || !is_array($questions)) // Fallback for Older versions
        $questions = vibe_sanitize(get_post_meta($quiz_id,'vibe_quiz_questions',false));
      else
        delete_post_meta($quiz_id,'quiz_questions'.$user_id); // Re-capture new questions in quiz begining

      foreach($questions['ques'] as $question){
        global $wpdb;
        $wpdb->query($wpdb->prepare("UPDATE $wpdb->comments SET comment_approved='trash' WHERE comment_post_ID=%d AND user_id=%d",$question,$user_id));
      }
      echo '<p>'.__('Quiz Reset for Selected User','vibe').'</p>';
    }else{
      echo '<p>'.__('Could not find Quiz results for User. Contact Admin.','vibe').'</p>';
    }
	

    bp_course_record_activity(array(
      'action' => __('Instructor Reseted the Quiz for User','vibe'),
      'content' => __('Quiz ','vibe').get_the_title($quiz_id).__(' was reset by the Instructor for user','vibe').bp_core_get_userlink( $user_id ),
      'type' => 'reset_quiz',
      'primary_link' => get_permalink($quiz_id),
      'item_id' => $quiz_id,
      'secondary_item_id' => $user_id
      ));
    die();
}


add_action( 'wp_ajax_give_marks', 'give_marks' ); // RESETS QUIZ FOR USER
function give_marks(){
    $answer_id=intval($_POST['aid']);
    $value=intval($_POST['aval']);
    
    if(is_numeric($answer_id) && is_numeric($value))
      update_comment_meta( $answer_id, 'marks',$value);

    die();
}

add_action( 'wp_ajax_complete_course_marks', 'complete_course_marks' ); // COURSE MARKS FOR USER
function complete_course_marks(){
    $user_id=intval($_POST['user']);
    $course_id=intval($_POST['course']);
    $marks=intval($_POST['marks']);

    $badge_per = get_post_meta($course_id,'vibe_course_badge_percentage',true);
    $passing_per = get_post_meta($course_id,'vibe_course_passing_percentage',true);

    $activity_id=bp_course_record_activity(array(
      'action' => __('Instructor evaluated Course for Student','vibe'),
      'content' => __('Student ','vibe').bp_core_get_userlink( $user_id ).__(' got =','vibe').apply_filters('wplms_course_marks',$marks.'/100',$course_id).__(' in course ','vibe').get_the_title($course_id),
      'primary_link' => get_permalink($course_id),
      'type' => 'course_evaluated',
      'item_id' => $course_id,
      ));
    
    bp_course_record_activity_meta(array(
            'id' => $activity_id,
            'meta_key' => 'instructor',
            'meta_value' => get_post_field( 'post_author', $course_id )
            ));

    if(isset($badge_per) && $badge_per && $marks > $badge_per){
        
        $badges= vibe_sanitize(get_user_meta($user_id,'badges',false));
        if(!is_array($badges))
          $badges = array();
            
        if(!in_array($course_id,$badges)){
            $badges[]=$course_id;
            update_user_meta($user_id,'badges',$badges);
            bp_course_record_activity(array(
              'action' => __('Student got a Badge in the course ','vibe'),
              'content' => __('Student ','vibe').bp_core_get_userlink($user_id).__(' got a badge in the course ','vibe').get_the_title($course_id),
              'type' => 'student_badge',
              'item_id' => $course_id,
              'primary_link'=>get_permalink($course_id),
            )); 
          do_action('wplms_badge_earned',$course_id,$badges,$user_id);
        }
    }

    if(isset($passing_per) && $passing_per && $marks > $passing_per){
        $pass = array();
        $pass=vibe_sanitize(get_user_meta($user_id,'certificates',false));
        if(!in_array($course_id,$pass)){
          $pass[]=$course_id;
          update_user_meta($user_id,'certificates',$pass);

          bp_course_record_activity(array(
            'action' => __('Student got a Certificate in course','vibe'),
            'content' => __('Student ','vibe').bp_core_get_userlink($user_id).__(' got a certificate in the course ','vibe').get_the_title($course_id),
            'type' => 'student_certificate',
            'item_id' => $course_id,
            'primary_link'=>get_permalink($course_id),
          )); 
          do_action('wplms_certificate_earned',$course_id,$pass,$user_id);
        }
    }
    if(update_post_meta( $course_id,$user_id,$marks)){      

    $course_end_status = apply_filters('wplms_course_status',4);  
    update_user_meta( $user_id,'course_status'.$course_id,$course_end_status);//EXCEPTION

    $message = __('You\'ve obtained ','vibe').apply_filters('wplms_course_marks',$marks.'/100',$course_id).__(' in Course :','vibe').' <a href="'.get_permalink($course_id).'">'.get_the_title($course_id).'</a>';
    if(bp_is_active('messages'))
      messages_new_message( array('sender_id' => get_current_user_id(), 'subject' => __('Course results available','vibe'), 'content' => $message,   'recipients' => $user_id ) );
      echo __('COURSE MARKED COMPLETE','vibe');
      do_action('badgeos_wplms_evaluate_course',$course_id,$marks,$user_id);
    }else{
      echo __('FAILED TO MARK COURSE, CONTACT ADMIN','vibe');
    }
    die();
}



add_action( 'wp_ajax_save_quiz_marks', 'save_quiz_marks' ); // RESETS QUIZ FOR USER
function save_quiz_marks(){
    $quiz_id=intval($_POST['quiz_id']);
    $user_id=intval($_POST['user_id']);
    $marks=intval($_POST['marks']);

    $questions = vibe_sanitize(get_post_meta($quiz_id,'quiz_questions'.$user_id,false));
      if(!isset($questions) || !is_array($questions)) // Fallback for Older versions
        $questions = vibe_sanitize(get_post_meta($quiz_id,'vibe_quiz_questions',false));

    $max= array_sum($questions['marks']);

    
    update_post_meta( $quiz_id, $user_id,$marks);
    $message = __('You\'ve obtained ','vibe').$marks.__(' out of ','vibe').$max.__(' in Quiz','vibe').' : <a href="'.trailingslashit( bp_core_get_user_domain( $user_id )) . BP_COURSE_SLUG. '/course-results/?action='.$quiz_id .'">'.get_the_title($quiz_id).'</a>';
    if(bp_is_active('messages'))
    messages_new_message( array('sender_id' => get_current_user_id(), 'subject' => __('Quiz results available','vibe'), 'content' => $message,   'recipients' => $user_id ) );
    
    $activity_id=bp_course_record_activity(array(
      'action' => __('Instructor evaluated Quiz for student ','vibe'),
      'type' => 'quiz_evaluated',
      'content' => __('Student ','vibe').bp_core_get_userlink( $user_id ).__(' got ','vibe').$marks.__(' out of ','vibe').$max.__(' in Quiz ','vibe').get_the_title($course_id),
      'primary_link' => trailingslashit( bp_core_get_user_domain( $user_id ) . bp_get_course_slug()) . 'course-results/?action='.$quiz_id ,
      'item_id' => $quiz_id,
      ));

    bp_course_record_activity_meta(array(
      'id' => $activity_id,
      'meta_key' => 'instructor',
      'meta_value' => get_post_field( 'post_author', $quiz_id )
    ));
    do_action('badgeos_wplms_evaluate_quiz',$quiz_id,$marks,$user_id);
    die();
}

add_action( 'wp_ajax_evaluate_course', 'evaluate_course' ); // RESETS QUIZ FOR USER
function evaluate_course(){
    
    $course_id=intval($_POST['id']);
    $user_id=intval($_POST['user']);

    if ( !isset($_POST['security']) || !wp_verify_nonce($_POST['security'],$course_id) ){
        echo '<p>'.__('Security check failed !','vibe').'</p>';
        die();
    }

    if ( !isset($user_id) || !$user_id || !is_numeric($user_id)){
        echo '<p>'.__(' Incorrect User selected.','vibe').'</p>';
        die();
    }
    $sum=$max_sum=0;
    $curriculum=vibe_sanitize(get_post_meta($course_id,'vibe_course_curriculum',false));
     echo '<ul class="course_curriculum">';
    foreach($curriculum as $c){
      if(is_numeric($c)){
        if(get_post_type($c) == 'quiz'){
            $status = get_user_meta($user_id,$c,true);
            $marks=get_post_meta($c,$user_id,true);
            $sum +=$marks;

            $qmax = vibe_sanitize(get_post_meta($c,'quiz_questions'.$user_id,false));
            if(!isset($questions) || !is_array($questions))
              $qmax=vibe_sanitize(get_post_meta($c,'vibe_quiz_questions',false));

            $max=array_sum($qmax['marks']);
            $max_sum +=$max;
            echo '<li>
                  <strong>'.get_the_title($c).' <span>'.((isset($status) && $status !='')?__('MARKS: ','vibe').$marks.__(' out of ','vibe').$max:__(' PENDING','vibe')).'</span></strong>
                  </li>';
        }else{
            $status = get_user_meta($user_id,$c,true);
            echo '<li>
                  <strong>'.get_the_title($c).' <span>'.((isset($status) && $status !='')?'<i class="icon-check"></i> '.__('DONE','vibe'):'<i class="icon-alarm-1"></i>'.__(' PENDING','vibe')).'</span></strong>
                  </li>';
        } 
      }else{

      }
    }     
    do_action('wplms_course_manual_evaluation',$course_id,$user_id);
    echo '</ul>';
    echo '<div id="total_marks">'.__('Total','vibe').' <strong><span>'.apply_filters('wplms_course_student_marks',$sum,$course_id,$user_id).'</span> / '.apply_filters('wplms_course_maximum_marks',$max_sum,$course_id,$user_id).'</strong> </div>';
    echo '<div id="course_marks">'.__('Course Percentage (Out of 100)','vibe').' <strong><span><input type="number" name="course_marks" id="course_marks_field" class="form_field" value="0" placegolder="'.__('Course Percentage out of 100','vibe').'" /></span></div>';
    echo '<a href="#" id="course_complete" class="button full" data-course="'.$course_id.'" data-user="'.$user_id.'">'.__('Mark Course Complete','vibe').'</a>';
  die();
}


add_action( 'wp_ajax_evaluate_quiz', 'evaluate_quiz' ); // EVALAUTES QUIZ FOR USER : MANUAL EVALUATION
function evaluate_quiz(){

    $quiz_id=intval($_POST['id']);
    $user_id=intval($_POST['user']);

    if ( !isset($_POST['security']) || !wp_verify_nonce($_POST['security'],'vibe_quiz') ){
       echo '<p>'.__('Security check failed !','vibe').'</p>';
        die();
    }

    if ( !isset($user_id) || !$user_id){
         echo '<p>'.__(' Incorrect User selected.','vibe').'</p>';
        die();
    }

    if(get_post_type($quiz_id) != 'quiz'){
      echo '<p>'.__(' Incorrect Quiz Id.','vibe').'</p>';
        die();
    }

  $questions = vibe_sanitize(get_post_meta($quiz_id,'quiz_questions'.$user_id,false));
  if(!isset($questions) || !is_array($questions)) // Fallback for Older versions
    $questions = vibe_sanitize(get_post_meta($quiz_id,'vibe_quiz_questions',false));
  
  if(count($questions)):

    echo '<ul class="quiz_questions">';
    $sum=$max_sum=0;
    foreach($questions['ques'] as $key=>$question){
      if(isset($question) && $question){
      $q=get_post($question);
      echo '<li>
          <div class="q">'.apply_filters('the_content',$q->post_content).'</div>';
      $comments_query = new WP_Comment_Query;
      $comments = $comments_query->query( array('post_id'=> $question,'user_id'=>$user_id,'number'=>1,'status'=>'approve') );   
      echo '<strong>';
      _e('Marked Answer :','vibe');
      echo '</strong>';

      $correct_answer=get_post_meta($question,'vibe_question_answer',true);
      foreach($comments as $comment){ // This loop runs only once
        $type = get_post_meta($question,'vibe_question_type',true);

          switch($type){
            case 'select':
            case 'single': 
              $options = vibe_sanitize(get_post_meta($question,'vibe_question_options',false));
              
              echo $options[(intval($comment->comment_content)-1)]; // Reseting for the array
              if(isset($correct_answer) && $correct_answer !=''){
                $ans=$options[(intval($correct_answer)-1)];

              }
            break;  
            case 'multiple': 
              $options = vibe_sanitize(get_post_meta($question,'vibe_question_options',false));
              $ans=explode(',',$comment->comment_content);

              foreach($ans as $an){
                echo $options[intval($an)-1].' ';
              }

              $cans = explode(',',$correct_answer);
              $ans='';
              foreach($cans as $can){
                $ans .= $options[intval($can)-1].', ';
              }
            break;
            case 'match': 
            case 'sort': 
              $options = vibe_sanitize(get_post_meta($question,'vibe_question_options',false));
              $ans=explode(',',$comment->comment_content);

              foreach($ans as $an){
                echo $an.'. '.$options[intval($an)-1].' ';
              }

              $cans = explode(',',$correct_answer);
              $ans='';
              foreach($cans as $can){
                $ans .= $can.'. '.$options[intval($can)-1].', ';
              }
            break;
            case 'fillblank':
            case 'smalltext': 
                echo $comment->comment_content;
                $ans = $correct_answer;
            break;
            default: 
                echo apply_filters('the_content',$comment->comment_content);
                $ans = $correct_answer;
            break;
        }
        $cid=$comment->comment_ID;
        $marks=get_comment_meta( $comment->comment_ID, 'marks', true );
      }

      if(isset($correct_answer) && $correct_answer !=''){
        echo '<strong>';
        _e('Correct Answer :','vibe');
        echo '<span>'.$ans.'</span></strong>';


      }
      

    

      if(isset($marks) && $marks !=''){
          echo '<span class="marking">'.__('Marks Obtained','vibe').' <input type="text" id="'.$cid.'" class="form_field question_marks" value="'.$marks.'" placeholder="'.__('Give marks','vibe').'" />
                <a href="#" class="give_marks button" data-ans-id="'.$cid.'">'.__('Update Marks','vibe').'</a>';

          $sum = $sum+$marks;
      }else{
        echo '<span class="marking">'.__('Marks Obtained','vibe').' <input type="text" id="'.$cid.'" class="form_field question_marks" value="" placeholder="'.__('Give marks','vibe').'" />
        <a href="#" class="give_marks button" data-ans-id="'.$cid.'">'.__('Give Marks','vibe').'</a>';
      }
      $max_sum=$max_sum+intval($questions['marks'][$key]);
      echo '<span> '.__('Total Marks','vibe').' : '.$questions['marks'][$key].'</span>';
      echo '</li>';

      } // IF question check
    } 
    echo '</ul>';
    echo '<div id="total_marks">'.__('Total','vibe').' <strong><span>'.$sum.'</span> / '.$max_sum.'</strong> </div>';
    echo '<a href="#" id="mark_complete" class="button full" data-quiz="'.$quiz_id.'" data-user="'.$user_id.'">'.__('Mark Quiz as Checked','vibe').'</a>';
    endif;

    die();
}



add_action( 'wp_ajax_send_bulk_message', 'send_bulk_message' );
function send_bulk_message(){

    $course_id=$_POST['course'];
    if ( isset($_POST['security']) && wp_verify_nonce($_POST['security'],'security'.$course_id) ){
        echo 'Security check failed !';
        die();
    }
    $members = json_decode(stripslashes($_POST['members']));

    $sender = $_POST['sender'];
    $subject=stripslashes($_POST['subject']);
    if(!isset($subject)){
      _e('Set a Subject for the message','vibe');
      die();  
    }
    $message=stripslashes($_POST['message']);
    if(!isset($message)){
      _e('Set a Subject for the message','vibe');
      die();  
    }
    $sent=0;
    if(count($members) > 0){
      foreach($members as $member){
          if(bp_is_active('messages'))
          if( messages_new_message( array('sender_id' => $sender, 'subject' => $subject, 'content' => $message,   'recipients' => $member ) ) ){
            $sent++;
          }
      }
      echo __('Messages Sent to ','vibe').$sent.__(' members','vibe');
    }else{
      echo __('Please select members','vibe');
    }

    bp_course_record_activity(array(
      'action' => __('Instructor sent Bulk message to students : ','vibe').$subject,
      'content' => __('Bulk Message sent to students ','vibe').$message,
      'type' => 'bulk_action',
      'item_id' => $course_id,
      ));

    die();
}


add_action( 'wp_ajax_add_bulk_students', 'add_bulk_students' );
function add_bulk_students(){
    
    $course_id=$_POST['course'];
    if ( isset($_POST['security']) && wp_verify_nonce($_POST['security'],'security'.$course_id) ){
        echo 'Security check failed !';
        die();
    }

    $members = stripslashes($_POST['members']);
    if(strpos($members,',')){
      $members=explode(',',$members);

      foreach($members as $member){
        if(filter_var($member, FILTER_VALIDATE_EMAIL)) {
          $user_id = email_exists($member);
        }else {
          $user_id = bp_core_get_userid_from_nicename($member);
        }
        if($user_id){
          if(update_post_meta($course_id,$user_id,0)){ // Move forward only if update is successful
           $course_duration = get_post_meta($course_id,'vibe_duration',true);
           $course_duration_parameter = apply_filters('vibe_course_duration_parameter',86400);
           $duration = time() + $course_duration*$course_duration_parameter;
            if(update_user_meta($user_id,$course_id,$duration)){ // Move forward only if update is successful
                update_user_meta($user_id,'course_status'.$course_id,1);
                $group_id=get_post_meta($course_id,'vibe_group',true);
                if(isset($group_id) && $group_id !='')
                  groups_join_group($group_id, $user_id );  

                bp_course_record_activity(array(
                      'action' => __('Instructor added Student for course ','vibe').get_the_title($course_id),
                      'content' => __('Instructore added Student ','vibe').bp_core_get_userlink( $user_id ).__(' subscribed for course ','vibe').get_the_title($course_id),
                      'type' => 'subscribe_course',
                      'item_id' => $course_id,
                      'primary_link'=>get_permalink($course_id),
                      'secondary_item_id'=>$user_id
                    ));      
                $field = vibe_get_option('student_field');
                if(!isset($field) || !$field) $field = 'Location';

                echo '<li id="s'.$user_id.'">
                <input type="checkbox" class="member" value="'.$user_id.'">
                '.bp_core_fetch_avatar ( array( 'item_id' => $user_id, 'type' => 'full' ) ).'
                <h6>'.bp_core_get_userlink( $user_id ).'</h6><span>'.(function_exists('xprofile_get_field_data')?xprofile_get_field_data( $field, $user_id ):'').'</span><ul> 
                <li><a class="tip reset_course_user" data-course="'.$course_id.'" data-user="'.$user_id.'" title="" data-original-title="'.__('Reset Course for User','vibe').'"><i class="icon-reload"></i></a></li>
                <li><a class="tip course_stats_user" data-course="'.$course_id.'" data-user="'.$user_id.'" title="" data-original-title="'.__('See Course stats for User','vibe').'"><i class="icon-bars"></i></a></li>
                <li><a class="tip remove_user_course" data-course="'.$course_id.'" data-user="'.$user_id.'" title="" data-original-title="'.__('Remove User from this Course','vibe').'"><i class="icon-x"></i></a></li>
                </ul></li>'; 
            } 
          }
        }

      }
    }else{ // Same Code as above, just assuming that there are no commas in the entry : re-check for better

        if(filter_var($members, FILTER_VALIDATE_EMAIL)) { 
          $user_id = email_exists($members);
        }else {
          $user_id = bp_core_get_userid_from_nicename($members);
        }
        if($user_id){
          if(update_post_meta($course_id,$user_id,0)){ // Move forward only if update is successful
           $course_duration = get_post_meta($course_id,'vibe_duration',true);
           $course_duration_parameter = apply_filters('vibe_course_duration_parameter',86400);
           $duration = time() + $course_duration*$course_duration_parameter;
            if(update_user_meta($user_id,$course_id,$duration)){ // Move forward only if update is successful
                update_user_meta($user_id,'course_status'.$course_id,1);
                $group_id=get_post_meta($course_id,'vibe_group',true);
                if(isset($group_id) && $group_id !='')
                  groups_join_group($group_id, $user_id );  

                bp_course_record_activity(array(
                      'action' => __('Instructor added Student for course ','vibe').get_the_title($course_id),
                      'content' => __('Instructore added Student ','vibe').bp_core_get_userlink( $user_id ).__(' subscribed for course ','vibe').get_the_title($course_id),
                      'type' => 'subscribe_course',
                      'item_id' => $course_id,
                      'primary_link'=>get_permalink($course_id),
                      'secondary_item_id'=>$user_id
                    ));  
                $field = vibe_get_option('student_field');
                if(!isset($field) || !$field) $field = 'Location';

                echo '<li id="s'.$user_id.'">
                <input type="checkbox" class="member" value="'.$user_id.'">
                '.bp_core_fetch_avatar ( array( 'item_id' => $user_id, 'type' => 'full' ) ).'
                <h6>'.bp_core_get_userlink( $user_id ).'</h6><span>'.(function_exists('xprofile_get_field_data')?xprofile_get_field_data( $field, $user_id ):'').'</span><ul> 
                <li><a class="tip reset_course_user" data-course="'.$course_id.'" data-user="'.$user_id.'" title="" data-original-title="'.__('Reset Course for User','vibe').'"><i class="icon-reload"></i></a></li>
                <li><a class="tip course_stats_user" data-course="'.$course_id.'" data-user="'.$user_id.'" title="" data-original-title="'.__('See Course stats for User','vibe').'"><i class="icon-bars"></i></a></li>
                <li><a class="tip remove_user_course" data-course="'.$course_id.'" data-user="'.$user_id.'" title="" data-original-title="'.__('Remove User from this Course','vibe').'"><i class="icon-x"></i></a></li>
                </ul></li>';        
            } 
          }
        }
    }


    bp_course_record_activity(array(
      'action' => __('Instructor added students in course  ','vibe'),
      'content' => __('Instructor added ','vibe').count($members).__(' students in course ','vibe'),
      'type' => 'bulk_action',
      'item_id' => $course_id,
      ));

    die();
}



/*=== ASSIGN CERTIFICATES & BADGES to STUDENTS FROM FRONT END v 1.5.4 =====*/
add_action( 'wp_ajax_assign_badge_certificates', 'assign_badge_certificates' );
function assign_badge_certificates(){

    $course_id=$_POST['course'];

    if ( isset($_POST['security']) && wp_verify_nonce($_POST['security'],'security'.$course_id) ){
        echo 'Security check failed !';
        die();
    }
    $members = json_decode(stripslashes($_POST['members']));

    $assign_action = $_POST['assign_action'];
    if(!isset($assign_action) && !$assign_action){
      _e('Select Assign Value','vibe');
      die();  
    }

    $assigned=0;
    if(count($members) > 0){
      foreach($members as $mkey=>$member){ 
          if(is_numeric($member) && get_post_type($course_id) == 'course'){

            switch($assign_action){
              case 'add_badge':
                $badges = vibe_sanitize(get_user_meta($member,'badges',false));
                if(isset($badges) && is_array($badges)){
                  $badges[]=$course_id;
                }else{
                  $badges = array($course_id);
                }
                update_user_meta($member,'badges',$badges);
              break;
              case 'add_certificate':
                $certificates = vibe_sanitize(get_user_meta($member,'certificates',false));
                if(isset($certificates) && is_array($certificates)){
                  $certificates[]=$course_id;
                }else{
                    $certificates = array($course_id);
                }
                update_user_meta($member,'certificates',$certificates);
              break;
              case 'remove_badge': 
                $badges = vibe_sanitize(get_user_meta($member,'badges',false));
                $k=array_search($course_id,$badges);
                if(isset($k))
                  unset($badges[$k]);
                $badges = array_values($badges);
                update_user_meta($member,'badges',$badges);
              break;
              case 'remove_certificate':
                $certificates = vibe_sanitize(get_user_meta($member,'certificates',false));
                $k=array_search($course_id,$certificates);
                if(isset($k))
                  unset($certificates[$k]);
                $certificates = array_values($certificates);
                update_user_meta($member,'certificates',$certificates);
              break;
            }
            
            
            $flag=1;
            $assigned++;
          }else{
            $flag=0;
            break;
          }
      }


      if($flag){
        echo __('Action assigned to ','vibe').$assigned.__(' members','vibe');
        bp_course_record_activity(array(
        'action' => __('Instructor assigned/removed Certificate/Badges  ','vibe'),
        'content' => __('Instructor added/removed Badges/Certificates from ','vibe').count($members).__(' students in course ','vibe'),
        'type' => 'bulk_action',
        'item_id' => $course_id,
        ));
      }else
        echo __('Could not assign action to members','vibe');

    }else{
      echo __('Please select members','vibe');
    }

    die();
}

/*=== EXTEND SUBSCRIPTION =====*/
add_action( 'wp_ajax_extend_course_subscription', 'wplms_extend_course_subscription' );
function wplms_extend_course_subscription(){

    $course_id=$_POST['course'];

    if ( isset($_POST['security']) && wp_verify_nonce($_POST['security'],'security'.$course_id) ){
        echo 'Security check failed !';
        die();
    }
    $members = json_decode(stripslashes($_POST['members']));
    $extend_amount = $_POST['extend_amount'];
    if(!isset($extend_amount) || !$extend_amount){
      echo __('Please enter extension amount','vibe');
      die();
    }

    if(!count($members)){
      echo __('Please select members','vibe');
      die();
    }
    $course_duration_parameter = apply_filters('vibe_course_duration_parameter',86400);
    $extend_amount_seconds = $extend_amount*$course_duration_parameter;
    $count=0;$neg=0;
    foreach($members as $member){
        if(is_numeric($member) && get_post_type($course_id) == 'course'){

          $expiry = get_user_meta($member,$course_id,true);
          if(isset($expiry) && $expiry){
            $expiry = $expiry + $extend_amount_seconds;
            update_user_meta($member,$course_id,$expiry);
            $count++;
          }else{
            $neg++;
          }
        }
    }
    if($neg)
      echo sprintf(__('Subscription extended for %d students, unable to extend for %d students','vibe'),$count,$neg);
    else
      echo sprintf(__('Subscription extended for %d students','vibe'),$count);

  die();  
}

/*=== DOWNLOAD STATS =====*/
add_action('wp_ajax_download_stats','wplms_course_download_stats');
function wplms_course_download_stats(){
  $course_id=$_POST['course'];
  if ( isset($_POST['security']) && !wp_verify_nonce($_POST['security'],'security') ){
      echo __('Security check failed !','vibe');
      die();
  } 
  if (!current_user_can('edit_posts') || !is_numeric($course_id)){
      echo __('User does not have capability to download stats !','vibe');
      die();
  }
  
  $fields = json_decode(stripslashes($_POST['fields']));
  $type=stripslashes($_POST['type']);
  if(!isset($type))
    die();

  $users = array();
  $csv = array();$csv_title=array();
  global $wpdb,$bp;

  switch($type){
    case 'all_students':
      $users = $wpdb->get_results($wpdb->prepare("SELECT meta_key as user_id FROM {$wpdb->postmeta} WHERE post_id = %d AND meta_key REGEXP '[0-9]+' AND meta_value REGEXP '[0-9]+'",$course_id),ARRAY_A);
    break;
    case 'finished_students':
      $users = $wpdb->get_results($wpdb->prepare("SELECT meta_key as user_id FROM {$wpdb->postmeta} WHERE post_id = %d AND meta_key REGEXP '[0-9]+' AND meta_value > %d",$course_id,2),ARRAY_A);
    break;
    case 'pursuing_students':
      $users = $wpdb->get_results($wpdb->prepare("SELECT meta_key as user_id FROM {$wpdb->postmeta} WHERE post_id = %d AND meta_key REGEXP '[0-9]+' AND meta_value => %d AND meta_value < %d",$course_id,0,3),ARRAY_A);
    break;
    case 'badge_students':
      $users = $wpdb->get_results($wpdb->prepare("SELECT user_id as user_id FROM {$wpdb->usermeta} WHERE meta_key = %s AND meta_value LIKE %s",'badges',"%$course_id%"),ARRAY_A);
    break;
    case 'certificate_students':
      $users = $wpdb->get_results($wpdb->prepare("SELECT user_id as user_id FROM {$wpdb->usermeta} WHERE meta_key = %s AND meta_value LIKE %s",'certificates',"%$course_id%"),ARRAY_A);
    break;
  }
  if(count($users)){ 
    foreach($users as $user){
      $user_id = $user['user_id'];
      $i=0;
      foreach($fields as $k=>$field){
        switch($field){
          case 'stats_student_start_date':
            $title=__('START DATE','vibe');
            if(!in_array($title,$csv_title))
              $csv_title[$i]=$title;

            $date = $wpdb->get_results($wpdb->prepare("SELECT date_recorded FROM {$bp->activity->table_name} WHERE type=%s AND user_id = %d and item_id = %d",'start_course',$user_id,$course_id));
            if(is_array($date) && is_object($date[0]) && isset($date[0]->date_recorded))
              $csv[$i][]=$date[0]->date_recorded;
            else
              $csv[$i][]=__('N.A','vibe');

          break;
          case 'stats_student_completion_date':
            $title=__('COMPLETION DATE','vibe');
            if(!in_array($title,$csv_title))
              $csv_title[$i]=$title;

            $date = $wpdb->get_results($wpdb->prepare("SELECT date_recorded FROM {$bp->activity->table_name} WHERE type=%s AND user_id = %d and item_id = %d",'submit_course',$user_id,$course_id));
            if(is_array($date) && is_object($date[0]) && isset($date[0]->date_recorded))
              $csv[$i][]=$date[0]->date_recorded;
            else
              $csv[$i][]=__('N.A','vibe');
          break;
          case 'stats_student_id':
            $title=__('ID','vibe');
            if(!in_array($title,$csv_title))
              $csv_title[$i]=$title;

            $csv[$i][] = $user_id;
          break;
          case 'stats_student_name':
            $title=__('NAME','vibe');
            if(!in_array($title,$csv_title))
              $csv_title[$i]=$title;

            $csv[$i][] = bp_core_get_username($user_id);
          break;
          case 'stats_student_unit_status':
            $units=bp_course_get_curriculum_units($course_id);
            foreach($units as $unit_id){
              if(get_post_type($unit_id) == 'unit'){

              $title=get_the_title($unit_id);
              if(!in_array($title,$csv_title))
              $csv_title[$i]=$title;

              if(bp_course_check_unit_complete($unit_id,$user_id)){
                $csv[$i][] = 1;
              }else{
                $csv[$i][] = 0;
              }
              $i++;
              }
            }
            
          break;
          case 'stats_student_quiz_score':
            $units=bp_course_get_curriculum_units($course_id);
            

            foreach($units as $unit_id){
              if(get_post_type($unit_id) == 'quiz'){

                $title=get_the_title($unit_id);
                if(!in_array($title,$csv_title))
                  $csv_title[$i]=$title;

                $score = get_post_meta($unit_id,$user_id,true);
                if(!isset($score) || !$score)
                  $csv[$i][] = __('N.A','vibe');
                else
                  $csv[$i][] = $score;

                $i++;
              }
            }
          break;
          case 'stats_student_badge':
            $title=__('BADGE','vibe');
              if(!in_array($title,$csv_title))
              $csv_title[$i]=$title;
            $check = $wpdb->get_results($wpdb->prepare("SELECT COUNT(meta_key) as count FROM {$wpdb->usermeta} WHERE meta_key = %s AND user_id = %d AND meta_value LIKE %s",'badges',$user_id,"%$course_id%"),ARRAY_A);
            if(isset($check) && is_array($check)){
               if($check[0]['count'])
                $csv[$i][]= 1;
                else
                  $csv[$i][]= 0;
            }
          break;
          case 'stats_student_certificate':
            $title=__('CERTIFICATE','vibe');
            if(!in_array($title,$csv_title))
            $csv_title[$i]=$title;
            $check = $wpdb->get_results($wpdb->prepare("SELECT COUNT(meta_key) as count FROM {$wpdb->usermeta} WHERE meta_key = %s AND user_id = %d AND meta_value LIKE %s",'certificates',$user_id,"%$course_id%"),ARRAY_A);
            if(isset($check) && is_array($check)){
               if($check[0]['count'])
                $csv[$i][]= 1;
                else
                  $csv[$i][]= 0;
            }
          break;
          case 'stats_student_marks':
          $title=__('SCORE','vibe');
          if(!in_array($title,$csv_title))
            $csv_title[$i]=$title;

          $score = get_post_meta($course_id,$user_id,true);
          $csv[$i][]=$score;

          break;
          default;
          do_action_ref_array('wplms_course_stats_process', array( &$csv_title, &$csv,&$i,&$course_id,&$user_id,&$field));
          break;
        }
        $i++;
      }
    }
  }  

  if(!count($csv) || !is_array($csv[0])){
    echo '#';
    die();
  }

  $dir = wp_upload_dir();
  $user_id = get_current_user_id();
  $file_name = 'download_'.$course_id.'_'.$user_id.'.csv';
  $filepath = $dir['basedir'] . '/stats/';
  if(!file_exists($filepath))
    mkdir($filepath,0755);

  $file = $filepath.$file_name;
  if(file_exists($file))
  unlink($file);
  
  
  if (($handle = fopen($file, "w")) !== FALSE) {
    fputcsv($handle,$csv_title);
      
      $rows = count($csv[0]);

      for($i=0;$i<$rows;$i++){
        $arr=array(); 
          foreach ($csv as $key=>$f) {
            $arr[]=$f[$i];
          }
        fputcsv($handle, $arr);  
      }
    }
    fclose($handle);

  $file_url = $dir['baseurl']. '/stats/'.$file_name;

  echo $file_url;
  
  die();
}

add_action('wp_ajax_download_mod_stats','wplms_download_mod_stats');
function wplms_download_mod_stats(){

  $id=$_POST['id'];
  $post_type=$_POST['type'];
  if ( isset($_POST['security']) && !wp_verify_nonce($_POST['security'],'security') ){
      echo __('Security check failed !','vibe');
      die();
  } 
  if (!current_user_can('edit_posts') || !is_numeric($id)){
      echo __('User does not have capability to download stats !','vibe');
      die();
  }
  
  $fields = json_decode(stripslashes($_POST['fields']));
  $type=stripslashes($_POST['select']);
  if(!isset($type))
    die();

  $users = array();
  $csv = array();$csv_title=array();
  global $wpdb,$bp;
  if(in_array($post_type,array('quiz','wplms-assignment'))){
  switch($type){
    case 'all_students':
      $users = $wpdb->get_results($wpdb->prepare("SELECT meta_key as user_id FROM {$wpdb->postmeta} WHERE post_id = %d AND meta_key REGEXP '[0-9]+' AND meta_value REGEXP '[0-9]+'",$id),ARRAY_A);
    break;
    case 'finished_students':
      $users = $wpdb->get_results($wpdb->prepare("SELECT meta_key as user_id FROM {$wpdb->postmeta} WHERE post_id = %d AND meta_value REGEXP '[0-9]+'  AND meta_key IN (SELECT user_id FROM {$wpdb->usermeta} WHERE meta_key = %d AND meta_value < %d)",$id,0,$id,time()),ARRAY_A);
    break;
    }
  }
  if($post_type == 'question'){
    $users = $wpdb->get_results($wpdb->prepare("SELECT user_id as user_id FROM {$wpdb->comments} WHERE comment_post_ID = %d AND comment_approved = %d",$id,1),ARRAY_A);
  }

  if(count($users)){
    foreach($users as $user){
      if(is_numeric($user['user_id']) && $user['user_id']){
      $user_id = $user['user_id'];
      $i=0;
      foreach($fields as $k=>$field){
        switch($field){
          case 'stats_student_start_date':
            $title=__('START DATE','vibe');
            if(!in_array($title,$csv_title))
              $csv_title[$i]=$title;

           if(in_array($post_type,array('quiz','wplms-assignment'))){
            $dtype='start_'.$post_type;
            if($post_type == 'wplms-assignment')
              $dtype='assignment_started';
            
            $date = $wpdb->get_results($wpdb->prepare("SELECT date_recorded FROM {$bp->activity->table_name} WHERE type=%s AND user_id = %d and item_id = %d",$dtype,$user_id,$id));
            }else if($post_type == 'question'){
              $date = $wpdb->get_results($wpdb->prepare("SELECT comment_date as date_recorded FROM {$wpdb->comments} WHERE comment_approved= %d AND user_id = %d and comment_post_ID = %d",1,$user_id,$id));
            }
            if(is_array($date) && is_object($date[0]) && isset($date[0]->date_recorded))
              $csv[$i][]=$date[0]->date_recorded;
            else
              $csv[$i][]=__('N.A','vibe');
          break;
          case 'stats_student_finish_date':
            $title=__('COMPLETION DATE','vibe');
            if(!in_array($title,$csv_title))
              $csv_title[$i]=$title;
            if(in_array($post_type,array('quiz','wplms-assignment'))){
               $dtype='submit_'.$post_type;
               if($post_type == 'wplms-assignment')
              $dtype='assignment_submitted';
            $date = $wpdb->get_results($wpdb->prepare("SELECT date_recorded FROM {$bp->activity->table_name} WHERE type=%s AND user_id = %d and item_id = %d",$dtype,$user_id,$id));
            }else if($post_type == 'question'){
              $date = $wpdb->get_results($wpdb->prepare("SELECT comment_date as date_recorded FROM {$wpdb->comments} WHERE comment_approved= %d AND user_id = %d and comment_post_ID = %d",1,$user_id,$id));
            }
            if(is_array($date) && is_object($date[0]) && isset($date[0]->date_recorded))
              $csv[$i][]=$date[0]->date_recorded;
            else
              $csv[$i][]=__('N.A','vibe');
            
          break;
          case 'stats_student_id':
            $title=__('ID','vibe');
            if(!in_array($title,$csv_title))
              $csv_title[$i]=$title;

            $csv[$i][] = $user_id;
          break;
          case 'stats_student_name':
            $title=__('NAME','vibe');
            if(!in_array($title,$csv_title))
              $csv_title[$i]=$title;

            $csv[$i][] = bp_core_get_username($user_id);
          break;
          case 'stats_question_scores':
            $quiz_dynamic = get_post_meta($id,'vibe_quiz_dynamic',true);
            if(!vibe_validate($quiz_dynamic)){
                $questions = vibe_sanitize(get_post_meta($id,'vibe_quiz_questions',true));
                $i_bkup = $i;
                if(is_array($questions) && is_array($questions['ques'])){
                  foreach($questions['ques'] as $m=>$question){
                    $title = get_the_title($question).' ('.$questions['marks'][$m].') ';
                    if(!in_array($title,$csv_title)){
                      $csv_title[$i_bkup]=$title;  
                      $i_bkup++;
                    }
                  }
                  foreach($questions['ques'] as $m=>$question){
                    $marks = $wpdb->get_results($wpdb->prepare("SELECT meta_value as score FROM {$wpdb->commentmeta} WHERE meta_key = %s AND comment_id IN ( SELECT comment_ID FROM {$wpdb->comments} WHERE comment_approved= %d AND user_id = %d and comment_post_ID = %d )",'marks',1,$user_id,$id));
                    if(isset($marks) && is_array($marks) && is_object($marks[0]) && isset($marks[0]->score))  
                      $csv[$i][]=$marks[0]->score;
                    else
                      $csv[$i][]=0;
                  }
                }
            }
          break;
          case 'stats_student_marks':
          $title=__('SCORE','vibe');
          if(!in_array($title,$csv_title))
            $csv_title[$i]=$title;
          if(in_array($post_type,array('quiz','wplms-assignment'))){
            $score = get_post_meta($id,$user_id,true);
            $csv[$i][]=$score;
          }else if($post_type == 'question'){
            $marks = $wpdb->get_results($wpdb->prepare("SELECT meta_value as score FROM {$wpdb->commentmeta} WHERE meta_key = %s AND comment_id IN ( SELECT comment_ID FROM {$wpdb->comments} WHERE comment_approved= %d AND user_id = %d and comment_post_ID = %d )",'marks',1,$user_id,$id));
            if(isset($marks) && is_array($marks) && is_object($marks[0]) && isset($marks[0]->score))  
              $csv[$i][]=$marks[0]->score;
            else
              $csv[$i][]=0;
          }
          break;
        }
        $i++;
        }
      }
    }
  }  

   $dir = wp_upload_dir();
  $user_id = get_current_user_id();
  $file_name = 'download_'.$id.'_'.$user_id.'.csv';
  $filepath = $dir['basedir'] . '/stats/';
  if(!file_exists($filepath))
    mkdir($filepath,0755);

  $file = $filepath.$file_name;
  if(file_exists($file))
  unlink($file);

  if (($handle = fopen($file, "w")) !== FALSE) {
    fputcsv($handle,$csv_title);
    $rows = count($csv_title);
      for($i=0;$i<$rows;$i++){
        $arr=array();
          foreach ($csv as $key=>$f) {
            $arr[]=$f[$i];
          }

        fputcsv($handle, $arr);  
      }
    }
    fclose($handle);
  //$query=$wpdb->prepare("SELECT * INTO OUTFILE %s FIELDS TERMINATED BY ',' OPTIONALLY ENCLOSED BY '\"' LINES TERMINATED BY '\n' FROM {$wpdb->postmeta} WHERE post_id = %d AND meta_key REGEXP '[0-9]+' AND meta_value REGEXP '[0-9]+'",$file,$course_id);
  //$check = $wpdb->get_results($query);

  //print_r($check);

  $file_url = $dir['baseurl']. '/stats/'.$file_name;

  echo $file_url;


  die();
}
/* == Display and download stats for Quiz/Question/Assignment == */

add_action('wp_ajax_load_stats','wplms_load_cpt_stats');
add_action('wp_ajax_nopriv_load_stats','wplms_load_cpt_stats');
function wplms_load_cpt_stats(){
  $type=$_POST['cpttype'];
  $id=$_POST['id'];
  $check = vibe_get_option('stats_visibility');
  $flag=0;
  if(isset($check)){
    switch($check){
      case '1':
      if(!is_user_logged_in())
        $flag= 1;
      break;
      case '2':
         if(!current_user_can('edit_posts'))
          $flag=1;
      break;
      case '3':
      $user_id = get_current_user_id();
        $instructors = apply_filters('wplms_course_instructors',get_post_field('post_author',$id,'raw'),$id);
        if((!is_array($instructors) || !in_array($user_id,$instructors)) && !current_user_can( 'manage_options' ))
          $flag=1;
      break;
    }
  }
  
  if($flag){
    echo '<div class="message stats_content"><p>'.__('User not allowed to access stats','vibe').'</p></div>';
    die();
  }


  global $wpdb;
  $count=apply_filters('wplms_starts_leader_board',10);
  switch($type){
    case 'quiz':
    case 'wplms-assignment':
      $results = $wpdb->get_results($wpdb->prepare("SELECT meta_key as user_id, meta_value as marks FROM {$wpdb->postmeta} WHERE meta_key REGEXP '[0-9]+' AND post_id = %d ORDER BY meta_value DESC LIMIT 0,%d",$id,$count));
    break;
    case 'question':
      $results = $wpdb->get_results($wpdb->prepare("
        SELECT n.user_id as user_id,m.meta_value as marks
        FROM {$wpdb->comments} AS n
        LEFT JOIN {$wpdb->commentmeta} AS m ON n.comment_ID = m.comment_id
        WHERE  n.comment_post_ID = %d
        AND  n.comment_approved   = %d
        ORDER BY m.meta_value DESC
        LIMIT 0,%d
         ",$id,1,$count));
    break;
  }
  if(!is_array($results)){
    echo '<div class="message stats_content"><p>'.__('No data available','vibe').'</p></div>';
    die();
  }

  foreach($results as $result){
    if(is_numeric($result->marks) && is_numeric($result->user_id) && $result->user_id)
    $user_marks[$result->user_id] = $result->marks;
  }
  echo '<div class="stats_content">
        <h2>'.__('Stats','vibe').'</h2><hr />';
  if(is_array($user_marks)){
    $cnt=count($user_marks);if(!$cnt)$cnt=1;
    $average = round(array_sum($user_marks)/$cnt,2);
    $max = max($user_marks);
    $min = min($user_marks);  
    echo '<h4>'.__('Average','vibe').'<span class="right">'.$average.'</span></h4>
        <h4>'.__('Max','vibe').'<span class="right">'.$max.'</span></h4>
        <h4>'.__('Min','vibe').'<span class="right">'.$min.'</span></h4>';
  }else{
    echo '<div class="message">'.__('N.A','vibe').'</div>';
  }
  
  
  echo '<h3 class="heading">'.__('Leaderboard','vibe').'</h3>';

  //arsort($user_marks);    

  if(is_array($user_marks)){
    echo '<ol class="marks">';
    
    foreach($user_marks as $userid=>$marks){
      if($count){
        echo '<li>'.bp_core_get_user_displayname($userid).'<span class="right">'.$marks.'</span></li>';
      }else{
        break;
      }
    }
    echo '</ol>';
  }
  if(is_user_logged_in()){
    $user_id = get_current_user_id();
    $instructors = apply_filters('wplms_course_instructors',get_post_field('post_author',$id,'raw'),$id);
    if((is_array($instructors) && in_array($user_id,$instructors)) || current_user_can( 'manage_options' )){
      echo '<h3 class="heading" id="download_stats_options">'.__('Download Stats','vibe').'<i class="icon-download-3 right"></i></h3>';
      echo '<div class="select_download_options">
      <ul>
      <li><input type="checkbox" id="stats_student_start_date" class="field" value="1" /><label for="stats_student_start_date">'.__('Start Date/Time','vibe').'</label></li>
      <li><input type="checkbox" id="stats_student_finish_date" class="field" value="1" /><label for="stats_student_finish_date">'.__('End Date/Time','vibe').'</label></li>
      <li><input type="checkbox" id="stats_student_id" class="field" value="1" /><label for="stats_student_id">'.__('ID','vibe').'</label></li>
      <li><input type="checkbox" id="stats_student_name" class="field" value="1" /><label for="stats_student_name">'.__('Student Name','vibe').'</label></li>';
      if($type == 'quiz'){
        echo '<li><input type="checkbox" id="stats_question_scores" class="field" value="1" /><label for="stats_question_scores">'.__('Question scores (* for static quizzes only)','vibe').'</label></li>';
      }
      echo '<li><input type="checkbox" id="stats_student_marks" class="field" value="1" /><label for="stats_student_marks">'.__('Score','vibe').'</label></li>
      </ul>';
      echo '<br class="clear" /><select id="stats_students">
      <option value="all_students">'.__('All students','vibe').'</option>
      <option value="finished_students">'.__('Students who finished','vibe').'</option>
      </select>';
      wp_nonce_field('security','stats_security');
      echo '<a class="button full" id="download_mod_stats" data-type="'.$type.'" data-id="'.$id.'"><i class="icon-download-3"></i> '.__('Process Stats','vibe').'</a>
      </div>';
    } 
  }
  echo '</div>';         
  die();
}

/*=== UNIT TRAVERSE =====*/
add_action('wp_ajax_unit_traverse', 'unit_traverse');
add_action( 'wp_ajax_nopriv_unit_traverse', 'unit_traverse' );

function unit_traverse(){
  $unit_id= $_POST['id'];
  $course_id = $_POST['course_id'];
  if ( !isset($_POST['security']) || !wp_verify_nonce($_POST['security'],'security') ){
     _e('Security check Failed. Contact Administrator.','vibe');
     die();
  }
  // Check if user has taken the course
  $user_id = get_current_user_id();
  $coursetaken=get_user_meta($user_id,$course_id,true);
  //if(!isset($_COOKIE['course'])) {
    if($coursetaken>time()){
      setcookie('course',$course_id,$expire,'/');
      $_COOKIE['course'] = $course_id;
    }else{
      $pid=get_post_meta($course_id,'vibe_product',true);
      $pid=apply_filters('wplms_course_product_id',$pid,$course_id,-1); // $id checks for Single Course page or Course page in the my courses section
      if(is_numeric($pid))
        $pid=get_permalink($pid);

      echo '<div class="message"><p>'.__('Course Expired.','vibe').'<a href="'.$pid.'" class="link alignright">'.__('Click to renew','vibe').'</a></p></div>';
      die();
    }
  //}
  
  if(isset($coursetaken) && $coursetaken){
      
      $course_curriculum=vibe_sanitize(get_post_meta($course_id,'vibe_course_curriculum',false));
      

        $units=array();
          foreach($course_curriculum as $key=>$curriculum){
            if(is_numeric($curriculum)){
                $units[]=$curriculum;
            }
          }

      // Drip Feed Check    
      $drip_enable=get_post_meta($course_id,'vibe_course_drip',true);

      
      if(vibe_validate($drip_enable)){
          $drip_duration = get_post_meta($course_id,'vibe_course_drip_duration',true);
          $unitkey = array_search($unit_id,$units);
          if($unitkey == 0){
            $pre_unit_time=get_post_meta($units[$unitkey],$user_id,true);
            if(!isset($pre_unit_time) || $pre_unit_time ==''){
              add_post_meta($units[$unitkey],$user_id,time());
            }
          }else{
             $pre_unit_time=get_post_meta($units[($unitkey-1)],$user_id,true);
             if(isset($pre_unit_time) && $pre_unit_time){
                $drip_duration_parameter = apply_filters('vibe_drip_duration_parameter',86400);
                $value = $pre_unit_time + $drip_duration*$drip_duration_parameter;
                $value = apply_filters('wplms_drip_value',$value,$units[($unitkey-1)],$course_id,$units[$unitkey]);
               if($value > time()){
                      echo '<div class="message"><p>'.__('Unit will be available in ','vibe').tofriendlytime($value-time()).'</p></div>';
                      die();
                  }else{
                      $pre_unit_time=get_post_meta($units[$unitkey],$user_id,true);
                      if(!isset($pre_unit_time) || $pre_unit_time ==''){
                        add_post_meta($units[$unitkey],$user_id,time());

                        bp_course_record_activity(array(
                          'action' => __('Student started a unit','vibe'),
                          'content' => __('Student started the unit ','vibe').get_the_title($unit_id).__(' in course ','vibe').get_the_title($course_id),
                          'type' => 'unit',
                          'primary_link' => get_permalink($unit_id),
                          'item_id' => $unit_id,
                          'secondary_item_id' => $user_id
                        ));
                      }
                  } 
              }else{
                  echo '<div class="message"><p>'.__('Unit can not be accessed.','vibe').'</p></div>';
                  die();
              }    
            }
          }  
        
      

      // END Drip Feed Check  
      
      echo '<div id="unit" class="unit_title" data-unit="'.$unit_id.'">';
      the_unit_tags($unit_id);
      the_unit_instructor($unit_id);
      $minutes=0;
      $mins = get_post_meta($unit_id,'vibe_duration',true);
      $unit_duration_parameter = apply_filters('vibe_unit_duration_parameter',60);
      if($mins){
        if($mins > $unit_duration_parameter){
          $hours = floor($mins/$unit_duration_parameter);
          $minutes = $mins - $hours*$unit_duration_parameter;
        }else{
          $minutes = $mins;
        }
      
        do_action('wplms_course_unit_meta');
        if($mins < 9999){ 
          if($unit_duration_parameter == 1)
            echo '<span><i class="icon-clock"></i> '.(isset($hours)?$hours.__(' Minutes','vibe'):'').' '.$minutes.__(' seconds','vibe').'</span>';
          else if($unit_duration_parameter == 60)
            echo '<span><i class="icon-clock"></i> '.(isset($hours)?$hours.__(' Hours','vibe'):'').' '.$minutes.__(' minutes','vibe').'</span>';
          else if($unit_duration_parameter == 3600)
            echo '<span><i class="icon-clock"></i> '.(isset($hours)?$hours.__(' Days','vibe'):'').' '.$minutes.__(' hours','vibe').'</span>';
        } 

      }
      echo '<div class="clear"></div>';
      echo '<h1>'.get_the_title($unit_id).'</h1>';
      echo '<h3>';
        the_sub_title($unit_id);
      echo '</h3></div>';
      the_unit($unit_id);  
      
      
              $unit_class='unit_button';
              $hide_unit=0;
              $nextunit_access = vibe_get_option('nextunit_access');
              

              $k=array_search($unit_id,$units);
              $done_flag=get_user_meta($user_id,$unit_id,true);

              $next=$k+1;
              $prev=$k-1;
              $max=count($units)-1;

              echo  '<div class="unit_prevnext"><div class="col-md-3">';
              if($prev >=0){

                if(get_post_type($units[$prev]) == 'quiz'){
                  $quiz_status = get_user_meta($user_id,$units[$prev],true);
                  if(!empty($quiz_status))
                      echo '<a href="#" data-unit="'.$units[$prev].'" class="'.$unit_class.'">'.__('Back to Quiz','vibe').'</a>';
                  else          
                      echo '<a href="'.get_permalink($units[$prev]).'" class="unit_button">'.__('Back to Quiz','vibe').'</a>';

                }else    
                  echo '<a href="#" id="prev_unit" data-unit="'.$units[$prev].'" class="unit unit_button">'.__('Previous Unit','vibe').'</a>';
              }
              echo '</div>';

              echo  '<div class="col-md-6">';
              if(get_post_type($units[($k)]) == 'quiz'){
                $quiz_status = get_user_meta($user_id,$units[($k)],true);
                if(!empty($quiz_status)){
                    echo '<a href="'.bp_loggedin_user_domain().BP_COURSE_SLUG.'/'.BP_COURSE_RESULTS_SLUG.'/?action='.$units[($k)].'" class="quiz_results_popup">'.__('Check Results','vibe').'</a>';
                }else{
                    echo '<a href="'.get_permalink($units[($k)]).'" class=" unit_button">'.__('Start Quiz','vibe').'</a>';
                }
              }else  
                  echo ((isset($done_flag) && $done_flag)?'': apply_filters('wplms_unit_mark_complete','<a href="#" id="mark-complete" data-unit="'.$units[($k)].'" class="unit_button">'.__('Mark this Unit Complete','vibe').'</a>',$unit_id,$course_id));

              echo '</div>';

              echo  '<div class="col-md-3">';

              if($next <= $max){

                if(isset($nextunit_access) && $nextunit_access){
                    $hide_unit=1;

                    if(isset($done_flag) && $done_flag){
                      $unit_class .=' ';
                      $hide_unit=0;
                    }else{
                      $unit_class .=' hide';
                      $hide_unit=1;
                    }
                }

                if(get_post_type($units[$next]) == 'quiz'){
                  $quiz_status = get_user_meta($user_id,$units[$next],true);
                  if(!empty($quiz_status))
                      echo '<a href="#" data-unit="'.$units[$next].'" class="unit '.$unit_class.'">'.__('Proceed to Quiz','vibe').'</a>';
                  else          
                      echo '<a href="'.get_permalink($units[$next]).'" class=" unit_button">'.__('Proceed to Quiz','vibe').'</a>';
                }else  
                  echo '<a href="#" id="next_unit" '.(($hide_unit)?'':'data-unit="'.$units[$next].'"').' class="unit '.$unit_class.'">'.__('Next Unit','vibe').'</a>';
              }
              echo '</div></div>';
          
        }
        die();
}  

?>