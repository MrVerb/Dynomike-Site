<?php
/*
Plugin Name: Featured Category Widget
Plugin URI: http://wasistlos.waldemarstoffel.com/plugins-fur-wordpress/featured-category-widget
Description: The Featured Category Widget does, what the name says; it creates a widget, which you can drag to your sidebar and it will show excerpts of the posts of the category you chose. Display one or more random posts or the first five of the category in order.
Version: 2.5
Author: Stefan Crämer
Author URI: http://www.stefan-craemer.com
License: GPL3
Text Domain: category-feature
Domain Path: /languages
*/

/*  Copyright 2012 -2016 Stefan Crämer (email : support@atelier-fuenf.de)

    This program is free software: you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation, either version 3 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program.  If not, see <http://www.gnu.org/licenses/>.

*/


/* Stop direct call */

defined('ABSPATH') OR exit;

if (!defined('FCW_PATH')) define( 'FCW_PATH', plugin_dir_path(__FILE__) );
if (!defined('FCW_BASE')) define( 'FCW_BASE', plugin_basename(__FILE__) );

# loading the framework
if (!class_exists('A5_Image')) require_once FCW_PATH.'class-lib/A5_ImageClass.php';
if (!class_exists('A5_Excerpt')) require_once FCW_PATH.'class-lib/A5_ExcerptClass.php';
if (!class_exists('A5_Widget')) require_once FCW_PATH.'class-lib/A5_WidgetClass.php';
if (!class_exists('A5_FormField')) require_once FCW_PATH.'class-lib/A5_FormFieldClass.php';
if (!class_exists('A5_OptionPage')) require_once FCW_PATH.'class-lib/A5_OptionPageClass.php';
if (!class_exists('A5_DynamicFiles')) require_once FCW_PATH.'class-lib/A5_DynamicFileClass.php';

#loading plugin specific classes
if (!class_exists('CF_Admin')) require_once FCW_PATH.'class-lib/CF_AdminClass.php';
if (!class_exists('CF_DynamicCSS')) require_once FCW_PATH.'class-lib/CF_DynamicCSSClass.php';
if (!class_exists('Featured_Category_Widget')) require_once FCW_PATH.'class-lib/CF_WidgetClass.php';

class CategoryFeature {
	
	static $options;
	
	function __construct() {
		
		load_plugin_textdomain('category-feature', false , basename(dirname(__FILE__)).'/languages');
		
		add_action('save_post', array($this, 'flush_widget_cache'));
		add_action('deleted_post', array($this, 'flush_widget_cache'));
		add_action('switch_theme', array($this, 'flush_widget_cache'));
		
		add_action('admin_enqueue_scripts', array($this, 'enqueue_scripts'));
		
		add_filter('plugin_row_meta', array($this, 'register_links'), 10, 2);	
		add_filter( 'plugin_action_links', array($this, 'plugin_action_links'), 10, 2 );
				
		register_activation_hook(  __FILE__, array($this, '_install') );
		register_deactivation_hook(  __FILE__, array($this, '_uninstall') );
		
		self::$options = get_option('cf_options');
		
		if (@!array_key_exists('flushed', self::$options)) add_action('init', array ($this, 'update_rewrite_rules'));
		
		if (true == WP_DEBUG):
		
			add_action('wp_before_admin_bar_render', array($this, 'admin_bar_menu'));
		
		endif;
		
		$CF_DynamicCSS = new CF_DynamicCSS;
		$CF_Admin = new CF_Admin;
		
	}
	
	/* attach JavaScript file for textarea resizing */
	function enqueue_scripts($hook) {
		
		if ($hook != 'settings_page_featured-category-settings' && $hook != 'widgets.php' && $hook != 'post.php') return;
		
		$min = (SCRIPT_DEBUG == false) ? '.min.' : '.';
		
		wp_register_script('ta-expander-script', plugins_url('ta-expander'.$min.'js', __FILE__), array('jquery'), '3.0', true);
		wp_enqueue_script('ta-expander-script');
		
	}
	
