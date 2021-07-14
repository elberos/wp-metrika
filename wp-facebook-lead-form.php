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
				require_once "admin/Forms_Table.php";
				require_once "admin/Leads_Table.php";
				require_once "admin/Settings.php";
			}
		);
		add_action('admin_menu', 'Elberos_Facebook_Lead_Form_Plugin::register_admin_menu');
		
		/* Load entities */
		add_action(
			'plugins_loaded',
			function()
			{
				include __DIR__ . "/include/Forms.php";
				include __DIR__ . "/include/Helper.php";
				include __DIR__ . "/include/Leads.php";
			},
		);
		
		// Add Cron
		if ( !wp_next_scheduled( 'elberos_facebook_load_leads' ) )
		{
			wp_schedule_event( time() + 60, 'elberos_five_minute', 'elberos_facebook_load_leads' );
		}
		
		add_action( 'elberos_facebook_load_leads', 'Elberos_Facebook_Lead_Form_Plugin::load_leads' );
	}
	
	
	
	/**
	 * Register Admin Menu
	 */
	public static function register_admin_menu()
	{
		add_menu_page(
			'Facebook Leads', 'Facebook Leads',
			'manage_options', 'elberos-facebook-leads',
			function ()
			{
				$table = new \Elberos\Facebook\Leads\Leads_Table();
				$table->display();
			},
			null
		);		
		
		add_submenu_page(
			'elberos-facebook-leads', 
			'Forms', 'Forms',
			'manage_options', 'elberos-facebook-leads-forms', 
			function()
			{
				$table = new \Elberos\Facebook\Leads\Forms_Table();
				$table->display();
			}
		);
		
		add_submenu_page(
			'elberos-facebook-leads', 
			'Settings', 'Settings',
			'manage_options', 'elberos-facebook-leads-settings', 
			function()
			{
				\Elberos\Facebook\Leads\Settings::show();
			}
		);
		
	}
	
	
	
	/**
	 * Load leads
	 */
	public static function load_leads()
	{
		global $wpdb;
		
		/* Retrieve leads */
		$enabled = \Elberos\Facebook\Leads\Helper::get_key("elberos_facebook_leads_enable", "");
		if ($enabled == "yes")
		{
			$fb = wp_facebook_lead_create_instance();
			$access_token = \Elberos\Facebook\Leads\Helper::get_key("elberos_facebook_leads_access_token", "");
			
			$sql = "select * from " . $wpdb->base_prefix . "elberos_facebook_leads_forms ".
				"where is_deleted=0 and status='ACTIVE';";
			$forms = $wpdb->get_results($sql, ARRAY_A);
			foreach ($forms as $form)
			{
				$form_id = $form["id"];
				$facebook_form_id = $form["facebook_id"];
				if ($facebook_form_id == 0) continue;
				
				try
				{
					$response = $fb->get('/' . $facebook_form_id . '/leads', $access_token);
					$data = $response->getGraphEdge()->asArray();
					
					if (gettype($data) == 'array') foreach ($data as $row)
					{
						$created_time = $row["created_time"];
						$created_time = $created_time->getTimestamp();
						
						$insert =
						[
							"facebook_id" => $row["id"],
							"facebook_form_id" => $facebook_form_id,
							"form_id" => $form_id,
							"data" => @json_encode($row["field_data"]),
							"gmtime_lead_create" => gmdate("Y-m-d H:i:s", $created_time),
						];
						
						\Elberos\wpdb_insert_or_update
						(
							$wpdb->base_prefix . 'elberos_facebook_leads',
							[
								"facebook_id" => $row["id"],
							],
							[
								"facebook_id" => $row["id"],
								"facebook_form_id" => $facebook_form_id,
								"form_id" => $form_id,
								"data" => @json_encode($row["field_data"]),
								"gmtime_lead_create" => gmdate("Y-m-d H:i:s", $created_time),
							]
						);
						
						/* Remove lead */
						try
						{
							$response = $fb->delete('/' . $row["id"], [], $access_token);
							$graphNode = $response->getGraphNode()->asArray();
						}
						catch (\Exception $e)
						{
						}
					}
				}
				catch (\Exception $e)
				{
				}
			}
		}
		
		/* Send emails */
		$email_to = \Elberos\Facebook\Leads\Helper::get_key("elberos_facebook_leads_email_to", "");
		$sql = "select leads.*, leads_forms.name as form_name ".
			"from " . $wpdb->base_prefix . "elberos_facebook_leads as leads " .
			"left join " . $wpdb->base_prefix . "elberos_facebook_leads_forms as leads_forms " .
				"on (leads_forms.id = leads.form_id) " .
			"where leads.send_email=0 and leads.is_deleted=0;"
		;
		$leads = $wpdb->get_results($sql, ARRAY_A);
		foreach ($leads as $row)
		{
			list ($title, $message) = static::getLeadsMail($row);
			\Elberos\add_email("forms", $email_to, $title, $message);
			$wpdb->update
			(
				$wpdb->base_prefix . "elberos_facebook_leads",
				[
					"id" => $row["id"],
				],
				[
					"send_email" => 1,
				]
			);
		}
	}
	
	
	
	/**
	 * Returns leads mail
	 */
	public static function getLeadsMail($lead_item)
	{
		$title = "";
		$message = "";
		
		$site_name = get_bloginfo("", "name");
		$title = "Новая заявка с сайта " . $site_name;
		
		$form_data_res = [];
		$form_data_res[] =
		[
			'title' => "Сайт",
			'value' => $site_name,
		];
		$form_data_res[] =
		[
			'title' => "Фэйсбук форма",
			'value' => $lead_item["form_name"],
		];
		
		$form_data = @json_decode($lead_item["data"], true);
		foreach ($form_data as $item)
		{
			$form_data_res[] =
			[
				'title' => $item["name"],
				'value' => implode(", ", $item["values"]),
			];
		}
		
		$res_data = array_map
		(
			function($item)
			{
				return "
					<tr class='forms_data_item'>
						<td class='forms_data_item_key' style='padding: 2px; text-align: right;'>".
							esc_html($item['title']).":</td>
						<td class='forms_data_item_value' style='padding: 2px; text-align: left;'>".
							esc_html($item['value'])."</td>
					</tr>
				";
			},
			$form_data_res
		);
		
		ob_start();
		?>
		<html>
		<head>
		<title><?php echo $title; ?></title>
		</head>
		<body>
		<div style="font-family:verdana;font-size:16px">
		<table class="forms_data_display_item">
			<?php echo implode($res_data, ""); ?>
		</table>
		</div>
		</body>
		</html>
		<?php
		$message = ob_get_contents();
		ob_end_clean();
		
		return [$title, $message];
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

function wp_facebook_lead_create_instance()
{
	wp_facebook_lead_form_api_load();
	
	$item = \Elberos\Facebook\Leads\Helper::load_options([
		"elberos_facebook_leads_app_id"=>"",
		"elberos_facebook_leads_app_secret"=>"",
	]);
	
	$fb = new \Facebook\Facebook([
		'app_id' => $item['elberos_facebook_leads_app_id'],
		'app_secret' => $item['elberos_facebook_leads_app_secret'],
		/* 'default_graph_version' => 'v2.10', */
	]);
	
	return $fb;
}

}
