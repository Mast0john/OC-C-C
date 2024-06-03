<?php
/********************************************************************************
 * Activity & Notification Functions
 *
 * These functions handle the recording, deleting and formatting of activity and
 * notifications for the user and for this specific component.
 */


/**
 * bp_course_screen_notification_settings()
 *
 * Adds notification settings for the component, so that a user can turn off email
 * notifications set on specific component actions.
 */


function bp_course_screen_notification_settings() {
	global $current_user;
	?>
	<hr />
	<table class="notification-settings" id="bp-course-notification-settings">
		<thead>
		<tr>
			<th class="icon"></th>
			<th class="title"><?php _e( 'Course', 'vibe' ) ?></th>
			<th class="yes"><?php _e( 'Yes', 'vibe' ) ?></th>
			<th class="no"><?php _e( 'No', 'vibe' )?></th>
		</tr>
		</thead>
		<tbody>
		<tr>
			<td></td>
			<td><?php _e( 'Instructor Announcements', 'vibe' ) ?></td>
			<td class="yes"><input type="radio" name="notifications[wplms_instructor_annoucement]" value="yes" <?php if ( !get_user_meta( $current_user->id, 'wplms_instructor_annoucement', true ) || 'yes' == get_user_meta( $current_user->id, 'wplms_instructor_annoucement', true ) ) { ?>checked="checked" <?php } ?>/></td>
			<td class="no"><input type="radio" name="notifications[wplms_instructor_annoucement]" value="no" <?php if ( 'no' == get_user_meta( $current_user->id, 'wplms_instructor_annoucement', true ) ) { ?>checked="checked" <?php } ?>/></td>
		</tr>
		<tr>
			<td></td>
			<td><?php _e( 'Quizes evaluation', 'vibe' ) ?></td>
			<td class="yes"><input type="radio" name="notifications[wplms_course_quiz_evaluation]" value="yes" <?php if ( !get_user_meta( $current_user->id, 'wplms_course_quiz_evaluation', true ) || 'yes' == get_user_meta( $current_user->id, 'wplms_course_quiz_evaluation', true ) ) { ?>checked="checked" <?php } ?>/></td>
			<td class="no"><input type="radio" name="notifications[wplms_course_quiz_evaluation]" value="no" <?php if ( 'no' == get_user_meta( $current_user->id, 'wplms_course_quiz_evaluation', true ) ) { ?>checked="checked" <?php } ?>/></td>
		</tr>
		<tr>
			<td></td>
			<td><?php _e( 'Assignment evaluation', 'vibe' ) ?></td>
			<td class="yes"><input type="radio" name="notifications[wplms_course_assignment_evaluation]" value="yes" <?php if ( !get_user_meta( $current_user->id, 'wplms_course_assignment_evaluation', true ) || 'yes' == get_user_meta( $current_user->id, 'wplms_course_assignment_evaluation', true ) ) { ?>checked="checked" <?php } ?>/></td>
			<td class="no"><input type="radio" name="notifications[wplms_course_assignment_evaluation]" value="no" <?php if ( 'no' == get_user_meta( $current_user->id, 'wplms_course_assignment_evaluation', true ) ) { ?>checked="checked" <?php } ?>/></td>
		</tr>
		<tr>
			<td></td>
			<td><?php _e( 'Course evaluation', 'vibe' ) ?></td>
			<td class="yes"><input type="radio" name="notifications[wplms_course_evaluation]" value="yes" <?php if ( !get_user_meta( $current_user->id, 'notification_course_evaluation', true ) || 'yes' == get_user_meta( $current_user->id, 'wplms_course_evaluation', true ) ) { ?>checked="checked" <?php } ?>/></td>
			<td class="no"><input type="radio" name="notifications[wplms_course_evaluation]" value="no" <?php if ( 'no' == get_user_meta( $current_user->id, 'notification_course_evaluation', true ) ) { ?>checked="checked" <?php } ?>/></td>
		</tr>
		<tr><td></td>
			<td><?php _e( 'Course expire warning notifications', 'vibe' ) ?></td>
			<td class="yes"><input type="radio" name="notifications[wplms_course_expire]" value="yes" <?php if ( !get_user_meta( $current_user->id, 'wplms_course_expire', true ) || 'yes' == get_user_meta( $current_user->id, 'wplms_course_expire', true ) ) { ?>checked="checked" <?php } ?>/></td>
			<td class="no"><input type="radio" name="notifications[wplms_course_expire]" value="no" <?php if ( get_user_meta( $current_user->id, 'wplms_course_expire') == 'no' ) { ?>checked="checked" <?php } ?>/></td>
		</tr>
		<?php do_action( 'bp_course_notification_settings' ); ?>
		</tbody>
	</table>
<?php
}
//add_action( 'bp_notification_settings', 'bp_course_screen_notification_settings' );


