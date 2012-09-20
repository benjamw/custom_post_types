<?php

/**********************************************************\
 *
 *    EXAMPLE CUSTOM POST TYPE
 *
\**********************************************************/

class CPTExample extends CustomPostType
{

	/** var $post_type
	 *
	 *		this is the post type name and
	 *		should be set to something unique
	 *
	 * @var string
	 * @required
	 */
	protected $post_type = 'example';


	/** var $post_slug
	 *
	 *		this is the slug used in the URL when
	 *		accessing the archive or index pages
	 *
	 *		if this variable is missing/empty, it will be
	 *		set to $post_type with an 's' added on the end
	 *		if that form is incorrect, set the proper form here
	 *
	 * @var string
	 */
	protected $post_slug = 'example_classes';


	/** var $singular
	 *
	 *		this is the string used for the singular version
	 *		of the post type name in various places
	 *
	 *		if this variable is missing/empty, it will be
	 *		set to $post_type with every word capitalized
	 *		if that form is incorrect, set the proper form here
	 *
	 * @var string
	 */
	protected $singular = 'Example Class';


	/** var $plural
	 *
	 *		this is the string used for the plural version
	 *		of the post type name in various places
	 *
	 *		if this variable is missing/empty, it will be
	 *		set to $post_slug with every word capitalized
	 *		if that form is incorrect, set the proper form here
	 *
	 * @var string
	 */
	protected $plural = 'Example Classes';


	/** var $menu_title
	 *
	 *		this is the string used for the menu item
	 *
	 *		if this variable is missing/empty, it will be
	 *		set to the same value as $plural
	 *		if that form is incorrect, set the proper form here
	 *
	 * @var string
	 */
	protected $menu_title 'Ex. Class';


	/** var $defaults
	 *
	 *		this is the array that holds the default values for the CPT
	 *
	 *		see the CustomPostType class for a complete list of defaults
	 *
	 *		see http://codex.wordpress.org/Function_Reference/register_post_type
	 *			for an explanation of those values
	 *
	 * @var array
	 */
	protected $defaults = array(
			'menu_position' => 26,
			'capability_type' => 'post',
			'hierarchical' => false,
			'supports' => array(
				'title',
				'thumbnail',
				'editor',
				'page-attributes', // this adds page order and parent (if page and hierarchical)
			),
			'taxonomies' => array( ),
			'has_archive' => false, // we're gonna use a page for content
		);


	/** function register_meta_box_cb
	 *
	 *		this function is where all of the
	 *		add_meta_box calls and and script
	 *		registration calls should be placed
	 *
	 *		add_meta_box( $id, $title, $callback, $page, $context = 'advanced', $priority = 'default', $callback_args = null );
	 *
	 *		see http://codex.wordpress.org/Function_Reference/add_meta_box
	 *
	 * @param void
	 * @return void
	 */
	public function register_meta_box_cb( )
	{
		// if you need some javascript for your CPT form, place them here
		wp_deregister_script('jquery');
		wp_register_script('jquery',
			get_bloginfo('template_directory').'/js/jquery-1.6.4.min.js',
			array( ),
			'1.5.2');

		wp_enqueue_style('datepicker',
			get_bloginfo('template_directory').'/css/smoothness/jquery-ui-1.8.11.custom.css',
			array( ),
			'1.8.11',
			'screen');

		wp_register_script('datepicker',
			get_bloginfo('template_directory').'/js/jquery-ui-1.8.11.custom.min.js',
			array('jquery'),
			'1.8.11');
		wp_enqueue_script('datepicker');

		wp_register_script('date',
			get_bloginfo('template_directory').'/js/date.js',
			array('datepicker'),
			'1.0');
		wp_enqueue_script('date');

		// generate the custom form fields
		add_meta_box('meta_text', 'Text', array($this, 'meta_text'), $this->post_type, 'normal', 'high');
		add_meta_box('meta_textarea', 'Textarea', array($this, 'meta_textarea'), $this->post_type, 'normal', 'high');
		add_meta_box('meta_date', 'Datepicker', array($this, 'meta_date'), $this->post_type, 'normal', 'high');
		add_meta_box('meta_manual_date', 'Manual Date', array($this, 'meta_manual_date'), $this->post_type, 'normal', 'high');
		add_meta_box('meta_map_location', 'Map Location', array($this, 'meta_map_location'), $this->post_type, 'normal', 'high');
		add_meta_box('meta_related_examples', 'Related Examples', array($this, 'meta_related_examples'), $this->post_type, 'normal', 'high');
	}


