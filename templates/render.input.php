<?php
/*
 * rendering product meta on product page
*/

global $nmcontact;

/* $args = array('name'	=> 'title', 'data-attr'	=> 'Lovelyman', 'col'=>50, 'row'=>5);
$nmcontact -> inputs['textarea'] -> render_input($args, 'The value'); */

$single_form = $nmcontact -> get_forms( $nmcontact -> form_id );
/* $nmcontact -> pa($single_form); */

$existing_meta 		= json_decode( $single_form -> the_meta, true);

//webcontact_pa($existing_meta);

if($existing_meta){
?>

<style>
<?php
/*
 * pasting the custom css if used in form settings
 */
echo stripslashes( strip_tags($single_form -> form_style));
?>
</style>

<?php 

echo '<form class="nm-webcontact-form" id="webcontact-'.$nmcontact -> form_id .'"';
echo 'data-form="'.esc_attr( $single_form -> the_meta ).'">';
echo '<div id="nm-webcontact-box-'. $nmcontact->form_id .'" class="nm-webcontact-box">';

echo '<div class="grid grid-pad">';
		$editing = (isset($single_form -> photo_editing)) ? $single_form -> photo_editing : '' ;
		
		/*
		 * forms extra information being sent hidden
		*/
		echo '<input type="hidden" name="_form_id" value="'.$nmcontact -> form_id.'">';
		echo '<input type="hidden" name="_sender_email" value="'.$single_form -> sender_email.'">';
		echo '<input type="hidden" name="_sender_name" value="'.$single_form -> sender_name.'">';
		echo '<input type="hidden" name="_subject" value="'.$single_form -> subject.'">';
		echo '<input type="hidden" name="_receiver_emails" value="'.$single_form -> receiver_emails.'">';
		//echo '<input type="hidden" name="_reply_to" value="'.$single_form -> reply_to.'">';
		echo '<input type="hidden" name="_send_file_as" value="'.$single_form -> send_file_as.'">';
		echo '<input type="hidden" name="_photo_editing" value="'.$editing.'">';
		echo '<input type="hidden" name="_aviary_api_key" value="'.$single_form -> aviary_api_key.'">';
		echo '<input type="hidden" name="_success_message" value="'.stripslashes($single_form ->success_message).'" />';
		echo '<input type="hidden" name="_error_message" value="'.stripslashes($single_form -> error_message).'" />';

		$row_size = 0;
		$started_section = '';
		$grid_control = 0;
		
		foreach($existing_meta as $key => $meta)
		{
			
			/* webcontact_pa($meta); */
			$type = $meta['type'];
			$dataname = (isset($meta['data_name'])) ? $meta['data_name'] : '' ;
			$name = strtolower(preg_replace("![^a-z0-9]+!i", "_", $dataname));
			
			// conditioned elements

			$visibility = '';
			$conditions_data = '';
			if (isset($meta['logic']) && ($meta['logic'] == 'on')) {
				
				if($meta['conditions']['visibility'] == 'Show')
					$visibility = 'display: none';
		
				$conditions_data	= 'data-rules="'.esc_attr( json_encode($meta['conditions'] )).'"';
			}
			$wide = (isset($meta['width'])) ? $meta['width'] : 0 ;
			if(($row_size + intval($wide)) > 100 || $type == 'section'){

				echo '<div style="clear:both; margin: 0;"></div>';

				if($type == 'section'){
					$row_size = 100;
				}else{

					$row_size = intval( $meta['width'] );
				}

			}else{

				$row_size += intval( $meta['width'] );
			}


			$show_asterisk 		= (isset($meta['required'])) ? '<span class="show_required"> *</span>' : '';
			$show_description	= ($meta['description']) ? '<span class="show_description">'.stripslashes($meta['description']).'</span>' : '';

			$wide = (isset($meta['width'])) ? $meta['width'] : 0 ;
			$the_width = intval($wide);
			$grid_control = $the_width + $grid_control;
			$the_margin = '1%';

			$field_label = $meta['title'] . $show_asterisk . $show_description;

			$args = '';
			
			switch($type)
			{
				
				case 'text':

					$requi = (isset($meta['required'])) ? $meta['required'] : '' ;	
					echo '<div id="box-'.$name.'" class="col-'.$meta['width'].'-12"  style="'.$visibility.'" '.$conditions_data.'>';
					$args = array(	'name'			=> $name,
									'id'			=> $name,
									'data-type'		=> $type,
									'data-req'		=> $requi,
									'data-message'	=> $meta['error_message']);
					echo '<label for="'.$name.'">'. $field_label.' </label> <br />';
					
					$nmcontact -> inputs[$type]	-> render_input($args);					
					
					//for validtion message
					echo '<span class="errors"></span>';
					echo '</div>';
					if ($grid_control % 12 == 0) {
						echo '</div><div class="grid grid-pad">';
					}
					break;
				
				case 'number':

					$requi = (isset($meta['required'])) ? $meta['required'] : '' ;

					$args = array(	'name'			=> $name,
									'id'			=> $name,
									'data-type'		=> $type,
									'data-req'		=> $requi,
									'min'			=> $meta['min_number'],
									'max'			=> $meta['max_number'],
									'step'			=> $meta['step'],
									'data-message'	=> $meta['error_message']);
					echo '<div id="box-'.$name.'" class="col-'.$meta['width'].'-12"  style="'.$visibility.'" '.$conditions_data.'>';
					echo '<label for="'.$name.'">'. $field_label.' </label> <br />';
					
					$nmcontact -> inputs[$type]	-> render_input($args);					
					
					//for validtion message
					echo '<span class="errors"></span>';
					echo '</div>';
					if ($grid_control % 12 == 0) {
						echo '</div><div class="grid grid-pad">';
					}
					break;
					
				case 'masked':

					$requi = (isset($meta['required'])) ? $meta['required'] : '' ;
						
					$args = array(	'name'			=> $name,
					'id'			=> $name,
					'data-type'		=> $type,
					'data-req'		=> $requi,
					'data-mask'		=> $meta['mask'],
					'data-ismask'	=> "no",
					'data-message'	=> $meta['error_message']);
					echo '<div id="box-'.$name.'" class="col-'.$meta['width'].'-12"  style="'.$visibility.'" '.$conditions_data.'>';
					echo '<label for="'.$name.'">'. $field_label.' </label> <br />';
						
					$nmcontact -> inputs[$type]	-> render_input($args);
						
					//for validtion message
					echo '<span class="errors"></span>';
					echo '</div>';
					if ($grid_control % 12 == 0) {
						echo '</div><div class="grid grid-pad">';
					}
					break;
				
				case 'hidden':

					$args = array(	'name'			=> $name,
									'id'			=> $name,
									'data-type'		=> $type,
								);
					
					$nmcontact -> inputs[$type]	-> render_input($args);	
					break;
					
			
				case 'date':
					$requi = (isset($meta['required'])) ? $meta['required'] : '' ;

					$args = array(	'name'			=> $name,
									'id'			=> $name,
									'data-type'		=> $type,
									'data-req'		=> $requi,
									'data-message'	=> $meta['error_message'],
									'data-format'	=> $meta['date_formats']);
			
					echo '<div id="box-'.$name.'" class="col-'.$meta['width'].'-12"  style="'.$visibility.'" '.$conditions_data.'>';
					echo '<label for="'.$name.'">'. $field_label.' </label> <br />';
			
					$nmcontact -> inputs[$type]	-> render_input($args);
					//for validtion message
					echo '<span class="errors"></span>';
					echo '</div>';
					if ($grid_control % 12 == 0) {
						echo '</div><div class="grid grid-pad">';
					}
					break;
					
				
				case 'color':
					$requi = (isset($required)) ? $required : '' ;
					$errmsg = (isset($error_message)) ? $error_message : '' ;
					$args = array(	'name'			=> $name,
					'id'			=> $name,
					'data-type'		=> $type,
					'data-req'		=> $requi,
					'data-message'	=> $errmsg,
					'default-color'	=> $meta['default_color'],
					'show-onload'	=> $meta['show_onload'],
					'show-palletes'	=> $meta['show_palletes']);
						
					echo '<div id="box-'.$name.'" class="col-'.$meta['width'].'-12"  style="'.$visibility.'" '.$conditions_data.'>';
					printf( __('<label for="%1$s">%2$s</label><br />', 'nm-webcontact'), $name, $field_label );
						
					$nmcontact -> inputs[$type]	-> render_input($args);
					//for validtion message
					echo '<span class="errors"></span>';
					echo '</div>';
					if ($grid_control % 12 == 0) {
						echo '</div><div class="grid grid-pad">';
					}
					break;
		
				case 'email':
					
					$requi = (isset($meta['required'])) ? $meta['required'] : '' ;
					$sendmail = (isset($meta['send_email'])) ? $meta['send_email'] : '' ;

					$args = array(	'name'			=> $name,
									'id'			=> $name,
									'data-type'		=> $type,
									'data-req'		=> $requi,
									'data-message'	=> $meta['error_message'],
									'data-sendemail'=> $sendmail);
					echo '<div id="box-'.$name.'" class="col-'.$meta['width'].'-12"  style="'.$visibility.'" '.$conditions_data.'>';
					echo '<label for="'.$name.'">'. $field_label.' </label> <br />';
					
					$nmcontact -> inputs[$type]	-> render_input($args);
					//for validtion message
					echo '<span class="errors"></span>';
					echo '</div>';
					if ($grid_control % 12 == 0) {
						echo '</div><div class="grid grid-pad">';
					}
					break;
					
				
				case 'textarea':

					$requi = (isset($meta['required'])) ? $meta['required'] : '' ;
				
					$args = array(	'name'			=> $name,
							'id'			=> $name,
							'data-type'		=> $type,
							'data-req'		=> $requi,
							'data-message'	=> $meta['error_message']);
					
					echo '<div id="box-'.$name.'" class="col-'.$meta['width'].'-12"  style="'.$visibility.'" '.$conditions_data.'>';
					echo '<label for="'.$name.'">'. $field_label.' </label> <br />';
					
					$nmcontact -> inputs[$type]	-> render_input($args);				
					//for validtion message
					echo '<span class="errors"></span>';
					echo '</div>';
					if ($grid_control % 12 == 0) {
						echo '</div><div class="grid grid-pad">';
					}
					break;
					
					
				case 'select':
				
					$options = explode("\n", $meta['options']);
					$default_selected = $meta['selected'];
					
					$requi = (isset($meta['required'])) ? $meta['required'] : '' ;
					$args = array(	'name'			=> $name,
									'id'			=> $name,
									'data-type'		=> $type,
									'data-req'		=> $requi,
									'data-message'	=> $meta['error_message']);
				
					echo '<div id="box-'.$name.'" class="col-'.$meta['width'].'-12"  style="'.$visibility.'" '.$conditions_data.'>';
					echo '<label for="'.$name.'">'. $field_label.' </label> <br />';
					
					$nmcontact -> inputs[$type]	-> render_input($args, $options, $default_selected);
				
					//for validtion message
					echo '<span class="errors"></span>';
					echo '</div>';
					if ($grid_control % 12 == 0) {
						echo '</div><div class="grid grid-pad">';
					}
					break;
					
				case 'countries':
				
					
					$options = explode("\n", $meta['options']);
					$default_selected = $meta['selected'];
					$requi = (isset($meta['required'])) ? $meta['required'] : '' ;
					$args = array(	'name'			=> $name,
							'id'			=> $name,
							'data-type'		=> $type,
							'data-req'		=> $requi,
							'data-message'	=> $meta['error_message']);
				
					echo '<div id="box-'.$name.'" class="col-'.$meta['width'].'-12"  style="'.$visibility.'" '.$conditions_data.'>';
					echo '<label for="'.$name.'">'. $field_label.' </label> <br />';
						
					$nmcontact -> inputs[$type]	-> render_input($args, $options, $default_selected);
				
					//for validtion message
					echo '<span class="errors"></span>';
					echo '</div>';
					if ($grid_control % 12 == 0) {
						echo '</div><div class="grid grid-pad">';
					}
					break;
						
				case 'radio':
					
					$options = explode("\n", $meta['options']);
					$default_selected = $meta['selected'];
					$requi = (isset($meta['required'])) ? $meta['required'] : '' ;
					
					$args = array(	'name'			=> $name,
									'id'			=> $name,
									'data-type'		=> $type,
									'data-req'		=> $requi,
									'data-message'	=> $meta['error_message']);
				
					echo '<div id="box-'.$name.'" class="col-'.$meta['width'].'-12"  style="'.$visibility.'" '.$conditions_data.'>';
					echo '<label for="'.$name.'">'. $field_label.' </label> <br />';
					
					$nmcontact -> inputs[$type]	-> render_input($args, $options, $default_selected);
					//for validtion message
					echo '<span class="errors"></span>';
					echo '</div>';
					if ($grid_control % 12 == 0) {
						echo '</div><div class="grid grid-pad">';
					}
					break;
		
				case 'checkbox':
			
					$options = explode("\n", $meta['options']);
					$defaul_checked = explode("\n", $meta['checked']);
					$requi = (isset($meta['required'])) ? $meta['required'] : '' ;
		
					$args = array(	'name'			=> $name,
							'id'			=> $name,
							'data-type'		=> $type,
							'data-req'		=> $requi,
							'data-message'	=> $meta['error_message']);
					
					echo '<div id="box-'.$name.'" class="col-'.$meta['width'].'-12"  style="'.$visibility.'" '.$conditions_data.'>';
					echo '<label for="'.$name.'">'. $field_label.' </label> <br />';
					
					$nmcontact -> inputs[$type]	-> render_input($args, $options, $defaul_checked);
					//for validtion message
					echo '<span class="errors"></span>';
					echo '</div>';
					if ($grid_control % 12 == 0) {
						echo '</div><div class="grid grid-pad">';
					}
					break;
					
				case 'file':
				
					$label_select = ($meta['button_label_select'] == '' ? __('Select files', 'nm-personalizedproduct') : $meta['button_label_select']);
					$label_upload = (isset($meta['button_label_upload']) == '' ? __('Upload files', 'nm-personalizedproduct') : $meta['button_label_upload']);
					$files_allowed = ($meta['files_allowed'] == '' ? 1 : $meta['files_allowed']);
					$file_types = ($meta['file_types'] == '' ? 'jpg,png,gif' : $meta['file_types']);
					$file_size = ($meta['file_size'] == '' ? '10mb' : $meta['file_size']);
					$chunk_size = ($meta['chunk_size'] == '' ? '5mb' : $meta['chunk_size']);

					$requi = (isset($meta['required'])) ? $meta['required'] : '' ;
					$photoedit = (isset($meta['photo_editing'])) ? $meta['photo_editing'] : '' ;
					
					$args = array(	'name'			=> $name,
									'id'			=> $name,
									'data-type'		=> $type,
									'data-req'		=> $requi,
									'data-message'	=> $meta['error_message'],
									'button-label-select'	=> $label_select,
									'button-label-upload'	=> $label_upload,
									'files-allowed'			=> $files_allowed,
									'file-types'			=> $file_types,
									'file-size'				=> $file_size,
									'chunk-size'			=> $chunk_size,
									'button-class'			=> $meta['button_class'],
									'photo-editing'			=> $photoedit,
									'editing-tools'			=> $meta['editing_tools'],
									'aviary-api-key'		=> $single_form -> aviary_api_key,
									'popup-width'	=> $meta['popup_width'],
									'popup-height'	=> $meta['popup_height']);
					
					echo '<div id="box-'.$name.'" class="fileupload-box col-'.$meta['width'].'-12"  style="'.$visibility.'" '.$conditions_data.'>';
					echo '<label for="'.$name.'">'. $field_label.'</label>';
					echo '<div id="nm-uploader-area-'. $name.'" class="nm-uploader-area">';
					
					$nmcontact -> inputs[$type]	-> render_input($args);
				
					echo '<span class="errors"></span>';
				
					echo '</div>';		//.nm-uploader-area
					echo '</div>';
				
					// adding thickbox support
					add_thickbox();
					if ($grid_control % 12 == 0) {
						echo '</div><div class="grid grid-pad">';
					}
					break;
					
					
					case 'image':
					
						$requi = (isset($meta['required'])) ? $meta['required'] : '' ;
						$default_selected = (isset($meta['selected'])) ? $meta['selected'] : '' ;
						$multiple = (isset($meta['multiple_allowed'])) ? $meta['multiple_allowed'] : '' ;
						$args = array(	'name'			=> $name,
								'id'			=> $name,
								'data-type'		=> $type,
								'data-req'		=> $requi,
								'data-message'	=> $meta['error_message'],
								'popup-width'	=> $meta['popup_width'],
								'popup-height'	=> $meta['popup_height'],
								'multiple-allowed' => $multiple);
					
						echo '<div id="pre-uploaded-images-'.$name.'" class="fileupload-box col-'.$meta['width'].'-12"  style="'.$visibility.'" '.$conditions_data.'>';
						echo '<label for="'.$name.'">'. $field_label.' </label> <br />';
							
						$nmcontact -> inputs[$type]	-> render_input($args, $meta['images'], $default_selected);
					
						//for validtion message
						echo '<span class="errors"></span>';
						echo '</div>';
						add_thickbox();
					if ($grid_control % 12 == 0) {
						echo '</div><div class="grid grid-pad">';
					}
					break;
					
					
					case 'section':
						
						if($started_section)		//if section already started then close it first
							echo '</section>';
						
						$section_title 		= strtolower(preg_replace("![^a-z0-9]+!i", "_", $meta['title'])); 
						$started_section 	= 'webcontact-section-'.$section_title;
						
						$args = array(	'id'			=> $started_section,
								'data-type'		=> $type,
								'title'			=> $meta['title'],
								'description'			=> $meta['description'],
								);
						
						$nmcontact -> inputs[$type]	-> render_input($args);
						
					break;

				case 'autocomplete':
				
					$options = explode("\n", $meta['options']);
					$default_selected = (isset($meta['selected'])) ? $meta['selected'] : '' ;

					$default_hint  = ($meta['hint_text']) ? $meta['hint_text'] : 'Type to search...';
					$default_no_result  = ($meta['no_result_text']) ? $meta['no_result_text'] : 'No results...';
					$requi = (isset($meta['required'])) ? $meta['required'] : '' ;
					$args = array(	'name'			=> $name,
									'id'			=> $name,
									'data-type'		=> $type,
									'data-req'		=> $requi,
									'hint-text'		=> $default_hint,
									'no-result-text'=> $default_no_result,
									'data-message'	=> $meta['error_message']);
				
					echo '<div id="box-'.$name.'" class="col-'.$meta['width'].'-12"  style="'.$visibility.'" '.$conditions_data.'>';
					echo '<label for="'.$name.'">'. $field_label.' </label> <br />';
					
					$nmcontact -> inputs[$type]	-> render_input($args, $options, $default_selected);
				
					//for validtion message
					echo '<span class="errors"></span>';
					echo '</div>';
					if ($grid_control % 12 == 0) {
						echo '</div><div class="grid grid-pad">';
					}
					break;					
		
				}
		}
		
		
		echo '<div style="clear: both"></div>';
	echo "</div>"; // ends grid
	
	echo '</div>';  //ends nm-webcontact-box
	
	
	echo '<p class="webcontact-save-button"><input type="submit" class="'.$single_form -> button_class.'" value="'.$single_form -> button_label.'"></p>';
	echo '<span id="nm-sending-form"></span>';
	wp_nonce_field('doing_contact','nm_webcontact_nonce');
	echo '</form>';
}

	//	<!-- if section_slides = yes  -->

	if($single_form -> section_slides == 'on'){
		
	echo '<table>';
		echo '<tr>';
			echo '<td style="text-align: left; width: 10%"><a href="#!" id="slide_back"><img';
					echo 'border="0" width="32"';
					echo 'src="'.$nmcontact -> plugin_meta['url'].'/images/left-arrow.png">';
			echo '</a>';
			echo '</td>';
			echo '<td></td>';
			echo '<td style="text-align: right; width: 10%"><a href="#!"';
				echo 'id="slide_next"><img border="0" width="32"';
					echo 'src="'.$nmcontact -> plugin_meta['url'].'/images/right-arrow.png">';
			echo '</a>';
			echo '</td>';
		echo '</tr>';

		echo '<tr>';
			echo '<td colspan="3">';
				echo '<table id="section_titles">';
					echo '<tr>';
					echo '</tr>';
				echo '</table>';
			echo '</td>';

		echo '</tr>';
	echo '</table>';
	}

?>
