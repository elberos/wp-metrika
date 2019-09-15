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


trait Helper
{
	public static function update_key($key, $value)
	{
		if (!add_option($key, $value, "", "no"))
		{
			update_option($key, $value);
		}
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