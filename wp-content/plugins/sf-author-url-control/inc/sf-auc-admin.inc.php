<?php
if( !defined( 'ABSPATH' ) )
	die( 'Cheatin\' uh?' );

/* !-------------------------------------------------------------------------------- */
/* !ACTIVATION: set a transient for displaying a help message,						 */
/* flush rewrite rules with a possible existing author base							 */
/* --------------------------------------------------------------------------------- */

function sf_auc_activation() {
	update_option( 'sf_auc_first_message', 1 );
	sf_auc_author_base();
	flush_rewrite_rules();
}

register_activation_hook( SF_AUC_FILE, 'sf_auc_activation' );


/* !-------------------------------------------------------------------------------- */
/* !DEACTIVATION: flush rewrite rules with "author" as author_base					 */
/* --------------------------------------------------------------------------------- */

function sf_auc_deactivation() {
	global $wp_rewrite;
	if ( $wp_rewrite->author_base != 'author' ) {
		$wp_rewrite->author_base = 'author';
		flush_rewrite_rules();
	}
}

register_deactivation_hook( SF_AUC_FILE, 'sf_auc_deactivation' );


/* !-------------------------------------------------------------------------------- */
/* !UNINSTALL: delete author base option											 */
/* --------------------------------------------------------------------------------- */

function sf_auc_uninstaller() {
	delete_option( 'author_base' );
	sf_auc_deactivation();
}

register_uninstall_hook( SF_AUC_FILE, 'sf_auc_uninstaller' );


/* --------------------------------------------------------------------------------- */
/* !LANGUAGE SUPPORT																 */
/* --------------------------------------------------------------------------------- */

add_action( 'init', 'sf_auc_lang_init' );

function sf_auc_lang_init() {
	load_plugin_textdomain( 'sf-author-url-control', false, SF_AUC_DIRNAME . '/languages/' );
}


/* !-------------------------------------------------------------------------------- */
/* !ACTIVATION MESSAGE																 */
/* --------------------------------------------------------------------------------- */

add_action( 'admin_notices', 'sf_auc_activation_message' );

function sf_auc_activation_message() {
	global $pagenow;
	if ( $pagenow == 'plugins.php' &&  get_option( 'sf_auc_first_message' ) ) {
		echo '<div class="updated">'."\n"
				.'<p>'.sprintf(
					__('<strong>SF Author Url Control</strong>: Now you can go to Settings &#8250; %1$sPermalinks</a> to change the authors base url. Also, go to %2$sUsers</a> and chose a user profile, %3$slike your own</a>, for the user&#8217;s slug.', 'sf-author-url-control'),
					'<a href="' . admin_url('options-permalink.php') . '#author_base">', '<a href="' . self_admin_url('users.php') . '">', '<a href="' . self_admin_url('profile.php') . ( is_network_admin() ? '?wp_http_referer=' . urlencode( network_admin_url('users.php') ) : '' ) . '#user_nicename">'
				) . "</p>\n"
			.'</div>';
		delete_option( 'sf_auc_first_message' );
	}
}


/* !-------------------------------------------------------------------------------- */
/* !ADD A "SETTINGS LINK"															 */
/* --------------------------------------------------------------------------------- */

add_filter( 'plugin_action_links_'.SF_AUC_BASENAME, 'sf_auc_settings_action_links', 10, 2 );
add_filter( 'network_admin_plugin_action_links_'.SF_AUC_BASENAME, 'sf_auc_settings_action_links', 10, 2 );

function sf_auc_settings_action_links( $links, $file ) {
	$links['settings'] = '<a href="' . admin_url('options-permalink.php') . '#author_base">' . __('Permalinks') . '</a>';
	return $links;
}


/* !-------------------------------------------------------------------------------- */
/* !COLUMNS IN USERS LIST															 */
/* --------------------------------------------------------------------------------- */

add_filter( 'manage_users_columns',       'sf_auc_manage_users_columns' );
add_filter( 'manage_users_custom_column', 'sf_auc_manage_users_custom_column', 10, 3 );

function sf_auc_manage_users_columns( $defaults ) {
	$defaults['user-nicename'] = __( 'URL slug', 'sf-author-url-control' );
	return $defaults;
}


