<?php
/*
Plugin Name: Whisper Comment For Class
Plugin URI: http://www.joshparker.us/blog/wordpress/whisper_comment_reloaded.html
Description: Whisper Comment For Class is a WordPress plugin for commenters to control comment visibility by other students. Instructors (wp_role=instructor,teachingassistant) can leave comments for students (wp_role=student) that are only visible by that student. Students can reply to the Instructor privately. 

Version: 1.0
Original Author: Anubis From Memphis
Other Author: Joshua Parker Consulting
Author: Sofia Robb
Author URI: https://github.com/srobb1/WP_Notebook
*/

  add_action ('admin_menu', 'whisper_comment_add_options');
  // Sofia changed capablilty of edit_posts to edit_plugins	
  
  function whisper_comment_add_options () {
    add_options_page(__('Whisper Comment Reloaded Settings','whisper-comment-afm'), __('Whisper Comment Reloaded','whisper-comment-afm'), 'edit_plugins', __FILE__, 'whisper_comment_options');
  }
  	
  
  function whisper_comment_options(){
    global $wpdb;
    $options = get_option('whisper_comment_options');
  		
    // Update options and print message
    if($_POST['wc_submit'] && $_POST['wc_submit'] == __('Save Changes','whisper-comment-afm')) {
      if(0 == $_POST['wc_whisper_on'])
        $wpdb->query("UPDATE {$wpdb->comments} SET comment_approved = 0 WHERE comment_type LIKE 'whisper%'");
      else if (1 == $_POST['wc_whisper_on'])
        $wpdb->query("UPDATE {$wpdb->comments} SET comment_approved = 1 WHERE comment_type LIKE 'whisper%'");
        $options = array (
          'whisper_on'	=> $_POST['wc_whisper_on'],
          'whisper_to'	=> $_POST['wc_whisper_to'],
          'whisper_form_label' => $_POST['wc_whisper_form_label'],
          'whisper_denied_msg' => $_POST['wc_whisper_denied_msg'],
          'whisper_granted_msg'=> $_POST['wc_whisper_granted_msg'],
          'whisper_author_msg' => $_POST['wc_whisper_author_msg'],
          'whisper_admin_msg' => $_POST['wc_whisper_admin_msg']
        );
  
        update_option('whisper_comment_options', $options);	
        echo '<div id="message" class="updated fade"><p>';
        _e('Options saved.');
        echo '</p></div>';	
    }
  		
    // Convert all whispers and print message
    if($_POST['wc_convert_whispers'] && $_POST['wc_convert_whispers'] == __('Convert All Whispers','whisper-comment-afm')){
      echo '<div id="message" class="updated fade"><p>';
      $whispers = $wpdb->get_results("SELECT * FROM {$wpdb->comments} WHERE comment_type LIKE 'whisper%'");
      if(count($whispers) > 0) {
        $wpdb->query("UPDATE {$wpdb->comments} SET comment_type = '',comment_approved = 0 WHERE comment_type LIKE 'whisper%'");
        printf(__('All whispers have been converted. There are %s comments affected.','whisper-comment-afm'), count($whispers));
      } else {
        _e('There are currently no whispers.','whisper-comment-afm');				
      }
      echo '</p></div>';	
    }
  		
    // Delete all whispers and print message
    if($_POST['wc_delete_whispers'] && $_POST['wc_delete_whispers'] == __('Delete All Whispers','whisper-comment-afm')){
      echo '<div id="message" class="updated fade"><p>';
      $whispers = $wpdb->get_results("SELECT * FROM {$wpdb->comments} WHERE comment_type LIKE 'whisper%'");
      if(count($whispers) > 0) {
        $wpdb->query("DELETE FROM {$wpdb->comments} WHERE comment_type LIKE 'whisper%'");
        printf(__('All whispers have been deleted. There are %s comments affected.','whisper-comment-afm'), count($whispers));
      } else {
        _e('There are currently no whispers.','whisper-comment-afm');				
      }
      echo '</p></div>';
    }
  		
    // Default options
    if (empty($options)) {
      $options = array (
      'whisper_on'		=> 0,
      'whisper_to'		=> 1,
      'whisper_form_label'	=> __('Whisper to blog author(s)','whisper-comment-afm'),
      'whisper_denied_msg'	=> __('(...whisper...)','whisper-comment-afm'),
      'whisper_granted_msg'	=> __('(...whisper for you...)','whisper-comment-afm'),
      'whisper_author_msg'	=> __('(...whisper from you...)','whisper-comment-afm'),
      'whisper_admin_msg'		=> __('(...whisper...)','whisper-comment-afm')
  						);
      update_option('whisper_comment_options', $options);
    }
  
  
?>

<!-- DIV WRAP start -->
<div class="wrap">
  <h2><?php _e('Whisper Comment Reloaded Options','whisper-comment-afm'); ?></h2>
  <p><?php _e('Whisper Comment Reloaded is a plugin for comment authors to control the comment visibility to other users.','whisper-comment-afm') ?></p>
			
  <!-- DIV POSTSTUFF start -->
  <div id="poststuff" class="metabox-holder">
                
    <!-- DIV META-BOX-SORTABLESS start -->
    <div class="meta-box-sortabless ui-sortable" id="side-sortables">
                        
      <!-- DIV POSTBOX start -->
      <div class="postbox" id="solstice_feedburner_options">
        <h3><?php _e('Whisper Comment Reloaded Database Cleaning Tools','whisper-comment-afm') ?></h3>
                            
        <!-- DIV INSIDE start -->
        <div class="inside">                                
          <p><?php _e('<ul class="settings"><li>Convert All Whispers will convert all whispers to normal comments and change their statuses to waiting.</li><li>Delete All Whispers will remove all whispers permanently</li></ul>','whisper-comment-afm') ?></p>
                            	
          <p><?php _e('Whisper Comment Reloaded creates a new comment type in order to separate normal comments from whispers. If you have decided to remove this plugin and would like to clear obsolete data, you might want to use these tools. However, please remember to backup your database before you proceed.','whisper-comment-afm') ?></p>

          <form method="post" action="">
           <p class="submit">
             <input type="submit" name="wc_convert_whispers" class="button-primary" value="<?php _e('Convert All Whispers','whisper-comment-afm') ?>" />
             <input type="submit" name="wc_delete_whispers" class="button-primary" value="<?php _e('Delete All Whispers','whisper-comment-afm') ?>" />
           </p>
          </form>
        </div><!-- DIV INSIDE end -->
     </div> <!-- DIV POSTBOX end --> 
   </div><!-- DIV META-BOX-SORTABLESS end -->
   <!-- DIV HAS-SIDEBAR start -->
   <div id="post-body-content" class="has-sidebar">
     <!-- DIV HAS-SIDEBAR-CONTENT start -->
     <div class="has-sidebar-content">
       <!-- DIV META-BOX-SORTABLESS start -->
       <div class="meta-box-sortabless">
         <form method="post" action="">
           <?php wp_nonce_field('update-options'); ?>
             <!-- DIV POSTBOX start -->
             <div class="postbox" id="solstice_feedburner_options">
               <h3><?php _e('Whisper Comment Reloaded Settings','whisper-comment-afm'); ?></h3>
               <!-- DIV INSIDE start -->
               <div class="inside">
	         <table class="form-table">
                   <tr valign="top">    
                     <th scope="row"><label for="wc_whisper_on"><?php _e('Activate Whisper Comment Reloaded','whisper-comment-afm') ?></label></th>
                     <td> <fieldset><legend class="hidden">_e('Activate Whisper Comment Reloaded','whisper-comment-afm') ?></legend><label for="whisper_on"><input type="checkbox" <?php if(1 == $options['whisper_on'] ) echo 'checked="checked"'; ?> value="1" id="wc_whisper_on" name="wc_whisper_on"/> <?php _e('Activate','whisper-comment-afm') ?></label></fieldset><br />
                       <span class="setting-description"><?php _e('If this option is changed from yes to no, all whisper statuses will be set to waiting to prevent whispers being exposed.','whisper-comment-afm')?></span>
                      </td>
                   </tr>
                                         
                   <tr valign="top">    
                     <th scope="row"><label for="wc_whisper_to"><?php _e('Send Whisper To','whisper-comment-afm') ?></label></th>
                     <td>
                       <fieldset>
                         <legend class="hidden">_e('Send Whisper To','whisper-comment-afm') ?></legend>
                         <label title="Whisper to Administrator only"><input type="radio" value="1" name="wc_whisper_to" <?php if(1 == $options['whisper_to']) echo 'checked="checked"'; ?>/>  <?php _e('Whisper to Primary Administrator only','whisper-comment-afm') ?></label><br/>
                         <label title="Whisper to a registered user"><input type="radio" value="2" name="wc_whisper_to"<?php if(2 == $options['whisper_to']) echo 'checked="checked"'; ?>/> <?php _e('Whisper to an author of a blog post','whisper-comment-afm') ?></label><br/>
                       </fieldset><br />
                       <span class="setting-description"><?php _e('This plugin supports whisper comments to the administrator and blog authors.','whisper-comment-afm')?></span>
                                                
                     </td>
                   </tr>
                   <tr valign="top">    
                     <th scope="row"><label for="wc_whisper_form_label"><?php _e('Whisper Checkbox Label','whisper-comment-afm') ?></label></th>
                     <td><input type="text" class="normal-text" id="wc_whisper_form_label" name="wc_whisper_form_label" value="<?php echo $options['whisper_form_label'] ?>"/></td>
                   </tr> 
                   <tr valign="top">    
                     <th scope="row">
                       <label for="wc_whisper_denied_msg"><?php _e('Whisper Access Denied Message','whisper-comment-afm') ?></label>
                     </th>
                     <td><input type="text" class="normal-text" id="wc_whisper_denied_msg" name="wc_whisper_denied_msg" value="<?php echo $options['whisper_denied_msg'] ?>"/>
                     </td>
                   </tr>
                   <tr valign="top">    
                     <th scope="row"><label for="wc_whisper_granted_msg"><?php _e('Whisper Access Granted Message','whisper-comment-afm') ?></label></th>
                     <td><input type="text" class="normal-text" id="wc_whisper_granted_msg" name="wc_whisper_granted_msg" value="<?php echo $options['whisper_granted_msg'] ?>"/></td>
                   </tr> 
                   <tr valign="top">    
                     <th scope="row"><label for="wc_whisper_author_msg"><?php _e('Whisper Author Access Message','whisper-comment-afm') ?></label></th>
                     <td><input type="text" class="normal-text" id="wc_whisper_author_msg" name="wc_whisper_author_msg" value="<?php echo $options['whisper_author_msg'] ?>"/></td>
                   </tr>
                   <tr valign="top">    
                     <th scope="row"><label for="wc_whisper_admin_msg"><?php _e('Whisper Administrator Access Message','whisper-comment-afm') ?></label></th>                      <td><input type="text" class="normal-text" id="wc_whisper_admin_msg" name="wc_whisper_admin_msg" value="<?php echo $options['whisper_admin_msg'] ?>"/></td>
                   </tr> 
                 </table>                           
                 <p class="submit">
                   <input type="submit" name="wc_submit" class="button-primary" value="<?php _e('Save Changes','whisper-comment-afm') ?>" />
                 </p>
               </div><!-- DIV INSIDE end -->
             </div> <!-- DIV POSTBOX end -->
           </div><!-- DIV META-BOX-SORTABLESS end -->                    
         </form>
       </div><!-- DIV HAS-SIDEBAR-CONTENT end -->
     </div><!-- DIV HAS-SIDEBAR-CONTENT end -->
   </div><!-- DIV HAS-SIDEBAR end -->
 </div><!-- DIV WRAP end -->
 <?php
  }
	
  if(!function_exists(utf8_trim)) {
    function utf8_trim($str) {
      $len = strlen($str);
      for ($i=strlen($str)-1; $i>=0; $i-=1){
        $hex .= ' '.ord($str[$i]);
	$ch = ord($str[$i]);
	if (($ch & 128)==0) return(substr($str,0,$i));
	if (($ch & 192)==192) return(substr($str,0,$i));
      }
      return($str.$hex);
    }
  }
	
  if(!function_exists(substr_UTF8)) {
    function substr_UTF8($str, $start, $lenth) {
      $len = strlen($str);
      $r = array();
      $n = 0;
      $m = 0;
      for($i = 0; $i < $len; $i++) {
        $x = substr($str, $i, 1);
        $a = base_convert(ord($x), 10, 2);
	$a = substr('00000000'.$a, -8);
	if ($n < $start){
	  if (substr($a, 0, 1) == 0) {
	  } elseif (substr($a, 0, 3) == 110) {
            $i += 1;
	  } elseif (substr($a, 0, 4) == 1110) {
	    $i += 2;
	  }
	  $n++;
	} else {
	  if (substr($a, 0, 1) == 0) {
	    $r[] = substr($str, $i, 1);
	  }elseif (substr($a, 0, 3) == 110) {
	    $r[] = substr($str, $i, 2);
	    $i += 1;
	  }elseif (substr($a, 0, 4) == 1110) {
	    $r[] = substr($str, $i, 3);
	    $i += 2;
	  }else{
            $r[] = '';
          }
          if (++$m >= $lenth){
            break;
          }
        }
      }
      return join($r);
    }
  }

  if (!class_exists('WhisperComment')) {
    @session_start();
    class WhisperComment {
		
    public function __construct() {
      add_action('comment_form', array(&$this, 'comment_form'));	
      add_action('comment_post', array(&$this, 'comment_post'));
      add_filter('comment_text', array(&$this, 'comment_text'));
      add_filter('comment_text_rss', array(&$this, 'comment_text'));
      add_filter('comment_excerpt', array(&$this, 'comment_excerpt'));
      add_filter('comment_edit_pre', array(&$this, 'comment_edit_pre'));
      add_filter('comment_row_actions', array(&$this, 'comment_row_actions'));

      load_plugin_textdomain('whisper-comment-afm',false,basename(dirname(__FILE__)));
    }


    public function comment_form() {
      global $post, $user_ID, $wpdb, $wp_version;
      $options = get_option('whisper_comment_options');
			
      if(1 == $options['whisper_on']) {
        if (1 == $options['whisper_to']) {
	  ?>
	  <p>
            <input type="checkbox" style="width: auto;" value="comment_whisper" id="comment_whisper" name="comment_whisper"/><label for="comment_whisper"><?php echo $options['whisper_form_label']; ?></label>
          </p>
	  <?php
	}else if (2 == $options['whisper_to']) {
	  ?>
	  <p>
            <!-- Sofia added: default checked: this is a whisper comment if the post is not "public" -->
            <input type="checkbox"  <?php $status = $post->post_status; if( ($status) != "publish" ) echo 'checked="checked"'; ?> style="width: auto;" value="comment_whisper" id="comment_whisper" name="comment_whisper"/><?php echo " " . $options['whisper_form_label'] ?>
            <?php $this->display_user_select($options) ?>
          </p>
	  <?php
	}
      }
    }

    public function comment_post($comment_ID) {
      global $wpdb, $user_ID, $_POST;
      $options = get_option('whisper_comment_options');
		
      if (isset($_POST['comment_whisper'])) {
        $whisper_type	= 'whisper';
	$whisper_to	= 1;	
	if(1 == $options['whisper_to']) { // Default to Administrator	
	  $wpdb->query("UPDATE {$wpdb->comments} SET comment_type = '{$whisper_type}' WHERE comment_ID = {$comment_ID}");
	} 
        else if (2 == $options['whisper_to']) { // Whisper to a single user
	if(isset($_POST['comment_target'])) $whisper_type .= ':'. $_POST['comment_target']; 
	  $wpdb->query("UPDATE {$wpdb->comments} SET comment_type = '{$whisper_type}' WHERE comment_ID = {$comment_ID}");
	} else {
	  // TODO
	}
      }
      return $comment_ID;
    }


    public function comment_text($text) {
      global $wpdb, $user_ID, $comment, $wp_version;
			
      $viewer_id	= isset($user_ID) ? $user_ID : -1;
      $options 		= get_option('whisper_comment_options'); 
      $msg_denied	= $options['whisper_denied_msg'];
      $msg_granted	= $options['whisper_granted_msg'];
      $msg_author	= $options['whisper_author_msg'];
      $msg_admin	= $options['whisper_admin_msg'];
			
      // The comment type can be either 'whisper', 'whisper: [id]' or 'whisper: [ids separated by commas]' 
      $is_whisper = preg_match('/^whisper(\:([\d])+(,([\d])+)*)*$/',$comment->comment_type, $match);			
      $is_target	= false;
      if($is_whisper && (1 == $options['whisper_on'])) {
       if('whisper' == $comment->comment_type) {
         $whisper_to	= 1;		
       } else {
	 $targets = explode(',',substr($comment->comment_type,8));
	 foreach($targets as $target) {
           if($target == $viewer_id) {
	     $is_target = true;
	     break;
	   }
	 }
       }
       $whisper_from 	= $comment->user_id;			
       $comment_text 	= $msg_denied;
       $is_Admin 	= appthemes_check_user_role( 'administrator' ) ; 
       $is_Editor 	= appthemes_check_user_role( 'editor' ) ; 
       $is_teachingAssistant = appthemes_check_user_role( 'teachingassistant' ) ; 
       $is_instructor 	= appthemes_check_user_role( 'instructor' ) ; 
       $is_Student 	= appthemes_check_user_role( 'student' ) ;
       $is_Author 	= appthemes_check_user_role( 'author' ) ; 
       if($is_target) {
         $comment_text = $msg_granted . '<br />' . $text;	
       } else if ($whisper_from == $viewer_id) {
         $comment_text = $msg_author . '<br />' . $text;
       } else if (1 == $viewer_id OR $is_teachingAssistant OR $is_instructor OR $is_Admin) {
	 $comment_text = $msg_admin . '<br />' . $text;		
       }
       return $comment_text;
     }
     return $text;		
   }
		
    // Trim UTF8 content and append ' ...' for long content
    public function comment_excerpt($excerpt) {
      $trimed_excerpt = substr_UTF8($excerpt,0,100);
      $has_dots = preg_match('/.*\.{3}$/',$excerpt, $match);
      if(!$has_dots && !strstr($trimed_excerpt,$excerpt)) {
        $trimed_excerpt .= ' ...';	
      }
      return $this->comment_text($trimed_excerpt);
    }

    // Preventing Untargeted Editors or Administrators to see whisper content in edit screen
    public function comment_edit_pre($text) {
      return $this->comment_text($text);
    }

    // Preventing Untargeted Users to edit whisper content
    public function comment_row_actions($actions) {
      global $wpdb, $user_ID, $wp_version, $comment;
      $viewer_id		= isset($user_ID) ? $user_ID : -1;
      $options		= get_option('whisper_comment_options'); 
      // The comment type can be either 'whisper', 'whisper: [id]' or 'whisper: [ids separated by commas]' 
      $is_whisper = preg_match('/^whisper(\:([\d])+(,([\d])+)*)*$/',$comment->comment_type, $match);			
      $is_target	= false;
      if($is_whisper && (1 == $options['whisper_on'])) {
        if('whisper' == $comment->comment_type) {
          $whisper_to	= 1;		
        } else {
          $targets = explode(',',substr($comment->comment_type,8));
          foreach($targets as $target) {
            if($target == $viewer_id) {
	      $is_target = true;
	      break;
            }
    	  }
	}
        $whisper_from = $comment->user_id;
	if($is_target || ($whisper_from == $viewer_id) ||(1 == $viewer_id)) {
						
	} else {
	  $actions = array();	
        }
	return $actions;
      }
      return $actions;		
    }

    function display_user_select($options) {
      global $wpdb;
      $author = get_the_author();
      //added by Sofia 01232013
      $author_login = get_the_author_meta('user_login');
      $comment_id = get_comment_ID();         
      $comment_obj = get_comment( $comment_id ); 
      $comment_parent_id = $comment_obj->comment_parent; 
      $comment_parent_obj = get_comment ($comment_parent_id);
      $parent_comment_author = $comment_parent_obj->comment_author;
      $parent_comment_user_id = $comment_parent_obj->user_id ? $comment_parent_obj->user_id : 0;
      $parent_user_obj  = get_userdata($parent_comment_user_id);
      $parent_display_name = $parent_user_obj->display_name;
      //?>
      <p>
        Send my Whisper to:&nbsp;
        <select name="comment_target"> 
        <!--<option value=""><?php echo attribute_escape(__('Select a user','whisper-comment-afm')); ?></option>--> 
        //<?php
        //$wp_users = $wpdb->get_results("SELECT * FROM {$wpdb->users} WHERE user_login = '".$author."' ORDER BY ID");
        //added by Sofia 01232013 -- changed $author to $author_login
        $wp_users = $wpdb->get_results("SELECT * FROM {$wpdb->users} WHERE user_login = '".$author_login."' ORDER BY ID");
        if ($parent_comment_user_id){
          $parent_user_obj  = get_userdata($parent_comment_user_id);
          $option = '<option value="'.$parent_comment_user_id.'">';
	  $option .= $parent_display_name;
	  $option .= '</option>';
	  echo $option;
        }
	foreach ($wp_users as $userid) {
  	  $user_id       = (int) $userid->ID;
    	  $user_login    = stripslashes($userid->user_login);
	  $display_name  = stripslashes($userid->display_name);
	  if(1 == $user_id)
	    $display_name .= '*';
	    $option = '<option value="'.$user_id.'">';
            $option .= $display_name;
	    $option .= '</option>';
	    echo $option;
	} 
	echo '</select>';
     }
  }
	
  $whisper = new WhisperComment();
}
?>
