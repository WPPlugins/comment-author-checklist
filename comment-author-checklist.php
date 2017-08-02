<?php
/*
Plugin Name: Comment Author Checklist
Plugin URI: http://sillybean.net/code/comment-author-checklist
Description: Creates a template tag that generates a list of registered users and crosses off the names of those who have commented on a post.
Version: 1.04
Author: Stephanie Leary
Author URI: http://sillybean.net/

Changelog:
1.04 (June 11, 2008)
	Changes to admin page
1.03 (April 4, 2008)
	Switched from text input to checkboxes for category selection
	Options are now removed from the database when the plugin is deactivated
1.02 (April 1, 2008)
	Bug fix AGAIN to get multiple categories working correctly
	Added the * option for all categories
	Added support for attributes in heading HTML
1.01 (March 31, 2008)
	Bug fix to get the "ignore admin user" checkbox working correctly.
1.0 (March 30, 2008)
	First release

Copyright 2008  Stephanie Leary  (email : steph@sillybean.net)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

// Hook for adding admin menus
add_action('admin_menu', 'comment_author_checklist_add_pages');
add_action('admin_head', 'comment_author_checklist_css');

// action function for above hook
function comment_author_checklist_add_pages() {
    // Add a new submenu under Options:
	add_options_page('Comment Checklist', 'Comment Checklist', 8, 'comment-author-checklist', 'comment_checklist_options');
}

// add the script that lets us check all checkboxes at onces 		
wp_enqueue_script( 'admin-forms', '/wp-admin/js/forms.js', false, '20080317' );

// what to do when the plugin is deactivated
function unset_comment_checklist_options() {
	delete_option('comment_author_checklist_categories');
	delete_option('comment_author_checklist_ignore_admin');
	delete_option('comment_author_checklist_sort');
	delete_option('comment_author_checklist_min_level');
	delete_option('comment_author_checklist_list_heading');
}

register_deactivation_hook(__FILE__, 'unset_comment_checklist_options');

function comment_author_checklist_css() {
		echo "<style type=\"text/css\">\n";
	 	echo "#list_categories li { list-style: none; }\n";	
		echo "#list_categories { margin-left: 0; padding-left: 0; }\n";	
		echo "</style>";
}

// displays the options page content
function comment_checklist_options() {
	
	// variables for the field and option names 
		$opt_name = 'comment_author_checklist_sort';
		$hidden_field_name = 'comment_author_checklist_submit_hidden';
	
		// Read in existing option value from database
		$categories = get_option('comment_author_checklist_categories');
		$ignore_admin = get_option('comment_author_checklist_ignore_admin');
		$sort = get_option('comment_author_checklist_sort');
		$min_level = get_option('comment_author_checklist_min_level');
		$heading = get_option('comment_author_checklist_list_heading');
	
		// See if the user has posted us some information
		// If they did, this hidden field will be set to 'Y'
		if( $_POST[ $hidden_field_name ] == 'Y' ) {
			// Read their posted value
			if ($_POST['checkall'] == "all")
				$categories = $_POST['checkall'];
			else
				$categories = implode(",", $_POST['post_category']);
			$ignore_admin = $_POST['comment_author_checklist_ignore_admin'];
			$sort = $_POST['comment_author_checklist_sort'];
			$min_level = $_POST['comment_author_checklist_min_level'];
			$heading = htmlspecialchars(addslashes($_POST['comment_author_checklist_list_heading']));
	
			// Save the posted value in the database
			update_option('comment_author_checklist_categories', $categories);
			update_option('comment_author_checklist_ignore_admin', $ignore_admin);
			update_option('comment_author_checklist_sort', $sort);
			update_option('comment_author_checklist_min_level', $min_level);
			update_option('comment_author_checklist_list_heading', $heading);
	
			// Put an options updated message on the screen
	
	?>
	<div class="updated"><p><strong><?php _e('Options saved.'); ?></strong></p></div>
    
	<?php } // Now display the options editing screen ?>
	
    <div class="wrap">
	<form method="post" id="comment_author_checklist_form">
    <h2><?php _e( 'Comment Author Checklist Options'); ?></h2>
	<input type="hidden" name="<?php echo $hidden_field_name; ?>" value="Y">
    
    <h3><?php _e("Authors to show in the checklist:"); ?></h3>
    <p><label><?php _e("Sort Authors by: "); ?>
    <select name="comment_author_checklist_sort" id="comment_author_checklist_sort">
    <option value="user_id" <?php if ($sort == "user_id") echo "selected=\"selected\""; ?>><?php _e('User ID'); ?></option>
    <option value="display_name" <?php if ($sort == "display_name") echo "selected=\"selected\""; ?>><?php _e('Display Name'); ?></option>
    <option value="user_firstname" <?php if ($sort == "user_firstname") echo "selected=\"selected\""; ?>><?php _e('First Name'); ?></option>
    <option value="user_lastname" <?php if ($sort == "user_lastname") echo "selected=\"selected\""; ?>><?php _e('Last Name'); ?></option>
    <option value="user_login" <?php if ($sort == "user_login") echo "selected=\"selected\""; ?>><?php _e('Login'); ?></option>
      </select>
    </label></p>
    
    <p><label><?php _e("Minimum Author Level to Display: "); ?>
      <select name="comment_author_checklist_min_level" id="comment_author_checklist_min_level">
      <?php 
      //wp_dropdown_roles();  // wp_dropdown_roles() doesn't allow for an option to be selected, so we'll reinvent the wheel
      /*
      Roles to Levels conversion:
      0: Subscriber
      1: Contributor
      2-4: Author
      5-7: Editor
      8-10: Admin
      */  
      ?>
      <option value="8" <?php if ($min_level == "8") echo "selected=\"selected\""; ?>><?php _e('Administrator'); ?></option>
        <option value="5" <?php if ($min_level == "5") echo "selected=\"selected\""; ?>><?php _e('Editor'); ?></option>
        <option value="2" <?php if ($min_level == "2") echo "selected=\"selected\""; ?>><?php _e('Author'); ?></option>
        <option value="1" <?php if ($min_level == "1") echo "selected=\"selected\""; ?>><?php _e('Contributor'); ?></option>
        <option value="0" <?php if ($min_level == "0") echo "selected=\"selected\""; ?>><?php _e('Subscriber'); ?></option> 
      </select>
      </label>
    </p>
    <p><label><input type="checkbox" name="comment_author_checklist_ignore_admin" id="comment_author_checklist_ignore_admin" value="yes" <?php if ($ignore_admin == "yes") echo "checked=\"checked\""; ?> /> <?php _e('Ignore Admin User'); ?></label></p>
    <h3><?php _e("Show the checklist in the following categories:"); ?></h3>
