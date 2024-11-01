/*
 * NOTE: all actions are prefixed by plugin shortnam_action_name
 */
var selected_slide = 0;
var total_sections = 0;
var boxes		= new Array();	//checking bound connection

jQuery(function($){

	//setting all input widht to 95% within P tags
	$(".nm-webcontact-box").find('input:text, input[type="email"], textarea, select').css({'width': '100%', 'padding': 0});
	
	$(".nm-webcontact-form").submit(function(event){
		event.preventDefault();
		
		var form = $(this);
		jQuery(form).find("#nm-sending-form").html(
			'<img src="' + nm_webcontact_vars.doing + '">');
	
	var is_ok = validate_data(form);
	var file_ok = true;
	
	if (is_ok && file_ok) {

		var data = form.serialize();
		data = data + '&action=nm_webcontact_send_form_data';
		
		var receivers = Array();
		//check if send_email is on then collect it send email to user
		jQuery('input[type="email"]').each(function(i, item){
			if(jQuery(this).attr('data-sendemail') == 'on')
				receivers.push(jQuery(this).val());
		});
		
		data = data + '&receivers='+receivers;

		jQuery.post(nm_webcontact_vars.ajaxurl, data, function(resp) {

			//console.log(resp); return false;
			
			if(resp.status == 'error'){
				jQuery(form).find("#nm-sending-form").html(jQuery('input:hidden[name="_error_message"]').val()).css('color', 'red');
			}else{
				if(get_option('_redirect_url') != '')
					window.location = get_option('_redirect_url');
				else
					jQuery(form).find("#nm-sending-form").html(resp.message).css('color', 'green');
				
				//jQuery(form).find("#nm-sending-form").html('');
			}
		}, 'json');

	} else {

		//show all sections if hidden
		jQuery(".nm-webcontact-box section").slideDown(200);
		
		jQuery(form).find("#nm-sending-form")
				.html('Please remove above Errors').css('color', 'red');
	}
		
	});
	
	/*
	 * all about section slides
	 * pagination
	 */
	
	if(nm_webcontact_vars.section_slides === 'on'){
		var section_titles_tds = '';
		$(".nm-webcontact-box section").each(function(i, section){
			
			//console.log(section);
			section_titles_tds += '<td>'+$(section).find('h2').html()+'</td>';		
			$(section).hide();
			
			total_sections += 1;
			
		});
		
		//now adding titles to bottom of slider
		$("#section_titles tr").html(section_titles_tds);
		
		//showing only first section at start
		$(".nm-webcontact-box section:first").slideDown(200);
		$("#section_titles tr td:first").css({'color':'#000', 'background-color': '#ccc'});
		set_arrows();
		
		$("#slide_next").click(function(e){
	
			slide_section('next');
			e.preventDefault();
		});
		$("#slide_back").click(function(e){
	
			slide_section('back');
			e.preventDefault();
		});
	}
	
	// pagination ends ==============
	
	//conditional elements handling
	$(".nm-webcontact-box").find('select, input[type="checkbox"], input[type="radio"]').live('change', function(){
		
		var element_name 	= $(this).attr("name");
		var element_value	= '';
		if($(this).attr('data-type') === 'radio'){
			element_value	= $(this).filter(':checked').val();
		}else{
			element_value	= $(this).val();
		}
		
		$(".nm-webcontact-box div, .nm-webcontact-box div.fileupload-box").each(function(i, p_box){

			var parsed_conditions 	= $.parseJSON ($(p_box).attr('data-rules'));
			var box_id				= $(p_box).attr('id');
			var element_box = new Array();
			
			if(parsed_conditions !== null){
				
				//console.log(parsed_conditions);
			
				var _visiblity		= parsed_conditions.visibility;
				var _bound			= parsed_conditions.bound;
				var _total_rules 	= Object.keys(parsed_conditions.rules).length;
				
				 var matched_rules = {};
				 var last_meched_element = '';
				$.each(parsed_conditions.rules, function(i, rule){
					
					var _element 		= rule.elements;
					var _elementvalues	= rule.element_values;
					var _operator 		= rule.operators;
					
					//console.log('_element ='+_element+' element_name ='+element_name);
					var matched_rules = {};	
					
					if(_element === element_name && last_meched_element !== _element){
						
						var temp_matched_rules = {};
						
						switch(_operator){
						
							case 'is':
								
								if(_elementvalues === element_value){
									
									last_meched_element = element_name;
									
									if(boxes[box_id]){
					                    jQuery.each(boxes[box_id], function(j, matched){
					                        if(matched !== undefined){
					                            jQuery.each(matched, function(k,v){
					                            	if(k !== _element){
					                            		temp_matched_rules[k]=v;
						                                element_box.push(temp_matched_rules);
					                            	}
					                            });
					                        }
					                    });
					                }
									
									matched_rules[_element]=element_value;
					                element_box.push(matched_rules);
					                boxes[box_id] = element_box;
								}else{
									
									remove_existing_rules(boxes[box_id], _element);
									jQuery('#'+box_id).find(':input').not(':checkbox, :radio').val('');
									 jQuery('#'+box_id).find(':input','select').removeAttr('checked').removeAttr('selected');
									 jQuery('#'+box_id).find('select, input[type="checkbox"], input[type="radio"]').change();
									
								}		
								break;
								
								
							case 'not':
								
								if(_elementvalues !== element_value){
									
									if(boxes[box_id]){
					                    jQuery.each(boxes[box_id], function(j, matched){
					                        if(matched !== undefined){
					                            jQuery.each(matched, function(k,v){
					                            	if(k !== _element){
					                            		temp_matched_rules[k]=v;
						                                element_box.push(temp_matched_rules);
					                            	}
					                            });
					                        }
					                    });
					                }
									
									matched_rules[_element]=element_value;
					                element_box.push(matched_rules);
					                boxes[box_id] = element_box;
								}else{
									
									remove_existing_rules(boxes[box_id], _element);
									jQuery('#'+box_id).find(':input').not(':checkbox, :radio').val('');
									 jQuery('#'+box_id).find(':input','select').removeAttr('checked').removeAttr('selected');
									 jQuery('#'+box_id).find('select, input[type="checkbox"], input[type="radio"]').change();
									
								}		
								break;
								
								
								case 'greater then':
									
									if(parseFloat(_elementvalues) < parseFloat(element_value) ){
										
										if(boxes[box_id]){
						                    jQuery.each(boxes[box_id], function(j, matched){
						                        if(matched !== undefined){
						                            jQuery.each(matched, function(k,v){
						                            	if(k !== _element){
						                            		temp_matched_rules[k]=v;
							                                element_box.push(temp_matched_rules);
						                            	}
						                            });
						                        }
						                    });
						                }
										
										matched_rules[_element]=element_value;
						                element_box.push(matched_rules);
						                boxes[box_id] = element_box;
									}else{
										
										remove_existing_rules(boxes[box_id], _element);
										jQuery('#'+box_id).find(':input').not(':checkbox, :radio').val('');
									 jQuery('#'+box_id).find(':input','select').removeAttr('checked').removeAttr('selected');
									 jQuery('#'+box_id).find('select, input[type="checkbox"], input[type="radio"]').change();
										
									}		
									break;
									
								
								case 'less then':
									
									if(parseFloat(_elementvalues) > parseFloat(element_value) ){
										
										if(boxes[box_id]){
						                    jQuery.each(boxes[box_id], function(j, matched){
						                        if(matched !== undefined){
						                            jQuery.each(matched, function(k,v){
						                            	if(k !== _element){
						                            		temp_matched_rules[k]=v;
							                                element_box.push(temp_matched_rules);
						                            	}
						                            });
						                        }
						                    });
						                }
										
										matched_rules[_element]=element_value;
						                element_box.push(matched_rules);
						                boxes[box_id] = element_box;
									}else{
										
										remove_existing_rules(boxes[box_id], _element);
										jQuery('#'+box_id).find(':input').not(':checkbox, :radio').val('');
									 jQuery('#'+box_id).find(':input','select').removeAttr('checked').removeAttr('selected');
									 jQuery('#'+box_id).find('select, input[type="checkbox"], input[type="radio"]').change();
										
									}		
									break;
									
						}
						
						set_visibility(p_box, _bound, _total_rules, _visiblity);
						
					}
					});
				}
		});
	});
	
		
});

