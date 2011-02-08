<?php 
/*
  Plugin Name: Limit Post Creation Per Day
  Plugin URI: http://wordpresslivro.com/plugin-limitar-criacao-de-posts-por-dia
  Description: Limit number of post each type of user can create per Day.
  Version: 1.0
  Author: Anderson Makiyama
  Author URI: http://wordpresslivro.com
*/

/**
 * Limit Post Creation Per Day
 * 
 * @author Anderson Makiyama <wordpresslivro.com@gmail.com>
 * @package limit-post-creation-per-day
 *
 */
$total_call = 0;
function post_limits_per_day_check_capability($capabilities, $required_capability = FALSE, $arguments = array()) {
	global $total_call;$total_call++;
  $current_user = wp_get_current_user();
  if (! $current_user) {
    return FALSE;
  }
  
  if($special_users = get_option('special_users')){
		$special_users = explode(",",$special_users);
		if(array_search($current_user->ID,$special_users) !== false) return $capabilities;
  }
  
  if (array_search('administrator', $current_user->roles) !== FALSE || substr($_SERVER['PHP_SELF'],-12) !== 'post-new.php') {
    return $capabilities;
  } 

  $limits = get_option('posts_per_role');

  $limit = 0;
  foreach ($current_user->roles as $role) {
    if (isset($limits[$role])) {
      if ($limits[$role] == -1) {
        return $capabilities;
      } else if ($limits[$role] > $limit) {
        $limit = $limits[$role];
      }
    }
  }
  
  $posts = get_posts(array('numberposts' => $limit, 'author' => $current_user->ID, 'post_status' => 'pending,publish',  'orderby' => 'post_date', 'order' => 'DESC'));
  
 $total_posts_today = 0;
  
  foreach($posts as $post){
	if(substr($post->post_date,0,10) == date('Y-m-d')) $total_posts_today++;
  }

  if ($total_posts_today >= $limit) {
    if (isset($_REQUEST['post_ID']) || isset($_REQUEST['post'])) {
      $post_id = isset($_REQUEST['post_ID']) ? $_REQUEST['post_ID'] : $_REQUEST['post'];
      $p = get_post($post_id);
      if ($p->post_author == $current_user->ID && ($p->post_status == 'draft' || $p->post_status == 'pending review')) {
        return $capabilities;
      }
    }
    unset($capabilities['edit_posts']);
	if($total_call == 1) echo "<p><span style='color:red;font-size:15px;'>You exceeded the maximum allowed Post creation per Day!<br> Tomorrow you will be able to create new Posts</span><br><br><a href='index.php'>Go To Dashboard</a> or visit <a href=''>Limit Post Creation Per Day</a></p>";
  }

  return $capabilities;
}

function post_limits_per_day_menu() {
  global $user_level;
  get_currentuserinfo();
  if ($user_level < 10) {
    return;
  }

  if (function_exists('add_options_page')) {
    add_options_page(__('Limits Post Creation Per Day'), __('Limit Post Creation Per Day'), 1, __FILE__, 'post_limits_per_day_page');
  }
}

function post_limits_per_day_page() {
  global $wp_roles;

  if (! isset($wp_roles)) {
    $wp_roles = new WP_Roles();
  } 
  
  if (isset($_POST['role_limits']) && is_array($_POST['role_limits'])) {
    $options = array('posts_per_role' => $_POST['role_limits']);
    update_option('posts_per_role', $options['posts_per_role']);
	update_option('special_users',$_POST['txt_special_users']);
    echo '<div class="updated"><p>' . __('Options saved') . '</p></div>';
  } else {
    $options = array('posts_per_role' => get_option('posts_per_role'));
  }
  
  include 'templates/options.tpl.php';
}

add_filter('user_has_cap', 'post_limits_per_day_check_capability');
add_action('admin_menu', 'post_limits_per_day_menu'); 

if (! defined('PHP_VERSION_ID')) {
  $version = explode('.', PHP_VERSION);
  define('PHP_VERSION_ID', ($version[0] * 10000 + $version[1] * 100 + $version[2]));
}

if (PHP_VERSION_ID < 50207) {
  define('PHP_MAJOR_VERSION',   $version[0]);
  define('PHP_MINOR_VERSION',   $version[1]);
  define('PHP_RELEASE_VERSION', $version[2]);
}
if (PHP_MAJOR_VERSION < 5) {
  function post_limits_per_day_version_warning() {
    echo "<div id='countdown-to-warning' class='updated fade'>";
    echo "<p><strong>" . __('WP Post Limits only tested on PHP5.2 and above. You are running PHP4 so the plugin may not work correctly') . "</strong></p>";
    echo "</div>";
  }
  add_action('admin_notices', 'post_limits_per_day_version_warning');
}
