<?php

add_action( 'widgets_init', 'wplms_dash_course_progress' );

function wplms_dash_course_progress() {
    register_widget('wplms_course_progress');
}

class wplms_course_progress extends WP_Widget {
 
 
    /** constructor -- name this the same as the class above */
    function wplms_course_progress() {
    $widget_ops = array( 'classname' => 'wplms_course_progress', 'description' => __('Student Progress in Courses', 'wplms-dashboard') );
    $control_ops = array( 'width' => 300, 'height' => 350, 'id_base' => 'wplms_course_progress' );
    $this->WP_Widget( 'wplms_course_progress', __(' DASHBOARD : Course Progress', 'wplms-dashboard'), $widget_ops, $control_ops );

    //Start recording Course Progress
    add_action('badgeos_wplms_unit_complete',array($this,'wplms_course_progress_record'),10,3);
    add_action('wplms_student_course_reset',array($this,'wplms_student_course_reset'),10,2);
    add_action('wplms_student_course_remove',array($this,'wplms_student_course_remove'),10,2);
  }
        
 
    /** @see WP_Widget::widget -- do not rename this */
    function widget( $args, $instance ) {
    extract( $args );

    //Our variables from the widget settings.
    $title = apply_filters('widget_title', $instance['title'] );
    $num =  $instance['number'];
    $finished =  $instance['finished'];
    $width =  $instance['width'];
    echo '<div class="'.$width.'">
            <div class="dash-widget">'.$before_widget;

    // Display the widget title 
    if ( $title )
      	echo $before_title . $title . $after_title;
    		global $wpdb,$bp;

        $user_id = get_current_user_id();

        $meta_query=array(
              array(
                'key' => $user_id,
                'compare' => 'EXISTS'
                )
            );

        if(!isset($finished) || !$finished){
          
          $meta_query=array(array(
            'key' => $user_id,
            'compare' => '<',
            'value' => 2
            ));
        }
        $query_args = array(
          'post_status'  => 'publish',
          'post_type'  => BP_COURSE_CPT,
          'order' => 'ASC',
          'orderby'=> 'meta_value_num',
          'meta_key' => $user_id,
          'posts_per_page' => $num,
          'paged'    => $paged,
          'meta_query'   => $meta_query
        );

  		$query_args=apply_filters('wplms_dashboard_course_progerss', $query_args);
      $query = new WP_Query($query_args);

		if($query->have_posts()){
      echo "<script>
          jQuery(document).ready(function($){
            $('.dash-courses-progress li').each(function(){
              var cookie_id = 'course_progress'+$(this).find('.course_progress').attr('data-course');
              if($.cookie(cookie_id)!= null){
                $(this).find('.course_progress .bar').css('width',$.cookie(cookie_id)+'%');
                $(this).find('.course_progress').prev().find('span').text($.cookie(cookie_id)+'%');
              }
            });
          });
          </script>";
			echo '<div class="course_progress">
					   <ul class="dash-courses-progress">';
             $i=0;
			while($query->have_posts()){
        $query->the_post();

        $st = get_post_meta(get_the_ID(),$user_id,true);
        $percentage = 100;
        switch($st){
          case 0: $status = __('START','wplms-dashboard');
          $percentage = 0;
          break;
          case 1: $status = __('CONTINUE','wplms-dashboard');
          $prgrss='progress'.get_the_ID();

          $percentage = get_user_meta($user_id,$prgrss,true);
          if(!isset($percentage) || !$percentage){
            $percentage = $this->calculate_course_progress(get_the_ID());
          }
          break;
          case 2: $status = __('SUBMITTED','wplms-dashboard');
          break;
          default: $status = __('FINISHED','wplms-dashboard');
          break;
        }
				echo '<li>
              <strong><a href="'.get_permalink().'">'.get_the_title().'</a><span>'.$percentage.'%</span></strong>
							<div class="progress course_progress" data-course="'.get_the_ID().'">
               <div class="bar animate stretchRight" style="width: '.$percentage.'%; background-color:'.wplms_get_random_color($i).'"></div>
             </div>
					  </li>';
          $i++;
				}			  
    			echo '</ul>
    			</div>';
    		}
      wp_reset_postdata();
      echo $after_widget.'</div></div>';
    }
 