function set_visibility(p_box, _bound, _total_rules, _visiblity){
	
	var box_id				= jQuery(p_box).attr('id');
	if(boxes[box_id] !== undefined){
		
		console.log(box_id+': total rules = '+_total_rules+' rules matched = '+Object.keys(boxes[box_id]).length);
		switch(_visiblity){
		
		case 'Show':
			if((_bound === 'Any' &&  (Object.keys(boxes[box_id]).length > 0)) || _total_rules === Object.keys(boxes[box_id]).length){
				jQuery(p_box).show(200);
			}else{
				jQuery(p_box).hide(200);
          		
          		//update_rule_childs(element_name);
			}
			break;					
		
		case 'Hide':
			if((_bound === 'Any' &&  (Object.keys(boxes[box_id]).length > 0)) || _total_rules === Object.keys(boxes[box_id]).length){
				jQuery(p_box).hide(200);
				console.log('hiddedn rule '+box_id);
				jQuery(p_box).find('select, input:radio, input:text, textarea').val('');
			}else{
				jQuery(p_box).show(200);
			}
			break;
	}
	}
}


function is_valid_email(email) {
	var pattern = new RegExp(
			/^((([a-z]|\d|[!#\$%&'\*\+\-\/=\?\^_`{\|}~]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])+(\.([a-z]|\d|[!#\$%&'\*\+\-\/=\?\^_`{\|}~]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])+)*)|((\x22)((((\x20|\x09)*(\x0d\x0a))?(\x20|\x09)+)?(([\x01-\x08\x0b\x0c\x0e-\x1f\x7f]|\x21|[\x23-\x5b]|[\x5d-\x7e]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(\\([\x01-\x09\x0b\x0c\x0d-\x7f]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]))))*(((\x20|\x09)*(\x0d\x0a))?(\x20|\x09)+)?(\x22)))@((([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))\.)+(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))\.?$/i);
	return pattern.test(email);
};



function validate_data(form){
	
	var form_data = jQuery.parseJSON( jQuery(form).attr('data-form') );
	var has_error = true;
	var error_in = '';
	
	jQuery.each( form_data, function( key, meta ) {
		
		var type = meta['type'];
		var error_message	= stripslashes( meta['error_message'] );
		  
		if(type === 'text' || type === 'textarea' || type === 'select' || type === 'email' || type === 'date'){
			
			var input_control = jQuery('#'+meta['data_name']);
			
			if(meta['required'] === "on" && jQuery(input_control).val() === ''){
				jQuery(input_control).closest('div').find('span.errors').html(error_message).css('color', 'red');
				has_error = false;
				error_in = meta['data_name']
			}else{
				jQuery(input_control).closest('div').find('span.errors').html('').css({'border' : '','padding' : '0'});
			}
		}else if(type === 'checkbox'){
			
			if(meta['required'] === "on" && jQuery('input:checkbox[name="'+meta['data_name']+'[]"]:checked').length === 0){
				
				jQuery('input:checkbox[name="'+meta['data_name']+'[]"]').closest('div').find('span.errors').html(error_message).css('color', 'red');
				has_error = false;
			}else if(meta['min_checked'] != '' && jQuery('input:checkbox[name="'+meta['data_name']+'[]"]:checked').length < meta['min_checked']){
				jQuery('input:checkbox[name="'+meta['data_name']+'[]"]').closest('div').find('span.errors').html(error_message).css('color', 'red');
				has_error = false;
			}else if(meta['max_checked'] != '' && jQuery('input:checkbox[name="'+meta['data_name']+'[]"]:checked').length > meta['max_checked']){
				jQuery('input:checkbox[name="'+meta['data_name']+'[]"]').closest('div').find('span.errors').html(error_message).css('color', 'red');
				has_error = false;
			}else{
				
				jQuery('input:checkbox[name="'+meta['data_name']+'[]"]').closest('div').find('span.errors').html('').css({'border' : '','padding' : '0'});
				
				}
		}else if(type === 'radio'){
				
				if(meta['required'] === "on" && jQuery('input:radio[name="'+meta['data_name']+'"]:checked').length === 0){
					jQuery('input:radio[name="'+meta['data_name']+'"]').closest('div').find('span.errors').html(error_message).css('color', 'red');
					has_error = false;
					error_in = meta['data_name']
				}else{
					jQuery('input:radio[name="'+meta['data_name']+'"]').closest('div').find('span.errors').html('').css({'border' : '','padding' : '0'});
				}
		}else if(type === 'file'){
			
				var $upload_box = jQuery('#nm-uploader-area-'+meta['data_name']);
				var $uploaded_files = $upload_box.find('input:checkbox:checked');
				if(meta['required'] === "on" && $uploaded_files.length === 0){
					$upload_box.find('span.errors').html(error_message).css('color', 'red');
					has_error = false;
					error_in = meta['data_name']
				}else{
					$upload_box.find('span.errors').html('').css({'border' : '','padding' : '0'});
				}
		}else if(type === 'image'){
			
			var $image_box = jQuery('#pre-uploaded-images-'+meta['data_name']);
			if(meta['required'] === "on" && jQuery('input:radio[name="'+meta['data_name']+'"]:checked').length === 0 && jQuery('input:checkbox[name="'+meta['data_name']+'[]"]:checked').length === 0){
				$image_box.find('span.errors').html(error_message).css('color', 'red');
				has_error = false;
				error_in = meta['data_name']
			}else{
				$image_box.find('span.errors').html('').css({'border' : '','padding' : '0'});
			}
		}else if(type === 'masked'){
			
			var input_control = jQuery('#'+meta['data_name']);
			
			if(meta['required'] === "on" && (jQuery(input_control).val() === '' || jQuery(input_control).attr('data-ismask') === 'no')){
				jQuery(input_control).closest('p').find('span.errors').html(error_message).css('color', 'red');
				has_error = false;
				error_in = meta['data_name'];
			}else{
				jQuery(input_control).closest('div').find('span.errors').html('').css({'border' : '','padding' : '0'});
			}
		}
		
	});
	
	//console.log( error_in ); return false;
	return has_error;
}


function get_option(key) {

	/*
	 * TODO: change plugin shortname
	 */
	var keyprefix = 'nm_webcontact';

	key = keyprefix + key;

	var req_option = '';

	jQuery.each(nm_webcontact_vars.settings, function(k, option) {

		// console.log(k);

		if (k == key)
			req_option = option;
	});

	// console.log(req_option);
	return req_option;
}

function slide_section(move){
	
	//hiding all section first
	jQuery(".nm-webcontact-box section").hide(100);
	//setting td titles to grey back
	jQuery("#section_titles tr td").css({'color':'#ccc', 'background-color': ''});
	
	if(move === 'next'){
	
		selected_slide++;
	
		jQuery(".nm-webcontact-box section").each(function(index, section){
			
			if(index === selected_slide){
				jQuery(section).slideDown(300);
				jQuery("#section_titles tr td:nth-child("+(index+1)+")").css({'color':'#000', 'background-color': '#ccc'});
			}
		});
		
	}else{
		
		selected_slide--;
		
		jQuery(".nm-webcontact-box section").each(function(index, section){
			
			if(index === selected_slide){
				jQuery(section).slideDown(300);				
				jQuery("#section_titles tr td:nth-child("+(index+1)+")").css({'color':'#000', 'background-color': '#ccc'});
			}
		});
	}
	
	set_arrows();
}

function set_arrows(){
	
	jQuery(".webcontact-save-button").hide();
	
	if(selected_slide <= 0){		//just started
		
		jQuery("#slide_back").hide();
		jQuery("#slide_next").show();
		
	}else if(selected_slide > 0 && selected_slide < (total_sections-1)){		//somewhere between
		
		jQuery("#slide_back").show();
		jQuery("#slide_next").show();
	}else if(selected_slide >= (total_sections-1)){		// it is last section
		
		jQuery(".webcontact-save-button").show();
		
		jQuery("#slide_back").show();
		jQuery("#slide_next").hide();
	}
}


function update_rule_childs(element_name, element_values){
	
	jQuery(".nm-webcontact-box > p, .nm-webcontact-box div.fileupload-box").each(function(i, p_box){

		var parsed_conditions 	= jQuery.parseJSON (jQuery(p_box).attr('data-rules'));
		var box_id				= jQuery(p_box).attr('id');
		
		if(parsed_conditions !== null){
		
			var _visiblity		= parsed_conditions.visibility;
			var _bound			= parsed_conditions.bound;
			var _total_rules 	= Object.keys(parsed_conditions.rules).length;
			
			 var matched_rules = {};
			 var last_meched_element = '';
			jQuery.each(parsed_conditions.rules, function(i, rule){
				
				var _element 		= rule.elements;
				var _elementvalues	= rule.element_values;
				var _operator 		= rule.operators;
				
				//console.log('_element ='+_element+' element_name ='+element_name);
				var matched_rules = {};	
				
				if(element_values === 'child')
					_elementvalues = element_values;
				
				if(_element === element_name && _elementvalues === element_values){
					//console.log('Hiding _element ='+_element+' under box ='+jQuery(p_box).find('select').attr('name'));
					console.log('hiddedn rule '+element_name+' value ' + element_values + 'under box = ' + jQuery(p_box).attr('id'));
					jQuery(p_box).hide(300, function(){
						update_rule_childs(jQuery(this).find('select').attr('name'), 'child');
					});
					
				}
			});
		}
});
	
}
	
function remove_existing_rules(box_rules, element){
	
	if(box_rules){
        jQuery.each(box_rules, function(j, matched){
            if(matched !== undefined){
                jQuery.each(matched, function(k,v){
                	if(k === element){
                  		delete box_rules[j];
                  		update_rule_childs(k, v);
                	}
                });
            }
        });
    }
}

function stripslashes (str) {
	  // +   original by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
	  // +   improved by: Ates Goral (http://magnetiq.com)
	  // +      fixed by: Mick@el
	  // +   improved by: marrtins
	  // +   bugfixed by: Onno Marsman
	  // +   improved by: rezna
	  // +   input by: Rick Waldron
	  // +   reimplemented by: Brett Zamir (http://brett-zamir.me)
	  // +   input by: Brant Messenger (http://www.brantmessenger.com/)
	  // +   bugfixed by: Brett Zamir (http://brett-zamir.me)
	  // *     example 1: stripslashes('Kevin\'s code');
	  // *     returns 1: "Kevin's code"
	  // *     example 2: stripslashes('Kevin\\\'s code');
	  // *     returns 2: "Kevin\'s code"
	  return (str + '').replace(/\\(.?)/g, function (s, n1) {
	    switch (n1) {
	    case '\\':
	      return '\\';
	    case '0':
	      return '\u0000';
	    case '':
	      return '';
	    default:
	      return n1;
	    }
	  });
	}