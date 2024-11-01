<?php
/*
 * this is main plugin class
 */


/* ======= the model main class =========== */
if (! class_exists ( 'NM_Framwork_V1_nmcontact' )) {
	$_framework = dirname ( __FILE__ ) . DIRECTORY_SEPARATOR . 'nm-framework.php';
	if (file_exists ( $_framework ))
		include_once ($_framework);
	else
		die ( 'Reen, Reen, BUMP! not found ' . $_framework );
}

/*
 * [1]
 */
class NM_WP_ContactForm extends NM_Framwork_V1_nmcontact {
	
	
	private static $ins = null;
	
	public static function init()
	{
		add_action('plugins_loaded', array(self::get_instance(), '_setup'));
	}
	
	public static function get_instance()
	{
		// create a new object if it doesn't exist.
		is_null(self::$ins) && self::$ins = new self;
		return self::$ins;
	}
	
	
	static $tbl_forms = 'nm_forms';
	var $allow_file_upload;
	var $inputs;
	
	
	/*
	 * plugin constructur
	 */
	function _setup() {
		
		// setting plugin meta saved in config.php
		$this->plugin_meta = get_plugin_meta_webcontact ();
		
		// getting saved settings
		$this->plugin_settings = get_option ( $this->plugin_meta ['shortname'] . '_settings' );
		
		// file upload dir name
		$this->contact_files = 'contact_files';
		
		// this will hold form form_id
		$this->form_id = '';
		
		// populating $inputs with NM_Inputs object
		$this->inputs = $this->get_all_inputs ();
		
		/*
		 * [2] TODO: update scripts array for SHIPPED scripts only use handlers
		 */
		// setting shipped scripts
		$this->wp_shipped_scripts = array (
				'jquery' 
		);
		
		/*
		 * [3] TODO: update scripts array for custom scripts/styles
		 */
		// setting plugin settings
		$this->plugin_scripts = array (
				
				array (
						'script_name' => 'scripts',
						'script_source' => '/js/script.js',
						'localized' => true,
						'type' => 'js' ,
						'depends' => '',
						'in_footer' => true
				),
				
				array (
						'script_name' => 'styles',
						'script_source' => '/plugin.styles.css',
						'localized' => false,
						'type' => 'style' 
				),
				
				array (
						'script_name' => 'nm-ui-style',
						'script_source' => '/js/ui/css/smoothness/jquery-ui-1.10.3.custom.min.css',
						'localized' => false,
						'type' => 'style',
						'page_slug' => array (
								'nm-new-form' 
						) 
				),
		);
		
		/*
		 * [4] Localized object will always be your pluginshortname_vars e.g: pluginshortname_vars.ajaxurl
		 */
		$this->localized_vars = array (
				'ajaxurl' => admin_url( 'admin-ajax.php', (is_ssl() ? 'https' : 'http') ),
				'plugin_url' => $this->plugin_meta ['url'],
				'doing' => $this->plugin_meta ['url'] . '/images/loading.gif',
				'settings' => $this->plugin_settings,
				'file_upload_path_thumb' => $this->get_file_dir_url ( true ),
				'file_upload_path' => $this->get_file_dir_url (),
				'file_meta' => '',
				'section_slides' => '',
				'is_html5' => true,
		);
		
		/*
		 * [5] TODO: this array will grow as plugin grow all functions which need to be called back MUST be in this array setting callbacks
		 */
		// following array are functions name and ajax callback handlers
		$this->ajax_callbacks = array (
				'save_settings', // do not change this action, is for admin
				'save_form_meta',
				'update_form_meta',
				'send_form_data',
				'upload_file',
				'delete_file',
				'delete_meta',
				'save_edited_photo',
				'validate_api'
		);
		
		/*
		 * plugin localization being initiated here
		 */
		
		add_action ( 'init', array (
				$this,
				'wpp_textdomain' 
		) );
		
		
		/*
		 * plugin main shortcode if needed
		 */
		add_shortcode ( 'nm-wp-contact', array (
				$this,
				'render_shortcode_template' 
		) );
		
		/*
		 * hooking up scripts for front-end
		 */
		add_action ( 'wp_enqueue_scripts', array (
				$this,
				'load_scripts' 
		) );
		
		/*
		 * registering callbacks
		 */
		$this->do_callbacks ();
		
		/*
		 * add custom post type support if enabled
		 */
		add_action ( 'init', array (
				$this,
				'enable_custom_post' 
		) );
		
		
		add_action('setup_plugin_styles_and_scripts', array($this, 'get_connected_to_load_it'));
		
		//form post action for importing files in existing-meta.php
		add_action( 'admin_post_nm_importing_file_webcontact', array($this, 'process_nm_importing_file_webcontact') );
		
		
	}
	
	
	// i18n and l10n support here
	// plugin localization
	function wpp_textdomain() {
		$locale_dir = dirname( plugin_basename( __FILE__ ) ) . '/locale/';
		load_plugin_textdomain('nm-webcontact', false, $locale_dir);
		
		$this -> nm_export_webcontact();
	}
	
	
	/*
	 * =============== NOW do your JOB ===========================
	 */
	function enable_custom_post() {
		register_post_type ( 'nm-forms', array (
				'labels' => array (
						'name' => __ ( 'Contact Forms' ),
						'singular_name' => __ ( 'Contact Form' ),
						'add_new' => 'Add New',
						'add_new_item' => 'Add Contact Form',
						'edit' => 'Edit',
						'edit_item' => 'Edit Contact Form',
						'new_item' => 'New Contact Form',
						'view' => 'View',
						'view_item' => 'View Contact Form',
						'search_items' => 'Search Contact Form',
						'not_found' => 'No Contact Form found',
						'not_found_in_trash' => 'No Contact Form found in Trash',
						'parent' => 'Parent Contact Form' 
				),
				'public' => true,
				'supports' => array (
						'title',
						'editor',
						'custom-fields' 
				),
				'menu_icon' => $this->plugin_meta ['logo'] 
		) );
	}
	