<p>
<?php
if (get_option('comment_author_checklist_categories') == "all") 
	$all = true;
?>
<label><input type="checkbox" name="checkall" value="all" <?php if ($all) echo "checked=\"checked\""; ?> />  <strong><?php _e("All Categories"); ?></strong></label>
</p>
<ul id="list_categories">
<?php 
$args = array( 'exclude'          => '',
			  'hide_empty'       => false, 
			  'hierarchical'     => true, 
			  'echo'             => false,
			  'name'             => true,
			  'class'            => "",
			  'multiple'         => true,
			  'selected'         => get_option('comment_author_checklist_categories'),
			  'size'             => 1 );
$list = print_category_checklist(0, 0, true, $args);
// this is a clumsy way of making sure the saved options are shown as selected
if ($all)
	$arrs = get_all_category_ids(); 
else
	$arrs = explode(",", get_option('comment_author_checklist_categories'));
foreach ($arrs as $arr) {
	$search = 'value="'.$arr.'"';
	$replace = $search.' checked="checked"';
	$list = str_replace($search, $replace, $list);
}
echo $list;
?></ul>
<h3 style="clear: both;"><?php _e("Display Options: "); ?></h3>
   <p>
  <label><?php _e("Heading for the Checklist: "); ?>   <input type="text" name="comment_author_checklist_list_heading" id="comment_author_checklist_list_heading" value="<?php echo(stripslashes(stripslashes($heading))); ?>" />  </label>
  <br />
  <small><?php _e("Include HTML heading tags, if desired (example: &lt;h2&gt;Checklist&lt;/h2&gt;)"); ?> </small></p>
	<p class="submit">
	<input type="submit" name="submit" value="<?php _e('Update Options'); ?>" />
	</p>
	<p><?php _e("Once you have saved these options, add <code>&lt;?php if (function_exists(show_comment_author_checklist)) { show_comment_author_checklist(); } ?&gt;</code> to your post template where you would like the checklist to appear."); ?></p>
	</form>
	</div>
	
