<?php

class WPLMS_tips{

	var $settings;
	function __construct(){
		add_filter('login_redirect',array($this,'instructor_login_redirect'),100,3);
		$lms_settings = get_option('lms_settings');
		if(is_array($lms_settings) && isset($lms_settings['general'])){
			$this->settings = $lms_settings['general'];
			foreach($this->settings as $key=>$setting){
				switch($key){
					case 'instructor_login_redirect':
					case 'student_login_redirect':
						add_filter('login_redirect',array($this,'login_redirect'),100,3);
					break;
					break;
					case 'hide_course_members':
						add_filter('wplms_course_nav_menu',array($this,'coursenav_remove_members'));
					break;
					case 'course_curriculum_below_description':
						add_filter('wplms_course_nav_menu',array($this,'coursenav_remove_curriculum'));
						add_action('wplms_after_course_description',array($this,'course_curriculum_below_description'));  
					break;
					case 'admin_instructor':
						add_filter('wplms_show_admin_in_instructors',array($this,'hide_admin_in_instructor'));
					break;
					case 'unit_quiz_start_datetime':
						add_filter('wplms_unit_metabox',array($this,'show_unit_date_time_backend'));
						add_filter('wplms_front_end_unit_settings',array($this,'add_date_time_field'));
						add_action('wplms_front_end_unit_settings_form',array($this,'show_date_time_field'),10,1);
						add_action('wplms_front_end_save_unit_settings_extras',array($this,'save_unit_extra_settings'),10,1);
						add_filter('wplms_drip_value',array($this,'apply_unit_date_time_drip_feed'),10,4);
					break;
					case 'one_session_per_user':
						add_filter( 'authenticate',array($this,'one_session_per_user'), 30, 3 );
					break;
					case 'course_codes':
						add_filter('wplms_course_product_metabox',array($this,'course_codes_setting'));
						add_filter('wplms_frontend_create_course_pricing',array($this,'wplms_front_end_course_codes'));
						add_action('wplms_front_end_pricing_content',array($this,'wplms_front_end_show_course_codes'),10,1);
						add_action('wplms_front_end_save_course_pricing',array($this,'wplms_front_end_save_course_codes'),10,1);
						add_action('bp_before_course_body',array($this,'wplms_course_code_check'));
					break;
					case 'woocommerce_account':
						add_filter('wplms_logged_in_top_menu',array($this,'wplms_woocommerce_orders_link'));  
					break;
					case 'wplms_course_delete':
						add_filter('wplms_front_end_course_delete',array($this,'enable_front_end_course_deletion'));
					break;
					case 'disable_autofree':
						add_filter('wplms_auto_subscribe',array($this,'disable_auto_subscribe'));
						add_filter('wplms_private_course_button',array($this,'manual_subscription'),10,2);
						add_filter('wplms_private_course_button_label',array($this,'free_label'),10,2);
						add_action('bp_before_course_body',array($this,'subscribe_free_course'));
					break;
					case 'default_order':
						add_filter('wplms_course_drectory_default_order',array($this,'default_order'));
					break;
				}
			}
		}
	}
	function default_order($order){
		switch($this->settings['default_order']){
			case 'date':
				$order['orderby']['date']='DESC';
			break;
			case 'title':
				$order['orderby']['title']='ASC';
			break;
			case 'popular':
				$order['orderby']['meta_value']='DESC';
				$order['meta_key']='vibe_students';
			break;
			case 'rated':
				$order['orderby']['meta_value']='DESC';
				$order['meta_key']='average_rating';
			break;
		}
		return $order;
	}
	function subscribe_free_course(){
		global $post;
		if(isset($_GET['subscribe'])){
			$free = get_post_meta($post->ID,'vibe_course_free',true);
			if(vibe_validate($free)){
				$user_id = get_current_user_id();
				bp_course_add_user_to_course($user_id,$post->ID);
			}
		}
	}
	function manual_subscription($link,$course_id){
		$free = get_post_meta($course_id,'vibe_course_free',true);
		if(vibe_validate($free)){
			$link = get_permalink($course_id).'?subscribe';
		}
		return $link;
	}
	function free_label($label,$course_id){
		$free = get_post_meta($course_id,'vibe_course_free',true);
		if(vibe_validate($free)){
			$label = __('Take this Course','vibe-customtypes');	
		}
		return $label;
	}
	function disable_auto_subscribe($flag){
		return 0;
	}
	function enable_front_end_course_deletion($flag){
		return 1;
	}
	function wplms_woocommerce_orders_link($loggedin_menu){
            $myaccount_page_id = get_option( 'woocommerce_myaccount_page_id' );
            if ( isset($myaccount_page_id) && is_numeric($myaccount_page_id) ) {
              $loggedin_menu['orders']=array(
                          'icon' => 'icon-list',
                          'label' => __('My Orders','vibe'),
                          'link' =>get_permalink( $myaccount_page_id )
                          );
            }
            $pmpro_account_page_id = get_option('pmpro_account_page_id');
            if ( isset($pmpro_account_page_id ) && is_numeric($pmpro_account_page_id ) ) {
              $loggedin_menu['membership']=array(
                          'icon' => 'icon-archive',
                          'label' => __('My Membership','vibe'),
                          'link' =>get_permalink( $pmpro_account_page_id )
                          );
            }
		return  $loggedin_menu;
    }

