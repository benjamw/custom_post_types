<?php
/*

There are a couple of methods to include new CPTs with this set of functions.

You can create a new class that extends the CustomPostType class and store it
in a file with the prefix of 'cpt.' and it will get auto-included and run by
the include_cpt_files( ) function in this file.  Just make sure to have the
custom_post_type('CPTExample'); call at the bottom of that file. Then just
include this file in your theme's functions.php file and you're done.

include 'custom_post_type.class.php';


Another method is to call the custom_post_type( ) function with the given
arguments as follows: (See the function arguments and options below)

custom_post_type('child', array(
			'menu_position' => 30,
			'capability_type' => 'post',
			'hierarchical' => false,
			'supports' => array(
				'title',
				'editor',
			),
			'taxonomies' => array( ),
			'has_archive' => false, // we're gonna use a page for content
		), 'children');

No class needed, just pass everything to the function and it does the rest.


HELPER FUNCTIONS INCLUDED:
---------------------------------------------------------------------
make_checkbox_list($name, $options, $values = array( ));
make_radio($name, $options, $values = array( ), $in_list = false);
make_select_options($options, $values = array( ));
make_time($name, $value = '0000-00-00 00:00:00', $incl_date = true, $incl_time = true, $tab_index = 0);

get_meta_value($name, $default = '');
get_meta_array($name, $default = array( ));

update_meta_array($post_id, $name, $data = array( ), $delete_prev = true);
update_meta_time($post_id, $name, $data = array( ));

my_round($val, $closest = 0);

*/

/**
 * Base Custom Post Type Class
 *
 * a heavily modified version of SD Register Post Type 1.3 by Matt Wiebe
 * http://somadesign.ca/
 *
 */
