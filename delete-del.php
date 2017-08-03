<?php
/*
Plugin Name: Delete Del
Plugin URI: http://wordpress.org/extend/plugins/delete-del/
Version: 0.9.5
Description: Delete del elements from RSS 1.0 feeds.
Author: IKEDA Yuriko
Author URI: http://www.yuriko.net/cat/wordpress/
Text Domain: delete_del
Domain Path: lang/
*/

/*  Copyright (c) 20080-2009 yuriko

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; version 2 of the License.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
*/

if ( !defined('WP_INSTALLING') || !WP_INSTALLING ) :
/* ==================================================
 *   DeleteDel class
   ================================================== */

class DeleteDel {
	var $plugin_dir;
	var $textdomain = 'delete_del';
	var $textdomain_loaded = false;
	var $wp_multibyte_patch;

function DeleteDel() {
	$this->plugin_dir = basename(dirname(__FILE__));
	add_filter('plugins_loaded', array($this, 'load_textdomain'));
	add_filter('plugins_loaded', array($this, 'change_excerpt'));
	
	add_filter('the_content_feed', array($this, 'delete_html_comments'), 9, 3);
	add_filter('the_content_feed', array($this, 'content_delete_del'), 9, 3);
	add_filter('the_content_rss', array($this, 'delete_html_comments'), 9);
	add_filter('the_content_rss', array($this, 'content_delete_del'), 9);
	add_filter('the_content', array($this, 'delete_html_comments'), 9);
}

/* ==================================================
 * @param	none
 * @return	none
 * @since	0.9.5
 */
//public 
function load_textdomain() {
	if (! $this->textdomain_loaded) {
		$lang_dir = $this->plugin_dir . '/lang';
		$plugin_path = defined('PLUGINDIR') ? PLUGINDIR . '/' : 'wp-content/plugins/';
		load_plugin_textdomain($this->textdomain, $plugin_path . $lang_dir, $lang_dir);
		$this->textdomain_loaded = true;
	}
}

/* ==================================================
 * @param	none
 * @return	none
 * @since	0.9.5
 */
//public 
function change_excerpt() {
	global $wpmp;
	if (class_exists('multibyte_patch') && isset($wpmp) && false !== $wpmp->conf['patch_wp_trim_excerpt']) {
		$this->wp_multibyte_patch = $wpmp;
		remove_filter('get_the_excerpt', array($this->wp_multibyte_patch, 'wp_trim_excerpt'));
		remove_filter('get_comment_excerpt', array($this->wp_multibyte_patch, 'get_comment_excerpt'));
		add_filter('get_the_excerpt', array($this, 'mb_excerpt_deleted_del'));
		add_filter('get_comment_excerpt', array($this, 'mb_excerpt_deleted_del'));
	} else {
		remove_filter('get_the_excerpt', 'wp_trim_excerpt');
		add_filter('get_the_excerpt', array($this, 'excerpt_deleted_del'));
	}
}

/* ==================================================
 * @param	string $output
 * @return	string $output
 * @since	0.9.5
 */
//public 
function delete_html_comments($output, $feed_type = 'rss') {
	$output = preg_replace('#< !\[endif\]#', '<![endif]', $output);
	$output = preg_replace('#<!--[^[].*?-->\\s*#s', '', $output);
	return $output;
}

/* ==================================================
 * @param	string $output
 * @return	string $output
 * @since	0.9.0
 */
//public 
function content_delete_del($output, $feed_type = 'rss') {
	if ($feed_type == 'rss') {
		$output = preg_replace('#<del[^>]*>.*?</del>\\s*#s', '', $output);
	}
	return $output;
}

/* ==================================================
 * @param	string $text
 * @return	string $text
 * @since	0.9.0
 * Based on wp_trim_excerpt() at wp-includes/formatting.php of WP 2.9
 */
//public 
function excerpt_deleted_del($text) {
	$raw_excerpt = $text;
	if ( '' == $text ) {
		$text = get_the_content('');
		if (function_exists('strip_shortcodes')) {
			$text = strip_shortcodes( $text );
		}
		$text = apply_filters('the_content', $text);
		$text = $this->delete_html_comments($text);
		$text = $this->content_delete_del($text);
		$text = str_replace(']]>', ']]&gt;', $text);
		$text = strip_tags($text);
		$excerpt_length = apply_filters('excerpt_length', 55);
		$excerpt_more = apply_filters('excerpt_more', ' ' . '[...]');
		$words = explode(' ', $text, $excerpt_length + 1);
		if (count($words) > $excerpt_length) {
			array_pop($words);
			$text = implode(' ', $words);
			$text .= $excerpt_more;
		}
	}
	return apply_filters('wp_trim_excerpt', $text, $raw_excerpt);
}

/* ==================================================
 * @param	string $text
 * @return	string $text
 * @since	0.9.5
 * Based on wp_trim_excerpt() at wp-multibyte-patch.php of WP-Multibyte-Patch plugin 1.1.6
 */
//public 
function mb_excerpt_deleted_del($text) {
	$raw_excerpt = $text;
	$blog_encoding = $this->wp_multibyte_patch->blog_encoding;
	if ( '' == $text ) {
		$text = get_the_content('');
		if (function_exists('strip_shortcodes')) {
			$text = strip_shortcodes( $text );
		}
		$text = apply_filters('the_content', $text);
		$text = $this->delete_html_comments($text);
		$text = $this->content_delete_del($text);
		$text = str_replace(']]>', ']]&gt;', $text);
		$text = strip_tags($text);
		$excerpt_length = apply_filters('excerpt_length', $this->wp_multibyte_patch->conf['excerpt_length']);
		$excerpt_mblength = $this->wp_multibyte_patch->conf['excerpt_mblength'];
		$excerpt_more = apply_filters('excerpt_more', ' ' . '[...]');
		if ($this->wp_multibyte_patch->is_almost_ascii($text, $blog_encoding)) {
			$words = explode(' ', $text, $excerpt_length + 1);
			if ( count($words) > $excerpt_length ) {
				array_pop($words);
				$text = implode(' ', $words);
				$text .= $excerpt_more;
			}
		} elseif (mb_strlen($text, $blog_encoding) > $excerpt_mblength) {
			$text = mb_substr($text, 0, $excerpt_mblength, $blog_encoding) . $excerpt_more;
		}
	}
	return apply_filters('wp_trim_excerpt', $text, $raw_excerpt);
}

/* ==================================================
 * @param	string $text
 * @return	string $text
 * @since	0.9.5
 * Based on get_comment_excerpt() at wp-includes/comment-template.php of WP 2.9
 */
//public 
function get_comment_excerpt($excerpt) {
	global $comment;
	$comment_text = $comment->comment_content;
	$comment_text = $this->delete_html_comments($comment_text);
	$comment_text = $this->content_delete_del($comment_text);
	$comment_text = strip_tags($comment_text);
	$blah = explode(' ', $comment_text);
	if (count($blah) > 20) {
		$k = 20;
		$use_dotdotdot = 1;
	} else {
		$k = count($blah);
		$use_dotdotdot = 0;
	}
	$excerpt = '';
	for ($i=0; $i<$k; $i++) {
		$excerpt .= $blah[$i] . ' ';
	}
	$excerpt .= ($use_dotdotdot) ? '...' : '';
	return $excerpt;
}

/* ==================================================
 * @param	string $text
 * @return	string $text
 * @since	0.9.5
 * Based on get_comment_excerpt() at wp-multibyte-patch.php of WP-Multibyte-Patch plugin 1.1.6
 */
//public 
function mb_get_comment_excerpt($excerpt) {
	global $comment;
	$blog_encoding = $this->wp_multibyte_patch->blog_encoding;
	$excerpt_length = $this->wp_multibyte_patch->conf['comment_excerpt_length'];
	$excerpt_mblength = $this->wp_multibyte_patch->conf['comment_excerpt_mblength'];

	$comment_text = $comment->comment_content;
	$comment_text = $this->delete_html_comments($comment_text);
	$comment_text = $this->content_delete_del($comment_text);
	$comment_text = strip_tags($comment_text);

	if ($this->wp_multibyte_patch->is_almost_ascii($comment_text, $blog_encoding)) {
		$words = explode(' ', $comment_text, $excerpt_length + 1);

		if(count($words) > $excerpt_length) {
			array_pop($words);
			array_push($words, '[...]');
			$comment_text = implode(' ', $words);
		}
	} elseif (mb_strlen($comment_text, $blog_encoding) > $excerpt_mblength) {
		$comment_text = mb_substr($comment_text, 0, $excerpt_mblength, $blog_encoding) . ' [...]';
	}

	return $excerpt;
}

// ===== End of class ====================
}

$DeleteDel = new DeleteDel();
endif;
?>