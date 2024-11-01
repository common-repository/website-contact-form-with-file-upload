<?php
/*
 * Followig class handling select input control and their
* dependencies. Do not make changes in code
* Create on: 9 November, 2013
*/

class NM_Autocomplete extends NM_Inputs{
	
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
		
		$this -> title 		= __ ( 'Auto Complete', 'nm-webcontact' );
		$this -> desc		= __ ( 'auto complete input field', 'nm-webcontact' );
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
		
		'options' => array (
				'type' => 'textarea',
				'title' => __ ( 'Add options', 'nm-webcontact' ),
				'desc' => __ ( 'Type each option per line', 'nm-webcontact' ) 
		),
		'hint_text' => array (
				'type' => 'text',
				'title' => __ ( 'Hint text', 'nm-webcontact' ),
				'desc' => __ ( 'Type the hint text, it will be displayed on focus.', 'nm-webcontact' ) 
		),
		'no_result_text' => array (
				'type' => 'text',
				'title' => __ ( 'No Result text', 'nm-webcontact' ),
				'desc' => __ ( 'Type the no result text, it will be displayed when no search results.', 'nm-webcontact' ) 
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
	 * @params: $options
	*/
	function render_input($args, $options=""){
		// print_r($options);

		
		$_html = '<input type="text" ';
		
		foreach ($args as $attr => $value){
			
			$_html .= $attr.'="'.stripslashes( $value ).'"';
		}
		
		if(isset($content))
			$_html .= 'value="' . stripslashes($content	) . '"';
		
		$_html .= ' />';
		
		echo $_html;

		$this -> get_input_js($args, $options);
	}

	/*
	 * following function is rendering JS needed for input
	*/
	function get_input_js($args, $options){

		$option_key = array();
		foreach ($options as $option) {
			
			$option_key[] = array('name'	=> $option);		
		}

		$option_key = json_encode($option_key);
		
		echo '<script type="text/javascript">';

		echo 'var options2 = '.$option_key.';';
		
		echo 'jQuery(function($){';
		
		echo 	'$("#'.$args['id'].'").tokenInput(options2, {';
		echo 	'hintText:"'.$args['hint-text'].'",';
		echo 	'noResultsText: "'.$args['no-result-text'].'",';
		echo 	'theme:"facebook",';
        echo    'tokenValue: "name"});';
		echo	'});';
		
		
		echo '</script>';
	
	}	
}