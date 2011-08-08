<?php

/*
Plugin Name: Most and Least Read Posts Widget
Plugin URI: http://www.whiletrue.it/
Description: Provide two widgets, showing lists of the most and reast read posts.
Author: WhileTrue
Version: 1.0
Author URI: http://www.whiletrue.it/
*/

/*
    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License version 2, 
    as published by the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.
*/


add_filter('the_content', 'most_and_least_read_posts_update');


function most_and_least_read_posts_update ($content) {

	// ONLY APPLIES TO SINGLE POSTS

	if (!is_single()) {
		return $content;
	}
	
	// AVOID THE MOST COMMON WEB SPIDERS
	
	$spiders = array(
		'Googlebot', 'Yammybot', 'Openbot', 'Yahoo', 'Slurp', 'msnbot',
		'ia_archiver', 'Lycos', 'Scooter', 'AltaVista', 'Teoma', 'Gigabot'
	);
	foreach ($spiders as $spider) {
		if (eregi($spider, $_SERVER['HTTP_USER_AGENT'])) {
			return $content;
		}
	}

	// UPDATE HITS

	$post_id = get_the_ID();
	$meta_key = 'custom_total_hits';
	$custom_field_total_hits = get_post_meta($post_id, $meta_key, true);
	$total_hits = (is_numeric($custom_field_total_hits)) ? (int)$custom_field_total_hits : 0;
	update_post_meta($post_id, $meta_key, str_pad(($total_hits+1),9,0,STR_PAD_LEFT)); 
	return $content;
}


function most_and_least_read_posts ($instance, $order) {
	global $wpdb;

	$sql_esc = '';
	if ($instance['words_excluded']!='') {
		$excludes = array_filter(explode(',',$instance['words_excluded']));
		$sql_esc_arr = array();
		foreach($excludes as $val) {
			$sql_esc_arr[] = " p.post_title not like '%".$val."%' ";
		}
		$sql_esc = " and ( ".implode(" or ", $sql_esc_arr).") ";
	}

	$sql = " select p.ID, p.post_title
		FROM $wpdb->postmeta as m
			LEFT JOIN $wpdb->posts as p on (m.post_id = p.ID)
		WHERE m.meta_key = 'custom_total_hits'
			$sql_esc
		ORDER BY m.meta_value $order
		LIMIT 0, ".$instance['posts_number'];
	        
	$output = $wpdb->get_results($sql);

	if ($output) {
		foreach ($output as $line) {
	  	$out .=  '<li>
					<a title="'.str_replace("'","&apos;", $line->post_title).'" href="'.get_permalink($line->ID).'">'
						.$line->post_title.'
					</a>
				</li>';
		}   
	} else {
		$out .= '<li>'.__('No results available', 'least_read_posts').'</li>';
	}      
	return '<ul>'.$out.'</ul>';
}


//////////



/**
 * LeastReadPostsWidget Class
 */
class LeastReadPostsWidget extends WP_Widget {
    /** constructor */
    function LeastReadPostsWidget() {
        parent::WP_Widget(false, $name = 'Least Read Posts');	
    }

    /** @see WP_Widget::widget */
    function widget($args, $instance) {		
        extract( $args );
        $title = apply_filters('widget_title', $instance['title']);
        echo $before_widget;  
		if ( $title ) echo $before_title . $title . $after_title; 
		echo most_and_least_read_posts($instance, ' ASC ').$after_widget;
    }

    /** @see WP_Widget::update */
    function update($new_instance, $old_instance) {				
	$instance = $old_instance;
	$instance['title'] = strip_tags($new_instance['title']);
	$instance['posts_number'] = strip_tags($new_instance['posts_number']);
	$instance['words_excluded'] = strip_tags($new_instance['words_excluded']);
        return $instance;
    }

    /** @see WP_Widget::form */
    function form($instance) {
		if (empty($instance)) {
			$instance['title'] = 'Least Read Posts';
			$instance['posts_number'] = 5;
			$instance['words_excluded'] = '';
		}					
        $title = esc_attr($instance['title']);
        $words_number = esc_attr($instance['words_number']);
        $words_excluded = esc_attr($instance['words_excluded']);
        ?>
         <p>
          <label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title:'); ?></label> 
          <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo $title; ?>" />
        </p>
         <p>
          <label for="<?php echo $this->get_field_id('posts_number'); ?>"><?php _e('Number of posts to show:'); ?></label> 
          <input class="widefat" id="<?php echo $this->get_field_id('posts_number'); ?>" name="<?php echo $this->get_field_name('posts_number'); ?>" type="text" value="<?php echo $words_number; ?>" />
        </p>
         <p>
          <label for="<?php echo $this->get_field_id('words_excluded'); ?>"><?php _e('Exclude post whose title contains any of these words (comma separated):'); ?></label> 
          <input class="widefat" id="<?php echo $this->get_field_id('words_excluded'); ?>" name="<?php echo $this->get_field_name('words_excluded'); ?>" type="text" value="<?php echo $words_excluded; ?>" />
        </p>
        <?php 
    }

} // class LeastReadPostsWidget


/**
 * MostReadPostsWidget Class
 */
class MostReadPostsWidget extends WP_Widget {
    /** constructor */
    function MostReadPostsWidget() {
        parent::WP_Widget(false, $name = 'Most Read Posts');	
    }

    /** @see WP_Widget::widget */
    function widget($args, $instance) {		
        extract( $args );
        $title = apply_filters('widget_title', $instance['title']);
        echo $before_widget;  
		if ( $title ) echo $before_title . $title . $after_title; 
		echo most_and_least_read_posts($instance, ' DESC ').$after_widget;
    }

    /** @see WP_Widget::update */
    function update($new_instance, $old_instance) {				
	$instance = $old_instance;
	$instance['title'] = strip_tags($new_instance['title']);
	$instance['posts_number'] = strip_tags($new_instance['posts_number']);
	$instance['words_excluded'] = strip_tags($new_instance['words_excluded']);
        return $instance;
    }

    /** @see WP_Widget::form */
    function form($instance) {
		if (empty($instance)) {
			$instance['title'] = 'Most Read Posts';
			$instance['posts_number'] = 5;
			$instance['words_excluded'] = '';
		}					
        $title = esc_attr($instance['title']);
        $posts_number = esc_attr($instance['posts_number']);
        $words_excluded = esc_attr($instance['words_excluded']);
        ?>
         <p>
          <label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title:'); ?></label> 
          <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo $title; ?>" />
        </p>
         <p>
          <label for="<?php echo $this->get_field_id('posts_number'); ?>"><?php _e('Number of posts to show:'); ?></label> 
          <input class="widefat" id="<?php echo $this->get_field_id('posts_number'); ?>" name="<?php echo $this->get_field_name('posts_number'); ?>" type="text" value="<?php echo $posts_number; ?>" />
        </p>
         <p>
          <label for="<?php echo $this->get_field_id('words_excluded'); ?>"><?php _e('Exclude post whose title contains any of these words (comma separated):'); ?></label> 
          <input class="widefat" id="<?php echo $this->get_field_id('words_excluded'); ?>" name="<?php echo $this->get_field_name('words_excluded'); ?>" type="text" value="<?php echo $words_excluded; ?>" />
        </p>
        <?php 
    }

} // class MostReadPostsWidget


// register widget
add_action('widgets_init', create_function('', 'return register_widget("MostReadPostsWidget");'));
add_action('widgets_init', create_function('', 'return register_widget("LeastReadPostsWidget");'));
