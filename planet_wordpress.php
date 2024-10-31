<?php
/*
Plugin Name: Planet Wordpress
Plugin URI: http://anilmakhijani.com/wordpress-dev/planet-wordpress/
Description: Create a planet config.ini file from your Wordpress blogroll.
Author: Anil Makhijani
Version: 0.4
Author URI: http://anilmakhijani.com
*/

add_action('edit_link', 'planet_wordpress_write_config');
add_action('add_link', 'planet_wordpress_write_config');
add_action('deleted_link', 'planet_wordpress_write_config');

function planet_wordpress_write_config() {
	$options = get_option('planet_wordpress_opts');
    $pw_feed_name = stripslashes($options['pw_feed_name']);
    $pw_feed_url = $options['pw_feed_url'];
    $pw_config_loc = $options['pw_config_loc'];
    $pw_feed_owner= stripslashes($options['pw_feed_owner']);
    $pw_feed_owner_email= $options['pw_feed_owner_email'];
    $pw_cache_dir = $options['pw_cache_dir'];
    $pw_output_dir = $options['pw_output_dir'];
    $pw_template_files = $options['pw_template_files'];
    $pw_items_page = $options['pw_items_page'];

	$fh = @fopen($pw_config_loc, 'w');
	if ($fh) {
       global $wpdb;
       fwrite($fh, "[Planet]\n");
	   fwrite($fh, "name = ".$pw_feed_name."\n");
	   fwrite($fh, "link = ".$pw_feed_url."\n");
	   fwrite($fh, "owner_name = ".$pw_feed_owner."\n");
	   fwrite($fh, "owner_email = ".$pw_feed_owner_email."\n");
	   fwrite($fh, "cache_directory = ".$pw_cache_dir."\n");
	   fwrite($fh, "new_feed_items = 10\n");
	   fwrite($fh, "log_level = DEBUG\n");
	   fwrite($fh, "template_files = ".$pw_template_files."\n");
	   fwrite($fh, "output_dir = ".$pw_output_dir."\n");
	   fwrite($fh, "items_per_page = ".$pw_items_page."\n");
	   fwrite($fh, "days_per_page = 7\n");
	   fwrite($fh, "date_format = %B %d, %Y %I:%M %p\n");
	   fwrite($fh, "new_date_format = %B %d, %Y\n");
	   fwrite($fh, "encoding = utf-8\n");
	   fwrite($fh, "[DEFAULT]\n");
	   fwrite($fh, "facewidth = 65\n");
	   fwrite($fh, "faceheight  = 85\n");

	   $links = $wpdb->get_results("SELECT * FROM $wpdb->links");
	   foreach ($links as $link) {
	   	    if ($link->link_rss && $link->link_name) {
	   	    	fwrite($fh, "[".$link->link_rss."]\n");
	   	    	fwrite($fh, "name = ".$link->link_name."\n");
	   	    }
	   }
    }
	@fclose($fh);
}

add_action('admin_menu', 'planet_wordpress_admin_menu');

function planet_wordpress_admin_menu() {
  add_options_page('Planet Wordpress', 'Planet Wordpress', 8, __FILE__, 'planet_wordpress_options');
}

