<?php

/**
 * FILE: custom_widgets.php 
 * Created on Nov 2, 2012 at 12:17:08 PM 
 * Author: Mr.Vibe 
 * Credits: www.VibeThemes.com
 * Project: VibeCom
 */


add_action( 'widgets_init', 'vibe_widgets' );

function vibe_widgets() {
    register_widget('vibecarousel');
    register_widget('vibetabs');
    register_widget('vibeposts');
    register_widget('vibegallery');
    register_widget('vibetestimonials'); 
}



class vibetabs extends WP_Widget {
        
  function vibetabs() {
  $widget_ops = array( 'classname' => 'Vibe Tabs', 'description' => 'Displays Posts/Authors/Commentors in Tabs.' );
  $control_ops = array( 'width' => 250, 'height' => 350,'id_base' => 'vibetabs');
  $this->WP_Widget( 'vibetabs',  __('Vibe Tabs','vibe'), $widget_ops, $control_ops);
  }
        
        
function form($instance) {
    // outputs the options form on admin
                /* Set up some default widget settings. */
    $defaults = array( 
                                'theme'  => 'light',
                                'show_tab1'  => '1',
                                'title_tab1'  => __('Recent Posts','vibe'),
                                'style_tab1' => __('thumbnail','vibe'), //thumbnail, normal, carousel, Thumbnail grid
                                'type_tab1'  => 'post', //post,project,testimonials
                                'sort_tab1'  => 'recent',  //Recent , Recent Tags, Recent Category, 
                                'num_tab1' => '10', 
                                'show_tab2'  => '1',
                                'title_tab2'  => __('Comments','vibe'),
                                'type_tab2'  => 'post', //post,project,testimonials
                                'sort_tab2'  => 'recent',  //Recent , Recent Tags, Recent Category, 
                                'num_tab2' => '5', 
                                'show_tab3'  => '1',
                                'title_tab3'  => __('Courses','vibe'),
                                'style_tab3' => 'courses', //thumbnail, normal, carousel, Thumbnail grid
                                'type_tab3'  => 'post', //post,project,testimonials
                                'sort_tab3'  => 'recent',  //Recent , Recent Tags, Recent Category, 
                                'num_tab3' => '5'
                    );
    $instance = wp_parse_args( (array) $instance, $defaults );
                
                ?>
                <p>
                    <?php _e('Theme:','vibe'); ?> 
                        <select name="<?php echo $this->get_field_name( 'theme' ); ?>">
                            <option value="light" <?php if($instance['theme'] == 'light'){echo "selected";}; ?>><?php _e('Light','vibe'); ?></option>
                            <option value="dark" <?php if($instance['theme'] == 'dark'){echo "selected";}; ?>><?php _e('Dark','vibe'); ?></option>
                        </select>
                </p>    
                <?php
                for($k=1;$k<4;$k++)
                {  
                ?>
                <br />
                <p>
                    <?php _e('Show Tab','vibe'); echo $k; ?>: 
                        <select name="<?php echo $this->get_field_name( 'show_tab'.$k ); ?>">
                            <option value="1" <?php if($instance['show_tab'.$k] == 1){echo "selected";}; ?>><?php _e('Yes','vibe'); ?></option>
                            <option value="0" <?php if($instance['show_tab'.$k] == 0){echo "selected";}; ?>><?php _e('No','vibe'); ?></option>
                        </select>
                </p>
                <p> <?php _e('Title','vibe'); ?> : <input class="text" type="text" name="<?php echo $this->get_field_name('title_tab'.$k); ?>" value="<?php echo $instance['title_tab'.$k]; ?>" /></p>
                <p><?php _e('Content & Style','vibe');?> : <select class="select" name="<?php echo $this->get_field_name('style_tab'.$k); ?>">
                        <option value="thumbnail" <?php if($instance['style_tab'.$k] == 'thumbnail') { ?>selected="selected" <?php } ?>><?php _e('Thumbnail Posts','vibe'); ?></option>
                        <option value="courses" <?php if($instance['style_tab'.$k] == 'courses') { ?>selected="selected" <?php } ?>><?php _e('Courses','vibe'); ?></option>
                        <option value="comments" <?php if($instance['style_tab'.$k] == 'comments') { ?>selected="selected" <?php } ?>><?php _e('Comments','vibe'); ?></option>
                        </select>
                </p>
                <p><?php _e('Number of items','vibe');?> : <input class="text" type="text" name="<?php echo $this->get_field_name('num_tab'.$k); ?>" value="<?php echo $instance['num_tab'.$k]; ?>" /></p>  
                    <?php
                }//End Tabs
  }
        
        
function update($new_instance, $old_instance) {
    $instance = array();
                $instance = $old_instance;
    $instance['theme']=$new_instance['theme'];
               /* Strip tags (if needed) and update the widget settings. */ 
               for($k=1;$k<4;$k++){ 
                        $instance['show_tab'.$k]=$new_instance['show_tab'.$k];
                        if(isset($new_instance['title_tab'.$k]))
                        $instance['title_tab'.$k]= $new_instance['title_tab'.$k];
                        if(isset( $new_instance['style_tab'.$k] ))
                        $instance['style_tab'.$k]=$new_instance['style_tab'.$k];
                        if(isset( $new_instance['num_tab'.$k] ))
                        $instance['num_tab'.$k]=$new_instance['num_tab'.$k];
               }
                return $instance;
  }
        
        
function widget( $args, $instance ) 
{   
    extract( $args ); global $vibe_options;
    echo $before_widget;
    ?>
                
      <div class="tabs tabbable">
    <ul class="nav nav-tabs <?php echo $instance['theme'];?>" id="widget-tabs">
            <?php
            for($i=1;$i<4;$i++){
                if($instance['show_tab'.$i]) {
                if($i==1) echo '<li class="active">';
                else echo '<li>';
                echo '<a href="#tab'.$i.'">'.$instance['title_tab'.$i].'</a></li>';
                }
            }
            ?>
    </ul>
    <div class="tab-content <?php echo $instance['theme'];?>">
            <?php
            for($i=1;$i<4;$i++){
                if($instance['show_tab'.$i]) {
                if($i==1) echo '<div class="tab-pane active" id="tab'.$i.'">';
                else echo '<div class="tab-pane" id="tab'.$i.'">';
                    

                    switch($instance['style_tab'.$i]){
                      case 'thumbnail':{
                              echo '<ul class="more_posts">';

                              $query = 'posts_per_page='.$instance['num_tab'.$i];
                              $loop = new WP_Query($query);
                              while ( $loop->have_posts() ) : $loop->the_post();
                              
                                   $thumb=get_the_post_thumbnail($loop->post->ID,'mini');
                                   
                                  echo '<li><a href="'.get_permalink($loop->post->ID).'" title="'.get_the_title($loop->post->ID).'">'.$thumb.'<span>'.get_the_title().'</span></a><small>';
                                  the_category(' ','',$loop->post->ID); 
                                  
                                echo '</small></li>';
                                    endwhile;
                                   echo '</ul>';
                                // Reset Post Data
                                   wp_reset_query();
                                wp_reset_postdata();
                        break;
                      }
                      case 'courses':{
                              echo '<ul class="more_posts">';

                              $query = 'post_type=course&posts_per_page='.$instance['num_tab'.$i];
                              $loop = new WP_Query($query);
                              while ( $loop->have_posts() ) : $loop->the_post();
                              
                                   $thumb=get_the_post_thumbnail($loop->post->ID,'mini');
                                   
                                  echo '<li><a href="'.get_permalink($loop->post->ID).'" title="'.get_the_title($loop->post->ID).'">'.$thumb.'<span>'.get_the_title().'</span></a><small>';
                                  echo get_the_term_list( $loop->post->ID, 'course-cat', '', ',', ' ' );
                                  
                                echo '</small></li>';
                                    endwhile;
                                   echo '</ul>';
                                // Reset Post Data
                                   wp_reset_query();
                                wp_reset_postdata();
                        break;
                      }
                      case 'comments':{
                        $args=array(
                          'status' => 'approve',
                          'number' => $instance['num_tab'.$i],
                          );
                        $comments = get_comments( $args );
                         echo '<ul class="more_posts">';
                        foreach($comments as $comment){
                          echo '<li><a>'.get_avatar($comment->comment_author_email,120).'</a> <span>'.$comment->comment_author.'</span><small><a href="'.get_permalink($comment->comment_post_ID).'">'.get_the_title($comment->comment_post_ID).'</a></small>';
                        }
                        echo '</ul>';
                        break;
                      }

                    }

                      echo '</div>';
                }
            }
            ?>
      </div>
    </div> <!-- END TABS -->      
                
    <?php
    echo $after_widget;
    }
}