/**
 * bp_course_remove_screen_notifications()
 *
 * Remove a screen notification for a user.
 */
function bp_course_remove_notifications() {
	global $bp;
	/**
	 * When clicking on a screen notification, we need to remove it from the menu.
	 * The following command will do so.bp_notifications_delete_notifications_by_type
 	 */
	$actions_array = apply_filters('wplms_notifications_array',array(
		'quiz_evaluated',
		'course_evaluated',
		'evaluate_assignment',
		'course_annoucements',
		'course_expire'
		));
	foreach($actions_array as $action){
		bp_notifications_delete_notifications_by_type( $bp->loggedin_user->id, $bp->course->slug, $action);
	}
}
//add_action( 'bp_before_course_results', 'bp_course_remove_notifications' );
//add_action( 'xprofile_screen_display_profile', 'bp_course_remove_notifications' );
/**
 * bp_course_format_notifications()
 *
 * The format notification function will take DB entries for notifications and format them
 * so that they can be displayed and read on the screen.
 *
 * Notifications are "screen" notifications, that is, they appear on the notifications menu
 * in the site wide navigation bar. They are not for email notifications.
 *
 *
 * The recording is done by using bp_core_add_notification() which you can search for in this file for
 * courses of usage.
 */
function bp_course_format_notifications( $action, $item_id, $secondary_item_id, $total_items ) {
	global $bp;

	switch ( $action ) {
		case 'quiz_evaluated':
			/* In this case, $item_id is the user ID of the user who sent the high five. */

			/***
			 * We don't want a whole list of similar notifications in a users list, so we group them.
			 * If the user has more than one action from the same component, they are counted and the
			 * notification is rendered differently.
			 */
			if ( (int)$total_items > 1 ) {
				return apply_filters( 'bp_course_multiple_new_quiz_notification', '<a href="' . $bp->loggedin_user->domain . $bp->course->slug . '/screen-one/" title="' . __( 'Quizes evaluated', 'vibe' ) . '">' . sprintf( __( '%d Quizes evaluated !', 'vibe' ), (int)$total_items ) . '</a>', $total_items );
			} else {
				$user_fullname = bp_core_get_user_displayname( $item_id, false );
				$user_url = bp_core_get_user_domain( $item_id );
				return apply_filters( 'bp_course_single_new_quiz_notification', '<a href="' . $user_url . '?new" title="' . $user_fullname .'\'s profile">' . sprintf( __( '%s Quiz evaluated!', 'vibe' ), $user_fullname ) . '</a>', $user_fullname );
			}
		break;
		case 'course_evaluated':
			if ( (int)$total_items > 1 ) {
				return apply_filters( 'bp_course_multiple_new_quiz_notification', '<a href="' . $bp->loggedin_user->domain . $bp->course->slug . '/screen-one/" title="' . __( 'Quizes evaluated', 'vibe' ) . '">' . sprintf( __( '%d Quizes evaluated !', 'vibe' ), (int)$total_items ) . '</a>', $total_items );
			} else {
				$user_fullname = bp_core_get_user_displayname( $item_id, false );
				$user_url = bp_core_get_user_domain( $item_id );
				return apply_filters( 'bp_course_single_new_quiz_notification', '<a href="' . $user_url . '?new" title="' . $user_fullname .'\'s profile">' . sprintf( __( '%s Quiz evaluated!', 'vibe' ), $user_fullname ) . '</a>', $user_fullname );
			}
		break;
		case 'evaluate_assignment':
			if ( (int)$total_items > 1 ) {
				return apply_filters( 'bp_course_multiple_new_quiz_notification', '<a href="' . $bp->loggedin_user->domain . $bp->course->slug . '/screen-one/" title="' . __( 'Quizes evaluated', 'vibe' ) . '">' . sprintf( __( '%d Quizes evaluated !', 'vibe' ), (int)$total_items ) . '</a>', $total_items );
			} else {
				$user_fullname = bp_core_get_user_displayname( $item_id, false );
				$user_url = bp_core_get_user_domain( $item_id );
				return apply_filters( 'bp_course_single_new_quiz_notification', '<a href="' . $user_url . '?new" title="' . $user_fullname .'\'s profile">' . sprintf( __( '%s Quiz evaluated!', 'vibe' ), $user_fullname ) . '</a>', $user_fullname );
			}
		break;
		case 'course_annoucements':
			if ( (int)$total_items > 1 ) {
				return apply_filters( 'bp_course_multiple_new_quiz_notification', '<a href="' . $bp->loggedin_user->domain . $bp->course->slug . '/screen-one/" title="' . __( 'Quizes evaluated', 'vibe' ) . '">' . sprintf( __( '%d Quizes evaluated !', 'vibe' ), (int)$total_items ) . '</a>', $total_items );
			} else {
				$user_fullname = bp_core_get_user_displayname( $item_id, false );
				$user_url = bp_core_get_user_domain( $item_id );
				return apply_filters( 'bp_course_single_new_quiz_notification', '<a href="' . $user_url . '?new" title="' . $user_fullname .'\'s profile">' . sprintf( __( '%s Quiz evaluated!', 'vibe' ), $user_fullname ) . '</a>', $user_fullname );
			}
		break;
		case 'course_expire':
			if ( (int)$total_items > 1 ) {
				return apply_filters( 'bp_course_multiple_new_quiz_notification', '<a href="' . $bp->loggedin_user->domain . $bp->course->slug . '/screen-one/" title="' . __( 'Quizes evaluated', 'vibe' ) . '">' . sprintf( __( '%d Quizes evaluated !', 'vibe' ), (int)$total_items ) . '</a>', $total_items );
			} else {
				$user_fullname = bp_core_get_user_displayname( $item_id, false );
				$user_url = bp_core_get_user_domain( $item_id );
				return apply_filters( 'bp_course_single_new_quiz_notification', '<a href="' . $user_url . '?new" title="' . $user_fullname .'\'s profile">' . sprintf( __( '%s Quiz evaluated!', 'vibe' ), $user_fullname ) . '</a>', $user_fullname );
			}
		break;
	}

	do_action( 'bp_course_format_notifications', $action, $item_id, $secondary_item_id, $total_items );

	return false;
}

