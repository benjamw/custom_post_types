<?php
/**
 * @package CPT
 * @version 1.0
 */
/*
Plugin Name: Custom Post Types Instatiator
Plugin URI: https://github.com/benjamw/custom_post_types
Description: Instatiates the CPTs included in this installation
Author: Benjam Welker
Version: 1.0
Author URI: http://iohelix.net/
*/

function include_cpt_files( ) {
	// open the current dir
	$dh = opendir(dirname(__FILE__));

	$filelist = array( );
	while (false !== ($file = readdir($dh))) {
		if (preg_match('/^cpt\..+\.php$/i', $file)) { // scanning for cpt.__.php files only
			// if we found one of those files, include it, the rest happens by magic
			include $file;
		}
	}

	closedir($dh);
}

include_once 'custom_post_type.class.php';