//* ==== VIBE Carousel ==== */

class vibecarousel extends WP_Widget {

  function vibecarousel() {
    $widget_ops = array( 'classname' => 'Vibe Carousel', 'description' => __('Posts Carousel ', 'vibe') );
    $control_ops = array( 'width' => 300, 'height' => 350, 'id_base' => 'vibecarousel' );
    $this->WP_Widget( 'vibecarousel', __('Vibe Carousel', 'vibe'), $widget_ops, $control_ops );
  }
  
  function widget( $args, $instance ) {
    extract( $args );
                global $vibe_options;
    //Our variables from the widget settings.
    $title = apply_filters('widget_title', $instance['title'] );
    
    echo $before_widget;

    // Display the widget title 
    if ( $title )
      echo $before_title . $title . $after_title;

    //Display the name 
    if ( $instance['type'] )
                    if($instance['type'] == 'post'){
                                    if($instance['sort'] == 'recent') {
                                            $query = 'posts_per_page='.$instance['num'];
                                            $loop = new WP_Query($query);
                                    }else{
                                        $query='orderby=comment_count&posts_per_page='.$instance['num'];
                                        $loop = new WP_Query($query);
                                        }
                       }else{
                        
                                            $query = 'post_type='.$instance['type'].'&posts_per_page='.$instance['num'];
                                            $loop = new WP_Query($query);
                            }
                            
                            
    ?>
                    <div class="widget_carousel flexslider <?php echo $instance['theme'].' '.(($instance['auto'])?'auto':'').' '.(($instance['loop'])?'loop':'');?> loading">
              <ul class="slides">
                                              <?php
                                              if($instance['style'] == 'post'){
                                              while ( $loop->have_posts() ) : $loop->the_post(); 
                                              $thumb=  featured_component($loop->post->ID,'big');
                                              
                                              $price='';
                                              if(isset($vibe_options['listing_fields']['field_type'])){
                                                  $i = array_search('price',$vibe_options['listing_fields']['field_type']);
                                                  if(isset($i)){
                                                      $key = 'vibe_'.strtolower(str_replace(' ', '-',$vibe_options['listing_fields']['label'][$i]));
                                                      $price = getPostMeta($loop->post->ID,$key);
                                                  }
                                              }
                                             
                                              echo '
                <li>
                  <article>
                    <div class="post_thumb">';
                                              echo '<a href="'.get_permalink($loop->post->ID).'">'.  $thumb.'</a>
                    </div>
                    <h4 class="post_title"><a href="'.get_permalink($loop->post->ID).'">'.get_the_title($loop->post->ID).'</a></h4>                                                
                    <p class="post_excerpt">'.custom_excerpt($instance['excerpt_length']).'</p>
                    </article>
                </li>';
                                            
                                          endwhile;
                                          // Restore original Query & Post Data
                                            wp_reset_query();
                                            wp_reset_postdata();


                                              }
                                                if($instance['style'] == 'imagetitle'){
                                              while ( $loop->have_posts() ) : $loop->the_post(); 
                                              $thumb=get_the_post_thumbnail($loop->post->ID);
                                              
                                              $price='';
                                              if(isset($vibe_options['listing_fields']['field_type'])){
                                                  $i = array_search('price',$vibe_options['listing_fields']['field_type']);
                                                  if(isset($i)){
                                                      $key = 'vibe_'.strtolower(str_replace(' ', '-',$vibe_options['listing_fields']['label'][$i]));
                                                      $price = getPostMeta($loop->post->ID,$key);
                                                  }
                                              }
                                              $featured='';
                                              if(isset($vibe_options['listing_fields']['field_type'])){
                                                  $i = array_search('featured',$vibe_options['listing_fields']['field_type']);
                                                  if(isset($i)){
                                                      $key = 'vibe_'.strtolower(str_replace(' ', '-',$vibe_options['listing_fields']['label'][$i]));
                                                      $featured = getPostMeta($loop->post->ID,$key);
                                                  }
                                              }
                                              
                                                if(!isset($thumb) || $thumb == '')
                                                $thumb='<img src="'.VIBE_URL.'/img/default_mini.jpg" />';
                                                 $onsale ='';
                                                $terms = wp_get_post_terms( $loop->post->ID, 'status');
                                                if(isset($terms) && is_array($terms)){
                                                    foreach($terms as $term){
                                                    $onsale = '<span class="'.$term->slug.'">'.$term->name.'</span>';
                                                    }
                                                  }
                                              echo '
                <li>
                  <article>
                    <div class="post_thumb">'.$onsale;
                                              echo '<a href="'.get_permalink($loop->post->ID).'">'.  $thumb.'</a>';
                                              if(vibe_validate($featured))
                                              echo '<span class="vfeatured" data-rel="tooltip" data-original-title="'.$vibe_options['listing_fields']['label'][$i].'"><i class="icon-star"></i></span>';
                                              echo '    
                    </div>
                                                <div class="carouselposttitle">
                    <h5><a href="'.get_permalink($loop->post->ID).'">'.get_the_title($loop->post->ID).'</a></h5>
                                                '.(isset($price)?'<span class="price"><i class="'.$vibe_options['currency'].'"></i>'.$price.'</span>'.get_the_term_list( $loop->post->ID, 'location', ' ', ' ', '' ):'').'
                                                </div>    
                    </article>
                </li>';
                                            
                                          endwhile;
                                          // Restore original Query & Post Data
                                            wp_reset_query();
                                            wp_reset_postdata();
                                              }
                                              if($instance['style'] == 'image'){
                                              while ( $loop->have_posts() ) : $loop->the_post(); 
                                              $thumb=get_the_post_thumbnail($loop->post->ID);
                                                if(!isset($thumb) || $thumb == '')
                                                $thumb='<img src="'.VIBE_URL.'/img/default_mini.jpg" />';
                                              echo '
                <li>
                    <div class="post_thumb">';
                                              echo '<a href="'.get_permalink($loop->post->ID).'">'.  $thumb .'</a>
                    </div>
                </li>';
                                            
                                          endwhile;
                                          // Restore original Query & Post Data
                                            wp_reset_query();
                                            wp_reset_postdata();
                                              }
                                                if($instance['style'] == 'title'){
                                              while ( $loop->have_posts() ) : $loop->the_post(); 
                                              echo '
                <li>
                    <h5><a href="'.get_permalink($loop->post->ID).'">'.get_the_title($loop->post->ID).'</a></h5>
        
                </li>';
                                            
                                          endwhile;
                                          // Restore original Query & Post Data
                                            wp_reset_query();
                                            wp_reset_postdata();
                                              }
                                              ?>
              </ul> 
          </div>  
                                <?php
    echo $after_widget;
                }

