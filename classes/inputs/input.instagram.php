<?php
/*
 * Bissmillah
 * This file will call api's to Instagram
 * to import photos of visitor
 */

class NM_Instagram extends NM_Inputs{
	
	
	/*
	 * input control settings
	*/
	var $title, $desc, $settings;
	
	/*
	 * this var is pouplated with current plugin meta
	*/
	var $plugin_meta;
	
	
	
	// for authorization
	var $authorize_url;
	
	// for token access
	var $access_token_url;
	
	// client_id provided by Instagram 
	var $client_id;
	
	// client_secret provided by Instagram
	var $client_secret;
	
	// this uri must be defined in instagram app
	var $redirect_uri;
	
	// response_type: code|
	var $response_type;
	
	// grant_type has only value: authorization_code
	var $grant_type;
	
	var $access_token, $user_id;
	
	function __construct(){
		
		$this -> plugin_meta = get_plugin_meta_productmeta();
		
		$this -> title 		= __ ( 'Instagram import', $this -> plugin_meta ['shortname'] );
		$this -> desc		= __ ( 'Import photo from Instagram', $this -> plugin_meta ['shortname'] );
		$this -> settings	= self::get_settings();
		
		$this -> access_token_url = 'https://api.instagram.com/oauth/access_token';
		$this -> grant_type		= 'authorization_code';
		$this -> response_type	= 'code';
		
	}
	
