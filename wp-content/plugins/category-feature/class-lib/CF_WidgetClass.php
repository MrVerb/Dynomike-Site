<?php

/**
 *
 * Class CF Widget
 *
 * @ Featured Category Widget
 *
 * building the actual widget
 *
 */
class Featured_Category_Widget extends A5_Widget {
	
private static $options;
 
	function Featured_Category_Widget() {
		
		$widget_opts = array( 'description' => __('Configure the output and looks of the widget. Then display thumbnails and excerpts of posts in your widget areas.', 'category-feature') );
		$control_opts = array( 'width' => 400 );
		
		parent::__construct(false, $name = 'Featured Category Widget', $widget_opts, $control_opts);
		
		self::$options = get_option('cf_options');
	
	}
	 
	function form($instance) {
		
	// setup some default settings
	
		$defaults = array(
			'title' => NULL,
			'postcount' => 5,
			'offset' => NULL,
			'static' => NULL,
			'random' => false,
			'link_title' => false,
			'no_title' => 1,
			'home' => false,
			'category_id' => NULL,
			'wordcount' => NULL,
			'width' => get_option('thumbnail_size_w'),
			'words' => NULL,
			'readmore' => false,
			'rmtext' => NULL,
			'linespace' => false,
			'line' => 1,
			'line_color' => '#dddddd',
			'style' => NULL,
			'homepage' => 1,
			'frontpage' => false,
			'page' => false,
			'category' => 1,
			'single' => false,
			'date' => false,
			'archive' => false,
			'tag' => false,
			'attachment' => false,
			'taxonomy' => false,
			'author' => false,
			'search' => false,
			'not_found' => false,
			'login_page' => false,
			'h' => 3,
			'headline' => NULL,
			'headshort' => NULL,
			'rmclass' => NULL,
			'filter' => false,
			'noshorts' => false,
			'format' => false,
			'show_date' => NULL,
			'alignment' => NULL,
			'imgborder' => NULL,
			'allow_double' => false,
			'alpha' => false,
			'custom_field' => NULL
		);
		
		$instance = wp_parse_args( (array) $instance, $defaults );
		
		$title = esc_attr($instance['title']);
		$postcount = esc_attr($instance['postcount']);
		$offset = esc_attr($instance['offset']);
		$static = $instance['static'];
		$random = esc_attr($instance['random']);
		$link_title = esc_attr($instance['link_title']);
		$no_title = esc_attr($instance['no_title']);
		$home = esc_attr($instance['home']);
		$category_id = esc_attr($instance['category_id']);
		$wordcount = esc_attr($instance['wordcount']);
		$width = esc_attr($instance['width']);
		$words = esc_attr($instance['words']);
		$readmore = esc_attr($instance['readmore']);
		$rmtext = esc_attr($instance['rmtext']);
		$noshorts = esc_attr($instance['noshorts']);
		$linespace = esc_attr($instance['linespace']);
		$line=esc_attr($instance['line']);
		$line_color=esc_attr($instance['line_color']);
		$style=esc_attr($instance['style']);
		$homepage=esc_attr($instance['homepage']);
		$frontpage=esc_attr($instance['frontpage']);
		$page=esc_attr($instance['page']);
		$category=esc_attr($instance['category']);
		$single=esc_attr($instance['single']);
		$date=esc_attr($instance['date']);
		$archive=esc_attr($instance['archive']);
		$tag=esc_attr($instance['tag']);
		$attachment=esc_attr($instance['attachment']);
		$taxonomy=esc_attr($instance['taxonomy']);
		$author=esc_attr($instance['author']);
		$search=esc_attr($instance['search']);
		$not_found=esc_attr($instance['not_found']);
		$login_page=esc_attr($instance['login_page']);
		$h=esc_attr($instance['h']);
		$headline=esc_attr($instance['headline']);
		$headshort=esc_attr($instance['headshort']);
		$rmclass=esc_attr($instance['rmclass']);
		$filter=esc_attr($instance['filter']);
		$format=esc_attr($instance['format']);
		$show_date=esc_attr($instance['show_date']);
		$alignment=esc_attr($instance['alignment']);
		$imgborder=esc_attr($instance['imgborder']);
		$allow_double=esc_attr($instance['allow_double']);
		$alpha=esc_attr($instance['alpha']);
		$custom_field=esc_attr($instance['custom_field']);
		
		$features = get_categories('hide_empty=0');
		foreach ( $features as $feature ) :
		
			$categories[] = array($feature->cat_ID, $feature->cat_name );
		
		endforeach;
		
		$options = array (array('top', __('Above thumbnail', 'category-feature')) , array('bottom', __('Under thumbnail', 'category-feature')));
		
		$date_options = array (array('top', __('Above post', 'category-feature')), array('middel', __('Under thumbnail', 'category-feature')), array('bottom', __('Under post', 'category-feature')), array('none', __('Don&#39;t show date', 'category-feature')));
		
		$base_id = 'widget-'.$this->id_base.'-'.$this->number.'-';
		$base_name = 'widget-'.$this->id_base.'['.$this->number.']';
		
		a5_text_field($base_id.'title', $base_name.'[title]', $title, __('Title:', 'category-feature'), array('class' => 'widefat', 'space' => true));
		a5_select($base_id.'category_id', $base_name.'[category_id]', $categories, $category_id, __('Category:', 'category-feature'), __('Choose a category', 'category-feature'), array('class' => 'widefat', 'space' => true));
		a5_checkbox($base_id.'link_title', $base_name.'[link_title]', $link_title, __('Link the widget title to the chosen category.', 'category-feature'), array('size' => 4, 'step' => 1, 'space' => true));
		a5_checkbox($base_id.'no_title', $base_name.'[no_title]', $no_title, __('Show the post title.', 'category-feature'), array('size' => 4, 'step' => 1, 'space' => true));
		a5_number_field($base_id.'postcount', $base_name.'[postcount]', $postcount, __('How many posts will be displayed in the widget:', 'category-feature'), array('size' => 4, 'step' => 1, 'space' => true));
		a5_number_field($base_id.'offset', $base_name.'[offset]', $offset, __('Offset (how many posts are spared out in the beginning):', 'category-feature'), array('size' => 4, 'step' => 1, 'space' => true));
		a5_checkbox($base_id.'static', $base_name.'[static]', $static, __('Switch off pagination in the widget.', 'category-feature'), array('space' => true));
		a5_text_field($base_id.'custom_field', $base_name.'[custom_field]', $custom_field, __('If you want to display a custom field, give it&#39;s name here:', 'category-feature'), array('class' => 'widefat', 'space' => true));
		a5_checkbox($base_id.'random', $base_name.'[random]', $random, __('Check to display random post(s) instead of a standard loop:', 'category-feature'), array('space' => true));
		a5_checkbox($base_id.'alpha', $base_name.'[alpha]', $alpha, __('Check to display posts in aplhabetical order:', 'category-feature'), array('space' => true));
		a5_checkbox($base_id.'home', $base_name.'[home]', $home, __('Check to have the offset only on your Frontpage.', 'category-feature'), array('space' => true));
		a5_number_field($base_id.'width', $base_name.'[width]', $width, __('Width of the thumbnail (in px):', 'category-feature'), array('size' => 4, 'step' => 1, 'space' => true));
		a5_text_field($base_id.'imgborder', $base_name.'[imgborder]', $imgborder, sprintf(__('If wanting a border around the image, write the style here. %s would make it a black border, 1px wide.', 'category-feature'), '<strong>1px solid #000000</strong>'), array('space' => true, 'class' => 'widefat'));
		a5_number_field($base_id.'wordcount', $base_name.'[wordcount]', $wordcount, __('To overwrite the excerpt of WP, give here the number of sentences from the post that you want to display:', 'category-feature'), array('size' => 4, 'step' => 1, 'space' => true));
		a5_checkbox($base_id.'words', $base_name.'[words]', $words, __('Check to display words instead of sentences.', 'category-feature'), array('space' => true));
		a5_checkbox($base_id.'linespace', $base_name.'[linespace]', $linespace, __('Check to have each sentence in a new line.', 'category-feature'), array('space' => true));
		a5_checkbox($base_id.'noshorts', $base_name.'[noshorts]', $noshorts, __('Check to suppress shortcodes in the widget (in case the content is showing).', 'category-feature'), array('space' => true));
		a5_checkbox($base_id.'filter', $base_name.'[filter]', $filter, __('Check to return the excerpt unfiltered (might avoid interferences with other plugins).', 'category-feature'), array('space' => true));
		a5_checkbox($base_id.'format', $base_name.'[format]', $format, __('Check to keep the layout of the post (all tags and spaces).', 'category-feature'), array('space' => true));
		a5_select($base_id.'headline', $base_name.'[headline]', $options, $headline, __('Choose, whether to display the title above or under the thumbnail.', 'category-feature'), false, array('space' => true));
		parent::select_heading($instance);
		a5_select($base_id.'show_date', $base_name.'[show_date]', $date_options, $show_date, __('Choose, whether or not to display the publishing date and whether it comes above or under the post.', 'category-feature'), false, array('space' => true));
		parent::textalign($instance);
		$shorten_title = a5_number_field($base_id.'headshort', $base_name.'[headshort]', $headshort,false, array('size' => 4, 'step' => 1), false);
		echo sprintf(__('%1$sLimit the title to %2$s words.%3$s', 'category-feature'), '<p>', $shorten_title, '</p>');
		a5_number_field($base_id.'line', $base_name.'[line]', $line, __('If you want a line between the posts, this is the height in px (if not wanting a line, leave emtpy):', 'category-feature'), array('size' => 4, 'step' => 1, 'space' => true));
		a5_color_field($base_id.'line_color', $base_name.'[line_color]', $line_color, __('The color of the line (e.g. #cccccc):', 'category-feature'), array('size' => 13, 'space' => true));	
		parent::read_more($instance);
		a5_checkbox($base_id.'allow_double', $base_name.'[allow_double]', $allow_double, __('Check to keep the post of the main loop in the widget.', 'category-feature'), array('space' => true));
		parent::page_checkgroup($instance);
		a5_textarea($base_id.'style', $base_name.'[style]', $style, sprintf(__('Here you can finally style the widget. Simply type something like%1$s%2$sborder-left: 1px dashed;%2$sborder-color: #000000;%3$s%2$sto get just a dashed black line on the left. If you leave that section empty, your theme will style the widget.', 'category-feature'), '<strong>', '<br />', '</strong>'), array('style' => 'height: 60px;', 'class' => 'widefat', 'space' => true));
		a5_resize_textarea(array($base_id.'style'));
		
	} // form
	 
