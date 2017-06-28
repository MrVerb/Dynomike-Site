<?php

/**
 *
 * Class CF Dynamic CSS
 *
 * Extending A5 Dynamic Files
 *
 * Presses the dynamical CSS of the Featured Category Widget into a virtual style sheet
 *
 */

class CF_DynamicCSS extends A5_DynamicFiles {
	
	private static $options;
	
	function __construct() {
		
		self::$options = get_option('cf_options');
		
		if (!isset(self::$options['inline'])) self::$options['inline'] = false;
		
		if (!array_key_exists('priority', self::$options)) self::$options['priority'] = false;
		
		if (!array_key_exists('compress', self::$options)) self::$options['compress'] = true;
		
		$this->a5_styles('wp', 'all', self::$options['inline'], self::$options['priority']);
		
		$cfw_styles = self::$options['css_cache'];
		
		if (!$cfw_styles) :
		
			$eol = (self::$options['compress']) ? '' : "\n";
			$tab = (self::$options['compress']) ? '' : "\t";
			
			$css_selector = 'widget_featured_category_widget[id^="featured_category_widget"]';
			
			$cfw_styles = (!self::$options['compress']) ? $eol.'/* CSS portion of the Featured Category Widget */'.$eol.$eol : '';
			
			$style = '-moz-hyphens: auto;'.$eol.$tab.'-o-hyphens: auto;'.$eol.$tab.'-webkit-hyphens: auto;'.$eol.$tab.'-ms-hyphens: auto;'.$eol.$tab.'hyphens: auto;';
			
			if (!empty(self::$options['css'])) $style.=$eol.$tab.str_replace('; ', ';'.$eol.$tab, str_replace(array("\r\n", "\n", "\r"), ' ', self::$options['css']));
			
			$cfw_styles .= parent::build_widget_css($css_selector, '').'{'.$eol.$tab.$style.$eol.'}'.$eol;
			
			$cfw_styles .= parent::build_widget_css($css_selector, 'img').'{'.$eol.$tab.'height: auto;'.$eol.$tab.'max-width: 100%;'.$eol.'}'.$eol;
			
			if (!empty(self::$options['custom_css'])) :
			
				$style = $eol.$tab.str_replace('; ', ';'.$eol.$tab, str_replace(array("\r\n", "\n", "\r"), ' ', self::$options['custom_css']));
				
				$cfw_styles .= 'p.custom_field {'.$eol.$tab.$style.$eol.'}'.$eol;
				
			endif;
			
			self::$options['css_cache'] = $cfw_styles;
			
			update_option('cf_options', self::$options);
			
		endif;
		
		parent::$wp_styles .= $cfw_styles;

	}
	
} // CF_Dynamic CSS

?>