	//Additional links on the plugin page
	
	function register_links($links, $file) {
		
		if ($file == FCW_BASE) {
			$links[] = '<a href="http://wordpress.org/extend/plugins/category-coloumn/faq/" target="_blank">'.__('FAQ', 'category-feature').'</a>';
			$links[] = '<a href="https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=RMF326NZYFL6L" target="_blank">'.__('Donate', 'category-feature').'</a>';
		}
		
		return $links;
		
	}
		
	function plugin_action_links( $links, $file ) {
		
		if ($file == FCW_BASE) array_unshift($links, '<a href="'.admin_url( 'options-general.php?page=featured-category-settings' ).'">'.__('Settings', 'category-feature').'</a>');
	
		return $links;
	
	}
	
	// Creating default options on activation
	
	function _install() {
		
		$compress = (SCRIPT_DEBUG) ? false : true;
		
		$default = array(
			'cache' => array(),
			'inline' => false,
			'compress' => $compress,
			'css_cache' => '',
			'flushed' => true
		);
		
		add_option('cf_options', $default);
		
		add_rewrite_rule('a5-framework-frontend.css', 'index.php?A5_file=wp_css', 'top');
		add_rewrite_rule('a5-framework-frontend.js', 'index.php?A5_file=wp_js', 'top');
		add_rewrite_rule('a5-framework-backend.css', 'index.php?A5_file=admin_css', 'top');
		add_rewrite_rule('a5-framework-backend.js', 'index.php?A5_file=admin_js', 'top');
		add_rewrite_rule('a5-framework-login.css', 'index.php?A5_file=login_css', 'top');
		add_rewrite_rule('a5-framework-login.js', 'index.php?A5_file=login_js', 'top');
		add_rewrite_rule('a5-export-settings', 'index.php?A5_file=export', 'top');
		flush_rewrite_rules();
		
	}
	
	// Cleaning on deactivation
	
	function _uninstall() {
		
		delete_option('cf_options');
		
		flush_rewrite_rules();
		
	}
	
	function update_rewrite_rules() {
		
		add_rewrite_rule('a5-framework-frontend.css', 'index.php?A5_file=wp_css', 'top');
		add_rewrite_rule('a5-framework-frontend.js', 'index.php?A5_file=wp_js', 'top');
		add_rewrite_rule('a5-framework-backend.css', 'index.php?A5_file=admin_css', 'top');
		add_rewrite_rule('a5-framework-backend.js', 'index.php?A5_file=admin_js', 'top');
		add_rewrite_rule('a5-framework-login.css', 'index.php?A5_file=login_css', 'top');
		add_rewrite_rule('a5-framework-login.js', 'index.php?A5_file=login_js', 'top');
		add_rewrite_rule('a5-export-settings', 'index.php?A5_file=export', 'top');
		
		flush_rewrite_rules();
		
		self::$options['flushed'] = true;
		
		update_option('cf_options', self::$options);
	
	}
	
	function flush_widget_cache() {
		
		global $wpdb;
		
		self::$options['cache'] = array();
		
		$update_args = array('option_value' => serialize(self::$options));
		
		$result = $wpdb->update( $wpdb->options, $update_args, array( 'option_name' => 'cf_options' ) );
	
	}
	
	/**
	 *
	 * Adds a link to the settings to the admin bar in case WP_DEBUG is true
	 *
	 */
	function admin_bar_menu() {
		
		global $wp_admin_bar;
		
		if (!is_super_admin() || !is_admin_bar_showing()) return;
		
		$wp_admin_bar->add_node(array('parent' => '', 'id' => 'a5-framework', 'title' => 'A5 Framework'));
		
		$wp_admin_bar->add_node(array('parent' => 'a5-framework', 'id' => 'a5-category-feature', 'title' => 'Featured Category Widget', 'href' => admin_url('options-general.php?page=featured-category-settings')));
		
	}
	
}

$CategoryFeature = new CategoryFeature;

?>