	/** function meta_text
	 *
	 *		this is an example of a meta box callback function
	 *		tha contains a simple text field
	 *
	 * @param void
	 * @return void
	 */
	public function meta_text( )
	{
		$text = get_meta_value('text');

		?>

		<label for="text">Text</label><!-- the label is optional -->
		<input type="text" name="text" id="cpt_text" value="<?php echo $text; ?>" />

		<?php
	}


	/** function meta_textarea
	 *
	 *		this is an example of a meta box callback function
	 *		tha contains a simple textarea box
	 *
	 * @param void
	 * @return void
	 */
	public function meta_textarea( )
	{
		$comments = get_meta_value('comments');

		?>

		<label for="comments">Comments</label><!-- the label is optional -->
		<textarea name="comments" id="cpt_comments" cols="50" rows="5"><?php echo $comments; ?></textarea>

		<?php
	}


	/** function meta_date
	 *
	 *		this is an example of a meta box callback function
	 *		tha contains a date field with a datepicker
	 *
	 * @param void
	 * @return void
	 */
	public function meta_date( )
	{
		$start_date = get_meta_value('start_date');
		$end_date = get_meta_value('start_date');

		?>

		<label for="start_date">Start</label><!-- the label is optional, but because there are two, is recommended -->
		<input name="start_date" id="cpt_start_date" class="date" size="12" value="<?php echo $start_date; ?>" />

		<label for="end_date">End</label><!-- the label is optional, but because there are two, is recommended -->
		<input name="end_date" id="cpt_end_date" class="date" size="12" value="<?php echo $end_date; ?>" />

		<?php
	}


	/** function meta_manual_date
	 *
	 *		this is an example of a meta box callback function
	 *		tha contains manually set date fields
	 *
	 * @param void
	 * @return void
	 */
	public function meta_manual_date( )
	{
		$manual_date = get_meta_value('manual_date');

		echo make_time('manual_date', $manual_date, true, false, 200);
	}


	/** function meta_map_location
	 *
	 *		this is an example of a meta box callback function
	 *		tha contains manually set date fields
	 *
	 * @param void
	 * @return void
	 */
	public function meta_map_location( )
	{
		$map_location = get_meta_value('map_location');

		?>

		<label for="map_location">(Latitude, Longitude)</label><!-- the label is optional, but because it's descriptive, is recommended -->
		<input type="text" name="map_location" id="cpt_map_location" size="100" value="<?php echo $map_location; ?>" />

		<?php
	}


	/** function meta_related_examples
	 *
	 *		this is an example of a meta box callback function
	 *		tha contains a dropdown of related examples
	 *
	 * @param void
	 * @return void
	 */
	public function meta_related_examples( )
	{
		$examples = get_meta_array('related_examples');
		$values = $this->grab_hierarchical_array( );

		$options = make_select_options($examples, $values);

		?>

		<select name="related_examples[]" id="cpt_related_examples" multiple="multiple" size="10" style="height:auto;">
			<?php echo $options; ?>
		</select>

		<?php
	}


