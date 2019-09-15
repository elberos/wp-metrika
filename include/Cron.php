<?php

/*!
 *  Elberos WP Metrika
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


class Cron
{
	use Helper;
	
	
	static function load_data()
	{
		$app_id = get_option( 'elberos_wp_metrika_app_id' );
		$metrika_id = get_option( 'elberos_wp_metrika_id' );
		$access_token = get_option( 'elberos_wp_metrika_token_access' );
		$token_expires_in = get_option( 'elberos_wp_metrika_token_expires_in' );
		if ($app_id == null || $metrika_id == null || $token_expires_in == null || $access_token == null)
		{
			return;
		}
		if (time() > $token_expires_in)
		{
			return;
		}
		
		$arr = 
		[
			"ids"=>$metrika_id,
			"metrics"=>"ym:s:pageviews,ym:s:visits",
			"date1"=>"30daysAgo",
			"date2"=>"today",
		];
		$url = "https://api-metrika.yandex.net/stat/v1/data?" . http_build_query($arr);
		
		/* curl request */
		$curl = curl_init();
		curl_setopt_array(
			$curl,
			[
				CURLOPT_URL => $url,
				CURLOPT_USERAGENT => "WordPress",
				CURLOPT_CUSTOMREQUEST => 'GET',
				CURLOPT_RETURNTRANSFER => true,
				CURLOPT_HTTPHEADER => 
				[
					"Authorization: OAuth " . $access_token,
					"Content-Type: application/x-yametrika+json",
				],
			]
		);
		$out = curl_exec($curl);
		$code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
		curl_close($curl);
		
		
		if ($code == 200)
		{
			$response = @json_decode($out, true);
			if ($response != null)
			{
				$data = isset($response['data']) ? $response['data'] : [];
				$data = isset($data[0]) ? $data[0] : [];
				$metrics = isset($data['metrics']) ? $data['metrics'] : [];
				$pageviews = isset($metrics[0]) ? $metrics[0] : 0;
				$visits = isset($metrics[1]) ? $metrics[1] : 0;
				
				static::update_key("elberos_wp_metrika_last_time", time());
				static::update_key("elberos_wp_metrika_pageviews30", $pageviews);
				static::update_key("elberos_wp_metrika_visits30", $visits);
			}
		}
		
	}
	
	
}