  //Update the widget 
   
  function update( $new_instance, $old_instance ) {
    $instance = $old_instance;

    $instance['title'] = strip_tags( $new_instance['title'] );
    $instance['theme'] = strip_tags( $new_instance['theme'] );
                $instance['type'] = strip_tags( $new_instance['type'] );
                $instance['sort'] = strip_tags( $new_instance['sort'] );
                $instance['style'] = strip_tags( $new_instance['style'] );
                $instance['excerpt_length'] = strip_tags( $new_instance['excerpt_length'] );
                $instance['auto'] = strip_tags( $new_instance['auto'] );
                $instance['loop'] = strip_tags( $new_instance['loop'] );
                $instance['num'] = strip_tags( $new_instance['num'] );

    return $instance;
  }

  
  function form( $instance ) {

    //Set up some default widget settings.
    $defaults = array( 
                        'theme'  => 'light',
                        'title'  => 'Recent Posts',
                        'type'  => 'post',
                        'style'  => 'post',
                        'excerpt_length'  => '100',
                        'auto'  => '1',
                        'loop'  => '1',
                        'num'  => '3',
                        );
    $instance = wp_parse_args( (array) $instance, $defaults ); 
                
                ?>
        <p> <?php _e('Title','vibe'); ?> <input type="text" class="text" name="<?php echo $this->get_field_name('title'); ?>" value="<?php echo $instance['title']; ?>" /></p>
  <p><?php _e('Theme','vibe');?> : <select class="select" name="<?php echo $this->get_field_name('theme'); ?>">
                           <option value="light" <?php if($instance['theme'] == 'light') { ?>selected="selected" <?php } ?>><?php _e('Light','vibe'); ?></option>
                           <option value="dark" <?php if($instance['theme'] == 'dark') { ?>selected="selected" <?php } ?>><?php _e('Dark','vibe'); ?></option>
                    </select>        
        </p>  
            <p> <?php _e('Post Type','vibe');?> : <select class="select" name="<?php echo $this->get_field_name('type'); ?>">
                        <option value="post" <?php if($instance['type'] == 'post') { ?>selected="selected" <?php } ?>>Post</option>
                        <?php
                        $args=array(
                                    'public'   => true,
                                    '_builtin' => false
                                    );
                        $post_types=get_post_types($args,'names'); 
                        foreach ($post_types as $post_type ) {?>
                        <option value="<?php echo $post_type; ?>" <?php if($instance['type'] == $post_type) { ?>selected="selected" <?php } ?>><?php echo $post_type; ?></option>;
                        <?php }
                        ?>
                </select>
                </p>
                <p>
                    <?php _e('Sort','vibe'); ?> : <select class="select" name="<?php echo $this->get_field_name('sort'); ?>">
                        <option value="recent" <?php if($instance['sort'] == 'recent') { ?>selected="selected" <?php } ?>>Recent</option>
                        <option value="popular" <?php if($instance['sort'] == 'popular') { ?>selected="selected" <?php } ?>> Popular</option>
                        </select>
                </p>
                <p>
                    <?php _e('Style','vibe'); ?> : <select class="select" name="<?php echo $this->get_field_name('style'); ?>">
                        <option value="post" <?php if($instance['style'] == 'post') { ?>selected="selected" <?php } ?>><?php _e('Post','vibe'); ?></option>
                        <option value="imagetitle" <?php if($instance['style'] == 'imagetitle') { ?>selected="selected" <?php } ?>> <?php _e('Image with Title','vibe'); ?></option>
                        <option value="image" <?php if($instance['style'] == 'image') { ?>selected="selected" <?php } ?>><?php _e('Image Only','vibe'); ?></option>
                        <option value="title" <?php if($instance['style'] == 'title') { ?>selected="selected" <?php } ?>><?php _e('Title Only','vibe'); ?></option>
                        </select>
                </p>
                <p>
                    <?php _e('Excerpt length','vibe'); ?> : <input type="text" class="text" name="<?php echo $this->get_field_name('excerpt_length'); ?>" value="<?php echo $instance['excerpt_length']; ?>" /></p>
                </p>
                <p>
                    <?php _e('Auto Start','vibe'); ?> : <select class="select" name="<?php echo $this->get_field_name('auto'); ?>">
                        <option value="1" <?php if($instance['auto'] == '1') { ?>selected="selected" <?php } ?>>Yes</option>
                        <option value="0" <?php if($instance['auto'] == '0') { ?>selected="selected" <?php } ?>> No</option>
                        </select>
                </p>
                <p>
                    <?php _e('Loop back, after completion','vibe'); ?> : <select class="select" name="<?php echo $this->get_field_name('loop'); ?>">
                        <option value="1" <?php if($instance['loop'] == '1') { ?>selected="selected" <?php } ?>>Yes</option>
                        <option value="0" <?php if($instance['loop'] == '0') { ?>selected="selected" <?php } ?>> No</option>
                        </select>
                </p>
                <p> <?php _e('Number of Posts','vibe'); ?> : <input type="text" class="text" name="<?php echo $this->get_field_name('num'); ?>" value="<?php echo $instance['num']; ?>" /></p>

    

  <?php
  }
}