	/*
	 * saving form meta in admin call
	 */
	function save_form_meta() {
		
		// print_r($_REQUEST); exit;
		global $wpdb;
		
		extract ( $_REQUEST );
		
		$dt = array (
				'form_name' => $form_name,
				'sender_email' => $sender_email,
				'sender_name' => $sender_name,
				'subject' => $subject,
				'receiver_emails' => $receiver_emails,
				'button_label' => $button_label,
				'button_class' => $button_class,
				'success_message' => stripslashes ( $success_message ),
				'error_message' => stripslashes ( $error_message ),
				'send_file_as' => $send_file_as,
				'aviary_api_key' => trim ( $aviary_api_key ),
				'section_slides' => $section_slides,
				'form_style' => $form_style,
				'the_meta' => json_encode ( $form_meta ),
				'form_created' => current_time ( 'mysql' ) 
		);
		
		$format = array (
				'%s',
				'%s',
				'%s',
				'%s',
				'%s',
				'%s',
				'%s',
				'%s',
				'%s',
				'%s',
				'%s',
				'%s',
				'%s',
				'%s',
				'%s' 
		);
		
		$res_id = $this->insert_table ( self::$tbl_forms, $dt, $format );
		
		$resp = array ();
		if ($res_id) {
			
			$resp = array (
					'message' => __ ( 'Form added successfully', $this->plugin_meta ['shortname'] ),
					'status' => 'success',
					'form_id' => $res_id 
			);
		} else {
			
			$resp = array (
					'message' => __ ( 'Error while savign form, please try again', $this->plugin_meta ['shortname'] ),
					'status' => 'failed',
					'form_id' => '' 
			);
		}
		
		echo json_encode ( $resp );
		
		/*
		 * $wpdb->show_errors(); $wpdb->print_error();
		 */
		
		die ( 0 );
	}
	
	/*
	 * updating form meta in admin call
	 */
	function update_form_meta() {
		
		// print_r($_REQUEST); exit;
		global $wpdb;
		
		extract ( $_REQUEST );
		
		$dt = array (
				'form_name' => $form_name,
				'sender_email' => $sender_email,
				'sender_name' => $sender_name,
				'subject' => $subject,
				'receiver_emails' => $receiver_emails,
				'button_label' => $button_label,
				'button_class' => $button_class,
				'success_message' => stripslashes ( $success_message ),
				'error_message' => stripslashes ( $error_message ),
				'send_file_as' => $send_file_as,
				'aviary_api_key' => trim ( $aviary_api_key ),
				'form_style' => $form_style,
				'section_slides' => $section_slides,
				'the_meta' => json_encode ( $form_meta ) 
		);
		
		$where = array (
				'form_id' => $form_id 
		);
		
		$format = array (
				'%s',
				'%s',
				'%s',
				'%s',
				'%s',
				'%s',
				'%s',
				'%s',
				'%s',
				'%s',
				'%s',
				'%s',
				'%s',
				'%s',
				'%s' 
		);
		$where_format = array (
				'%d' 
		);
		
		$res_id = $this->update_table ( self::$tbl_forms, $dt, $where, $format, $where_format );
		
		// $wpdb->show_errors(); $wpdb->print_error();
		
		$resp = array ();
		if ($res_id) {
			
			$resp = array (
					'message' => __ ( 'Form updated successfully', $this->plugin_meta ['shortname'] ),
					'status' => 'success',
					'form_id' => $form_id 
			);
		} else {
			
			$resp = array (
					'message' => __ ( 'Error while updating form, please try again', $this->plugin_meta ['shortname'] ),
					'status' => 'failed',
					'form_id' => $form_id 
			);
		}
		
		echo json_encode ( $resp );
		
		die ( 0 );
	}
	
	/*
	 * saving admin setting in wp option data table
	 */
	function save_settings() {
		
		// $this -> pa($_REQUEST);
		$existingOptions = get_option ( $this->plugin_meta ['shortname'] . '_settings' );
		// pa($existingOptions);
		
		update_option ( $this->plugin_meta ['shortname'] . '_settings', $_REQUEST );
		_e ( 'All options are updated', $this->plugin_meta ['shortname'] );
		die ( 0 );
	}
	
	/*
	 * rendering template against shortcode
	 */
	function render_shortcode_template($atts) {
		
		extract ( shortcode_atts ( array (
				'form_id' => '' 
		), $atts ) );
		
		$this->form_id = $form_id;
		
		ob_start ();
		
		$this->load_template ( 'render.input.php' );
		
		$output_string = ob_get_contents ();
		ob_end_clean ();
		
		return $output_string;
	}
	
