<?php
/*
Plugin Name: Hancock County Calendar Plugin

Description: A events list widget for multisite

Version: 0.8

Author: zig
Date: 28Aug2014
Author URI: http://wwww.reachmaine.com

License: GPL3

*/



	require_once(ABSPATH . 'wp-admin/includes/upgrade.php');


	/* include('hcc_functions.php'); */
    include('hcc_list_class.php');



	add_action( 'widgets_init', 'load_hcc');
	function load_hcc() {
		register_widget( 'hcc_list' );
	}
 ?>