/**
 * Notification functions are used to send email notifications to users on specific events
 * They will check to see the users notification settings first, if the user has the notifications
 * turned on, they will be sent a formatted email notification.
 *
 * You should use your own custom actions to determine when an email notification should be sent.
 */

function bp_course_send_course_evaluation_notification( $course_id, $marks,$student_id ) {
	$course_notifications = vibe_get_option('course_notifications');
	if(!isset($course_notifications) || !$course_notifications)
		return;
	global $bp;
	$instructor_id = get_post_field('post_author',$course_id);
	/* Let's grab both user's names to use in the email. */
	$sender_name = bp_core_get_user_displayname( $instructor_id, false );
	$reciever_name = bp_core_get_user_displayname( $student_id, false );

	bp_core_add_notification($course_id,$student_id,'course','course_evaluated',0,false,1);

	/* Get the userdata for the reciever and sender, this will include usernames and emails that we need. */
	$reciever_ud = get_userdata( $student_id );
	$sender_ud = get_userdata( $instructor_id);

	/* Now we need to construct the URL's that we are going to use in the email */
	$sender_profile_link = site_url( BP_MEMBERS_SLUG . '/' . $sender_ud->user_login . '/' . $bp->profile->slug );
	$results_link = site_url( BP_MEMBERS_SLUG . '/' . $reciever_ud->user_login . '/' . $bp->course->slug . '/stats/' );
	$reciever_settings_link = site_url( BP_MEMBERS_SLUG . '/' . $reciever_ud->user_login . '/settings/notifications' );
	$course_title = get_the_title($course_id);
	/* Set up and send the message */
	$to = $reciever_ud->user_email;
	$subject = sprintf( __( 'Course %s results are available', 'vibe' ), stripslashes($sender_name) );
	$message = sprintf( __('Results for %s are avaialble. You\'ve recieved %s marks. see results %s ,evaluated by %s [%s]', 'vibe' ), $course_title,$marks,$results_link ,$sender_name, $sender_profile_link);
	$message .= sprintf( __( 'To disable these notifications please log in and go to: %s', 'vibe' ), $receiver_settings_link );
	// Send it!
	wp_mail( $to, $subject, $message );
}