	function wplms_course_code_check(){
		$user_id=get_current_user_id();
    	$course_id =get_the_ID();
    	$course_codes = get_post_meta($course_id,'vibe_course_codes',true);
		if($_POST['submit_course_codes']){
      		if ( !isset($_POST['security_code']) || !wp_verify_nonce($_POST['security_code'],'security'.$user_id) ){
			    echo '<p class="message">'.__('Security check Failed. Contact Administrator.','vibe-customtypes').'</p>';
		    }else{
		    	$code = $_POST['course_code'];
	    		$pos=strpos($course_codes,$code);
	    		if($pos === false){
	    			echo '<p class="message">'.__('Code does not exist. Please check the code.','vibe-customtypes').'</p>';
	    		}else{	
	    			$codes = explode(',',$course_codes);
	    			if(strpos($course_codes,'|')){
		    			foreach($codes as $ccode){
		    				$ccodes = explode('|',$ccode);
		    				if(in_array($code,$ccodes)){
		    					$total_count = $ccodes[1];
		    					break;
		    				}
		    			}
		    			global $wpdb,$bp;
		    			$count = $wpdb->get_var($wpdb->prepare("SELECT count(*) FROM {$bp->activity->table_name} WHERE component = %s AND type = %s AND content = %s AND item_id = %d",'course','course_code',$code,$course_id));
		    			
		    			if($count <= $total_count){
		    				if(!wplms_user_course_check($user_id,$course_id)){
				    			bp_course_record_activity(array(
						          'action' => __('Course code applied','vibe'),
						          'content' => $code,
						          'type' => 'course_code',
						          'item_id' => $course_id,
						          'primary_link'=>get_permalink($course_id),
						          'secondary_item_id'=>$user_id
						        )); 
						        bp_course_add_user_to_course($user_id,$course_id);
						        echo '<p class="message success">'.__('Congratulations! You are now added to the course.','vibe-customtypes').'</p>';
				    		}else{
				    			echo '<p class="message">'.__('User already in course.','vibe-customtypes').'</p>';
				    		}
		    			}else{
		    				echo '<p class="message">'.__('Maximum number of usage for course code exhausted','vibe-customtypes').'</p>';
		    			}
		    		}else{
		    			if(!wplms_user_course_check($user_id,$course_id)){
		    				bp_course_record_activity(array(
					          'action' => __('Course code applied','vibe'),
					          'content' => $code,
					          'type' => 'course_code',
					          'item_id' => $course_id,
					          'primary_link'=>get_permalink($course_id),
					          'secondary_item_id'=>$user_id
					        )); 
			    			bp_course_add_user_to_course($user_id,$course_id);
			    			echo '<p class="message success">'.__('Congratulations! You are now added to the course.','vibe-customtypes').'</p>';
			    		}else{
			    			echo '<p class="message">'.__('User already in course.','vibe-customtypes').'</p>';
			    		}
		    		}
	    		}
		    }
      	}
	}
	function wplms_front_end_save_course_codes($course_id){
		if($_POST['extras']){ 
			$extras = json_decode(stripslashes($_POST['extras']));
	        if(is_array($extras) && isset($extras))
	        foreach($extras as $c){
	           update_post_meta($course_id,$c->element,$c->value);
	        }
		}
	}
	function wplms_front_end_show_course_codes($course_id){
		$course_codes='';
		if(isset($_GET['action']) && is_numeric($_GET['action'])){
            $course_id = $_GET['action'];
            $course_codes = get_post_meta($course_id,'vibe_course_codes',true);
        }
		echo '<li class="course_membership"><h3>'.__('Course Codes','vibe-customtypes').'<span>
                  <textarea id="vibe_course_codes" class="vibe_extras" placeholder="'.__('Enter Course codes (XXX|2,YYY|4)','vibe-customtypes').'" >'.$course_codes.'</textarea>
              </span>
              </h3>
          </li>';
	}
	function wplms_front_end_course_codes($settings){
		$settings['vibe_course_codes']='';
		if(isset($_GET['action']) && is_numeric($_GET['action'])){
            $course_id = $_GET['action'];
            $settings['vibe_course_codes'] = get_post_meta($course_id,'vibe_course_codes',true);
        }
		return $settings;
	}
	function course_codes_setting($setting){
		$setting[]=array( // Text Input
					'label'	=> __('Set Course purchase codes','vibe-customtypes'), // <label>
					'desc'	=> __('Student can gain access to Course using course codes (multiple codes comma saperated, usage count pipe saperate eg : xxx|2,yyy|4)','vibe-customtypes'), // description
					'id'	=> 'vibe_course_codes', // field id and name
					'type'	=> 'textarea', // type of field
				);
		return $setting;
	}
	function show_unit_date_time_backend($settings){
		$prefix='vibe_';
		$settings[]= array( // Text Input
					'label'	=> __('Access Date','vibe-customtypes'), // <label>
					'desc'	=> __('Date on which unit is accessible','vibe-customtypes'), // description
					'id'	=> $prefix.'access_date', // field id and name
					'type'	=> 'date', // type of field
				);
		$settings[]=array( // Text Input
					'label'	=> __('Access Time','vibe-customtypes'), // <label>
					'desc'	=> __('Time after which unit is accessible','vibe-customtypes'), // description
					'id'	=> $prefix.'access_time', // field id and name
					'type'	=> 'time', // type of field
				);
		return $settings;
	}
	function add_date_time_field($unit_settings){
		$unit_settings['vibe_access_date']='';
		$unit_settings['vibe_access_time']='';
		$vibe_access_date= get_post_meta(get_the_ID(),'vibe_access_date',true);
		$vibe_access_time= get_post_meta(get_the_ID(),'vibe_access_time',true);
		if(isset($vibe_access_date) && isset($vibe_access_time) && $vibe_access_date && $vibe_access_time){
			$unit_settings['vibe_access_date']=$vibe_access_date;
			$unit_settings['vibe_access_time']=$vibe_access_time;
		}
		return $unit_settings;
	}
	function show_date_time_field($unit_settings){
		wp_enqueue_script( 'jquery-ui-datepicker', array( 'jquery', 'jquery-ui-core' ) );
		wp_enqueue_script( 'timepicker_box', VIBE_PLUGIN_URL . '/vibe-customtypes/metaboxes/js/jquery.timePicker.min.js', array( 'jquery' ) );
		echo '<script>
		jQuery(document).ready(function(){
				jQuery( ".datepicker" ).datepicker({
                    dateFormat: "yy-mm-dd",
                    numberOfMonths: 1,
                    showButtonPanel: true,
                });
                 jQuery( ".timepicker" ).each(function(){
                 jQuery(this).timePicker({
                      show24Hours: false,
                      separator:":",
                      step: 15
                  });
                });});</script>
		     <li><label>'.__('Unit access date','vibe-customtypes').'</label>
                <h3>'.__('Access date','wplms-front-end').'<span>
                <input type="text" class="datepicker vibe_extras" id="vibe_access_date" value="'.$unit_settings['vibe_access_date'].'" /> 
            </li><li><label>'.__('Unit access time','vibe-customtypes').'</label>
                <h3>'.__('Access time','wplms-front-end').'<span>
                <input type="text" class="timepicker vibe_extras" id="vibe_access_time" value="'.$unit_settings['vibe_access_time'].'" /> 
            </li>';
	}
	function save_unit_extra_settings($unit_id){
		if($_POST['extras']){
			$extras = json_decode(stripslashes($_POST['extras']));
	        if(is_array($extras) && isset($extras))
	        foreach($extras as $c){
	           update_post_meta($unit_id,$c->element,$c->value);
	        }
		}
	}

