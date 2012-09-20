<?php
/*

There are a couple of methods to include new CTs with this set of functions.

You can create a new class that extends the CustomTaxonomy class and store it
in a file with the prefix of 'ct.' and it will get auto-included and run by
the include_ct_files( ) function in this file.  Just make sure to have the
custom_taxonomy('CTExample'); call at the bottom of that file. Then just
include this file in your theme's functions.php file and you're done.

include 'custom_taxonomy.class.php';


Another method is to call the custom_taxonomy( ) function with the given
arguments as follows: (See the function arguments and options below)

custom_taxonomy('child', array(
			'hierarchical' => false,
			'has_archive' => false, // we're gonna use a page for content
		), 'children');

No class needed, just pass everything to the function and it does the rest.


HELPER FUNCTIONS INCLUDED:
---------------------------------------------------------------------
none yet...

*/

// include a taxonomy meta class if available
if (is_readable('Tax-meta-class/Tax-meta-class.php')) {
	include_once 'Tax-meta-class/Tax-meta-class.php';
}


/**
 * Base Custom Taxonomy Class
 *
 */
if ( ! class_exists('CustomTaxonomy')) {

class CustomTaxonomy
{

	/** var $tax_name
	 *
	 *		this is the taxonomy name and
	 *		should be set to something unique
	 *
	 * @var string
	 * @required
	 */
	protected $tax_name = 'custom_type';


	/** var $tax_slug
	 *
	 *		this is the slug used in the URL when
	 *		accessing the archive or index pages
	 *
	 *		if this variable is missing/empty, it will be
	 *		set to $tax_name with an 's' added on the end
	 *		if that form is incorrect, set the proper form here
	 *
	 * @var string
	 */
	protected $tax_slug;


	/** var $singular
	 *
	 *		this is the string used for the singular version
	 *		of the post type name in various places
	 *
	 *		if this variable is missing/empty, it will be
	 *		set to $tax_name with every word capitalized
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
	 *		set to $tax_slug with every word capitalized
	 *		if that form is incorrect, set the proper form here
	 *
	 * @var string
	 */
	protected $plural;


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
	 *		this is the array that holds the used values for the CT
	 *
	 *		see the CustomTaxonomy class for a complete list of defaults
	 *
	 *		see http://codex.wordpress.org/Function_Reference/register_taxonomy
	 *			for an explanation of those values
	 *
	 * @var array
	 */
	protected $args;


	/** var $defaults
	 *
	 *		this is the array that holds the default values for the CT
	 *
	 *		see http://codex.wordpress.org/Function_Reference/register_taxonomy
	 *			for an explanation of these values
	 *
	 * @var array
	 */
	protected $defaults = array(
		'labels' => array( ), // this can be overridden by setting it, but if it's empty, defaults will be used
		'public' => true,
		'show_ui' => true,
		'show_tagcloud' => true,
#		'capabilities' => array( ),
		'hierarchical' => false,
		'has_archive' => true,
		'rewrite' => true, /** array(
			'slug' => 'slug', // '/slug/[tax_slug]/'
			'with_front' => true, // if your permalink structure is /blog/, then your links will be: false->/news/, true->/blog/news/
			'feeds' => true,
			'pages' => true,
		), //*/
		'query_var' => true,
		'show_in_nav_menus' => true,
#		'labels' => array(
#			-- see labels below in set_defaults( ) method --
#		),
#		'register_meta_box_cb' => callback function that registers the admin meta boxes
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
	 * @param array optional defaults
	 * @param string optional post slug
	 * @return CT class object
	 */
	public function __construct($tax_name = null, $args = array( ), $tax_slug = false)
	{
		// generate/store our names
		if ($tax_name) {
			$this->tax_name = $tax_name;
		}

		if (empty($this->tax_slug)) {
			$this->tax_slug = ( ! empty($tax_slug)) ? $tax_slug : $this->tax_name.'s';
		}

		$this->set_defaults( );
		$this->args = array_merge($this->defaults, $args);

		// run the stuff
		$this->add_actions( );
		$this->add_filters( );
	}


	/** function get_tax_name
	 *
	 *		returns the taxonomy name
	 *
	 * @param void
	 * @return string taxonomy name
	 */
	public function get_tax_name( )
	{
		return $this->tax_name;
	}


	/** function set_defaults
	 *
	 *		this function merges the defaults from any children classes,
	 *		incorporates the remaining lable data into the defaults,
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

		$tax_name = ucwords(str_replace(array('_', '-'), ' ', $this->tax_name));
		$tax_slug = ucwords(str_replace(array('_', '-'), ' ', $this->tax_slug));

		$this->singular = ( ! empty($this->singular)) ? $this->singular : $tax_name;
		$this->plural = ( ! empty($this->plural)) ? $this->plural : $tax_slug;
		$this->menu_title = ( ! empty($this->menu_title)) ? $this->menu_title : $this->plural;

		$this->defaults['labels'] = ! empty($this->defaults['labels']) ? $this->defaults['labels'] : array(
			'name' => _x($this->plural, 'taxonomy general name'), // Tags
			'singular_name' => _x($this->singular, 'taxonomy singular name'), // Tag
			'add_new' => _x('Add New', strtolower($tax_slug)), // Add New
			'add_new_item' => __('Add New '.$this->singular), // Add New Tag
			'new_item_name' => __('New '.$this->singular.' Name'), // New Tag Name
			'edit_item' => __('Edit '.$this->singular), // Edit Tag
			'update_item' => __('Update '.$this->singular), // Update Tag
			'new_item' => __('New '.$this->singular), // New Tag
			'view_item' => __('View '.$this->singular), // View Tag
			'separate_items_with_commas' => __('Separate '.$this->menu_title.' with commas'), // Separate Tags with commas
			'add_or_remove_items' => __('Add or remove '.strtolower($this->plural)), // Add or remove tags
			'choose_from_most_used' => __('Choose from the most used '.strtolower($this->plural)), // Choose from the most used tags
			'popular_items' => __('Popular '.$this->menu_title), // Popular Tags
			'search_items' => __('Search '.$this->menu_title), // Search Tags
			'all_items' => __('All '.$this->menu_title), // All Tags
			'not_found' =>  __('No '.$this->plural.' found'), // No Tags found
			'not_found_in_trash' => __('No '.$this->plural.' found in Trash'), // No Tags found in Trash
			'parent_item' => __('Parent '.$this->singular), // Parent Tag
			'parent_item_colon' => __('Parent '.$this->singular.':'), // Parent Tag: (hierarchical only)
			'menu_name' => _x($this->menu_title, 'taxonomy menu name'), // Tags (in menu)
		);

		$this->defaults['has_archive'] = (true == $this->defaults['has_archive']) ? $this->tax_slug : $this->defaults['has_archive'];
		$this->defaults['rewrite'] = (true == $this->defaults['rewrite']) ? array('slug' => $this->tax_slug) : $this->defaults['rewrite'];

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
		add_action('init', array($this, 'register_taxonomy'));
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
		add_filter('manage_edit-'.$this->tax_name.'_sortable_columns', array($this, 'sort_columns'));
		add_filter('request', array($this, 'fix_sort_columns'));
	}


	/** function register_taxonomy
	 *
	 *		registers the new CT with WP and edits hooks as needed
	 *
	 * @param void
	 * @return void
	 */
	public function register_taxonomy( )
	{
		register_taxonomy($this->tax_name, '', $this->args);

		if (is_admin( ) && ! empty($this->args['tax_meta']) && class_exists('Tax_Meta_Class')) {
			$config = array(
				'id' => $this->tax_name.'_meta_box',          // meta box id, unique per meta box
				'title' => $this->singular.' Meta Box',          // meta box title
				'pages' => array($this->tax_name),        // taxonomy name, accept categories, post_tag and custom taxonomies
				'context' => 'normal',            // where the meta box appear: normal (default), advanced, side; optional
				'fields' => array( ),            // list of meta fields (can be added by field arrays)
				'local_images' => true,          // Use local or hosted images (meta box images for add/remove)
				'use_with_theme' => true,          //change path if used with theme set to true, false for a plugin or anything else for a custom path(default false).
				'boxes' => array( ),
			);

			$prefix = ( ! empty($this->args['tax_meta']['prefix']) ? $this->args['tax_meta']['prefix'] : $this->tax_name.'_mb_');

			$this->args['tax_meta'] = array_merge($config, $this->args['tax_meta']);

			$tax_meta = new Tax_Meta_Class($this->args['tax_meta']);

			foreach ($this->args['tax_meta']['boxes'] as $box) {
				$func = 'add'.ucfirst($box['type']);

				$field_id = ( ! empty($box['id']) ? $box['id'] : $prefix.$box['type'].'_field_id');

				switch ($box['type']) {
					case 'select' :
					case 'radio' :
						$tax_meta->{$func}($field_id, $box['options'], $box['args']);
						break;

					case 'taxonomy' :
						$tax_meta->{$func}($field_id, array('taxonomy' => $box['taxonomy']), $box['args']);
						break;

					case 'posts' :
						$tax_meta->{$func}($field_id, array('taxonomy' => $box['taxonomy']), $box['args']);
						break;

					case 'text' :
					case 'textarea' :
					case 'checkbox' :
					case 'date' :
					case 'time' :
					case 'color' :
					case 'image' :
					case 'file' :
					case 'wysiwyg' :
					default :
						$tax_meta->{$func}($field_id, $box['args']);
						break;
				}
			}

			$tax_meta->Finish( );
		}

#		add_filter('manage_edit-'.$this->tax_name.'_columns', array($this, 'edit_columns'));
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
		if (get_query_var('post_type') == $this->tax_name) {
			if (is_single( )) {
				if ($single = locate_template(array($this->tax_name.'/single.php'))) {
					return $single;
				}
			}
			else { // loop
				return locate_template(array(
					$this->tax_name.'/index.php',
					$this->tax_name.'.php',
					'index.php',
				));
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
		if (get_query_var('term') === $this->tax_name) {
			$c[] = $this->tax_name;
			$c[] = 'tax-'.$this->tax_name;
		}
		return $c;
	}


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

			if ( taxonomy_supports( $this->tax_name, 'author' ) )
				$posts_columns['author'] = __( 'Author' );

			if ( empty( $this->tax_name ) || is_object_in_taxonomy( $this->tax_name, 'category' ) )
				$posts_columns['categories'] = __( 'Categories' );

			if ( empty( $this->tax_name ) || is_object_in_taxonomy( $this->tax_name, 'post_tag' ) )
				$posts_columns['tags'] = __( 'Tags' );

			$post_status = !empty( $_REQUEST['post_status'] ) ? $_REQUEST['post_status'] : 'all';
			if ( taxonomy_supports( $this->tax_name, 'comments' ) && !in_array( $post_status, array( 'pending', 'draft', 'future' ) ) )
				$posts_columns['comments'] = '<div class="vers"><img alt="' . esc_attr__( 'Comments' ) . '" src="' . esc_url( admin_url( 'images/comment-grey-bubble.png' ) ) . '" /></div>';

			$posts_columns['date'] = __( 'Date' );

			$posts_columns = apply_filters( "manage_{$this->tax_name}_posts_columns", $posts_columns );

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
	 *		pull a hierachical list of the given post types
	 *
	 * @param string optional post type (default current post type)
	 * @return array list of the given post types
	 */
	protected function grab_hierarchical_array($tax_name = null)
	{
		if ( ! $tax_name) {
			$tax_name = $this->tax_name;
		}

		// grab all the entries
		$items = get_pages(array(
			'sort_column' => 'menu_order',
			'sort_order' => 'ASC',
			'term' => $tax_name,
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

} // end CustomTaxonomy class

} // end class exists test


/**
 * Custom Post Type Registry
 *
 * this helps with dealing with more complex CTs
 * that require the use of the save_post action,
 * and the manage_posts/pages_custom_column actions
 *
 */
if ( ! class_exists('CTRegistry')) {

class CTRegistry
{

	/** var $_instance
	 *
	 *		this holds the singleton instance of the registry
	 *
	 * @var CTRegistry Object
	 * @static
	 */
	static private $_instance;

	/** var $regsitry
	 *
	 *		this is the CT registry array
	 *
	 * @var array
	 * @required
	 */
	protected $regsitry = array( );


	/** static public function get_instance
	 *
	 *		Returns the singleton instance
	 *		of the CTRegistry Object as a reference
	 *
	 * @param array optional configuration array
	 * @action optionally creates the instance
	 * @return CTRegistry Object reference
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
	 *		painless CT instantiation
	 *
	 * @param string optional post type name
	 * @param array optional defaults
	 * @param string optional post slug
	 * @return CT class object
	 */
	static public function register($tax_name, $args = array( ), $tax_slug = false)
	{
		$_this = self::get_instance( );
		return $_this->_register($tax_name, $args, $tax_slug);
	}


	/** function _register
	 *
	 *		A helper function to allow quick,
	 *		painless CT instantiation
	 *
	 * @param string optional post type name
	 * @param array optional defaults
	 * @param string optional post slug
	 * @return CT class object
	 */
	private function _register($tax_name, $args = array( ), $tax_slug = false)
	{
		if (('CT' == substr($tax_name, 0, 2)) && class_exists($tax_name)) {
			$CT = new $tax_name( );
			$this->registry[$CT->get_tax_name( )] = $CT;
		}
		else {
			$CT = new CustomTaxonomy($tax_name, $args, $tax_slug);
			$this->registry[$tax_name] = $CT;
		}

		return $CT;
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

		if (isset($_POST['taxonomy']) && ! empty($this->registry[$_POST['taxonomy']])) {
			$this->registry[$_POST['taxonomy']]->save_data( );
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

		if (isset($post->taxonomy) && ! empty($this->registry[$post->taxonomy])) {
			$this->registry[$post->taxonomy]->custom_columns($column);
		}
	}

} // end CTRegistry class

} // end class exists test



/**********************************************************\
 *
 *    HELPER FUNCTIONS
 *
\**********************************************************/


/** function custom_taxonomy
 *
 *		A helper function to allow quick, painless class instantiation
 *
 * @param string optional post type name
 * @param array optional defaults
 * @param string optional post slug
 * @return CT class object
 */
if ( ! function_exists('custom_taxonomy') && class_exists('CustomTaxonomy') && class_exists('CTRegistry')) {
	function custom_taxonomy($tax_name, $args = array( ), $tax_slug = false) {
		return CTRegistry::register($tax_name, $args, $tax_slug);
	}
}


/** function include_ct_files
 *
 *		a helper function that will search the containing dir
 *		for ct.__.php prefixed files and automagically includes them
 *
 * @param void
 * @return void
 */
function include_ct_files( ) {
	// open the current dir
	$dh = opendir(dirname(__FILE__));

	$filelist = array( );
	while (false !== ($file = readdir($dh))) {
		if (preg_match('/^ct\..*\.?php$/i', $file)) { // scanning for ct.__.php files only
			// if we found one of those files, include it, the rest happens by magic
			include $file;
		}
	}

	closedir($dh);
}
include_ct_files( );



/**********************************************************\
 *
 *    MISC FUNCTIONS
 *
\**********************************************************/

// none yet...