// Same for Quiz and Assignment Evaluation
function bp_course_send_quiz_evaluation_notification( $quiz_id, $marks,$student_id ) {
	$course_notifications = vibe_get_option('course_notifications');
	if(!isset($course_notifications) || !$course_notifications)
		return;

	global $bp;
	$instructor_id = get_post_field('post_author',$quiz_id);
	/* Let's grab both user's names to use in the email. */
	$sender_name = bp_core_get_user_displayname( $instructor_id, false );
	$reciever_name = bp_core_get_user_displayname( $student_id, false );

	bp_core_add_notification($quiz_id,$student_id,'course','quiz_evaluation',0,false,1);

	/* Get the userdata for the reciever and sender, this will include usernames and emails that we need. */
	$reciever_ud = get_userdata( $student_id );
	$sender_ud = get_userdata( $instructor_id);

	/* Now we need to construct the URL's that we are going to use in the email */
	$sender_profile_link = site_url( BP_MEMBERS_SLUG . '/' . $sender_ud->user_login . '/' . $bp->profile->slug );
	$results_link = site_url( BP_MEMBERS_SLUG . '/' . $reciever_ud->user_login . '/' . $bp->course->slug . '/course-results/?action='.$quiz_id );
	$reciever_settings_link = site_url( BP_MEMBERS_SLUG . '/' . $reciever_ud->user_login . '/settings/notifications' );
	$course_title = get_the_title($quiz_id);
	/* Set up and send the message */
	$to = $reciever_ud->user_email;
	$subject = sprintf( __( 'Course %s results are available', 'vibe' ), stripslashes($sender_name) );
	$message = sprintf( __('Results for %s are avaialble. You\'ve recieved %s marks. see results %s ,evaluated by %s [%s]', 'vibe' ), $course_title,$marks,$results_link ,$sender_name, $sender_profile_link);
	$message .= sprintf( __( 'To disable these notifications please log in and go to: %s', 'vibe' ), $receiver_settings_link );
	// Send it!
	wp_mail( $to, $subject, $message );
}

//Annoucement notification
function bp_course_send_announcement_notification( $course_id,$type,$email) {

	if(!isset($email) || !$email)
		return;

	global $bp;
	$instructor_id = get_post_field('post_author',$course_id);
	/* Let's grab both user's names to use in the email. */
	$sender_name = bp_core_get_user_displayname( $instructor_id, false );
	$reciever_name = bp_core_get_user_displayname( $student_id, false );

	 bp_core_add_notification($course_id,$student_id,'course','course_annoucement',0,false,1);

	/* Get the userdata for the reciever and sender, this will include usernames and emails that we need. */
	$reciever_ud = get_userdata( $student_id );
	$sender_ud = get_userdata( $instructor_id);

	/* Now we need to construct the URL's that we are going to use in the email */
	$sender_profile_link = site_url( BP_MEMBERS_SLUG . '/' . $sender_ud->user_login . '/' . $bp->profile->slug );
	$results_link = site_url( BP_MEMBERS_SLUG . '/' . $reciever_ud->user_login . '/' . $bp->course->slug . '/course-results/?action='.$quiz_id );
	$reciever_settings_link = site_url( BP_MEMBERS_SLUG . '/' . $reciever_ud->user_login . '/settings/notifications' );
	$course_title = get_the_title($quiz_id);
	/* Set up and send the message */
	$to = $reciever_ud->user_email;
	$subject = sprintf( __( 'Course %s results are available', 'vibe' ), stripslashes($sender_name) );
	$message = sprintf( __('Results for %s are avaialble. You\'ve recieved %s marks. see results %s ,evaluated by %s [%s]', 'vibe' ), $course_title,$marks,$results_link ,$sender_name, $sender_profile_link);
	$message .= sprintf( __( 'To disable these notifications please log in and go to: %s', 'vibe' ), $receiver_settings_link );
	// Send it!
	wp_mail( $to, $subject, $message );
}

//add_action('badgeos_wplms_evaluate_course','bp_course_send_course_evaluation_notification',10,3);
//add_action('badgeos_wplms_evaluate_quiz','bp_course_send_quiz_evaluation_notification',10,3);
//add_action('badgeos_wplms_evaluate_assignment','bp_course_send_quiz_evaluation_notification',10,3);
//add_action('wplms_dashboard_course_annoucement','bp_course_send_annoucement_notification');



?>