	function update($new_instance, $old_instance) {
		
		unset(self::$options['cache'][$this->number]);
			
		global $wpdb;
		
		$update_args = array('option_value' => serialize(self::$options));
		
		$result = $wpdb->update( $wpdb->options, $update_args, array( 'option_name' => 'cf_options' ) );
	
		$instance = $old_instance;
	
		$instance['title'] = strip_tags($new_instance['title']);
		$instance['postcount'] = strip_tags($new_instance['postcount']);
		$instance['offset'] = strip_tags($new_instance['offset']);
		$instance['static'] = @$new_instance['static'];
		$instance['random'] = @$new_instance['random'];
		$instance['home'] = @$new_instance['home'];
		$instance['link_title'] = @$new_instance['link_title'];
		$instance['no_title'] = @$new_instance['no_title'];
		$instance['category_id'] = strip_tags($new_instance['category_id']); 
		$instance['wordcount'] = strip_tags($new_instance['wordcount']);
		$instance['width'] = strip_tags($new_instance['width']);
		$instance['words'] = @$new_instance['words'];
		$instance['readmore'] = @$new_instance['readmore'];
		$instance['rmtext'] = strip_tags($new_instance['rmtext']);
		$instance['noshorts'] = @$new_instance['noshorts'];
		$instance['linespace'] = @$new_instance['linespace'];
		$instance['line'] = strip_tags($new_instance['line']);
		$instance['line_color'] = strip_tags($new_instance['line_color']);
		$instance['style'] = strip_tags($new_instance['style']);
		$instance['homepage'] = @$new_instance['homepage'];
		$instance['frontpage'] = @$new_instance['frontpage'];
		$instance['page'] = @$new_instance['page'];
		$instance['category'] = @$new_instance['category'];
		$instance['single'] = @$new_instance['single'];
		$instance['date'] = @$new_instance['date'];
		$instance['archive'] = @$new_instance['archive'];
		$instance['tag'] = @$new_instance['tag'];
		$instance['attachment'] = @$new_instance['attachment'];
		$instance['taxonomy'] = @$new_instance['taxonomy'];
		$instance['author'] = @$new_instance['author'];
		$instance['search'] = @$new_instance['search'];
		$instance['not_found'] = @$new_instance['not_found'];
		$instance['login_page'] = @$new_instance['login_page'];
		$instance['h'] = strip_tags($new_instance['h']);
		$instance['headline'] = strip_tags($new_instance['headline']);
		$instance['headshort'] = strip_tags($new_instance['headshort']);
		$instance['rmclass'] = strip_tags($new_instance['rmclass']);
		$instance['filter'] = @$new_instance['filter'];
		$instance['format'] = @$new_instance['format'];
		$instance['show_date'] = strip_tags($new_instance['show_date']);
		$instance['alignment'] = strip_tags($new_instance['alignment']);
		$instance['imgborder'] = strip_tags($new_instance['imgborder']);
		$instance['allow_double'] = @$new_instance['allow_double'];
		$instance['alpha'] = @$new_instance['alpha'];
		$instance['custom_field'] = strip_tags($new_instance['custom_field']);
		
		return $instance;
	
	} // update
	 
