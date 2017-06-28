<?php

/**
 *
 * Class Featured Category Widget Admin
 *
 * @ A5 Featured Category Widget
 *
 * building admin page
 *
 */
class CF_Admin extends A5_OptionPage {
	
	const language_file = 'category-feature';
	
	static $options;
	
	function __construct() {
	
		add_action('admin_init', array($this, 'initialize_settings'));
		add_action('admin_menu', array($this, 'add_admin_menu'));
		if (WP_DEBUG == true) add_action('admin_enqueue_scripts', array($this, 'enqueue_scripts'));
		
		self::$options = get_option('cf_options');
		
	}
	
	/**
	 *
	 * Make debug info collapsable
	 *
	 */
	function enqueue_scripts($hook){
		
		if ($hook != 'settings_page_featured-category-settings') return;
		
		wp_enqueue_script('dashboard');
		
		if (wp_is_mobile()) wp_enqueue_script('jquery-touch-punch');
		
	}
	
	/**
	 *
	 * Add options-page for single site
	 *
	 */
	function add_admin_menu() {
		
		add_options_page('Featured Category '.__('Settings', 'category-feature'), '<img alt="" src="'.plugins_url('category-feature/img/a5-icon-11.png').'"> Featured Category', 'administrator', 'featured-category-settings', array($this, 'build_options_page'));
		
	}
	
	/**
	 *
	 * Actually build the option pages
	 *
	 */
	function build_options_page() {
		
		$eol = "\n";
		
		self::open_page('Featured Category', __('http://wasistlos.waldemarstoffel.com/plugins-fur-wordpress/featured-category-widget', 'category-feature'), 'category-feature', __('Plugin Support', 'category-feature'));
		
		self::open_form('options.php');
		
		settings_fields('cf_options');
		do_settings_sections('cf_style');
		submit_button();
		
		if (WP_DEBUG === true) :
		
			self::open_tab();
			
			self::sortable('deep-down', self::debug_info(self::$options, __('Debug Info', 'category-feature')));
		
			self::close_tab();
		
		endif;
		
		self::close_page();
		
	}
	
	/**
	 *
	 * Initialize the admin screen of the plugin
	 *
	 */
	function initialize_settings() {
		
		register_setting( 'cf_options', 'cf_options', array($this, 'validate') );
		
		add_settings_section('cf_settings', __('Styling of the widgets', 'category-feature'), array($this, 'display_section'), 'cf_style');
		
		add_settings_field('cf_css', __('Widget container:', 'category-feature'), array($this, 'css_field'), 'cf_style', 'cf_settings', array(__('You can enter your own style for the widgets here. This will overwrite the styles of your theme.', 'category-feature'), __('If you leave this empty, you can still style every instance of the widget individually.', 'category-feature')));
		
		add_settings_field('cf_custom_css', __('Custom Field:', 'category-feature'), array($this, 'custom_css_field'), 'cf_style', 'cf_settings', array(__('If you want to display a custom field of the posts in the widget, it will be wrapped in a &#39;&lt;p&gt;&#39; tag.', 'category-feature'), __('Here you can style the class of that paragraph.', 'category-feature')));
		
		add_settings_field('cf_compress', __('Compress Style Sheet:', 'category-feature'), array($this, 'compress_field'), 'cf_style', 'cf_settings', array(__('Click here to compress the style sheet.', 'category-feature')));
		
		add_settings_field('cf_inline', __('Debug:', 'category-feature'), array($this, 'inline_field'), 'cf_style', 'cf_settings', array(__('If you can&#39;t reach the dynamical style sheet, you&#39;ll have to diplay the styles inline. By clicking here you can do so.', 'category-feature')));
		
		$cachesize = count(self::$options['cache']);
		
		$entry = ($cachesize > 1) ? __('entries', 'category-feature') : __('entry', 'category-feature');
		
		if ($cachesize > 0) add_settings_field('cf_reset', sprintf(__('Empty cache (%d %s):', 'category-feature'), $cachesize, $entry), array($this, 'reset_field'), 'cf_style', 'cf_settings', array(__('You can empty the plugin&#39;s cache here, if necessary.', 'category-feature')));
		
		add_settings_field('cf_resize', false, array($this, 'resize_field'), 'cf_style', 'cf_settings');
	
	}
	
	function display_section() {
		
		echo '<p>'.__('Just put some css code here.', 'category-feature').'</p>';
	
	}
	
	function css_field($labels) {
		
		echo $labels[0].'</br>'.$labels[1].'</br>';
		
		a5_textarea('css', 'cf_options[css]', @self::$options['css'], false, array('rows' => 7, 'cols' => 35));
		
	}
	
	function custom_css_field($labels) {
		
		echo $labels[0].'</br>'.$labels[1].'</br>';
		
		a5_textarea('custom_css', 'cf_options[custom_css]', @self::$options['custom_css'], false, array('rows' => 7, 'cols' => 35));
		
	}
	
	function compress_field($labels) {
		
		a5_checkbox('compress', 'cf_options[compress]', @self::$options['compress'], $labels[0]);
		
	}
	
	function inline_field($labels) {
		
		a5_checkbox('inline', 'cf_options[inline]', @self::$options['inline'], $labels[0]);
		
	}
	
	function reset_field($labels) {
		
		a5_checkbox('reset_options', 'cf_options[reset_options]', @self::$options['reset_options'], $labels[0]);
		
	}
	
	function resize_field() {
		
		a5_resize_textarea(array('css', 'custom_css'));
		
	}
		
	function validate($input) {
		
		self::$options['css']=trim($input['css']);
		self::$options['custom_css']=trim($input['custom_css']);
		self::$options['compress'] = isset($input['compress']) ? true : false;
		self::$options['inline'] = isset($input['inline']) ? true : false;
		
		if (isset($input['reset_options'])) :
		
			self::$options['cache'] = array();
			
			add_settings_error('cf_options', 'empty-cache', __('Cache emptied.', 'category-feature'), 'updated');
			
		endif;
		
		self::$options['css_cache'] = '';
		
		return self::$options;
	
	}

} // end of class

?>