/*======== Vibe Posts =========*/

class vibeposts extends WP_Widget {

  function vibeposts() {
    $widget_ops = array( 'classname' => 'Vibe Posts', 'description' => __('Posts Widget ', 'vibe') );
    $control_ops = array( 'width' => 300, 'height' => 350, 'id_base' => 'vibeposts' );
    $this->WP_Widget( 'vibeposts', __('Vibe Posts', 'vibe'), $widget_ops, $control_ops );
  }
  
  function widget( $args, $instance ) {
    extract( $args );
                global $vibe_options;
    //Our variables from the widget settings.
    $title = apply_filters('widget_title', $instance['title'] );
    
    echo $before_widget;

    // Display the widget title 
    if ( $title )
      echo $before_title . $title . $after_title;
                
                    
                     if(isset($instance['taxonomy']) && $instance['taxonomy']!=''){ 
                     
                         $check=term_exists($instance['term'], $instance['taxonomy']);
                         
                    if ($check == 0 || $check == null || !$check) {
                            $error = new VibeErrors();
                            echo $error->get_error('term_taxonomy_mismatch');
                        } 
                         
                        $check=is_object_in_taxonomy($instance['type'], $instance['taxonomy']);
                    if ($check == 0 || $check == null || !$check) {
                            $error = new VibeErrors();
                            echo $error->get_error('term_postype_mismatch');
                        }
                     }
                             
                     $query=array( 'posts_per_page' => $instance['num']);
                     
                    // if($instance['type'] != 'post') {
                     $query['post_type'] = $instance['type'];
                      //}  
                        
                     if(!empty($instance['taxonomy'])){
                         if($instance['taxonomy'] == 'category'){
                             $instance['taxonomy']='category_name'; 
                             }
                          if($instance['taxonomy'] == 'tag'){
                             $instance['taxonomy']='tag_name'; 
                             }   
                         $query[$instance['taxonomy']] = $instance['term'];
                     }
                     if($instance['sort'] == 'popular') {
                     $query['orderby'] = 'comment_count';
                      }  
                     
                     $loop = new WP_Query($query);
                
    ?>
                    <div class="postlist <?php echo $instance['theme'].' post'.$instance['size'];?>">
         <ul class="vibeposts">
                             
                <?php
                                              
                      if($instance['style'] == 'post'){
                        while ( $loop->have_posts() ) : $loop->the_post(); 
                        
                          $thumb=  get_the_post_thumbnail($loop->post->ID,$instance['size']);
                          
                        
                        if(isset($instance['chars']) && $instance['chars'] !='')
                            $chars=intval($instance['chars']);
                        else
                            $chars=80;
                        echo '
                              <li>
                                <article>
                                  <div class="post_thumb">';
                                                            echo '<a href="'.get_permalink($loop->post->ID).'" class="'.$instance['size'].'_thumb">'.  $thumb.'</a>
                                  </div>
                                  <h4 class="post_title"><a href="'.get_permalink($loop->post->ID).'">'.get_the_title($loop->post->ID).'</a></h4>
                                  <p class="post_excerpt">'.custom_excerpt($chars).'</p>
                                  </article>
                              </li>';
                      
                          endwhile;
                      }
                      if($instance['style'] == 'imagetitle'){
                          while ( $loop->have_posts() ) : $loop->the_post(); 
                          
                          
                          $thumb=get_the_post_thumbnail($loop->post->ID,$instance['size']);
                          
                          echo '<li>
                                  <article>
                                    <div class="post_thumb">';
                                                              echo '<a href="'.get_permalink($loop->post->ID).'">'.  $thumb.'</a>
                                    </div>
                                    <h5><a href="'.get_permalink($loop->post->ID).'">'.get_the_title($loop->post->ID).'</a></h5>
                                    </article>
                                </li>';
                        
                        endwhile;
                     }
                    if($instance['style'] == 'image'){
                      while ( $loop->have_posts() ) : $loop->the_post(); 
                       
                      $thumb=get_the_post_thumbnail($loop->post->ID,$instance['size']);
                      
                      echo '<li>
                                <div class="post_thumb imageonly">';
                                                          echo '<a href="'.get_permalink($loop->post->ID).'">'.  $thumb .'</a>
                                </div>
                            </li>';
                    
                      endwhile;
                    }
                    if($instance['style'] == 'title'){
                      while ( $loop->have_posts() ) : $loop->the_post(); 
                      echo '<li>
                                  <h5><a href="'.get_permalink($loop->post->ID).'">'.get_the_title($loop->post->ID).'</a></h5>
                              </li>';
                    
                      endwhile;
                    }
                  ?>
              </ul> 
          </div>  
<?php
// Restore original Query & Post Data
          wp_reset_query();
          wp_reset_postdata();
    echo $after_widget;
                }