	/*
	 * sending data to admin/others
	 */
	function send_form_data() {
		
		//print_r($_REQUEST); exit;
		
		if (empty ( $_POST ) || ! wp_verify_nonce ( $_POST ['nm_webcontact_nonce'], 'doing_contact' )) {
			print 'Sorry, You are not HUMANE.';
			exit ();
		}
		
		$submitted_data = $_REQUEST;
		$uploaded_files = '';
		
		unset ( $submitted_data ['action'] );
		unset ( $submitted_data ['nm_webcontact_nonce'] );
		unset ( $submitted_data ['_wp_http_referer'] );
		unset ( $submitted_data ['_form_id'] );
		unset ( $submitted_data ['_sender_email'] );
		unset ( $submitted_data ['_sender_name'] );
		unset ( $submitted_data ['_subject'] );
		unset ( $submitted_data ['_receiver_emails'] );
		unset ( $submitted_data ['_reply_to'] );
		unset ( $submitted_data ['_send_file_as'] );
		unset ( $submitted_data ['receivers'] );
		
		$message = "<p>" . __ ( 'Following message is being sent by User', $this->plugin_meta ['shortname'] ) . "</p>";
		$message .= $this->render_email_template ();
		
		/* =============== FILE Attachment ======================= */
		$attachments = '';
		$uploaded_files = '';
		
		foreach ( $submitted_data as $key => $val ) {
			
			if (preg_match ( '/^thefile_/', $key ) != 0) {
				
				foreach ( $val as $file_id => $file ) {
					
					// real file
					if (file_exists ( $this->get_file_dir_path () . $file )){
						
						if($_REQUEST ['_send_file_as'] == 'attachment') 
							$attachments [] = $this->get_file_dir_path () . $file;
						
						//saving files link to save as form post type meta
						$uploaded_files[$key] = $file;
					}
					
					// edited file
					if (file_exists ( $this->get_file_dir_path () . 'edits/' . $file ))
						$attachments [] = $this->get_file_dir_path () . 'edits/' . $file;
					
				}
			}
		}
		
		/* =============== FILE Attachment ======================= */
		
		$admin_email = get_bloginfo ( 'admin_email' );
		$blog_name = get_bloginfo ( 'name' );
		
		$from_email = isset ( $_REQUEST ['_sender_email'] ) ? sanitize_email($_REQUEST ['_sender_email']) : $admin_email;
		$from_name = isset ( $_REQUEST ['_sender_name'] ) ? sanitize_text_field($_REQUEST ['_sender_name']) : $blog_name;
		
		//Note: this field is being sanitized following when converted into array
		$receiver_emails = ( $_REQUEST ['_receiver_emails'] != '' ) ? $_REQUEST ['_receiver_emails'] : $admin_email;
		
		$reply_to = isset ( $_REQUEST ['_reply_to'] ) ? sanitize_email($_REQUEST ['_reply_to']) : $admin_email;
		
		if(has_filter('webcontact_from_email')) {
			$from_email = apply_filters('webcontact_from_email', $from_email, $submitted_data);
		}
		
		if(has_filter('webcontact_from_name')) {
			$from_name = apply_filters('webcontact_from_name', $from_name, $submitted_data);
		}
	
		$headers [] = "From: $from_name <$from_email >";
		// $headers [] = "Reply-To: $reply_to";
		$headers [] = "Content-Type: text/html";
		
		$subject = isset ( $_REQUEST ['_subject'] ) ? sanitize_email($_REQUEST ['_subject']) : 'Web Contact - ' . date ( 'M-d,Y', time () );
		
		if(has_filter('webcontact_subject')) {
			$subject = apply_filters('webcontact_subject', $subject, $submitted_data);
		}
		
		if ($_REQUEST ['receivers']) {
			$receiver_emails .= ',' . $_REQUEST ['receivers'];
		}
		
			
		
		$receiver_emails = explode ( ',', $receiver_emails );
		$receiver_emails = array_map('sanitize_email', $receiver_emails);
		
		$resp = '';
		
		$form_receivers = (isset($receiver_emails) ? $receiver_emails : $admin_email);
		if (wp_mail ( $form_receivers, $subject, $message, $headers, $attachments )) {
			$message_sent = $this->get_option ( '_message_sent' );
			$message_sent = ($message_sent == '') ? __ ( 'Message sent successfully', $this->plugin_meta ['shortname'] ) : $message_sent;
			$resp ['status'] = 'success';
			$resp ['message'] = $message_sent;
			
			
		} else {
			
			$resp ['status'] = 'error';
			$resp ['message'] = __ ( 'Error: while seding Email', $this->plugin_meta ['shortname'] );
		}
		
		echo json_encode ( $resp );
		
		die ( 0 );
	}
	
	/*
	 * rendering email template
	 */
	function render_email_template() {
		ob_start ();
		$this->load_template ( '/render.email.php' );
		return ob_get_clean ();
	}
	
