<?php
/**
 * @package by_Sofia 
 * @version 1.0
 */
/*
Plugin Name: Plugin by Sofia: Classroom Friendly
Plugin URI: 
Description: Plugins to create student/teacher functionality such as: 1)Teacher/Student Privacy of post and comment visibility 2) Adds Student, Teaching Assistant and Instrutor Roles 3)removes quick edit from student roles 4)loads Comments as homepage for Dashview

Author: Sofia Robb
Version: 1.0
Author URI: 
*/



/**
 * Checks if a particular user has a role.
 * Returns true if a match was found.
 *
 * @param string $role Role name.
 * @param int $user_id (Optional) The ID of a user. Defaults to the current user.
 * @return bool
*/
function appthemes_check_user_role( $role, $user_id = null ) {

    if ( is_numeric( $user_id ) )
        $user = get_userdata( $user_id );
    else
        $user = wp_get_current_user();

    if ( empty( $user ) )
        return false;

    return in_array( $role, (array) $user->roles );
}


function bySofia_get_current_user_role (){
  global $current_user, $wpdb;
  $role = $wpdb->prefix . 'capabilities';
  $current_user->role = array_keys($current_user->$role);
  $role = $current_user->role[0];
  return $role;
}

// allow only a user's own posts to be viewed in dashboard
function bySofia_remove_post_counts($posts_count_disp)
{
  //$posts_count_disp contains the 3 links, we keep 'Mine' and remove the other two.
  unset($posts_count_disp['all']);
  unset($posts_count_disp['publish']);
  unset($posts_count_disp['trash']);
  unset($posts_count_disp['draft']);
  unset($posts_count_disp['pending']);
  unset($posts_count_disp['private']);
  return $posts_count_disp;
}

add_action('pre_get_posts', 'bySofia_filter_posts_list');
function bySofia_filter_posts_list($query)
{
    //$pagenow holds the name of the current page being viewed
     global $pagenow;
 
    //$current_user uses the get_currentuserinfo() method to get the currently logged in user's data
     global $current_user;
     get_currentuserinfo();
  
     $is_Editor = appthemes_check_user_role( 'editor' ) ; // default is current user
     $is_teachingAssistant = appthemes_check_user_role( 'teachingassistant' ) ; // default is current user
     $is_instructor = appthemes_check_user_role( 'instructor' ) ; // default is current user
     $is_Student = appthemes_check_user_role( 'student' ) ; // default is current user
     $is_Author = appthemes_check_user_role( 'author' ) ; // default is current user
    
        //Shouldn't happen for the admin,editors, or TA's  but for any role with the edit_posts capability and only on the posts list page, that is edit.php
        //if( ($is_Student OR $is_Author ) && current_user_can('edit_posts')  && ('edit.php' == $pagenow))
        if(!current_user_can('administrator') && !$is_Editor && !$is_instructor && !$is_teachingAssistant && current_user_can('edit_posts') && ('edit.php' == $pagenow))
     {
        //global $query's set() method for setting the author as the current user's id
        //$user_role =  bySofia_get_current_user_role();
        //echo  $user_role;
        $query->set('author', $current_user->ID);
        $screen = get_current_screen();
        add_filter('views_'.$screen->id, 'bySofia_remove_post_counts');
     }
}

// make comments viewable only to the to and from users
add_filter('the_comments', 'wpse56652_filter_comments');

function wpse56652_filter_comments($comments){
    global $pagenow;
    global $user_ID;
    get_currentuserinfo();
    $is_Editor = appthemes_check_user_role( 'editor' ) ; // default is current user
    $is_teachingAssistant = appthemes_check_user_role( 'teachingassistant' ) ; // default is current user
    $is_instructor = appthemes_check_user_role( 'instructor' ) ; // default is current user
    $is_grader = $is_instructor or $is_teachingAssistant or $is_Editor or current_user_can('administrator') ? 1 : 0;
    $is_Student = appthemes_check_user_role( 'student' ) ; // default is current user
    //Shouldn't happen for the admin or editors, but for any role with the edit_posts capability and only on the posts list page, that is edit.php
    if(!current_user_can('administrator') && !$is_Editor && !$is_instructor && !$is_teacingAssistant && $pagenow == 'edit-comments.php' && $is_Student &&  current_user_can('edit_posts')){
        foreach($comments as $i => $comment){
            $the_post = get_post($comment->comment_post_ID);
            if($comment->user_id != $user_ID  && $the_post->post_author != $user_ID)
                unset($comments[$i]);
        }
    }
    return $comments;
}