function planet_wordpress_options() {

if ('process' == $_POST['stage']) {
  if (current_user_can('edit_plugins')) {
    $options = Array();
    $options['pw_feed_name'] = stripslashes($_POST['pw_feed_name']);
    $options['pw_feed_url'] = $_POST['pw_feed_url'];
    $options['pw_config_loc'] = $_POST['pw_config_loc'];
    $options['pw_feed_owner'] = stripslashes($_POST['pw_feed_owner']);
    $options['pw_feed_owner_email'] = $_POST['pw_feed_owner_email'];
    $options['pw_cache_dir'] = $_POST['pw_cache_dir'];
    $options['pw_output_dir'] = $_POST['pw_output_dir'];
    $options['pw_template_files'] = $_POST['pw_template_files'];
    $options['pw_items_page'] = $_POST['pw_items_page'];
    update_option('planet_wordpress_opts', $options);
    planet_wordpress_write_config();
  }
}
$options = get_option('planet_wordpress_opts');
$pw_feed_name = stripslashes($options['pw_feed_name']);
$pw_feed_url = $options['pw_feed_url'];
$pw_config_loc = $options['pw_config_loc'];
$pw_feed_owner= stripslashes($options['pw_feed_owner']);
$pw_feed_owner_email = $options['pw_feed_owner_email'];
$pw_cache_dir = $options['pw_cache_dir'];
$pw_template_files = $options['pw_template_files'];
$pw_output_dir = $options['pw_output_dir'];
$pw_items_page = $options['pw_items_page'];

//defaults
if ($pw_cache_dir == "") {
 $pw_cache_dir = "examples/cache";
}

if ($pw_output_dir == "") {
 $pw_output_dir = "examples/output";
}


if ($pw_template_files == "") {
 $pw_template_files = "examples/fancy/index.html.tmpl examples/atom.xml.tmpl examples/rss20.xml.tmpl examples/rss10.xml.tmpl examples/opml.xml.tmpl examples/foafroll.xml.tmpl";
}

$location = get_option('siteurl') . '/wp-admin/options-general.php?page=planet-wordpress/planet_wordpress.php'; // Form Action URI
$no_write_error = "";
$fh = @fopen($pw_config_loc, 'a');
if (!$fh) {
  $no_write_error = "<span class='error'>Cannot open this file for writing</span>";
}
?>
  <div class="wrap">
     <h2>Planet Wordpress Options</h2>
        <form name="pw-form" method="post" action="<?php echo $location ?>">
        <input type="hidden" name="stage" value="process" />
        <table width="100%" cellspacing="2" cellpadding="5" class="form-table">
           <tr valign="top">
              <th scope="row"><?php _e('Name of Feed:') ?></th>
              <td><input name="pw_feed_name" type="text" id="pw_feed_name" value="<?php echo $pw_feed_name; ?>" size="40" /></td>
           </tr>
           <tr valign="top">
              <th scope="row"><?php _e('URL of Planet Site:') ?></th>
              <td><input name="pw_feed_url" type="text" id="pw_feed_url" value="<?php echo $pw_feed_url; ?>" size="40" /></td>
           </tr>
           <tr valign="top">
              <th scope="row"><?php _e('Location to write planet config:') ?></th>
              <td><input name="pw_config_loc" type="text" id="pw_config_loc" value="<?php echo $pw_config_loc; ?>" size="40" /><?php echo $no_write_error; ?></td>
           </tr>
           <tr valign="top">
             <th scope="row"><?php _e('Name of Owner:') ?></th>
             <td><input name="pw_feed_owner" type="text" id="pw_feed_owner" value="<?php echo $pw_feed_owner; ?>" size="40" /></td>
           </tr>
           <tr valign="top">
            <th scope="row"><?php _e('Email of Owner:') ?></th>
            <td><input name="pw_feed_owner_email" type="text" id="pw_feed_owner_email" value="<?php echo $pw_feed_owner_email; ?>" size="40" /></td>
           </tr>
           <tr valign="top">
            <th scope="row"><?php _e('Planet Cache Directory:') ?></th>
            <td><input name="pw_cache_dir" type="text" id="pw_cache_dir" value="<?php echo $pw_cache_dir; ?>" size="40" /><p>(This directory should be relative to location from which planet.py is called.)</p></td>
           </tr>
           <tr valign="top">
            <th scope="row"><?php _e('Planet Output Directory:') ?></th>
            <td><input name="pw_output_dir" type="text" id="pw_output_dir" value="<?php echo $pw_output_dir; ?>" size="40" /><p>(This directory should be relative to location from which planet.py is called.)</p></td>
           </tr> 
           <tr valign="top">
            <th scope="row"><?php _e('Planet Template Files:') ?></th>
            <td><input name="pw_template_files" type="text" id="pw_template_files" value="<?php echo $pw_template_files; ?>" size="40" /><p>(These file paths should be relative to the directory from which planet.py is called.)</p></td>
           </tr>
           <tr valign="top">
            <th scope="row"><?php _e('Items per Page:') ?></th>
            <td><input name="pw_items_page" type="text" id="pw_items_page" value="<?php echo $pw_items_page; ?>" size="40" /></td>
           </tr>
 
        </table>
        <p class="submit">
           <input type="submit" value="Update Options" name="Submit" />
        </p>
        </form>
  </div>
<?php
}
?>