function sf_auc_manage_users_custom_column( $default, $column_name, $user_id ) {
	if ( $column_name == 'user-nicename' ) {
		$userdata = get_userdata( (int) $user_id );
		$userdata->user_nicename = isset($userdata->user_nicename) && $userdata->user_nicename ? sanitize_title( $userdata->user_nicename ) : '';

		if ( !$userdata->user_nicename ) {
			$span = array( '<span style="color:red;font-weight:bold">', '</span>' );
			$userdata->user_nicename = __('Empty slug!', 'sf-author-url-control');
		}
		elseif ( sf_auc_user_can_edit_user_slug( $user_id ) && $userdata->user_nicename != sanitize_title($userdata->user_login) )
			$span = array( '<span style="color:green">', '</span>' );
		else
			$span = array('','');

		$default = $span[0] . $userdata->user_nicename . $span[1];
	}

	return $default;
}


// Some CSS
add_action( 'admin_print_scripts-users.php', 'sf_auc_manage_users_column_css' );

function sf_auc_manage_users_column_css() {
	echo '<style type="text/css">.manage-column.column-user-nicename{width:12em}</style>'."\n";
}


/* --------------------------------------------------------------------------------- */
/* 																					 */
/* !AUTHORS BASE =================================================================== */
/* 																					 */
/* --------------------------------------------------------------------------------- */

// Set the new author base, update/delete the option, init rewrite

function sf_auc_set_author_base( $author_base = '' ) {
	if ( $author_base !== get_option('author_base') ) {
		global $wp_rewrite;
		if ( $author_base && $author_base != 'author' ) {
			update_option( 'author_base', $author_base );
			$wp_rewrite->author_base = $author_base;
		} else {
			delete_option( 'author_base' );
			$wp_rewrite->author_base = 'author';
		}
		$wp_rewrite->init();
	}
}


// Remove "/" or "/blog/" at the beginning of an uri

function sf_auc_remove_blog_slug( $uri ) {
	global $wp_rewrite;
	$front = !empty( $wp_rewrite ) ? trim($wp_rewrite->front, '/') . '/' : 'blog/';
	$uri   = trim( $uri, '/' );
	$uri   = strpos( $uri, $front ) === 0 ? substr( $uri, 0, strlen($front) ) : $uri;		// Compat old version of the plugin
	return $uri;
}


/* !-------------------------------------------------------------------------------- */
/* !ADD THE FIELD TO THE PERMALINKS PAGE											 */
/* --------------------------------------------------------------------------------- */

add_action( 'load-options-permalink.php', 'sf_auc_register_setting' );

function sf_auc_register_setting() {
	add_settings_field( 'author_base', __( 'Authors page base', 'sf-author-url-control' ), 'sf_auc_author_base_field', 'permalink', 'optional', array( 'label_for' => 'author_base' ) );
}


/* !-------------------------------------------------------------------------------- */
/* !PRINT THE FIELD IN THE PERMALINKS SETTINGS PAGE									 */
/* --------------------------------------------------------------------------------- */

function sf_auc_author_base_field( $args ) {
	$blog_prefix	= '';
	$author_base	= sf_auc_get_author_base();
	$author_base	= $author_base != 'author' ? $author_base : '';

	if ( is_multisite() && !is_subdomain_install() && is_main_site() ) {
		$blog_prefix = '/blog';
		$author_base = $author_base ? '/'.$author_base : $author_base;
	}

	echo $blog_prefix . ' <input name="author_base" id="author_base" type="text" value="'.$author_base.'" class="regular-text code"/> <span class="description">('.__( 'Leave empty for default value: author', 'sf-author-url-control' ).')</span>';

	if ( is_dir( path_join( plugin_dir_path( dirname( SF_AUC_FILE ) ), 'user-name-security/' ) ) )
		return;

	if ( defined('NAH_LEAVE_ME_ALONE') && NAH_LEAVE_ME_ALONE )
		return;

	echo '<p>';
	printf(
		__( 'If you also want to remove the display of your login in the CSS classes from the <code>&lt;body&gt;</code> tag of your site, and force your members to change their display name, I advise you to install %s.', 'sf-author-url-control' ),
		'<a href="' . wp_nonce_url( self_admin_url( 'update.php?action=install-plugin&plugin=user-name-security' ), 'install-plugin_user-name-security' ) . '" title="' . sprintf( __('Install %s'), 'SX User Name Security' ) . '">SX User Name Security</a>'
	);
	echo "</p>\n";
}


