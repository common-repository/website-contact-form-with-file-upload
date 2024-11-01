<?php
/*
 * this file contains pluing meta information and then shared
 * between pluging and admin classes
 * 
 * [1]
 */


$plugin_meta = array();
function get_plugin_meta_webcontact(){
	
	$plugin_meta		= array('name'			=> 'WebContact',
							'shortname'		=> 'nm_webcontact',
							'path'			=> untrailingslashit(plugin_dir_path( __FILE__ )),
							'url'			=> untrailingslashit(plugin_dir_url( __FILE__ )),
							'db_version'	=> 3.0,
							'logo'			=> plugin_dir_url( __FILE__ ) . 'images/logo.png',
							'menu_position'	=> 76);
	
	return $plugin_meta;
}


/*
 * rendering that It is Pro
 */
function nm_webcontact_pro(){
	
	return '<a class="nm_pro" href="#">'.__('It is PRO', 'nm_webcontact').'</a>';
}

function webcontact_pa($arr){
	
	echo '<pre>';
	print_r($arr);
	echo '</pre>';
}