	function apply_unit_date_time_drip_feed($value,$pre_unit_id,$course_id,$unit_id){
		$vibe_access_date= get_post_meta($unit_id,'vibe_access_date',true);
		$vibe_access_time= get_post_meta($unit_id,'vibe_access_time',true);
		if(isset($vibe_access_date) && isset($vibe_access_time) && $vibe_access_date && $vibe_access_time){
			$value=strtotime($vibe_access_date.' '.$vibe_access_time);
		}
		return $value;
	}
	function custom_wplms_login_widget_action($url){
        return wp_login_url( get_permalink() );
	}  
	function login_redirect($redirect_url,$request_url,$user){
		global $bp;
		global $user;
		if(is_a($user,'WP_User')){
			if (isset($user->allcaps['edit_posts'])) {
				switch($this->settings['instructor_login_redirect']){
					case 'profile':
						$redirect_url=bp_core_get_user_domain($user->ID);
					break;
					case 'mycourses':
						$redirect_url=bp_core_get_user_domain($user->ID).'/'.BP_COURSE_SLUG;
					break;
					case 'instructing_courses': 
						$redirect_url=bp_core_get_user_domain($user->ID).'/'.BP_COURSE_SLUG.'/instructor-courses';
					break;
					case 'dashboard':
						$redirect_url=bp_core_get_user_domain($user->ID).'/dashboard';
					break;
					default:
						$redirect_url=site_url();
					break;
				}
			}else{
				switch($this->settings['student_login_redirect']){
					case 'profile':
						$redirect_url=bp_core_get_user_domain($user->ID);
					break;
					case 'mycourses':
						$redirect_url=bp_core_get_user_domain($user->ID).'/'.BP_COURSE_SLUG;
					break;
					case 'dashboard':
						$redirect_url=bp_core_get_user_domain($user->ID).'/dashboard';
					break;
					default:
						$redirect_url=site_url();
					break;
				}
			}
		}
		return $redirect_url;
	}