	function widget($args, $instance) {
		
		$show_widget = parent::check_output($instance);
	
		if ($show_widget) :
			
			extract( $args );
			
			$title = apply_filters('widget_title', $instance['title']);
			
			if (!empty($instance['style'])) :
			
				$style=str_replace(array("\r\n", "\n", "\r"), '', $instance['style']);
				
				$before_widget = str_replace('>', 'style="'.$style.'">', $before_widget);
			
			endif;	
			
			echo $before_widget;
			
			if ( $title && $instance['link_title'] ) $title = '<a href="'.get_category_link($instance['category_id']).'" title="'.__('Permalink to', 'category-feature').' '.get_cat_name($instance['category_id']).'">'.$title.'</a>';
			
			if ( $title ) echo $before_title . $title . $after_title;
		 
			/* This is the actual function of the plugin, it fills the widget area with the customized excerpts */
			
			global $wp_query, $post;
			
			$i=1;
			
			$cfw_setup['posts_per_page'] = $instance['postcount'];
			
			$cfw_setup['offset'] = $instance['offset'];
			
			if (!$instance['static']) :
			
				if (is_category() || is_home() || empty($instance['home'])) :
					
					$cfw_page = $wp_query->get( 'paged' );
					$cfw_numberposts = $wp_query->get( 'posts_per_page' );
					
					$cfw_setup['offset'] = ($cfw_page) ? (($cfw_page-1)*$cfw_numberposts)+$instance['offset'] : $instance['offset'];
					
					$cfw_cat_count = (!empty($instance['category_id'])) ? get_category($instance['category_id'])->category_count : wp_count_posts()->publish;
					
					if ($cfw_cat_count - $cfw_setup['offset'] < $cfw_setup['offset']) $cfw_setup['offset'] = $cfw_cat_count - $instance['postcount'];
				
				endif;
				
			endif;
			
			$cfw_setup['cat'] = $instance['category_id'];
			
			if ($instance['random']) $cfw_setup['orderby'] = 'rand';
			
			if ($instance['alpha']) : 
			
				$cfw_setup['orderby'] = 'title';
			
				$cfw_setup['order'] = 'ASC';
			
			endif;
			
			if (is_single() && !$instance['allow_double']) $cfw_setup['post__not_in'] = array($wp_query->get_queried_object_id());
			
			$cfw_posts = new WP_Query($cfw_setup);
			
			while($cfw_posts->have_posts()) :
				
				$cfw_posts->the_post();
				
				setup_postdata($post);
				
				// build post title
				
				$the_title = $post->post_title;
				
				if ($instance['headshort']) :
				
					$args = array(
						'content' => $the_title,
						'count' => $instance['headshort'],
						'type' => 'words',
						'filter' => false
					);
						
					$the_title = A5_Excerpt::text($args).'&#8230;';
					
				endif;
				
				if (isset(self::$options['cache'][$this->number][$post->ID]['tags'])) :
				
					$cfw_tags = self::$options['cache'][$this->number][$post->ID]['tags'];
				
				else :
			
					$cfw_tags = A5_Image::tags();
					
					self::$options['cache'][$this->number][$post->ID]['tags'] = $cfw_tags;
						
					update_option('cf_options', self::$options);
					
				endif;
				
				$eol = "\n";
				
				$cfw_image_alt = $cfw_tags['image_alt'];
				$cfw_image_title = $cfw_tags['image_title'];
				$cfw_title_tag = $cfw_tags['title_tag'];
			
				$cfw_headline = (!empty($instance['no_title'])) ? '<h'.$instance['h'].'>'.$eol.'<a href="'.get_permalink().'" title="'.$cfw_title_tag.'">'.$the_title.'</a>'.$eol.'</h'.$instance['h'].'>'.$eol : '';
				
				$cfw_style = ($instance['alignment'] != 'notext' && $instance['alignment'] != 'none') ? ' style="text-align: '.$instance['alignment'].';"' : '';
				
				if ($instance['show_date'] != 'none') $post_date = $eol.'<p'.$cfw_style.'>'.get_the_date().'</p>';
	
				// get thumbnail
				
				if (isset(self::$options['cache'][$this->number][$post->ID]['image'])) :
			
					$cfw_image = self::$options['cache'][$this->number][$post->ID]['image'];
				
				else :
				
					$cfw_image = false;
				
					$cfw_float = ($instance['alignment'] != 'notext') ? $instance['alignment'] : 'none';
						
					$cfw_margin = '';
					if ($instance['alignment'] == 'left') $cfw_margin = ' margin-right: 1em;';
					if ($instance['alignment'] == 'right') $cfw_margin = ' margin-left: 1em;';
					
					$cfw_imgborder = (isset($instance['imgborder'])) ? ' border: '.$instance['imgborder'].';' : '';
				
					$args = array (
						'id' => $post->ID,
						'width' => $instance['width']
					);
							
					$cfw_image_info = A5_Image::thumbnail($args);
					
					if ($cfw_image_info) :
							
						$cfw_thumb = $cfw_image_info[0];
					
						$cfw_width = $cfw_image_info[1];
				
						$cfw_height = ($cfw_image_info[2]) ? 'height="'.$cfw_image_info[2].' "' : '';
						
						$cfw_image_tag = '<img title="'.$cfw_image_title.'" src="'.$cfw_thumb.'" alt="'.$cfw_image_alt.'" class="wp-post-image" width="'.$cfw_width.'"'.$cfw_height.'style="float: '.$cfw_float.';'.$cfw_margin.$cfw_imgborder.'" />';
						
					else :
				
						$cfw_image_tag = '';
						
					endif;
					
					if (!empty($cfw_image_tag)) :
					
						$cfw_image = '<a href="'.get_permalink().'">'.$cfw_image_tag.'</a>'.$eol;
				
						if ($instance['alignment'] == 'none' || $instance['alignment'] == 'notext') $cfw_image .= '<div style="clear: both;"></div>'.$eol;
						
						self::$options['cache'][$this->number][$post->ID]['image'] = $cfw_image;
						
						update_option('cf_options', self::$options);
						
					endif;
					
				endif;
				
				// get excerpt
				
				if (isset(self::$options['cache'][$this->number][$post->ID]['text'])) :
					
					$cfw_text = self::$options['cache'][$this->number][$post->ID]['text'];
				
				else :
					
					$type = (!$instance['words']) ? 'sentences' : 'words';
					
					$excerpt = ($instance['wordcount']) ? false : $post->post_excerpt;
					
					$rmtext = ($instance['rmtext']) ? $instance['rmtext'] : '[&#8230;]';
					
					$shortcode = ($instance['noshorts']) ? false : true;
								
					$args = array(
						'excerpt' => $excerpt,
						'content' => $post->post_content,
						'type' => $type,
						'count' => $instance['wordcount'],
						'linespace' => $instance['linespace'],
						'shortcode' => $shortcode,
						'readmore' => $instance['readmore'],
						'rmtext' => $rmtext,
						'link' => get_permalink(),
						'title' => $cfw_title_tag,
						'class' => $instance['rmclass'],
						'filter' => $instance['filter'],
						'format' => $instance['format']
					);
					
					$cfw_text = A5_Excerpt::text($args);
					
					self::$options['cache'][$this->number][$post->ID]['text'] = $cfw_text;
					
					update_option('cf_options', self::$options);
					
				endif;
				
				// output
				
				if ('top' == $instance['headline']) echo $cfw_headline;
				
				if ('top' == $instance['show_date']) echo $post_date.$eol;
				
				if (!empty($cfw_image)) echo $eol.$cfw_image;
				
				if ('middel' == $instance['show_date']) echo $post_date.$eol;
				
				if ('bottom' == $instance['headline']) echo $cfw_headline;
				
				if ($instance['alignment'] == 'left' || $instance['alignment'] == 'right') echo $eol.do_shortcode($cfw_text).$eol;
			
				echo '<div style="clear: both;"></div>'.$eol;
				
				if ($instance['alignment'] == 'none') echo do_shortcode($cfw_text).$eol;
				
				if ('bottom' == $instance['show_date']) echo $post_date.$eol;
				
				// custom field, if wanted
				
				if (isset($instance['custom_field'])) :
				
					$field = get_post_custom_values($instance['custom_field'], $post_id);
					
					if (isset($field)) foreach ($field as $value) echo '<p class="custom_field">'.$value.'</p>';
					
				endif;
				
				// line, if wanted
				   
				if (!empty($instance['line']) && $i <  $instance['postcount']) :
					
					echo '<hr style="color: '.$instance['line_color'].'; background-color: '.$instance['line_color'].'; height: '.$instance['line'].'px;" />';
					
					$i++;
					
				endif;
				
				unset ($cfw_img);
				
				endwhile;
			
				// Restore original Post Data
				wp_reset_postdata();
			
			echo $after_widget;
		
		else:
		
			echo '<!-- Featured Category Widget is not setup for this view. -->';
		
		endif;
	 
	}
 
}

add_action('widgets_init', create_function('', 'return register_widget("Featured_Category_Widget");'));


?>