  //Update the widget 
   
  function update( $new_instance, $old_instance ) {
    $instance = $old_instance;

    $instance['title'] = strip_tags( $new_instance['title'] );
    $instance['theme'] = strip_tags( $new_instance['theme'] );
                $instance['type'] = strip_tags( $new_instance['type'] );
                $instance['taxonomy'] = strip_tags( $new_instance['taxonomy'] );
                $instance['term'] = strip_tags( $new_instance['term'] );
                $instance['sort'] = strip_tags( $new_instance['sort'] );
                $instance['size'] = strip_tags( $new_instance['size'] );
                $instance['style'] = strip_tags( $new_instance['style'] );
                $instance['num'] = strip_tags( $new_instance['num'] );
                 $instance['chars'] = strip_tags( $new_instance['chars'] );
    return $instance;
  }

  
  function form( $instance ) {
           $v_all_taxonomies = array();
           $v_taxonomy_terms = array();
            $taxonomies=get_taxonomies('','names'); 
                    foreach ($taxonomies as $taxonomy ) {
                    $v_all_taxonomies[$taxonomy]=$taxonomy;
                    }
                                
                $v_taxonomy_terms=get_all_taxonomy_terms(); 
    //Set up some default widget settings.
    $defaults = array( 
                        'theme'  => 'light',
                        'title'  => 'Recent Posts',
                        'type'  => 'post',
                        'taxonomy'  => 'categpry',
                        'term'  => '',
                        'style'  => 'post',
                        'sort'  => 'recent',
                        'size'  => 'big',
                        'num'  => '3',
                        'chars'  => '80',
                        );
    $instance = wp_parse_args( (array) $instance, $defaults ); 
                
                ?>
        <p><?php _e(' Title','vibe'); ?> <input type="text" class="text" name="<?php echo $this->get_field_name('title'); ?>" value="Recent Posts" /></p>
  <p><?php _e('Theme','vibe');?> : <select class="select" name="<?php echo $this->get_field_name('theme'); ?>">
                           <option value="light" <?php if($instance['theme'] == 'light') { ?>selected="selected" <?php } ?>><?php _e('Light','vibe'); ?></option>
                           <option value="dark" <?php if($instance['theme'] == 'dark') { ?>selected="selected" <?php } ?>><?php _e('Dark','vibe'); ?></option>
                    </select>        
        </p>  
           <p> <?php _e('Post Type','vibe'); ?> : <select class="select" name="<?php echo $this->get_field_name('type'); ?>">
                        <option value="post" <?php if($instance['type'] == 'post') { ?>selected="selected" <?php } ?>><?php _e('Post','vibe'); ?></option>
                        <?php
                        $args=array(
                                    'public'   => true,
                                    '_builtin' => false
                                    );
                        $post_types=get_post_types($args,'names'); 
                        foreach ($post_types as $post_type ) {?>
                        <option value="<?php echo $post_type; ?>" <?php if($instance['type'] == $post_type) { ?>selected="selected" <?php } ?>><?php echo $post_type; ?></option>;
                        <?php }
                        ?>
                </select>
                </p>
                
                <p> Taxonomy : <select class="select" name="<?php echo $this->get_field_name('taxonomy'); ?>">
                        <option value="" <?php if(!isset($instance['taxonomy']) || $instance['taxonomy'] == '') echo 'selected="selected"'; ?>><?php _e('All','vibe'); ?></option>;
                         <?php
                        foreach ($v_all_taxonomies as $taxonomy ) {?>
                        <option value="<?php echo $taxonomy; ?>" <?php if($instance['taxonomy'] == $taxonomy) { ?>selected="selected" <?php } ?>><?php echo $taxonomy; ?></option>;
                        <?php }
                        ?>
                </select>
                </p>
                 <p> Taxonomy Term : <select class="select" name="<?php echo $this->get_field_name('term'); ?>">
                         <?php
                        
                        foreach ($v_taxonomy_terms as $term  => $name) {?>
                        <option value="<?php echo $term; ?>" <?php if($instance['term'] == $term) { ?>selected="selected" <?php } ?>><?php echo $name; ?></option>;
                        <?php }
                        ?>
                </select>
                </p>
                <p>
                    <?php _e('Sort','vibe'); ?> : <select class="select" name="<?php echo $this->get_field_name('sort'); ?>">
                        <option value="recent" <?php if($instance['sort'] == 'recent') { ?>selected="selected" <?php } ?>><?php _e('Recent','vibe'); ?></option>
                        <option value="popular" <?php if($instance['sort'] == 'popular') { ?>selected="selected" <?php } ?>> <?php _e('Popular','vibe'); ?></option>
                        </select>
                </p>
                <p>
                  <?php _e(' Image Size','vibe'); ?> : <select class="select" name="<?php echo $this->get_field_name('size'); ?>">
                        <option value="big" <?php if($instance['size'] == 'big') { ?>selected="selected" <?php } ?>><?php _e('Big','vibe'); ?></option>
                        <option value="small" <?php if($instance['size'] == 'small') { ?>selected="selected" <?php } ?>> <?php _e('Small','vibe'); ?></option>
                        </select>
                </p>
                <p>
                    <?php _e('Style','vibe'); ?> : <select class="select" name="<?php echo $this->get_field_name('style'); ?>">
                        <option value="post" <?php if($instance['style'] == 'post') { ?>selected="selected" <?php } ?>><?php _e('Post','vibe'); ?></option>
                        <option value="imagetitle" <?php if($instance['style'] == 'imagetitle') { ?>selected="selected" <?php } ?>> <?php _e('Image with Title','vibe'); ?></option>
                        <option value="image" <?php if($instance['style'] == 'image') { ?>selected="selected" <?php } ?>> <?php _e('Image Only','vibe'); ?></option>
                        </select>
                </p>
                <p><?php _e('Number of Posts','vibe'); ?> : <input type="text" class="text" name="<?php echo $this->get_field_name('num'); ?>" value="<?php echo $instance['num']; ?>" /></p>
                <p><?php _e('Number of Characters in Excerpt [Post Style]','vibe'); ?>: <input type="text" class="text" name="<?php echo $this->get_field_name('chars'); ?>" value="<?php echo $instance['chars']; ?>" size="2" /></p>

    

  <?php
  }
}

    
/*======= Vibe Gallery ======== */    

 class vibegallery extends WP_Widget {
 
 
    /** constructor -- name this the same as the class above */
    function vibegallery() {
    $widget_ops = array( 'classname' => 'Vibe Google Map', 'description' => __('Vibe Gallery', 'vibe') );
    $control_ops = array( 'width' => 300, 'height' => 350, 'id_base' => 'vibegallery' );
    $this->WP_Widget( 'vibegallery', __('Vibe Gallery', 'vibe'), $widget_ops, $control_ops );
  }
        
 
    /** @see WP_Widget::widget -- do not rename this */
    function widget( $args, $instance ) {
    extract( $args );

    //Our variables from the widget settings.
    $title = apply_filters('widget_title', $instance['title'] );
                
    
    echo $before_widget;

    // Display the widget title 
    if ( $title )
      echo $before_title . $title . $after_title;
                
               
               echo do_shortcode('[gallery  size="'.$instance['size'].'" ids="'.$instance['ids'].'"]');
                
                
                echo $after_widget;
                
    }
 
    /** @see WP_Widget::update -- do not rename this */
    function update($new_instance, $old_instance) {   
    $instance = $old_instance;
    $instance['title'] = strip_tags($new_instance['title']);
    $instance['size'] = $new_instance['size'];
                $instance['ids'] = $new_instance['ids'];
        return $instance;
    }
 
    /** @see WP_Widget::form -- do not rename this */
    function form($instance) {  
        $defaults = array( 
                                'title'  => 'Featured Gallery',
                                'size'  => 'normal',
                                'ids'  => ''
                    );
  $instance = wp_parse_args( (array) $instance, $defaults );
                
        $title  = esc_attr($instance['title']);
        $size = esc_attr($instance['size']);
         $ids = esc_attr($instance['ids']);                            
        ?>
         <p>
          <label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title:','vibe'); ?></label> 
          <input class="regular_text" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo $title; ?>" />
        </p>
        <p><label><?php _e('Size ','vibe'); ?></label> <select class="select" name="<?php echo $this->get_field_name('size'); ?>">
                       <option value="medium" <?php if($instance['size'] == 'normal') { echo 'selected="selected"'; } ?>> <?php _e('Medium','vibe'); ?> </option>
                       <option value="small" <?php if($instance['size'] == 'small') { echo 'selected="selected"'; } ?>><?php _e(' Small','vibe'); ?> </option>
                       <option value="micro" <?php if($instance['size'] == 'micro') { echo 'selected="selected"'; } ?>> <?php _e('Mini','vibe'); ?> </option>
                       <option value="big" <?php if($instance['size'] == 'large') { echo 'selected="selected"'; } ?>> <?php _e('Big','vibe'); ?> </option>
                      
        </select></p>
  <p>
          <label for="<?php echo $this->get_field_id('ids'); ?>"><?php _e('Enter Attachment Ids ','vibe'); ?></label> 
          <textarea class="textarea" id="<?php echo $this->get_field_id('ids'); ?>" name="<?php echo $this->get_field_name('ids'); ?>"><?php echo $ids; ?></textarea> 
        </p>
        <?php 
    }
}   
    
          