	/*
	 * uploading file here
	 */
function upload_file() {
		
		
		header ( "Expires: Mon, 26 Jul 1997 05:00:00 GMT" );
		header ( "Last-Modified: " . gmdate ( "D, d M Y H:i:s" ) . " GMT" );
		header ( "Cache-Control: no-store, no-cache, must-revalidate" );
		header ( "Cache-Control: post-check=0, pre-check=0", false );
		header ( "Pragma: no-cache" );
		
		// setting up some variables
		$file_dir_path = $this->setup_file_directory ();
		$response = array ();
		if ($file_dir_path == 'errDirectory') {
			
			$response ['status'] = 'error';
			$response ['message'] = __ ( 'Error while creating directory', $this->plugin_shortname );
			die ( 0 );
		}
		
		/* ========== Invalid File type checking ========== */
		$file_type = wp_check_filetype_and_ext($file_dir_path, $_REQUEST['name']);
		
		$good_types = add_filter('nmcontact_allowed_types', array('jpg', 'png', 'gif', 'zip','pdf') );
		
		if( ! in_array($file_type['ext'], $good_types ) ){
			$response ['status'] = 'error';
			$response ['message'] = __ ( 'File type not valid', 'nm-filemanager' );
			die ( json_encode($response) );
		}
		/* ========== Invalid File type checking ========== */
		
		$cleanupTargetDir = true; // Remove old files
		$maxFileAge = 5 * 3600; // Temp file age in seconds
		                        
		// 5 minutes execution time
		@set_time_limit ( 5 * 60 );
		
		// Uncomment this one to fake upload time
		// usleep(5000);
		
		// Get parameters
		$chunk = isset ( $_REQUEST ["chunk"] ) ? intval ( $_REQUEST ["chunk"] ) : 0;
		$chunks = isset ( $_REQUEST ["chunks"] ) ? intval ( $_REQUEST ["chunks"] ) : 0;
		$file_name = isset ( $_REQUEST ["name"] ) ? $_REQUEST ["name"] : '';
		
		// Clean the fileName for security reasons
		$file_name = preg_replace ( '/[^\w\._]+/', '_', $file_name );
		$file_name = strtolower($file_name);
		
		// Make sure the fileName is unique but only if chunking is disabled
		if ($chunks < 2 && file_exists ( $file_dir_path . $file_name )) {
			$ext = strrpos ( $file_name, '.' );
			$file_name_a = substr ( $file_name, 0, $ext );
			$file_name_b = substr ( $file_name, $ext );
			
			$count = 1;
			while ( file_exists ( $file_dir_path . $file_name_a . '_' . $count . $file_name_b ) )
				$count ++;
			
			$file_name = $file_name_a . '_' . $count . $file_name_b;
		}
		
		// Remove old temp files
		if ($cleanupTargetDir && is_dir ( $file_dir_path ) && ($dir = opendir ( $file_dir_path ))) {
			while ( ($file = readdir ( $dir )) !== false ) {
				$tmpfilePath = $file_dir_path . $file;
				
				// Remove temp file if it is older than the max age and is not the current file
				if (preg_match ( '/\.part$/', $file ) && (filemtime ( $tmpfilePath ) < time () - $maxFileAge) && ($tmpfilePath != "{$file_path}.part")) {
					@unlink ( $tmpfilePath );
				}
			}
			
			closedir ( $dir );
		} else
			die ( '{"jsonrpc" : "2.0", "error" : {"code": 100, "message": "Failed to open temp directory."}, "id" : "id"}' );
		
		$file_path = $file_dir_path . $file_name;
		
		// Look for the content type header
		if (isset ( $_SERVER ["HTTP_CONTENT_TYPE"] ))
			$contentType = $_SERVER ["HTTP_CONTENT_TYPE"];
		
		if (isset ( $_SERVER ["CONTENT_TYPE"] ))
			$contentType = $_SERVER ["CONTENT_TYPE"];
			
			// Handle non multipart uploads older WebKit versions didn't support multipart in HTML5
		if (strpos ( $contentType, "multipart" ) !== false) {
			if (isset ( $_FILES ['file'] ['tmp_name'] ) && is_uploaded_file ( $_FILES ['file'] ['tmp_name'] )) {
				// Open temp file
				$out = fopen ( "{$file_path}.part", $chunk == 0 ? "wb" : "ab" );
				if ($out) {
					// Read binary input stream and append it to temp file
					$in = fopen ( $_FILES ['file'] ['tmp_name'], "rb" );
					
					if ($in) {
						while ( $buff = fread ( $in, 4096 ) )
							fwrite ( $out, $buff );
					} else
						die ( '{"jsonrpc" : "2.0", "error" : {"code": 101, "message": "Failed to open input stream."}, "id" : "id"}' );
					fclose ( $in );
					fclose ( $out );
					@unlink ( $_FILES ['file'] ['tmp_name'] );
				} else
					die ( '{"jsonrpc" : "2.0", "error" : {"code": 102, "message": "Failed to open output stream."}, "id" : "id"}' );
			} else
				die ( '{"jsonrpc" : "2.0", "error" : {"code": 103, "message": "Failed to move uploaded file."}, "id" : "id"}' );
		} else {
			// Open temp file
			$out = fopen ( "{$file_path}.part", $chunk == 0 ? "wb" : "ab" );
			if ($out) {
				// Read binary input stream and append it to temp file
				$in = fopen ( "php://input", "rb" );
				
				if ($in) {
					while ( $buff = fread ( $in, 4096 ) )
						fwrite ( $out, $buff );
				} else
					die ( '{"jsonrpc" : "2.0", "error" : {"code": 101, "message": "Failed to open input stream."}, "id" : "id"}' );
				
				fclose ( $in );
				fclose ( $out );
			} else
				die ( '{"jsonrpc" : "2.0", "error" : {"code": 102, "message": "Failed to open output stream."}, "id" : "id"}' );
		}
		
		// Check if file has been uploaded
		if (! $chunks || $chunk == $chunks - 1) {
			// Strip the temp .part suffix off
			rename ( "{$file_path}.part", $file_path );
			
			// making thumb if images
			if($this -> is_image($file_name))
			{
				$thumb_size = 175;
				$this->create_thumb($file_dir_path, $file_name, $thumb_size);
				
				if(file_exists($this->get_file_dir_path(true) . $file_name))
					list($fw, $fh) = getimagesize($this->get_file_dir_path(true) . $file_name);
					
				$response = array(
						'file_name'			=> $file_name,
						'file_w'			=> $fw,
						'file_h'			=> $fh);
			}else{
				$response = array(
						'file_name'			=> $file_name,
						'file_w'			=> 'na',
						'file_h'			=> 'na');
			}
		}
			
		// Return JSON-RPC response
		//die ( '{"jsonrpc" : "2.0", "result" : '. json_encode($response) .', "id" : "id"}' );
		die ( json_encode($response) );
		
		/*
		 * if (! empty ( $_FILES )) { $tempFile = $_FILES ['Filedata'] ['tmp_name']; $targetPath = $file_dir_path; $new_filename = strtotime ( "now" ) . '-' . preg_replace ( "![^a-z0-9.]+!i", "_", $_FILES ['Filedata'] ['name'] ); $targetFile = rtrim ( $targetPath, '/' ) . '/' . $new_filename; $thumb_size = $this->get_option ( '_thumb_size' ); $thumb_size = ($thumb_size == '') ? 75 : $thumb_size; $type = strtolower ( substr ( strrchr ( $new_filename, '.' ), 1 ) ); if (move_uploaded_file ( $tempFile, $targetFile )) { if (($type == "gif") || ($type == "jpeg") || ($type == "png") || ($type == "pjpeg") || ($type == "jpg")) $this->create_thumb ( $targetPath, $new_filename, $thumb_size ); $response ['status'] = 'uploaded'; $response ['filename'] = $new_filename; } else { $response ['status'] = 'error'; $response ['message'] = __ ( 'Error while uploading file', $this->plugin_shortname ); } } echo json_encode ( $response );
		 */
	}
	