	function hide_admin_in_instructor($flag){ 
		return 0;
	}
	function instructor_login_redirect($redirect_url,$request_url,$user){
		global $bp; 
		$user_id = get_current_user_id();
		if(current_user_can('edit_posts'))
		return $redirect_url;
	}

	function coursenav_remove_members($menu_array){
		unset($menu_array['members']);
        return $menu_array;
	}

	function coursenav_remove_curriculum($menu_array){
		unset($menu_array['curriculum']);
        return $menu_array;
	}
	function course_curriculum_below_description(){

		global $post;
		$id= get_the_ID();
		$class='';
		if(isset($this->settings['curriculum_accordion']))
			$class="accordion";
		?>

			<div class="course_curriculum <?php echo $class; ?>">
				<h3 class="review_title"><?php  _e('Course Curriculum','vibe'); ?></h3>
			<?php
			do_action('wplms_course_curriculum_section',$id);
			$course_curriculum = vibe_sanitize(get_post_meta($id,'vibe_course_curriculum',false));

			if(isset($course_curriculum)){


				foreach($course_curriculum as $lesson){
					if(is_numeric($lesson)){
						$icon = get_post_meta($lesson,'vibe_type',true);

						if(get_post_type($lesson) == 'quiz')
							$icon='task';

								$href=get_the_title($lesson);
								$free='';
								$free = get_post_meta($lesson,'vibe_free',true);

								$curriculum_course_link = apply_filters('wplms_curriculum_course_link',0);
								if(vibe_validate($free) || ($post->post_author == get_current_user_id()) || current_user_can('manage_options') || $curriculum_course_link){
									$href=apply_filters('wplms_course_curriculum_free_access','<a href="'.get_permalink($lesson).'?id='.get_the_ID().'">'.get_the_title($lesson).(vibe_validate($free)?'<span>'.__('FREE','vibe').'</span>':'').'</a>',$lesson,$free);
								}

						echo '<div class="course_lesson">
								<i class="icon-'.$icon.'"></i><h6>'.apply_filters('wplms_curriculum_course_lesson',$href,$lesson).'</h6>';
								$minutes=0;
								$hours=0;
								$min = get_post_meta($lesson,'vibe_duration',true);
								$minutes = $min;
								if($minutes){
									if($minutes > 60){
										$hours = intval($minutes/60);
										$minutes = $minutes - $hours*60;
									}
								echo apply_filters('wplms_curriculum_time_filter','<span><i class="icon-clock"></i> '.(isset($hours)?$hours.__(' Hours','vibe'):'').' '.$minutes.' '.__('minutes','vibe').'</span><b>'.((isset($hours) && $hours)?$hours:"00").':'.$minutes.'</b>',$min);
								}	

								echo '</div>';
					}else{
						echo '<h5 class="course_section">'.$lesson.'</h5>';
					}
				}
			}
				?>
			</div>
		<?php
	}