<?php } // end function comment_checklist_options() 

// Prints the hierarchical category list
// A stripped-down clone of wp_category_checklist from WP includes/template.php, with the extra parameter for excluded cats and a return instead of echo
function print_category_checklist( $post_id = 0, $descendants_and_self = 0, $selected_cats = true,  $catargs) {
	$walker = new Walker_Category_Checklist;
	$descendants_and_self = (int) $descendants_and_self;

	$args = array();
	
	$args['selected_cats'] = array();
	$args['popular_cats'] = get_terms( 'category', array( 'fields' => 'ids', 'orderby' => 'count', 'order' => 'DESC', 'number' => 10, 'hierarchical' => false ) );
	$categories = get_categories($catargs);

	$args = array($categories, 0, $args);
	$output = call_user_func_array(array(&$walker, 'walk'), $args);

	return $output;
}

// prints the checklist (new template tag)
function show_comment_author_checklist() {
	global $post, $wpdb;
	// Read in existing option value from database
	$show_in_categories = get_option('comment_author_checklist_categories');
	$ignore_admin = get_option('comment_author_checklist_ignore_admin');
	$sort_order = get_option('comment_author_checklist_sort');
	$min_level = get_option('comment_author_checklist_min_level');
	$heading = get_option('comment_author_checklist_list_heading');
	
	$cats = array();
	$cat_list = explode(',', trim($show_in_categories));
	$post_categories = get_the_category();
	foreach ($post_categories as $cat) 
		$cats[] = $cat->term_id;
	
	$show_in = array_intersect($cat_list, $cats);
	
	if (!empty($show_in)) {
	
		$list = '';
		$commenters = array();
		$commented = $wpdb->get_results("SELECT DISTINCT user_id FROM $wpdb->comments WHERE comment_post_ID = '$post->ID' AND comment_approved ='1' ORDER BY user_id");
		//the following changes the result object to an array
		foreach ($commented as $commenter) 
			$commenters[] = $commenter->user_id;
		
		$users = $wpdb->get_results("SELECT ID, user_login, display_name FROM $wpdb->users ORDER BY $sort_order"); // query users
		foreach ($users as $user) : // start users'loop
		
			$user_level = $wpdb->get_var("SELECT meta_value FROM $wpdb->usermeta WHERE user_id='$user->ID' and meta_key='wp_user_level' LIMIT 1");
			$admin_passed = true;
			if (($user->user_login == 'admin') && ($ignore_admin))   //ignores admin user
				$admin_passed = false;
				if (($user_level >= $min_level) && ($admin_passed)) :
		
					// strike the author of the post off the list; they don't have to comment
					// if the author is in the array of commenters, strike them too
					if (($user->ID == get_the_author_ID()) || (in_array($user->ID, $commenters)))
						$list .= "\n<li><del>".$user->display_name."</del></li>"; 
					else
						$list .= "\n<li>".$user->display_name."</li>";	// else they haven't commented yet
			
				endif; // end user_level test
		endforeach; // end of the users' profile 'loop'
	echo stripslashes(stripslashes(htmlspecialchars_decode($heading)))."\n<ul class=\"comment-author-checklist\">\n".$list."\n</ul>";
	} // end if (!empty($show_in))
} 
?>