//removes quick edit from student 
function remove_quick_edit( $actions ) {
    $is_Student = appthemes_check_user_role( 'student' ) ; // default is current user
    $is_Author = appthemes_check_user_role( 'author' ) ; // default is current user
    global $post;
    //if( $post->post_type == 'custom_post_type_name' ) {
    if( $is_Student or $is_Author) {
        unset($actions['inline hide-if-no-js']);
	unset( $actions['edit'] );
	//unset( $actions['view'] );
	unset( $actions['trash'] );

	}
   return $actions;
}

if (is_admin() ) {
	add_filter('post_row_actions','remove_quick_edit',10,2);
}
//checks for Whisper-comment-reloaded installation
function bySofia_checkWhisperInstall(){ 
/// getting error: Call to undefined function is_plugin_active() in /Library/WebServer/Documents/wp-content/plugins/by_sofia/by_Sofia.php on line 132, but this cannot be, because i use it with privateMessages
// if (is_plugin_active('whisper-comment-reloaded/whisper-comment-reloaded.php')) {
   if (file_exists('/Library/WebServer/Documents/wp-content/plugins/whisper-comment-reloaded/whisper-comment-reloaded.php')) {
    return 1;
  }else {
   return 0;
 }
}

// allows comments on pending posts, to be used with whisper comment installed.
add_action ('comment_on_draft', 'bySofia_comment_on_draft');
function bySofia_comment_on_draft(){
  $time = current_time('mysql');
  $current_user = wp_get_current_user();
  $user_ID = (isset ($current_user->ID) ? trim (($current_user->ID) ) : null) ; 
  $comment_author = ( isset( $current_user->display_name)) ? trim( $current_user->display_name) : null  ; 
  $comment_post_ID = isset($_POST['comment_post_ID']) ? (int) $_POST['comment_post_ID'] : 0;
  $url = get_permalink( $comment_post_ID );
  $comment_author_email = ( isset($current_user->user_email) )   ? trim($current_user->user_email) : null;
  $comment_author_url   = ( isset($current_user->user_url) )     ? trim($current_user->user_url) : null;
  $comment_content      = ( isset($_POST['comment']) ) ? trim($_POST['comment']) : null;
  $comment_parent = isset($_POST['comment_parent']) ? absint($_POST['comment_parent']) : 0;
  $comment_approved = 1;
  //$comment_type =   bySofia_checkWhisperInstall() ?  'whisper' :  '';
  //$commentdata = compact('comment_post_ID', 'comment_author', 'comment_author_email', 'comment_author_url', 'comment_content', 'comment_type', 'comment_parent', 'user_ID', 'comment_approved', 'time');
  $commentdata = compact('comment_post_ID', 'comment_author', 'comment_author_email', 'comment_author_url', 'comment_content', 'comment_type', 'comment_parent', 'user_ID', 'comment_approved', 'time');
  $status = get_post_status($post);
  if ( $status !=  "publish" ) {
    wp_new_comment($commentdata);
  }
  header( "Location: $url" ); 
}


// redirect to message box instead of Dashboard for students
//checks for Private Message installation
function bySofia_PrivateMessageInstall(){
  #$current_plugins = get_option('active_plugins');
  if (is_plugin_active('private-messages-for-wordpress/pm4wp.php')) {
  #if (in_array('private-messages-for-wordpress/pm4wp.php', $current_plugins)) {
     return 1;
  }else {
    return 0;
  }
}
add_action('load-index.php', 'dashboard_Redirect');

// redirect to comments instead of Dashboard for students
function dashboard_Redirect(){
  #if (bySofia_PrivateMessageInstall()){
   $is_Student = appthemes_check_user_role( 'student' ) ;
   $is_Author = appthemes_check_user_role( 'author' ) ;
   if ($is_Student or $is_Author){
     wp_redirect(admin_url('edit-comments.php'));
     #wp_redirect(admin_url('admin.php?page=rwpm_inbox'));
    }
  #}
}

//add classroom specific user roles:
register_activation_hook (__FILE__, 'bySofia_createClassroom_user_roles');

