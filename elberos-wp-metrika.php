<?php
/**
 * Plugin Name: Elberos WP Metrika
 * Plugin URI:  https://github.com/elberos/wp-metrika
 * Description: Elberos WP Metrika
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
 
 
if ( !class_exists( 'Elberos_WP_Metrika_Plugin' ) ) 
{

require_once "include/Helper.php";
require_once "include/Cron.php";


class Elberos_WP_Metrika_Plugin
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
				require_once "pages/Info.php";
				require_once "pages/Settings.php";
			}
		);
		add_action('admin_menu', 'Elberos_WP_Metrika_Plugin::register_admin_menu');
		add_filter('cron_schedules', 'Elberos_WP_Metrika_Plugin::cron_schedules');
		
		
		// Add Cron
		if ( !wp_next_scheduled( 'elberos_wp_metrika_load_data' ) )
		{
			wp_schedule_event( time() + 60, 'elberos_wp_metrika_hour', 'elberos_wp_metrika_load_data' );
		}
		
		add_action( 'elberos_wp_metrika_load_data', 'Elberos\WP_Metrika\Cron::load_data' );
	}
	
	
	
	/**
	 * Cron schedules
	 */
	public static function cron_schedules()
	{
		$schedules['elberos_wp_metrika_hour'] = array(
			'interval' => 3600, // Каждый час
			'display'  => __( 'Once Hour', 'elberos_wp_metrika' ),
		);
		return $schedules;
	}
	
	
	
	/**
	 * Register Admin Menu
	 */
	public static function register_admin_menu()
	{
		add_menu_page(
			'Yandex Metrika', 'Yandex Metrika',
			'manage_options', 'elberos-wp-metrika',
			function ()
			{
				\Elberos\WP_Metrika\Info::show();
			},
			null
		);		
		
		add_submenu_page(
			'elberos-wp-metrika', 
			'Settings', 'Settings',
			'manage_options', 'elberos-wp-metrika-settings', 
			function()
			{
				\Elberos\WP_Metrika\Settings::show();
			}
		);
		
	}
	
}

Elberos_WP_Metrika_Plugin::init();

}
