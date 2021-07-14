<?php
/**
 * Plugin Name: Facebook Lead Form Connector
 * Plugin URI:  https://github.com/elberos/wp-facebook-lead-form
 * Description: Facebook Lead Form Connector
 * Version:     1.0
 * Author:      Ildar Bikmamatov <support@elberos.org>
 * Author URI:  https://elberos.org/
 * License:     Apache License 2.0
 *
 *  Licensed under the Apache License, Version 2.0 (the "License");
 *  you may not use this file except in compliance with the License.
 *  You may obtain a copy of the License at
 *
 *      https://www.apache.org/licenses/LICENSE-2.0
 *
 *  Unless required by applicable law or agreed to in writing, software
 *  distributed under the License is distributed on an "AS IS" BASIS,
 *  WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 *  See the License for the specific language governing permissions and
 *  limitations under the License.
 */
 
 
if ( !class_exists( 'Elberos_Facebook_Lead_Form_Plugin' ) ) 
{

require_once "include/Helper.php";

class Elberos_Facebook_Lead_Form_Plugin
{
	
	/**
	 * Init Plugin
	 */
	public static function init()
	{
		add_action(
			'admin_init', 
			function()
			{
				require_once "admin/Settings.php";
			}
		);
		add_action('admin_menu', 'Elberos_Facebook_Lead_Form_Plugin::register_admin_menu');
		
		
		// Add Cron
		/*
		if ( !wp_next_scheduled( 'elberos_wp_metrika_load_data' ) )
		{
			wp_schedule_event( time() + 60, 'hourly', 'elberos_wp_metrika_load_data' );
		}
		
		add_action( 'elberos_wp_metrika_load_data', 'Elberos\WP_Metrika\Cron::load_data' );
		*/
	}
	
	
	
	/**
	 * Register Admin Menu
	 */
	public static function register_admin_menu()
	{
		add_menu_page(
			'Facebook', 'Facebook',
			'manage_options', 'elberos-facebook',
			function ()
			{
				echo "Facebook";
			},
			null
		);		
		
		add_submenu_page(
			'elberos-facebook', 
			'Facebook Settings', 'Facebook Settings',
			'manage_options', 'elberos-facebook-settings', 
			function()
			{
				\Elberos\Facebook\Settings::show();
			}
		);
		
	}
	
}

Elberos_Facebook_Lead_Form_Plugin::init();

function wp_facebook_lead_form_api_load()
{
	if (!class_exists(\Facebook\Facebook::class))
	{
		require_once __DIR__ . "/vendor/autoload.php";
	}
}

}