function bySofia_createClassroom_user_roles ()
{
  $result = add_role('student', 'Student', array(
        'activate_plugins' => false,
        'add_users' => false, 
        'create_users' => false, 
        'delete_others_pages' => false, 
        'delete_others_posts' => false, 
        'delete_pages' => false, 
        'delete_plugins' => false, 
        'delete_posts' => true, 
        'delete_private_pages' => false, 
        'delete_private_posts' => false, 
        'delete_published_pages' => false, 
        'delete_published_posts' => false, 
        'delete_themes' => false, 
        'delete_users' => false, 
        'edit_dashboard' => false, 
        'edit_others_pages' => false, 
        'edit_others_posts' => false, 
        'edit_pages' => false, 
        'edit_plugins' => false, 
        'edit_posts' => true, 
        'edit_private_pages' => false,
        
        'edit_private_posts' => false, 
        'edit_published_pages' => false, 
        'edit_published_posts' => false, 
        'edit_theme_options' => false, 
        'edit_themes' => false, 
        'edit_users' => false, 
        'export' => false, 
        'import' => false, 
        'install_plugins' => false, 
        'install_themes' => false, 
        'list_users' => false, 
        'manage_categories' => false, 
        'manage_links' => false, 
        'manage_options' => false, 
        'moderate_comments' => false, 
        'promote_users' => false, 
        'publish_pages' => false, 
        'publish_posts' => false, 
        'read' => true, 
        'read_private_pages' => false, 
        'read_private_posts' => false,
        
        'remove_users' => false, 
        'switch_themes' => false, 
        'unfiltered_html' => false, 
        'unfiltered_upload' => false, 
        'update_core' => false, 
        'update_plugins' => false, 
        'update_themes' => false, 
        'upload_files' => true, 
        
  ));

  $result = add_role('teachingassistant', 'Teaching Assistant', array(
        'activate_plugins' => false,
        'add_users' => false, 
        'create_users' => false, 
        'delete_others_pages' => true, 
        'delete_others_posts' => true, 
        'delete_pages' => true, 
        'delete_plugins' => false, 
        'delete_posts' => true, 
        'delete_private_pages' => true, 
        'delete_private_posts' => true, 
        'delete_published_pages' => true, 
        'delete_published_posts' => true, 
        'delete_themes' => false, 
        'delete_users' => false, 
        'edit_dashboard' => false, 
        'edit_others_pages' => true, 
        'edit_others_posts' => true, 
        'edit_pages' => true, 
        'edit_plugins' => false, 
        'edit_posts' => true, 
        'edit_private_pages' => true,
        
        'edit_private_posts' => true, 
        'edit_published_pages' => true, 
        'edit_published_posts' => true, 
        'edit_theme_options' => false, 
        'edit_themes' => false, 
        'edit_users' => false, 
        'export' => false, 
        'import' => false, 
        'install_plugins' => false, 
        'install_themes' => false, 
        'list_users' => false, 
        'manage_categories' => true, 
        'manage_links' => true, 
        'manage_options' => false, 
        'moderate_comments' => true, 
        'promote_users' => false, 
        'publish_pages' => true, 
        'publish_posts' => true, 
        'read' => true, 
        'read_private_pages' => true, 
        'read_private_posts' => true,
        
        'remove_users' => false, 
        'switch_themes' => false, 
        'unfiltered_html' => true, 
        'unfiltered_upload' => false, 
        'update_core' => false, 
        'update_plugins' => false, 
        'update_themes' => false, 
        'upload_files' => true, 
  ));
  $result = add_role('instructor', 'instructor', array(
        'activate_plugins' => false,
        'add_users' => true, 
        'create_users' => true, 
        'delete_others_pages' => true, 
        'delete_others_posts' => true, 
        'delete_pages' => true, 
        'delete_plugins' => true, 
        'delete_posts' => true, 
        'delete_private_pages' => true, 
        'delete_private_posts' => true, 
        'delete_published_pages' => true, 
        'delete_published_posts' => true, 
        'delete_themes' => false, 
        'delete_users' => true, 
        'edit_dashboard' => false, 
        'edit_others_pages' => true, 
        'edit_others_posts' => true, 
        'edit_pages' => true, 
        'edit_plugins' => true, 
        'edit_posts' => true, 
        'edit_private_pages' => true,
        
        'edit_private_posts' => true, 
        'edit_published_pages' => true, 
        'edit_published_posts' => true, 
        'edit_theme_options' => true, 
        'edit_themes' => false, 
        'edit_users' => true, 
        'export' => true, 
        'import' => true, 
        'install_plugins' => true, 
        'install_themes' => false, 
        'list_users' => true, 
        'manage_categories' => true, 
        'manage_links' => true, 
        'manage_options' => true, 
        'moderate_comments' => true, 
        'promote_users' => true, 
        'publish_pages' => true, 
        'publish_posts' => true, 
        'read' => true, 
        'read_private_pages' => true, 
        'read_private_posts' => true,
        
        'remove_users' => true, 
        'switch_themes' => true, 
        'unfiltered_html' => true, 
        'unfiltered_upload' => true, 
        'update_core' => false, 
        'update_plugins' => false, 
        'update_themes' => false,
        'upload_files' => true, 
  
  ));

}


?>
