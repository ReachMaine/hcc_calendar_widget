<?php

include('hcc_functions.php');

class hcc_list extends WP_Widget {


	public function __construct() {
		// widget actual processes
		parent::__construct(
			'hcc_list', // Base ID
			__('zigs calendar list Widget', 'text_domain'), // Name
			array( 'description' => __( 'A events list widget for multisite.', 'text_domain' ), ) // Args
		);
	}


	function clean_xhtml($string)
	{
		$string = ereg_replace("<[^>]*>", "", $string);
		$string = preg_replace("@<p[^>]*?>.*?
		@siu", '',$string);
		return $string;
	}


	public function widget( $args, $instance ) {
		// outputs the content of the widget

	 	global $wpdb;
		/* set up the args */
		$title = apply_filters( 'widget_title',$instance['title']);
		$blogid = ($instance['blogid']>0?$instance['blogid']:12);
		$limit_list = ($instance['number']>0?$instance['number']:1);
		$category  = $instance['category'];
		$towns = $instance['towns'];
		$tags =  $instance['tags'];
		$linktarget = $instance['moretarget'];
		$linkwords = $instance['moretitle'];


		$html = "";
		//$html = '<nav class="widget widget_hcclist" >';
		$html .=  $args['before_widget'];
 		//$html.= ($title!='')?'<h5 class="widget-title"><a href="'.$calendar_blog_url.'/events/">'.$title.'</a></h5>':'';
		if ( ! empty( $title ) ) {
				$html.= $args['before_title'] . $title . $args['after_title'];
		}
		
		if ( is_multisite() ) {
			$blog_details = get_blog_details($blogid);
				if ($blog_details) {
					$calendar_blog_url = $blog_details->siteurl;
				}
		}
 		/* $html .= '<p> blogid:'.$blogid.'</p>';
 		$html .= '<p> category:'.$category.'</p>';
 		$html .= '<p> towns:'.$towns.'</p>';
 		$html .= '<p> number:'.$limit_list.'</p>'; */


		$hcc_events = hcc_get_lastest($blogid, $limit_list, $category, $towns, $tags);
		if ($hcc_events) {
			$html .= '<ul class="hcc_event_list">';
			foreach ($hcc_events as $evt) {
				$html .=  '<li class="hcc_event"><a href="'.$calendar_blog_url.'/?p='.$evt->ID.'">';
				$html .= '<h4 class = "hcc_event_title">'.$evt->post_title.'</h4>';
				$html .= '<div class="hcc_event_venue">'.$evt->Venue;
				if ($evt->City) {
					$html .= ', '.$evt->City;
				}
				$html .= '</div>';
				$t_startDate = new DateTime($evt->startDate);
				$str_startDate = date_format( $t_startDate, 'D, M. j');
				$t_eventStartTime = new DateTime($evt->startTime);
				$str_eventStartTime = date_format($t_eventStartTime, 'g:ia');
				$html .= '<div class="hcc_event_datetime">'.$str_startDate.', '.$str_eventStartTime.'</div>';
				$html .= '</a></li>';
			}
			$html .= '</ul>';
		}
		if ($linkwords && $linktarget) {
			$html.= '<a class="hcc_link" href="'.$linktarget.'">'.$linkwords.'</a>';
		}

		//$html .= '</nav>';
		$html .= $args['after_widget'];

		echo $html;


	}


	private function string_limit_words($string, $word_limit)
	{
	  $words = explode(' ', $string, ($word_limit + 1));
	  if(count($words) > $word_limit)
	  array_pop($words);
	  return implode(' ', $words);
	}

 	public function form( $instance ) {
		// outputs the options form on admin
		$blogid = isset($instance['blogid']) ? esc_attr( $instance['blogid'] ) : 12;
		$title     = isset( $instance['title'] ) ? esc_attr( $instance['title'] ) : '';
		$number    = isset( $instance['number'] ) ? absint( $instance['number'] ) : 6;
		$category = isset($instance['category']) ? esc_attr( $instance['category'] ) : '';
		$towns = isset($instance['towns']) ? esc_attr( $instance['towns'] ) : '';
		$tags = isset($instance['tags']) ? esc_attr( $instance['tags'] ) : '';

		$linkwords = isset($instance['moretitle']) ? esc_attr( $instance['moretitle'] ) : '';
		$linktarget = isset($instance['moretarget']) ? esc_attr( $instance['moretarget'] ) : '';

		?>
		<p>
			<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title (optional):' ); ?></label>
			<input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>" />
		</p>
		<p>
        	<label for="<?php echo $this->get_field_id( 'blogid' ); ?>"><?php _e( 'Blog ID' ); ?></label>
			<input id="<?php echo $this->get_field_id( 'blogid' ); ?>" name="<?php echo $this->get_field_name( 'blogid' ); ?>" type="text" value="<?php echo $blogid; ?>" size="3" />
		</p>
		<p>
        	<label for="<?php echo $this->get_field_id( 'number' ); ?>"><?php _e( 'Number of events to show:' ); ?></label>
			<input id="<?php echo $this->get_field_id( 'number' ); ?>" name="<?php echo $this->get_field_name( 'number' ); ?>" type="text" value="<?php echo $number; ?>" size="3" />
		</p>
		<p>
    		<label for="<?php echo $this->get_field_id( 'category' ); ?>"><?php _e('Enter Categories IDs - comma separated:'); ?></label>
			<input class="widefat" id="<?php echo $this->get_field_id( 'category' ); ?>" name="<?php echo $this->get_field_name( 'category' ); ?>" type="text" value="<?php echo esc_attr( $category ); ?>" />
		</p>
	    <p>
	    	<label for="<?php echo $this->get_field_id( 'towns' ); ?>"><?php _e('Towns filter - comma separated:'); ?></label>
			<input class="widefat" id="<?php echo $this->get_field_id( 'towns' ); ?>" name="<?php echo $this->get_field_name( 'towns' ); ?>" type="text" value="<?php echo esc_attr( $towns ); ?>" />
		</p>
		<p>
	    	<label for="<?php echo $this->get_field_id( 'tags' ); ?>"><?php _e('tags filter - comma separated slugs:'); ?></label>
			<input class="widefat" id="<?php echo $this->get_field_id( 'tags' ); ?>" name="<?php echo $this->get_field_name( 'tags' ); ?>" type="text" value="<?php echo esc_attr( $tags ); ?>" />
		</p>

		<p>
				<label for="<?php echo $this->get_field_id( 'moretitle' ); ?>"><?php _e('link title:'); ?></label>
			<input class="widefat" id="<?php echo $this->get_field_id( 'moretitle' ); ?>" name="<?php echo $this->get_field_name( 'moretitle' ); ?>" type="text" value="<?php echo esc_attr( $linkwords ); ?>" />
		</p>
		<p>
				<label for="<?php echo $this->get_field_id( 'moretarget' ); ?>"><?php _e('link target:'); ?></label>
			<input class="widefat" id="<?php echo $this->get_field_id( 'moretarget' ); ?>" name="<?php echo $this->get_field_name( 'moretarget' ); ?>" type="text" value="<?php echo esc_attr( $linktarget ); ?>" />
		</p>
		<?php
	}

	public function update( $new_instance, $old_instance ) {
		// processes widget options to be saved

		$instance = array();
		$instance['title'] = strip_tags($new_instance['title']);
		$instance['blogid'] = strip_tags($new_instance['blogid']);
		$instance['number'] = (int) $new_instance['number'];
		$instance['category'] = strip_tags($new_instance['category']);
		$instance['towns'] = strip_tags($new_instance['towns']);
		$instance['tags'] = strip_tags($new_instance['tags']);
		$instance['moretitle'] = strip_tags($new_instance['moretitle']);
		$instance['moretarget'] = strip_tags($new_instance['moretarget']);
		return $instance;

	}
} /* end class */

?>
