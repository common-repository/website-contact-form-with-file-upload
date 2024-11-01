<?php
/*
 * Followig class handling pre-uploaded image control and their
* dependencies. Do not make changes in code
* Create on: 9 November, 2013
*/

class NM_Image extends NM_Inputs{
	
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
		
		$this -> title 		= __ ( 'Pre Image', 'nm-webcontact' );
		$this -> desc		= __ ( 'Images selection', 'nm-webcontact' );
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
		
		'required' => array (
				'type' => 'checkbox',
				'title' => __ ( 'Required', 'nm-webcontact' ),
				'desc' => __ ( 'Select this if it must be required.', 'nm-webcontact' ) 
		),
				
		'images' => array (
				'type' => 'pre-images',
				'title' => __ ( 'Select images', 'nm-webcontact' ),
				'desc' => __ ( 'Select images from media library', 'nm-webcontact' )
		),
				
		'multiple_allowed' => array (
				'type' => 'checkbox',
				'title' => __ ( 'Allow multiple?', 'nm-webcontact' ),
				'desc' => __ ( 'Allow users to select more then one images?.', 'nm-webcontact' )
		),
				
		'popup_width' => array (
				'type' => 'text',
				'title' => __ ( 'Popup width', 'nm-webcontact' ),
				'desc' => __ ( 'Popup window width in px e.g: 750', 'nm-webcontact' )
		),
		
		'popup_height' => array (
				'type' => 'text',
				'title' => __ ( 'Popup height', 'nm-webcontact' ),
				'desc' => __ ( 'Popup window height in px e.g: 550', 'nm-webcontact' )
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
function render_input($args, $images = "", $default_selected = ""){
		
		//webcontact_pa($images);
		
		$_html = '<div class="pre_upload_image_box">';
			
		$img_index = 0;
		$popup_width	= $args['popup-width'] == '' ? 600 : $args['popup-width'];
		$popup_height	= $args['popup-height'] == '' ? 450 : $args['popup-height'];
		
		if ($images) {
			
			foreach ($images as $image){
					
				
				$_html .= '<div class="pre_upload_image">';
				if($image['id'] != ''){
					$_html .= '<img src="'.wp_get_attachment_thumb_url( $image['id'] ).'" />';
				}else{
					$_html .= '<img width="150" height="150" src="'.$image['link'].'" />';
				}
				
					
				// for bigger view
				$_html	.= '<div style="display:none" id="pre_uploaded_image_' . $args['id'].'-'.$img_index.'"><img style="margin: 0 auto;display: block;" src="' . $image['link'] . '" /></div>';
					
				$_html	.= '<div class="input_image">';
				if ($args['multiple-allowed'] == 'on') {
					$_html	.= '<input type="checkbox" data-price="'.$image['price'].'" data-title="'.stripslashes( $image['title'] ).'" name="'.$args['name'].'[]" value="'.esc_attr(json_encode($image)).'" />';
				}else{
					
					//default selected
					$image_price = (isset($image['price'])) ? $image['price'] : '' ;
					$checked = ($image['title'] == $default_selected ? 'checked = "checked"' : '' );
					$_html	.= '<input type="radio" data-price="'.$image_price.'" data-title="'.stripslashes( $image['title'] ).'" name="'.$args['name'].'" value="'.esc_attr(json_encode($image)).'" '.$checked.' />';
				}
					
				// image big view
					
				$_html	.= '<a href="#TB_inline?width='.$popup_width.'&height='.$popup_height.'&inlineId=pre_uploaded_image_' . $args['id'].'-'.$img_index.'" class="thickbox" title="' . $image['title'] . '"><img width="15" src="' . $this -> plugin_meta['url'] . '/images/zoom.png" /></a>';
				$_html	.= '<div class="p_u_i_name">'.stripslashes( $image['title'] ) . '</div>';
				$_html	.= '</div>';	//input_image
					
					
				$_html .= '</div>';
					
				$img_index++;
			}
		}
		
		$_html .= '<div style="clear:both"></div>';		//container_buttons
			
		$_html .= '</div>';		//container_buttons
		
		echo $_html;
		
		$this -> get_input_js($args);
	}
	
	
	/*
	 * following function is rendering JS needed for input
	*/
	function get_input_js($args){
		?>
			
					<script type="text/javascript">	
					<!--
					jQuery(function($){
	
						// pre upload image click selection
						$(".pre_upload_image").click(function(){

							if($(this).find('input:checkbox').attr("checked") === 'checked'){
								$(this).find('input:checkbox').attr("checked", false);
							}else{
								$(this).find('input:radio, input:checkbox').attr("checked", "checked");
							}
s
						});
					});
					
					//--></script>
					<?php
			}
}