	/** function save_data
	 *
	 *		this is where all the custom data gets saved to the post
	 *
	 * @param void
	 * @return void
	 */
	public function save_data( )
	{
		global $post;

		// normal single value data
		update_post_meta($post->ID, 'comments', $_POST['comments']);
		update_post_meta($post->ID, 'map_location', $_POST['map_location']);

		// array data
		update_meta_array($post->ID, 'related_examples', $_POST['related_examples'], $delete_prev = true);

		// date/time data
		update_meta_time($post->ID, 'manual_date', $_POST['date']);

		// values that need some extra processing before saving
		$start_date = preg_replace('/(\d{2})-(\d{2})-(\d{4})/', '$3-$1-$2', $_POST['start_date']);
		$end_date = preg_replace('/(\d{2})-(\d{2})-(\d{4})/', '$3-$1-$2', $_POST['end_date']);

		update_post_meta($post->ID, 'start_date', $start_date);
		update_post_meta($post->ID, 'end_date', $end_date);
	}


	/** function edit_columns
	 *
	 *		this function tells WP what info to display on the admin index page
	 *
	 * @param void
	 * @return array columns to show
	 */
	public function edit_columns( )
	{
		$columns = array(
			'cb' => '<input type="checkbox" />', // always include this column
			'title' => 'Name', // this is the title of the post
			'text' => 'Text', // custom
			'dates' => 'Date', // custom
			'map_location' => 'Map Location', // custom
		);

		// this adds the publish date and status at the end of the row
		$columns['date'] = __( 'Date' );

		return $columns;
	}


	/** function sort_columns
	 *
	 *		this function tells WP which field to use when that
	 *		column's sort buttons are clicked
	 *
	 * @param array the list of columns
	 * @return array the new list of columns
	 */
	public function sort_columns($columns)
	{
		// basically just set each key and value to the custom column name
		// that you used in edit_columns( )
		$columns['text'] = 'text';
		$columns['dates'] = 'dates';
		$columns['map_location'] = 'map_location';

		return $columns;
	}


	/** function custom_columns
	 *
	 *		this function tells WP how to get the custom data
	 *		that is being shown on the admin index page
	 *
	 * @param string the column name
	 * @return void
	 */
	public function custom_columns($column)
	{
		global $post;

		$custom = get_post_custom($post->ID);

		// set each custom column name that you add in edit_columns
		// as a separate case and tell WP how to get that data
		// you don't need to include cb, title, or date
		switch ($column) {
			case 'text' :
				if ( ! empty($custom['text'][0])) {
					echo $custom['text'][0];
				}
				break;

			case 'dates' :
				if ( ! empty($custom['start_date'][0]) && ! empty($custom['end_date'][0])) {
					echo date('F j, Y', strtotime($custom['start_date'][0])).'&ndash;'.date('F j, Y', strtotime($custom['end_date'][0]));
				}
				break;

			case 'map_location' :
				if ( ! empty($custom['hours'][0])) {
					echo $custom['map_location'][0];
				}
				break;
		}
	}


	/** function fix_sort_columns
	 *
	 *		this function tells WP how to sort the columns when that
	 *		column's sort buttons are clicked
	 *
	 *		see http://codex.wordpress.org/Class_Reference/WP_Query#Custom_Field_Parameters
	 *		see http://codex.wordpress.org/Class_Reference/WP_Query#Order_.26_Orderby_Parameters
	 *
	 * @param array the sort args
	 * @return array the new sort args
	 */
	public function fix_sort_columns($args)
	{
		if (is_admin( ) && isset($args['orderby'])) {
			switch ($args['orderby']) {
				// just add an entry for each custom column into this switch statement
				// and set the args to tell WP how to find that data and how to sort it
				case 'text' :
					$args = array_merge($args, array(
						'meta_key' => 'text',
						'orderby' => 'meta_value',
					));
					break;

				case 'dates' :
					$args = array_merge($args, array(
						'meta_key' => 'start_date',
						'orderby' => 'meta_value',
					));
					break;

				case 'map_location' :
					$args = array_merge($args, array(
						'meta_key' => 'map_location',
						'orderby' => 'meta_value',
					));
					break;

				default :
					// do nothing
					break;
			}
		}

		return $args;
	}

} // end of CPTExample class

custom_post_type('CPTExample');

