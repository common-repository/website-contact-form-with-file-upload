<?php
/*
 * Followig class handling date input control and their
* dependencies. Do not make changes in code
* Create on: 9 November, 2013
*/

class NM_Date extends NM_Inputs{
	
	/*
	 * input control settings
	 */
	var $title, $desc, $settings;
	
	/*
	 * this var is pouplated with current plugin meta
	*/
	var $plugin_meta;
	
	function __construct(){
		
		$this -> plugin_meta = get_plugin_meta_webcontact();
		
		$this -> title 		= __ ( 'Date Input', 'nm-webcontact' );
		$this -> desc		= __ ( 'regular date input', 'nm-webcontact' );
		$this -> settings	= self::get_settings();
		
		$this -> input_scripts = array('shipped'		=> array('jquery-ui-datepicker'));
		
		add_action ( 'wp_enqueue_scripts', array ($this, 'load_input_scripts'));
		
	}
	
	
	
	
	private function get_settings(){
		
		return array (
		'title' => array (
				'type' => 'text',
				'title' => __ ( 'Title', 'nm-webcontact' ),
				'desc' => __ ( 'It will be shown as field label', 'nm-webcontact' ) 
		),
		'data_name' => array (
				'type' => 'text',
				'title' => __ ( 'Data name', 'nm-webcontact' ),
				'desc' => __ ( 'REQUIRED: The identification name of this field, that you can insert into body email configuration. Note:Use only lowercase characters and underscores.', 'nm-webcontact' ) 
		),
		'description' => array (
				'type' => 'text',
				'title' => __ ( 'Description', 'nm-webcontact' ),
				'desc' => __ ( 'Small description, it will be diplay near name title.', 'nm-webcontact' ) 
		),
		'error_message' => array (
				'type' => 'text',
				'title' => __ ( 'Error message', 'nm-webcontact' ),
				'desc' => __ ( 'Insert the error message for validation.', 'nm-webcontact' ) 
		),
		
		'required' => array (
				'type' => 'checkbox',
				'title' => __ ( 'Required', 'nm-webcontact' ),
				'desc' => __ ( 'Select this if it must be required.', 'nm-webcontact' ) 
		),
		'class' => array (
				'type' => 'text',
				'title' => __ ( 'Class', 'nm-webcontact' ),
				'desc' => __ ( 'Insert an additional class(es) (separateb by comma) for more personalization.', 'nm-webcontact' ) 
		),
		'width' => array (
				'type' => 'select',
				'default' => '6',
				'options'		=> array('2'=>'2 Columns', '3'=>'3 Columns', '4'=>'4 Columns', '5'=>'5 Columns', '6'=>'6 Columns', '7'=>'7 Columns', '8'=>'8 Columns', '9'=>'9 Columns', '10'=>'10 Columns','11'=>'11 Columns','12'=>'12 Columns'),
				'title' => __ ( 'Width', 'nm-webcontact' ),
				'desc' => __ ( 'Select column for this field from 12 columns Grid', 'nm-webcontact' ) 
		),
		'date_formats' => array (
				'type' => 'select',
				'title' => __ ( 'Date formats', 'nm-webcontact' ),
				'desc' => __ ( 'Select date format.', 'nm-webcontact' ),
				'options' => array (
						'mm/dd/yy' => 'Default - mm/dd/yy',
						'yy-mm-dd' => 'ISO 8601 - yy-mm-dd',
						'd M, y' => 'Short - d M, y',
						'd MM, y' => 'Medium - d MM, y',
						'DD, d MM, yy' => 'Full - DD, d MM, yy',
						'\'day\' d \'of\' MM \'in the year\' yy' => 'With text - \'day\' d \'of\' MM \'in the year\' yy' 
				) 
		),
		'logic' => array (
				'type' => 'checkbox',
				'title' => __ ( 'Enable conditional logic', 'nm-webcontact' ),
				'desc' => __ ( 'Tick it to turn conditional logic to work below', 'nm-webcontact' )
		),
		'conditions' => array (
				'type' => 'html-conditions',
				'title' => __ ( 'Conditions', 'nm-webcontact' ),
				'desc' => __ ( 'Tick it to turn conditional logic to work below', 'nm-webcontact' )
		),
		);
	}
	
	
	/*
	 * @params: args
	*/
	function render_input($args, $content=""){
		
		$_html = '<input type="text" ';
		
		foreach ($args as $attr => $value){
			
			$_html .= $attr.'="'.stripslashes( $value ).'"';
		}
		
		if($content)
			$_html .= 'value="' . stripslashes($content	) . '"';
		
		$_html .= ' />';
		
		echo $_html;
		
		$this -> get_input_js($args);
	}
	
	/*
	 * following function is rendering JS needed for input
	*/
	function get_input_js($args){
		
		echo '<script type="text/javascript">';
		
		echo 'jQuery(function($){';
		
		echo 	'$("#'.$args['id'].'").datepicker({ 	changeMonth: true,';
		echo 	'changeYear: true,';
		echo 	'dateFormat: $(this).attr("data-format")';
		echo 	'});';
		echo	'});';
		
		
		echo '</script>';
	
	}
}