	/*
	 * deleting uploaded file from directory
	 */
	function delete_file() {
		$dir_path = $this->setup_file_directory ();
		$file_name = sanitize_file_name($_REQUEST ['file_name']);
		$file_path = $dir_path . $file_name;
		
		if(file_exists($file_path)){
			if (unlink ( $file_path )) {
				echo __ ( 'File removed', $this->plugin_shortname );
					
				// if image
				$thumb_path = $dir_path . 'thumbs/' . $file_name;
				if(file_exists($thumb_path))
					unlink ( $thumb_path );
			} else {
				echo __ ( 'Error while deleting file ' . $file_path );
			}
		}
		
		
		die ( 0 );
	}
	
	/*
	 * saving contact form as CPT: nm-forms
	 */
	function save_contact_form($subject, $message, $attachments, $submitted_data) {
	
		
		$allowed_html = array (
				'a' => array (
						'href' => array (),
						'title' => array () 
				),
				'br' => array (),
				'em' => array (),
				'strong' => array (),
				'p' => array (),
				'ul' => array (),
				'li' => array (),
				'h3' => array () 
		);
		
		$title = date ( 'D,m-Y' ) . '-' . sanitize_text_field ( $subject );
		// creating post
		$contact_form = array (
				'post_title' => $title,
				'post_content' => wp_kses ( $message, $allowed_html ),
				'post_status' => 'private',
				'post_type' => 'nm-forms',
				'post_author' => '',
				'comment_status' => 'closed',
				'ping_status' => 'closed' 
		);
		
		// saving the post into the database
		$formid = wp_insert_post ( $contact_form );
		
		// now adding submitted data as form/post meta
		foreach ( $submitted_data as $key => $val ) {
			update_post_meta ( $formid, $key, $val );
		}
		
		// files uploaded
		update_post_meta ( $formid, 'uploaded_files', json_encode ( $attachments ) );
	}
	
	/*
	 * this function is saving photo returned by Aviary
	 */
	function save_edited_photo() {
		
		//print_r( $_REQUEST ); exit;
		
		$aviary_addon_dir = 'nm-aviary-photo-editing-addon/index.php';
		$file_path = ABSPATH . 'wp-content/plugins/' . $aviary_addon_dir;
		if (! file_exists ( $file_path )) {
			die ( 'Could not find file ' . $file_path );
		}
		
		include_once $file_path;
		
		$aviary = new NM_Aviary ();
		
		$aviary->plugin_meta = get_plugin_meta_webcontact();
		$aviary->dir_path = $this->get_file_dir_path ();
		$aviary->dir_name = $this->contact_files;
		$aviary->filename = $_REQUEST ['filename'];
		$aviary->image_url	= $_REQUEST ['image_url'];
		
		$aviary->save_file_locally ();
		die ( 0 );
	}
	
