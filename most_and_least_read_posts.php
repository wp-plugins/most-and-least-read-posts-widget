<?php
/*
Plugin Name: Most and Least Read Posts Widget
Plugin URI: http://www.whiletrue.it/
Description: Provide two widgets, showing lists of the most and reast read posts.
Author: WhileTrue
Version: 2.1.3
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

add_filter('plugin_action_links', 'most_and_least_read_posts_add_settings_link', 10, 2 );
add_action('admin_menu', 'most_and_least_read_posts_menu');

add_filter('manage_posts_columns', 'most_and_least_read_posts_add_column');
add_action('manage_posts_custom_column', 'most_and_least_read_posts_custom_columns', 10, 2);


function most_and_least_read_posts_add_column($columns) {
    return array_merge($columns, array('hits' => __('Hits')));
}


function most_and_least_read_posts_custom_columns( $column, $post_id ) {
	if ( $column == 'hits' ) {
		echo most_and_least_read_posts_get_hits ($post_id);
	}
}


function most_and_least_read_posts_get_hits ($post_id) {
	if (!is_numeric($post_id)) {
		return 0;
	}
	$meta_key = 'custom_total_hits';
	$custom_field_total_hits = get_post_meta($post_id, $meta_key, true);
	$total_hits = (is_numeric($custom_field_total_hits)) ? (int)$custom_field_total_hits : 0;
	return $total_hits;
}

function most_and_least_read_posts_menu() {
	add_options_page('Most and Least Read Posts Options', 'Most read posts', 'manage_options', 'most_and_least_read_posts_options', 'most_and_least_read_posts_options');
}


function most_and_least_read_posts_add_settings_link($links, $file) {
	static $this_plugin;
	if (!$this_plugin) $this_plugin = plugin_basename(__FILE__);
 
	if ($file == $this_plugin){
		$settings_link = '<a href="admin.php?page=most_and_least_read_posts_options">'.__("Settings").'</a>';
		array_unshift($links, $settings_link);
	}
	return $links;
} 


function most_and_least_read_posts_update ($content) {

	// ONLY APPLIES TO SINGLE POSTS

	if (!is_single()) {
		return $content;
	}

	$post_id = get_the_ID();
	$total_hits = most_and_least_read_posts_get_hits ($post_id);

	// SKIP IF USER IS ADMIN (AVOID INFLATING HITS)

	if ( !current_user_can( 'manage_options' ) ) {
	
		// AVOID THE MOST COMMON WEB SPIDERS
	
		$spiders = array(
			'Googlebot', 'Yammybot', 'Openbot', 'Yahoo', 'Slurp', 'msnbot',
			'ia_archiver', 'Lycos', 'Scooter', 'AltaVista', 'Teoma', 'Gigabot'
		);
		foreach ($spiders as $spider) {
			if (preg_match('/'.$spider.'/i', $_SERVER['HTTP_USER_AGENT'])) {
				return $content;
			}
		}

		// UPDATE HITS

		$total_hits += 1;
		$meta_key = 'custom_total_hits';
		update_post_meta($post_id, $meta_key, str_pad($total_hits,9,0,STR_PAD_LEFT)); 
	}

	//GET ARRAY OF STORED VALUES
	$option = most_and_least_read_posts_get_options_stored();

	// CHECK IF HAS TO SHOW HITS
	$out = '';
	if ($option['show_hits_in_post']) {
		$out = '<p style="'.$option['css_style'].'">'
			.$option['text_shown_before'].$total_hits.$option['text_shown_after']
			.'</p>';
	
		if ($option['position']=='both') {
			return $out.$content.$out;
		} else if ($option['position']=='below') {
			return $content.$out;
		} else {
			return $out.$content;
		}
	}

	return $content;
}


function most_and_least_read_posts ($instance, $order) {
	global $wpdb, $table_prefix;

	$sql_esc = '';
	if ($instance['words_excluded']!='') {
		$excludes = array_filter(explode(',',$instance['words_excluded']));
		$sql_esc_arr = array();
		foreach($excludes as $val) {
			$sql_esc_arr[] = " p.post_title not like '%".$val."%' ";
		}
		$sql_esc = " and ".implode(" and ", $sql_esc_arr)." ";
	}
	
	$days_ago = (is_numeric($instance['days_ago'])) ? $instance['days_ago'] : 365;
	$min_date = date('Y-m-d', mktime(4,0,0,date('m'),date('d')-$days_ago,date('Y')));

	$sql_wpml = '';
	if (defined("ICL_LANGUAGE_CODE") and ICL_LANGUAGE_CODE!='') {  //if WPML is active
		$sql_wpml = " JOIN ".$table_prefix."icl_translations as t on (t.element_id = p.ID and t.language_code = '".ICL_LANGUAGE_CODE."') ";
	}
	
	$sql = " select DISTINCT p.ID, p.post_title, m.meta_value
		FROM $wpdb->postmeta as m
			LEFT JOIN $wpdb->posts as p on (m.post_id = p.ID)
			".$sql_wpml."
		WHERE p.post_status = 'publish'
			and p.post_type = 'post'
			and m.meta_key = 'custom_total_hits'
			and p.post_date >= '$min_date'
			$sql_esc
		ORDER BY m.meta_value $order
		LIMIT 0, ".$instance['posts_number'];
	        
	$output = $wpdb->get_results($sql);

	$out = '';
	if ($output) {
		foreach ($output as $line) {
			$hits_text = ($instance['show_hits_text']!='') ? ' '.$instance['show_hits_text'] : '';
			$hits = ($instance['show_hits']) ? ' ('.number_format((int)$line->meta_value).$hits_text.')' : '';
		
			$media = '';
			if ($instance['show_thumbs']) {
				$media = '';
				// TRY TO USE THE THUMBNAIL, OTHERWHISE TRY TO USE THE FIRST ATTACHMENT
				if ( function_exists('has_post_thumbnail') and has_post_thumbnail($line->ID) ) {
					$post_thumbnail_id = get_post_thumbnail_id($line->ID);
					$media = wp_get_attachment_image($post_thumbnail_id, 'thumbnail', false);
				}
				// IF NO MEDIA IS FOUND, LOOK FOR AN ATTACHMENT (THUMBNAIL)
				if ($media=='') {
					$args = array(
						'post_type'   => 'attachment',
						'numberposts' => 1,
						'post_status' => null,
						'post_parent' => $line->ID
						);

					$attachments = get_posts( $args );

					if ( $attachments ) {
						$attachment = $attachments[0];
						$media = wp_get_attachment_image($attachment->ID, 'thumbnail', false);
					}
				}
			}		
		  $out .=  '
        <li><a title="'.str_replace("'","&apos;", $line->post_title).'" href="'.get_permalink($line->ID).'">'
          .$media.$line->post_title
					.'</a>
					<span class="most_and_least_read_posts_hits">'.$hits.'</span>
				</li>';
		}   
	} else {
		$out .= '<li>'.__('No results available', 'least_read_posts').'</li>';
	}      
	return '<ul class="mlrp_ul">'.$out.'</ul>
		<div style="clear:both;"></div>';
}


function most_and_least_read_posts_options () {

	$option_name = 'most_and_least_read_posts';

	//must check that the user has the required capability 
	if (!current_user_can('manage_options')) {
		wp_die( __('You do not have sufficient permissions to access this page.') );
	}

	$out = '';
	
	// See if the user has posted us some information
	if( isset($_POST['most_and_least_read_posts_position'])) {
		$option = array();

		$option['show_hits_in_post'] = (isset($_POST[$option_name.'_show_hits_in_post']) and $_POST[$option_name.'_show_hits_in_post']=='on') ? true : false;
		$option['position'] = esc_html($_POST[$option_name.'_position']);
		$option['text_shown_before'] = esc_html($_POST[$option_name.'_text_shown_before']);
		$option['text_shown_after'] = esc_html($_POST[$option_name.'_text_shown_after']);
		$option['css_style'] = esc_html($_POST[$option_name.'_css_style']);
		
		update_option($option_name, $option);
		// Put a settings updated message on the screen
		$out .= '<div class="updated"><p><strong>'.__('Settings saved.', 'menu-test' ).'</strong></p></div>';
	}
	
	//GET ARRAY OF STORED VALUES
	$option = most_and_least_read_posts_get_options_stored();
	
	$sel_above = ($option['position']=='above') ? 'selected="selected"' : '';
	$sel_below = ($option['position']=='below') ? 'selected="selected"' : '';
	$sel_both  = ($option['position']=='both' ) ? 'selected="selected"' : '';

	$show_hits_in_post = ($option['show_hits_in_post']) ? 'checked="checked"' : '';

	// SETTINGS FORM

	$out .= '
	<style>
	#most_and_least_read_posts_form h3 { cursor: default; }
	#most_and_least_read_posts_form td { vertical-align:top; padding-bottom:15px; }
	</style>
	
	<div class="wrap">
	<h2>'.__( 'Most and least read posts', 'menu-test' ).'</h2>
	<div id="poststuff" style="padding-top:10px; position:relative;">

	<div style="float:left; width:74%; padding-right:1%;">

		<form id="most_and_least_read_posts_form" name="form1" method="post" action="">

		<div class="postbox">
		<h3>'.__("General options", 'menu-test' ).'</h3>
		<div class="inside">
			<table>
			<tr><td style="width:130px;">'.__("Show hits in posts", 'menu-test' ).':</td>
			<td><input type="checkbox" name="'.$option_name.'_show_hits_in_post" '.$show_hits_in_post.' />
			</td></tr>
			<tr><td>'.__("Position", 'menu-test' ).':</td>
			<td><select name="'.$option_name.'_position">
				<option value="above" '.$sel_above.' > '.__('only above the post', 'menu-test' ).'</option>
				<option value="below" '.$sel_below.' > '.__('only below the post', 'menu-test' ).'</option>
				<option value="both"  '.$sel_both.'  > '.__('above and below the post', 'menu-test' ).'</option>
				</select>
			</td></tr>
			<tr><td>'.__("Text shown", 'menu-test' ).':</td>
			<td>
				<input type="text" name="'.$option_name.'_text_shown_before" value="'.stripslashes($option['text_shown_before']).'" size="25">
				(number)
				<input type="text" name="'.$option_name.'_text_shown_after" value="'.stripslashes($option['text_shown_after']).'" size="25">
				<br />
				<span class="description">'.__("text added before and after the number, to form a standard phrase, 
				e.g. ' This post has been read (number) times'.
				Remember to leave a blank space or a puntuaction mark after the first and before the second text.", 'menu-test' ).'</span>
			</td></tr>
			<tr><td>'.__("CSS Style", 'menu-test' ).':</td>
			<td>
				<input type="text" name="'.$option_name.'_css_style" value="'.stripslashes($option['css_style']).'" size="50"><br />
				<span class="description">'.__("optional style applied to the paragraph; insert individual rules followed by semicolons.", 'menu-test' ).'</span>
			</td></tr>
			</table>
		</div>
		</div>
		
		<p class="submit">
			<input type="submit" name="Submit" class="button-primary" value="'.esc_attr('Save Changes').'" />
		</p>

		</form>

	</div>
	
	<div style="float:right; width:25%;">'
		.most_and_least_read_posts_box_content('PremiumPress Shopping Cart', '
			<a target="_blank" href="https://secure.avangate.com/order/product.php?PRODS=2929632&amp;QTY=1&amp;AFFILIATE=26764&amp;AFFSRC=really_simple_share_plugin">
				<img border="0" src="http://shopperpress.com/inc/images/banners/180x150.png" style="display: block; margin-left: auto; margin-right: auto;">
			</a>
		')
		.most_and_least_read_posts_box_content('Really simple, isn\'t it?', '
			Most of the actual plugin features were requested by users and developed for the sake of doing it.<br /><br />
			If you want to be sure this passion lasts centuries, please consider donating some cents!<br /><br />
			<div style="text-align: center;">
			<form method="post" action="https://www.paypal.com/cgi-bin/webscr">
			<input value="_s-xclick" name="cmd" type="hidden">
			<input value="-----BEGIN PKCS7-----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-----END PKCS7----- " name="encrypted" type="hidden"> 
			<input alt="PayPal - The safer, easier way to pay online!" name="submit" border="0" src="https://www.paypalobjects.com/WEBSCR-640-20110306-1/en_US/i/btn/btn_donateCC_LG.gif" type="image"> 
			<img height="1" width="1" src="https://www.paypalobjects.com/WEBSCR-640-20110306-1/it_IT/i/scr/pixel.gif" border="0"> 
			</form>
			</div>
		')
		.most_and_least_read_posts_box_content('News by WhileTrue', most_and_least_read_posts_feed())
	.'</div>

	</div>
	</div>
	';
	echo $out; 
}


function most_and_least_read_posts_feed () {
	$feedurl = 'http://www.whiletrue.it/feed/';
	$select = 8;

	$rss = fetch_feed($feedurl);
	if (!is_wp_error($rss)) { // Checks that the object is created correctly
		$maxitems = $rss->get_item_quantity($select);
		$rss_items = $rss->get_items(0, $maxitems);
	}
	if (!empty($maxitems)) {
		$out .= '
			<div class="rss-widget">
				<ul>';
    foreach ($rss_items as $item) {
			$out .= '
						<li><a class="rsswidget" href="'.$item->get_permalink().'">'.$item->get_title().'</a> 
							<span class="rss-date">'.date_i18n(get_option('date_format') ,strtotime($item->get_date('j F Y'))).'</span></li>';
		}
		$out .= '
				</ul>
			</div>';
	}
	return $out;
}


function most_and_least_read_posts_box_content ($title, $content) {
	if (is_array($content)) {
		$content_string = '<table>';
		foreach ($content as $name=>$value) {
			$content_string .= '<tr>
				<td style="width:130px;">'.__($name, 'menu-test' ).':</td>	
				<td>'.$value.'</td>
				</tr>';
		}
		$content_string .= '</table>';
	} else {
		$content_string = $content;
	}

	$out = '
		<div class="postbox">
			<h3>'.__($title, 'menu-test' ).'</h3>
			<div class="inside">'.$content_string.'</div>
		</div>
		';
	return $out;
}


function most_and_least_read_posts_get_options_stored () {
	//GET ARRAY OF STORED VALUES
	$option = get_option('most_and_least_read_posts');
	 
	if ($option===false) {
		//OPTION NOT IN DATABASE, SO WE INSERT DEFAULT VALUES
		$option = array();
		$option['position'] = 'above';
		$option['show_hits_in_post'] = false;
		$option['text_shown_before'] = 'This post has already been read ';
		$option['text_shown_after'] = ' times!';
		$option['css_style'] = 'font-style:italic; font-size:0.8em;';
		add_option('most_and_least_read_posts', $option);
	}
	return $option;
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
		$instance['days_ago'] = strip_tags($new_instance['days_ago']);
		$instance['show_hits'] = ($new_instance['show_hits']=='on') ? true : false;
		$instance['show_hits_text'] = strip_tags($new_instance['show_hits_text']);
        return $instance;
    }

    /** @see WP_Widget::form */
    function form($instance) {
			if (empty($instance)) {
				$instance['title'] = 'Least Read Posts';
				$instance['posts_number'] = 5;
				$instance['words_excluded'] = '';
				$instance['show_hits'] = false;
				$instance['show_hits_text'] = 'views';
			}					
		$title = esc_attr($instance['title']);
		$posts_number = esc_attr($instance['posts_number']);
		$words_excluded = esc_attr($instance['words_excluded']);
		$days_ago = is_numeric($instance['days_ago']) ? esc_attr($instance['days_ago']) : 365;
		$show_hits = ($instance['show_hits']) ? 'checked="checked"' : '';
		$show_hits_text = esc_attr($instance['show_hits_text']);
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
         <p>
          <label for="<?php echo $this->get_field_id('days_ago'); ?>"><?php _e('Look back X days ago:'); ?></label> 
          <input class="widefat" id="<?php echo $this->get_field_id('days_ago'); ?>" name="<?php echo $this->get_field_name('days_ago'); ?>" type="text" value="<?php echo $days_ago; ?>" />
        </p>
         <p>
          <input id="<?php echo $this->get_field_id('show_hits'); ?>" name="<?php echo $this->get_field_name('show_hits'); ?>" type="checkbox" <?php echo $show_hits; ?> />
          <label for="<?php echo $this->get_field_id('show_hits'); ?>"><?php _e('Show post hits'); ?></label> 
        </p>
         <p>
          <label for="<?php echo $this->get_field_id('show_hits_text'); ?>"><?php _e('Text to append to the post hits<br />(e.g. "views")'); ?></label> 
          <input class="widefat" id="<?php echo $this->get_field_id('show_hits_text'); ?>" name="<?php echo $this->get_field_name('show_hits_text'); ?>" type="text" value="<?php echo $show_hits_text; ?>" />
        </p>
        <p style="text-align:center; font-weight:bold;">
          <?php echo __('Do you like it? I\'m supporting it, please support me!', 'most-and-least-read-posts-widget') ?><br />
          <a href="https://www.paypal.com/cgi-bin/webscr?cmd=_donations&business=giu%40formikaio%2eit&item_name=WhileTrue&currency_code=EUR&bn=PP%2dDonationsBF%3abtn_donate_LG%2egif%3aNonHosted" target="_blank">
         	  <img alt="PayPal - The safer, easier way to pay online!" src="https://www.paypalobjects.com/en_US/i/btn/btn_donate_LG.gif" > 
          </a>
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
		$instance['days_ago'] = strip_tags($new_instance['days_ago']);
		$instance['show_thumbs'] = ($new_instance['show_thumbs']=='on') ? true : false;
		$instance['show_hits']   = ($new_instance['show_hits']=='on'  ) ? true : false;
		$instance['show_hits_text'] = strip_tags($new_instance['show_hits_text']);
        return $instance;
    }

    /** @see WP_Widget::form */
    function form($instance) {
			if (empty($instance)) {
				$instance['title'] = 'Most Read Posts';
				$instance['words_excluded'] = '';
				$instance['show_thumbs'] = false;
				$instance['show_hits']   = false;
				$instance['show_hits_text'] = 'views';
			}					
			$title = esc_attr($instance['title']);
			$posts_number = is_numeric($instance['posts_number']) ? esc_attr($instance['posts_number']) : 5;
			$words_excluded = esc_attr($instance['words_excluded']);
			$days_ago = is_numeric($instance['days_ago']) ? esc_attr($instance['days_ago']) : 365;
			$show_thumbs = ($instance['show_thumbs']) ? 'checked="checked"' : '';
			$show_hits   = ($instance['show_hits']  ) ? 'checked="checked"' : '';
			$show_hits_text = esc_attr($instance['show_hits_text']);
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
          <label for="<?php echo $this->get_field_id('words_excluded'); ?>"><?php _e('Exclude post if title contains any of these words (comma separated):'); ?></label> 
          <input class="widefat" id="<?php echo $this->get_field_id('words_excluded'); ?>" name="<?php echo $this->get_field_name('words_excluded'); ?>" type="text" value="<?php echo $words_excluded; ?>" />
        </p>
         <p>
          <label for="<?php echo $this->get_field_id('days_ago'); ?>"><?php _e('Look back X days ago:'); ?></label> 
          <input class="widefat" id="<?php echo $this->get_field_id('days_ago'); ?>" name="<?php echo $this->get_field_name('days_ago'); ?>" type="text" value="<?php echo $days_ago; ?>" />
        </p>
         <p>
          <input id="<?php echo $this->get_field_id('show_thumbs'); ?>" name="<?php echo $this->get_field_name('show_thumbs'); ?>" type="checkbox" <?php echo $show_thumbs; ?> />
          <label for="<?php echo $this->get_field_id('show_thumbs'); ?>"><?php _e('Show post thumbs'); ?></label> 
        </p>
         <p>
          <input id="<?php echo $this->get_field_id('show_hits'); ?>" name="<?php echo $this->get_field_name('show_hits'); ?>" type="checkbox" <?php echo $show_hits; ?> />
          <label for="<?php echo $this->get_field_id('show_hits'); ?>"><?php _e('Show post hits'); ?></label> 
        </p>
         <p>
          <label for="<?php echo $this->get_field_id('show_hits_text'); ?>"><?php _e('Text to append to the post hits (e.g. "views")'); ?></label> 
          <input class="widefat" id="<?php echo $this->get_field_id('show_hits_text'); ?>" name="<?php echo $this->get_field_name('show_hits_text'); ?>" type="text" value="<?php echo $show_hits_text; ?>" />
        </p>
        <p style="text-align:center; font-weight:bold;">
          <?php echo __('Do you like it? I\'m supporting it, please support me!', 'most-and-least-read-posts-widget') ?><br />
          <a href="https://www.paypal.com/cgi-bin/webscr?cmd=_donations&business=giu%40formikaio%2eit&item_name=WhileTrue&currency_code=EUR&bn=PP%2dDonationsBF%3abtn_donate_LG%2egif%3aNonHosted" target="_blank">
         	  <img alt="PayPal - The safer, easier way to pay online!" src="https://www.paypalobjects.com/en_US/i/btn/btn_donate_LG.gif" > 
          </a>
        </p>
        <?php 
    }

} // class MostReadPostsWidget


// register widget
add_action('widgets_init', create_function('', 'return register_widget("MostReadPostsWidget");'));
add_action('widgets_init', create_function('', 'return register_widget("LeastReadPostsWidget");'));