	private function get_settings(){
	
		return array (
				'title' => array (
						'type' => 'text',
						'title' => __ ( 'Title', $nmpersonalizedproduct->plugin_meta ['shortname'] ),
						'desc' => __ ( 'It will be shown as field label', $nmpersonalizedproduct->plugin_meta ['shortname'] )
				),
				'data_name' => array (
						'type' => 'text',
						'title' => __ ( 'Data name', $nmpersonalizedproduct->plugin_meta ['shortname'] ),
						'desc' => __ ( 'REQUIRED: The identification name of this field, that you can insert into body email configuration. Note:Use only lowercase characters and underscores.', $nmpersonalizedproduct->plugin_meta ['shortname'] )
				),
				'description' => array (
						'type' => 'text',
						'title' => __ ( 'Description', $nmpersonalizedproduct->plugin_meta ['shortname'] ),
						'desc' => __ ( 'Small description, it will be diplay near name title.', $nmpersonalizedproduct->plugin_meta ['shortname'] )
				),
				'error_message' => array (
						'type' => 'text',
						'title' => __ ( 'Error message', $nmpersonalizedproduct->plugin_meta ['shortname'] ),
						'desc' => __ ( 'Insert the error message for validation.', $nmpersonalizedproduct->plugin_meta ['shortname'] )
				),
	
				'required' => array (
						'type' => 'checkbox',
						'title' => __ ( 'Required', $nmpersonalizedproduct->plugin_meta ['shortname'] ),
						'desc' => __ ( 'Select this if it must be required.', $nmpersonalizedproduct->plugin_meta ['shortname'] )
				),
				
				'class' => array (
						'type' => 'text',
						'title' => __ ( 'Class', $nmpersonalizedproduct->plugin_meta ['shortname'] ),
						'desc' => __ ( 'Insert an additional class(es) (separateb by comma) for more personalization.', $nmpersonalizedproduct->plugin_meta ['shortname'] )
				),
				'width' => array (
					'type' => 'select',
					'default' => '6',
					'options'		=> array('2'=>'2 Columns', '3'=>'3 Columns', '4'=>'4 Columns', '5'=>'5 Columns', '6'=>'6 Columns', '7'=>'7 Columns', '8'=>'8 Columns', '9'=>'9 Columns', '10'=>'10 Columns','11'=>'11 Columns','12'=>'12 Columns'),
					'title' => __ ( 'Width', 'nm-webcontact' ),
					'desc' => __ ( 'Select column for this field from 12 columns Grid', 'nm-webcontact' ) 
				),
				
				'button_label_import' => array (
						'type' => 'text',
						'title' => __ ( 'Button label', 'nm-webcontact' ),
						'desc' => __ ( 'Type button label e.g: Import Photos', 'nm-webcontact' ) 
				),
				'button_class' => array (
						'type' => 'text',
						'title' => __ ( 'Button class', 'nm-webcontact' ),
						'desc' => __ ( 'Type class for import button', 'nm-webcontact' )
				),
	
				'client_id' => array (
						'type' => 'text',
						'title' => __ ( 'Client ID', $nmpersonalizedproduct->plugin_meta ['shortname'] ),
						'desc' => __ ( 'Get client id from your Instagram app.', $nmpersonalizedproduct->plugin_meta ['shortname'] )
				),
				'client_secret' => array (
						'type' => 'text',
						'title' => __ ( 'Client secret', $nmpersonalizedproduct->plugin_meta ['shortname'] ),
						'desc' => __ ( 'Get client secret from your Instagram app.', $nmpersonalizedproduct->plugin_meta ['shortname'] )
				),
				'redirect_uri' => array (
						'type' => 'text',
						'title' => __ ( 'Redirect URI', $nmpersonalizedproduct->plugin_meta ['shortname'] ),
						'desc' => __ ( 'Paste redirect uri you used in Instagram app page, it MUST be matched.', $nmpersonalizedproduct->plugin_meta ['shortname'] )
				),
				
				
				'logic' => array (
						'type' => 'checkbox',
						'title' => __ ( 'Enable conditional logic', $nmpersonalizedproduct->plugin_meta ['shortname'] ),
						'desc' => __ ( 'Tick it to turn conditional logic to work below', $nmpersonalizedproduct->plugin_meta ['shortname'] )
				),
				'conditions' => array (
						'type' => 'html-conditions',
						'title' => __ ( 'Conditions', $nmpersonalizedproduct->plugin_meta ['shortname'] ),
						'desc' => __ ( 'Tick it to turn conditional logic to work below', $nmpersonalizedproduct->plugin_meta ['shortname'] )
				),
		);
	}
	
	
	/*
	 * @params: $opt['option']ions
	*/
	function render_input($args, $content=""){
		
		$this->client_id = $args['client-id'];
		$this->client_secret	= $args['client-secret'];
		
		// $product_slug = ($_GET['product'] != '') ? $_GET['product'] : '';
		$this -> redirect_uri	= $this->get_redirect_uri();
		$this -> authorize_url	= $this->get_authorization_url();
		
		// getting access token
		$this->get_access_token();
		
		$_html = '<div class="instagram_box">';
		
		// nm_personalizedproduct_pa($_SESSION);
		
		if ($this->access_token != '') {
			$_html .= $this -> fetch_photos($args);
		}else{

			$_html .= '<div class="container_buttons">';
			$_html .= '<div class="btn_center" style="float:none;margin:0 auto">';
			$_html .= '<a id="importfile-'.$args['id'].'" href="'.$this->authorize_url.'" class="select_button '.$args['button-class'].'">' . $args['button-label-import'] . '</a>';
			$_html .= '</div>';
				
			$_html .= '</div>';		//container_button
			
			//$_html .= '<div id="drophere-'.$args['id'].'" style="border: 1px solid #ccc; height: 25px;">Drop here</div>';
			 
			$_html .= '<div id="filelist-'.$args['id'].'" class="filelist"></div>';
			
		}
		
		$_html .= '</div>';		// instagram_box
		
    	echo $_html;    	
    	// $this -> get_input_js($args);
	}
	
	
	/*
	 * loading photos
	 */
	private function fetch_photos($args){
		
		$user_id		= $this->user_id;
		$access_token	= $this->access_token;
		
		$result = $this -> fetch_insta_data("https://api.instagram.com/v1/users/{$user_id}/media/recent/?access_token={$access_token}");
		$result = json_decode($result);
		
		// nm_personalizedproduct_pa($result);
		
		$_html = '';
		
		$img_index = 0;
		$popup_width	= $args['popup-width'] == '' ? 600 : $args['popup-width'];
		$popup_height	= $args['popup-height'] == '' ? 450 : $args['popup-height'];
		
		foreach ($result -> data as $post){
				
			$_html .= '<div class="instagram_photo">';
			
			$_html .= '<img width="75" src="'.$post->images->thumbnail->url.'" />';
				
			// for bigger view
			$_html	.= '<div style="display:none" id="instagram_photo_' . $img_index . '"><img src="' . $post->images->standard_resolution->url . '" /></div>';
				
			$_html	.= '<div class="input_image">';
			if ($args['multiple-allowed'] == 'on') {
				$_html	.= '<input type="checkbox" name="'.$args['name'].'[]" value="'.$post->images->standard_resolution->url.'" />';
			}else{
				$_html	.= '<input type="radio" name="'.$args['name'].'" value="'.$post->images->standard_resolution->url.'" />';
			}
				
				
			$_html	.= '<a href="#TB_inline?width='.$popup_width.'&height='.$popup_height.'&inlineId=instagram_photo_' . $img_index . '" class="thickbox" title="' . $args['name'] . '"><img width="15" src="' . $this -> plugin_meta['url'] . '/images/zoom.png" /></a>';
			$_html	.= '<div class="p_u_i_name">'.stripslashes( $post->caption->text ).'</div>';
			$_html	.= '</div>';	//input_image
			
			$_html	.= '</div>';	//instagram_photo
				
			$img_index++;
		}
		
		$_html .= '<div style="clear:both"></div>';		//container_buttons
		
		/* foreach ($result -> data as $post):
				// Renders images. @Options (thumbnail,low_resoulution, high_resolution)
				$html .= '<a class="group" rel="group1" href="'.$post->images->standard_resolution->url.'"><img src="'.$post->images->thumbnail->url.'"></a>';
		endforeach; */

		return $_html;
	}
	
	
	// Gets our data
	private function fetch_insta_data($url){
	
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_TIMEOUT, 20);
		$result = curl_exec($ch);
		curl_close($ch);
	