if ( ! class_exists('CustomPostType')) {

class CustomPostType
{

	/** var $post_type
	 *
	 *		this is the post type name and
	 *		should be set to something unique
	 *
	 * @var string
	 * @required
	 */
	protected $post_type = 'custom_post';


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
	protected $post_slug;


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
	protected $singular;


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
	protected $plural;


	/** var $singular_menu_title
	 *
	 *		this is the string used for the menu item
	 *
	 *		if this variable is missing/empty, it will be
	 *		set to the same value as $singular
	 *		if that form is incorrect, set the proper form here
	 *
	 * @var string
	 */
	protected $singular_menu_title;


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
	protected $menu_title;


	/** var $args
	 *
	 *		this is the array that holds the used values for the CPT
	 *
	 *		see the CustomPostType class for a complete list of defaults
	 *
	 *		see http://codex.wordpress.org/Function_Reference/register_post_type
	 *			for an explanation of those values
	 *
	 * @var array
	 */
	protected $args;


	/** var $defaults
	 *
	 *		this is the array that holds the default values for the CPT
	 *
	 *		see http://codex.wordpress.org/Function_Reference/register_post_type
	 *			for an explanation of these values
	 *
	 * @var array
	 */
	protected $defaults = array(
#		'labels' => array( ), // this can be overridden by setting it, but if it's empty, defaults will be used
#		'description' => '',
		'public' => true,
		'exclude_from_search' => false,
		'publicly_queryable' => true,
		'show_ui' => true,
		'show_in_nav_menus' => true,
		'show_in_menu' => true,
		'show_in_admin_bar' => true,
		'menu_position' => 20,
		'menu_icon' => null,
		'capability_type' => 'page',
#		'capabilities' => array( ),
		'hierarchical' => true,
		'supports' => array(
			'title',
			'editor',
#			'author',
			'thumbnail',
#			'excerpt',
#			'trackbacks',
#			'custom-fields',
#			'comments',
			'revisions',
			'page-attributes',
#			'post-formats',
		),
#		'register_meta_box_cb' => callback function that registers the admin meta boxes
		'taxonomies' => array(
			'category',
		),
		'has_archive' => true,
#		'permalink_epmask' => EP_PERMALINK,
		'rewrite' => true, /** array(
			'slug' => 'slug', // '/slug/[post_slug]/'
			'with_front' => true, // if your permalink structure is /blog/, then your links will be: false->/news/, true->/blog/news/
			'feeds' => true,
			'pages' => true,
			'ep_mask' => EP_PERMALINK,
		), //*/
		'query_var' => true,
		'can_export' => true,

# These are here for informational purposes, they can be overridden, but are not needed for CPTs themselves
#		'save_data' => callback function that saves the data
#		'edit_columns' => callback function tells WP what info to display on the admin index page
#		'sort_columns' => callback function tells WP which field to use when that column's sort buttons are clicked
#		'custom_columns' => callback function tells WP how to get the custom data that is being shown on the admin index page
#		'fix_sort_columns' => callback function tells WP how to sort the columns when that column's sort buttons are clicked
	);


	/** class constructor
	 *
	 *		this is where the class gets all setup and run
	 *
	 * @param string optional post type name
	 * @param array optional args
	 * @param string optional post slug
	 * @return CPT class object
	 */
	public function __construct($post_type = null, $args = array( ), $post_slug = false)
	{
		// generate/store our names
		if ($post_type) {
			$this->post_type = $post_type;
		}

		if (empty($this->post_slug)) {
			$this->post_slug = ( ! empty($post_slug)) ? $post_slug : $this->post_type.'s';
		}

		$this->set_defaults( );
		$this->args = array_merge($this->defaults, $args);

		// run the stuff
		$this->add_actions( );
		$this->add_filters( );
	}


	/** function get_post_type
	 *
	 *		returns the post type
	 *
	 * @param void
	 * @return string post type
	 */
	public function get_post_type( )
	{
		return $this->post_type;
	}


	/** function set_defaults
	 *
	 *		this function merges the defaults from any children classes,
	 *		incorporates the remaining label data into the defaults,
	 *		and sets any missing data to default values
	 *
	 * @param void
	 * @return void
	 */
	private function set_defaults( )
	{
		$cpt_vars = get_class_vars(__CLASS__);

		if (isset($cpt_vars['defaults']) && ! empty($cpt_vars['defaults'])) {
			$this->defaults = array_merge($cpt_vars['defaults'], $this->defaults);
		}

		$post_type = ucwords(str_replace(array('_', '-'), ' ', $this->post_type));
		$post_slug = ucwords(str_replace(array('_', '-'), ' ', $this->post_slug));

		$this->singular = ( ! empty($this->singular)) ? $this->singular : $post_type;
		$this->plural = ( ! empty($this->plural)) ? $this->plural : $post_slug;
		$this->singular_menu_title = ( ! empty($this->singular_menu_title)) ? $this->singular_menu_title : $this->singular;
		$this->menu_title = ( ! empty($this->menu_title)) ? $this->menu_title : $this->plural;

		$this->defaults['labels'] = ! empty($this->defaults['labels']) ? $this->defaults['labels'] : array(
			'name' => _x($this->plural, 'post type general name'), // Posts
			'singular_name' => _x($this->singular, 'post type singular name'), // Post
			'menu_name' => _x($this->menu_title, 'post type menu name'), // Posts (in menu)
			'name_admin_bar' => _x($this->singular, 'post type singular name'), // Post (in dropdown menu)
			'all_items' => __('All '.$this->menu_title), // All Posts (in menu)
			'add_new' => _x('Add New', strtolower($post_slug)), // Add New
			'add_new_item' => __('Add New '.$this->singular), // Add New Post
			'edit_item' => __('Edit '.$this->singular), // Edit Post
			'new_item' => __('New '.$this->singular), // New Post
			'view_item' => __('View '.$this->singular), // View Post
			'search_items' => __('Search '.$this->menu_title), // Search Posts
			'not_found' =>  __('No '.$this->plural.' found'), // No Posts found
			'not_found_in_trash' => __('No '.$this->plural.' found in Trash'), // No Posts found in Trash
			'parent_item_colon' => ':', // Parent Page: (hierarchical only)
		);

		$this->defaults['has_archive'] = (true == $this->defaults['has_archive']) ? $this->post_slug : $this->defaults['has_archive'];
		$this->defaults['rewrite'] = (true == $this->defaults['rewrite']) ? array('slug' => $this->post_slug) : $this->defaults['rewrite'];

		// make sure we haven't included both an admin_init argument and a register_meta_box_cb argument
		if ( ! empty($this->defaults['register_meta_box_cb']) && ! empty($this->defaults['admin_init'])) {
			trigger_error('Both a "register_meta_box_cb" and "admin_init" argument have been included. Only one or the other argument is allowed. The admin_init argument has been deprecated.', E_USER_WARNING);
			$this->defaults['register_meta_box_cb'] = $this->defaults['register_meta_box_cb'];
		}
		elseif ( ! empty($this->defaults['register_meta_box_cb'])) {
			$this->defaults['register_meta_box_cb'] = $this->defaults['register_meta_box_cb'];
		}
		elseif ( ! empty($this->defaults['admin_init'])) {
			$this->defaults['register_meta_box_cb'] = $this->defaults['admin_init'];
		}
		else {
			$this->defaults['register_meta_box_cb'] = array($this, 'register_meta_box_cb');
		}
	}


	/** function add_actions
	 *
	 *		adds the required actions to the WP hooks
	 *
	 * @param void
	 * @return void
	 */
	public function add_actions( )
	{
		add_action('init', array($this, 'register_post_type'));
		add_action('template_redirect', array($this, 'template_redirect'));
	}


	/** function add_filters
	 *
	 *		adds the required filters to the WP hooks
	 *
	 * @param void
	 * @return void
	 */
	public function add_filters( )
	{
//		add_filter('generate_rewrite_rules', array($this, 'add_rewrite_rules'));
		add_filter('template_include', array($this, 'template_include'));
		add_filter('body_class', array($this, 'body_classes'));

		// sortable columns
		add_filter('manage_edit-'.$this->post_type.'_sortable_columns', array($this, 'sort_columns'));
		add_filter('request', array($this, 'fix_sort_columns'));
	}


	/** function register_post_type
	 *
	 *		registers the new CPT with WP and edits hooks as needed
	 *
	 * @param void
	 * @return void
	 */
	public function register_post_type( )
	{
		register_post_type($this->post_type, $this->args);

		// if title and editor are not supported, we need to manually remove them
		if ( ! in_array('title', $this->args['supports'])) {
			remove_post_type_support($this->post_type, 'title');
		}

		if ( ! in_array('editor', $this->args['supports'])) {
			remove_post_type_support($this->post_type, 'editor');
		}

		add_filter('manage_edit-'.$this->post_type.'_columns', array($this, 'edit_columns'));
	}


	public function template_redirect( )
	{
		if (get_query_var('post_type') == $this->post_type) {
			global $wp_query;
			$wp_query->is_home = false;
		}
	}


	public function add_rewrite_rules( )
	{
		// don't know exactly what this does, but WP
		// was throwing errors looking for it
	}


	/** function template_include
	 *
	 *		tells WP where to find the template files
	 *
	 * @param string template
	 * @return string template
	 */
	public function template_include($template)
	{
		if (get_query_var('post_type') == $this->post_type) {
			if (is_single( )) {
				$single = locate_template(array(
					$this->post_type.'/single.php',
				));

				if ($single) {
					return $single;
				}
			}
			else { // loop
				$archive = locate_template(array(
					$this->post_type.'/index.php',
					$this->post_type.'.php',
				));

				if ($archive) {
					return $archive;
				}
			}
		}

		return $template;
	}


	/** function body_classes
	 *
	 *		tells WP what body class to add to the body
	 *
	 * @param string class
	 * @return string class
	 */
	public function body_classes($c)
	{
		if (get_query_var('post_type') === $this->post_type) {
			$c[] = $this->post_type;
			$c[] = 'type-'.$this->post_type;
		}
		return $c;
	}


	/** function register_meta_box_cb
	 *
	 *		this function is where all of the
	 *		add_meta_box calls and script
	 *		registration calls should be placed
	 *
	 *		add_meta_box( $id, $title, $callback, $page, $context = 'advanced', $priority = 'default', $callback_args = null );
	 *
	 *		see http://codex.wordpress.org/Function_Reference/add_meta_box
	 *
	 *		this is a placeholder for more in-depth child classes
	 *
	 * @param void
	 * @return void
	 */
	public function register_meta_box_cb( )
	{
		// placeholder

		// for backwards compatibility, run the admin_init function if it exists
		if (method_exists($this, 'admin_init')) {
			$this->admin_init( );
		}
	}


	/** function save_data
	 *
	 *		this is where all the custom data gets saved to the post
	 *
	 *		this is a placeholder for more in-depth child classes
	 *		or, alternatively, you can pass in a global function name into
	 *		the save_data argument
	 *
	 * @param void
	 * @return void
	 */
	public function save_data( )
	{
		// this is a placeholder for more in-depth child classes
		// or, alternatively, you can pass in a global function name into
		// the save_data argument

		if ( ! empty($this->args['save_data']) && function_exists($this->args['save_data'])) {
			return ${$this->args['save_data']}( );
		}
	}


	/** function edit_columns
	 *
	 *		this function tells WP what info to display on the admin index page
	 *
	 *		this is a placeholder for more in-depth child classes
	 *		or, alternatively, you can pass in a global function name into
	 *		the edit_columns argument
	 *
	 * @param void
	 * @return array columns to show
	 */
	public function edit_columns( )
	{
		if ( ! empty($this->args['edit_columns']) && function_exists($this->args['edit_columns'])) {
			${$this->args['edit_columns']}( );
		}
		else {
			$posts_columns = array();

			$posts_columns['cb'] = '<input type="checkbox" />';

			/* translators: manage posts column name */
			$posts_columns['title'] = _x( 'Title', 'column name' );

			if ( post_type_supports( $this->post_type, 'author' ) )
				$posts_columns['author'] = __( 'Author' );

			if ( empty( $this->post_type ) || is_object_in_taxonomy( $this->post_type, 'category' ) )
				$posts_columns['categories'] = __( 'Categories' );

			if ( empty( $this->post_type ) || is_object_in_taxonomy( $this->post_type, 'post_tag' ) )
				$posts_columns['tags'] = __( 'Tags' );

			$post_status = !empty( $_REQUEST['post_status'] ) ? $_REQUEST['post_status'] : 'all';
			if ( post_type_supports( $this->post_type, 'comments' ) && !in_array( $post_status, array( 'pending', 'draft', 'future' ) ) )
				$posts_columns['comments'] = '<div class="vers"><img alt="' . esc_attr__( 'Comments' ) . '" src="' . esc_url( admin_url( 'images/comment-grey-bubble.png' ) ) . '" /></div>';

			$posts_columns['date'] = __( 'Date' );

			$posts_columns = apply_filters( "manage_{$this->post_type}_posts_columns", $posts_columns );

			return $posts_columns;
		}
	}


	/** function sort_columns
	 *
	 *		this function tells WP which field to use when that
	 *		column's sort buttons are clicked
	 *
	 *		this is a placeholder for more in-depth child classes
	 *		or, alternatively, you can pass in a global function name into
	 *		the sort_columns argument
	 *
	 * @param array the list of columns
	 * @return array the new list of columns
	 */
	public function sort_columns($columns)
	{
		if ( ! empty($this->args['sort_columns']) && function_exists($this->args['sort_columns'])) {
			return ${$this->args['sort_columns']}($columns);
		}

		return $columns;
	}


	/** function custom_columns
	 *
	 *		this function tells WP how to get the custom data
	 *		that is being shown on the admin index page
	 *
	 *		this is a placeholder for more in-depth child classes
	 *		or, alternatively, you can pass in a global function name into
	 *		the custom_columns argument
	 *
	 * @param string the column name
	 * @return void
	 */
	public function custom_columns($column)
	{
		if ( ! empty($this->args['custom_columns']) && function_exists($this->args['custom_columns'])) {
			return ${$this->args['custom_columns']}($column);
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
	 *		this is a placeholder for more in-depth child classes
	 *		or, alternatively, you can pass in a global function name into
	 *		the fix_sort_columns argument
	 *
	 * @param array the sort args
	 * @return array the new sort args
	 */
	public function fix_sort_columns($args)
	{
		if ( ! empty($this->args['fix_sort_columns']) && function_exists($this->args['fix_sort_columns'])) {
			return ${$this->args['fix_sort_columns']}($args);
		}

		return $args;
	}


	/* helper functions */

	/** function grab_hierarchical_array
	 *
	 *		pull a hierarchical list of the given post types
	 *
	 * @param string optional post type (default current post type)
	 * @return array list of the given post types
	 */
	protected function grab_hierarchical_array($post_type = null)
	{
		if ( ! $post_type) {
			$post_type = $this->post_type;
		}

		// grab all the entries
		$items = get_pages(array(
			'sort_column' => 'menu_order',
			'sort_order' => 'ASC',
			'post_type' => $post_type,
		));

		$return = array( );
		foreach ($items as $item) {
			if (0 == $item->post_parent) {
				$cur_parent = $item->post_title;
				$return[$cur_parent] = array( );
			}
			else {
				$return[$cur_parent][$item->ID] = $item->post_title;
			}
		}

		return $return;
	}

} // end CustomPostType class

} // end class exists test


/**
 * Custom Post Type Registry
 *
 * this helps with dealing with more complex CPTs
 * that require the use of the save_post action,
 * and the manage_posts/pages_custom_column actions
 *
 */
if ( ! class_exists('CPTRegistry')) {

class CPTRegistry
{

	/** var $_instance
	 *
	 *		this holds the singleton instance of the registry
	 *
	 * @var CPTRegistry Object
	 * @static
	 */
	static private $_instance;

	/** var $registry
	 *
	 *		this is the CPT registry array
	 *
	 * @var array
	 * @required
	 */
	protected $registry = array( );


	/** static public function get_instance
	 *
	 *		Returns the singleton instance
	 *		of the CPTRegistry Object as a reference
	 *
	 * @param array optional configuration array
	 * @action optionally creates the instance
	 * @return CPTRegistry Object reference
	 */
	static public function get_instance( )
	{
		if (is_null(self::$_instance)) {
			$c = __CLASS__;
			self::$_instance = new $c( );
		}

		return self::$_instance;
	}


	/** class constructor
	 *
	 *		Instantiates the class and adds the actions to WP
	 *
	 * @param void
	 * @return void
	 */
	private function __construct( )
	{
		// add the actions this thing was built for
		add_action('save_post', array($this, 'master_custom_save_switch'));
		add_action('manage_posts_custom_column', array($this, 'master_custom_columns_switch'));
		add_action('manage_pages_custom_column', array($this, 'master_custom_columns_switch'));
	}


	/** function __clone
	 *
	 *		Prevent users from cloning the instance
	 *
	 * @param void
	 * @return void
	 */
	public function __clone( )
	{
		trigger_error('Clone is not allowed.', E_USER_ERROR);
	}


	/** function register
	 *
	 *		A static helper function to allow quick,
	 *		painless CPT instantiation
	 *
	 * @param string optional post type name
	 * @param array optional defaults
	 * @param string optional post slug
	 * @return CPT class object
	 */
	static public function register($post_type, $args = array( ), $post_slug = false)
	{
		$_this = self::get_instance( );
		return $_this->_register($post_type, $args, $post_slug);
	}


	/** function _register
	 *
	 *		A helper function to allow quick,
	 *		painless CPT instantiation
	 *
	 * @param string optional post type name
	 * @param array optional defaults
	 * @param string optional post slug
	 * @return CPT class object
	 */
	private function _register($post_type, $args = array( ), $post_slug = false)
	{
		if (('CPT' == substr($post_type, 0, 3)) && class_exists($post_type)) {
			$CPT = new $post_type( );
			$this->registry[$CPT->get_post_type( )] = $CPT;
		}
		else {
			$CPT = new CustomPostType($post_type, $args, $post_slug);
			$this->registry[$post_type] = $CPT;
		}

		return $CPT;
	}


	/** function master_custom_save_switch
	 *
	 *		This function takes all the contained post types
	 *		and switches the action performed by WP based
	 *		on which post type is being edited
	 *
	 * @param void
	 * @return void
	 */
	public function master_custom_save_switch( )
	{
		// skip if it's an autosave
		// autosave doesn't send everything, and if it doesn't
		// send everything, things may get deleted
		if ( ! isset($_POST['action']) || (0 !== strcmp('editpost', $_POST['action']))) {
			return;
		}

		if (isset($_POST['post_type']) && ! empty($this->registry[$_POST['post_type']])) {
			$this->registry[$_POST['post_type']]->save_data( );
		}
	}


	/** function master_custom_columns_switch
	 *
	 *		This function takes all the contained post types
	 *		and switches the action performed by WP based
	 *		on which post type is being shown
	 *
	 * @param string column
	 * @return void
	 */
	public function master_custom_columns_switch($column)
	{
		global $post;

		if (isset($post->post_type) && ! empty($this->registry[$post->post_type])) {
			$this->registry[$post->post_type]->custom_columns($column);
		}
	}

} // end CPTRegistry class

} // end class exists test



/**********************************************************\
 *
 *    HELPER FUNCTIONS
 *
\**********************************************************/


/** function custom_post_type
 *
 *		A helper function to allow quick, painless class instantiation
 *
 * @param string optional post type name
 * @param array optional defaults
 * @param string optional post slug
 * @return CPT class object
 */
if ( ! function_exists('custom_post_type') && class_exists('CustomPostType') && class_exists('CPTRegistry')) {
	function custom_post_type($post_type, $args = array( ), $post_slug = false) {
		return CPTRegistry::register($post_type, $args, $post_slug);
	}
}


/** function include_cpt_files
 *
 *		a helper function that will search the containing dir
 *		for cpt.__.php prefixed files and automagically includes them
 *
 * @param void
 * @return void
 */
function include_cpt_files( ) {
	// open the current dir
	$dh = opendir(dirname(__FILE__));

	$filelist = array( );
	while (false !== ($file = readdir($dh))) {
		if (preg_match('/^cpt\..*\.?php$/i', $file)) { // scanning for cpt.__.php files only
			// if we found one of those files, include it, the rest happens by magic
			include $file;
		}
	}

	closedir($dh);
}
include_cpt_files( );



/**********************************************************\
 *
 *    MISC FUNCTIONS
 *
\**********************************************************/


/** function make_checkbox_list
 *
 *		create a set of checkboxes in an unordered list
 *
 * @param string the field name
 * @param array the options ('value' => 'Text')
 * @param mixed optional the selected values
 * @return string the checkbox html
 */
function make_checkbox_list($name, $options, $values = array( )) {
	if (empty($options) || ! is_array($options)) {
		return false;
	}

	if (empty($values)) {
		$values = array( );
	}
	$values = (array) $values;

	$html = '<ul>';

	foreach ($options as $value => $key) {
		$checked = '';
		if (in_array($value, $values)) {
			$checked = 'checked="checked"';
		}

		$html .= '<li><label><input type="checkbox" name="'.$name.'[]" value="'.$value.'" '.$checked.' /> '.$key.'</label></li>';
	}

	$html .= '</ul>';

	return $html;
}


/** function make_radio
 *
 *		create a set of radio buttons in an optional unordered list
 *
 * @param string the field name
 * @param array the options ('value' => 'Text')
 * @param mixed optional the selected values
 * @param bool optional put radio buttons in a list
 * @return string the radio button html
 */
function make_radio($name, $options, $values = array( ), $in_list = false) {
	if (empty($options) || ! is_array($options)) {
		return false;
	}

	if (empty($values)) {
		$values = array( );
	}
	$values = (array) $values;

	$html = '';
	if ($in_list) {
		$html .= '<ul>';
	}

	foreach ($options as $value => $text) {
		$checked = '';
		if (in_array($value, $values)) {
			$checked = 'checked="checked"';
		}

		if ($in_list) {
			$html .= '<li>';
		}

		$html .= '<label><input type="radio" name="'.$name.'" value="'.$value.'" '.$checked.' /> '.$text.'</label>';

		if ($in_list) {
			$html .= '</li>';
		}
	}

	if ($in_list) {
		$html .= '</ul>';
	}

	return $html;
}


/** function make_select_options
 *
 *		create a set of select box options
 *
 *		if $options is a 2D array, the first indexes will
 *		be used as optgroups for the second indexes
 *
 *		array(
 *			'value' => 'Output Text',
 *			...
 *		);
 *
 *		- OR -
 *
 *		array(
 *			'Opt Group Text' => array(
 *				'value' => 'Output Text',
 *				...
 *			),
 *			...
 *		);
 *
 * @param array the options
 * @param mixed optional the selected values
 * @return string the options html
 */
function make_select_options($options, $values = array( )) {
	if (empty($options) || ! is_array($options)) {
		return false;
	}

	$values = (array) $values;

	$html = '';

	foreach ($options as $key => $value) {
		if (is_array($value)) {
			$html .= '<optgroup label="'.$key.'">';
			$html .= make_select_options($value, $values);
			$html .= '</optgroup>';
		}
		else {
			$sel = (in_array($key, $values) ? ' selected="selected"' : '');
			$html .= '<option value="'.$key.'"'.$sel.'>'.$value.'</option>';
		}
	}

	return $html;
}


/** function make_time
 *
 *		create a date/time input area
 *		to be used in conjunction with update_meta_time( )
 *
 *		based on WordPress' touch_time( ) function in /wp-admin/includes/template.php
 *
 * @param string the field name
 * @param string optional the given date
 * @param bool optional include date (default true)
 * @param bool optional include time (default true)
 * @param int optional starting tab index
 * @return string the date/time input html
 */
function make_time($name, $value = '0000-00-00 00:00:00', $incl_date = true, $incl_time = true, $tab_index = 0) {
	global $wp_locale;

	$tab_index_attribute = '';
	if ( (int) $tab_index > 0 ) {
		$tab_index_attribute = " tabindex=\"$tab_index\"";
	}

	$time_adj = strtotime($value);
	$aa = gmdate( 'Y', $time_adj );
	$mm = gmdate( 'm', $time_adj );
	$jj = gmdate( 'd', $time_adj );
	$hh = gmdate( 'H', $time_adj );
	$mn = gmdate( 'i', $time_adj );

	$month = '<select name="' . $name . '[month]"' . $tab_index_attribute . '>'."\n";
	for ( $i = 1; $i < 13; $i = $i + 1 ) {
		$month .= "\t\t\t" . '<option value="' . zeroise($i, 2) . '"';
		if ( $i == $mm )
			$month .= ' selected="selected"';
		$month .= '>' . $wp_locale->get_month_abbrev( $wp_locale->get_month( $i ) ) . "</option>\n";
	}
	$month .= '</select>';

	$day = '<input type="text" name="' . $name . '[day]" value="' . $jj . '" size="2" maxlength="2"' . $tab_index_attribute . ' autocomplete="off" />';
	$year = '<input type="text" name="' . $name . '[year]" value="' . $aa . '" size="4" maxlength="4"' . $tab_index_attribute . ' autocomplete="off" />';
	$hour = '<input type="text" name="' . $name . '[hour]" value="' . $hh . '" size="2" maxlength="2"' . $tab_index_attribute . ' autocomplete="off" />';
	$minute = '<input type="text" name="' . $name . '[minute]" value="' . $mn . '" size="2" maxlength="2"' . $tab_index_attribute . ' autocomplete="off" />';

	$return = '<div class="timestamp-wrap">';
	/* translators: 1: month input, 2: day input, 3: year input, 4: hour input, 5: minute input, 6: second input */

	$format = '';
	if ($incl_date) {
		$format .= '%1$s%2$s, %3$s';
	}

	if ($incl_time) {
		if ( ! empty($format)) {
			$format .= ' @ ';
		}

		$format .= '%4$s : %5$s';
	}

	$return .= sprintf(__($format), $month, $day, $year, $hour, $minute);
	$return .= '</div>';

	return $return;
}


/** function get_meta_value
 *
 *		pulls a single meta value for the post
 *		returns a default value if none found
 *
 * @param string the meta variable name
 * @param mixed optional the default value
 * @return mixed the value found (or default if none)
 */
function get_meta_value($name, $default = '') {
	global $post;

	$value = $default;

	$custom = get_post_custom($post->ID);
	if ( ! empty($custom[$name][0])) {
		$value = $custom[$name][0];
	}

	return $value;
}


/** function get_meta_array
 *
 *		pulls multiple meta values for the post
 *		returns a default value if none found
 *
 * @param string the meta variable name
 * @param mixed optional the default value
 * @return mixed the value found (or default if none)
 */
function get_meta_array($name, $default = array( )) {
	global $post;

	$value = $default;

	$custom = get_post_custom($post->ID);
	if ( ! empty($custom[$name])) {
		$value = $custom[$name];
	}

	return $value;
}


/** function update_meta_array
 *
 *		saves an array of meta data to the post
 *		and optionally deletes any previous data
 *		before doing so
 *
 * @param int the post id
 * @param string the meta variable name
 * @param array optional the meta data
 * @param bool optional delete any previous data (default true)
 * @return void
 */
function update_meta_array($post_id, $name, $data = array( ), $delete_prev = true) {
	// delete the previous values
	if ($delete_prev) {
		delete_post_meta($post_id, $name);
	}

	foreach ($data as $entry) {
		add_post_meta($post_id, $name, $entry);
	}
}


/** function update_meta_time
 *
 *		saves date/time value to the post
 *		to be used in conjunction with make_time( )
 *
 * @param int the post id
 * @param string the meta variable name
 * @param array optional the meta data
 * @return void
 */
function update_meta_time($post_id, $name, $data = array( )) {
	$time = '';

	if ( ! empty($data['year']) || ! empty($data['month']) || ! empty($data['day'])) {
		if (empty($data['year'])) {
			$data['year'] = '0000';
		}

		if (empty($data['month'])) {
			$data['month'] = '00';
		}

		if (empty($data['day'])) {
			$data['day'] = '00';
		}

		$time .= $data['year'].'-'.$data['month'].'-'.$data['day'];
	}

	if ( ! empty($data['hour']) || ! empty($data['minute']) || ! empty($data['second'])) {
		if (empty($data['hour'])) {
			$data['hour'] = '00';
		}

		if (empty($data['minute'])) {
			$data['minute'] = '00';
		}

		if (empty($data['second'])) {
			$data['second'] = '00';
		}

		if ( ! empty($time)) {
			$time .= ' ';
		}

		$time .= $data['hour'].':'.$data['minute'].':'.$data['second'];
	}

	update_post_meta($post_id, $name, $time);
}


/** function my_round
 *
 *		rounds a value to the given decimal places
 *		e.g.- my_round(3.14159, 2) => 3.14
 *
 * @param float the given value
 * @param int optional the decimal places to round to
 * @return float the rounded value
 */
function my_round($val, $closest = 0) {
	$closest = (int) $closest;

	if (1 >= $closest) {
		return (int) round($val);
	}

	return (int) (round($val / $closest) * $closest);
}

