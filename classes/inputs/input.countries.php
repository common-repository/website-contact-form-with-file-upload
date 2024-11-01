<?php
/*
 * Followig class handling text input control and their
* dependencies. Do not make changes in code
* Create on: 9 November, 2013
*/

class NM_Countries extends NM_Inputs{
	
	/*
	 * input control settings
	 */
	var $title, $desc, $settings, $countries;
	
	
	/*
	 * this var is pouplated with current plugin meta
	*/
	var $plugin_meta;
	
	function __construct(){
		
		$this -> plugin_meta = get_plugin_meta_webcontact();
		
		$this -> countries = "Afghanistan;Albania;Algeria;American Samoa;Andorra;Angola;Anguilla;Antarctica;Antigua;Argentina;Armenia;Aruba;Australia;Austria;Azerbaijan;Bahamas;Bahrain;Bangladesh;Barbados;Belarus;Belgium;Belize;Benin;Bermuda;Bhutan;Bolivia;Bosnia and Herzegowina;Botswana;Brazil;Brunei;Bulgaria;Burkina Faso;Burundi;Cambodia;Cameroon;Canada;Cape Verde;Cayman Islands;Central African Republic;Chad;Chile;China;Christmas Island;Cocos Islands;Colombia;Comoros;Congo;Cook Islands;Costa Rica;Cote D'Ivoire;Croatia;Cuba;Cyprus;Czech Republic;Denmark;Djibouti;Dominica;Dominican Republic;East Timor;Ecuador;Egypt;El Salvador;Equatorial Guinea;Eritrea;Estonia;Ethiopia;Falkland Islands;Faroe Islands;Fiji;Finland;France;French Guiana;French Polynesia;Gabon;Gambia;Georgia;Germany;Ghana;Gibraltar;Greece;Greenland;Grenada;Guadeloupe;Guam;Guatemala;Guinea;Guinea-Bissau;Guyana;Haiti;Holy See (Vatican City);Honduras;Hong Kong;Hungary;Iceland;India;Indonesia;Iran;Iraq;Ireland;Israel;Italy;Jamaica;Japan;Jordan;Kazakhstan;Kenya;Kiribati;Korea; DPR of Korea;Kuwait;Kyrgyzstan;Laos;Latvia;Lebanon;Lesotho;Liberia;Libya;Liechtenstein;Lithuania;Luxembourg;Macau;Macedonia;Madagascar;Malawi;Malaysia;Maldives;Mali;Malta;Marshall Islands;Martinique;Mauritania;Mauritius;Mexico;Micronesia;Moldova; Republic Of Monaco;Mongolia;Montserrat;Morocco;Mozambique;Myanmar;Namibia;Nauru;Nepal;Netherlands;Netherlands Antilles;New Caledonia;New Zealand;Nicaragua;Niger;Nigeria;Niue;Northern Mariana Islands;Norway;Not in list;Oman;Pakistan;Palau;Panama;Papua New Guinea;Paraguay;Peru;Philippines;Pitcairn;Poland;Portugal;Puerto Rico;Qatar;Reunion;Romania;Russia;Rwanda;Saint Kitts And Nevis;Saint Lucia;Saint Vincent and Grenadines;Samoa;San Marino;Saudi Arabia;Senegal;Seychelles;Sierra Leone;Singapore;Slovakia;Slovenia;Solomon Islands;Somalia;South Africa;Spain;Sri Lanka;St. Helena;Sudan;Suriname;Swaziland;Sweden;Switzerland;Syria;Taiwan;Tajikistan;Tanzania;Thailand;Togo;Tonga;Trinidad And Tobago;Tunisia;Turkey;Turkmenistan;Turks And Caicos Islands;Tuvalu;Uganda;Ukraine;United Arab Emirates;United Kingdom;United States;Uruguay;Uzbekistan;Vanuatu;Venezuela;Viet Nam;Virgin Islands (British);Virgin Islands (U.S.);Western Sahara;Yemen;Yugoslavia;Zaire;Zambia;Zimbabwe";
		$this -> countries = explode(";", $this->countries);
		
		$this -> title 		= __ ( 'Countries list', 'nm-webcontact' );
		$this -> desc		= __ ( 'List of all countries', 'nm-webcontact' );
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
				'desc' => __ ( 'Type each option per line', 'nm-webcontact' ),
				'options'	=> $this -> countries,
				
		),
		'selected' => array (
				'type' => 'text',
				'title' => __ ( 'Selected option', 'nm-webcontact' ),
				'desc' => __ ( 'Type option name (given above) if you want already selected.', 'nm-webcontact' )
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
	 * @params: args
	*/
	function render_input($args, $options="", $default=""){
		
		$_html = '<select ';
		
		foreach ($args as $attr => $value){
			
			$_html .= $attr.'="'.stripslashes( $value ).'"';
		}
		
		$_html .= '>';
		
		$_html .= '<option value="">'.__('Select option', $this -> plugin_meta['shortname']).'</option>';
		
		foreach($options as $opt)
		{
				
			$selected = ($opt == $default) ? 'selected="selected"' : '';
			$output = stripslashes(trim($opt));
				
			$_html .= '<option value="'.$opt.'" '. $selected.'>';
			$_html .= $output;
			$_html .= '</option>';
		}
		
		$_html .= '</select>';
		
		echo $_html;
	}
}