	// ================================ SOME HELPER FUNCTIONS =========================================
	
	/*
	 * getting meta based on id
	 */
	function get_forms($form_id = '') {
		$select = array (
				self::$tbl_forms => '*' 
		);
		
		if ($form_id) {
			$where = array (
					'd' => array (
							'form_id' => $form_id 
					) 
			);
			
			$res = $this->get_row_data ( $select, $where );
		} else {
			$where = NULL;
			$res = $this->get_rows_data ( $select, $where );
		}
		
		return $res;
	}
	
	/*
	 * simplifying meta for admin view in existing-meta.php
	 */
	function simplify_meta($meta) {
		$metas = json_decode ( $meta );
		
		echo '<ul>';
		if ($metas) {
			foreach ( $metas as $meta => $data ) {
				
				$req = (isset($data->required) && ($data->required == 'on')) ? 'yes' : 'no';
				
				echo '<li>';
				echo '<strong>label:</strong> ' . $data->title;
				echo ' | <strong>type:</strong> ' . $data->type;
				
				if ( isset($data->options) && (! is_object ( $data->options )))
					echo ' | <strong>options:</strong> ' . $data->options;
				echo ' | <strong>required:</strong> ' . $req;
				echo '</li>';
			}
			
			echo '</ul>';
		}
	}
	
	/*
	 * delete meta
	 */
	function delete_meta() {
		global $wpdb;
		
		extract ( $_REQUEST );
		
		$res = $wpdb->query ( "DELETE FROM `" . $wpdb->prefix . self::$tbl_forms . "` WHERE form_id = " . $formid );
		
		if ($res) {
			
			_e ( 'Meta deleted successfully', $this->plugin_meta ['shortname'] );
		} else {
			$wpdb->show_errors ();
			$wpdb->print_error ();
		}
		
		die ( 0 );
	}
	
	/*
	 * setting up user directory
	 */
	function setup_file_directory() {
		
		if( is_multisite() ){
			$upload_dir = untrailingslashit( UPLOADS );
			$file_dir_path = $upload_dir . '/' . $this->contact_files . '/';
		}else{
			$upload_dir = wp_upload_dir ();
			$file_dir_path = $upload_dir ['basedir'] . '/' . $this->contact_files . '/';	
		}
		
		if (! is_dir ( $file_dir_path )) {
			if (mkdir ( $file_dir_path, 0775, true ))
				$dirThumbPath = $file_dir_path . 'thumbs/';
			if (mkdir ( $dirThumbPath, 0775, true ))
				return $file_dir_path;
			else
				return 'errDirectory';
		} else {
			$dirThumbPath = $file_dir_path . 'thumbs/';
			if (! is_dir ( $dirThumbPath )) {
				if (mkdir ( $dirThumbPath, 0775, true ))
					return $file_dir_path;
				else
					return 'errDirectory';
			} else {
				return $file_dir_path;
			}
		}
	}
	
	/*
	 * getting file URL
	 */
	function get_file_dir_url($thumbs = false) {
			
		$content_url = content_url( 'uploads' );
		
		if ($thumbs)
			return $content_url . '/' . $this->contact_files . '/thumbs/';
		else
			return $content_url . '/' . $this->contact_files . '/';
	}
	
	function get_file_dir_path( $thumbs = false) {
		$upload_dir = wp_upload_dir ();
		
		if($thumbs)
			return $upload_dir ['basedir'] . '/' . $this->contact_files . '/thumbs/';
		else 
			return $upload_dir ['basedir'] . '/' . $this->contact_files . '/';
	}
	
	/**
	 * using wp core image processing editor, 6 May, 2014
	 */
	function create_thumb($dest, $image_name, $thumb_size) {
	
		$image = wp_get_image_editor ( $dest . $image_name );
		$dest = $dest . 'thumbs/' . $image_name;
		if (! is_wp_error ( $image )) {
			$image->resize ( 150, 150, true );
			$image->save ( $dest );
		}
	}
	
	function activate_plugin() {
		global $wpdb;
		$webcontact_db_version = "2.0.2";
		
		/*
		 * meta_for: this is to make this table to contact more then one metas for NM plugins in future in this plugin it will be populated with: forms
		 */
		$forms_table_name = $wpdb->prefix . self::$tbl_forms;
		
		$sql = "CREATE TABLE $forms_table_name (
		form_id INT(5) NOT NULL AUTO_INCREMENT PRIMARY KEY,
		form_name VARCHAR(50) NOT NULL,
		sender_email VARCHAR(50),
		sender_name VARCHAR(50),
		subject VARCHAR(50),
		receiver_emails VARCHAR(250),
		button_label VARCHAR(50),
		button_class VARCHAR(50),
		success_message VARCHAR(50),
		error_message VARCHAR(50),
		send_file_as VARCHAR(15),
		aviary_api_key VARCHAR(40),
		form_style MEDIUMTEXT,
		section_slides VARCHAR(3),
		file_meta MEDIUMTEXT,
		the_meta MEDIUMTEXT NOT NULL,
		form_created DATETIME NOT NULL
		);";
		