/* !-------------------------------------------------------------------------------- */
/* !SAVE THE AUTHOR BASE AND DISPLAY ERROR NOTICES									 */
/* --------------------------------------------------------------------------------- */

add_action( 'load-options-permalink.php', 'sf_auc_save_author_base' );

function sf_auc_save_author_base() {

	if ( isset( $_POST['submit'], $_POST['author_base'] ) && current_user_can( 'manage_options' ) ) {
		check_admin_referer('update-permalink');

		$author_base = sanitize_title( $_POST['author_base'] );

		// Check for identical slug
		if ( $author_base == '' || $author_base == 'author' ) {

			sf_auc_set_author_base();

		} else {

			global $wp_rewrite;
			$message = false;
			$is_first_blog = is_multisite() && !is_subdomain_install() && is_main_site();

			// Get all the available slugs
			$bases = array();	// slug => what

			// The "obvious" ones
			$bases['blog']							= 'blog';
			$bases['date']							= 'date';
			$bases[$wp_rewrite->search_base]		= 'search_base';
			$bases[$wp_rewrite->comments_base]		= 'comments_base';
			$bases[$wp_rewrite->pagination_base]	= 'pagination_base';
			$bases[$wp_rewrite->feed_base]			= 'feed_base';

			// RSS
			if ( count($wp_rewrite->feeds) ) {
				foreach ( $wp_rewrite->feeds as $item ) {
					$bases[$item] = $item;
				}
			}

			// Post types and taxos
			$post_types = get_post_types( array('public' => true), 'objects' );
			$taxos = get_taxonomies( array('public' => true), 'objects' );
			$whatever = array_merge( $taxos, $post_types );
			if ( count($whatever) ) {
				foreach ( $whatever as $what ) {
					// Singular
					if ( !empty($what->rewrite['slug']) ) {
						$bases[$what->rewrite['slug']] = $what->name;
					} else
						$bases[$what->name] = $what->name;
					// Archive
					if ( !empty($what->has_archive) && true !== $what->has_archive )
						$bases[$what->has_archive] = $what->name;
				}
			}

			if ( !empty($bases[$author_base]) ) {	// Oops!

				if ( taxonomy_exists( $bases[$author_base] ) )									// Taxos

					$message = __(" (for a taxonomy)", 'sf-author-url-control');

				elseif ( post_type_exists( $bases[$author_base] ) )								// Post type

					$message = __(" (for a custom post type)", 'sf-author-url-control');

				else

					$message = '';
			}
			elseif ( get_page_by_path( $author_base ) )											// Page

				$message =  __(" (for a page)", 'sf-author-url-control');

			elseif ( trim(get_option( 'permalink_structure' ), '/') == trim($wp_rewrite->front.'%postname%', '/') && get_page_by_path( $author_base, 'OBJECT', 'post' ) )		// Post

				$message = __(" (for a post)", 'sf-author-url-control');

			if ( $message !== false ) {
				add_settings_error( 'permalink', 'wrong_author_base', sprintf(__('<strong>ERROR</strong>: This authors page base is already used somewhere else%s. Please choose another one.', 'sf-author-url-control'), $message) );
				set_transient('settings_errors', get_settings_errors(), 30);
				return;
			}

			sf_auc_set_author_base( $author_base );

		}
	}
}


/* --------------------------------------------------------------------------------- */
/* 																					 */
/* !USER PROFILE =================================================================== */
/* 																					 */
/* --------------------------------------------------------------------------------- */

// Return true if the current user can edit the user slug

function sf_auc_user_can_edit_user_slug( $user_id = false ) {
	return current_user_can('edit_users') || ( ( (defined('IS_PROFILE_PAGE') && IS_PROFILE_PAGE) || ($user_id && $user_id == get_current_user_id()) ) && apply_filters( 'sf_auc_user_can_edit_user_slug', false ) );
}

