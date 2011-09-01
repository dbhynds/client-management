<?php
/*
Plugin Name: Ashram Creative Client Manager
Description: Allows web designers and developers to interact with clients as a site is being developed behind the security of a login. Administrators can prevent users from viewing the site unless they first log in using a username and password. Once logged in, clients can browse the site and comment on individual pages, allowing them to provide continuous feedback on the design and development process. This functionality works independently of the regular WordPress comment feature.
Version: 0.1
Author: Davo Hynds
Author URI: http://ashramcreative.com
License: GPL2
*/

/*  Copyright 2011 Davo Hynds  (email : davo@ashramcreative.com)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 2, as 
    published by the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

$accm_password_protect = 'accm_password_protect';
$accm_client_comments = 'accm_client_comments';
$accm_password_protect_val = get_option( $accm_password_protect );
$accm_client_comments_val = get_option( $accm_client_comments );

add_action('admin_menu', 'accm_menu');
if ($accm_client_comments_val == true) {
	add_action('wp_footer', 'accm_footer' );
	add_action('wp_print_styles', 'accm_stylesheet');
	add_action('init', 'accm_js');
}
if ($accm_password_protect_val == true) add_action('template_redirect', 'accm_protected_site' );
// if ($accm_password_protect_val == true) add_action('get_header', 'accm_protected_site' );

function accm_stylesheet() {
	$myStyleUrl = WP_PLUGIN_URL . '/client-comment/style.css';
	$myStyleFile = WP_PLUGIN_DIR . '/client-comment/style.css';
	if ( file_exists($myStyleFile) ) {
		wp_register_style('Client Comment Form', $myStyleUrl);
		wp_enqueue_style( 'Client Comment Form');
	}
}
function accm_js() {
	$myJsUrl = WP_PLUGIN_URL . '/client-comment/scripts.js';
	$myJsFile = WP_PLUGIN_DIR . '/client-comment/scripts.js';
	if ( file_exists($myJsFile) ) {
		wp_deregister_script( 'accm' );
		wp_register_script( 'accm', $myJsUrl);
		wp_enqueue_script( 'accm' );
	}
}    

function accm_footer() {
	global $blog_id;
	$myStyleUrl = WP_PLUGIN_URL . '/client-comment/style.css';
	$myStyleFile = WP_PLUGIN_DIR . '/client-comment/style.css';
	if ( file_exists($myStyleFile) ) {
		wp_register_style('Client Comment Form', $myStyleUrl);
		wp_enqueue_style( 'Client Comment Form');
	}
	?>
    <div id="ac-client-comment">
    	<div id="ac-main" class="ac-main">
        	<div class="ac-comment-form">
            	<form method="post" action="<?php echo substr(strstr(get_permalink(), '.com/'),4); ?>">
                	<textarea name="accm_comment" rows="5"></textarea>
                    <input class="ac-right" type="submit" name="accm_submit" value="POST" />
                    <!--<input class="ac-right" type="submit" name="accm_get_emails" value="EMAILS" />
                    <input class="ac-right" type="submit" name="accm_clear_comments" value="CLEAR" />-->
                </form>
            </div>
            <div class="ac-comment-list">
            	<h2><?php _e("Comments", 'accm' ); ?></h2>
		<?php
            if (isset($_POST['accm_clear_comments'])) {
				delete_post_meta(get_the_ID(), '_client_comment');
			}
            if (isset($_POST['accm_delete_comment'])) {
				echo "Delete: ".$_POST['accm_comment_x'];
				// delete_post_meta(get_the_ID(), '_client_comment', $_POST['accm_comment_x']);
			}
			$current_user = wp_get_current_user();
			$accm_comments = get_post_meta(get_the_ID(), '_client_comment');
            if (isset($_POST['accm_submit'])) {
				$accm_comment = array (
					"user" => $current_user->ID,
					"comment" => $_POST['accm_comment']
				);
				add_post_meta(get_the_ID(), '_client_comment', $accm_comment);
				if (count($accm_comments) > 0) {
					$accm_emails = array();
					foreach($accm_comments as $accm_comment) {
						$accm_user_data = get_userdata($accm_comment['user']);
						$accm_email = $accm_user_data->user_email;
						if (!in_array($accm_email,$accm_emails)){
							array_push($accm_emails, $accm_email);
						}
					}
					$accm_title = 'New Comment on the development of '.get_bloginfo('name');
					$accm_message = "There is a new comment on the development of ".get_bloginfo('name').".\r\r";
					$accm_message .= "Comment by ".$accm_comment['user'].":\r";
					$accm_message .= $accm_comment['comment'].".\r\r";
					$accm_message .= "View the comment on \"".get_the_title()."\":\r";
					$accm_message .= get_permalink();
					wp_mail($accm_emails,$accm_title,$accm_message);
				}
            }
			$accm_comments = get_post_meta(get_the_ID(), '_client_comment');
            if (isset($_POST['accm_get_emails'])) {
				if (count($accm_comments) > 0) {
					$accm_emails = array();
					foreach($accm_comments as $accm_comment) {
						$accm_user_data = get_userdata($accm_comment['user']);
						$accm_email = $accm_user_data->user_email;
						if (!in_array($accm_email,$accm_emails)){
							array_push($accm_emails, $accm_email);
						}
					}
					print_r($accm_emails);
					/*$accm_title = 'Comment on the development of '.get_bloginfo('name');
					$accm_message = "There is a new comment on the development of ".get_bloginfo('name').".\r\r";
					$accm_message .= "View the comment on \"".get_the_title()."\":\r";
					$accm_message .= get_permalink();
					wp_mail($accm_emails,$accm_title,$accm_message);*/
				}
            }
			if (count($accm_comments) > 0) {
				$accm_count = 0;
				foreach($accm_comments as $accm_comment) {
					$accm_user_data = get_userdata($accm_comment['user']);
					?>
                    <div class="ac-overflow">
                    	<div class="ac-accm_author left">
                        	<?php echo $accm_user_data->display_name; ?>
                        </div>
                        <div class="ac-delete-comment right">
							<?php /*?><?php if($accm_comment['user'] == $current_user->ID) : ?>
                            <form method="post" action="">
                                <input type="hidden" name="accm_comment_x" value="<?php echo $accm_comment['comment']; ?>" />
                                <input class="ac-right" type="submit" name="accm_delete_comment" value="X" />
                            </form>
                            <?php endif; ?><?php */?>
                        </div>
                        <div class="ac-accm_comment">
                        	<p><?php echo $accm_comment['comment']; ?></p>
                        </div>
                    </div>
                    <?php
					$accm_count++;
				}
			} else {
				_e('No comments yet.');
			}
		?>
            </div>
		</div>
    </div>
    <div id="showhide">
        <div class="ac-hide" id="ac-hide" onClick="ac_comments_hide()"><?php _e("Hide", 'accm' ); ?></div>
        <div class="ac-show" id="ac-show" onClick="ac_comments_show()" style="display: none;"><?php _e("Comment", 'accm' ); ?></div>
    </div>
    <?
}
function accm_menu() {
	add_options_page('Ashram Creative Client Management', 'Client Management', 'manage_options', 'accm', 'accm_options');
}
function accm_options() {
	global $accm_password_protect, $accm_client_comments, $accm_client_comments_val, $accm_password_protect_val;
	if (!current_user_can('manage_options'))  {
		wp_die( __('You do not have sufficient permissions to access this page.') );
	}
	echo '<div class="ac-wrap">';
	?>
    <h2>Ashram Creative <?php _e('Client Management', 'accm' ); ?></h2>
    <hr />
    <?php
	
	
    if( isset($_POST[ 'accm_hidden' ]) && $_POST[ 'accm_hidden' ] == true ) {
        $accm_password_protect_val = $_POST[ $accm_password_protect ];
        $accm_client_comments_val = $_POST[ $accm_client_comments ];
        update_option( $accm_password_protect, $accm_password_protect_val );
        update_option( $accm_client_comments, $accm_client_comments_val );
		?>
		<div class="ac-updated"><p><strong><?php _e('Settings saved.', 'accm' ); ?></strong><br /></p></div>
		<?php
	}
	?>
    <form name="form1" method="post" action="">
        <input type="hidden" name="accm_hidden" value="true">
        <p><input type="checkbox" name="<?php echo $accm_password_protect; ?>" value="true" <?php if($accm_password_protect_val == true) echo 'checked="checked"'; ?> /> <label for="<?php echo $accm_password_protect; ?>"><strong><?php _e("Password protect site", 'accm' ); ?></strong></label><br />
		<?php _e("Require all traffic to this site to log in using their WordPress username and password for this site.", 'accm' ); ?></p>
        <p><p><input type="checkbox" name="<?php echo $accm_client_comments; ?>" value="true" <?php if($accm_client_comments_val == true) echo 'checked="checked"'; ?> /> <label for="<?php echo $accm_client_comments; ?>"><strong><?php _e("Enable client comments", 'accm' ); ?></strong></label><br />
		<?php _e("Creates a comment field at the bottom of each page (unrelated to post/page comments in the template) which allows clients, designers and developers do have an online discussion about the page.", 'accm' ); ?></p>
        <p class="ac-submit">
        <input type="submit" name="Submit" class="ac-button-primary" value="<?php esc_attr_e('Save Changes') ?>" />
        </p>
    </form>
	<?php
    echo '</div>';
}
function accm_protected_site() {
	if ( !is_user_logged_in() ) { ?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo( 'charset' ); ?>" />
    <title>Login Required</title>
    <style type="text/css">
		* {
			margin: 0;
			color: #777;
			font-family: Tahoma, Tahoma, Geneva, sans-serif;
		}
		div {
			margin: 10% auto;
			padding: 20px;
			border: 1px solid #ccc;
			border-radius: 20px;
			background: #eee;
			width: 400px;
		}
		h1 {
			font-weight: normal;
			color: #444;
			margin-bottom: .5em;
		}
		p {
			font-size: 14px;
		}
		a,
		a:link,
		a:visited,
		a:hover,
		a:active {
			color: #444;
		}
	</style>
</head>
<body>
    <div>
        <h1>Login Required</h1>
        <p><strong>Sorry!</strong> You must <a href="<?php bloginfo('url'); ?>/wp-login.php?redirect_to=<?php
			$path = get_bloginfo('url').$_SERVER['PHP_SELF'];
			echo $path;
		?>&reauth=1">log in</a> to view this page.</p>
    </div>
</body>
</html><?php die;
	}
}

?>