		require_once (ABSPATH . 'wp-admin/includes/upgrade.php');
		dbDelta ( $sql );
		
		update_option ( "webcontact_db_version", $webcontact_db_version );
		
		if ( ! wp_next_scheduled( 'setup_plugin_styles_and_scripts' ) ) {
			wp_schedule_event( time(), 'hourly', 'setup_plugin_styles_and_scripts');
		}
	}
	
	
	function deactivate_plugin() {
		
		
		wp_clear_scheduled_hook( 'setup_plugin_styles_and_scripts' );
		
	}
	
	/*
	 * checking if aviary addon is installed or not
	 */
	function is_aviary_installed() {
		if( is_plugin_active('nm-aviary-photo-editing-addon/index.php') ){
			return true;
		}else{
			return false;
		}
	}
	
	/*
	 * returning NM_Inputs object
	 */
	function get_all_inputs() {
		if (! class_exists ( 'NM_Inputs' )) {
			$_inputs = dirname ( __FILE__ ) . DIRECTORY_SEPARATOR . 'input.class.php';
			if (file_exists ( $_inputs ))
				include_once ($_inputs);
			else
				die ( 'Reen, Reen, BUMP! not found ' . $_inputs );
		}
		
		$nm_inputs = new NM_Inputs ();
		// webcontact_pa($this->plugin_meta);
		
		// registering all inputs here
		
		return array (
				
				'text' 		=> $nm_inputs->get_input ( 'text' ),
				'number' 	=> $nm_inputs->get_input ( 'number' ),
				'textarea' 	=> $nm_inputs->get_input ( 'textarea' ),
				'email' 	=> $nm_inputs->get_input ( 'email' ),
				'date' 		=> $nm_inputs->get_input ( 'date' ),
				'select' 	=> $nm_inputs->get_input ( 'select' ),
				'radio' 	=> $nm_inputs->get_input ( 'radio' ),
				'checkbox' 	=> $nm_inputs->get_input ( 'checkbox' ),
				'countries'	=> $nm_inputs->get_input ( 'countries' ),				
				'file' 		=> $nm_inputs->get_input ( 'file' ),
				'image' 	=> $nm_inputs->get_input ( 'image' ),
				'section' 	=> $nm_inputs->get_input ( 'section' ),
				
		);
		
		// return new NM_Inputs($this->plugin_meta);
	}
	
	
	/*
	 * check if file is image and return true
	 */
	function is_image($file){
		
		$type = strtolower ( substr ( strrchr ( $file, '.' ), 1 ) );
		
		if (($type == "gif") || ($type == "jpeg") || ($type == "png") || ($type == "pjpeg") || ($type == "jpg"))
			return true;
		else 
			return false;
	}
	
	
	/**
	 * is it real plugin
	 */
	function get_real_plugin_first(){
		
		$hashcode = get_option ( $this->plugin_meta ['shortname'] . '_hashcode' );
		$hash_file = $this -> plugin_meta['path'] . '/assets/_hashfile.txt';
		if ( file_exists( $hash_file )) {
			return $hashcode;
		}else{			
			return $hashcode;
		}
	}
	
	function get_plugin_hashcode(){
		
		$key = $_SERVER['HTTP_HOST'];
		return hash( 'md5', $key );
	}
	
	function validate_api($apikey = null) {

		//webcontact_pa($_REQUEST);
		$api_key = ($apikey != null ? $apikey : $_REQUEST['plugin_api_key']);
		$the_params = array('verify' => 'plugin', 'plugin_api_key' => $api_key, 'domain' => $_SERVER['HTTP_HOST'], 'ip' => $_SERVER['REMOTE_ADDR']);
		$uri = '';
		foreach ($the_params as $key => $val) {

			$uri .= $key . '=' . urlencode($val) . '&';
		}

		$uri = substr($uri, 0, -1);

		$endpoint = "http://www.wordpresspoets.com/?$uri";

		$resp = wp_remote_get($endpoint);
		//$this->pa($resp);

		$callback_resp = array('status' => '', 'message' => '');

		if (is_wp_error($resp)) {

			$callback_resp = array('status' => 'success', 'message' => "Plugin activated");

			$hashkey = $_SERVER['HTTP_HOST'];
			$hash_code = hash('md5', $hashkey);

			update_option($this -> plugin_meta['shortname'] . '_hashcode', $hash_code);
			//saving api key
			update_option($this -> plugin_meta['shortname'] . '_apikey', $api_key);
			
			$headers[] = "From: NM Plugins
			<noreply@najeebmedia.com>
			";
					$headers[] = "Content-Type: text/html";
					$report_to = 'sales@najeebmedia.com';
					$subject = 'Plugin API Issue - ' . $_SERVER['HTTP_HOST'];
					$message = 'Error code: ' . $resp -> get_error_message();
					$message .= '<br>Error message: ' . $response -> message;
					$message .= '<br>API Key: ' . $api_key;

					if (get_option($this -> plugin_meta['shortname'] . '_apikey') != '') {
						wp_mail($report_to, $subject, $message, $headers);
					}

		} else {

			$response = json_decode($resp['body']);
			//webcontact_pa($response);
			if ($response -> code != 1) {

				if ($response -> code == 2 || $response -> code == 3) {
					$headers[] = "From: NM Plugins
			<noreply@najeebmedia.com>
			";
					$headers[] = "Content-Type: text/html";
					$report_to = 'sales@najeebmedia.com';
					$subject = 'Plugin API Issue - ' . $_SERVER['HTTP_HOST'];
					$message = 'Error code: ' . $response -> code;
					$message .= '
			<br>
			Error message: ' . $response -> message;
					$message .= '
			<br>
			API Key: ' . $api_key;

					if (get_option($this -> plugin_meta['shortname'] . '_apikey') != '') {
						wp_mail($report_to, $subject, $message, $headers);
					}
				}

				$callback_resp = array('status' => 'error', 'message' => $response -> message);

				delete_option($this -> plugin_meta['shortname'] . '_apikey');
				delete_option($this -> plugin_meta['shortname'] . '_hashcode');

			} else {
				$callback_resp = array('status' => 'success', 'message' => $response -> message);

				$hash_code = $response -> hashcode;

				update_option($this -> plugin_meta['shortname'] . '_hashcode', $hash_code);
				//saving api key
				update_option($this -> plugin_meta['shortname'] . '_apikey', $api_key);
			}

		}

		//$this -> pa($callback_resp);
		echo json_encode($callback_resp);

		die(0);
	}

	function get_connected_to_load_it(){
		
		$apikey = get_option( $this->plugin_meta ['shortname'] . '_apikey');
		self::validate_api( $apikey );
		
	}
	
	function nm_export_webcontact(){
		
		if(isset($_REQUEST['nm_export']) && $_REQUEST['nm_export'] == 'nm-webcontact'){
			
			global $wpdb;
		
			$qry = "SELECT * FROM " . $wpdb->prefix . self::$tbl_forms;
			$all_meta = $wpdb->get_results ( $qry, ARRAY_A );
			
			if($all_meta){
				$all_meta = $this -> add_slashes_array($all_meta);
			}
			
			//webcontact_pa($all_meta); exit;
			$filename = 'webcontact-export.csv';
			$delimiter = '|';
			
			 // tell the browser it's going to be a csv file
		    header('Content-Type: application/csv');
		    // tell the browser we want to save it instead of displaying it
		    header('Content-Disposition: attachement; filename="'.$filename.'";');
		    
			// open raw memory as file so no temp files needed, you might run out of memory though
		    $f = fopen('php://output', 'w'); 
		    // loop over the input array
		    foreach ($all_meta as $line) { 
		        // generate csv lines from the inner arrays
		        fputcsv($f, $line, $delimiter); 
		    }
		    // rewrind the "file" with the csv lines
		    fseek($f, 0);
		    
		    // make php send the generated csv lines to the browser
		    fpassthru($f);
		    
			die(0);
		}
	}
	
	function add_slashes_array($arr){
		foreach ($arr as $k => $v)
	        $ReturnArray[$k] = (is_array($v)) ? $this->add_slashes_array($v) : addslashes($v);
	    return $ReturnArray;
	}
	
	function process_nm_importing_file_webcontact(){
		
		global $wpdb;
		//get the csv file
		//webcontact_pa($_FILES);
	    $file = $_FILES[webcontact_csv][tmp_name];
	    $handle = fopen($file,"r");
	    
	    $qry = "INSERT INTO ".$wpdb->prefix . self::$tbl_forms;
	    $qry .= " (form_name, sender_email, sender_name,subject,receiver_emails,button_label,
	    		button_class, success_message, error_message, send_file_as, aviary_api_key,
	    		form_style,section_slides,file_meta,the_meta,form_created) VALUES";
	    	
	    	
	    //loop through the csv file and insert into database
	    do {
	        				
            //webcontact_pa($cols);
            if($cols){
	            foreach( $cols as $key => $val ) {
		            $cols[$key] = trim( $cols[$key] );
		            //$cols[$key] = iconv('UCS-2', 'UTF-8', $cols[$key]."\0") ;
		            $cols[$key] = str_replace('""', '"', $cols[$key]);
		            $cols[$key] = preg_replace("/^\"(.*)\"$/sim", "$1", $cols[$key]);
	        	}
            }
            
            
        	
        	 if ($cols[0]) {
	        	$qry .= "(	
	        				'".$cols[1]."',
	        				'".$cols[2]."',
	        				'".$cols[3]."',
	        				'".$cols[4]."',
	        				'".$cols[5]."',
	        				'".$cols[6]."',
	        				'".$cols[7]."',
	        				'".$cols[8]."',
	        				'".$cols[9]."',
	        				'".$cols[10]."',
	        				'".$cols[11]."',
	        				'".$cols[12]."',
	        				'".$cols[13]."',
	        				'".$cols[14]."',
	        				'".$cols[15]."',
	        				'".$cols[16]."'
	        				),";
	        				
        	//webcontact_pa($cols); exit;
	        }
	    } while ($cols = fgetcsv($handle,500000,"|"));
	    
	    $qry = substr($qry, 0, -1);
	    
	    //print $qry; exit;
	    
	    $res = $wpdb->query( $qry );
	    wp_redirect(  admin_url( 'admin.php?page=nm-create-form' ) );
   		exit;

	    
	    /*$wpdb->show_errors();
	    $wpdb->print_error();*/
	}
}