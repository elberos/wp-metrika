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


if ( !class_exists( Info::class ) ) 
{

class Info
{
	use Helper;
	
	
	public static function show()
	{
		
		$time = time();
		$app_id = get_option( 'elberos_wp_metrika_app_id' );
		$metrika_id = get_option( 'elberos_wp_metrika_id' );
		$token_expires_in = get_option( 'elberos_wp_metrika_token_expires_in' );
		$last_time = (int)get_option( 'elberos_wp_metrika_last_time', 0 );
		$pageviews = (int)get_option( 'elberos_wp_metrika_pageviews30', 0 );
		$visits = (int)get_option( 'elberos_wp_metrika_visits30', 0 );
		
		$token_exists = $app_id != null && $metrika_id != null && $token_expires_in != null;
		
		?>
		<h2><?php _e('Info', 'elberos-wp-metrika')?></h2>
		<div class="wrap">	
			
			<?php if ( !$token_exists ) { ?>
			<div style='padding-bottom: 20px;'>
				<div id="notice" class="error"><p>Token does not exists</p></div>
			</div>
			
			<?php } else if ( $token_expires_in > $time ) { ?>
			<div style='padding-bottom: 20px;'>
				<div id="message" class="updated"><p>Token exists</p></div>
				Your token expire:
					<b><?php echo date("Y-m-d H:i:s e", $token_expires_in) ?></b>
			</div>
			
			<?php } else if ( $token_expires_in <= $time ) { ?>
			<div style='padding-bottom: 20px;'>
				<div id="notice" class="error"><p>Attention: Token is expired</p></div>
				Your token expire:
					<b><?php echo date("Y-m-d H:i:s e", $token_expires_in) ?></b>
			</div>
			<?php } ?>
			
			
			<?php if ( $last_time > 0 ) { ?>
				
				Last request: <?php echo date("Y-m-d H:i:s e", $last_time) ?><br/>
				Pageviews: <?php echo esc_html($pageviews) ?><br/>
				Visits: <?php echo esc_html($visits) ?><br/>
				
			<?php } ?>
			
			
		</div>
		<?php
	}
	
	
}

}