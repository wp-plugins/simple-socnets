<?php 
/*
Plugin Name: Simple Socnets
Plugin URI: http://wordpress.org/extend/plugins/simple-socnets/
Description: This plugin was built by the Maine WordPress Meetup group to make it really easy to add social network icons to your posts.
Author: Maine WordPress Meetup Group
Version: 1.0.2.1
Author URI: http://www.meetup.com/Southern-Maine-WordPress-Meetup/
*/


function socnet_get_the_links($all = false) {
	/*
		TODO Make this dynamic, allow for front-end updating
	*/
	$output['facebook']['url'] = 'http://www.facebook.com/sharer/sharer.php?u={url}';
	$output['facebook']['name'] = 'Facebook';
	
	$output['twitter']['url'] = 'http://twitter.com/home?status={title}+{url}';
	$output['twitter']['name'] = 'Twitter';
	
	$output['linkedin']['url'] = 'http://www.linkedin.com/shareArticle?mini=true&url={url}&amp;title={title}';
	$output['linkedin']['name'] = 'LinkedIn';
	
	$output['stumbleupon'] = array(
		'url' => 'http://www.stumbleupon.com/submit?url={url}&amp;title={title}',
		'name' => 'Stumbleupon'
	);
	$output['delicious'] = array(
		'url' => 'http://del.icio.us/post?url={url}&title={title}',
		'name' => 'Delicious'
	);
	$output['digg'] = array(
		'url' => 'http://digg.com/submit?url={url}&title={title}',
		'name' => 'Digg'
	);
	$output['reddit'] = array(
		'url' => 'http://reddit.com/submit?url={url}&title={title}',
		'name' => 'Reddit'
	);
	$output['designfloat'] = array(
		'url' => 'http://www.designfloat.com/submit.php?url={url}&title={title}',
		'name' => 'Designfloat'
	);
	
	$settings = get_option('socnet_settings');
	
	if(is_array($output) && !$all) :  foreach($output as $network => $junk) :
		if(!isset($settings['networks'][$network])) {
			unset($output[$network]);
		}
	endforeach; endif;
	
	return $output;
}

function socnet_display_links($post_id = null) {
	if($post_id == null) :
		global $post;
		$post_id = $post->ID;
		if(!$post_id) {
			return false;
		}
	endif;
	$socnet_post = get_post($post_id);
	$title = urlencode($socnet_post->post_title);
	$url = urlencode(get_permalink($post_id));
	
	$socnet_links = socnet_get_the_links();
	
	if(is_array($socnet_links)) :  foreach($socnet_links as $network => $values) :
		$urlstruct = $values['url'];
		$name = $values['name'];
		
		$u = str_replace('{url}', $url, $urlstruct);
		$u = str_replace('{title}', $title, $u);
		
		$icon[] = '<a href="javascript:window.open(\''.$u.'\',\''.$name.'\',\'toolbar=no,location=no,status=no,menubar=no,scrollbars=yes,resizable=yes,width=400,height=300\'); void(0);" id="simplesocnet-icon-'.$network.'"><img src="'.plugins_url( 'icons/'.$network.'.png' , __FILE__ ).'" alt="'.$name.'" /></a>';
	endforeach; endif;
	
	return '<div class="socnet-links">'.implode(' ', $icon).'</div>';
}

add_filter('the_content', 'socnet_filter_the_content');
function socnet_filter_the_content($the_content) {
	$settings = get_option('socnet_settings');
	if($settings['placement'] == 'none') { return $the_content; }
	$display_links = socnet_display_links();
	switch ($settings['placement']) {
		case 'top':
			$output = $display_links.$the_content;
			break;
		case 'bottom':
			$output = $the_content.$display_links;
			break;
		default:
			$output = $the_content;
			break;
	}
	return $output;
}

add_action('admin_menu', 'socnet_register_global_settings_page');

function socnet_register_global_settings_page() {
	add_submenu_page( 'options-general.php', 'Simple Socnets', 'Simple Socnets', 'manage_options', 'simple-socnet-global-settings', 'socnet_global_settings_page' ); 
}

function socnet_global_settings_page() {
	if(isset($_POST['socnet_globals_save']) && $_POST['socnet_globals_save'] == 1) :
		$settings['placement'] = $_POST['simple_socnet_placement'];
		$settings['networks'] = $_POST['networks'];
		update_option('socnet_settings', $settings);
	endif;
	$settings = get_option('socnet_settings');
	
	?>
	<div class="wrap">
	<div id="icon-options-general" class="icon32"><br></div><h2>Simple Socnet Settings</h2>

	<form action="" method="post">

	<h3>Display</h3>
	<p>How would you like your simple socnets to show up?</p>

	<table class="form-table">
	<tbody><tr valign="top">
	<th scope="row">Placement</th>
	<td>
		<?php
		$options = array('top'=>'Top of post body', 'bottom'=>'Bottom of post body', 'none'=>'Not at all (display via template tag)');
		?>
		<select name="simple_socnet_placement" id="simple_socnet_placement">
			<?php if(is_array($options)) :  foreach($options as $key => $val) : ?>
				<option value="<?php echo $key ?>" <?php if($key == $settings['placement']) { echo 'selected="selected"'; } ?>><?php echo $val ?></option>
			<?php endforeach; endif; ?>
		</select>
	</td>
	</tr>
	<th scope="row">Networks</th>
	<td>
		<?php
		$options = socnet_get_the_links(true);
		?>
		<ul>
		<?php if(is_array($options)) :  foreach($options as $network => $meta) : ?>
			<li><input type="checkbox" name="networks[<?php echo $network ?>]" value="1" <?php if($settings['networks'][$network] == 1 || !$settings['networks']) { echo 'checked="checked"'; } ?>/> <?php echo $meta['name']; ?></li>
		<?php endforeach; endif; ?>
		</ul>
	</td>
	</tr>
	</tbody></table>

	<input type="hidden" name="socnet_globals_save" value="1" id="socnet_globals_save" />
	<p class="submit"><input type="submit" name="submit" id="submit" class="button button-primary" value="Save Changes"></p>
	</form>

	</div>
	<?php 

}