    /** @see WP_Widget::update -- do not rename this */
    function update($new_instance, $old_instance) {   
	    $instance = $old_instance;
	    $instance['title'] = strip_tags($new_instance['title']);
	    $instance['number'] = $new_instance['number'];
	    $instance['finished'] = $new_instance['finished'];
	    $instance['width'] = $new_instance['width'];
	    return $instance;
    }
 
    /** @see WP_Widget::form -- do not rename this */
    function form($instance) {  
        $defaults = array( 
                        'title'  => __('Course Progress','wplms-dashboard'),
                        'number'  => 5,
                        'finished' => 1,
                        'width' => 'col-md-6 col-sm-12'
                    );
  		  $instance = wp_parse_args( (array) $instance, $defaults );
        $title  = esc_attr($instance['title']);
        $finished = esc_attr($instance['finished']);
        $number = esc_attr($instance['number']);
        $width = esc_attr($instance['width']);
        ?>
        <p>
          <label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title:','wplms-dashboard'); ?></label> 
          <input class="regular_text" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo $title; ?>" />
        </p>
        <p>
          <label for="<?php echo $this->get_field_id('number'); ?>"><?php _e('Number of activities in one screen','wplms-dashboard'); ?></label> 
          <input class="regular_text" id="<?php echo $this->get_field_id('number'); ?>" name="<?php echo $this->get_field_name('number'); ?>" type="text" value="<?php echo $number; ?>" />
        </p>
        <p>
          <label for="<?php echo $this->get_field_id('finished'); ?>"><?php _e('Show Finished Courses','wplms-dashboard'); ?></label> 
          <input class="checkbox" id="<?php echo $this->get_field_id( 'finished' ); ?>" name="<?php echo $this->get_field_name( 'finished' ); ?>" type="checkbox" value="1"  <?php checked($finished,1,true) ?>/>
        </p>
        <p>
          <label for="<?php echo $this->get_field_id('width'); ?>"><?php _e('Select Width','wplms-dashboard'); ?></label> 
          <select id="<?php echo $this->get_field_id('width'); ?>" name="<?php echo $this->get_field_name('width'); ?>">
          	<option value="col-md-3 col-sm-6" <?php selected('col-md-3 col-sm-6',$width); ?>><?php _e('One Fourth','wplms-dashboard'); ?></option>
          	<option value="col-md-4 col-sm-6" <?php selected('col-md-4 col-sm-6',$width); ?>><?php _e('One Third','wplms-dashboard'); ?></option>
          	<option value="col-md-6 col-sm-12" <?php selected('col-md-6 col-sm-12',$width); ?>><?php _e('One Half','wplms-dashboard'); ?></option>
            <option value="col-md-8 col-sm-12" <?php selected('col-md-8 col-sm-12',$width); ?>><?php _e('Two Third','wplms-dashboard'); ?></option>
             <option value="col-md-8 col-sm-12" <?php selected('col-md-9 col-sm-12',$width); ?>><?php _e('Three Fourth','wplms-dashboard'); ?></option>
          	<option value="col-md-12" <?php selected('col-md-12',$width); ?>><?php _e('Full','wplms-dashboard'); ?></option>
          </select>
        </p>
        <?php 
    }

    function wplms_course_progress_record($unit_id,$course_progress,$course_id){
        $user_id = get_current_user_id();
        $progress='progress'.$course_id;
        $course_progress = round($course_progress*100);
        update_user_meta($user_id,$progress,$course_progress);
    }
    function wplms_student_course_reset($course_id,$user_id){
      $progress='progress'.$course_id;
      update_user_meta($user_id,$progress,0);
    }
    function wplms_student_course_remove($course_id,$user_id){
      $progress='progress'.$course_id;
      delete_user_meta($user_id,$progress);
    }

    function calculate_course_progress($course_id){
      $user_id = get_current_user_id();
      $progress='progress'.$user_id;
      $curriculum=bp_course_get_curriculum_units($course_id);
      $base = count($curriculum);
      foreach($curriculum as $key=>$unit){
        $check = get_user_meta($user_id,$unit,true);
        if(!isset($check) || !$check)
          break;
      }   
      if(!$base)$base=1;
      $course_progress = round((100*($key/$base)),0);
      update_user_meta($user_id,$progress,$course_progress);
      return $course_progress;
    }
} 

?>