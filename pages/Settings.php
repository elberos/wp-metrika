<?php

/*!
 *  Elberos Forms
 *
 *  (c) Copyright 2019 "Ildar Bikmamatov" <support@elberos.org>
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

namespace Elberos\WP_Metrika;


if ( !class_exists( Settings::class ) ) 
{

class Settings
{
	use Helper;
	
	
	public static function show()
	{
		
		if ( isset($_POST["oauth_code"]) )
		{
			static::oauth_get_token();
			return;
		}
		
		if ( isset($_POST["nonce"]) && (int)wp_verify_nonce($_POST["nonce"], basename(__FILE__)) > 0 )
		{
			static::update_post_key("elberos_wp_metrika_id");
			static::update_post_key("elberos_wp_metrika_app_id");
			static::update_post_key("elberos_wp_metrika_app_secret");
		}
		
		$item = static::load_options([
			"elberos_wp_metrika_id"=>"",
			"elberos_wp_metrika_app_id"=>"",
			"elberos_wp_metrika_app_secret"=>"",
			"elberos_wp_metrika_token_expires_in"=>"",
		]);
		
		
		
		/* OAuth URL */
		$oauth_redirect = "https://oauth.yandex.ru/verification_code";
		$oauth_url = "https://oauth.yandex.ru/authorize?response_type=code&client_id=".
			$item['elberos_wp_metrika_app_id']."&redirect_uri=" . $oauth_redirect;
		
		$time = time();
		$token_expires_in = $item['elberos_wp_metrika_token_expires_in'];
		
		?>
		<div class="icon32 icon32-posts-post" id="icon-edit"><br></div>
		<h2><?php _e('Settings', 'elberos-wp-metrika')?></h2>
		<div class="wrap">	
			<form id="form" method="POST">
				<input type="hidden" name="nonce" value="<?php echo wp_create_nonce(basename(__FILE__))?>"/>
				
				<div class="add_or_edit_form" style="width: 60%">
					<? static::display_form($item) ?>
				</div>
				<input type="submit" id="submit" class="button-primary" name="submit"
					value="<?php _e('Save', 'elberos-wp-metrika')?>" >
						
			</form>
			<div style='clear: both;'></div>
		</div>
		
		<div style='padding-top: 10px;'></div>
		<h2>Setup Yandex Token</h2>
		
		<?php if ($item['elberos_wp_metrika_app_id'] != "" && $item['elberos_wp_metrika_app_secret'] != "") {?>
		<div class="wrap">
			
			<?php if ( $token_expires_in != "" && $token_expires_in > $time ) { ?>
			<div style='padding-bottom: 20px;'>
				<div id="message" class="updated"><p>Token exists</p></div>
				Your token expire:
					<b><?php echo date("Y-m-d H:i:s e", $item['elberos_wp_metrika_token_expires_in']) ?></b>
			</div>
			<?php } ?>
			
			<?php if ( $token_expires_in != "" && $token_expires_in <= $time ) { ?>
			<div style='padding-bottom: 20px;'>
				<div id="notice" class="error"><p>Attention: Token is expired</p></div>
				Your token expire:
					<b><?php echo date("Y-m-d H:i:s e", $item['elberos_wp_metrika_token_expires_in']) ?></b>
			</div>
			<?php } ?>
			
			
			<div>
				<a href="<?php echo esc_attr($oauth_url) ?>" target="_blank">
					<button class="button">Get Yandex Verification Code from OAuth Service</button>
				</a>
			</div>
			
			<form method="POST">
				<input type="input" name="oauth_code" value="" 
					placeholder="Enter Yandex Verification Code" style="width: 300px;" />
				<button class="button-primary">Setup Yandex Code</button>
			</form>
		</div>
		<?php } else { ?>
		<div class="wrap">
			<div id="notice" class="error"><p>OAuth App ID or Secret is empty</p></div>
		</div>
		<?php } ?>	
		<?php
	}
	
	
	
	public static function display_form($item)
	{
		?>
		
		<!-- Metrika ID -->
		<p>
		    <label for="elberos_wp_metrika_id"><?php _e('Metrika ID:', 'elberos-wp-metrika')?></label>
		<br>
            <input id="elberos_wp_metrika_id" name="elberos_wp_metrika_id" type="text" style="width: 100%"
				value="<?php echo esc_attr($item['elberos_wp_metrika_id'])?>" >
		</p>
		
		<!-- OAuth App ID -->
		<p>
		    <label for="elberos_wp_metrika_app_id"><?php _e('OAuth App ID:', 'elberos-wp-metrika')?></label>
		<br>
            <input id="elberos_wp_metrika_app_id" name="elberos_wp_metrika_app_id" type="text" style="width: 100%"
				value="<?php echo esc_attr($item['elberos_wp_metrika_app_id'])?>" >
		</p>
		
		<!-- OAuth App Secret -->
		<p>
		    <label for="elberos_wp_metrika_app_secret">
				<?php _e('OAuth App Secret:', 'elberos-wp-metrika')?>
			</label>
		<br>
            <input id="elberos_wp_metrika_app_secret" name="elberos_wp_metrika_app_secret" 
				type="text" style="width: 100%"
				value="<?php echo esc_attr($item['elberos_wp_metrika_app_secret'])?>" >
		</p>
		
		<?php
	}
	
	
	
	public static function oauth_get_token()
	{
		$app_id = get_option( 'elberos_wp_metrika_app_id' );
		$app_secret = get_option( 'elberos_wp_metrika_app_secret' );
		$oauth_code = isset($_POST['oauth_code']) ? $_POST['oauth_code'] : '';
		
		if ($app_id == null || $app_secret == null)
		{
			?>
			<div class="wrap">
				<div id="notice" class="error"><p>OAuth App ID or Secret is empty</p></div>
			</div>
			<?php
		}
		
		
		/* curl request */
		$curl = curl_init();
		curl_setopt_array(
			$curl,
			[
				CURLOPT_URL => "https://oauth.yandex.ru/token",
				CURLOPT_USERAGENT => "WordPress",
				CURLOPT_CUSTOMREQUEST => 'POST',
				CURLOPT_RETURNTRANSFER => true,
				CURLOPT_POSTFIELDS => 
				[
					"grant_type"=>"authorization_code",
					"code"=>$oauth_code,
					"client_id"=>$app_id,
					"client_secret"=>$app_secret,
				],
			]
		);
		$out = curl_exec($curl);
		$code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
		curl_close($curl);
		
		
		$response = null;
		$code = (int)$code;
		if ($code == 200) $response = @json_decode($out, true);
		
		if ($response == null)
		{
			?>
			<div class="wrap">
				<div id="notice" class="error"><p>Error. Response is null</p></div>
				<div>
					Code: <?php echo esc_html($code); ?><br/>
					<?php echo esc_html($out); ?>
				</div>
			</div>
			<?php
			return;
		}
		
		$token_type = isset($response['token_type']) ? $response['token_type'] : "";
		$access_token = isset($response['access_token']) ? $response['access_token'] : "";
		$expires_in = isset($response['expires_in']) ? $response['expires_in'] : "";
		$refresh_token = isset($response['refresh_token']) ? $response['refresh_token'] : "";
		$expires_in = time() + $expires_in;
		
		static::update_key("elberos_wp_metrika_token_type", $token_type);
		static::update_key("elberos_wp_metrika_token_access", $access_token);
		static::update_key("elberos_wp_metrika_token_expires_in", $expires_in);
		static::update_key("elberos_wp_metrika_token_refresh", $refresh_token);
		
		?>
		<div class="wrap">
			<div id="message" class="updated"><p>Success</p></div>
			Your token expire: <?php echo date("Y-m-d H:i:s", $expires_in) ?><br/>
		</div>
		<?php
		
	}
	
	
}

}