/*
Example to allow editors to edit their own profile slug:

add_filter( 'sf_auc_user_can_edit_user_slug', 'allow_editors_to_edit_slug' );
function allow_editors_to_edit_slug() {
	return current_user_can( 'edit_pages' );
}
*/


/* --------------------------------------------------------------------------------- */
/* !ADD A TEXT FIELD IN THE USER PROFILE											 */
/* --------------------------------------------------------------------------------- */

add_action( 'show_user_profile', 'sf_auc_edit_user_options' );		// Own profile
add_action( 'edit_user_profile', 'sf_auc_edit_user_options' );		// Others

function sf_auc_edit_user_options() {
	global $user_id;
	$user_id = isset($user_id) ? (int) $user_id : 0;

	if ( !($userdata = get_userdata( $user_id )) )
		return;

	if ( !sf_auc_user_can_edit_user_slug() )
		return;

	$def_user_nicename	= sanitize_title( $userdata->user_login );
	$blog_prefix		= is_multisite() && !is_subdomain_install() && is_main_site() ? '/blog/' : '/';
	$author_base		= $GLOBALS['wp_rewrite']->author_base;
	$link				= current_user_can( 'manage_options' ) && $author_base == 'author' ? '<a title="' . esc_attr__( 'Do you know you can change this part too?', 'sf-author-url-control' ) . '" href="' . admin_url('options-permalink.php') . '#author_base">' : '';

	echo '<table class="form-table">'."\n"
			."<tr>\n"
				.'<th><label for="user_nicename">'.__('Profile URL slug', 'sf-author-url-control')."</label></th>\n"
				.'<td>'
					.$blog_prefix . $link . $author_base . ($link ? '</a>' : '') . '/'
					.'<input id="user_nicename" name="user_nicename" class="regular-text code" type="text" value="'.sanitize_title($userdata->user_nicename, $def_user_nicename).'"/> '
					.'<span class="description">('.sprintf(__('Leave empty for default value: %s', 'sf-author-url-control'), $def_user_nicename).')</span> '
					.'<a href="'.get_author_posts_url($user_id).'">'.__('Your Profile').'</a> '
				."</td>\n"
			."</tr>\n"
		."</table>\n";
}


/* !-------------------------------------------------------------------------------- */
/* !SAVE USER NICENAME																 */
/* --------------------------------------------------------------------------------- */

add_action( 'personal_options_update',  'sf_auc_save_user_options' );	// Own profile
add_action( 'edit_user_profile_update', 'sf_auc_save_user_options' );	// Others

function sf_auc_save_user_options() {
	$user_id = isset($_POST['user_id']) ? (int) $_POST['user_id'] : 0;

	if ( !isset($_POST[ '_wpnonce' ]) || !wp_verify_nonce( $_POST[ '_wpnonce' ], 'update-user_'.$user_id ) )
		return;
	if ( !sf_auc_user_can_edit_user_slug() )
		return;
	if ( !isset($_POST['user_nicename']) || !( $userdata = get_userdata( $user_id ) ) )
		return;

	$def_user_nicename	= sanitize_title( $userdata->user_login );
	$new_nicename		= sanitize_title($_POST['user_nicename'], $def_user_nicename);

	if ( $new_nicename == $userdata->user_nicename )
		return;

	if ( !get_user_by('slug', $new_nicename) ) {
		if ( !wp_update_user( array ('ID' => $user_id, 'user_nicename' => $new_nicename) ) )
			add_action('user_profile_update_errors', 'sf_auc_user_profile_slug_generic_error', 10, 3 );
	} else
		add_action('user_profile_update_errors', 'sf_auc_user_profile_slug_error', 10, 3 );
}


// Notices

function sf_auc_user_profile_slug_generic_error( $errors, $update, $user ) {
	$errors->add( 'user_nicename', __( '<strong>ERROR</strong>: There was an error updating the author slug. Please try again.', 'sf-author-url-control' ) );
}


function sf_auc_user_profile_slug_error( $errors, $update, $user ) {
	$errors->add( 'user_nicename', __( '<strong>ERROR</strong>: This profile URL slug is already registered. Please choose another one.', 'sf-author-url-control' ) );
}
/**/