		return $result;
	}
	
	/*
	 * making import button/link
	 */
	function build_import_link($control_id = 'instaupload'){
		
		$html 	= '<div class="btn_center">';
			$html 	.= '<a id="insta-import-'.$control_id.'" href="javascript:;" class="select_button " style="position: relative; z-index: 1;">';
			$html	.= __('Import photos from Instagram', 'nm-personalizedproduct');
			$html 	.= '</a>';
		$html 	.= '</div>';			
	}
	
	
	function get_authorization_url(){
		
		$auth_endpoint	= 'https://api.instagram.com/oauth/authorize/';
		return $auth_endpoint . "?client_id=$this->client_id&redirect_uri=$this->redirect_uri&response_type=".$this->response_type;
	}
	
	function get_redirect_uri(){
		
		$vars = array('import' => 'instagram', 'code'	=> '');
		
		$uri = str_replace( '%7E', '~', $this->current_page_url());
		$parts = explode("?", $uri);
		
		$qsArr = array();
		if(isset($parts[1])){	////// query string present explode it
			$qsStr = explode("&", $parts[1]);
			foreach($qsStr as $qv){
				$p = explode("=",$qv);
				$qsArr[$p[0]] = $p[1];
			}
		}
		
		//////// updatig query string
		foreach($vars as $key=>$val){
			if($val==NULL) unset($qsArr[$key]); else $qsArr[$key]=$val;
		}
		
		////// rejoin query string
		$qsStr="";
		foreach($qsArr as $key=>$val){
			$qsStr.=$key."=".$val."&";
		}
		if($qsStr!="") $qsStr=substr($qsStr,0,strlen($qsStr)-1);
		$uri = $parts[0];
		if($qsStr!="") $uri.="?".$qsStr;
		
		return $uri;
	}
	
	// processing instgram response and get api token
	function get_access_token(){
		
		if($_REQUEST['code'] != '' && $_REQUEST['import'] == 'instagram'){
			$api_response = $this -> fetch_access_token($_REQUEST['code']);
			// nm_personalizedproduct_pa($api_response);
			
			$this->access_token	= $api_response -> access_token;
			$this->user_id		= $api_response ->user -> id;
		
		}
	}
	
	function fetch_access_token($code){
	
	
		$post_data		= array('client_id' => $this->client_id,
								'client_secret' 	=> $this->client_secret,
								'grant_type'		=> $this->grant_type,
								'code'				=> $code,
								'redirect_uri'		=> $this->redirect_uri
		);
		
		// url-ify the data for the POST
		foreach($post_data as $key=>$value) { $fields_string .= $key.'='.$value.'&'; }
		rtrim($fields_string, '&');
	
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $this -> access_token_url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_TIMEOUT, 20);
		curl_setopt($ch, CURLOPT_POST, count($fields));
		curl_setopt($ch, CURLOPT_POSTFIELDS, $fields_string);
		$result = curl_exec($ch);
		curl_close($ch);
	
		return json_decode($result);
	}
	
}