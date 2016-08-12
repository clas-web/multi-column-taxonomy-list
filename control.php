<?php

require_once( __DIR__.'/widget-shortcode-control.php' );

/**
 * The MultiColumnTaxonomyList_WidgetShortcodeControl class for the "Multi-Column Taxonomy List" plugin.
 * Derived from the official WP RSS widget.
 * 
 * Shortcode Example:
 * [multi_column_taxonomy_list title="Title" taxonomy="category" columns="3" show_post_list="yes"]
 * 
 * @package    clas-buttons
 * @author     Crystal Barton <atrus1701@gmail.com>
 */
if( !class_exists('MultiColumnTaxonomyList_WidgetShortcodeControl') ):
class MultiColumnTaxonomyList_WidgetShortcodeControl extends WidgetShortcodeControl
{
	
	/**
	 * Constructor.
	 * Setup the properties and actions.
	 */
	public function __construct()
	{
		$widget_ops = array(
			'description'	=> '',
		);
		
		parent::__construct( 'multi-column-taxonomy-list', 'Multi-Column Taxonomy List', $widget_ops );
	}
	
	
	/**
	 * Enqueues the scripts or styles needed for the control in the site frontend.
	 */
	public function enqueue_scripts()
	{
		wp_enqueue_style( 'multi-column-taxonomy-list', MCTL_PLUGIN_URL . '/style.css', false, MCTL_PLUGIN_VERSION );
	}
	
	
	/**
	 * Output the widget form in the admin.
	 * Use this function instead of form.
	 * @param   array   $options  The current settings for the widget.
	 */
	public function print_widget_form( $options )
	{
		$options = $this->merge_options( $options );
		extract( $options );
		
		?>

		<p>
		<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title:' ); ?></label> 
		<br/>
		<input id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>" class="widefat">
		<br/>
		</p>
		
		<p>
		<label for="<?php echo $this->get_field_id( 'taxonomy' ); ?>"><?php _e( 'Taxonomy:' ); ?></label>
		<br/>
		<?php foreach( $all_taxonomies as $tax ): ?>
			<?php if( in_array($tax->name, $exclude_taxonomies) ) continue; ?>
			<input type="radio" name="<?php echo $this->get_field_name( 'taxonomy' ); ?>" value="<?php echo esc_attr( $tax->name ); ?>" <?php echo ( in_array($tax->name, $taxonomies) ? 'checked' : '' ); ?> />
			<?php echo $tax->label; ?>
			<br/>
		<?php endforeach; ?>
		</p>
		
		<p>
		<label for="<?php echo $this->get_field_id( 'columns' ); ?>"><?php _e( 'Number of Columns:' ); ?></label>
		<br/>
		<select name="<?php echo $this->get_field_name( 'columns' ); ?>">
		<?php for( $i = 0; $i < 10; $i++ ): ?>
			<option value="<?php echo ($i+1); ?>"><?php echo ($i+1); ?></option>
		<?php endfor; ?>
		</select>
		</p>
		
		<p>
		<input type="hidden" name="<?php echo $this->get_field_name( 'show_post_count' ); ?>" value="no" />
		<input type="checkbox" name="<?php echo $this->get_field_name( 'show_post_count' ); ?>" value="yes" <?php checked($show_post_count, 'yes'); ?> />
		Show Post Count
		</p>		

		<p>
		<input type="hidden" name="<?php echo $this->get_field_name( 'hide_empty_items' ); ?>" value="no" />
		<input type="checkbox" name="<?php echo $this->get_field_name( 'hide_empty_items' ); ?>" value="yes" <?php checked($hide_empty_items, 'yes'); ?> />
		Hide Empty Items
		</p>		

		<p>
		<input type="hidden" name="<?php echo $this->get_field_name( 'show_post_list' ); ?>" value="no" />
		<input type="checkbox" name="<?php echo $this->get_field_name( 'show_post_list' ); ?>" value="yes" <?php checked($show_post_list, 'yes'); ?> />
		Show Post List
		</p>
		
		<?php
	}
	
	
	/**
	 * Get the default settings for the widget or shortcode.
	 * @return  array  The default settings.
	 */
	public function get_default_options()
	{
		$defaults = array();

		// title
		$defaults['title'] = '';

		// taxonomy types
		$defaults['all_taxonomies'] = get_taxonomies( array(), 'objects' );
		$defaults['exclude_taxonomies'] = array( 'nav-menu', 'link-category', 'post-format' );
		$defaults['taxonomy'] = 'categories';

		// minimum count
		$defaults['columns'] = 3;

		$defaults['show_post_list'] = 'no';
		$defaults['hide_empty_items'] = 'no';
		$defaults['show_post_count'] = 'no';
		
		return $defaults;
	}
	
	
	/**
	 * Process options from the database or shortcode.
	 * Designed to convert options from strings or sanitize output.
	 * @param   array   $options  The current settings for the widget or shortcode.
	 * @return  array   The processed settings.
	 */
	public function process_options( $options )
	{
		if( array_key_exists('columns', $options) )
		{
			$options['columns'] = intval( $options['columns'] );
		
			if( $options['columns'] < 1 )
				$options['columns'] = 1;
		
			if( $options['columns'] > 10 )
				$options['columns'] = 10;
		}
		
		return $options;
	}
	
	
	/**
	 * Echo the widget or shortcode contents.
	 * @param   array  $options  The current settings for the control.
	 * @param   array  $args     The display arguments.
	 */
	public function print_control( $options, $args = null )
	{
		extract( $options );
		
		$terms_list = array();
		$error = null;
		
		if( ! empty( $taxonomy ) )
		{
			$terms_list = get_terms(
				array(
					'taxonomy' => $taxonomy,
					'hide_empty' => false,
				)
			);
			
			if( is_a( $terms_list, 'WP_Error' ) ) {
				$error = $terms_list->get_error_message();
				$terms_list = array();
			} elseif( $hide_empty_items ) {
				$terms_list = array_filter( $terms_list, function($v) {
					return ( $v->count > 0 );
				} );
			}
		}
		
		if( $show_post_list === 'yes' )
		{
			$post_list_args = array(
				'post_per_page' => -1,
				'post_status' => 'publish',
			);
			
			foreach( $terms_list as $term )
			{
				$post_list_args[ $taxonomy ] = $term->name;
				$term->posts = get_posts( $post_list_args );
			}
		}
		
		$terms_per_column = (int) ( count( $terms_list ) / $columns );
		if( $terms_per_column * $columns !== count( $terms_list ) ) {
			$terms_per_column += 1;
		}
		
		
		echo $args['before_widget'];
		echo '<div id="multi-column-taxonomy-list-control-'.self::$index.'" class="wscontrol multi-column-taxonomy-list-control">';
		
		if( ! empty( $title ) ) {
			echo $args['before_title'] . $title . $args['after_title'];
		}
		
		echo '<div class="wscontrol-content columns-' . $columns . '">';
		
		if( $error ) {
			echo '<div class="wscontrol-error">' . $error . '</div>';
		} else {
			$column_count = 0;
			echo '<div class="column"><ul>';
			foreach( $terms_list as $term )
			{
				$link = get_term_link( $term, $term->taxonomy );
				echo '<li>';
				echo '<div class="term-title"><a href="' . esc_attr( $link ) . '" title="' . esc_attr( $term->name ) . '">' . $term->name;
				if( 'yes' == $show_post_count )
				{
					echo ' (' . $term->count . ')';
				}
				echo '</a></div>';
				
				if( ! empty( $term->posts ) )
				{
					echo '<ul>';
					foreach( $term->posts as $post )
					{
						$link = get_permalink( $post->ID );
						echo '<li>';
						echo '<div class="post-title"><a href="' . esc_attr( $link ) . '" title="' . esc_attr( $post->post_title ) . '">' . $post->post_title . '</a></div>';
						echo '</li>';
					}
					echo '</ul>';
				}
				echo '</li>';
				
				$column_count++;
				if( $terms_per_column == $column_count ) {
					echo '</ul></div><div class="column"><ul>';
					$column_count = 0;
				}
			}
			echo '</ul></div>';
		}
		
		echo '</div>';
		
		echo '</div>';
		echo $args['after_widget'];		
	}
}
endif;

