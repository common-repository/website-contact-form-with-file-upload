<?php
/*
 * Followig class handling date input control and their
* dependencies. Do not make changes in code
* Create on: 9 November, 2013
*/

class NM_Color extends NM_Inputs{
	
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
		
		$this -> title 		= __ ( 'Color picker', 'nm-webcontact' );
		$this -> desc		= __ ( 'Color pallete input', 'nm-webcontact' );
		$this -> settings	= self::get_settings();
		
		$this -> input_scripts = array(	'shipped'		=> array(''),
		
										'custom'		=> array(
												array (
														'script_name' => 'wp_iris_script',
														'script_source' => '/js/color/Iris/dist/iris.min.js',
														'localized' => false,
														'type' => 'js',
														'depends'	=> array('jquery','jquery-ui-core','jquery-ui-draggable', 'jquery-ui-slider'),
														'in_footer'	=> '',
												),
										)
		);
		
		
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
				
		'default_color' => array (
				'type' => 'text',
				'title' => __ ( 'Dedfault color', 'nm-webcontact' ),
				'desc' => __ ( 'Define default color e.g: #effeff', 'nm-webcontact' ) 
		),
		
		'show_palletes' => array (
				'type' => 'checkbox',
				'title' => __ ( 'Show palletes', 'nm-webcontact' ),
				'desc' => __ ( 'Tick if need to show a group of common colors beneath the square', 'nm-webcontact' )
		),
				
		'show_onload' => array (
				'type' => 'checkbox',
				'title' => __ ( 'Show color picker', 'nm-webcontact' ),
				'desc' => __ ( 'Display color picker by default, otherwise will show when field selected', 'nm-webcontact' )
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
		
		$palletes = ($args['show-palletes'] == '') ? 'off' : $args['show-palletes'];
		$show = ($args['show-onload'] == '') ? 'off' : $args['show-onload'];
	?>
		
				<script type="text/javascript">	
				<!--
				jQuery(function($){

					var palette = '<?php echo $palletes;?>' == 'on' ? true : false;
					var hide = '<?php echo $show;?>' == 'on' ? false : true;
					
					var options = {
							palettes: palette,
							color: "<?php echo $args['default-color'];?>",
							hide: hide,
							change: function(event,ui){
								$("#box-<?php echo $args['id'];?>").css( 'color', ui.color.toString());
							}
					};
					
					$("#<?php echo $args['id'];?>").iris(options);
				});
				
				//--></script>
				<?php
		}
}