/*======= Vibe Testimonials ======== */  

class vibetestimonials extends WP_Widget {
 
 
    /** constructor -- name this the same as the class above */
    function vibetestimonials() {
    $widget_ops = array( 'classname' => 'Vibe Testimonials', 'description' => __('Vibe Testimonials ', 'vibe') );
    $control_ops = array( 'width' => 300, 'height' => 350, 'id_base' => 'vibetestimonials' );
    $this->WP_Widget( 'vibetestimonials', __('Vibe Testimonials', 'vibe'), $widget_ops, $control_ops );
  }
        
 
    /** @see WP_Widget::widget -- do not rename this */
    function widget( $args, $instance ) {
    extract( $args );

    //Our variables from the widget settings.
    $title = apply_filters('widget_title', $instance['title'] );
                
    
    echo $before_widget;

    // Display the widget title 
    if ( $title )
      echo $before_title . $title . $after_title;
                
               
               echo do_shortcode('[testimonial id="'.$instance['id'].'" length="'.$instance['length'].'"]');
                
                
                echo $after_widget;
                
    }
 
    /** @see WP_Widget::update -- do not rename this */
    function update($new_instance, $old_instance) {   
    $instance = $old_instance;
    $instance['title'] = strip_tags($new_instance['title']);
    $instance['id'] = $new_instance['id'];
                $instance['length'] = $new_instance['length'];
        return $instance;
    }
 
