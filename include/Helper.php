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

namespace Elberos\Facebook\Leads;

if ( !class_exists( Helper::class ) ) 
{

class Helper
{
	
	public static function update_key($key, $value)
	{
		if ( ! is_multisite() )
		{
			if (!add_option($key, $value, "", "no"))
			{
				update_option($key, $value);
			}
		}
		else
		{
			if (!add_network_option(1, $key, $value, "", "no"))
			{
				update_network_option(1, $key, $value);
			}
		}
	}
	
	public static function get_key($key, $value)
	{
		if ( ! is_multisite() )
		{
			return get_option($key, $value);
		}
		return get_network_option(1, $key, $value);
	}
	
	
	public static function update_post_key($key, $def_val = "")
	{
		$value = isset($_POST[$key]) ? $_POST[$key] : $def_val;
		static::update_key($key, $value);
	}
	
	
	public static function load_options($arr)
	{
		$item = [];
		foreach ($arr as $key => $value)
		{
			$item[$key] = get_option( $key, $value );
		}
		return $item;
	}
}

}