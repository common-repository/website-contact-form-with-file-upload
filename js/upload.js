var the_i=0;
files = new Array();

String.prototype.trunc = 
    function(n){
        return this.substr(0,n-1)+(this.length>n?'&hellip;':'');
};
 

function setup_uploader(file_input, button_text, files_allowed, types_allowed, file_size_limit, button_width, photo_editing, editing_tools) {
	
	


}

function setup_uploader_widget(file_input, button_text, files_allowed, types_allowed, file_size_limit, button_width, photo_editing, editing_tools) {
	
	var uploader = new jQuery("#nm-uploader-area-fileuplaod").plupload.Uploader({
		// General settings
		runtimes : 'html5,flash,silverlight,html4',
		url : nm_webcontact_vars.ajaxurl,
		multipart_params : {
			'action' : 'nm_webcontact_upload_file',
			'username' : nm_webcontact_vars.current_user,
	    },
		// User can upload no more then 20 files in one go (sets multiple_queues to false)
		max_file_count: 20,
		
		chunk_size: '1mb',

		// Resize images on clientside if we can
		resize : {
			width : 200, 
			height : 200, 
			quality : 90,
			crop: true // crop to exact dimensions
		},
		
		filters : {
			// Maximum file size
			max_file_size : '1000mb',
			// Specify what files to browse for
			mime_types: [
				{title : "Image files", extensions : "jpg,gif,png,pdf"},
				{title : "Zip files", extensions : "zip"}
			]
		},

		// Rename files by clicking on their titles
		rename: true,
		
		// Sort files
		sortable: true,

		// Enable ability to drag'n'drop files onto the widget (currently only HTML5 supports that)
		dragdrop: true,

		// Views to activate
		views: {
			list: true,
			thumbs: true, // Show thumbs
			active: 'thumbs'
		},
		
		// when a file is uploaded
		/*FileUploaded: function(up, file, info){
			
			console.log('[FileUploaded] File:' + file + "Info: " + info);
		},
		
		FilesAdded: function(up, file){
			
			console.log('[FileUploaded] File:' + file);
		},*/

		// Flash settings
		flash_swf_url : nm_webcontact_vars.plugin_url + '/js/uploader/Moxie.swf',

		// Silverlight settings
		silverlight_xap_url : nm_webcontact_vars.plugin_url + '/js/uploader/Moxie.xap'
	});
	
	uploader.bind('FileUploaded', function(up, file, res){
		alert('hi added');
	});
}



 

function check_file_type(file, file_types_allowed) {

	if(file_types_allowed == 'all')
		return true;
	
	
	file_types_allowed = file_types_allowed.split(",");
	console.log(file_types_allowed);

	var file_name = file.name;
	var ext = file_name.substring(file_name.lastIndexOf('.') + 1); // Extract
																	// EXT
	ext = ext.toLowerCase();

	var is_allowed = false;
	jQuery.each(file_types_allowed, function(i, allowed_ext) {

		// console.log(item);
		if (ext == allowed_ext) {

			is_allowed = true;
		}
	});

	if (!is_allowed) {

		alert(get_option('_filetype_error'));
		return false;
	} else {
		return true;
	}

}

function show_thumbs(files, file_input, photo_editing, editing_tools){
	
	jQuery("#uploaded_files-"+file_input).html('');
	//console.log(files);
	
	var del_file = nm_webcontact_vars.plugin_url+'/images/delete_16.png';
	var edit_file = nm_webcontact_vars.plugin_url+'/images/edit-photo.png';
	
	var ext,file_path,html,is_image;
	
	var hidden_file_name = 'files_'+file_input;
	var existing_files = jQuery('input[name="'+hidden_file_name+'"]').val().split(",");
	
	//console.log( existing_files );
	
	jQuery.each(existing_files, function(i, item){
        
		if(item != ''){
			//console.log('show thumb of'+item);
					
			ext = item.substring(item.lastIndexOf('.') + 1);
			
			ext = ext.toLowerCase();
			the_i++;
			
			if(ext == 'png' || ext == 'gif' || ext == 'jpg' || ext == 'jpeg'){
				file_path = nm_webcontact_vars.file_upload_path_thumb + item;
				is_image = true;
			}else{
				file_path = nm_webcontact_vars.plugin_url+'/images/file.png';
				is_image = false;
			}
			
			var image_id = 'thumb-'+new Date().getTime();;
			
			html = '<div style="border-bottom: #ccc 1px solid;" id="f-'+the_i+'">';
				html += '<img style="float:left;" src="'+file_path+'" id="'+image_id+'">';
				html += '<span style="float:left;padding: 15px 0 0 5px">'+item.trunc(20)+'</span>';
				html += '<span style="float:right;padding: 15px 5px 0 0">';
					html += '<img src="'+del_file+'" onclick="remove_uploaded_file(\''+item+'\', '+the_i+', '+is_image+', \''+file_input+'\')">';
				html += '</span>';
				
				if(photo_editing === 'on'){
					html += '<span style="float:right;padding: 15px 5px 0 0">';
					html += '<img src="'+edit_file+'" onclick="return launch_aviary_editor(\''+image_id+'\', \''+nm_webcontact_vars.file_upload_path + item+'\', \''+item+'\', \''+editing_tools+'\')">';
				html += '</span>';
				}
			html += '</div>';
			
			html += '<div style="clear:both"></div>';
			
			jQuery("#uploaded_files-"+file_input).append(html);
		}
			
		});
}

function remove_uploaded_file(filename, index, isimage, file_input){

	var hidden_file_name = 'files_'+file_input;
	var existing_files = jQuery('input[name="'+hidden_file_name+'"]').val().split(",");
	
	jQuery("#f-"+index).find('img').attr('src', nm_webcontact_vars.plugin_url+'/images/loading.gif');
	
	var data = {action: 'nm_webcontact_delete_file', file_name: filename, is_image: isimage};
	
	jQuery.post(nm_webcontact_vars.ajaxurl, data, function(resp){
		//alert(resp);
		jQuery("#f-"+index).remove();		
		
	});
	
	//updating files Array
	jQuery.each(existing_files, function(i, item){
		
		if(item == filename)
			existing_files.splice(i, 1);
		
		//now updating hiddend input
		jQuery('input[name="'+hidden_file_name+'"]').attr('value', existing_files);
	});

}

/*function launch_aviary_editor(id, src, file_name, editing_tools) {
	editing_tools = (editing_tools == '' && editing_tools == undefined) ? 'all' : editing_tools;
    featherEditor.launch({
        image: id,
        url: src,
        tools: editing_tools,
        postData			: {filename: file_name},
    });
   return false;
}*/