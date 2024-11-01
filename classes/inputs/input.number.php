<?php
/*
 * Followig class handling text input control and their
* dependencies. Do not make changes in code
* Create on: 9 November, 2013
*/

class NM_Number extends NM_Inputs{
	
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
		
		$this -> title 		= __ ( 'Number Input', 'nm-webcontact' );
		$this -> desc		= __ ( 'regular number input', 'nm-webcontact' );
		$this -> settings	= self::get_settings();
		
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
		
		'min_number' => array (
				'type' => 'text',
				'title' => __ ( 'Minimum Number', 'nm-webcontact' ),
				'desc' => __ ( 'Enter minimum number for field.', 'nm-webcontact' ) 
		),
		
		'max_number' => array (
				'type' => 'text',
				'title' => __ ( 'Maximum Number', 'nm-webcontact' ),
				'desc' => __ ( 'Enter maximum number for field.', 'nm-webcontact' ) 
		),
		
		'step' => array (
				'type' => 'text',
				'title' => __ ( 'Step', 'nm-webcontact' ),
				'desc' => __ ( 'Increase amount step.', 'nm-webcontact' ) 
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
		
		$_html = '<input type="number" ';
		
		foreach ($args as $attr => $value){
			
			$_html .= $attr.'="'.stripslashes( $value ).'"';
		}
		
		if($content)
			$_html .= 'value="' . stripslashes($content	) . '"';
		
		$_html .= ' />';
		
		echo $_html;
	}
}