    /** @see WP_Widget::form -- do not rename this */
    function form($instance) {  
        $defaults = array( 
                                'title'  => 'Featured Testimonial',
                                'ids'  => '',
                                'length'  => '',
                                'style'  => ''
                    );
  $instance = wp_parse_args( (array) $instance, $defaults );
                
        $title  = esc_attr($instance['title']);
        $id = esc_attr($instance['ids']);
        
        $query = 'post_type=testimonials&post_per_page=-1';
        $loop = new WP_Query($query);
                                            
        ?>
         
         <p>
          <label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title:','vibe'); ?></label> 
          <input class="regular_text" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo $title; ?>" />
        </p>
  <p>
          <label for="<?php echo $this->get_field_id('id'); ?>"><?php _e('Testimonial Id ','vibe'); ?></label> 
           <select class="select" name="<?php echo $this->get_field_name('id'); ?>">
                         <?php
                          echo '<option value="random" '.(($instance['id'] == 'random')?'selected=selected':'' ).' > '.__('Random','vibe').' </option>';
                        while ( $loop->have_posts() ) :
                            $loop->the_post();
                          echo '<option value="'.get_the_ID().'" '.(($instance['id'] == get_the_ID())?'selected=selected':'' ).' > '.get_the_title().' </option>';
                        endwhile;
                        ?>
                </select>
        </p>
        <p>
          <label for="<?php echo $this->get_field_id('length'); ?>"><?php _e('Length of excerpt:','vibe'); ?></label> 
          <input class="regular_text" id="<?php echo $this->get_field_id('length'); ?>" name="<?php echo $this->get_field_name('length'); ?>" type="text" value="<?php echo $instance['length']; ?>" />
        </p>
        
        <?php 
        wp_reset_query();
        wp_reset_postdata();
    }
}

?>