	function one_session_per_user( $user, $username, $password ) { 
		
		if(isset($user->allcaps['edit_posts']) && $user->allcaps['edit_posts']){
			return $user;
		}
		$sessions = WP_Session_Tokens::get_instance( $user->ID );
    	$all_sessions = $sessions->get_all();
		if ( count($all_sessions) ) {
			$user = new WP_Error('already_signed_in', __('<strong>ERROR</strong>: User already logged in.','vibe-customtypes'));
		}
	    return $user;
	}
}

add_action('init','wplms_tips_init');
function wplms_tips_init(){
	$tips = new WPLMS_tips();
}


add_action( 'widgets_init', 'wplms_course_code_widget');
function wplms_course_code_widget(){
	register_widget('wplms_course_codes');
}

class wplms_course_codes extends WP_Widget {
 
	function wplms_course_codes() {
	    $widget_ops = array( 'classname' => 'wplms_course_codes', 'description' => __('WPLMS Course codes widget', 'vibe') );
	    $control_ops = array( 'width' => 300, 'height' => 350, 'id_base' => 'wplms_course_codes' );
	    $this->WP_Widget( 'wplms_course_codes', __('WPLMS Course Codes', 'vibe'), $widget_ops, $control_ops );
  	}
        
    function widget( $args, $instance ) {
    	if(!is_singular(BP_COURSE_CPT) || !defined('BP_COURSE_CPT') || !is_user_logged_in())
    		return;

    	$user_id=get_current_user_id();
    	$course_id =get_the_ID();
    	$course_codes = get_post_meta($course_id,'vibe_course_codes',true);
    	if(!isset($course_codes) || strlen($course_codes)<2)
    		return;

    	extract( $args );
    	$title = apply_filters('widget_title', $instance['title'] );

    	echo $before_widget;
    	// Display the widget title 
    	if ( $title )
      		echo $before_title . $title . $after_title;

      	echo '<form method="post">
      			<input type="text" name="course_code" class="form_field" placeholder="'.$placeholder.'"/>';
      			wp_nonce_field('security'.$user_id,'security_code');
      	echo '<input type="submit" name="submit_course_codes" value="'.__('Submit','vibe-customtypes').'"/></form>';
    	echo $after_widget;
    }
 
    /** @see WP_Widget::update -- do not rename this */
    function update($new_instance, $old_instance) {   
	    $instance = $old_instance;
	    $instance['title'] = strip_tags($new_instance['title']);
	    $instance['placeholder'] = $new_instance['placeholder'];
        return $instance;
    }
 
    /** @see WP_Widget::form -- do not rename this */
    function form($instance) {  
    $defaults = array( 
        'title'  => __('Enter Course code','vibe-customtypes'),
        'placeholder'  => __('Place holder text','vibe-customtypes'),
    );
    $instance = wp_parse_args( (array) $instance, $defaults );                 
    ?>
    <p> <?php _e('Title','vibe'); ?> <input type="text" class="text" name="<?php echo $this->get_field_name('title'); ?>" value="<?php echo $instance['title']; ?>" /></p>
    <p> <?php _e('Course Codes input box text','vibe'); ?> <input type="text" class="text" name="<?php echo $this->get_field_name('placeholder'); ?>" value="<?php echo $instance['placeholder']; ?>" /></p>
	<?php
    }
}
