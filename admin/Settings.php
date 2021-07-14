<?php

/*!
 *  Elberos Facebook Lead Form Connector
 *
 *  (c) Copyright 2021 "Ildar Bikmamatov" <support@elberos.org>
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

namespace Elberos\Facebook;


if ( !class_exists( Settings::class ) ) 
{

class Settings
{
	
	public static function show()
	{
		session_start();
		
		if ( isset($_POST["nonce"]) && (int)wp_verify_nonce($_POST["nonce"], basename(__FILE__)) > 0 )
		{
			Helper::update_post_key("elberos_facebook_app_id");
			Helper::update_post_key("elberos_facebook_app_secret");
			Helper::update_post_key("elberos_facebook_access_token");
		}
		
		$form_item = Helper::load_options([
			"elberos_facebook_app_id"=>"",
			"elberos_facebook_app_secret"=>"",
			"elberos_facebook_access_token"=>"",
		]);
		
		$error = [];
		$success = [];
		$redirect_url = "";
		$loginUrl = "";
		$accessToken = null;
		
		if ($form_item['elberos_facebook_app_id'] != "" && $form_item['elberos_facebook_app_secret'] != "")
		{
			wp_facebook_lead_form_api_load();
			
			$fb = new \Facebook\Facebook([
				'app_id' => $form_item['elberos_facebook_app_id'],
				'app_secret' => $form_item['elberos_facebook_app_secret'],
				'default_graph_version' => 'v2.10',
			]);
			
			$helper = $fb->getRedirectLoginHelper();
			
			// Redirect url
			$redirect_url = site_url("/wp-admin/admin.php?page=elberos-facebook-settings&oauth=true");
			
			// Optional permissions https://developers.facebook.com/docs/permissions/reference/
			$permissions = ['leads_retrieval'];
			
			// Facebook login url
			$loginUrl = $helper->getLoginUrl($redirect_url, $permissions);
			
			if ( isset($_GET['oauth']) )
			{
				try
				{
					$accessToken = $helper->getAccessToken();
				}
				catch(\Facebook\Exception\ResponseException $e)
				{
					$error[] = "Graph returned an error: " . $e->getMessage();
				}
				catch(\Facebook\Exception\SDKException $e)
				{
					$error[] = "Facebook SDK returned an error: " . $e->getMessage();
				}
				catch(\Exception $e)
				{
					$error[] = "Fatal Error: " . $e->getMessage();
				}
				
				if (!isset($accessToken))
				{
					if ($helper->getError())
					{
						$error[] = "Error: " . $helper->getError();
						$error[] = "Error Code: " . $helper->getErrorCode();
						$error[] = "Error Reason: " . $helper->getErrorReason();
						$error[] = "Error Description: " . $helper->getErrorDescription();
					}
					else
					{
						$error[] = "Error: Bad request";
					}
				}
				else
				{
					$success[] = "Токен успешно получен";
					Helper::update_key("elberos_facebook_access_token", (string) $accessToken);
					$form_item["elberos_facebook_access_token"] = (string) $accessToken;
				}
			}
		}
		
		else
		{
			$error[] = "OAuth App ID or Secret is empty";
		}
		
		?>
		<div class="icon32 icon32-posts-post" id="icon-edit"><br></div>
		<h2><?php _e('Settings', 'elberos-facebook')?></h2>
		<div class="wrap">	
			<form id="form" method="POST">
				<input type="hidden" name="nonce" value="<?php echo wp_create_nonce(basename(__FILE__))?>"/>
				
				<div class="add_or_edit_form" style="width: 60%">
					<? static::display_form($form_item) ?>
				</div>
				<input type="submit" id="submit" class="button-primary" name="submit"
					value="<?php _e('Save', 'elberos-facebook')?>" >
			</form>
			<div style='clear: both;'></div>
		</div>
		
		<?php if ($form_item['elberos_facebook_app_id'] != "" && $form_item['elberos_facebook_app_secret'] != "" &&
			!isset($_GET['oauth']))
		{
			?>
				<div style='padding-top: 10px;'></div>
				<h2>Setup Facebook Token:</h2>
				
				<div class="wrap">
					
					<div>
						Redirect url:<br/>
						<input type="text" style="width: 100%" value="<?= esc_attr($redirect_url) ?>" readonly>
					</div>
					
					<br/>
					
					<div>
						<a href="<?php echo esc_attr($loginUrl) ?>" target="_blank">
							<button class="button">Auth Facebook</button>
						</a>
					</div>
					
				</div>
			<?php
		}
		
		/* Errorr */
		if (count($error) > 0)
		{
			?>
			<div class="wrap">
				<div id="notice" class="error">
					<p><?= nl2br( esc_html( implode("\n", $error ) ) ) ?></p>
				</div>
			</div>
			<?php
		}
		
		/* Success */
		if (count($success) > 0)
		{
			?>
			<div class="wrap">
				<div id="message" class="updated">
					<p><?= nl2br( esc_html( implode("\n", $success ) ) ) ?></p>
				</div>
			</div>
			<?php
		}
	}
	
	
	
	public static function display_form($item)
	{
		?>
		
		<!-- App ID -->
		<p>
		    <label for="elberos_facebook_app_id"><?php _e('Facebook App ID:', 'elberos-facebook')?></label>
		<br>
            <input id="elberos_facebook_app_id" name="elberos_facebook_app_id" type="text" style="width: 100%"
				value="<?php echo esc_attr($item['elberos_facebook_app_id'])?>" >
		</p>
		
		<!-- App Secret -->
		<p>
		    <label for="elberos_facebook_app_secret"><?php _e('Facebook App Secret:', 'elberos-facebook')?></label>
		<br>
            <input id="elberos_facebook_app_secret" name="elberos_facebook_app_secret" type="text" style="width: 100%"
				value="<?php echo esc_attr($item['elberos_facebook_app_secret'])?>" >
		</p>
		
		<!-- Access token -->
		<p>
		    <label for="elberos_facebook_access_token"><?php _e('Access token:', 'elberos-facebook')?></label>
		<br>
            <input id="elberos_facebook_access_token" name="elberos_facebook_access_token" type="text" style="width: 100%"
				value="<?php echo esc_attr($item['elberos_facebook_access_token'])?>" >
		</p>
		
		<?php
	}
	
	
	
}

}